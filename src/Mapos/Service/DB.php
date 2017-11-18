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
class DB implements ServiceInterface
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

    public function testData(&$db)
    {
        echo 'testData#1#';
        $collection = $db->selectCollection('product');
        $s1 = $db->save(array(
//            '_id' => new \MongoId("535bf420f58aea99708b456a"),
            'name' => 'Product 1',
            'category' => 'Category 1',
            'price' => 11.21
            )
        );

//        if (!$s1) {
//            throw new ServiceException('The record already exists. Do you have SETUP turned on in config/main.php? Please comment line with SETUP line');
//        }

        $s2 = $db->save(array(
            'name' => 'Product 2',
            'category' => 'Category 1',
            'price' => 444.12
            )
        );

//        if (!$s2) {
//            throw new ServiceException('The record already exists. Do you have SETUP turned on in config/main.php? Please comment line with SETUP line');
//        }

        $collection = $db->selectCollection('user');
        $empty = $db->save(array(
            'email' => 'mapoart@gmail.com',
            'firstname' => 'Marcin',
            'lastname' => 'Polak'
            )
        );

        $empty = $db->save(array(
            'email' => 'mpolak@onet.eu',
            'firstname' => 'Marcin2',
            'lastname' => 'Polak3'
            )
        );
    }

    public function setup(&$db)
    {
        $collection = $db->selectCollection('product');
        $collection->ensureIndex(array('name' => 1), array('unique' => true));

        $collection = $db->selectCollection('user');
        $collection->ensureIndex(array('email' => 1), array('unique' => true));
    }

}
