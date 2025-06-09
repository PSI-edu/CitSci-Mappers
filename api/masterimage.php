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

// Connect to database
global $db_host, $db_username, $db_password, $db_name, $db_port;
$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch all rows from the 'images' table
$sql = "SELECT x, y, done FROM images";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($rows);
} else {
    header('Content-Type: application/json');
    echo json_encode([]); // Return an empty array if no results are found
}

$conn->close();
