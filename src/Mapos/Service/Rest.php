<?php

namespace Mapos\Service;

use Mapos\Service\Service;

/**
 * Rest service class 
 *
 * @author   Marcin Polak <mapoart@gmail.com>
 */
class Rest implements ServiceInterface
{

    private $instance;

    public function __construct()
    {
        
    }

    public function get()
    {
        return $this;
    }

    public function show($data)
    {
        return json_encode($data);
    }

    public function error($err)
    {
        return json_encode(array('error', $err));
    }

    public function testData(&$db)
    {
        
    }

    public function setup(&$db)
    {
        
    }

}
