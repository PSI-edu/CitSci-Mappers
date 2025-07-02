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

// What's needed
// Select an image where done = 0
// and where the image hasn't done the image

// Get the data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if ($data !== null &&
    $data["app_id"] !== null && isset($data["app_id"]) &&
    $data["user_id"] !== null && isset($data["user_id"])) {

    $app_id = clean_inputs($data["app_id"]);
    $user_id = clean_inputs($data["user_id"]);

    // Connect to database
    global $db_host, $db_username, $db_password, $db_name, $db_port;
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($app_id == 2) {
        $sql = "SELECT i1.id, i1.file_location, i1.image_set_id
                FROM images i1
                WHERE i1.application_id = ?
                AND i1.done = 0
                AND NOT EXISTS (
                    SELECT 1
                    FROM image_users iu
                    WHERE iu.image_id = i1.id
                    AND iu.user_id = ?
                )
                AND EXISTS (
                    SELECT 1
                    FROM images i2
                    WHERE i2.image_set_id = i1.image_set_id
                    AND i2.id != i1.id
                    AND i2.done = 0
                    AND i2.application_id = ?
                    AND NOT EXISTS (
                        SELECT 1
                        FROM image_users iu2
                        WHERE iu2.image_id = i2.id
                        AND iu2.user_id = ?
                    )
                )
                ORDER BY i1.image_set_id ASC, i1.id ASC
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        // Bind app_id twice for the outer query and the inner EXISTS subquery
        $stmt->bind_param("iiii", $app_id, $user_id, $app_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $image_set_id = $row['image_set_id'];

            // Now select the two rows from the same image_set_id
            $sql_set = "SELECT i.id, i.file_location
                        FROM images i
                        WHERE i.image_set_id = ?
                        AND i.application_id = ?
                        AND i.done = 0
                        AND NOT EXISTS (
                            SELECT 1
                            FROM image_users iu
                            WHERE iu.image_id = i.id
                            AND iu.user_id = ?
                        )
                        ORDER BY i.id ASC
                        LIMIT 2";
            $stmt_set = $conn->prepare($sql_set);
            // Bind image_set_id, app_id, and user_id
            $stmt_set->bind_param("iii", $image_set_id, $app_id, $user_id);
            $stmt_set->execute();
            $result_set = $stmt_set->get_result();

            $response = [];
            while ($row_set = $result_set->fetch_assoc()) {
                $response[] = [
                    'id' => $row_set['id'],
                    'file_location' => $row_set['file_location']
                ];
            }
            echo json_encode($response);
            $stmt_set->close();
        } else {
            echo json_encode(['error' => 'No matching image set found.']);
        }

    } else {
        $sql = "SELECT i.id, i.file_location
        FROM images i
        WHERE 
            i.application_id = ?
        AND i.done = 0
        AND i.id NOT IN (
            SELECT iu.image_id
            FROM image_users iu
            WHERE iu.user_id = ?
        )
        ORDER BY i.id ASC
        LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $app_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response = [
                'id' => $row['id'],
                'file_location' => $row['file_location'] // Decode JSON
            ];
            echo json_encode($response); // Return as JSON
            $stmt->close();
        } else {
            echo json_encode(['error' => 'No matching image found.']); // Return error as JSON
        }
    }

    $conn->close();

} else {
    echo "malformed request";
}

