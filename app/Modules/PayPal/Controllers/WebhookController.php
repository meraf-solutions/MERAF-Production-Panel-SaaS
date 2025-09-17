<?php
// 1. special-offer when upgrading
// 2. expiring email notification
// 3. PayPal_Webhook_Event_Payment_Failed (retry payment dates 3 tries for consecutive days)
// 4. reactivate cancelled subscription until remaining date
namespace App\Modules\PayPal\Controllers;

use CodeIgniter\I18n\Time;
use App\Controllers\Home;
use App\Modules\PayPal\Libraries\PayPalService;
use App\Modules\PayPal\Filters\PayPalWebhookFilter;
use DateTime;
use DateTimeZone;

class WebhookController extends Home
{
    protected $PayPalService;

    public function __construct()
    {
        parent::__construct();
        
        // Set the timezone to UTC
        setMyTimezone('UTC');
        
        $this->response = service('response');
        $this->PayPalService = new PayPalService();

        // Set JSON response type for all responses
        $this->response->setContentType('application/json');
    }

    /**
     * Get current UTC datetime
     */
    private function getCurrentUtcTime(): string
    {
        return Time::now()->setTimezone('UTC');
    }

    /**
     * Parse custom_id to extract package_id and user_id
     * Handles both old format (just package_id) and new format (package_id:user_id)
     */
    private function parseCustomId($customId)
    {
        if (empty($customId)) {
            return [null, null];
        }

        // Check if custom_id contains the separator
        if (strpos($customId, ':') !== false) {
            // New format: package_id:user_id
            list($packageId, $userId) = explode(':', $customId);
            return [(int)$packageId, (int)$userId];
        }

        // Old format: just package_id
        return [(int)$customId, null];
    }

    public function handle()
    {
        try {
            // Get webhook data from filter
            $webhookData = PayPalWebhookFilter::getWebhookData($this->request);

            // Verify webhook data was validated by filter
            if (!$webhookData || !$webhookData->verified) {
                log_message('error', '[PayPal WebhookController] Request not verified by webhook filter');
                return $this->response
                    ->setStatusCode(401)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Unauthorized webhook request'
                    ]);
            }

            log_message('info', '[PayPal WebhookController] Request verified by the webhook filter as '. (true ? 'valid' : 'invalid'));

            // Get webhook event data
            $event = $webhookData->body;
            
            // Log the event type
            log_message('info', '[PayPal WebhookController] Processing webhook event: ' . ($event->event_type ?? 'unknown'));

            // Check if this is a test request
            $isTestRequest = $webhookData->test ?? false;

            // Trigger webhook received event
            \CodeIgniter\Events\Events::trigger('paypal_webhook_received', $event);

            try {
                // Handle different event types
                $result = match ($event->event_type ?? '') {
                    'BILLING.SUBSCRIPTION.CREATED' => $this->handleSubscriptionCreated($event, $isTestRequest),
                    'BILLING.SUBSCRIPTION.ACTIVATED' => $this->handleSubscriptionActivated($event, $isTestRequest),
                    'BILLING.SUBSCRIPTION.UPDATED' => $this->handleSubscriptionUpdated($event, $isTestRequest),
                    'BILLING.SUBSCRIPTION.CANCELLED' => $this->handleSubscriptionCancelled($event, $isTestRequest),
                    'BILLING.SUBSCRIPTION.SUSPENDED' => $this->handleSubscriptionSuspended($event, $isTestRequest),
                    'BILLING.SUBSCRIPTION.PAYMENT.FAILED' => $this->handlePaymentFailed($event, $isTestRequest),
                    'PAYMENT.SALE.COMPLETED' => $this->handlePaymentCompleted($event, $isTestRequest),
					'PAYMENT.SALE.DENIED' => $this->handlePaymentDenied($event, $isTestRequest),
                    'PAYMENT.SALE.PENDING' => $this->handlePaymentPending($event, $isTestRequest),
					'PAYMENT.SALE.REFUNDED' => $this->handlePaymentRefunded($event, $isTestRequest),
                    default => [
                        'success' => true,
                        'message' => 'Event acknowledged but not processed',
                        'event_type' => $event->event_type ?? 'unknown'
                    ]
                };

                // Log successful processing
                log_message('info', '[PayPal WebhookController] Successfully processed event: ' . json_encode($result));
                
                // Trigger webhook processed event
                \CodeIgniter\Events\Events::trigger('paypal_webhook_processed', $event, $result);

                return $this->response->setJSON($result);

            } catch (\Exception $e) {
                // Log the error with full details
                log_message('error', '[PayPal WebhookController] Error processing webhook: ' . $e->getMessage());
                log_message('error', '[PayPal WebhookController] Stack trace: ' . $e->getTraceAsString());

                // Trigger webhook error event
                \CodeIgniter\Events\Events::trigger('paypal_webhook_error', $event, $e->getMessage());

                return $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Internal server error',
                        'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'An error occurred'
                    ]);
            }

        } catch (\Exception $e) {
            // Log any unexpected errors
            log_message('critical', '[PayPal WebhookController] Critical error: ' . $e->getMessage());
            log_message('critical', '[PayPal WebhookController] Stack trace: ' . $e->getTraceAsString());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Critical error occurred',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'An error occurred'
                ]);
        }
    }

    private function handleSubscriptionCreated($event, $isTestRequest)
    {
        log_message('info', '[PayPal WebhookController] Processing subscription created event');
        log_message('debug', '[PayPal WebhookController] Event data: ' . json_encode($event));
        
        if ($isTestRequest) {
            return [
                'success' => true,
                'message' => 'Test subscription created event processed'
            ];
        }
        
        try {
            $this->db->transBegin();
            
            // Parse custom_id to get package_id and user_id
            list($packageId, $userId) = $this->parseCustomId($event->resource->custom_id ?? '');
            
            if (!$packageId) {
                throw new \Exception('Invalid custom_id format. Expected package_id or package_id:user_id');
            }

            // If user_id is not provided in custom_id, try to get it from the authenticated user
            if (!$userId) {
                $userId = auth()->id();
                log_message('debug', '[PayPal WebhookController] Using authenticated user_id: ' . $userId);
            }

            // Get package details for currency and amount
            $package = $this->PackageModel->find($packageId);
            if (!$package) {
                throw new \Exception('Invalid package ID: ' . $packageId);
            }

            // Get subscription details from PayPal to get complete information
            $subscriptionDetails = $this->PayPalService->getSubscription($event->resource->id);
            log_message('debug', '[PayPal WebhookController] Full subscription details: ' . json_encode($subscriptionDetails));

            // Calculate initial next payment date based on billing cycle
            $nextPaymentDate = $this->calculateEndDate(
                $this->getCurrentUtcTime(),
                strtolower($package['validity_duration']),
                (int)$package['validity']
            );

            // Get the transaction token
            $baToken = '';
            foreach ($subscriptionDetails->links as $link) {
                if ($link->rel === 'approve') {
                    $baToken = $link->href;
                    break;
                }
            }

            $subscriptionData = [
                'subscription_reference' => $event->resource->id,
                'user_id' => (int)$userId,
                'package_id' => (int)$packageId,
                'subscription_status' => 'pending',
                'payment_status' => 'pending',
                'transaction_token' => $baToken ?? '',
                'payment_method' => 'PayPal',
                'currency' => $subscriptionDetails->billing_info->last_payment->amount->currency_code ?? 
                            $event->resource->amount->currency ?? 
                            $this->adminSettings['packageCurrency'],
                'amount_paid' => $subscriptionDetails->billing_info->last_payment->amount->value ?? 
                               $event->resource->amount->total ?? 
                               $package['price'] ?? '0.00',
                'billing_cycle' => strtolower($package['validity_duration']),
                'billing_period' => (int)$package['validity'],
                'start_date' => $this->getCurrentUtcTime(),
                'next_payment_date' => $nextPaymentDate,
            ];
            
            log_message('debug', '[PayPal WebhookController] Subscription created data: ' . json_encode($subscriptionData));

            // Insert subscription record
            $this->SubscriptionModel->insert($subscriptionData);
            
            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                throw new \Exception('Failed to save subscription data');
            }
            
            $this->db->transCommit();
			
            // Get user details for email
            $user = $this->UserModel->find($userId);
            if ($user) {
                // Prepare data for email
                $emailData = [
                    'subscription_id' => $event->resource->id,
                    'package_name' => $package['package_name'],
                    'status' => 'pending',
                    'start_time' => $this->getCurrentUtcTime(),
                    'next_billing_time' => null,
                    'amount' => '0.00', // Will be updated when payment is received
                    'currency' => $event->resource->amount->currency ?? $this->adminSettings['packageCurrency']
                ];

                log_message('debug', '[PayPal WebhookController] Email Data: ' . json_encode($emailData));

                // Send subscription created email
                try {
                    $emailService = new \App\Libraries\EmailService();
                    $result = $emailService->sendSubscriptionEmail([
                        'userID' => $userId,
                        'template' => 'subscription_created',
                        'data' => $emailData
                    ]);

                    if($result) {
                        log_message('info', '[PayPal WebhookController] Successfully sent subscription created email!');
                    }
                } catch (\Exception $e) {
                    // Log the error
                    log_message('error', '[PayPal WebhookController] Failed to send subscription created email: ' . $e->getMessage());
                    
                    // You might want to log additional details
                    log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                } catch (\Error $e) {
                    // Catch PHP errors
                    log_message('error', '[PayPal WebhookController] Critical error while sending subscription created email: ' . $e->getMessage());
                    log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                }
            }			

            // Trigger subscription created event
            \CodeIgniter\Events\Events::trigger('subscription_created', $subscriptionData);
            
            return [
                'success' => true,
                'message' => 'Subscription created event processed'
            ];
            
        } catch (\Exception $e) {
            log_message('error', '[PayPal WebhookController] Error processing subscription created: ' . $e->getMessage());
            
            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
            }
            
            throw $e;
        }
    }

    private function handleSubscriptionActivated($event, $isTestRequest)
    {
        log_message('info', '[PayPal WebhookController] Processing subscription activated event');
        
        if ($isTestRequest) {
            return [
                'success' => true,
                'message' => 'Test subscription activated event processed'
            ];
        }
        
        try {
            $subscriptionId = $event->resource->id;          
            log_message('debug', '[PayPal WebhookController] Subscription activated raw data: '. json_encode($event->resource));
            
            // Update both subscription and payment status
            $this->SubscriptionModel->updateSubscriptionStatus($subscriptionId, 'active');
            // $this->SubscriptionModel->updatePaymentStatus($subscriptionId, 'completed');
			
			// Get subscription and user details for email
            $subscription = $this->SubscriptionModel->getBySubscriptionReference($subscriptionId);
            if ($subscription) {
                $user = $this->UserModel->find($subscription['user_id']);
                $package = $this->PackageModel->find($subscription['package_id']);
                
                if ($user && $package) {

                    // Before informing the user with the new subscription, check if user has existing reactivated subscription which is currently active
                    $currentReactivatedSubscription = $this->SubscriptionModel->where('user_id', $subscription['user_id'])
                                                                            ->where('subscription_status', 'active')
                                                                            ->where('is_reactivated', 'yes')
                                                                            ->first();

                    if($currentReactivatedSubscription) {
                        $this->SubscriptionModel->cancelSubscription(
                            $currentReactivatedSubscription['subscription_reference'],
                            'Reactivated subscription cancelled due to new subscription',
                            true
                        );

                        log_message('debug', '[PayPal WebhookController] Updated the current active Reactivated Subscription to expired: ' . json_encode($currentReactivatedSubscription));
                    }

                    // Prepare data for email
                    $emailData = [
                        'subscription_id' => $subscriptionId,
                        'package_name' => $package['package_name'],
                        'status' => 'active',
                        'start_time' => $subscription['start_date'],
                        'next_billing_time' => $subscription['next_payment_date'],
                        'amount' => $subscription['amount_paid'],
                        'currency' => $subscription['currency'],
                    ];
                    
                    log_message('debug', '[PayPal WebhookController] Email Data: ' . json_encode($emailData));

                    // Send subscription activated email
                    try {
                        $emailService = new \App\Libraries\EmailService();
                        $result = $emailService->sendSubscriptionEmail([
                            'userID' => $subscription['user_id'],
                            'template' => 'subscription_activated',
                            'data' => $emailData
                        ]);

                        if($result) {
                            log_message('info', '[PayPal WebhookController] Successfully sent subscription activated email!');
                        }
                    } catch (\Exception $e) {
                        // Log the error
                        log_message('error', '[PayPal WebhookController] Failed to send subscription activated email: ' . $e->getMessage());
                        
                        // You might want to log additional details
                        log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                    } catch (\Error $e) {
                        // Catch PHP errors
                        log_message('error', '[PayPal WebhookController] Critical error while sending subscription activated email: ' . $e->getMessage());
                        log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                    }
                }
            }

            // Trigger subscription activated event
            \CodeIgniter\Events\Events::trigger('subscription_activated', [
                'subscription_id' => $subscriptionId,
                'event' => $event
            ]);
            
            return [
                'success' => true,
                'message' => 'Subscription activated event processed'
            ];
            
        } catch (\Exception $e) {
            log_message('error', '[PayPal WebhookController] Error processing subscription activated: ' . $e->getMessage());
            throw $e;
        }
    }

    private function handleSubscriptionUpdated($event, $isTestRequest)
    {
        log_message('info', '[PayPal WebhookController] Processing subscription updated event');
        log_message('debug', '[PayPal WebhookController] Event data: ' . json_encode($event));
        
        if ($isTestRequest) {
            return [
                'success' => true,
                'message' => 'Test subscription updated event processed'
            ];
        }
        
        try {
            $subscriptionId = $event->resource->id;
            
            // Get subscription details from PayPal to get complete information
            $subscriptionDetails = $this->PayPalService->getSubscription($subscriptionId);
            log_message('debug', '[PayPal WebhookController] Full subscription details: ' . json_encode($subscriptionDetails));

            // Only update next_payment_date if billing_info is available
            if (isset($subscriptionDetails->billing_info->next_billing_time)) {
                $nextBillingTime = $subscriptionDetails->billing_info->next_billing_time;
                if ($nextBillingTime) {
                    $this->SubscriptionModel->updateNextPaymentDate($subscriptionId, $nextBillingTime);
                }
            }

            // Trigger subscription updated event
            \CodeIgniter\Events\Events::trigger('subscription_updated', [
                'subscription_id' => $subscriptionId,
                'event' => $event
            ]);
            
            return [
                'success' => true,
                'message' => 'Subscription updated event processed'
            ];
            
        } catch (\Exception $e) {
            log_message('error', '[PayPal WebhookController] Error processing subscription updated: ' . $e->getMessage());
            throw $e;
        }
    }

    private function handleSubscriptionCancelled($event, $isTestRequest)
    {
        log_message('info', '[PayPal WebhookController] Processing subscription cancelled event');
        
        if ($isTestRequest) {
            return [
                'success' => true,
                'message' => 'Test subscription cancelled event processed'
            ];
        }
        
        try {
            $this->db->transBegin();
            
            $subscriptionId = $event->resource->id;

            // Update subscription status
            $this->SubscriptionModel->cancelSubscription($subscriptionId);
            
            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                throw new \Exception('Failed to process subscription cancellation');
            }
            
            $this->db->transCommit();
			
			// Get subscription and user details for email
            $subscription = $this->SubscriptionModel->getBySubscriptionReference($subscriptionId);
            if ($subscription) {
                $user = $this->UserModel->find($subscription['user_id']);
                $package = $this->PackageModel->find($subscription['package_id']);
                
                if ($user && $package) {
                    // Prepare data for email
                    $emailData = [
                        'subscription_id' => $subscriptionId,
                        'package_name' => $package['package_name'],
                        'status' => 'cancelled',
                        'payment_date' => $this->getCurrentUtcTime(),
                        'amount' => $subscription['amount_paid'],
                        'currency' => $subscription['currency']
                    ];

                    log_message('debug', '[PayPal WebhookController] Email Data: ' . json_encode($emailData));

                    // Send subscription cancelled email
                    try {
                        $emailService = new \App\Libraries\EmailService();
                        $result = $emailService->sendSubscriptionEmail([
                            'userID' => $subscription['user_id'],
                            'template' => 'subscription_cancelled',
                            'data' => $emailData
                        ]);

                        if($result) {
                            log_message('info', '[PayPal WebhookController] Successfully sent subscription cancelled email!');
                        }
                    } catch (\Exception $e) {
                        // Log the error
                        log_message('error', '[PayPal WebhookController] Failed to send subscription cancelled emaill: ' . $e->getMessage());
                        
                        // You might want to log additional details
                        log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                    } catch (\Error $e) {
                        // Catch PHP errors
                        log_message('error', '[PayPal WebhookController] Critical error while sending subscription cancelled email: ' . $e->getMessage());
                        log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                    }
                }
            }

            // Trigger subscription cancelled event
            \CodeIgniter\Events\Events::trigger('subscription_cancelled', [
                'subscription_id' => $subscriptionId,
                'event' => $event
            ]);
            
            return [
                'success' => true,
                'message' => 'Subscription cancelled event processed'
            ];
            
        } catch (\Exception $e) {
            log_message('error', '[PayPal WebhookController] Error processing subscription cancelled: ' . $e->getMessage());
            
            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
            }
            
            throw $e;
        }
    }

    private function handleSubscriptionSuspended($event, $isTestRequest)
    {
        log_message('info', '[PayPal WebhookController] Processing subscription suspended event');
        
        if ($isTestRequest) {
            return [
                'success' => true,
                'message' => 'Test subscription suspended event processed'
            ];
        }
        
        try {
            $subscriptionId = $event->resource->id;
            
            $this->SubscriptionModel->updateSubscriptionStatus($subscriptionId, 'suspended');
			
			// Get subscription and user details for email
            $subscription = $this->SubscriptionModel->getBySubscriptionReference($subscriptionId);
            if ($subscription) {
                $user = $this->UserModel->find($subscription['user_id']);
                $package = $this->PackageModel->find($subscription['package_id']);
                
                if ($user && $package) {
                    // Prepare data for email
                    $emailData = [
                        'subscription_id' => $subscriptionId,
                        'package_name' => $package['package_name'],
                        'status' => 'suspended',
                        'payment_date' => $this->getCurrentUtcTime(),
                        'amount' => $subscription['amount_paid'],
                        'currency' => $subscription['currency']
                    ];
                    
                    log_message('debug', '[PayPal WebhookController] Email Data: ' . json_encode($emailData));

                    // Send subscription suspended email
                    try {
                        $emailService = new \App\Libraries\EmailService();
                        $result = $emailService->sendSubscriptionEmail([
                            'userID' => $subscription['user_id'],
                            'template' => 'subscription_suspended',
                            'data' => $emailData
                        ]);

                        if($result) {
                            log_message('info', '[PayPal WebhookController] Successfully sent subscription suspended email!');
                        }
                    } catch (\Exception $e) {
                        // Log the error
                        log_message('error', '[PayPal WebhookController] Failed to send subscription suspended email: ' . $e->getMessage());
                        
                        // You might want to log additional details
                        log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                    } catch (\Error $e) {
                        // Catch PHP errors
                        log_message('error', '[PayPal WebhookController] Critical error while sending subscription suspended email: ' . $e->getMessage());
                        log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                    }
                }
            }

            // Trigger subscription suspended event
            \CodeIgniter\Events\Events::trigger('subscription_suspended', [
                'subscription_id' => $subscriptionId,
                'event' => $event
            ]);
            
            return [
                'success' => true,
                'message' => 'Subscription suspended event processed'
            ];
            
        } catch (\Exception $e) {
            log_message('error', '[PayPal WebhookController] Error processing subscription suspended: ' . $e->getMessage());
            throw $e;
        }
    }

    private function handlePaymentCompleted($event, $isTestRequest)
    {
        log_message('info', '[PayPal WebhookController] Processing payment completed event');
        log_message('debug', '[PayPal WebhookController] Payment completed event raw: ' . json_encode($event->resource));
        
        if ($isTestRequest) {
            return [
                'success' => true,
                'message' => 'Test payment completed event processed'
            ];
        }
        
        try {
            $this->db->transBegin();
            
            $subscriptionId = $event->resource->billing_agreement_id ?? null;
            
            if (!$subscriptionId) {
                throw new \Exception('Subscription ID not found in the event data');
            }

            $subscription = $this->SubscriptionModel->getBySubscriptionReference($subscriptionId);
            
            if (!$subscription) {
                throw new \Exception('Subscription not found in the database');
            }

            // Calculate new end date from current payment
            $endDate = $this->calculateEndDate(
                $this->getCurrentUtcTime(),
                $subscription['billing_cycle'],
                (int)$subscription['billing_period']
            );

            // Get package details for currency and amount
            $package = $this->PackageModel->find($subscription['package_id']);
            if (!$package) {
                throw new \Exception('Invalid package ID: ' . $packageId);
            }
            
            // Calculate initial next payment date based on billing cycle
            $nextPaymentDate = $this->calculateEndDate(
                $this->getCurrentUtcTime(),
                strtolower($package['validity_duration']),
                (int)$package['validity']
            );

            // Update subscription details
            $updatedSubscriptionData = [
                'subscription_status' => 'active',
                'transaction_id' => $event->resource->id,
                'transaction_token' => null,
                'payment_status' => $event->resource->state,
                'start_date' => $this->getCurrentUtcTime(),
                'end_date' => $endDate,
                'next_payment_date' => $event->resource->next_billing_time ?? $nextPaymentDate,
                'last_payment_date' => $this->getCurrentUtcTime(),
                'sent_expiring_reminder' => 'no',
                'amount_paid' => $event->resource->amount->total ?? $subscription['amount_paid'],
            ];
            $this->SubscriptionModel->update($subscription['id'], $updatedSubscriptionData);

            // Log updated subscription details
            log_message('info', "[PayPal WebhookController] Updated subscription details: " . json_encode($updatedSubscriptionData));

            // Check if payment entry already exists
            $existingPayment = $this->PaymentModel->where('subscription_id', $subscriptionId)
                                                  ->where('transaction_id', $event->resource->id)
                                                  ->first();

            $paymentData = [
                'subscription_id' => $subscriptionId,
                'transaction_id' => $event->resource->id,
                'amount' => $event->resource->amount->total ?? $subscription['amount_paid'],
                'currency' => $event->resource->amount->currency ?? $subscription['currency'],
                'payment_status' => $event->resource->state,
                'payment_date' => $this->getCurrentUtcTime(),
                'token' => null
            ];

            if ($existingPayment) {
                // Update existing payment entry
                $this->PaymentModel->update($existingPayment['id'], $paymentData);
                log_message('info', "[PayPal WebhookController] Updated existing payment details: " . json_encode($paymentData));
            } else {
                // Insert a new subscription payment entry
                $this->PaymentModel->insert($paymentData);
                log_message('info', "[PayPal WebhookController] Inserted new payment details: " . json_encode($paymentData));
            }

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                throw new \Exception('Failed to process payment completion');
            }
            
            $this->db->transCommit();

            // Send payment receipt email
            $user = $this->UserModel->find($subscription['user_id']);
            $package = $this->PackageModel->find($subscription['package_id']);
            
            if ($user && $package) {
                $emailData = [
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'transaction_id' => $event->resource->id,
                    'amount' => $paymentData['amount'],
                    'currency' => $paymentData['currency'],
                    'payment_date' => $paymentData['payment_date'],
                    'subscription_id' => $subscriptionId,
                    'package_name' => $package['package_name'],
                    'next_billing_time' => $event->resource->next_billing_time ?? $nextPaymentDate,
                    'payment_method' => 'PayPal'
                ];

                log_message('debug', '[PayPal WebhookController] Email Data: ' . json_encode($emailData));

                // Send payment receipt email
                try {
                    $emailService = new \App\Libraries\EmailService();
                    $emailService->sendPaymentReceipt([
                        'userID' => $subscription['user_id'],
                        'data' => $emailData
                    ]);
                } catch (\Exception $e) {
                    // Log the error
                    log_message('error', '[PayPal WebhookController] Failed to send subscription payment receipt email: ' . $e->getMessage());
                    
                    // You might want to log additional details
                    log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                } catch (\Error $e) {
                    // Catch PHP errors
                    log_message('error', '[PayPal WebhookController] Critical error while sending subscription payment receipt email: ' . $e->getMessage());
                    log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                }
            }

            // Trigger payment completed event
            \CodeIgniter\Events\Events::trigger('subscription_payment_received', [
                'payment' => $paymentData,
                'subscription' => $subscription,
                'event' => $event
            ]);
            
            log_message('info', "[PayPal WebhookController] Payment completed event processed for subscription: {$subscriptionId}");

            return [
                'success' => true,
                'message' => 'Payment completed event processed'
            ];
            
        } catch (\Exception $e) {
            log_message('error', "[PayPal WebhookController] Error processing payment completed: " . $e->getMessage());
            
            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
            }
            
            throw $e;
        }
    }
	
    private function handlePaymentDenied($event, $isTestRequest)
    {
        log_message('info', '[PayPal WebhookController] Processing payment denied event');
        
        if ($isTestRequest) {
            return [
                'success' => true,
                'message' => 'Test payment denied event processed'
            ];
        }
        
        try {
            log_message('debug', '[PayPal WebhookController] Subscription payment denied raw data: '. json_encode($event->resource));

            $subscriptionId = $event->resource->billing_agreement_id ?? null;
            
            if (!$subscriptionId) {
                throw new \Exception('Subscription ID not found in the event data');
            }

            $subscription = $this->SubscriptionModel->getBySubscriptionReference($subscriptionId);
            
            if (!$subscription) {
                throw new \Exception('Subscription not found in the database');
            }

            // Update subscription payment status
            $updatedSubscriptionData = [
                'payment_status' => 'denied',
            ];
            $this->SubscriptionModel->update($subscription['id'], $updatedSubscriptionData);

            // Log updated subscription details
            log_message('info', "[PayPal WebhookController] Updated subscription details for denied payment: " . json_encode($updatedSubscriptionData));

            // Check if we need to update subscription status or trigger retry logic
            $retryCount = ($subscription['retry_count'] ?? 0) + 1;
            if ($retryCount > 3) {
                $this->SubscriptionModel->update($subscription['id'], [
                    'subscription_status' => 'suspended',
                    'retry_count' => $retryCount,
                ]);
            } else {
                // Trigger retry logic
                $this->handleFailedPayment($event, 'denied');
                return; // handleFailedPayment will handle the rest of the process
            }

            // Send payment denied email
            $user = $this->UserModel->find($subscription['user_id']);
            $package = $this->PackageModel->find($subscription['package_id']);
            
            if ($user && $package) {
                $emailData = [
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'payment_date' => $this->getCurrentUtcTime(),
                    'amount' => $subscription['amount_paid'],
                    'currency' => $subscription['currency'],
                    'package_name' => $package['package_name'],
                ];

                log_message('debug', '[PayPal WebhookController] Email Data: ' . json_encode($emailData));

                // Send subscription payment denied email
                try {
                    $emailService = new \App\Libraries\EmailService();
                    $result = $emailService->sendSubscriptionEmail([
                        'userID' => $subscription['user_id'],
                        'template' => 'payment_denied',
                        'data' => $emailData
                    ]);

                    if($result) {
                        log_message('info', '[PayPal WebhookController] Successfully sent subscription payment denied email!');
                    }
                } catch (\Exception $e) {
                    // Log the error
                    log_message('error', '[PayPal WebhookController] Failed to send subscription payment denied email: ' . $e->getMessage());
                    
                    // You might want to log additional details
                    log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                } catch (\Error $e) {
                    // Catch PHP errors
                    log_message('error', '[PayPal WebhookController] Critical error while sending subscription payment denied email: ' . $e->getMessage());
                    log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                }
            }

            // Trigger payment denied event
            \CodeIgniter\Events\Events::trigger('subscription_payment_denied', [
                'subscription_id' => $subscriptionId,
                'event' => $event
            ]);
            
            log_message('info', "[PayPal WebhookController] Payment denied event processed for subscription: {$subscriptionId}");

            return [
                'success' => true,
                'message' => 'Payment denied event processed'
            ];
            
        } catch (\Exception $e) {
            log_message('error', "[PayPal WebhookController] Error processing payment denied: " . $e->getMessage());
            throw $e;
        }
    }

    private function handlePaymentPending($event, $isTestRequest)
    {
        log_message('info', '[PayPal WebhookController] Processing payment pending event');
        
        if ($isTestRequest) {
            return [
                'success' => true,
                'message' => 'Test payment pending event processed'
            ];
        }
        
        try {
            log_message('debug', '[PayPal WebhookController] Payment pending event raw data: ' . json_encode($event->resource));

            $subscriptionId = $event->resource->billing_agreement_id ?? null;
            
            if (!$subscriptionId) {
                throw new \Exception('Subscription ID not found in the event data');
            }

            $subscription = $this->SubscriptionModel->getBySubscriptionReference($subscriptionId);
            
            if (!$subscription) {
                throw new \Exception('Subscription not found in the database');
            }

            // Update subscription payment status
            $updatedSubscriptionData = [
                'payment_status' => 'pending',
            ];
            $this->SubscriptionModel->update($subscription['id'], $updatedSubscriptionData);

            // Log updated subscription details
            log_message('info', "[PayPal WebhookController] Updated subscription details for pending payment: " . json_encode($updatedSubscriptionData));

            // Check if payment entry already exists
            $existingPayment = $this->PaymentModel->where('subscription_id', $subscriptionId)
                                                  ->where('transaction_id', $event->resource->id)
                                                  ->first();

            if (!$existingPayment) {
                // Record pending payment
                $paymentData = [
                    'subscription_id' => $subscriptionId,
                    'transaction_id' => $event->resource->id,
                    'amount' => $event->resource->amount->total,
                    'currency' => $event->resource->amount->currency,
                    'payment_status' => 'pending',
                    'payment_date' => $this->getCurrentUtcTime(),
                    'token' => null
                ];
                
                // Insert a new subscription payment entry
                $this->PaymentModel->insert($paymentData);

                // Log entered new payment details
                log_message('info', "[PayPal WebhookController] Entered new pending payment details: " . json_encode($paymentData));
            } else {
                log_message('info', "[PayPal WebhookController] Payment entry already exists for transaction: " . $event->resource->id);
            }

            // Send payment pending email
            $user = $this->UserModel->find($subscription['user_id']);
            $package = $this->PackageModel->find($subscription['package_id']);
            
            if ($user && $package) {
                $emailData = [
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'payment_date' => $this->getCurrentUtcTime(),
                    'amount' => $event->resource->amount->total,
                    'currency' => $event->resource->amount->currency,
                    'package_name' => $package['package_name'],
                ];

                log_message('debug', '[PayPal WebhookController] Email Data: ' . json_encode($emailData));

                // Send subscription payment pending email
                try {
                    $emailService = new \App\Libraries\EmailService();
                    $result = $emailService->sendSubscriptionEmail([
                        'userID' => $subscription['user_id'],
                        'template' => 'payment_pending',
                        'data' => $emailData
                    ]);

                    if($result) {
                        log_message('info', '[PayPal WebhookController] Successfully sent subscription payment pending email!');
                    }
                } catch (\Exception $e) {
                    // Log the error
                    log_message('error', '[PayPal WebhookController] Failed to send subscription payment pending email: ' . $e->getMessage());
                    
                    // You might want to log additional details
                    log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                } catch (\Error $e) {
                    // Catch PHP errors
                    log_message('error', '[PayPal WebhookController] Critical error while sending subscription payment pending email: ' . $e->getMessage());
                    log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                }
            }

            // Trigger payment pending event
            \CodeIgniter\Events\Events::trigger('subscription_payment_pending', [
                'subscription_id' => $subscriptionId,
                'event' => $event
            ]);
            
            log_message('info', "[PayPal WebhookController] Payment pending event processed for subscription: {$subscriptionId}");

            return [
                'success' => true,
                'message' => 'Payment pending event processed'
            ];
            
        } catch (\Exception $e) {
            log_message('error', "[PayPal WebhookController] Error processing payment pending: " . $e->getMessage());
            throw $e;
        }
    }
	
    private function handlePaymentRefunded($event, $isTestRequest)
    {
        log_message('info', '[PayPal WebhookController] Processing payment refunded event');
        
        if ($isTestRequest) {
            return [
                'success' => true,
                'message' => 'Test payment refunded event processed'
            ];
        }
        
        try {
            log_message('debug', '[PayPal WebhookController] Payment refunded event raw data: ' . json_encode($event->resource));
    
            $saleId = $event->resource->sale_id ?? null;
            
            if (!$saleId) {
                throw new \Exception('Sale ID not found in the event data');
            }
    
            // Find the subscription associated with this sale
            $payment = $this->PaymentModel->where('transaction_id', $saleId)->first();
            
            if (!$payment) {
                throw new \Exception('Payment not found in the database');
            }
    
            $subscription = $this->SubscriptionModel->getBySubscriptionReference($payment['subscription_id']);
            
            if (!$subscription) {
                throw new \Exception('Subscription not found in the database');
            }
    
            // Check if it's a full or partial refund
            $refundAmount = (float)$event->resource->amount->total;
            $originalAmount = (float)$payment['amount'];
            $isFullRefund = $refundAmount >= $originalAmount;
    
            // Update subscription status
            $updatedSubscriptionData = [
                'subscription_status' => $isFullRefund ? 'cancelled' : 'active',
                'payment_status' => $isFullRefund ? 'refunded' : 'partially_refunded',
                'cancellation_reason' => $isFullRefund ? 'Refunded in full' : '',
            ];
    
            // If it's a full refund, cancel the subscription with PayPal
            if ($isFullRefund) {
                try {
                    $cancellationResult = $this->PayPalService->cancelSubscription($subscription['subscription_reference'], 'Full refund issued', true);
                    log_message('info', "[PayPal WebhookController] PayPal subscription cancellation result: " . json_encode($cancellationResult));
                    
                    if (!$cancellationResult['success']) {
                        throw new \Exception('Failed to cancel subscription with PayPal: ' . $cancellationResult['message']);
                    }
                } catch (\Exception $e) {
                    log_message('error', "[PayPal WebhookController] Error cancelling subscription with PayPal: " . $e->getMessage());
                    // Even if PayPal cancellation fails, we proceed with updating our database
                }
            }
    
            $this->SubscriptionModel->update($subscription['id'], $updatedSubscriptionData);
    
            // Log updated subscription details
            log_message('info', "[PayPal WebhookController] Updated subscription details for refunded payment: " . json_encode($updatedSubscriptionData));
    
            // Update payment record
            $updatedPaymentData = [
                'payment_status' => $isFullRefund ? 'refunded' : 'partially_refunded',
                'refund_id' => $event->resource->id,
                'refund_amount' => $refundAmount,
                'refund_currency' => $event->resource->amount->currency,
                'refund_date' => $this->getCurrentUtcTime(),
                'is_partial_refund' => $isFullRefund ? 0 : 1
            ];
            $this->PaymentModel->update($payment['id'], $updatedPaymentData);
    
            // Log updated payment details
            log_message('info', "[PayPal WebhookController] Updated payment details for refund: " . json_encode($updatedPaymentData));
    
            // Send refund email
            $user = $this->UserModel->find($subscription['user_id']);
            $package = $this->PackageModel->find($subscription['package_id']);
            
            if ($user && $package) {
                $emailData = [
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'refund_date' => $this->getCurrentUtcTime(),
                    'amount' => $refundAmount,
                    'subscription_id' => $subscription['id'],
                    'currency' => $event->resource->amount->currency,
                    'package_name' => $package['package_name'],
                    'is_full_refund' => $isFullRefund ? true : false
                ];

                log_message('debug', '[PayPal WebhookController] Email Data: ' . json_encode($emailData));

                // Send subscription payment refunded email
                try {
                    $emailService = new \App\Libraries\EmailService();
                    $result = $emailService->sendSubscriptionEmail([
                        'userID' => $subscription['user_id'],
                        'template' => 'payment_refunded',
                        'data' => $emailData
                    ]);

                    if($result) {
                        log_message('info', '[PayPal WebhookController] Successfully sent subscription payment refunded email!');
                    }
                } catch (\Exception $e) {
                    // Log the error
                    log_message('error', '[PayPal WebhookController] Failed to send subscription payment refunded email: ' . $e->getMessage());
                    
                    // You might want to log additional details
                    log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                } catch (\Error $e) {
                    // Catch PHP errors
                    log_message('error', '[PayPal WebhookController] Critical error while sending subscription payment refunded email: ' . $e->getMessage());
                    log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                }
            }
    
            // Trigger payment refunded event
            \CodeIgniter\Events\Events::trigger('subscription_payment_refunded', [
                'subscription_id' => $subscription['id'],
                'event' => $event,
                'is_full_refund' => $isFullRefund
            ]);
            
            log_message('info', "[PayPal WebhookController] Payment refunded event processed for subscription: {$subscription['id']}. Full refund: " . ($isFullRefund ? 'Yes' : 'No'));
    
            return [
                'success' => true,
                'message' => 'Payment refunded event processed'
            ];
            
        } catch (\Exception $e) {
            log_message('error', "[PayPal WebhookController] Error processing payment refunded: " . $e->getMessage());
            throw $e;
        }
    }

    private function handlePaymentFailed($event, $isTestRequest)
    {
        log_message('info', '[PayPal WebhookController] Processing payment failed event');
        log_message('debug', '[PayPal WebhookController] Payment failed event raw data: ' . json_encode($event));
        
        if ($isTestRequest) {
            return [
                'success' => true,
                'message' => 'Test payment failed event processed'
            ];
        }
        
        try {
            $subscriptionId = $event->resource->id ?? null;
            
            if (!$subscriptionId) {
                throw new \Exception('Subscription ID not found in the event data');
            }

            $subscription = $this->SubscriptionModel->getBySubscriptionReference($subscriptionId);
            
            if (!$subscription) {
                throw new \Exception('Subscription not found in the database');
            }

            $failedPaymentsCount = $event->resource->billing_info->failed_payments_count ?? 0;
            $nextBillingTime = $event->resource->billing_info->next_billing_time ?? null;
            $outstandingBalance = $event->resource->billing_info->outstanding_balance->value ?? '0.00';
            $failureReasonCode = $event->resource->billing_info->last_failed_payment->reason_code ?? 'Unknown';

            // Update subscription details
            $updatedSubscriptionData = [
                'retry_count' => $failedPaymentsCount,
                'next_retry_date' => $nextBillingTime,
                'outstanding_balance' => $outstandingBalance,
                'last_payment_failure_reason' => $failureReasonCode,
                'payment_status' => 'failed'
            ];

            if ($failedPaymentsCount > 3) {
                $updatedSubscriptionData['subscription_status'] = 'suspended';
                log_message('info', "[PayPal WebhookController] Subscription {$subscriptionId} suspended after 3 failed attempts");
            }

            $this->SubscriptionModel->update($subscription['id'], $updatedSubscriptionData);

            // Log updated subscription details
            log_message('info', "[PayPal WebhookController] Updated subscription details for failed payment: " . json_encode($updatedSubscriptionData));

            // Trigger retry logic
            return $this->handleFailedPayment($event, 'failed');

        } catch (\Exception $e) {
            log_message('error', "[PayPal WebhookController] Error processing payment failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function handleFailedPayment($event, $failureType)
    {
        try {
            log_message('debug', '[PayPal WebhookController] Handling failed payment. Event data: ' . json_encode($event));

            $subscriptionId = $event->resource->id ?? null;
            
            if (!$subscriptionId) {
                throw new \Exception('Subscription ID not found in the event data');
            }

            $subscription = $this->SubscriptionModel->getBySubscriptionReference($subscriptionId);
            
            if (!$subscription) {
                throw new \Exception('Subscription not found in the database');
            }

            // Get the last payment failure details
            $lastFailedPayment = $event->resource->billing_info->last_failed_payment ?? null;
            $failureReason = $lastFailedPayment ? ($lastFailedPayment->reason_code ?? 'Unknown') : 'Unknown';

            // Calculate retry dates if not already set
            $retryDates = json_decode($subscription['retry_dates'] ?? '[]', true);
            if (empty($retryDates)) {
                $retryDates = $this->calculateRetryDates();
            }

            $currentDate = $this->getCurrentUtcTime();
            $retryCount = ($subscription['retry_count'] ?? 0) + 1;
            $newStatus = 'active';

            // Determine if we should suspend the subscription
            // We keep the subscription active for 3 retry attempts
            // If all 3 attempts fail or if the current date is past the 3rd retry date, we suspend the subscription
            if ($retryCount > 3 || ($retryCount == 3 && $currentDate > $retryDates[2])) {
                $newStatus = 'suspended';
                $nextRetryDate = null;
                log_message('info', "[PayPal WebhookController] Subscription {$subscriptionId} suspended after 3 failed attempts");
            } else {
                $nextRetryDate = $retryDates[$retryCount - 1] ?? null;
                log_message('info', "[PayPal WebhookController] Subscription {$subscriptionId} payment failed. Retry attempt {$retryCount} scheduled for {$nextRetryDate}");
            }

            // Update subscription payment status
            $updatedSubscriptionData = [
                'subscription_status' => $newStatus,
                'payment_status' => $failureType,
                'last_payment_failure_reason' => $failureReason,
                'retry_count' => $retryCount,
                'next_retry_date' => $nextRetryDate,
                'retry_dates' => json_encode($retryDates),
                'end_date' => $retryDates[2]
            ];
            $this->SubscriptionModel->update($subscription['id'], $updatedSubscriptionData);

            // Log updated subscription details
            log_message('info', "[PayPal WebhookController] Updated subscription details for failed payment: " . json_encode($updatedSubscriptionData));

            // Send payment failed email
            $user = $this->UserModel->find($subscription['user_id']);
            $package = $this->PackageModel->find($subscription['package_id']);
            
            if ($user && $package) {
                $emailData = [
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'payment_date' => $currentDate,
                    'amount' => $subscription['amount_paid'],
                    'currency' => $subscription['currency'],
                    'package_name' => $package['package_name'],
                    'failure_reason' => $failureReason,
                    'subscription_status' => $newStatus,
                    'retry_date_1' => $retryDates[0] ?? null,
                    'retry_date_2' => $retryDates[1] ?? null,
                    'retry_date_3' => $retryDates[2] ?? null,
                ];

                log_message('debug', '[PayPal WebhookController] Email Data: ' . json_encode($emailData));

                // Send subscription payment failed email
                try {
                    $emailService = new \App\Libraries\EmailService();
                    $result = $emailService->sendSubscriptionEmail([
                        'userID' => $subscription['user_id'],
                        'template' => 'payment_failed',
                        'data' => $emailData
                    ]);

                    if($result) {
                        log_message('info', '[PayPal WebhookController] Successfully sent subscription payment failed email!');
                    }
                } catch (\Exception $e) {
                    // Log the error
                    log_message('error', '[PayPal WebhookController] Failed to send subscription payment failed email: ' . $e->getMessage());
                    
                    // You might want to log additional details
                    log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                } catch (\Error $e) {
                    // Catch PHP errors
                    log_message('error', '[PayPal WebhookController] Critical error while sending subscription payment failed email: ' . $e->getMessage());
                    log_message('debug', '[PayPal WebhookController] Error details: ' . $e->getTraceAsString());
                }
            }

            // Trigger payment failed event
            \CodeIgniter\Events\Events::trigger('subscription_payment_failed', [
                'subscription_id' => $subscriptionId,
                'event' => $event,
                'failure_type' => $failureType,
                'failure_reason' => $failureReason,
                'new_status' => $newStatus,
                'retry_count' => $retryCount,
                'next_retry_date' => $nextRetryDate
            ]);
            
            log_message('info', "[PayPal WebhookController] Payment {$failureType} event processed for subscription: {$subscriptionId}. Reason: {$failureReason}. New status: {$newStatus}. Retry count: {$retryCount}");

            return [
                'success' => true,
                'message' => "Payment {$failureType} event processed"
            ];
            
        } catch (\Exception $e) {
            log_message('error', "[PayPal WebhookController] Error processing payment {$failureType}: " . $e->getMessage());
            throw $e;
        }
    }

    private function calculateRetryDates()
    {
        $now = Time::now();
        return [
            $now->addDays(1)->format('Y-m-d H:i:s'),
            $now->addDays(2)->format('Y-m-d H:i:s'),
            $now->addDays(3)->format('Y-m-d H:i:s')
        ];
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
}
