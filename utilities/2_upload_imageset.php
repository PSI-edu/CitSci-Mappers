<?php
// Inputs: id, name, filename, path

if ($argc != 5) {
die("The wrong number of arguments. Usage > upload_imageset.php app_id imageset_name imagelist.txt path_to_images\n");
} else if (!file_exists($argv[3])) {
die("The file $argv[3] does not exist.\n");
}

// Open the file
$file = fopen($argv[3], 'r');
$id = $argv[1];
$name = $argv[2];
$path = $argv[4];

global $db_host, $db_username, $db_password, $db_name, $db_port;
require_once("../api/settings.php");

// Check connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);
if ($conn->connect_error) {
die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to database: " . $db_name . " on " . $db_host . ":" . $db_port . "<br>";


$sql = "INSERT INTO image_sets (name, application_id) VALUES ('$name', '$id') ";
if ($conn->query($sql) === TRUE) {
$set_id = $conn->insert_id;
echo "New record created successfully with id: " . $set_id . "\n";
} else {
echo "Error: " . $sql . "<br>" . $conn->error;
}
$set_id =1;

// Create the image set
$i = 0;
while (($data = fgetcsv($file, 0, "\t")) !== FALSE) {
if (count($data) == 3) { // Ensure there are three columns
$filename = $data[0]; //Sanitize filename
$x = intval($data[1]); //convert x to int
$y = intval($data[2]); //convert y to int

$fullpath = $path . $filename;
$query = "INSERT INTO images (image_set_id, application_id, name, file_location, x, y)
VALUES ($set_id, $id, '$filename', '$fullpath', $x, $y)";

if ($conn->query($query) === TRUE) {
$i++;
} else {
echo "Error: " . $query . "<br>" . $conn->error;
die();
}
} else {
echo "Error: Invalid data format in line: " . implode(",", $data) . "<br>";
}
}
$conn->close();
echo "inserted $i images successfully.<br>";
