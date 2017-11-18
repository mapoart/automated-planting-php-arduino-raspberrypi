<?php

use Mapos\Service\Service;
use Mapos\Validator\Validator;

function input($sName, $params = false)
{
    $fieldClass = "\\Mapos\\Web\\Form\\Field" . ucfirst($sName);
    return new $fieldClass($params);
}
