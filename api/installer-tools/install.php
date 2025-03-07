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
// Insert into the applications table a default application 0
$id = 0;
$name = "default";
$title = "Looking Ahead";
$description = "New projects are under development. Stay tuned.";

$stmt = $conn->prepare("INSERT INTO applications (id, name, title, description) VALUES (?, ?, ?, ?)");

// Bind parameters
$stmt->bind_param("isss", $id, $name, $title, $description);

// Execute the statement
if ($stmt->execute()) {
    echo "Default app record created successfully";
} else {
    echo "Error: " . $stmt->error;
}

//Insert the Mars Mosaic Project
$id = 1;
$name = "mars-mosaic";
$title = "Mars Mosaic";
$description = "Let's verify new algorithms make better Mars mosaics.";

$stmt->bind_param("isss", $id, $name, $title, $description);

// Execute the statement
if ($stmt->execute()) {
    echo "Mars Mosaic App created successfully";
} else {
    echo "Error: " . $stmt->error;
}

// Insert into the applications table Moon Mappers
$id = 2;
$name = "moon-mappers";
$title = "Moon Mappers";
$description = "Help us map out ancient geologies on the Moon.";

$stmt = $conn->prepare("INSERT INTO applications (id, name, title, description) VALUES (?, ?, ?, ?)");

// Bind parameters
$stmt->bind_param("isss", $id, $name, $title, $description);

// Execute the statement
if ($stmt->execute()) {
    echo "Default app record created successfully";
} else {
    echo "Error: " . $stmt->error;
}

$conn->close();