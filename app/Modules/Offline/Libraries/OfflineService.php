<?php

namespace App\Modules\Offline\Libraries;

use App\Modules\Offline\Config\Offline as OfflineConfig;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPaymentModel;
use App\Models\PackageModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;

class OfflineService
{
    protected $config;
    protected $SubscriptionModel;
    protected $PaymentModel;
    protected $PackageModel;
    protected $UserModel;

    public function __construct()
    {
        $this->config = new OfflineConfig();
        $this->SubscriptionModel = new SubscriptionModel();
        $this->PaymentModel = new SubscriptionPaymentModel();
        $this->PackageModel = new PackageModel();
        $this->UserModel = new UserModel();
    }

    public function moduleDetails()
    {
        return $this->config->adminMenu;
    }

    public function newSubscription($packageId)
    {
        try {
            log_message('info', '[Offline Service] Starting new subscription process');

            $referenceId = 'O-' . strtoupper(uniqid());
            
            $package = $this->PackageModel->find($packageId);

            if (!$package) {
                log_message('error', '[Offline Service] Package not found: ' . $packageId);
                throw new \Exception('Package not found');
            }
            log_message('info', '[Offline Service] Package found: ' . json_encode($package));

            $user = auth()->user();
            
            if (!$user) {
                log_message('error', '[Offline Service] User not authenticated');
                throw new \Exception('User not authenticated');
            }
            log_message('info', '[Offline Service] User authenticated: ' . $user->id);

            $transactionId = $this->generateTransactionId(true);

            $subscriptionData = [
                'user_id' => $user->id,
                'package_id' => $packageId,
                'subscription_status' => 'pending',
                'payment_method' => 'Offline',
                'subscription_reference' => $referenceId,
                'start_date' => Time::now()->setTimezone('UTC'),
                'amount_paid' => $package['price'],
                'currency' => $package['currency'] ?? 'USD',
                'payment_status' => 'pending',
                'billing_cycle' => $package['validity_duration'], 
                'billing_period' => (int)$package['validity'],
                'transaction_id' => $transactionId,
                'transaction_token' => base_url('payment-options/offline/payment/' . $referenceId)
            ];
            log_message('info', '[Offline Service] Subscription data prepared: ' . json_encode($subscriptionData));

            $subscriptionId = $this->SubscriptionModel->insert($subscriptionData);
            if (!$subscriptionId) {
                log_message('error', '[Offline Service] Failed to insert subscription. Last error: ' . print_r($this->SubscriptionModel->errors(), true));
                throw new \Exception('Failed to create subscription');
            }
            log_message('info', '[Offline Service] Subscription created with ID: ' . $subscriptionId);

            // Create a pending payment
            $paymentData = [
                'subscription_id' => $referenceId,
                'transaction_id' => $transactionId,
                'amount' => $package['price'],
                'currency' => $package['currency'] ?? 'USD',
                'payment_status' => 'pending',
                'payment_date' => Time::now()->setTimezone('UTC'),
                'payment_method' => 'Offline',
            ];
            log_message('info', '[Offline Service] Payment data prepared: ' . json_encode($paymentData));

            $paymentId = $this->PaymentModel->insert($paymentData);
            if (!$paymentId) {
                log_message('error', '[Offline Service] Failed to insert payment. Last error: ' . print_r($this->PaymentModel->errors(), true));
                // If payment creation fails, delete the subscription
                $this->SubscriptionModel->delete($subscriptionId);
                throw new \Exception('Failed to create payment record');
            }

            // Send subscription email
            try {

                // Check if transaction_id has completed payment reference contains the separator
                if (isset($transactionId) && strpos($transactionId, ':') !== false) {
                    // New format: package_id:user_id
                    list($paymentReference, $pendingID) = explode(':', $transactionId);
                } else {
                    // Handle the case where transaction_id is not set or doesn't contain the separator
                    $paymentReference = $transactionId ?? null;
                    $pendingID = null;
                }
                
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
                        'payment_method' => 'Offline',
                        'payment_reference' => $paymentReference
                    ];

                    // Send subscription created email
                    log_message('debug', '[Offline/OfflineService] Email Data: ' . json_encode($emailData));

                    try {
                        $emailService = new \App\Libraries\EmailService();
                        $result = $emailService->sendSubscriptionEmail([
                            'userID' => $user->id,
                            'template' => 'subscription_created',
                            'data' => $emailData,
                        ]);

                        if($result) {
                            log_message('debug', '[Offline/OfflineService] Successfully sent subscription created email!');
                        }
                    } catch (\Exception $e) {
                        // Log the error
                        log_message('error', '[Offline/OfflineService] Failed to send subscription email: ' . $e->getMessage());
                        
                        // You might want to log additional details
                        log_message('debug', '[Offline/OfflineService] Error details: ' . $e->getTraceAsString());
                    } catch (\Error $e) {
                        // Catch PHP errors
                        log_message('error', '[Offline/OfflineService] Critical error while sending subscription email: ' . $e->getMessage());
                        log_message('debug', '[Offline/OfflineService] Error details: ' . $e->getTraceAsString());
                    }
                } else {
                    log_message('error', '[Offline/OfflineService] Failed to send subscription email: User not found');
                }
            } catch (\Exception $e) {
                log_message('error', '[Offline/OfflineService] Error sending subscription email: ' . $e->getMessage());
            }

            log_message('info', '[Offline Service] Payment created with ID: ' . $paymentId);
            log_message('info', '[Offline Service] New subscription process completed successfully');
            return base_url('payment-options/offline/payment/' . $referenceId);
        } catch (\Exception $e) {
            log_message('error', '[Offline Service] New subscription failed: ' . $e->getMessage());
            log_message('error', '[Offline Service] Stack trace: ' . $e->getTraceAsString());
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
            return false;
        }
    }

    public function updateSubscriptionPayment($subscriptionReference, $referenceId)
    {
        try {
            $pendingInvoice = $this->PaymentModel->where('payment_status', 'pending')
                                                 ->where('subscription_id', $subscriptionReference)
                                                 ->first();
            $subscription = $this->SubscriptionModel->where('subscription_reference', $subscriptionReference)->first();
            if (!$subscription) {
                throw new \Exception('Subscription not found');
            }

            log_message('debug', '[Offline Service] Start saving user input of reference ID in the subscription: ' . json_encode($subscription));


            // Update subscription payment table
            $paymentData = [
                'transaction_id' => str_replace('PENDING_PAYMENT', $referenceId, $pendingInvoice['transaction_id']),
                'payment_status' => 'pending',
                'payment_date' => Time::now()->setTimezone('UTC')
            ];

            log_message('info', '[Offline Service] Attempting to update payment data: ' . json_encode($paymentData));

            $updated = $this->PaymentModel->where('id', $pendingInvoice['id'])->set($paymentData)->update();
            
            if (!$updated) {
                log_message('error', '[Offline Service] Failed to update payment. Last error: ' . print_r($this->PaymentModel->errors(), true));
                throw new \Exception('Failed to update payment');
            }

            // Update subscription table
            $subscriptionData = [
                'transaction_id' => str_replace('PENDING_PAYMENT', $referenceId, $subscription['transaction_id']),
            ];

            log_message('info', '[Offline Service] Attempting to update subscription data: ' . json_encode($paymentData));

            $updated = $this->SubscriptionModel->where('subscription_reference', $subscription['subscription_reference'])->set($subscriptionData)->update();

            if (!$updated) {
                log_message('error', '[Offline Service] Failed to update subscription. Last error: ' . print_r($this->SubscriptionModel->errors(), true));
                throw new \Exception('Failed to update payment');
            }

            // Add notification to Admin to validate offline payment receipt
            $notificationMessage = 'An offline payment receipt has been submitted';
            $notificationType = 'subscription_payment';
            $url = base_url('payment-options/offline/admin');
            $recipientUserId = 1;
            add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

            return true;
        } catch (\Exception $e) {
            log_message('error', '[Offline Service] Update subscription payment failed: ' . $e->getMessage());
            return false;
        }
    }

    public function cancelSubscription($subscriptionId, $reason = 'Cancelled by user')
    {
        try {
            $subscription = $this->SubscriptionModel->where('subscription_reference', $subscriptionId)->first();
            if (!$subscription) {
                throw new \Exception('Subscription not found');
            }

            $updated = $this->SubscriptionModel->cancelSubscription(
                            $subscription['subscription_reference'],
                            $reason
                        );

            if (!$updated) {
                throw new \Exception('Failed to cancel subscription');
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
                    log_message('info', '[OfflineService] Successfully sent subscription cancelled email!');
                }
            } catch (\Exception $e) {
                // Log the error
                log_message('error', '[OfflineService] Failed to send subscription cancelled emaill: ' . $e->getMessage());
                
                // You might want to log additional details
                log_message('debug', '[OfflineService] Error details: ' . $e->getTraceAsString());
            } catch (\Error $e) {
                // Catch PHP errors
                log_message('error', '[OfflineService] Critical error while sending subscription cancelled email: ' . $e->getMessage());
                log_message('debug', '[OfflineService] Error details: ' . $e->getTraceAsString());
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', '[Offline Service] Cancel subscription failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function suspendSubscription($subscriptionId, $reason = 'Suspended by system')
    {
        try {
            $subscription = $this->SubscriptionModel->where('subscription_reference', $subscriptionId)->first();
            if (!$subscription) {
                throw new \Exception('Subscription not found');
            }

            $updateData = [
                'subscription_status' => 'suspended',
            ];

            $updated = $this->SubscriptionModel->update($subscription['id'], $updateData);

            if (!$updated) {
                throw new \Exception('Failed to suspend subscription');
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', '[Offline Service] Suspend subscription failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function activateSubscription($subscriptionId, $reason = 'Activated by system')
    {
        try {
            $subscription = $this->SubscriptionModel->where('subscription_reference', $subscriptionId)->first();
            if (!$subscription) {
                throw new \Exception('Subscription not found');
            }

            $updateData = [
                'subscription_status' => 'active',
            ];

            $updated = $this->SubscriptionModel->update($subscription['id'], $updateData);

            if (!$updated) {
                throw new \Exception('Failed to activate subscription');
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', '[Offline Service] Activate subscription failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getOfflinePayments()
    {
        $subscription = $this->SubscriptionModel->where('payment_method', 'Offline')->findAll();
        
        $payments = [];
        
        foreach ($subscription as $sub) {
            $subPayments = $this->PaymentModel->where('subscription_id', $sub['subscription_reference'])->findAll();
            
            // Add user_id to each payment
            foreach ($subPayments as &$payment) {
                $payment['user_id'] = $sub['user_id'];
                $user = $this->UserModel->find($sub['user_id']);
                $payment['username'] = $user->username;
                $payment['user_email'] = $user->email;
            }
            
            $payments = array_merge($payments, $subPayments);
        }

        return $payments;
    }

    public function updatePaymentStatus($paymentId, $newStatus, $refundAmount = null)
    {
        try {
            $selectedPayment = $this->PaymentModel->find($paymentId);
            
            if (!$selectedPayment) {
                throw new \Exception('Payment details not found');
            }
    
            $subscriptionReference = $selectedPayment['subscription_id'];
    
            $subscription = $this->SubscriptionModel->where('subscription_reference', $subscriptionReference)->first();
    
            if (!$subscription) {
                throw new \Exception('Subscription not found');
            }
    
            log_message('debug', '[Offline Service] Start updating payment status for subscription: ' . json_encode($subscription));
    
            $currentTime = $this->getCurrentUtcTime();
    
            switch ($newStatus) {
                case 'completed':
                    return $this->handleCompletedPayment($subscription, $selectedPayment, $currentTime);
                
                case 'pending':
                    return $this->handlePendingPayment($selectedPayment, $subscriptionReference);
                
                case 'refunded':
                    return $this->handleRefundedPayment($subscription, $selectedPayment, $refundAmount, $currentTime);
                
                default:
                    throw new \Exception('Invalid payment status');
            }
        } catch (\Exception $e) {
            log_message('error', '[Offline Service] Update payment status failed: ' . $e->getMessage());
            return false;
        }
    }

    private function handleCompletedPayment($subscription, $selectedPayment, $currentTime)
    {
        $isInitialPayment = $subscription['subscription_status'] !== 'active';
        $startDate = $isInitialPayment ? $currentTime : $subscription['end_date'];
        
        $endDate = $this->calculateEndDate(
            $startDate,
            $subscription['billing_cycle'],
            (int)$subscription['billing_period']
        );
        
        $nextPaymentDate = $this->nextPaymentSchedule($endDate);
        
        $updatedSubscriptionData = [
            'subscription_status' => 'active',
            'transaction_id' => $selectedPayment['transaction_id'],
            'transaction_token' => null,
            'payment_status' => 'completed',
            'start_date' => $isInitialPayment ? $currentTime : $subscription['start_date'],
            'end_date' => $endDate,
            'next_payment_date' => $nextPaymentDate,
            'last_payment_date' => $currentTime,
            'sent_expiring_reminder' => 'no',
            'amount_paid' => $subscription['amount_paid'],
        ];
        
        $this->SubscriptionModel->update($subscription['id'], $updatedSubscriptionData);
        
        $paymentData = [
            'subscription_id' => $subscription['subscription_reference'],
            'amount' => $subscription['amount_paid'],
            'currency' => $subscription['currency'],
            'payment_status' => 'completed',
            'payment_date' => $currentTime,
            'token' => null
        ];
        
        $this->PaymentModel->update($selectedPayment['id'], $paymentData);
        
        $this->initializePaymentReceipt($subscription, $selectedPayment, $currentTime, $nextPaymentDate);
        
        return true;
    }

    private function handlePendingPayment($selectedPayment, $subscriptionReference)
    {
        $updated = $this->PaymentModel->update($selectedPayment['id'], ['payment_status' => 'pending']);
        
        if ($updated) {
            log_message('info', "[Offline Service] Payment status updated to pending for subscription: {$subscriptionReference}");
        } else {
            log_message('error', "[Offline Service] Error updating payment status to pending: " . print_r($this->PaymentModel->errors(), true));
        }

        return $updated;
    }

    private function handleRefundedPayment($subscription, $selectedPayment, $refundAmount, $currentTime)
    {
        $isFullRefund = (float)$refundAmount >= (float)$selectedPayment['amount'];
        
        $refundStatus = $isFullRefund ? 'refunded' : 'partially_refunded';
        
        $updatedPaymentData = [
            'payment_status' => $refundStatus,
            'refund_id' => strtoupper(uniqid()) . '_' . time(),
            'refund_amount' => $refundAmount,
            'refund_currency' => $subscription['currency'],
            'refund_date' => $currentTime,
            'is_partial_refund' => $isFullRefund ? 0 : 1
        ];
        
        $this->PaymentModel->update($selectedPayment['id'], $updatedPaymentData);

        // Update subscription status
        $updatedSubscriptionData = [
            'subscription_status' => $isFullRefund ? 'cancelled' : 'active',
            'payment_status' => $isFullRefund ? 'refunded' : 'partially_refunded',
            'cancellation_reason' => $isFullRefund ? 'Refunded in full' : '',
        ];

        $this->SubscriptionModel->update($subscription['id'], $updatedSubscriptionData);
        
        $this->sendRefundEmail($subscription, $selectedPayment, $refundAmount, $isFullRefund);
        
        return true;
    }

    private function initializePaymentReceipt($subscription, $selectedPayment, $currentTime, $nextPaymentDate)
    {
        try {
            $UserModel = new \App\Models\UserModel();
            $user = $UserModel->find($subscription['user_id']);
            $package = $this->PackageModel->find($subscription['package_id']);
        
            if ($user && $package) {
                $emailData = [
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'transaction_id' => $selectedPayment['transaction_id'] ?? '',
                    'amount' => $subscription['amount_paid'] ?? 0,
                    'currency' => $subscription['currency'] ?? 'USD',
                    'payment_date' => $currentTime ?? '',
                    'subscription_id' => $subscription['id'] ?? '',
                    'package_name' => $package['package_name'] ?? '',
                    'next_billing_time' => $nextPaymentDate ?? 'N/A',
                    'payment_method' => 'Offline'
                ];

                // Send payment receipt email
                log_message('debug', '[Offline/OfflineService] Email Data: ' . json_encode($emailData));
                
                // try {
                    $emailService = new \App\Libraries\EmailService();
                    $emailService->sendPaymentReceipt([
                        'userID' => $user->id,
                        'data' => $emailData,
                    ]);
                // } catch (\Exception $e) {
                //     // Log the error
                //     log_message('error', '[Offline/OfflineService] Failed to send subscription payment receipt email: ' . $e->getMessage());
                    
                //     // You might want to log additional details
                //     log_message('debug', '[Offline/OfflineService] Error details: ' . $e->getTraceAsString());
                // } catch (\Error $e) {
                //     // Catch PHP errors
                //     log_message('error', '[Offline/OfflineService] Critical error while sending subscription payment receipt email: ' . $e->getMessage());
                //     log_message('debug', '[Offline/OfflineService] Error details: ' . $e->getTraceAsString());
                // }
            } else {
                log_message('error', '[Offline/OfflineService] Failed to send payment receipt: User or package not found');
            }
        } catch (\Exception $e) {
            log_message('error', '[Offline/OfflineService] Error sending payment receipt: ' . $e->getMessage());
        }
    }

    private function sendRefundEmail($subscription, $selectedPayment, $refundAmount, $isFullRefund)
    {
        try {
            $UserModel = new \App\Models\UserModel();
            $user = $UserModel->find($subscription['user_id']);
            $package = $this->PackageModel->find($subscription['package_id']);
        
            if ($user && $package) {
                $emailData = [
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'refund_date' => $this->getCurrentUtcTime(),
                    'amount' => $refundAmount,
                    'subscription_id' => $subscription['id'],
                    'currency' => $subscription['currency'],
                    'package_name' => $package['package_name'],
                    'is_full_refund' => $isFullRefund,
                    'cancellation_date' => $isFullRefund ? $this->getCurrentUtcTime() : ''
                ];

                // Send payment refund email
                log_message('debug', '[Offline/OfflineService] Email Data: ' . json_encode($emailData));

                try {
                    $emailService = new \App\Libraries\EmailService();
                    $result = $emailService->sendSubscriptionEmail([
                        'userID' => $user->id,
                        'template' => 'payment_refunded',
                        'data' => $emailData,
                    ]);

                    if($result) {
                        log_message('info', '[Offline/OfflineService] Successfully sent subscription payment refund email!');
                    }
                } catch (\Exception $e) {
                    // Log the error
                    log_message('error', '[Offline/OfflineService] Failed to send subscription email: ' . $e->getMessage());
                    
                    // You might want to log additional details
                    log_message('debug', '[Offline/OfflineService] Error details: ' . $e->getTraceAsString());
                } catch (\Error $e) {
                    // Catch PHP errors
                    log_message('error', 'Critical error while sending subscription email: ' . $e->getMessage());
                    log_message('debug', '[Offline/OfflineService] Error details: ' . $e->getTraceAsString());
                }

            } else {
                log_message('error', '[Offline/OfflineService] Failed to send refund email: User or package not found');
            }
        } catch (\Exception $e) {
            log_message('error', '[Offline/OfflineService] Error sending refund email: ' . $e->getMessage());
        }
    }

    private function generateUniqueReference()
    {
        return 'OFFLINE_' . uniqid() . '_' . time();
    }

    public function nextPaymentSchedule($endDate)
    {
        $endDateObj = Time::parse($endDate);
        return $endDateObj->addMinutes(1);
    }

    private function calculateEndDate(string $startDate, string $billingCycle, int $billingPeriod): string
	{
		// Parse the start date in UTC
		$date = Time::parse($startDate, 'UTC');
		
		switch ($billingCycle) {
			case 'day':
				$date = $date->addDays($billingPeriod);
				break;
			case 'week':
				$date = $date->addDays($billingPeriod * 7);
				break;
			case 'month':
				$date = $date->addMonths($billingPeriod);
				break;
			case 'year':
				$date = $date->addYears($billingPeriod);
				break;
			case 'lifetime':
				$date = $date->addYears(99); // Set far future date for lifetime
				break;
		}
		
		// Return the new date as a formatted string in UTC
		return $date->toDateTimeString();
	}
	
	/**
     * Calculate next payment date based on billing cycle
     */
    private function calculateNextPaymentDate(string $startDate, string $billingCycle, int $billingPeriod): string
    {
        // Parse the start date in UTC
        $date = Time::parse($startDate, 'UTC');
        
        switch ($billingCycle) {
            case 'day':
                $date = $date->addDays($billingPeriod);
                break;
            case 'week':
                $date = $date->addDays($billingPeriod * 7);
                break;
            case 'month':
                $date = $date->addMonths($billingPeriod);
                break;
            case 'year':
                $date = $date->addYears($billingPeriod);
                break;
            case 'lifetime':
                $date = $date->addYears(99); // Set far future date for lifetime
                break;
        }
        
        // Return the new date as a formatted string in UTC
        return $date->toDateTimeString();
    }

    /**
     * Get current UTC datetime
     */
    private function getCurrentUtcTime(): string
    {
        return Time::now()->setTimezone('UTC');
    }

    public function generateTransactionId($is_pending = false): string
    {
        return 'INV-' . strtoupper(generateApiKey(3) . ':' . ($is_pending ? 'PENDING_PAYMENT' : uniqid()));
    }
}
