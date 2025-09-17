<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use App\Controllers\BaseController;
use App\Models\LicensesModel;
use App\Models\LicenseLogsModel;
use App\Models\IpBlockModel;
use App\Models\PackageModel;
use App\Models\SubscriptionModel;
use App\Models\EmailLogModel;

class Cronjob extends BaseController
{
    protected $userID;
    protected $myConfig;
    protected $LicensesModel;
    protected $LicenseLogsModel;
    protected $IpBlockModel;
    protected $PackageModel;
    protected $SubscriptionModel;
    protected $EmailService;
    protected $EmailLogModel;

    public function __construct()
	{
		// Set the locale dynamically based on user preference
		// setMyLocale();

        // Initialize Models
        $this->LicensesModel = new LicensesModel();
        $this->LicenseLogsModel = new LicenseLogsModel();
        $this->IpBlockModel = new IpBlockModel();
        $this->PackageModel = new PackageModel();
        $this->SubscriptionModel = new SubscriptionModel();
        $this->EmailLogModel = new EmailLogModel();

        // Initialize EmailService
        $this->EmailService = new \App\Libraries\EmailService();
    }

    public function check_abusive_ips()
    {
        // Get current time and time x minutes ago
        $currentTime = Time::now();
        $fiveMinutesAgo = $currentTime->subMinutes(5);
        $oneMinuteAgo = $currentTime->subMinutes(1);

        // Get logs from the last x minutes
        $recentLogs = $this->LicenseLogsModel
            ->where('time >=', $oneMinuteAgo->format('Y-m-d H:i:s'))
            ->where('is_valid', 'no')
            ->findAll();

        // Group logs by license_key and source (IP)
        $ipCounts = [];
        foreach ($recentLogs as $log) {
            $key = $log['license_key'] . '_' . $log['source'];
            if (!isset($ipCounts[$key])) {
                $ipCounts[$key] = [
                    'count' => 0,
                    'owner_id' => $log['owner_id'],
                    'ip' => $log['source'],
                    'license_key' => $log['license_key']
                ];
            }
            $ipCounts[$key]['count']++;
        }

        $blockedCount = 0;
        // Check for IPs with consecutive invalid attempts
        foreach ($ipCounts as $data) {
            if ($data['count'] >= 5) { // If there are 5 or more invalid attempts
                // Check if IP is already blocked
                $existingBlock = $this->IpBlockModel->where('ip_address', $data['ip'])->first();
                
                if (!$existingBlock) {
                    // Add IP to block list
                    $this->IpBlockModel->insert([
                        'owner_id' => $data['owner_id'],
                        'ip_address' => $data['ip'],
                        'license_key' => $data['license_key'],
                        'created_at' => $currentTime->format('Y-m-d H:i:s')
                    ]);
                    $blockedCount++;
                    
                    // Log the blocking action
                    log_message('info', "[Cronjob] IP {$data['ip']} blocked due to multiple invalid license attempts for key {$data['license_key']}");

                    // Add notification for new blocked IP
                    $notificationMessage = "IP {$data['ip']} blocked due to multiple invalid license attempts";
                    $notificationType = 'ip_blocked';
                    $url = base_url('admin-options/blocked-ip-logs');
                    $recipientUserId = 1;
                    add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
                }
            }
        }

        $response = [
            'success' => true,
            'status'  => 1,
            'msg'     => "IP block check completed. {$blockedCount} new IPs blocked.",
        ];
        return json_encode($response);
    }

    public function clean_blocked_ips()
    {
        // Get current time and time x minutes ago
        $currentTime = Time::now();
        $thirtyMinutesAgo = $currentTime->subMinutes(30);
        $twentyFourHoursAgo = $currentTime->subHours(24);

        // Delete IPs blocked more than x minutes ago
        $result = $this->IpBlockModel->where('created_at <', $twentyFourHoursAgo->format('Y-m-d H:i:s'))->delete();

        if(!$result) {

            // Add notification for cronjob error
            $notificationMessage = 'An error occurred upon deleting blocked IP';
            $notificationType = 'cronjob_error';
            $url = base_url('admin-options/blocked-ip-logs');
            $recipientUserId = 1;
            add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
            
            return json_encode([
                'success' => false,
                'status'  => 0,
                'msg'     => 'An error occurred upon deleting blocked IP',
            ]);
        }

        return json_encode([
            'success' => true,
            'status'  => 1,
            'msg'     => 'Old IP blocks cleaned successfully.',
        ]);
    }
    
    public function check_subscription_expiry()
    {
        $countProcessedSubscriptions = 0;
        $currentTime = Time::now();

        // Get expired subscriptions
        $expiredSubscriptions = $this->SubscriptionModel->getExpired();
        
        $defaultPackage = $this->PackageModel->getDefaultPackage() ?? null;
        
        foreach ($expiredSubscriptions as $subscription) {
            
            // Update subscription status
            $updateResult = $this->SubscriptionModel->updateSubscriptionStatus(
                                $subscription['subscription_reference'],
                                'expired'
                            );

            if($updateResult) {
                $countProcessedSubscriptions++;

    			$package = $this->PackageModel->find($subscription['package_id']);
    			$data = [
    				'subscription_id' => $subscription['subscription_reference'],
    				'package_name' => $package['package_name'],
    				'status' => $subscription['subscription_status'],
    				'start_time' => $subscription['created_at'],
    				'next_billing_time' => $subscription['next_payment_date'],
    				'amount' => $subscription['amount_paid'],
    				'is_trial' => $defaultPackage['id'] === $subscription['package_id'] ? true : false,
    			];
    
    			try {
    				log_message('debug', '[Cronjob] Preparing to send subscription email. Data: ' . json_encode($data));
    				$result = $this->EmailService->sendSubscriptionEmail([
    					'userID' => $subscription['user_id'],
    					'template' => 'subscription_expired',
    					'data' => $data
    				]);
    			
    				if($result) {
    					log_message('info', "[Cronjob] Expired subscription sent for subscription {$subscription['subscription_reference']}");
    				} else {
    					log_message('error', "[Cronjob] Failed to send expired subscription for subscription {$subscription['subscription_reference']}");
    
                        // Add notification for cronjob error
                        $notificationMessage = 'Failed to send expired subscription email';
                        $notificationType = 'cronjob_error';
                        $url = base_url('subscription-manager/subscription/view/' . $subscription['id']);
                        $recipientUserId = 1;
                        add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
    				}
    			} catch (\Exception $e) {
    				log_message('error', '[Cronjob] Failed to send subscription email: ' . $e->getMessage());
    				log_message('debug', '[Cronjob] Error details: ' . $e->getTraceAsString());
    				log_message('debug', '[Cronjob] Subscription data: ' . json_encode($subscription));
    				log_message('debug', '[Cronjob] Email data: ' . json_encode($data));
    
                    // Add notification for cronjob error
                    $notificationMessage = 'Failed to send expired subscription email';
                    $notificationType = 'cronjob_error';
                    $url = base_url('subscription-manager/subscription/view/' . $subscription['id']);
                    $recipientUserId = 1;
                    add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
    			} catch (\Error $e) {
    				log_message('error', '[Cronjob] Critical error while sending subscription email: ' . $e->getMessage());
    				log_message('debug', '[Cronjob] Error details: ' . $e->getTraceAsString());
    				log_message('debug', '[Cronjob] Subscription data: ' . json_encode($subscription));
    				log_message('debug', '[Cronjob] Email data: ' . json_encode($data));
    
                    // Add notification for cronjob error
                    $notificationMessage = 'Failed to send expired subscription email';
                    $notificationType = 'cronjob_error';
                    $url = base_url('subscription-manager/subscription/view/' . $subscription['id']);
                    $recipientUserId = 1;
                    add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
			    }
            }
        }

        // Get subscriptions expiring soon (7 days threshold)
        $expiringSoonSubscriptions = $this->SubscriptionModel->getExpiringSoon();
        
        foreach ($expiringSoonSubscriptions as $subscription) {
            if(
                ($subscription['sent_expiring_reminder'] === 'no') &&
                ($subscription['is_reactivated'] === 'no')
             )
             {
                $package = $this->PackageModel->find($subscription['package_id']);
                $data = [
                    'subscription_id' => $subscription['subscription_reference'],
                    'package_name' => $package['package_name'],
                    'status' => $subscription['subscription_status'],
                    'start_time' => $subscription['created_at'],
                    'next_billing_time' => $subscription['next_payment_date'],
                    'amount' => $subscription['amount_paid'],
                    'is_trial' => $defaultPackage['id'] === $subscription['package_id'] ? true : false,
                ];

                try {
                    $countProcessedSubscriptions++;
                    
                    log_message('debug', '[Cronjob] Preparing to send subscription email. Data: ' . json_encode($data));
                    $result = $this->EmailService->sendSubscriptionEmail([
                        'userID' => $subscription['user_id'],
                        'template' => 'subscription_expiring',
                        'data' => $data
                    ]);
                
                    if($result) {
                        log_message('info', "[Cronjob] Expiry reminder sent for subscription {$subscription['subscription_reference']}");
                        $this->SubscriptionModel->update($subscription['id'], [
                            'sent_expiring_reminder' => 'yes'
                        ]);
                    } else {
                        log_message('error', "[Cronjob] Failed to send expiry reminder for subscription {$subscription['subscription_reference']}");

                        // Add notification for cronjob error
                        $notificationMessage = 'Failed to send expiring reminder subscription email';
                        $notificationType = 'cronjob_error';
                        $url = base_url('subscription-manager/subscription/view/' . $subscription['id']);
                        $recipientUserId = 1;
                        add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
                    }
                } catch (\Exception $e) {
                    log_message('error', '[Cronjob] Failed to send subscription email: ' . $e->getMessage());
                    log_message('debug', '[Cronjob] Error details: ' . $e->getTraceAsString());
                    log_message('debug', '[Cronjob] Subscription data: ' . json_encode($subscription));
                    log_message('debug', '[Cronjob] Email data: ' . json_encode($data));

                    // Add notification for cronjob error
                    $notificationMessage = 'Failed to send expiring reminder subscription email';
                    $notificationType = 'cronjob_error';
                    $url = base_url('subscription-manager/subscription/view/' . $subscription['id']);
                    $recipientUserId = 1;
                    add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
                } catch (\Error $e) {
                    log_message('error', '[Cronjob] Critical error while sending subscription email: ' . $e->getMessage());
                    log_message('debug', '[Cronjob] Error details: ' . $e->getTraceAsString());
                    log_message('debug', '[Cronjob] Subscription data: ' . json_encode($subscription));
                    log_message('debug', '[Cronjob] Email data: ' . json_encode($data));

                    // Add notification for cronjob error
                    $notificationMessage = 'Failed to send expiring reminder subscription email';
                    $notificationType = 'cronjob_error';
                    $url = base_url('subscription-manager/subscription/view/' . $subscription['id']);
                    $recipientUserId = 1;
                    add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
                }
            }
        }

        $response = [
            'success' => true,
            'status'  => 1,
            'msg'     => "Subscription check completed. Processed {$countProcessedSubscriptions} expired subscriptions. Sent reminders for " . count($expiringSoonSubscriptions) . " expiring subscriptions.",
        ];
        return json_encode($response);
    }

    public function do_auto_key_expiry()
    {
        // Check if auto-expiration is enabled in the config and license manager is not SLM
        $this->myConfig = [];

        $countProcessedLicense = 0;

        // Get the current date and time
        $currentDateTime = Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s');

        // Define the where clause for filtering licenses
        $where = "license_status = 'active' AND date_expiry IS NOT NULL AND (license_type = 'subscription' OR license_type = 'trial')";

        // Fetch the list of licenses based on the where clause and order by id in descending order
        $listLicenses = $this->LicensesModel->where($where)->orderBy('id', 'DESC')->findAll();

        // Check if no licenses need processing
        if (empty($listLicenses)) {
            $response = [
                'success' => true,
                'status'  => 1,
                'msg'     => 'No eligible licenses found for expiration.',
            ];
            return json_encode($response);
        }

        // Loop through licenses to check expiration and update
        foreach ($listLicenses as $listLicense) {
            $this->userID = $listLicense['owner_id'];
            $this->myConfig = getMyConfig('', $this->userID);

            if (
                $this->myConfig['autoExpireLicenseKeys'] && 
                ($this->myConfig['licenseManagerOnUse'] !== 'slm') &&
                ($currentDateTime >= $listLicense['date_expiry'])
            ) {
                    
                // Log the activity
                licenseManagerLogger($listLicense['license_key'], 'notification: Expired license notification sent', 'yes');
                log_message('debug', '[Cronjob] Expired license notification sent: ' . $listLicense['license_key']);

                $clientFullName = $listLicense['first_name'] .' '. $listLicense['last_name'];

                $bccAdmin = $this->myConfig['sendBCCtoLicenseClientNotifications'] ? true : false;
                
                // Initiate email notification
                if($this->myConfig['sendExpiredNotification']) {
                    try {
                        $licenseNotificationResult = $this->EmailService->sendLicenseNotification([
                            'license_key' => $listLicense['license_key'],
                            'recipient_email' => $listLicense['email'],
                            'date_activity' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                            'template' => 'license_expired',
                            'email_format' => 'html'
                        ]);
                    } catch (\Throwable $e) {
                        // Handle exceptions
                        log_message(
                            'error',
                            '[Cronjob] Error sending license notification: ' . $e->getMessage()
                        );

                        // Add notification for cronjob error
                        $notificationMessage = 'Failed to send expired license email';
                        $notificationType = 'cronjob_error';
                        $url = base_url('license-manager/list-all?s=' . $listLicense['license_key']);
                        $recipientUserId = $listLicense['owner_id'];
                        add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
                    }
                }

                // Update license data after sending reminder
                $data = [
                    'license_status' => 'expired',
                    'reminder_sent' => $listLicense['reminder_sent'] + 1, // Increment reminder_sent
                    'reminder_sent_date' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                ];

                // Update the license record and handle any errors
                try {
                    $this->LicensesModel->update($listLicense['id'], $data);

                    // Check if needed to update WooCommerce
                    checkToUpdateWooCommerce($listLicense['license_key']);

                    $countProcessedLicense++;
                } catch (\Exception $e) {
                    log_message('error', '[Cronjob] Error updating license: ' . $e->getMessage());
                    // You can choose to handle the error here or continue processing other licenses

                    // Add notification for cronjob error
                    $notificationMessage = 'Failed to update expired license';
                    $notificationType = 'cronjob_error';
                    $url = base_url('license-manager/list-all?s=' . $listLicense['license_key']);
                    $recipientUserId = $listLicense['owner_id'];
                    add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
                }
            }
        }

        // Prepare response with processing details
        $response = [
            'success' => true,
            'status'  => 1,
            'msg'     => 'Auto-expiry cron job run successfully! Updated a total of ' . $countProcessedLicense . ' license(s).',
        ];
        return json_encode($response);
    } 

    public function do_expiry_reminder()
    {
        // Check if auto-expiration is enabled in the config and license manager is not SLM
        $this->myConfig = [];
        
        $countProcessedLicense = 0;

        // Define the where clause for filtering licenses
        $where = "license_status = 'active' AND date_expiry IS NOT NULL AND (license_type = 'subscription' OR license_type = 'trial') AND reminder_sent = '0'";

        // Fetch the list of licenses based on the where clause and order by id in descending order
        $listLicenses = $this->LicensesModel->where($where)->orderBy('id', 'DESC')->findAll();

        // Check if no licenses need processing
        if (empty($listLicenses)) {
            $response = [
                'success' => true,
                'status'  => 1,
                'msg'     => 'No eligible licenses found for reminding license expiration.',
            ];
            return json_encode($response);
        }

        // Loop through licenses to check expiration and update
        foreach ($listLicenses as $listLicense) {    
            
            $this->userID = $listLicense['owner_id'];
            $this->myConfig = getMyConfig('', $this->userID);

            if ($this->myConfig['autoExpireLicenseKeys'] && $this->myConfig['licenseManagerOnUse'] !== 'slm') {

                $setReminderSchedule = $this->myConfig['numberOfHoursToRemind'];

                $currentDateTime = Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'); // Get the current date and time
                $expirationDate = $listLicense['date_expiry']; // Get the license's expiration date
                $reminderDate = Time::parse($expirationDate)->subHours($setReminderSchedule); // Set the reminder

                if ($currentDateTime >= $reminderDate) {

                    // Log the activity
                    licenseManagerLogger($listLicense['license_key'], 'notification: Reminder for expiring license notification sent', 'yes');
                    log_message('debug', '[Cronjob] Reminder for expiring license notification sent: ' . $listLicense['license_key']);

                    $bccAdmin = $this->myConfig['sendBCCtoLicenseClientNotifications'] ? true : false;
                    
                    if($this->myConfig['sendReminderNotification']) {
                        // Initiate email notification
                        try {
                            $licenseNotificationResult = $this->EmailService->sendLicenseNotification([
                                'license_key' => $listLicense['license_key'],
                                'recipient_email' => $listLicense['email'],
                                'date_activity' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                                'template' => 'reminder_expiring_license',
                                'email_format' => 'html'
                            ]);
                        } catch (\Throwable $e) {
                            // Handle exceptions
                            log_message(
                                'error',
                                '[Cronjob] Error sending license notification: ' . $e->getMessage()
                            );

                            // Add notification for cronjob error
                            $notificationMessage = 'Failed to send expiring reminder license email';
                            $notificationType = 'cronjob_error';
                            $url = base_url('license-manager/list-all?s=' . $listLicense['license_key']);
                            $recipientUserId = $listLicense['owner_id'];
                            add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
                        }
                    }

                    // Update license data after sending reminder
                    $data = [
                        'reminder_sent' => $listLicense['reminder_sent'] + 1, // Increment reminder_sent
                        'reminder_sent_date' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                    ];
    
                    // Update the license record and handle any errors
                    try {
                        $this->LicensesModel->update($listLicense['id'], $data);

                        $countProcessedLicense++;
                    } catch (\Exception $e) {
                        log_message('error', '[Cronjob] Error updating license: ' . $e->getMessage());
                        // You can choose to handle the error here or continue processing other licenses

                        // Add notification for cronjob error
                        $notificationMessage = 'Failed to update expiring license data';
                        $notificationType = 'cronjob_error';
                        $url = base_url('license-manager/list-all?s=' . $listLicense['license_key']);
                        $recipientUserId = $listLicense['owner_id'];
                        add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
                    }
                }
            }
        }

        // Prepare response with processing details
        $response = [
            'success' => true,
            'status'  => 1,
            'msg'     => 'Reminder on expiring license cron job run successfully! Updated a total of ' . $countProcessedLicense . ' license(s).',
        ];
        return json_encode($response);
    }

    public function deleteOldEmailLogs()
    {
        $daysToKeep = 60;
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));

        $this->EmailLogModel->where('created_at <', $cutoffDate)->delete();

        $deletedCount = $this->EmailLogModel->affectedRows();

        log_message('info', "Deleted {$deletedCount} email logs older than {$daysToKeep} days.");

        $response = [
            'success' => true,
            'status'  => 1,
            'msg'     => "Deleted {$deletedCount} email logs older than {$daysToKeep} days.",
        ];
        return json_encode($response);
    }
}
