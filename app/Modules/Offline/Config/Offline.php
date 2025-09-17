<?php

namespace App\Modules\Offline\Config;

use CodeIgniter\Config\BaseConfig;

class Offline extends BaseConfig
{
    public $adminMenu = [
        'category' => 'payment_method',
        'title' => 'Offline',
        'logo' => MODULESPATH . 'Offline/Views/assets/offline_logo.svg',
        'url' => 'payment-options/offline/admin',
        'config' => 'Offline',
        'service_name' => 'OfflineService'
    ];

}
