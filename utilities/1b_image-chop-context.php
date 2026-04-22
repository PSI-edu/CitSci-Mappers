<?php
if ($argc != 2) {
    die("The wrong number of arguments. Usage '1b-image-chop-context.php filetochop.png\n");
} else if (!file_exists($argv[1])) {
    die("The file $argv[1] does not exist.\n");
}

$check = getimagesize($argv[1]);
if ($check !== false) {
    echo("File is an image - " . $argv[1] . "\n");
    $uploadOk = 1;
} else {
    echo("File is not an image.\n");
    $uploadOk = 0;
}

$extlen = strlen(pathinfo(basename($argv[1]), PATHINFO_EXTENSION))+1;
$rootFilename = substr(basename($argv[1]), 0, -$extlen);

$stampSize = 450;
$contextMargin = 400;
$overlap = 0.1 * $stampSize;

// Create a text file with the name of the image.txt
$file = fopen( "scratch/" . $rootFilename . "_400px-context.txt", "w") or die("Unable to open file!");

// write file name to the file
fwrite($file, basename($argv[1]) . "\n");

// Get the image height and width
list($width, $height) = getimagesize($argv[1]);

/* Notes on context images

   These should have an additional 400 pixels (the context margin) on all sides, but be centered on stamp images.
   If stamp is
*/

// cycle through the image in width
for ($x = 0; $x < $width - $overlap; $x += $stampSize - $overlap) {
    // cycle through the image in height
    for ($y = 0; $y < $height - $overlap; $y += $stampSize - $overlap) {
        // Sort start point if trying to go negative
        if ($x - $contextMargin < 0) {
            $x1 = 0;
            $xOffset = -1*($x-$contextMargin);
        } else {
            $x1 = ($x - $contextMargin);
            $xOffset = 0;
        }
        if ($y - $contextMargin < 0) {
            $y1 = 0;
            $yOffset = -1*($y-$contextMargin);
        } else {
            $y1 = ($y - $contextMargin);
            $yOffset = 0;
        }


        // sort image width if it doesn't fit the full width
        if ($x + $stampSize + $contextMargin > $width) {
            $delX = $width - $x1;
        } else {
            $delX = $stampSize+2*$contextMargin;
        }
        // sort image height if it doesn't fit the full height
        if ($y + $stampSize + $contextMargin > $height) {
            $delY = $height - $y1;
        } else {
            $delY = $stampSize+2*$contextMargin;
        }

        // copy pixels x1,y1 to x+450+400,y+450+400 into a new image
        $im = imagecreatefrompng($argv[1]);
        $im1 = imagecreatetruecolor($stampSize+2*$contextMargin, $stampSize+2*$contextMargin);
        imagecopy($im1, $im, $xOffset, $yOffset, $x1, $y1, $delX, $delY);
        imagepng($im1, "scratch/" . $rootFilename . "_$x-$y"."_context.png");
        imagedestroy($im1);
        imagedestroy($im);

        // write each sub-image to the file
        fwrite($file, $rootFilename . "_$x-$y.png\t$x\t$y\n");
    }
}
fclose($file);
// return a link to the text file

