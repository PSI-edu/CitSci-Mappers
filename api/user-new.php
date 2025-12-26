<?php
global $vue_url, $auth0_domain, $localhost_dev, $auth0_api_secret;

require_once("settings.php");
require_once("helper-functions.php");

header("Access-Control-Allow-Origin: " . $vue_url);
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// check for authentication key
    $headers = getallheaders();
    $authorizationHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// Expected format: "Bearer auth0_api_key_that_is_32charactersOrMore
    if (strpos($authorizationHeader, 'Bearer ') === 0) {
        $receivedSecret = substr($authorizationHeader, 7);
        if ($receivedSecret !== $auth0_api_secret) {
            http_response_code(403); // Forbidden
            echo "Unauthorized access: Invalid secret.";
            exit(); // Stop execution
        }
    } else {
        http_response_code(401); // Unauthorized
        echo "Unauthorized access: Missing Authorization header.";
        exit(); // Stop execution
    }


// Get the data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);
print_r($data);
if ($data !== null && $data['email'] !== null && isset($data['email'])) {
    $email = clean_inputs($data["email"]);

    // open database connection
    global $db_host, $db_username, $db_password, $db_name, $db_port;
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

    $query = "INSERT INTO users (email) VALUES ('" . $email . "')";
    if ($conn->query($query) === TRUE) {
        $last_id = $conn->insert_id;
        echo "$last_id";
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }

    $conn->close();

} else {
    echo "Email address not set correctly. ", $data;
}

?>
