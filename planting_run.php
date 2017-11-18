<?php
/**
 * Run Daemons
 *
 * @category Daemons
 * @package  Automated Planting 1.0
 * @author   Marcin Polak <mapoart@gmail.com>
 * @license  http://MarcinPolak.eu
 * @version  Release: 1.0
 * @link     http://MarcinPolak.eu/AutomatedPlanting
 */

if (0 !== posix_getuid()) {
    //echo  . PHP_EOL;
    die("You must run it as root.");
} else {
    echo "Run camera daemon.." . PHP_EOL;
    //Capture images
    exec("sudo php planting_camera.php > /dev/null 2>&1 &");

    //Transfer ftp / backup images
    echo "Run backup daemon.." . PHP_EOL;
    exec("sudo php planting_backup.php > /dev/null 2>&1 &");

    //TODO: It is not daemon - serial device is not seen...
    echo "Run planting device daemon.." . PHP_EOL;
    exec("sudo php planting_device.php > /dev/null 2>&1 &");

}
