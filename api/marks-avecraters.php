<?php

// Basic setup
require_once ("helper-functions.php");
require_once("settings.php");

global $vue_url, $db_host, $db_username, $db_password, $db_name, $db_port;
require_once ("settings.php");

// open database connection
global $db_host, $db_username, $db_password, $db_name, $db_port;
$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

// For Images that are Done find the averaged craters

// This will eventually run whenever an image is marked as done, but for now we will just run it manually. Let's get all the images that are done

// STEP 1: GET THE IMAGES THAT ARE DONE AND HAVE CRATERS
$sql = "SELECT DISTINCT i.*
        FROM images i
        JOIN marks m ON i.id = m.image_id
        WHERE i.application_id = 3 AND i.done = 1
        and m.confirmed IS NULL AND m.type = 'crater' limit 1;";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$Nimages = mysqli_num_rows($result);
$stmt->close();

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}

$Nimages = count($images);

// How many images is that?
//echo " Checksum $Nimages <br>";

// STEP 2: FOR EACH IMAGE, GET ALL THE CRATERS IN THAT IMAGE
$sql = "SELECT id, x1, y1, diameter, user_id, confirmed
        FROM marks
        WHERE image_id = ? AND type='crater' AND confirmed IS NULL
        ORDER BY x1, y1;";
$stmt = $conn->prepare($sql);
$marks = [];
$matched = [];
$maxDiff=10;
$maxDiffAve=2/3*$maxDiff;

$sql_shared ="INSERT INTO shared_marks 
              (image_id, application_id, x1, y1, diameter, confidence, type, details)
              VALUES
              (?, 3, ?,?,?,?, 'crater', ?)";
$stmt_shared = $conn->prepare($sql_shared);

$Nimages=0;
foreach($images as $image) {
    //echo $Nimages." = image_id ".$image['id'];
    $Nimages++;

    // Setup that Image
    $stmt->bind_param("i", $image['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Put each mark into the array
        $marks = $result->fetch_all(MYSQLI_ASSOC);

        $Ncraters = count($marks);
        //echo ", contains $Ncraters craters<br>";

        // find anything that is roughly the same size and same place (big error)
        // REMEMBER: These are sorted by X and then Y

        $confirmed=[];
        foreach ($marks as $matchThis) {
            // When a crater is confirmed, it goes in the confirmed array and should then be ignored
            // Only check things NOT in the confirmed array
            if (!in_array($matchThis['id'], $confirmed)) {
                // Look for things within maxDiff of what you're matching
                $matched = findCraterMatch($marks, $matchThis, $maxDiff);
                $N = count($matched);

                // if N>1, find average and look around the average to make sure nothing is missed
                if ($N > 1) {
                    $aveCrater = findCraterMatchesAve($matched);
                    $matchedCheck = findCraterMatch($marks, $aveCrater, $maxDiff);
                    $aveCraterCheck = findCraterMatchesAve($matchedCheck);
                    $stdDev = findCraterStdDev($matchedCheck, $aveCraterCheck);

                    // Insert the shared mark into the database and get its id number
                    $N = count($matchedCheck);
                    $confidence = sqrt($stdDev['x1']*$stdDev['x1']+$stdDev['y1']*$stdDev['y1']+$stdDev['diameter']*$stdDev['diameter']);
                    $details = '{"N":'.$N.',"x1_stdev":'.$stdDev['x1'].',"y1_stdev":'.$stdDev['y1'].',"diameter_stdev":'.$stdDev['diameter'].'}';

                    $stmt_shared->bind_param("iiidds", $image['id'], $aveCrater['x1'], $aveCrater['y1'], $aveCrater['diameter'], $confidence, $details);
                    $stmt_shared->execute();
                    $last_id = $conn->insert_id;

                    // and mark everything in this group as confirmed
                    foreach ($matchedCheck as $matchedCrater) {
                        $confirmed[] = $matchedCrater['id'];
                        $sql_update ="UPDATE marks
                                                SET confirmed = 1, shared_mark_id = $last_id
                                                WHERE id = ".$matchedCrater['id'].";";
                        $conn->query($sql_update);
                    }

                    // if N<=1, flag as unconfirmed
                } else {
                    // Remove it from list of things to check
                    $confirmed[] = $matchThis;
                    // Mark it as unconfirmed in the database
                    $sql_update = "UPDATE marks SET confirmed = -1 WHERE id = ".$matchThis['id'].";";
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
function printCrater($crater) {
    echo "id: ".$crater['id']." position: (".$crater['x1'].", ".$crater['y1'].") diameter: ".$crater['diameter']." confirmed: ".$crater['confirmed']."<br/>";
}

function findCraterMatch($matchArr, $toMatch, $maxDiff) {

    // Array to put stuff in
    $matched = [];

    foreach ($matchArr as $toCheck) {
        $xDist = abs($toCheck['x1'] - $toMatch['x1']);
        $yDist = abs($toCheck['y1'] - $toMatch['y1']);

        $totDist = sqrt($xDist*$xDist+$yDist*$yDist);
        $diaDist = abs($toCheck['diameter'] - $toMatch['diameter']);

        // Does it match?
        if ($totDist < $maxDiff && $diaDist < $maxDiff) {
            // Add the match to the matched array
            $matched[] = $toCheck;
        }
    }
    return $matched;

}

function findCraterMatchesAve($matched) {
    $ave = [];
    $ave['x1'] = 0;
    $ave['y1'] = 0;
    $ave['diameter'] = 0;
    $N = 0;

    foreach($matched as $crater) {
        $ave['x1'] += $crater['x1'];
        $ave['y1'] += $crater['y1'];
        $ave['diameter'] += $crater['diameter'];
        $N++;
    }

    $ave['x1'] /= $N;
    $ave['y1'] /= $N;
    $ave['diameter'] /= $N;

    return $ave;
}

function findCraterStdDev($matchArr, $aveCrater) {

    // StdDev = sqrt( 1/N sum (X - aveX)^2 )

    $std = [];
    $N = 0;

    foreach($matchArr as $match) {
        $std['x1'] += pow($match['x1'] - $aveCrater['x1'], 2);
        $std['y1'] += pow($match['y1'] - $aveCrater['y1'], 2);
        $std['diameter'] += pow($match['diameter'] - $aveCrater['diameter'], 2);
        $N++;
    }

    $std['x1'] /= $N;
    $std['y1'] /= $N;
    $std['diameter'] /= $N;

    $std['x1'] = sqrt($std['x1']);
    $std['y1'] = sqrt($std['y1']);
    $std['diameter'] = sqrt($std['diameter']);

    return $std;

}
