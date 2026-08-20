<?php

// Basic setup
require_once ("helper-functions.php");
require_once("settings.php");

global $vue_url, $db_host, $db_username, $db_password, $db_name, $db_port;

// Open database connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

// STEP 1: GET THE IMAGES THAT ARE DONE AND HAVE ROCKS
$sql = "SELECT DISTINCT i.*
        FROM images i
        JOIN marks m ON i.id = m.image_id
        WHERE i.application_id = 3 AND i.done = 1
        AND m.confirmed IS NULL AND m.type = 'rock';";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}
$stmt->close();

// STEP 2: FOR EACH IMAGE, GET ALL THE ROCKS
$sql = "SELECT id, x1, y1, user_id, confirmed
        FROM marks
        WHERE image_id = ? AND type='rock' AND confirmed IS NULL
        ORDER BY x1, y1;";
$stmt = $conn->prepare($sql);

$maxDiff = 5;

$sql_shared = "INSERT INTO shared_marks 
              (image_id, application_id, x1, y1, confidence, type, details)
              VALUES
              (?, 3, ?, ?, ?, 'rock', ?)";
$stmt_shared = $conn->prepare($sql_shared);

foreach ($images as $image) {
    $stmt->bind_param("i", $image['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $marks = $result->fetch_all(MYSQLI_ASSOC);
        $confirmed = [];

        foreach ($marks as $matchThis) {
            if (!in_array($matchThis['id'], $confirmed)) {

                // Match rocks within 5 pixels
                $matched = findRockMatch($marks, $matchThis, $maxDiff);
                $N = count($matched);

                if ($N > 1) {
                    $aveRock = findRockMatchesAve($matched);
                    $matchedCheck = findRockMatch($marks, $aveRock, $maxDiff);
                    $aveRockCheck = findRockMatchesAve($matchedCheck);
                    $stdDev = findRockStdDev($matchedCheck, $aveRockCheck);

                    $N = count($matchedCheck);
                    // Confidence calculated from 2D spatial distance standard deviation
                    $confidence = sqrt(pow($stdDev['x1'], 2) + pow($stdDev['y1'], 2));
                    $details = '{"N":' . $N . ',"x1_stdev":' . $stdDev['x1'] . ',"y1_stdev":' . $stdDev['y1'] . '}';

                    $stmt_shared->bind_param("iidds", $image['id'], $aveRockCheck['x1'], $aveRockCheck['y1'], $confidence, $details);
                    $stmt_shared->execute();
                    $last_id = $conn->insert_id;

                    // Mark group as confirmed
                    foreach ($matchedCheck as $matchedRock) {
                        $confirmed[] = $matchedRock['id'];
                        $sql_update = "UPDATE marks
                                       SET confirmed = 1, shared_mark_id = $last_id
                                       WHERE id = " . $matchedRock['id'] . ";";
                        $conn->query($sql_update);
                    }
                } else {
                    $confirmed[] = $matchThis['id'];
                    $sql_update = "UPDATE marks SET confirmed = -1 WHERE id = " . $matchThis['id'] . ";";
                    $conn->query($sql_update);
                }
            }
        }
    }
}

$stmt->close();
$stmt_shared->close();
$conn->close();

// FUNCTIONS

function printRock($rock) {
    echo "id: " . $rock['id'] . " position: (" . $rock['x1'] . ", " . $rock['y1'] . ") confirmed: " . $rock['confirmed'] . "<br/>";
}

function findRockMatch($matchArr, $toMatch, $maxDiff) {
    $matched = [];
    foreach ($matchArr as $toCheck) {
        $xDist = $toCheck['x1'] - $toMatch['x1'];
        $yDist = $toCheck['y1'] - $toMatch['y1'];
        $totDist = sqrt($xDist * $xDist + $yDist * $yDist);

        if ($totDist < $maxDiff) {
            $matched[] = $toCheck;
        }
    }
    return $matched;
}

function findRockMatchesAve($matched) {
    $ave = ['x1' => 0, 'y1' => 0];
    $N = count($matched);

    foreach ($matched as $rock) {
        $ave['x1'] += $rock['x1'];
        $ave['y1'] += $rock['y1'];
    }

    if ($N > 0) {
        $ave['x1'] /= $N;
        $ave['y1'] /= $N;
    }

    return $ave;
}

function findRockStdDev($matchArr, $aveRock) {
    $std = ['x1' => 0, 'y1' => 0];
    $N = count($matchArr);

    foreach ($matchArr as $match) {
        $std['x1'] += pow($match['x1'] - $aveRock['x1'], 2);
        $std['y1'] += pow($match['y1'] - $aveRock['y1'], 2);
    }

    if ($N > 0) {
        $std['x1'] = sqrt($std['x1'] / $N);
        $std['y1'] = sqrt($std['y1'] / $N);
    }

    return $std;
}