<?php
global $vue_url;

require_once ("settings.php");
require_once ("helper-functions.php");

header("Access-Control-Allow-Origin: ".$vue_url);
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');


// Get the data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);
if ($data !== null && $data['email'] !== null && isset($data['email'])) {
    $apiKey = clean_inputs($data["email"]);

    // open database connection
    global $db_host, $db_username, $db_password, $db_name, $db_port;
    require_once("settings.php");
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

    $query = "INSERT INTO users (email) VALUES ('".$apiKey."')";
    if ($conn->query($query) === TRUE) {
        echo "New record $apiKey created successfully";
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }

    $conn->close();

} else {
    echo "Email address not set";
}

?>