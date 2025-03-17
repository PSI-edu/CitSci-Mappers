<?php

global $db_host, $db_username, $db_password, $db_name, $db_port;

require_once("../settings.php");

$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to database: " . $db_name . " on " . $db_host . ":" . $db_port . "<br>";

// Get all the tables to install and install them
$dir = "db_tables";

foreach (glob($dir . '/*.sql') as $filename) {
    echo "Creating table: " . $filename . "<br>";
    $sql = file_get_contents($filename);
    if($conn->query($sql) === TRUE) {
        echo "Table created successfully from " . $filename . "<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

}

echo "Tables created successfully<br>";


// Get all the application jsons and insert them into the database
$dir = "applications";

foreach (glob($dir . '/*.json') as $filename) {
    $fileContents = file_get_contents($filename);

    if ($fileContents === false) {
        echo "Error reading file: " . $filename . "<br>";
        continue; // Skip to the next file.
    }

    $jsonData = json_decode($fileContents, true); // true for associative array

    if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
        echo "Error decoding JSON in file: " . $filename . "<br>";
        echo "JSON Error: " . json_last_error_msg() . "<br>";
        continue; // Skip to the next file.
    }

    // Process the JSON data here.
    $name = $jsonData['name'];
    $title = $jsonData['title'];
    $description = $jsonData['description'];
    $background_url = $jsonData['background_url'];
    $icon_url = $jsonData['icon_url'];

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO applications (name, title, description, background_url, icon_url) VALUES (?, ?, ?, ?, ?)");
    // Bind parameters
    $stmt->bind_param("sssss", $name, $title, $description, $background_url, $icon_url);
    // Execute the statement
    if ($stmt->execute()) {
        echo "App record created successfully from " . $filename . "<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }

    // Close the statement
    $stmt->close();
}
echo "App records created successfully<br>";

echo "Installation completed successfully<br>";
$conn->close();