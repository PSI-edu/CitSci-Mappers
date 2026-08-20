<?php

// Basic setup
require_once ("helper-functions.php");
require_once("settings.php");


global $vue_url, $db_host, $db_username, $db_password, $db_name, $db_port;

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

if ($data !== null && $data['app_id'] !== null && isset($data['app_id']) && $data['name'] !== null && isset($data['name'])) {
    $app_id = clean_inputs($data["application_id"]);
    $name   = clean_inputs($data["name"])+"%";

    // open database connection
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

// SQL queries to get the master images and then the images
    $sql  = "SELECT id FROM image_sets WHERE  name like ? AND application_id = ? LIMIT 1";
    $sql2 = "SELECT id, name, x, y, done FROM images WHERE image_sets_id = ? ";

// Prepare the statement to prevent SQL injection
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

// Process the query
    $stmt->bind_param("si", $name, $app_id); // "s" indicates a string
    $stmt->execute();
    $result = $stmt->get_result();

// Check if a row was found
    if ($result->num_rows > 0) {
        $master_id = $result->fetch_all(MYSQLI_ASSOC);

        // Get the images
        $stmt2 = $conn->prepare($sql2);
        if ($stmt2 === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt2->bind_param("i", $master_id[0]['id']);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        echo json_encode($result2->fetch_all(MYSQLI_ASSOC));

    } else {
        echo "Not found.";
    }

// Close the statement and connection
    $stmt->close();
    end_apicall($conn);

} else {
    echo "Name mis-set: ";
    print_r($data);
}

?>


