<?php
if ($argc != 2) {
    die("The wrong number of arguments. Usage 'image-chop.php filetochop.png\n");
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
$overlap = 0.1 * $stampSize;

// Create a text file with the name of the image.txt
$file = fopen( $rootFilename . ".txt", "w") or die("Unable to open file!");

// write file name to the file
fwrite($file, basename($argv[1]) . "\n");

// Get the image height and width
list($width, $height) = getimagesize($argv[1]);

// cycle through the image in width
for ($x = 0; $x < $width - $overlap; $x += $stampSize - $overlap) {
    // cycle through the image in height
    for ($y = 0; $y < $height - $overlap; $y += $stampSize - $overlap) {

        // sort image width if it doesn't fit the full width
        if ($x + $stampSize > $width) {
            $delX = $width - $x;
        } else {
            $delX = $stampSize;
        }
        // sort image height if it doesn't fit the full height
        if ($y + $stampSize > $height) {
            $delY = $height - $y;
        } else {
            $delY = $stampSize;
        }

        // copy pixels x,y to x+450,y+450 into a new image
        $im = imagecreatefrompng($argv[1]);
        $im1 = imagecreatetruecolor($stampSize, $stampSize);
        imagecopy($im1, $im, 0, 0, $x, $y, $delX, $delY);
        imagepng($im1, $rootFilename . "_$x-$y.png");
        imagedestroy($im1);
        imagedestroy($im);

        // write each sub-image to the file
        fwrite($file, $rootFilename . "_$x-$y.png\n");


    }
}
fclose($file);
// return a link to the text file

