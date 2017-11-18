<?php

namespace Mapos\Service;

use Mapos\Storage\Storage;
use Mapos\Service\Service;
use Mapos\Service\ServiceException;
use Mapos\Storage\StorageMongoDB;

/**
 * DB Service class 
 *
 * @author   Marcin Polak <mapoart@gmail.com>
 */
class Log implements ServiceInterface
{

    private $instance;

    public function __contruct()
    {
        
    }

    public function get()
    {
        $db = new Storage(new StorageMongoDB());
        $db->selectDB(DB_NAME);
        global $setupDone;
        if (!$setupDone && defined('SETUP')) {
            $this->setup($db);
            $this->testData($db);
            $setupDone = true;
        }
        return $this->instance = &$db;
    }

}
