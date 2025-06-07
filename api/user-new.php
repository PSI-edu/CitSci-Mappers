<?php
global $vue_url, $auth0_domain, $localhost_dev, $auth0_api_secret;

require_once ("settings.php");
require_once ("helper-functions.php");

if ($localhost_dev) {
    // If localhost, set the vue_url to the local development server
    header("Access-Control-Allow-Origin: ".$vue_url);
} else {
    // Otherwise, use the Auth0 domain
    header("Access-Control-Allow-Origin: $auth0_domain ");
}

header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// --- Security Check: Verify the Authorization header ---
$headers = getallheaders();
$authorizationHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// Expected format: "Bearer your-super-secret-auth0-api-key..."
if (strpos($authorizationHeader, 'Bearer ') === 0) {
    $receivedSecret = substr($authorizationHeader, 7); // Get the part after "Bearer "
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
if ($data !== null && $data['email'] !== null && isset($data['email'])) {
    $email = clean_inputs($data["email"]);

    // open database connection
    global $db_host, $db_username, $db_password, $db_name, $db_port;
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

    $query = "INSERT INTO users (email) VALUES ('".$email."')";
    if ($conn->query($query) === TRUE) {
        $last_id = $conn->insert_id;
        echo "$last_id";
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }

    $conn->close();

} else {
    echo "Email address not set correctly. ", $_POST["email"];
}

?>