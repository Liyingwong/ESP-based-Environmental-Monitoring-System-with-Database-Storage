<?php

$servername = "localhost";
$dbname = "environmental_monitoring";
$username = "root";
$password = "";
$api_key_value = "YongChun021030";

$api_key = $humidity = $temperature = $co2_level = $condition = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $api_key = test_input($_POST["api_key"]);
  if ($api_key == $api_key_value) {
    $humidity = test_input($_POST["humidity"]);
    $temperature = test_input($_POST["temperature"]);
    $co2_level = test_input($_POST["co2_level"]);
    $condition = test_input($_POST["condition"]);

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO sensor_data (humidity, temperature, co2_level, `condition`) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $humidity, $temperature, $co2_level, $condition);

    // Execute the prepared statement
    if ($stmt->execute()) {
      echo "New record created successfully";
    } else {
      echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
  } else {
    echo "Wrong API Key provided.";
  }
} else {
  echo "No data posted with HTTP POST.";
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
?>
