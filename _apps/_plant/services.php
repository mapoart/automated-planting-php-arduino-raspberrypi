<?php

$service = gi();
//
//$service->add('Storage', function () use ($service) {
//    return $service->gi('storage.mongodb');
//});
//
//$service->add('Model', function ($modelName) use (&$service) {
//    $db = $service->get('Storage');
//    $className = 'Mapos\\Model\\' . ucfirst($modelName);
//
//    $reflectionClass = new ReflectionClass($className);
//    return $reflectionClass->newInstanceArgs(array($db));
//});
