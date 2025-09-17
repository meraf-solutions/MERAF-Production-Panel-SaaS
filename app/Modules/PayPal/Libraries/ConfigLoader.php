<?php

namespace App\Modules\PayPal\Libraries;

class ConfigLoader
{
    public static function getMyConfig($requestedClass = '', $userID = NULL)
    {
        helper('myconfig');
        return getMyConfig($requestedClass, $userID);
    }
}
