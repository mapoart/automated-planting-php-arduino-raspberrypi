<?php

namespace Mapos\Service;

/**
 * Service class 
 *
 * @author   Marcin Polak <mapoart@gmail.com>
 */
interface ServiceInterface
{

    public function get();

    public function testData(&$db);

    public function setup(&$db);
}
