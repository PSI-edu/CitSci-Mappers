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

// prepare statement ahead of time
$sql_master = "select name from image_sets where id = ?";
$stmt_master = $conn->prepare($sql_master);


// Get all the master images where 1 or more images are done

$sql = "select image_set_id from images where application_id=3 and done = 1 group by image_set_id ";
$result = $conn->query($sql);

while ($row = $result->fetch_object()) {
    $image_set_id = $row->image_set_id;

    // Get master image info
    $stmt_master->bind_param("i", $image_set_id);
    $stmt_master->execute();
    $result_master = $stmt_master->get_result();
    $master_image = $result_master->fetch_object();
    $stmt_master->close();

    // Get all subimages that are done
    $sql_subimages = "select * from images where image_set_id = ? and done = 1";
    $stmt_subimages = $conn->prepare($sql_subimages);
    $stmt_subimages->bind_param("i", $image_set_id);
    $stmt_subimages->execute();
    $result_subimages = $stmt_subimages->get_result();

    while ($subimage = $result_subimages->fetch_object()) {
        // Get all marks for this subimage
        $sql_marks = "select * from marks where image_id = ?";
        $stmt_marks = $conn->prepare($sql_marks);
        $stmt_marks->bind_param("i", $subimage->id);
        $stmt_marks->execute();
        $result_marks = $stmt_marks->get_result();

        while ($mark = $result_marks->fetch_object()) {
            // Convert mark coordinates to master image coordinates
            $scale_x = $master_image->width / $subimage->width;
            $scale_y = $master_image->height / $subimage->height;

            $master_x = $mark->x1 * $scale_x + $subimage->offset_x;
            $master_y = $mark->y1 * $scale_y + $subimage->offset_y;
            $master_diameter = $mark->diameter * (($scale_x + scale_y) / 2);

            // Output CSV line
            echo "{$master_image->id},{$master_image->name},{$subimage->name},{$mark->type},{$master_x},{$master_y},{$master_diameter}\n";
        }

        $stmt_marks->close();
    }

    $stmt_subimages->close();
}
$stmt_master->close();

// output a csv with master_image id, name, subimage-name, mark type, x, y, diameter in master image coordinates



$conn->close();
