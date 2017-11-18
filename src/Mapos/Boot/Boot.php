<?php

/**
 * Mapos 1.0
 *
 * PHP version 5.5
 *
 * @category Framework
 * @package  MapOS
 * @author   Marcin Polak <mapoart@gmail.com>
 * @license  http://URL name
 * @version  GIT: <git_id>
 * @link     URL description
 */

namespace Mapos\Boot;

use IDS\Init;
use IDS\Monitor;

/**
 * Boot class
 *
 * @category Boot/Config
 * @package  MapOS
 * @author   Marcin Polak <mapoart@gmail.com>
 * @license  http://URL name
 * @version  Release: <package_version>
 * @link     URL description
 */
class Boot
{

    /**
     * Start the System
     *
     * @param string $sitePath Path where the Mapos system is located.
     * @param float $minPHPVersion Minimum PHP Version to run the system.
     *
     * @return void
     */
    public static function start($sitePath, $minPHPVersion = null)
    {
        if ($minPHPVersion) {
            self::checkPHP($minPHPVersion);
        }
        self::cliInit();
        self::sessionInit();
        self::site($sitePath);


    }

    public static function ids()
    {
        $request = array(
            'REQUEST' => $_REQUEST,
            'GET' => $_GET,
            'POST' => $_POST,
            'COOKIE' => $_COOKIE
        );

        $init = Init::init(MAPOS_BASE_PATH . 'etc/ids/Config.ini');

        $ids = new Monitor($init);
        $result = $ids->run($request);

        if (!$result->isEmpty()) {
//            $compositeLog = new \IDS_Log_Composite();
//            $compositeLog->addLogger(\IDS_Log_File::getInstance($init));
//            $compositeLog->execute($result);
//            


            $r = "IDS Error!";
            $r = "\n";
            $r .= $result;
            self::emergency($r);

            if (DEBUG) {
                print_r($r);
            }

            die("Naruszone zostały zasady bezpieczeństwa dla tego systemu. Ta sytuacja została zarejestrowana.");
        }
    }

    public static function emergency($msg)
    {
        lg('EMERGENCY', $msg);
    }

    /**
     * Handling, Secure native PHP sessions.
     *
     * @return void
     */
    public static function sessionInit()
    {
        ini_set('default_charset', 'UTF-8');
        ini_set('session.use_strict_mode', 1);

        // Forces sessions to only use cookies.
        ini_set('session.use_only_cookies', 1);
        // Better session id's, more random than PHP's default.
        ini_set('session.entropy_file', '/dev/urandom');
        // How many bytes which will be read from the above file. 512 = overkill?
        ini_set('session.entropy_length', '512');
        ini_set('session.hash_function', 'sha512');
        ini_set('session.hash_bits_per_character', '6');
        ini_set('session.cookie_lifetime', '604800');
        // Sets a custom session name.
        $sessionName = 'mapos_sec';
        // Set to true if HTTPS is being used.
        $secure = false;
        // Stops Javascript from being able to read the cookies.
        $httpOnly = true;
        // Gets current cookies params.
        $cPar = session_get_cookie_params();
        session_set_cookie_params(
            $cPar["lifetime"], $cPar["path"], $cPar["domain"], $secure, $httpOnly
        );
        // Sets the session name to the one set above.
        session_name($sessionName);
        if (session_status() == PHP_SESSION_NONE) {
            @session_start();
        }

        // Regenerated the session, delete the old one.
        //session_regenerate_id();
        unset($sessionName, $secure, $httpOnly);
    }

    /**
     * Checks PHP version
     *
     * @param float $minPHPVersion Minimum PHP Version to run the system.
     *
     * @return void
     */
    public static function checkPHP($minPHPVersion)
    {
        //This can be also checked by the Composer as it is on Mapos.
        if (version_compare(phpversion(), $minPHPVersion, '<')) {
            $exception = 'Please install PHP at version "' .
                $minPHPVersion . '". Your PHP version: ' . phpversion();
            throw new \RuntimeException($exception);
        }
    }

    /**
     * Handle the cli run
     *
     * @return void
     */
    public static function cliInit()
    {
        $httpHost = filter_input(INPUT_SERVER, 'HTTP_HOST');

        if (!$httpHost) {
            $httpHost = $_SERVER['HTTP_HOST']; // php ids handling security of the session.
        }

        if (!$httpHost) {
            //This if is done for phpunit testing.
            if (self::isCli()) {
                $runCommand = $_SERVER['argv'][0];
                $runCommand = end(explode('/', $runCommand));
                //for php unit we do not want to pass parameters.
                //then we do bellow && $runCommand!='phpunit'
                $args = array_slice($_SERVER['argv'], 1);
//                if (!isset($args[0]) && $runCommand != 'phpunit') {
//                    $message = "As first parameter please specify "
//                        . "domain name like: domainexample1.com\n";
//                    throw new \RuntimeException($message);
//                }

                if (!isset($_SERVER['HTTP_HOST'])) {
                    $_SERVER['HTTP_HOST'] = $args[0];
                }
            } else {
                $message = 'Error Boot::cli() - no cli and no $_SERVER["HTTP_HOST"]';
                throw new \RuntimeException($message);
            }
        }
    }

    public static function isCli()
    {
        return php_sapi_name() === 'cli' or defined('STDIN');
    }

    /**
     * We loads all needed files for the system to run.
     *
     * @param string $sitePath Path where the Mapos system is located.
     *
     * @return void
     */
    public static function site($sitePath)
    {
        include_once $sitePath . '/../_domain_config.php';
        define('APP_FOLDER', '_apps/_' . SITE_FOLDER . '/');
        include MAPOS_BASE_PATH . 'src/Mapos/Helpers/base.php';
        include MAPOS_BASE_PATH . APP_FOLDER . 'config/base_config_' . strtoupper(ENVIRONMENT) . '.php';
        //We protect site with ids!
        //self::ids();
        include_once MAPOS_BASE_PATH . APP_FOLDER . '/test.php';
        include_once MAPOS_BASE_PATH . APP_FOLDER . '/services.php';


        if (!self::isCli()) {
            //pages we only sends to the browser for now.
            include_once MAPOS_BASE_PATH . APP_FOLDER . '/router.php';
        }
    }

}
