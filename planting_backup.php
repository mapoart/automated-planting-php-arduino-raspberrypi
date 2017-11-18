<?php
/**
 * Backup FTP Daemon
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
use Mapos\Ftp\Ftp;

$daemon = new Daemon("plantbackup");
$daemon->setDelay(1200); //20 minutes
$daemon->start();

$ftp = new Ftp();

$ftp->setHost(FTP_HOST);
$ftp->setPort(FTP_PORT);
$ftp->setLogin(FTP_USERNAME);
$ftp->setPassword(FTP_PASSWORD);

$counter = 0;
$copied = 0;
while (1) {
    $daemon->info("run " . $counter++ . "x");

    $ftp->setHost(FTP_HOST);
    $ftp->setPort(FTP_PORT);
    $ftp->setLogin(FTP_USERNAME);
    $ftp->setPassword(FTP_PASSWORD);

    if (!$ftp->connect()) {
        echo "can't connect";
    } else {
        if (is_dir(FTP_BACKUP_FOLDER)) {
            $openDir = opendir(FTP_BACKUP_FOLDER);
            while ($file = readdir($openDir)) {
                if ($file != "." && $file != ".." && $file != "thumb") {
                    $daemon->info("Copying file: " . $file);

                    if ($ftp->upload(FTP_BACKUP_FOLDER . $file, $file)) {
                        $daemon->info("done");
                        unlink(FTP_BACKUP_FOLDER . $file);
                        $copied++;
                        $daemon->info("Copied " . $copied . " files so far.");
                    } else {
                        $daemon->info(" ! Failed copy file '" . FTP_BACKUP_FOLDER . $file . "'");
                    }
                }
            }
        }
    }

    $daemon->delay();
}