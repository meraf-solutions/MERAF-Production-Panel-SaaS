<?php

namespace App\Modules\Offline\Controllers;

use CodeIgniter\I18n\Time;
use App\Controllers\BaseController;
use App\Models\PackageModel;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPaymentModel;
use App\Models\UserModel;
use App\Modules\Offline\Libraries\OfflineService;

class Cronjob extends BaseController
{
    protected $PackageModel;
    protected $SubscriptionModel;
    protected $UserModel;
    protected $PaymentModel;
    protected $OfflineService;

    public function __construct()
    {
        // Set the timezone to UTC
        setMyTimezone('UTC');
        
        // Initialize Models
        $this->PackageModel = new PackageModel();
        $this->SubscriptionModel = new SubscriptionModel();
        $this->PaymentModel = new SubscriptionPaymentModel();
        $this->UserModel = new UserModel();
        $this->OfflineService = new OfflineService();
    }

    public function processOfflinePayments()
    {
        try {
            $currentDateTime = Time::now()->setTimezone('UTC');
            $threeDaysFromNow = $currentDateTime->addDays(3);
            $newInvoiceCount = 0;
            
            // Fetch subscriptions due within the next 3 days
            $dueSubscriptions = $this->SubscriptionModel->where('payment_method', 'Offline')
                ->where('next_payment_date <=', $threeDaysFromNow->toDateTimeString())
                ->findAll();
    
            foreach ($dueSubscriptions as $subscription) {
                try {
                    // Check for existing pending invoice
                    $existingPendingInvoice = $this->PaymentModel
                        ->where('subscription_id', $subscription['subscription_reference'])
                        ->where('payment_status', 'pending')
                        ->first();
    
                    // Skip if pending invoice exists
                    if ($existingPendingInvoice) {
                        continue;
                    }
    
                    // Generate new transaction ID
                    $transactionId = $this->OfflineService->generateTransactionId(true);
    
                    // Prepare payment data
                    $paymentData = [
                        'subscription_id' => $subscription['subscription_reference'],
                        'transaction_id' => $transactionId,
                        'amount' => $subscription['amount_paid'],
                        'currency' => $subscription['currency'],
                        'payment_status' => 'pending',
                        'payment_date' => $currentDateTime,
                        'payment_method' => 'Offline',
                    ];
    
                    // Insert new payment entry
                    if ($this->PaymentModel->insert($paymentData)) {
                        $newInvoiceCount++;
                    }

                    // Add the new transaction token
                    $updated = $this->SubscriptionModel->update($subscription['id'], ['transaction_token' => base_url('payment-options/offline/payment/' . $subscription['subscription_reference'])]);
                    $subscriptionReference = $subscription['subscription_reference'];
        
                    if ($updated) {
                        log_message('info', "[Offline/Cronjob] Payment status updated to pending for subscription: {$subscriptionReference}");

                        // Send payment pending email
                        $user = $this->UserModel->find($subscription['user_id']);
                        $package = $this->PackageModel->find($subscription['package_id']);

                        // Check if transaction_id has completed payment reference contains the separator
                        if (isset($transactionId) && strpos($transactionId, ':') !== false) {
                            // New format: package_id:user_id
                            list($paymentReference, $pendingID) = explode(':', $transactionId);
                        } else {
                            // Handle the case where transaction_id is not set or doesn't contain the separator
                            $paymentReference = $transactionId ?? null;
                            $pendingID = null;
                        }
                        
                        if ($user && $package) {
                            $emailData = [
                                'user_name' => $user->first_name . ' ' . $user->last_name,
                                'recipient_email' => $user->email,
                                'payment_date' => $currentDateTime,
                                'amount' => $subscription['amount_paid'],
                                'currency' => $subscription['currency'],
                                'package_name' => $package['package_name'],
                                'payment_method' => 'Offline',
                                'payment_reference' => $paymentReference
                            ];

                            log_message('debug', '[Offline/Cronjob] Email Data: ' . json_encode($emailData));

                            try {
                                $emailService = new \App\Libraries\EmailService();
                                $result = $emailService->sendSubscriptionEmail([
                                    'userID' => $subscription['user_id'],
                                    'template' => 'payment_pending',
                                    'data' => $emailData
                                ]);

                                if($result) {
                                    log_message('debug', '[Offline/Cronjob] Successfully sent subscription payment pending email!');
                                }
                            } catch (\Exception $e) {
                                // Log the error
                                log_message('error', '[Offline/Cronjob]Failed to send subscription email: ' . $e->getMessage());
                                
                                // You might want to log additional details
                                log_message('debug', '[Offline/Cronjob] Error details: ' . $e->getTraceAsString());
                            } catch (\Error $e) {
                                // Catch PHP errors
                                log_message('error', '[Offline/Cronjob]Critical error while sending subscription email: ' . $e->getMessage());
                                log_message('debug', '[Offline/Cronjob] Error details: ' . $e->getTraceAsString());
                            }
                        }
                    } else {
                        log_message('error', "[Offline/Cronjob] Error updating payment status to pending: " . print_r($this->PaymentModel->errors(), true));
                    }
    
                } catch (Exception $e) {
                    log_message('error', '[Offline/Cronjob]Error processing subscription {id}: {message}', [
                        'id' => $subscription['id'],
                        'message' => $e->getMessage()
                    ]);
                    continue;
                }
            }
    
            $response = [
                'success' => true,
                'status'  => 1,
                'msg'     => sprintf('Offline payments processed successfully. %d new invoice(s) generated.', $newInvoiceCount)
            ];
            return json_encode($response);
    
        } catch (Exception $e) {
            log_message('error', '[Offline/Cronjob]Error in processOfflinePayments: {message}', [
                'message' => $e->getMessage()
            ]);
    
            $response = [
                'success' => false,
                'status'  => 0,
                'msg'     => 'Error processing offline payments: ' . $e->getMessage(),
                'error'   => $e->getMessage()
            ];
            return json_encode($response);
        }
    }
}

