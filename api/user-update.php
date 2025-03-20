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
if ($data !== null && $data['email'] !== null && isset($data['email'])) {
    $email = clean_inputs($data["email"]);
    $id = clean_inputs($data["id"]);
    $username = clean_inputs($data["username"]);
    $publishable_name = clean_inputs($data["publishable_name"]);

    // open database connection
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);
    // Prepare to update the username and publishable name where the email and id provided match
    $query = "UPDATE users SET username = '".$username."', publishable_name = '".$publishable_name."' WHERE email = '".$email."' AND id = '".$id."'";
    // Execute the query
    $result = $conn->query($query);
    // Check if the query was successful
    if ($result === FALSE) {
        echo json_encode(array("success" => false));
    } else {
        echo json_encode(array("success" => true));
    }

    $conn->close();

} else {
    echo "{ \"success\": false }";
}
