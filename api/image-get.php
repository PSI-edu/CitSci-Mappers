<?php

global $vue_url;

require_once ("settings.php");
require_once ("helper-functions.php");

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

// What's needed
// Select an image where done = 0
// and where the image hasn't done the image

// Get the data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if ($data !== null &&
    $data["app_id"] !== null && isset($data["app_id"]) &&
    $data["user_id"] !== null && isset($data["user_id"])) {

    $app_id = clean_inputs($data["app_id"]);
    $user_id = clean_inputs($data["user_id"]);

    // Connect to database
    global $db_host, $db_username, $db_password, $db_name, $db_port;
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT i.id, i.file_location
        FROM images i
        WHERE 
            i.application_id = ?
        AND i.done = 0
        AND i.id NOT IN (
            SELECT iu.image_id
            FROM image_users iu
            WHERE iu.user_id = ?
        )
        ORDER BY i.id ASC
        LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $app_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = [
            'id' => $row['id'],
            'file_location' => $row['file_location'] // Decode JSON
        ];
        echo json_encode($response); // Return as JSON
    } else {
        echo json_encode(['error' => 'No matching image found.']); // Return error as JSON
    }

    $stmt->close();
    $conn->close();

} else {
    echo "malformed request";
}

