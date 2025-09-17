<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Localization extends BaseController
{
    public function setLocale($locale)
    {
        $redirect = $this->request->getGet('redirect') ?? '/';
        $response = redirect()->to($redirect);
        
        $response->setCookie('user_locale', $locale, 60*60*24*30); // Set cookie for 30 days
        
        service('request')->setLocale($locale);
        
        return $response;
    }
}

