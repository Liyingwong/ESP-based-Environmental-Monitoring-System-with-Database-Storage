Introduction
The heart of the system is the ESP8266 microcontroller, selected for its Wi-Fi capabilities and low power consumption, making it ideal for IoT applications. The system integrates several sensors, including the MQ-135 gas sensor and the DHT22 temperature and humidity sensor, to measure key environmental parameters.

Objective
Develop a system using an ESP microcontroller (ESP8266) to monitor environmental conditions and store the collected data in a relational database.

Features
Real-time data collection: Measures temperature, humidity, and CO2 levels.
Data display: Displays readings on an I2C LCD display.
Data storage: Transmits data to a remote server for storage in a relational database.
Web interface: Provides access to historical data for analysis and monitoring.

Hardware Required:
ESP8266 microcontroller
DHT22 temperature and humidity sensor
MQ-135 gas sensor
I2C LCD display
Breadboard and jumper wires
Power supply
Software Required
Arduino IDE
ESP8266 Board Package for Arduino

Libraries: ESP8266WiFi, ESP8266HTTPClient, DHT, Wire, LiquidCrystal_I2C, WiFiClient

Installation
Clone the repository:
git clone https://github.com/Liyingwong/environmental-monitoring-system.git
cd environmental-monitoring-system
Install required libraries:
Ensure you have the following libraries installed in your Arduino IDE:

ESP8266WiFi
ESP8266HTTPClient
DHT
Wire
LiquidCrystal_I2C
Setup the Arduino IDE:

Open the Arduino IDE.
Go to File > Preferences and add the following URL to the Additional Boards Manager URLs: http://arduino.esp8266.com/stable/package_esp8266com_index.json.
Go to Tools > Board > Boards Manager and install the ESP8266 board package.
Select your ESP8266 board from Tools > Board > ESP8266 Boards.
Configure WiFi and Server Details:
Update the following lines in the code with your WiFi and server details:
const char* ssid = "your_ssid";
const char* password = "your_password";
const char* serverName = "http://your_server_address/post.php";
String apiKeyValue = "your_api_key";

Upload the code:
Connect your ESP8266 to your computer and upload the provided code.

Usage
Power the system:
Connect the ESP8266 to a power source.

Monitor the Serial Output:
Open the Serial Monitor in Arduino IDE to view real-time data and debug information.

View Data on LCD:
The I2C LCD will display the current temperature, humidity, and CO2 levels.

Access Data Remotely:
Data will be sent to the configured server endpoint and stored in a relational database. Use your web interface to visualize and analyze historical data.
