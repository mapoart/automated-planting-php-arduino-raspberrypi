<?php

use \Mapos\Helpers;

function resizeImage($filename, $maxWidth, $maxHeight, $folder = '/thumb/', $quality = 100)
{ //if $folder will be null , overwrite file
    $filenameDestination = dirname($filename) . $folder . '/'; //sometimes is // but it works.
    //echo $filenameDestination;
    if (!is_dir($filenameDestination)) {
        echo $filenameDestination;
        mkdir($filenameDestination, 0777, true);
    }

    list($width, $height) = getimagesize($filename);

    $ratio = $width / $height; // width/height

    if ($width < $maxWidth && $height < $maxHeight) {
        // don't resize up - have to apply lanchos alg later ;)
        $maxWidth = $width;
        $maxHeight = $height;
    }

    $xRatio = $maxWidth / $width;
    $yRatio = $maxHeight / $height;

    if ($xRatio * $height < $maxHeight) { // Resize the image based on width
        $newheight = ceil($xRatio * $height);
        $newwidth = $maxWidth;
    } else { // Resize the image based on height
        $newwidth = ceil($yRatio * $width);
        $newheight = $maxHeight;
    }


//    if ($width > $height && $newheight < $height) {
//        $newheight = $height / ($width / $newwidth);
//    } elseif ($width < $height && $newwidth < $width) {
//        $newwidth = $width / ($height / $newheight);
//    } else {
//        $newwidth = $width;
//        $newheight = $height;
//    }
    $thumb = imagecreatetruecolor($newwidth, $newheight);
    $source = imagecreatefromjpeg($filename);
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    $destinationFilename = $filenameDestination . basename($filename);

    return imagejpeg($thumb, $destinationFilename, $quality);
}

function watermark_image($src, $watermak, $quality = 90)
{
    // Load the stamp and the photo to apply the watermark to
    $stamp = imagecreatefrompng($watermak);
    $im = imagecreatefromjpeg($src);

    // Set the margins for the stamp and get the height/width of the stamp image
    $marge_right = 10;
    $marge_bottom = 10;
    $sx = imagesx($stamp);
    $sy = imagesy($stamp);

    // Copy the stamp image onto our photo using the margin offsets and the photo 
    // width to calculate positioning of the stamp. 
    imagecopy($im, $stamp, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
    imagejpeg($im, $src, $quality);
}

function loadThumb($src)
{
    $src = rtrim($src, "/");
    $dir = dirname($src);
    $filename = basename($src);
    return IMAGE_LOADER . '/' . $dir . '/thumb/' . $filename;
}

function loadImage($src)
{
    return IMAGE_LOADER . '/' . $src . '/';
}
