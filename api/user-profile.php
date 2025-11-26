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

    // open database connection
    global $db_host, $db_username, $db_password, $db_name, $db_port;
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

    // Check if the email already exists - this is good
    $query = "SELECT * FROM users WHERE email = '".$email."' AND id = '".$id."'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        // return username, publishable_name, public_name as a JSON object
        $row = $result->fetch_assoc();

        if ($row["username"] == "" || $row["username"] == null) {
            $row["username"] = "not set";
        }
        if ($row["publishable_name"] == "" || $row["publishable_name"] == null) {
            $row["publishable_name"] = "not set";
        }
        if ($row["roles"] == "" || $row["roles"] == null) {
            $row["publishable_name"] = "none";
        }

        $response = array(
                "username" => $row["username"],
                "publishable_name" => $row["publishable_name"],
                "public_name" => $row["public_name"],
                "roles" => $row["roles"]
        );
        // return the response
        echo json_encode($response);
    }

    $conn->close();

} else {
    echo "{ \"error\": \"Invalid request\" }";
}
