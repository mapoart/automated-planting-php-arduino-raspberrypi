<?php

use Mapos\Planting\Planting;

$service->loadHelper('format.php');
$service->loadHelper('image.php');

$planting = new Planting();
$service->storage['presets'] = $planting->getPresets('id,name');

$status = $planting->getStatus();

$preset2 = @$_GET['preset'];
if(!$preset2){
    $preset2 = $status['preset'];
}

$service->storage['selectedPreset'] = $preset2;
$service->storage['actualPreset'] = $planting->getPreset($preset2)[0];
