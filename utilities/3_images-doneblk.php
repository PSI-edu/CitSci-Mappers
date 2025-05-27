<?php

// Go through the all the images in the database and check if
// they are entirely black. If they are, set done=1.

// load in the settings
global $db_host, $db_username, $db_password, $db_name, $db_port;
require_once("../api/settings.php");

// Check connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to database: " . $db_name . " on " . $db_host . ":" . $db_port . "\n";

// Get all the images
$sql = "SELECT id, file_location FROM images WHERE done=0";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    //output data of each row
    while ($row = $result->fetch_assoc()) {

        // Get the values
        $id = $row["id"];
        $file_location = $row["file_location"];

        echo "Checking image: " . $file_location . "\n";

        if (isImageBlack($file_location)) {
            echo "Image is black. Setting done=1 \n";
            $update_sql = "UPDATE images SET done = 1 WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("i", $id); // "i" specifies the variable type is integer
            if ($stmt->execute()) {
                echo "Image ID " . $id . " is black and marked as done.\n";
            } else {
                echo "Error updating record: " . $stmt->error . "\n";
            }
            $stmt->close();
        } else {
            echo "Image ID " . $id . " is not black.\n";
        }


    }
} else {
    echo "No images found.\n";
}
$conn->close();

function isImageBlack($image_url)
{
    try {
        $image = @imagecreatefromstring(file_get_contents($image_url)); //Use @ to suppress warnings in case of invalid URL or image

        if (!$image) {
            return false; // Invalid image or URL
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $tot = $width * $height;
        $notblk = 0;

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);

                if ($rgb != 0) { // not black
                    $notblk++;
                }
            }
        }
        imagedestroy($image);

        if (($notblk) / $tot <= 0.25)
            return true; // Mostly black
        else
            return false; // Mostly data
    } catch
    (Exception $e) {
        return false; // Handle exceptions (e.g., file not found, invalid image)
    }
}
