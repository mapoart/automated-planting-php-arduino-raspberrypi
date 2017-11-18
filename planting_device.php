<?php
/**
 * Planting Device Daemon
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
use Mapos\Device\DeviceSerial;
use Mapos\Planting\Planting;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

//We need to do it in one iteration because serial reseting Arduino,
//So we calculating sleepCollect Data

$daemon = false;
$sleepUpdate = 5;//seconds
$sleepCollectData = 600; // 10 mins

if ($daemon) {
    $daemon = new Daemon("plantdevice");
    $daemon->setDelay($sleepUpdate); //10 minutes
    $daemon->start();
}
$logPath = 'logs/planting_device.log';

$logger = new Logger('Serial');
$logger->pushHandler(new StreamHandler($logPath, Logger::INFO));

$device = new DeviceSerial();
$device->setLog($logger);

$planting = new Planting();
$planting->setSerialDevice($device);

$logger = new Logger('Planting');
$logger->pushHandler(new StreamHandler($logPath, Logger::INFO));
$planting->setLog($logger);

$planting->boot();
$run = 0;
$collectDataTimer = 0;
while (1) {
    if ($run == 0 || $collectDataTimer > $sleepCollectData) {
        $planting->collectData();
        $collectDataTimer = 0;
    }

    $planting->updateDevice();

    $collectDataTimer += $sleepUpdate;

    if ($daemon) {
        $daemon->info("run " . $run++);
        //sleep(5);
        $daemon->delay();
    } else {
        sleep($sleepUpdate);
    }
}