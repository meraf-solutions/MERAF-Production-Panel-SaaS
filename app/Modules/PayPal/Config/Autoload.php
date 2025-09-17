<?php

/**
 * PayPal Module Autoload Configuration
 * 
 * This file contains module-specific autoload settings that will be merged
 * with the main application's autoload configuration.
 */

// Module-specific namespaces
$psr4['App\Modules\PayPal\Controllers'] = APPPATH . 'Modules/PayPal/Controllers';
$psr4['App\Modules\PayPal\Models'] = APPPATH . 'Modules/PayPal/Models';
$psr4['App\Modules\PayPal\Libraries'] = APPPATH . 'Modules/PayPal/Libraries';
$psr4['App\Modules\PayPal\Config'] = APPPATH . 'Modules/PayPal/Config';

// Module-specific helper files
$helpers[] = 'subscription';

// Module-specific class mappings
$classmap['PayPalService'] = APPPATH . 'Modules/PayPal/Libraries/PayPalService.php';
$classmap['SubscriptionModel'] = APPPATH . 'Modules/PayPal/Models/SubscriptionModel.php';
$classmap['SubscriptionPaymentModel'] = APPPATH . 'Modules/PayPal/Models/SubscriptionPaymentModel.php';

// Module-specific files to autoload
$files[] = APPPATH . 'Modules/PayPal/Helpers/subscription_helper.php';

// Merge configurations with main autoload
$this->psr4 = array_merge($this->psr4, $psr4);
$this->classmap = array_merge($this->classmap, $classmap);
$this->files = array_merge($this->files, $files);
$this->helpers = array_merge($this->helpers, $helpers);
