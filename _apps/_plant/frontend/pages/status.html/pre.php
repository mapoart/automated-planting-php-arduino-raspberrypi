<?php
use Mapos\Planting\Planting;

$planting = new Planting();
$service->storage['status'] = $planting->readStatus();

$status =  $planting->getStatus();
$service->storage['stageName'] = $planting->displayStage($status['stage']);