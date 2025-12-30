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
        AND m.type = 'crater' ;";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$images = [];
while ($row = $result->fetch_object()) {
    $images[] = $row->id;
}

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

foreach($images as $image) {

    // Setup that Image
    echo "Now doing: ".$image."<br>";
    $stmt->bind_param("i", $image);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {

        // Put each mark into the array
        $marks = $result->fetch_all(MYSQLI_ASSOC);

        // find anything that is roughly the same size and same place (big error)
        // REMEMBER: These are sorted by X and then Y
        //

        $confirmed=[];
        foreach ($marks as $matchThis) {

            // Make sure this crater isn't already confirmed, then look for matches
            if (!in_array($matchThis['id'], $confirmed) ) $flag=TRUE;
            else $flag=FALSE;

            if($flag) {
                // Look for things within maxDiff of  what you're matching
                $matched = findCraterMatch($marks, $matchThis, $maxDiff);
                $N = count($matched);

                // If N>1, refine the center and make sure nothing was missed.
                if ($N>1) {
                    $aveCrater = findCraterMatchesAve($matched);
                    $N = count($matched);
                    // Refine things - recheck using the averaged value and maxDiffAve
                    $matchedCheck = findCraterMatch($marks, $aveCrater, $maxDiffAve);

                    $aveCraterCheck = findCraterMatchesAve($matchedCheck);
                    $N = count($matchedCheck);
                    $stdDev = findCraterStdDev($matchedCheck, $aveCraterCheck);
                }

                // is our original crater in that set of marks if not, flag as no matches)
                if ($N<=1) {
                    echo "NOT MATCHED - MUST FLAG<br>";
                    $sql_update ="UPDATE marks
                                              SET confirmed = -1
                                              WHERE id = ".$matchThis['id'].";";
                    echo $sql_update."<br>";

                    if ($conn->query($sql_update) === TRUE) {
                        echo "ok<br>";
                    }

                    // Remove from list of things to match
                    $confirmed[] = $matchThis['id'];

                } else {
                    echo "MATCHED<br>";
                    // ok, so matches were found and averaged Crater was created. Let's stick into Database
                    $confidence = sqrt($stdDev['x1']*$stdDev['x1']+$stdDev['y1']*$stdDev['y1']+$stdDev['diameter']*$stdDev['diameter']);
                    $details = '{"N":'.$N.',"x1_stdev":'.$stdDev['x1'].',"y1_stdev":'.$stdDev['y1'].',"diameter_stdev":'.$stdDev['diameter'].'}';

                    $stmt_shared->bind_param("iiiddb", $image, $aveCrater['x1'], $aveCrater['y1'], $aveCrater['diameter'], $confidence, $details);
                    $stmt_shared->execute();
                    $last_id = $conn->insert_id;

                    // Now let's update all the members
                    foreach($matchedCheck as $doneCrater) {
                        // add shared_mark_id and update confirmed
                        $sql_update ="UPDATE marks
                                                      SET confirmed = 1, shared_mark_id = $last_id
                                                      WHERE id = ".$doneCrater['id'].";";
                        if ($conn->query($sql_update) === TRUE) { echo "ok<br>"; }

                        // Make sure it doesn't get redone
                        $confirmed[] = $doneCrater['id'];

                    }
                }
            }
            $matched=[];
            $pop = array_search($matchThis, $marks);
            unset($marks[$pop]);
            $N = 0;
        }


    } else {
        echo "WHY NO MARKS??<br>";
    }

    // print_r($marks);
    $marks = [];
    $matched = [];

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
        // Remember the marks are sorted by X and abort loop as needed
        $xDist = abs($toCheck['x1'] - $toMatch['x1']);
        if ($xDist > $maxDiff) break;

        // Check other facets
        $yDist = abs($toCheck['y1'] - $toMatch['y1']);
        $diaDist = abs($toCheck['diameter'] - $toMatch['diameter']);

        //what max diameter should I use
        if ($toMatch['diameter']*0.2 > $maxDiff) $maxDiaDiff = $maxDiff;
        else if ($toMatch['diameter']*0.2 < 5) $maxDiaDiff = 5;
        else $maxDiaDiff = $toMatch['diameter']*0.2;

        // Does it match?
        if ($yDist < $maxDiff && $diaDist < $maxDiaDiff) {
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
