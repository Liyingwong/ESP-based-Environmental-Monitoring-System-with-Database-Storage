#include <ESP8266WiFi.h>         // Library to enable WiFi functionality on ESP8266
#include <ESP8266HTTPClient.h>   // Library to handle HTTP operations
#include <DHT.h>                 // Library for the DHT22 temperature and humidity sensor
#include <Wire.h>                // Library for I2C communication
#include <LiquidCrystal_I2C.h>   // Library for I2C LCD display
#include <WiFiClient.h>          // Library to create a WiFi client

#define DHTPIN D3                // Define the pin where the DHT22 is connected
#define DHTTYPE DHT22            // Define the type of DHT sensor
#define MQ135PIN A0              // Define the analog pin for MQ-135 gas sensor

DHT dht(DHTPIN, DHTTYPE);        // Initialize the DHT sensor
LiquidCrystal_I2C lcd(0x27, 16, 2); // Initialize the LCD with I2C address 0x27 and size 16x2

const char* ssid = "liying";         // WiFi SSID
const char* password = "liying28";   // WiFi Password
const char* serverName = "http://172.20.10.7/ESP-basedEnvironmentalMonitoringSystemwithDatabaseStorage/post.php"; // Server URL for data POST

String apiKeyValue = "YongChun021030"; // API key for authentication

WiFiClient client;                // Create a WiFi client instance

void setup() {
  Serial.begin(115200);           // Initialize serial communication at 115200 baud rate
  dht.begin();                    // Start the DHT sensor
  lcd.init();                     // Initialize the LCD
  lcd.backlight();                // Turn on the LCD backlight
  lcd.clear();                    // Clear any previous data on the LCD

  WiFi.begin(ssid, password);     // Begin WiFi connection
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {  // Wait until the WiFi is connected
    delay(1000);                   // Delay for 1 second
    Serial.print(".");             // Print a dot for connection progress
  }
  Serial.println("\nConnected to WiFi");  // Print confirmation when connected
}

void loop() {
  float temperature = dht.readTemperature(); // Read temperature from DHT22
  float humidity = dht.readHumidity();       // Read humidity from DHT22
  int co2_level = analogRead(MQ135PIN);      // Read CO2 level from MQ-135

  if (isnan(temperature) || isnan(humidity)) {  // Check if the readings are valid
    Serial.println("Failed to read from DHT sensor!"); // Print error message if readings are invalid
    return;                                   // Exit the loop if readings are invalid
  }

  // Print readings to serial monitor
  Serial.print("Humidity: ");
  Serial.print(humidity);
  Serial.print(" %\t");
  Serial.print("Temperature: ");
  Serial.print(temperature);
  Serial.print("  °C");
  Serial.println("");
  Serial.print("CO2: ");
  Serial.print(co2_level);
  Serial.println(" PPM");

  // Display readings on LCD
  lcd.setCursor(0,0);              // Set cursor to first row
  lcd.print("Humi :");             // Print label for humidity
  lcd.print(humidity);             // Print humidity value
  lcd.print(" %");                 // Print percentage sign

  lcd.setCursor(0,1);              // Set cursor to second row
  lcd.print("Temp :");             // Print label for temperature
  lcd.print(temperature);          // Print temperature value
  lcd.print(" C");                 // Print Celsius sign

  delay(2000);                     // Delay for 2 seconds before updating LCD

  lcd.clear();                     // Clear LCD screen before displaying CO2 reading

  lcd.setCursor(0,0);              // Set cursor to first row
  lcd.print("CO2 :");              // Print label for CO2 level
  lcd.print(co2_level);            // Print CO2 level value
  lcd.print(" PPM");               // Print PPM (parts per million) unit
  String condition;                // Variable to store air quality condition
  // Display air quality status based on CO2 level
  if ((co2_level >= 300) && (co2_level <= 1400)) {  // Check if CO2 level is in the 'Good' range
    lcd.setCursor(0,1);            // Set cursor to second row
    lcd.print("Condition: Good "); // Print condition status
    Serial.println("Condition: Good");  // Print condition to serial monitor
    condition = "Good";            // Set condition variable to 'Good'
  } else if ((co2_level >= 1400) && (co2_level <= 2000)) {  // Check if CO2 level is in the 'Bad' range
    lcd.setCursor(0,1);            // Set cursor to second row
    lcd.print("Condition: Bad ");  // Print condition status
    Serial.println("Condition: Bad");  // Print condition to serial monitor
    condition = "Bad";             // Set condition variable to 'Bad'
  } else {                          // Check if CO2 level is in the 'Danger' range
    lcd.setCursor(0,1);            // Set cursor to second row
    lcd.print("   Danger!");       // Print danger status
    Serial.println("Condition: Danger"); // Print condition to serial monitor
    condition = "Danger";          // Set condition variable to 'Danger'
  }

  if (WiFi.status() == WL_CONNECTED) {  // Check if WiFi is connected
    HTTPClient http;              // Create an HTTP client instance
    Serial.println("WiFi is connected"); // Print confirmation of WiFi connection
    Serial.println("Connecting to server..."); // Print message before connecting to server
    http.begin(client, serverName); // Initialize HTTP connection to server
    http.addHeader("Content-Type", "application/x-www-form-urlencoded"); // Add HTTP header for form data

    // Prepare data to be sent in the HTTP POST request
    String httpRequestData = "api_key=" + apiKeyValue + "&temperature=" + String(temperature) + "&humidity=" + String(humidity) + "&co2_level=" + String(co2_level) +"&condition="  + condition;

    Serial.print("Sending data: ");
    Serial.println(httpRequestData);  // Print data being sent

    int httpResponseCode = http.POST(httpRequestData); // Send HTTP POST request and get response code

    if (httpResponseCode > 0) {  // Check if the response code is positive
      String response = http.getString();  // Get the response from the server
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);    // Print HTTP response code
      Serial.print("Response: ");
      Serial.println(response);            // Print server response
    } else {
      Serial.print("Error on sending POST: ");  // Print error if POST request fails
      Serial.println(httpResponseCode);    // Print response code
    }
    http.end();                           // End HTTP connection
  } else {
    Serial.println("WiFi not connected"); // Print message if WiFi is not connected
  }

  delay(30000); // Wait 30 seconds before the next reading
}

