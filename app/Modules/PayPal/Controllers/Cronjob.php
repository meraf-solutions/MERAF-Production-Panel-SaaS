<?php

namespace App\Modules\PayPal\Controllers;

use CodeIgniter\I18n\Time;
use App\Controllers\BaseController;
use App\Models\PackageModel;
use App\Models\SubscriptionModel;
use App\Modules\PayPal\Libraries\PayPalService;

class Cronjob extends BaseController
{
    protected $PackageModel;
    protected $SubscriptionModel;
    protected $PayPalService;

    public function __construct()
    {
        // Set the timezone to UTC
        setMyTimezone('UTC');
        
        // Initialize Models
        $this->PackageModel = new PackageModel();
        $this->SubscriptionModel = new SubscriptionModel();
        $this->PayPalService = new PayPalService();
    }

    public function check_paypal_subscriptions()
    {
        $countProcessed = 0;
        $countUpdated = 0;

        // Get all active subscriptions with PayPal as payment provider
        $activeSubscriptions = $this->SubscriptionModel->where([
            'payment_method' => 'PayPal',
            'subscription_status' => 'active'
        ])->findAll();

        foreach ($activeSubscriptions as $subscription) {
            $countProcessed++;
            
            try {
                // Get subscription details from PayPal
                $paypalSubscription = $this->PayPalService->getSubscription($subscription['subscription_reference']);
                // log_message('debug', '[PayPal/Cronjob] paypalSubscription: '.print_r($paypalSubscription, true));
                // If subscription exists in PayPal
                if ($paypalSubscription) {
                    $status = strtolower($paypalSubscription->status);

                    $needsUpdate = false;
                    
                    if($subscription['is_reactivated'] === 'no') {

                        // Check if status needs updating
                        if($status !== $subscription['subscription_status'])  {
                            $needsUpdate = true;
                            
                            // Update subscription status
                            $this->SubscriptionModel->updateSubscriptionStatus(
                                $subscription['subscription_reference'],
                                $status
                            );
    
                            // If subscription is cancelled, suspended or expired in PayPal
                            if (in_array($status, ['cancelled', 'expired', 'suspended'])) {
                                // Update cancellation date if not set
                                if (empty($subscription['cancelled_at'])) {
                                    $this->SubscriptionModel->update($subscription['id'], [
                                        'cancelled_at' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s')
                                    ]);
                                }
                            }
                        }

                        // Check payment status
                        $lastPaymentStatus = $this->PayPalService->getTransactionDetails($subscription['transaction_id']);
                        // log_message('debug', '[PayPal/Cronjob] lastPaymentStatus: '.print_r($lastPaymentStatus, true));

                        // Update Payment Status
                        if ($lastPaymentStatus && (strtolower($lastPaymentStatus->status) !== $subscription['payment_status']) ) {
                            $needsUpdate = true;
                            $this->SubscriptionModel->updatePaymentStatus(
                                $subscription['subscription_reference'],
                                strtolower($lastPaymentStatus->status)
                            );
                        }

                        // Update next billing date if different
                        $nextBillingDate = isset($paypalSubscription->billing_info->next_billing_time) 
                        ? strval(new Time($paypalSubscription->billing_info->next_billing_time))
                        : null;
                        $subscriptionNextPaymentDate = new Time($subscription['next_payment_date']);
                        $subscriptionNextPaymentDate = $subscriptionNextPaymentDate->toDateTimeString('Y-m-d H:i');

                        if ($nextBillingDate && $nextBillingDate !== $subscriptionNextPaymentDate) {
                            $needsUpdate = true;
                            $this->SubscriptionModel->updateNextPaymentDate(
                                $subscription['subscription_reference'],
                                $nextBillingDate
                            );
                        }

                        if ($needsUpdate) {
                            $countUpdated++;
                            log_message('info', "[PayPal/Cronjob] Updated PayPal subscription: {$subscription['subscription_reference']}");
                        }
                    }

                } else {
                    // Subscription not found in PayPal - mark as cancelled
                    $this->SubscriptionModel->cancelSubscription(
                        $subscription['subscription_reference'],
                        'Subscription not found in PayPal',
                        true
                    );
                    
                    $countUpdated++;
                    log_message('warning', "[PayPal/Cronjob] PayPal subscription not found, marked as cancelled: {$subscription['subscription_reference']}");
                }
            } catch (\Exception $e) {
                log_message('error', "[PayPal/Cronjob] Error checking PayPal subscription {$subscription['subscription_reference']}: " . $e->getMessage());
            }
        }

        // Handle payment retries
        $this->handle_payment_retries();

        // Cancel subscriptions that are pending and have expired transaction_token (after 3 hours from start_date)
        $pendingSubscriptions = $this->SubscriptionModel->where([
            'payment_method' => 'PayPal',
            'subscription_status' => 'pending'
        ])->findAll();

        foreach ($pendingSubscriptions as $subscription) {
            $currentTime = Time::now()->setTimezone('UTC');
            $startDate = new Time($subscription['start_date'], 'UTC');
            $threeHoursAfterStart = $startDate->addHours(3);

            // log_message('debug', "[PayPal/Cronjob] Subscription ID: {$subscription['id']} - Current Time: {$currentTime->toDateTimeString()} and threeHoursAfterStart: {$threeHoursAfterStart->toDateTimeString()}");
            // log_message('debug', "[PayPal/Cronjob] Subscription ID: {$subscription['id']} - Is current time after three hours? " . ($currentTime > $threeHoursAfterStart ? 'Yes' : 'No'));

            if ($subscription['transaction_token'] && $currentTime > $threeHoursAfterStart) {
                // Cancel the subscription
                $this->SubscriptionModel->cancelSubscription(
                    $subscription['subscription_reference'],
                    'Cancelled pending PayPal subscription due to expired transaction token',
                    true
                );

                $countProcessed++;
                $countUpdated++;

                log_message('info', "[PayPal/Cronjob] Cancelled pending PayPal subscription due to expired transaction token: {$subscription['subscription_reference']}");
            }
        }

        $response = [
            'success' => true,
            'status'  => 1,
            'msg'     => "PayPal subscription check completed. Processed {$countProcessed} subscriptions, updated {$countUpdated}.",
        ];
        return json_encode($response);
    }

    private function handle_payment_retries()
    {
        $subscriptionsToRetry = $this->SubscriptionModel->where('payment_status', 'failed')
            ->where('retry_count <', 3)
            ->where('next_retry_date <=', Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'))
            ->findAll();

        foreach ($subscriptionsToRetry as $subscription) {
            try {
                // Attempt to process the payment
                $paymentResult = $this->PayPalService->processPayment($subscription['subscription_reference']);

                if ($paymentResult->success) {
                    // Payment successful, update subscription
                    $this->SubscriptionModel->update($subscription['id'], [
                        'payment_status' => 'completed',
                        'retry_count' => null,
                        'next_retry_date' => null,
                        'retry_dates' => null,
                    ]);

                    // Send success email
                    $this->sendPaymentSuccessEmail($subscription);
                } else {
                    // Payment failed again, update retry count and next retry date
                    $retryDates = json_decode($subscription['retry_dates'], true);
                    $nextRetryIndex = $subscription['retry_count'] + 1;

                    if ($nextRetryIndex < 3) {
                        $this->SubscriptionModel->update($subscription['id'], [
                            'retry_count' => $nextRetryIndex,
                            'next_retry_date' => $retryDates[$nextRetryIndex],
                        ]);
                    } else {
                        // All retries failed, suspend the subscription
                        $this->SubscriptionModel->update($subscription['id'], [
                            'subscription_status' => 'suspended',
                            'payment_status' => 'failed',
                            'retry_count' => null,
                            'next_retry_date' => null,
                            'retry_dates' => null,
                        ]);

                        // Send suspension email
                        $this->sendSubscriptionSuspendedEmail($subscription);
                    }
                }
            } catch (\Exception $e) {
                log_message('error', "[PayPal/Cronjob] Error processing payment retry for subscription {$subscription['id']}: " . $e->getMessage());
            }
        }
    }

    private function sendPaymentSuccessEmail($subscription)
    {
        $package = $this->PackageModel->find($subscription['package_id']);

        $user = $this->UserModel->find($subscription['user_id']);

        $data = [
            'subscription_id' => $subscription['subscription_reference'],
            'package_name' => $package['package_name'],
            'status' => 'active',
            'payment_date' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
            'amount' => $subscription['amount_paid'],
            'currency' => $subscription['currency']
        ];

		try {
			$emailService = new \App\Libraries\EmailService();
			$result = $emailService->sendSubscriptionEmail([
				'userID' => $subscription['user_id'],
				'template' => 'payment_success',
				'data' => $data,
			]);

            if($result) {
                log_message('info', "[PayPal/Cronjob] Sent payment success email for subscription: {$subscription['subscription_reference']}");
			}
		} catch (\Exception $e) {
			// Log the error
			log_message('error', '[PayPal/Cronjob] Failed to send subscription email: ' . $e->getMessage());
			
			// You might want to log additional details
			log_message('debug', '[PayPal/Cronjob] Error details: ' . $e->getTraceAsString());
		} catch (\Error $e) {
			// Catch PHP errors
			log_message('error', '[PayPal/Cronjob] Critical error while sending subscription email: ' . $e->getMessage());
			log_message('debug', '[PayPal/Cronjob] Error details: ' . $e->getTraceAsString());
		}

        
    }

    private function sendSubscriptionSuspendedEmail($subscription)
    {
        $package = $this->PackageModel->find($subscription['package_id']);

        $data = [
            'subscription_id' => $subscription['subscription_reference'],
            'package_name' => $package['package_name'],
            'status' => 'suspended',
            'suspension_date' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
            'amount' => $subscription['amount_paid'],
            'currency' => $subscription['currency']
        ];

		try {
			$emailService = new \App\Libraries\EmailService();
			$result = $emailService->sendSubscriptionEmail([
				'userID' => $subscription['user_id'],
				'template' => 'subscription_suspended',
				'data' => $data
			]);

            if($result) {
                log_message('info', "[PayPal/Cronjob] Sent subscription suspended email for subscription: {$subscription['subscription_reference']}");
			}
		} catch (\Exception $e) {
			// Log the error
			log_message('error', '[PayPal/Cronjob] Failed to send subscription email: ' . $e->getMessage());
			
			// You might want to log additional details
			log_message('debug', '[PayPal/Cronjob] Error details: ' . $e->getTraceAsString());
		} catch (\Error $e) {
			// Catch PHP errors
			log_message('error', '[PayPal/Cronjob] Critical error while sending subscription email: ' . $e->getMessage());
			log_message('debug', '[PayPal/Cronjob] Error details: ' . $e->getTraceAsString());
		}        
    }
}
