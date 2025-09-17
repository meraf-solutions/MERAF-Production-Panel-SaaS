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

namespace Config;

use CodeIgniter\Tasks\Config\Tasks as BaseTasks;
use CodeIgniter\Tasks\Scheduler;
use App\Services\ModuleScanner;

class Tasks extends BaseTasks
{
    /**
     * --------------------------------------------------------------------------
     * Should performance metrics be logged
     * --------------------------------------------------------------------------
     *
     * If true, will log the time it takes for each task to run.
     * Requires the settings table to have been created previously.
     */
    public bool $logPerformance = true;

    /**
     * --------------------------------------------------------------------------
     * Maximum performance logs
     * --------------------------------------------------------------------------
     *
     * The maximum number of logs that should be saved per Task.
     * Lower numbers reduced the amount of database required to
     * store the logs.
     */
    public int $maxLogsPerTask = 10;

    /**
     * Register any tasks within this method for the application.
     * Called by the TaskRunner.
     */
    public function init(Scheduler $schedule)
    {        
        // Check subscription expiry every 5 minutes
        $schedule->call(function() {
                    $cronjob = new \App\Controllers\Cronjob();
                    return $cronjob->check_subscription_expiry();
                })
                ->everyFiveMinutes()
                ->named('check-subscription-expiry');

        // Check license expiry every 5 minutes
        $schedule->call(function() {
                    $cronjob = new \App\Controllers\Cronjob();
                    return $cronjob->do_auto_key_expiry();
                })
                ->everyFiveMinutes()
                ->named('autoexpiry-license');
    
        // Send license expiry reminders every 5 minutes
        $schedule->call(function() {
                    $cronjob = new \App\Controllers\Cronjob();
                    return $cronjob->do_expiry_reminder();
                })
                ->everyFiveMinutes()
                ->named('remind-expiring-license');

        // Check for abusive IPs every minute
        $schedule->call(function() {
                    $cronjob = new \App\Controllers\Cronjob();
                    return $cronjob->check_abusive_ips();
                })
                ->everyMinute()
                ->named('check-abusive-ips');
                
        // Clean blocked IPs every minute
        $schedule->call(function() {
                    $cronjob = new \App\Controllers\Cronjob();
                    return $cronjob->clean_blocked_ips();
                })
                ->everyMinute()
                ->named('clean-blocked-ips');
                
        // Delete old email logs everyday
        $schedule->call(function() {
                    $cronjob = new \App\Controllers\Cronjob();            
                    return $cronjob->deleteOldEmailLogs();
                })
                ->daily()
                ->named('delete-old-email-logs');

        // Load module tasks
        $moduleScanner = new ModuleScanner();
        $moduleScanner->loadModuleTasks($schedule);

        // Clean push notification device token older than 270 days from last used
        $schedule->call(function() {
                    $cronjob = new \App\Libraries\FirebaseService();
                    return $cronjob->cleanupOldTokens();
                })
                ->monthly()
                ->named('clean-stale-device-token');
    }
}
