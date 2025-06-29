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

// Get the data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if ($data !== null &&
    ($data['user_id'] !== null && isset($data['user_id']) ) &&
    ($data['task'] !== null && isset($data['task']) ) &&
    ($data['app_id'] !== null && isset($data['app_id'] ))) {

    // Connect to database
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

    //put stuff in variables
    $user_id = $data['user_id'];
    $app_id = $data['app_id'];

    // case statement for the task
    switch ($data['task']) {
        case 'check':
            checkTutorial($conn, $user_id, $app_id);
            exit;
        case 'add':
            addTutorial($conn, $user_id, $app_id);
            exit;

        default:
            http_response_code(400);
            echo "Invalid task";
            exit;
    }

} else {
    http_response_code(400);
    echo "Invalid request";
}

function checkTutorial($conn, $user_id, $app_id) {
    $count = 0;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM tutorials WHERE user_id = ? AND application_id = ? LIMIT 1");
    $stmt->bind_param("ii", $user_id, $app_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "TRUE";
    } else {
        echo "FALSE";
    }
}

function addTutorial($conn, $user_id, $app_id) {
    $stmt = $conn->prepare("INSERT INTO tutorials (user_id, application_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $app_id);

    if ($stmt->execute()) {
        echo "TRUE";
    } else {
        http_response_code(500);
        echo "Error adding tutorial: " . $stmt->error;
    }

    $stmt->close();
}