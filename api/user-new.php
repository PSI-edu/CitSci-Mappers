<?php
global $vue_url;

require_once ("settings.php");
require_once ("helper-functions.php");

header("Access-Control-Allow-Origin: ".$vue_url);
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');


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
    echo "Email address not set";
}

?>