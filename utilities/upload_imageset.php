<?php

// Inputs: id, name, filename, path

if ($argc != 5) {
    die("The wrong number of arguments. Usage > upload_imageset.php app_id imageset_name imagelist.txt path_to_images\n");
} else if (!file_exists($argv[3])) {
    die("The file $argv[3] does not exist.\n");
}

// Open the file
$file = fopen($argv[3], 'r');

global $db_host, $db_username, $db_password, $db_name, $db_port;
require_once("../api/settings.php");

echo "$db_host, $db_username, $db_password, $db_name, $db_port";
die();
// Check connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to database: " . $db_name . " on " . $db_host . ":" . $db_port . "<br>";

// Create the image set

while(!feof($file)) {
    $line = fgets($file);
    echo $line;
}



