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

if ($data !== null && $data['name'] !== null && isset($data['name'])) {
    $name = clean_inputs($data["name"]);

    // open database connection
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

// SQL query to get the images
    $sql  = "SELECT id,application_id,done FROM images WHERE name = ?";
    $sql2 = "SELECT type, x1, y1, x2, y2, diameter,CAST(details AS CHAR) as details 
             FROM shared_marks WHERE image_id = ? or image_id = ?";

// Prepare the statement to prevent SQL injection
    $stmt  = $conn->prepare($sql);
    $stmt2 = $conn->prepare($sql2);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

// Process the query
    $stmt->bind_param("s", $name); // "s" indicates a string
    $stmt->execute();
    $result = $stmt->get_result();

// Check if a row was found
    if ($result->num_rows > 0) {

        $finalArr['features'] = 0;
        $finalArr['flows']    = 0;

        // Fetch the rows and return JSON
        $arr = $result->fetch_all(MYSQLI_ASSOC);
        foreach ($arr as $row) {
            if ($row['application_id'] == 3 && $row['done'] == 1) { $finalArr['features'] = 1; }
            if ($row['application_id'] == 4 && $row['done'] == 1) { $finalArr['flows'] = 1; }
        }

        $image_id_0 = $arr[0]['id'];
        $image_id_1 = $arr[1]['id'];

        // Get all the related marks
        $stmt2->bind_param("ii", $image_id_0, $image_id_1);
        $stmt2->execute();
        $result = $stmt2->get_result();

        // Check if marks found
        if ($result->num_rows > 0) {
            $markArr = $result->fetch_all(MYSQLI_ASSOC);
        }
        $finalArr['marks']    = $markArr;

        echo $JSON = json_encode($finalArr);

    } else {
        echo "image $name not found.";
    }

// Close the statement and connection
    $stmt->close();
    end_apicall($conn);

} else {
    echo "Name not set";
}

?>


