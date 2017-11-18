<?php
/**
 * Camera Image Daemon
 *
 * @category Daemons
 * @package  Automated Planting 1.0
 * @author   Marcin Polak <mapoart@gmail.com>
 * @license  http://MarcinPolak.eu
 * @version  Release: 1.0
 * @link     http://MarcinPolak.eu/AutomatedPlanting
 */

require_once "bootstrap.php";

use Mapos\Daemon\Daemon;
use Mapos\Device\DeviceVideo;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$daemon = true;
$delay = 60;
$bigPictureDelay = 600;

if ($daemon) {
    $daemon = new Daemon("plantcamera");
    $daemon->setDelay($delay); //10 minutes
    $daemon->start();
}

$logPath = 'logs/planting_camera.log';

$video = new DeviceVideo();
$video->setStoragePath(STORAGE_PATH);

$logger = new Logger('Video');
$logger->pushHandler(new StreamHandler($logPath, Logger::INFO));
$video->setLog($logger);

$run = 1;
$bigPictureTimer = 0;
while (1) {
    if ($run == 1 || $bigPictureTimer > $bigPictureDelay) {
        $video->captureImage();
        $bigPictureTimer = 0;
    }

    $video->captureThumbnail();

    $bigPictureTimer += $delay;

    if ($daemon) {
        $daemon->info("run " . $run);
        //sleep(5);
        $daemon->delay();
    } else {
        sleep($delay);
    }

    $run++;
}