<?php

// Basic setup
require_once ("helper-functions.php");
require_once("settings.php");

global $vue_url, $db_host, $db_username, $db_password, $db_name, $db_port;
require_once ("settings.php");

header("Access-Control-Allow-Origin: ".$vue_url);
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Get the data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if ($data !== null && isset($data['id']) && $data['id'] !== null
   && isset($data['username']) && $data['username'] !== null ) {

    $username = clean_inputs($data["username"]);
    $id = clean_inputs($data["id"]);

    // open database connection
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);
    // Create sql statement to check if the username already exists when ID is not the same
    $query = "SELECT * FROM users WHERE username = '".$username."' AND id != '".$id."'";

    // Execute the query
    $result = $conn->query($query);
    // Check if the query was successful
    if ($result === false) {
        echo "{ \"error\": \"Database error: " . $conn->error . "\" }";
        exit();
    }
    // Check if the username already exists
    if ($result->num_rows > 0) {
        // Username already exists
        echo "{ \"exists\": true }";
    } else {
        // Username does not exist
        echo "{ \"exists\": false }";
    }

    $conn->close();

} else {
    $response = array(
        "error" => "invalid request"
    );
    // return the response
    echo json_encode($response);
}
