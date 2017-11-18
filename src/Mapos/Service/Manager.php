<?php

namespace Mapos\Service;

class Manager
{

    protected $mapos;

    public function __construct()
    {
        $this->mapos = gi();
    }

    //Call method $name and return the instance from factory.
    public function gi($name)
    {
        $name = (string) $name;
        return $this->$name();
    }

}
