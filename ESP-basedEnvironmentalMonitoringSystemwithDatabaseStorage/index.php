<!DOCTYPE html>
<html>

<head>
  <title>Data Dashboard</title>
  <!-- Including Chart.js for charts and JustGage for gauges -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/justgage@1.2.9/raphael-2.1.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/justgage@1.2.9/justgage.js"></script>
  <style>
    /* Basic styling for the dashboard */
    body {
      font-family: Arial, sans-serif;
      text-align: center;
    }

    .title {
      font-size: 36px;
      margin: 20px 0;
    }

    .host {
      font-size: 18px;
      margin-bottom: 20px;
      font-weight: bold;
    }

    .current-values {
      margin: 20px 0;
      font-size: 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .current-values div {
      margin: 10px 0;
      display: flex;
      align-items: center;
    }

    .current-values img {
      vertical-align: middle;
      margin-right: 10px;
    }

    .condition-button {
      padding: 10px 20px;
      font-size: 20px;
      border: none;
      color: white;
      cursor: pointer;
    }

    .good {
      background-color: green;
    }

    .bad {
      background-color: red;
    }

    .gauge-container {
      width: 200px;
      height: 160px;
      display: inline-block;
      margin-right: 30px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }

    th,
    td {
      border: 1px solid #ddd;
      padding: 8px;
    }

    th {
      padding-top: 12px;
      padding-bottom: 12px;
      text-align: center;
      background-color: #D8BFD8;
      color: white;
    }

    .chart-container {
      width: 45%;
      margin: 20px auto;
    }

    .nav-buttons {
      margin: 20px 0;
    }
  </style>
</head>

<body>
  <div class="title">ESP - based Environmental Monitoring System</div>
  <div class="host">Host: Wong Li Ying 289912</div>

  <?php
  // Database connection details
  $servername = "localhost";
  $dbname = "environmental_monitoring";
  $username = "root";
  $password = "";

  // Creating a connection to the database
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Check if the connection was successful
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $limit = 10; // Number of entries to show per page
  $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

  // SQL query to fetch the latest sensor data
  $sql = "SELECT id, humidity, temperature, co2_level, `condition`, timestamp 
            FROM sensor_data ORDER BY id DESC LIMIT $limit OFFSET $offset";
  $result = $conn->query($sql);
  $data = [];

  // Fetching the data and storing it in an array
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $data[] = $row;
    }
  } else {
    echo '<p>No data available</p>';
  }

  // Displaying the latest sensor values
  if (!empty($data)) {
    $latestData = $data[0];
    echo '<div class="current-values">';
    echo '<div><img src="humidity.png" alt="Humidity Icon" width="30"><span>Current Humidity: ' . $latestData["humidity"] . '%</span></div>';
    echo '<div><img src="temperature.jpg" alt="Temperature Icon" width="30"><span>Current Temperature: ' . $latestData["temperature"] . '°C</span></div>';
    echo '<div><img src="co2level.jpg" alt="CO2 Icon" width="30"><span>Current CO2 Level: ' . $latestData["co2_level"] . ' ppm</span></div>';
    echo '<div>Current Condition:  <span style="color: green; font-weight: bold;">' . $latestData["condition"] . '</span></div>';
    echo '</div>'; // Close the "current-values" div
  }



  // Creating the table to display the sensor data
  echo '<table>
        <tr>
            <th>ID</th>
            <th>Humidity (%)</th>
            <th>Temperature (°C)</th>
            <th>CO2 Level (ppm)</th>
            <th>Condition</th>
            <th>Timestamp</th>
        </tr>';

  // Populating the table with data
  foreach ($data as $row) {
    // Setting the condition cell color based on the condition value
    $conditionClass = $row["condition"] === 'good' ? 'style="color:green"' : '';

    echo '<tr>
          <td>' . $row["id"] . '</td>
          <td>' . $row["humidity"] . '</td>
          <td>' . $row["temperature"] . '</td>
          <td>' . $row["co2_level"] . '</td>
          <td ' . $conditionClass . '>' . ucfirst($row["condition"]) . '</td>
          <td>' . $row["timestamp"] . '</td>
        </tr>';
  }

  echo '</table>';


  // Navigation buttons for pagination
  echo '<div class="nav-buttons">
        <button onclick="loadPrevious()">Previous</button>
        <button onclick="loadNext()">Next</button>
    </div>';

  $conn->close();
  ?>

  <!-- Containers for the gauges -->
  <div class="gauge-container" id="gauge-humidity"></div>
  <div class="gauge-container" id="gauge-temperature"></div>
  <div class="gauge-container" id="gauge-co2"></div>

  <!-- Containers for the charts -->
  <div class="chart-container"><canvas id="humidityChart"></canvas></div>
  <div class="chart-container"><canvas id="temperatureChart"></canvas></div>
  <div class="chart-container"><canvas id="co2Chart"></canvas></div>

  <script>
    // Fetching the data from PHP
    var data = <?php echo json_encode(array_reverse($data)); ?>;
    var currentOffset = <?php echo $offset; ?>;
    var limit = <?php echo $limit; ?>;

    var humidityValues = data.map(function (item) { return item.humidity; });
    var temperatureValues = data.map(function (item) { return item.temperature; });
    var co2Values = data.map(function (item) { return item.co2_level; });
    var labels = data.map(function (item) { return item.timestamp; });

    // Function to create chart options
    var chartOptions = function (titleText) {
      return {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: titleText,
            font: {
              weight: 'bold'
            },
            color: 'green' // Set title color to green by default
          }
        },
        scales: {
          x: {
            display: true,
            title: {
              display: true,
              text: 'Timestamp'
            }
          },
          y: {
            display: true,
            title: {
              display: true,
              text: titleText.split(' ')[0]
            }
          }
        }
      };
    };

    // Creating the charts
    new Chart(document.getElementById('humidityChart'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Humidity (%)',
          data: humidityValues,
          borderColor: 'blue',
          fill: false
        }]
      },
      options: chartOptions('Humidity Over Time')
    });

    new Chart(document.getElementById('temperatureChart'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Temperature (°C)',
          data: temperatureValues,
          borderColor: 'red',
          fill: false
        }]
      },
      options: chartOptions('Temperature Over Time')
    });

    new Chart(document.getElementById('co2Chart'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'CO2 Level (ppm)',
          data: co2Values,
          borderColor: 'green',
          fill: false
        }]
      },
      options: chartOptions('CO2 Level Over Time')
    });

    // Creating the gauges
    new JustGage({
      id: 'gauge-humidity',
      value: humidityValues[humidityValues.length - 1],
      min: 0,
      max: 100,
      title: 'Humidity (%)'
    });

    new JustGage({
      id: 'gauge-temperature',
      value: temperatureValues[temperatureValues.length - 1],
      min: 0,
      max: 50,
      title: 'Temperature (°C)'
    });

    new JustGage({
      id: 'gauge-co2',
      value: co2Values[co2Values.length - 1],
      min: 0,
      max: 2000,
      title: 'CO2 Level (ppm)'
    });

    // Pagination functions
    function loadPrevious() {
      if (currentOffset - limit >= 0) {
        window.location.href = 'index.php?offset=' + (currentOffset - limit);
      }
    }

    function loadNext() {
      window.location.href = 'index.php?offset=' + (currentOffset + limit);
    }
  </script>
</body>

</html>