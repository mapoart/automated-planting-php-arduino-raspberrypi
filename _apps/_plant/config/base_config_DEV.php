<?php
//Errors handling
define('DEBUG', true);

if(DEBUG){
    ini_set("display_errors", "On");
    error_reporting(E_ALL);
}else{
    ini_set("display_errors", "Off");
    error_reporting(0);
}
define('STORAGE_PATH', '/var/www/plant/storage/');

define("PERIOD", 10); //periode in seconds between shoots

define("DB_HOST", "localhost");
define("DB_NAME", "planting");
define("DB_USERNAME", "marcin");
define("DB_PASSWORD", "1,,,");


define("FTP_HOST","192.168.1.101");
define("FTP_PORT","21");
define("FTP_USERNAME","planting");
define("FTP_PASSWORD","-1-xV32-m==-A;V");
define("FTP_BACKUP_FOLDER", STORAGE_PATH);

date_default_timezone_set('Europe/Warsaw');

define('DEFAULT_RUN_FOLDER', 'frontend'); //When _apps/_hydro/somethinghere  does not exist will be looking at frontend

define('BASE_URL', "http://plant.local.pl/");
define('BASE_NAME', "Automated planting"); //Shows on emails etc.

define('EMAIL_FROM', 'mapoart@gmail.com');



