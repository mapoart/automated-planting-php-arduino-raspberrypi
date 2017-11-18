<?php

define('MAPOS_START', microtime(true));
if(!defined('MAPOS_BASE_PATH')){
    //We overwrite the base path eg for cli.
    define('MAPOS_BASE_PATH', '../../../'); //This is relative and here.
}

// if need absolute path: define('MAPOS_BASE_PATH', __DIR__ . '/');

use Mapos\Boot\Boot;

if (!file_exists($file = MAPOS_BASE_PATH . 'vendor/autoload.php')) {
    $message = 'Install dependencies to run this script. "composer update"';
    throw new \RuntimeException($message);
}

include $file;

Boot::start(getcwd(),5.4); //5.5 version of php is checked.

