<?php

// Basic setup
require_once ("helper-functions.php");

global $vue_url, $db_host, $db_username, $db_password, $db_name, $db_port;
require_once ("settings.php");

header("Access-Control-Allow-Origin: ".$vue_url);
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');


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

        //increment the image count
        $sql = "UPDATE images SET count = count + 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $image_id);
        if (!$stmt->execute()) {
            die("Error: " . $sql . "<br>" . $conn->error);
        }
        $stmt->close();

        // if the count is greater than 10, set the image to done
        $sql = "UPDATE images SET done = 1 WHERE id = ? AND count > 10";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $image_id);
        if (!$stmt->execute()) {
            die("Error: " . $sql . "<br>" . $conn->error);
        }
        $stmt->close();

    } else {
        die("Error:User ID or Image ID not set");
    }

// check what the app_id is and then call a related routine
if ($data !== null && $data['app_id'] !== null && isset($data['app_id'])) {
    $app_id = clean_inputs($data["app_id"]);
    // use a switch statement to determine which app_id is being used
    switch ($app_id) {
        case 2:
            submit_mars_mosaic($data, $last_id);
            break;
        case 3:
            submit_moon_activity_1($data, $last_id);
            break;
        default:
            echo "error: App not found";
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

function submit_moon_activity_1($data, $user_image_id) {
    global $conn;

    print_r($data["drawings"]);

    // set values that get reused
    $app_id = clean_inputs($data["app_id"]);
    $user_id = clean_inputs($data["user_id"]);
    $image_id = clean_inputs($data["image_id"]);

    // If there are marks, step through the json and save the drawings in the marks table
    if (!isset($data["drawings"]) || $data["drawings"] == null) {
        die("This image had no marks to record;");
    }
    foreach ($data["drawings"] as $drawing) {
        // Get the data

        $type = clean_inputs($drawing["type"]);

        switch ($type) {
            case "circle":
                $type = "crater";
                $x1 = clean_inputs($drawing["data"]["x"]);
                $y1 = clean_inputs($drawing["data"]["y"]);
                $diameter = 2 * clean_inputs($drawing["data"]["radius"]);

                //setup the mysql to insert into the marks table
                $sql = "INSERT INTO marks (application_id, image_user_id, user_id, image_id, type, x1, y1, diameter) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiiisiid", $app_id, $user_image_id, $user_id, $image_id, $type, $x1, $y1, $diameter);
                if (!$stmt->execute()) {
                    die("Error: " . $sql . "<br>" . $conn->error);
                }
                break;
            case "dot":
                $type = "rock";
                $x1 = clean_inputs($drawing["data"]["x"]);
                $y1 = clean_inputs($drawing["data"]["y"]);

                //setup the mysql to insert into the marks table
                $sql = "INSERT INTO marks (application_id, image_user_id, user_id, image_id, type, x1, y1) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiiisii", $app_id, $user_image_id, $user_id, $image_id, $type, $x1, $y1);
                if (!$stmt->execute()) {
                    die("Error: " . $sql . "<br>" . $conn->error);
                }
                break;
            case "line":
                $type = "boulder";
                // x1 is always the lower x value
                $x1 = clean_inputs($drawing["data"]["x1"]);
                $x2 = clean_inputs($drawing["data"]["x2"]);
                $y1 = clean_inputs($drawing["data"]["y1"]);
                $y2 = clean_inputs($drawing["data"]["y2"]);
                if ($x1 > $x2) {
                    $temp = $x1;
                    $x1 = $x2;
                    $x2 = $temp;
                    $temp = $y1;
                    $y1 = $y2;
                    $y2 = $temp;
                }

                //setup the mysql to insert into the marks table
                $sql = "INSERT INTO marks (application_id, image_user_id, user_id, image_id, type, x1, y1, x2, y2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiiisiiii", $app_id, $user_image_id, $user_id, $image_id, $type, $x1, $y1, $x2, $y2);
                break;
            default:
                die("Error: Unknown type");
        }
    }

}