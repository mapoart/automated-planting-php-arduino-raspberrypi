<?php

$files = glob(STORAGE_PATH . "/thumb/*.jpg");
$files = array_combine($files, array_map("filemtime", $files));
arsort($files);
//
//var_dump($files);

$latest_file = key($files);
$path = $latest_file;


if (file_exists($path)) {
    header('Content-Type: image/jpeg');
    echo file_get_contents($path);
}

exit;