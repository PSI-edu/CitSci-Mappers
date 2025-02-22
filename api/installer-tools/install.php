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

$conn->close();