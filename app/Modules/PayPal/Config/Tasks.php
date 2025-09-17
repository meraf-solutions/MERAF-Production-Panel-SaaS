<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Tasks.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Modules\PayPal\Config;

use CodeIgniter\Tasks\Config\Tasks as BaseTasks;
use CodeIgniter\Tasks\Scheduler;

class Tasks extends BaseTasks
{
    /**
     * Register any tasks within this method for the application.
     * Called by the TaskRunner.
     */
    public function init(Scheduler $schedule)
    {
        // Check PayPal subscriptions every 5 minutes
        $schedule->call(function() {
                    $cronjob = new \App\Modules\PayPal\Controllers\Cronjob();
                    return $cronjob->check_paypal_subscriptions();
                })
                ->everyFiveMinutes()
                ->named('check-paypal-subscriptions');
    }
}
