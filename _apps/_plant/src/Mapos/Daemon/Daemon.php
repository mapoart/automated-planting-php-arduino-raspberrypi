<?php


namespace Mapos\Daemon;

require_once "System/Daemon.php";

class Daemon
{
    private $delay = 10; //default 10 second delay

    private $appName;

    public function __construct($appName){
        $this->appName = $appName;
    }

    public function start()
    {
        $options = array(
            'appName' => $this->appName,
            // 'logLocation' => getcwd() . "logs/"
//        'appDescription' => 'transfer daemon.',
//        'authorName' => 'Marcin Polak',
//        'logLocation' => "../logs/",
//        'appPidLocation' => '/var/run/planting/',
//        'authorEmail' => 'mapoart@gmail.com',
//        'sysMaxExecutionTime' => '0',
//        'sysMaxInputTime' => '0',
//        'appRunAsGID' => 0,
//        'appRunAsUID' => 0
        );

        \System_Daemon::setOptions($options);
        return \System_Daemon::start();
    }

    public function stop()
    {
        \System_Daemon::stop();
    }

    public function info($msg)
    {
        \System_Daemon::info($msg);

    }

    public function delay()
    {
        \System_Daemon::Iterate($this->delay); // in seconds
    }

    /**
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @param int $delay
     */
    public function setDelay($delay)
    {
        $this->delay = (int)$delay;
    }

    /**
     * @return mixed
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @param mixed $appName
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }
}