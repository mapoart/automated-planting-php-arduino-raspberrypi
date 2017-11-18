<?php

$preset = $_GET['preset'];


if ($stage = @$_POST['stage']) {
    //We run preset!!!!!!
    $conn = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USERNAME, DB_PASSWORD);

    $sql = "UPDATE status set updateDevice='1', stage=?  WHERE id=1";
    $v = [$stage];
    $q = $conn->prepare($sql);
    $test = $q->execute($v);
    if (!$test) {
        print_r($q->errorInfo());
        exit;
    }

    $newURL = "index.html";
    header('Location: ' . $newURL);

}





if (@$_POST['preset_run']) {
    //We run preset!!!!!!
    $conn = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USERNAME, DB_PASSWORD);

    $sql = "UPDATE status set updateDevice='1', preset=?  WHERE id=1";
    $v = [$preset];
    $q = $conn->prepare($sql);
    $test = $q->execute($v);
    if (!$test) {
        print_r($q->errorInfo());
        exit;
    }

    $newURL = "index.html";
    header('Location: ' . $newURL);

}

//We add id to the end
if ($preset >= 5 && $preset <= 7) {
    //Only edit custom ones!!!
    $fields = array(
        'day_temp_min', 'day_temp_max', 'day_humidity_min', 'day_humidity_max',
        'night_temp_min', 'night_temp_max', 'night_humidity_min', 'night_humidity_max',

        'growing_day_start', 'growing_night_start', 'flowering_day_start', 'flowering_night_start',

        'growing_red', 'growing_blue', 'growing_uv', 'growing_ir',

        'flowering_red', 'flowering_blue', 'flowering_uv', 'flowering_ir',

        'sick_red', 'sick_blue', 'sick_uv', 'sick_ir',
    );

    $f = [];
    $v = [];
    foreach ($fields as $field):
        $f[] = $field . ' = ?';
        $v[] = $_POST[$field];
    endforeach;

    $v[] = $preset;

    $conn = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USERNAME, DB_PASSWORD);

    $sql = "UPDATE presets SET " . join(",", $f) . " WHERE id=?";

    $q = $conn->prepare($sql);
    $test = $q->execute($v);
    if (!$test) {
        print_r($q->errorInfo());
    }

    $newURL = "index.html?preset=" . $preset;
    header('Location: ' . $newURL);

}
