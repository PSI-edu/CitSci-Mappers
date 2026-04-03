<?php

// Basic setup
require_once ("helper-functions.php");
require_once("settings.php");

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

// Get the data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);
if ($data !== null && $data['email'] !== null && isset($data['email'])) {
    $email = clean_inputs($data["email"]);
    $id = clean_inputs($data["id"]);
    $username = clean_inputs($data["username"]);
    $publishable_name = !empty($data["publishable_name"]) ? clean_inputs($data["publishable_name"]) : NULL;
    $scistarter_email = !empty($data["scistarter_email"]) ? clean_inputs($data["scistarter_email"]) : NULL;



    // open database connection
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

// Prepare the statement
    $stmt = $conn->prepare("UPDATE users SET username = ?, publishable_name = ?, scistarter_email = ? WHERE email = ? AND id = ?");

// "ssssi" means: string, string, string, string, integer
    $stmt->bind_param("ssssi", $username, $publishable_name, $scistarter_email, $email, $id);

// Execute
    if ($stmt->execute()) {
        echo json_encode(array("success" => true));
    } else {
        echo json_encode(array("success" => false, "error" => $stmt->error));
    }

    $stmt->close();
    $conn->close();

} else {
    echo "{ \"success\": false }";
}
