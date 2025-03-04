<?php

// Basic setup
require_once ("helper-functions.php");

global $vue_url, $db_host, $db_username, $db_password, $db_name, $db_port;
require_once ("settings.php");

header("Access-Control-Allow-Origin: ".$vue_url);
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

echo $vue_url;

// Connect to database
$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

// Get the data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// if the user_id and image_id are set, add a recortd to the image_users table
if ($data !== null &&
    ($data['user_id'] !== null && isset($data['user_id']) ) &&
    ($data['image_id'] !== null && isset($data['image_id']) ) &&
    ($data['app_id'] !== null && isset($data['app_id'] ))) {

        // Get the values
        $user_id = clean_inputs($data["user_id"]);
        $image_id = clean_inputs($data["image_id"]);
        $app_id = clean_inputs($data["app_id"]);

        // Record that the user submitted the image
        $sql = "INSERT INTO image_users (user_id, image_id, application_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $image_id, $app_id);
        if (!$stmt->execute()) {
            die("Error: " . $sql . "<br>" . $conn->error);
        }
        $stmt->close();
        // get the user image id
        $last_id = $conn->insert_id;
    } else {
        die("Error:User ID or Image ID not set");
    }

// check what the app_id is and then call a related routine
if ($data !== null && $data['app_id'] !== null && isset($data['app_id'])) {
    $app_id = clean_inputs($data["app_id"]);
    if ($app_id == 1) {
        // Mars Mosaic App
        submit_mars_mosaic($data, $last_id);
    }
} else {
    echo "App ID not set";
}

// End API call
end_apicall($conn);

//set up the mars mosaic function
function submit_mars_mosaic($data, $user_image_id) {
    global $conn;
    // Get the data
    if ($data['response'] !== null && isset($data['response']) ) {

        // Get the values
        $app_id = clean_inputs($data["app_id"]);
        $user_id = clean_inputs($data["user_id"]);
        $image_id = clean_inputs($data["image_id"]);
        $type = clean_inputs($data["response"]);

        // Submit the record into the marks table
        $sql = "INSERT INTO marks (application_id, image_user_id, user_id, image_id, type) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiis", $app_id, $user_image_id, $user_id, $image_id, $type);
        if (!$stmt->execute()) {
            die("Error: " . $sql . "<br>" . $conn->error);
        }
        $stmt->close();
    } else {
        die("Error: Response not set");
    }
}