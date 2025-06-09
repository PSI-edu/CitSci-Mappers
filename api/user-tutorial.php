<?php

// Basic setup
require_once ("helper-functions.php");

global $vue_url, $db_host, $db_username, $db_password, $db_name, $db_port;
require_once ("settings.php");

header("Access-Control-Allow-Origin: ".$vue_url);
header('Access-Control-Allow-Methods: POST, OPTIONS');
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Validate the JWT token
require_once("auth-check.php");


// Connect to database
$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

// Get the data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// if the user_id and image_id are set, add a recortd to the image_users table
if ($data !== null &&
    ($data['user_id'] !== null && isset($data['user_id']) ) &&
    ($data['task'] !== null && isset($data['task']) ) &&
    ($data['app_id'] !== null && isset($data['app_id'] ))) {

    echo "TRUE";
} else {
    echo "FALSE";
}