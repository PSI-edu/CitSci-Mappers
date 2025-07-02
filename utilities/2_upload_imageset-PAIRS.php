<?php

// Check for correct number of arguments
if ($argc !== 4) {
    echo "Usage: php " . $argv[0] . " <app_id> <path> <filename.txt>\n";
    exit(1);
}

// Get command-line arguments
$filename = $argv[1];
$appId = (int)$argv[2];
$path = rtrim($argv[3], '/') . '/'; // Ensure path ends with a slash but not 2 slashes


// Validate app_id
if (!is_numeric($appId) || $appId <= 0) {
    echo "Error: app_id must be a positive integer.\n";
    exit(1);
}

global $db_host, $db_username, $db_password, $db_name, $db_port;
require_once("../api/settings.php");

// Check connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to database: " . $db_name . " on " . $db_host . ":" . $db_port . "\n";

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connected successfully.\n";

// Open the input file
$file = fopen($filename, "r");

if ($file === false) {
    echo "Error: Could not open file '{$filename}'. Please ensure the file exists and is readable.\n";
    $conn->close();
    exit(1);
}

echo "File '{$filename}' opened successfully.\n";

$lineNumber = 0;
while (($line = fgets($file)) !== false) {
    $lineNumber++;
    // Skip empty lines
    $line = trim($line);
    if (empty($line)) {
        continue;
    }

    // Split the line into columns
    $columns = explode("\t", $line); // Assuming tab-separated values. Change to ',' if comma-separated.

    if (count($columns) < 3) {
        echo "Warning: Skipping line {$lineNumber} due to insufficient columns. Expected 3, got " . count($columns) . ".\n";
        continue;
    }

    $colA = trim($columns[0]);
    $colB = trim($columns[1]);
    $colC = trim($columns[2]);

    // Construct image_set_name
    $imageSetName = $colB . " x " . $colC;

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Insert into image_sets table
        $stmtImageSets = $conn->prepare("INSERT INTO image_sets (name, application_id) VALUES (?, ?)");
        if ($stmtImageSets === false) {
            throw new Exception("Prepare statement for image_sets failed: " . $conn->error);
        }
        $stmtImageSets->bind_param("si", $imageSetName, $appId);
        if (!$stmtImageSets->execute()) {
            throw new Exception("Insert into image_sets failed for line {$lineNumber} ('{$imageSetName}'): " . $stmtImageSets->error);
        }
        $imageSetId = $conn->insert_id;
        $stmtImageSets->close();

        echo "Inserted image_set: '{$imageSetName}' (ID: {$imageSetId}) for line {$lineNumber}.\n";

        // Insert first row into images table
        $stmtImages1 = $conn->prepare("INSERT INTO images (image_set_id, application_id, name, file_location) VALUES (?, ?, ?, ?)");
        if ($stmtImages1 === false) {
            throw new Exception("Prepare statement for images (row 1) failed: " . $conn->error);
        }
        $fileLocationB = $path . $colB;
        $stmtImages1->bind_param("iiss", $imageSetId, $appId, $colB, $fileLocationB);
        if (!$stmtImages1->execute()) {
            throw new Exception("Insert into images (row 1) failed for line {$lineNumber} ('{$colA}'): " . $stmtImages1->error);
        }
        $stmtImages1->close();
        echo "Inserted image: '{$colB}' (file: {$fileLocationB}) into images for line {$lineNumber}.\n";

        // Insert second row into images table
        $stmtImages2 = $conn->prepare("INSERT INTO images (image_set_id, application_id, name, file_location) VALUES (?, ?, ?, ?)");
        if ($stmtImages2 === false) {
            throw new Exception("Prepare statement for images (row 2) failed: " . $conn->error);
        }
        $fileLocationC = $path . $colC;
        $stmtImages2->bind_param("iiss", $imageSetId, $appId, $colC, $fileLocationC);
        if (!$stmtImages2->execute()) {
            throw new Exception("Insert into images (row 2) failed for line {$lineNumber} ('{$colC}'): " . $stmtImages2->error);
        }
        $stmtImages2->close();
        echo "Inserted image: '{$colC}' (file: {$fileLocationC}) into images for line {$lineNumber}.\n";

        // Commit the transaction if all inserts were successful
        $conn->commit();
        echo "Transaction committed for line {$lineNumber}.\n\n";

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo "Error processing line {$lineNumber}: " . $e->getMessage() . ". Rolling back transaction.\n\n";
    }
}

// Close the file and database connection
fclose($file);
$conn->close();

echo "Script finished.\n";

?>