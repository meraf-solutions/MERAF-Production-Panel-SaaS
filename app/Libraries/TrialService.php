<?php

namespace App\Libraries;

use App\Models\SubscriptionModel;
use App\Models\SubscriptionPaymentModel;
use App\Models\PackageModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;

class TrialService
{
    protected $lastErrors;
    protected $SubscriptionModel;
    protected $PaymentModel;
    protected $PackageModel;
    protected $UserModel;

    public function __construct()
    {
        $this->SubscriptionModel = new SubscriptionModel();
        $this->PaymentModel = new SubscriptionPaymentModel();
        $this->PackageModel = new PackageModel();
        $this->UserModel = new UserModel();
        $this->lastErrors = [];
    }
    
    public function moduleDetails()
    {
        $menu = [
		    'category' =>'misc',
		    'title' => null,
		    'logo' => null,
		    'url' => null,
		    'config' => null,
			'service_name' => 'TrialService'
        ];
        
        // return $menu;
    }

    public function newSubscription($packageId)
    {
        try {
            log_message('info', '[Trial Service] Starting new trial subscription process');
            
            $package = $this->PackageModel->find($packageId);

            if (!$package) {
                log_message('error', '[Trial Service] Package not found: ' . $packageId);
                // throw new \Exception('Package not found');
                $this->lastErrors = ['package' => 'Trial package not found'];
                return false;
            }
            log_message('info', '[Trial Service] Package found: ' . json_encode($package));

            $user = auth()->user();

            if (!$user) {
                log_message('error', '[Trial Service] User not authenticated');
                // throw new \Exception('User not authenticated');
                $this->lastErrors = ['user' => 'User not authenticated'];
                return false;
            }
            log_message('info', '[Trial Service] User authenticated: ' . $user->id);

            // Start Date
            $startDate = Time::now('UTC');

            // End date calculation
            switch ($package['validity_duration']) {
                case 'day':
                    $endDate = $startDate->addDays($package['validity']);
                    break;
                case 'week':
                    $endDate = $startDate->addDays($package['validity'] * 7);
                    break;
                case 'month':
                    $endDate = $startDate->addMonths($package['validity']);
                    break;
                case 'year':
                    $endDate = $startDate->addYears($package['validity']);
                    break;
                case 'lifetime':
                    $endDate = $startDate->addYears(99); // Set far future date
                    break;
                default:
                    $endDate = $startDate; // fallback, same day
                    break;
            }

            // Format dates as string
            $startDateUTC = $startDate->toDateTimeString();
            $endDateUTC   = $endDate->toDateTimeString();

            // Generate transaction ID
            $transactionId = $this->generateTransactionId(true);

            // Generate reference ID
            $referenceId = 'T-' . strtoupper(uniqid());

            $subscriptionData = [
                'user_id' => $user->id,
                'package_id' => $packageId,
                'subscription_status' => 'active',
                'payment_status' => 'completed',
                'payment_method' => 'Trial',
                'subscription_reference' => $referenceId,
                'currency' => $package['currency'] ?? 'USD',
                'amount_paid' => $package['price'],
                'billing_cycle' => $package['validity_duration'], 
                'billing_period' => (int)$package['validity'],
                'transaction_id' => $transactionId,
                'start_date' => $startDateUTC,
                'end_date' => $endDateUTC,
                'last_payment_date' => $startDateUTC,
            ];
            log_message('info', '[Trial Service] Trial subscription data prepared: ' . json_encode($subscriptionData));

            $subscriptionId = $this->SubscriptionModel->insert($subscriptionData);
            if (!$subscriptionId) {
                log_message('error', '[Trial Service] Failed to insert trial subscription. Last error: ' . print_r($this->SubscriptionModel->errors(), true));
                // throw new \Exception('Failed to create trial subscription');
                $this->lastErrors = ['subscription' => 'Failed to create trial subscription'];
                return false;
            }
            log_message('info', '[Trial Service] Trial subscription created with ID: ' . $subscriptionId);

            // Create a pending payment
            $paymentData = [
                'subscription_id' => $referenceId,
                'transaction_id' => $transactionId,
                'amount' => $package['price'],
                'currency' => $package['currency'] ?? 'USD',
                'payment_status' => 'completed',
                'payment_date' => Time::now()->setTimezone('UTC'),
                'payment_method' => 'Trial',
            ];
            log_message('info', '[Trial Service] Payment data prepared: ' . json_encode($paymentData));

            $paymentId = $this->PaymentModel->insert($paymentData);
            if (!$paymentId) {
                log_message('error', '[Trial Service] Failed to insert payment. Last error: ' . print_r($this->PaymentModel->errors(), true));
                // If payment creation fails, delete the subscription
                $this->SubscriptionModel->delete($subscriptionId);
                // throw new \Exception('Failed to create payment record');
                $this->lastErrors = ['payment' => 'Failed to create payment record'];
                return false;
            }

            // Send subscription email
            try {
                
                if ($user) {
                    // Prepare data for email
                    $emailData = [
                        'subscription_id' => $subscriptionId,
                        'package_name' => $package['package_name'],
                        'status' => 'pending',
                        'start_time' => $this->getCurrentUtcTime(),
                        'next_billing_time' => null,
                        'amount' => $package['price'],
                        'currency' => $package['currency'] ?? 'USD',
                        'payment_method' => 'Trial',
                        'payment_reference' => $transactionId
                    ];

                    // Send subscription created email
                    log_message('debug', '[Trial/TrialService] Email Data: ' . json_encode($emailData));

                    try {
                        $emailService = new \App\Libraries\EmailService();
                        $result = $emailService->sendSubscriptionEmail([
                            'userID' => $user->id,
                            'template' => 'subscription_created',
                            'data' => $emailData,
                        ]);

                        if($result) {
                            log_message('debug', '[Trial/TrialService] Successfully sent trial subscription created email!');
                        }
                    } catch (\Exception $e) {
                        // Log the error
                        log_message('error', '[Trial/TrialService] Failed to send trial subscription email: ' . $e->getMessage());
                        
                        // You might want to log additional details
                        log_message('debug', '[Trial/TrialService] Error details: ' . $e->getTraceAsString());

                        $this->lastErrors = ['email' => 'Failed to send trial subscription email: ' . $e->getMessage()];
                        return false;
                    } catch (\Error $e) {
                        // Catch PHP errors
                        log_message('error', '[Trial/TrialService] Critical error while sending trial subscription email: ' . $e->getMessage());
                        log_message('debug', '[Trial/TrialService] Error details: ' . $e->getTraceAsString());

                        $this->lastErrors = ['email' => 'Critical error while sending trial subscription email: ' . $e->getMessage()];
                        return false;
                    }
                } else {
                    log_message('error', '[Trial/TrialService] Failed to send trial subscription email: User not found');

                    $this->lastErrors = ['email' => 'Failed to send trial subscription email: User not found'];
                    return false;
                }
            } catch (\Exception $e) {
                log_message('error', '[Trial/TrialService] Error sending trial subscription email: ' . $e->getMessage());

                $this->lastErrors = ['email' => 'Error sending trial subscription email: ' . $e->getMessage()];
                return false;
            }

            log_message('info', '[Trial Service] New trial subscription process completed successfully');
            return true;

        } catch (\Exception $e) {
            log_message('error', '[Trial Service] New trial subscription failed: ' . $e->getMessage());
            log_message('error', '[Trial Service] Stack trace: ' . $e->getTraceAsString());
            
            $this->lastErrors = ['subscription' => 'New trial subscription failed: ' . $e->getMessage()];
            return false;
        }
    }
    
    public function getSubscription($subscriptionId)
    {
        try {
            $subscription = $this->SubscriptionModel->where('subscription_reference', $subscriptionId)->first();
            if (!$subscription) {
                return false;
            }
    
            // Get payment information
            $payments = $this->PaymentModel->where('subscription_id', $subscriptionId)->findAll();
            $completedPayments = array_filter($payments, function($payment) {
                return $payment['payment_status'] === 'completed';
            });
            $failedPayments = array_filter($payments, function($payment) {
                return $payment['payment_status'] === 'failed';
            });
            $lastCompletedPayment = end($completedPayments);
    
            // Prepare the result
            $result = (object) $subscription;
            $result->status = $subscription['subscription_status'];
            $result->plan_id = $subscription['subscription_reference'];
            $result->billing_info = (object) [
                'cycle_executions' => [
                    (object) ['cycles_completed' => count($completedPayments)]
                ],
                'last_payment' => (object) [
                    'amount' => (object) [
                        'currency_code' => $lastCompletedPayment ? $lastCompletedPayment['currency'] : null,
                        'value' => $lastCompletedPayment ? $lastCompletedPayment['amount'] : null,
                    ],
                    'time' => $lastCompletedPayment ? $lastCompletedPayment['payment_date'] : null,
                ],
                'failed_payments_count' => count($failedPayments),
            ];
            
            log_message('debug', '[Offline Service] Get subscription returned data: ' . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            log_message('error', '[Offline Service] Get subscription failed: ' . $e->getMessage());

            $this->lastErrors = ['subscription' => 'Get subscription failed: ' . $e->getMessage()];
            return false;
        }
    }

    public function cancelSubscription($subscriptionId, $reason = 'Cancelled by user')
    {
        try {
            $subscription = $this->SubscriptionModel->where('subscription_reference', $subscriptionId)->first();
            if (!$subscription) {
                // throw new \Exception('Trial subscription not found');
                $this->lastErrors = ['subscription' => 'Trial subscription not found'];
                return false;
            }

            $updated = $this->SubscriptionModel->cancelSubscription(
                            $subscription['subscription_reference'],
                            $reason
                        );

            if (!$updated) {
                // throw new \Exception('Failed to cancel trial subscription');
                $this->lastErrors = ['subscription' => 'Failed to cancel trial subscription'];
                return false;
            }

            // Send subscription cancelled email
            // Prepare data for email
            $user = $this->UserModel->find($subscription['user_id']);
            $package = $this->PackageModel->find($subscription['package_id']);
            
            $emailData = [
                'subscription_id' => $subscriptionId,
                'package_name' => $package['package_name'],
                'status' => 'cancelled',
                'payment_date' => $this->getCurrentUtcTime(),
                'amount' => $subscription['amount_paid'],
                'currency' => $subscription['currency']
            ];

            try {
                $emailService = new \App\Libraries\EmailService();
                $result = $emailService->sendSubscriptionEmail([
                    'userID' => $subscription['user_id'],
                    'template' => 'subscription_cancelled',
                    'data' => $emailData
                ]);

                if($result) {
                    log_message('info', '[TrialService] Successfully sent trial subscription cancelled email!');
                }
            } catch (\Exception $e) {
                // Log the error
                log_message('error', '[TrialService] Failed to send trial subscription cancelled emaill: ' . $e->getMessage());
                
                // You might want to log additional details
                log_message('debug', '[TrialService] Error details: ' . $e->getTraceAsString());

                $this->lastErrors = ['subscription' => 'Get subscription failed: ' . $e->getMessage()];
                return false;
            } catch (\Error $e) {
                // Catch PHP errors
                log_message('error', '[TrialService] Critical error while sending trial subscription cancelled email: ' . $e->getMessage());
                log_message('debug', '[TrialService] Error details: ' . $e->getTraceAsString());

                $this->lastErrors = ['subscription' => 'Get subscription failed: ' . $e->getMessage()];
                return false;
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', '[Trial Service] Cancel trial subscription failed: ' . $e->getMessage());
            // throw $e;
            $this->lastErrors = ['subscription' => 'Cancel trial subscription failed: ' .  $e->getMessage()];
            return false;
        }
    }

    public function suspendSubscription($subscriptionId, $reason = 'Suspended by system')
    {
        try {
            $subscription = $this->SubscriptionModel->where('subscription_reference', $subscriptionId)->first();
            if (!$subscription) {
                throw new \Exception('Trial subscription not found');
                $this->lastErrors = ['subscription' => 'Trial subscription not found'];
                return false;
            }

            $updateData = [
                'subscription_status' => 'suspended',
            ];

            $updated = $this->SubscriptionModel->update($subscription['id'], $updateData);

            if (!$updated) {
                throw new \Exception('Failed to suspend trial subscription');
                $this->lastErrors = ['subscription' => 'Failed to suspend trial subscription'];
                return false;
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', '[Trial Service] Suspend trial subscription failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function generateUniqueReference()
    {
        return 'Trial_' . uniqid() . '_' . time();
    }
	
    /**
     * Get current UTC datetime
     */
    private function getCurrentUtcTime(): string
    {
        return Time::now()->setTimezone('UTC');
    }

    public function generateTransactionId(): string
    {
        return 'TRIAL-' . strtoupper(uniqid());
    }

	/**
     * Get last error messages
     *
     * @return array
     */
    public function getLastErrors(): array
    {
        return $this->lastErrors;
    }
}
