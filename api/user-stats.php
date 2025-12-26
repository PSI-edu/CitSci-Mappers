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


// Select the applications from the database, getting id, title, icon_url, and active values.

    // Prepare the SQL statement to select specific columns
    $sql = "SELECT 
                a.id, 
                a.title, 
                a.icon_url, 
                a.active,
                COUNT(DISTINCT i.id) AS total_images,
                SUM(CASE WHEN i.done = 0 THEN 1 ELSE 0 END) AS images_remaining,
                (SELECT COUNT(*) FROM image_users iu 
                 WHERE iu.application_id = a.id AND iu.user_id = ?) AS user_count,
                (SELECT COUNT(*) FROM images i2 
                 WHERE i2.application_id = a.id 
                 AND i2.done = 0 
                 AND NOT EXISTS (
                     SELECT 1 FROM image_users iu2 
                     WHERE iu2.image_id = i2.id 
                     AND iu2.user_id = ?
                 )) AS available_to_user
            FROM applications a
            LEFT JOIN images i ON a.id = i.application_id
            WHERE a.id > 1
            GROUP BY a.id";

    if ($stmt = $conn->prepare($sql)) {
        // Bind the user's ID to the subquery placeholder
        $stmt->bind_param("ii", $id, $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response = $result->fetch_all(MYSQLI_ASSOC);

            // Set header to JSON so Vue understands the response type

            echo json_encode($response);
        } else {
            echo "{ \"error\": \"No stats found for this user\" }";
        }
        $stmt->close();
    } else {
        echo "{ \"error\": \"Database query failed\" }";
    }

    $conn->close();

} else {
    echo "{ \"error\": \"Invalid request\" }";
}
