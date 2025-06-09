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
if ($data !== null && $data['email'] !== null && isset($data['email'])) {
    $email = clean_inputs($data["email"]);

    // open database connection
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

// SQL query to get the user ID
    $sql = "SELECT id FROM users WHERE email = ?";

// Prepare the statement to prevent SQL injection
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

// Bind the parameter
    $stmt->bind_param("s", $email); // "s" indicates a string

// Execute the query
    $stmt->execute();

// Get the result
    $result = $stmt->get_result();

// Check if a row was found
    if ($result->num_rows > 0) {
        // Fetch the row
        $row = $result->fetch_assoc();
        // Get the user ID
        $userId = $row["id"];
        echo $userId;
    } else {
        echo "User not found.";
    }

// Close the statement and connection
    $stmt->close();
    end_apicall($conn);

} else {
    echo "Email address not set";
}

?>