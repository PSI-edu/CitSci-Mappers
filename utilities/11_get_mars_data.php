<?php

// Go through the all the images in the database and check if
// they are entirely black. If they are, set done=1.

// load in the settings
global $db_host, $db_username, $db_password, $db_name, $db_port;
require_once("../api/settings.php");

// Check connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to database: " . $db_name . " on " . $db_host . ":" . $db_port . "\n";

// find out how many images there are with app_id = 2
$sql = "SELECT COUNT(*)/2 as total FROM images WHERE application_id=2";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_images = intval($row["total"]);

// find out how many images are done with app_id = 2
$sql = "SELECT COUNT(*) as done_count FROM images WHERE application_id=2 AND done=1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$done_count = intval($row["done_count"]);

echo "Images done for application_id=2: " . $done_count . " of ". $total_images ."\n";

