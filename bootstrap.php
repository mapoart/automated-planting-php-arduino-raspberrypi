<?php
require "_apps/_plant/config/base_config_DEV.php";

if (!file_exists($file = 'vendor/autoload.php')) {
    $message = 'Install dependencies to run this script. "composer update"';
    throw new \RuntimeException($message);
}

include $file;

//use Mapos\Boot\Boot;
//
//Boot::start(getcwd(),5.5); //5.5 version of php is checked.