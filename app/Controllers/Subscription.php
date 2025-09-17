<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use App\Controllers\Home;
use App\Services\ModuleScanner;

class Subscription extends Home
{
    protected $ModuleScanner;

    public function __construct()
    {
        parent::__construct();
        $this->ModuleScanner = new ModuleScanner();
        
        $this->checkIfLoggedIn(); // Check if user is logged before to proceed
    }

    /**
     * Prepare all data for packages available for the user and current subscription details if available
     */
    private function availablePackageAndUserSubscriptionData()
    {
        $data = [];

        /**
         * Get the currenct subscribed package of the user
         */
        $data['currentPackage'] = $this->SubscriptionModel->getActiveByUserId($this->userID);        

        /**
         * Get the package module details
         */
        $data['packageModules'] = $this->PackageModulesModel->findAll();

        /**
         * Get if the member had trial package previously
         */
        $hasHadTrialPackage = $this->SubscriptionModel->hasHadTrialPackage($this->userID);
        $data['hasHadTrialPackage'] = $hasHadTrialPackage;

        /**
         * Set the default package if user never had trial previously
         */
        $data['defaultPackage'] = !$hasHadTrialPackage ? $this->PackageModel->getDefaultPackage() : null;

        /**
         * Fetch packages from the database with owner_id 1 as the Admin
         */
        $allPackages = $this->PackageModel->where('owner_id', 1)->findAll();
        $packages = [];
        foreach ($allPackages as $package) {
            if($package['visible'] === 'on') {
                $packages[] = $package;
            }
            
            // Include trial package if needed
            if (!$hasHadTrialPackage && ($package['is_default'] === 'on') ) {
                $packages[] = $package;
            }

        }

        // Sort packages by sort_order
        usort($packages, function($a, $b) {
            return $a['sort_order'] - $b['sort_order'];
        });

        $data['packages'] = $packages;

        /**
         * Set the billing durations available
         */
        $billingDuration = [];
        foreach ($packages as $package) {
            if($package['visible'] === 'on') {
                $billingDuration[] = $package['validity_duration'];
            }
            
            // Include trial package if needed
            if (!$hasHadTrialPackage && ($package['is_default'] === 'on') ) {
                $billingDuration[] = $package['validity_duration'];
            }
        }
        $billingDuration = array_unique($billingDuration);

        $data['billingDuration'] = $billingDuration;

        /**
         * Get the first duration as default
         */
        // Sort billing durations
        $durationOrder = ['day', 'month', 'year', 'lifetime'];
        usort($billingDuration, function($a, $b) use ($durationOrder) {
            return array_search($a, $durationOrder) - array_search($b, $durationOrder);
        });
        
        $defaultDuration = reset($billingDuration);

        $data['defaultDuration'] = $defaultDuration;

        /**
         * Group the packages
         */
        $groupedPackages = [];
        foreach ($packages as $package) {
            if ($package['visible'] === 'on') {
                $groupedPackages[$package['validity_duration']][] = $package;
            }
            
            // Include trial package if needed
            if (!$hasHadTrialPackage && ($package['is_default'] === 'on') ) {
                $groupedPackages[$package['validity_duration']][] = $package;
            }
        }

        $data['groupedPackages'] = $groupedPackages;

        /**
         * User Subscription Data
         */

        // Initialize the arrays
        $subscription = [];
        $package = [];
        $payments = [];
        $nextBillingDate = '';

        // First try to get active subscription
        $subscription = $this->SubscriptionModel->getActiveByUserId($this->userID);

        // If no active subscription, get all subscriptions and take the most recent
        if (empty($subscription)) {
            $allSubscriptions = $this->SubscriptionModel->getAllByUserId($this->userID);
            if (!empty($allSubscriptions)) {
                $subscription = $allSubscriptions[0]; // First one is most recent due to DESC ordering in getAllByUserId
            }
        }

        // Get the package subscribed to
        if (!empty($subscription) && isset($subscription['package_id'])) {
            $package = $this->PackageModel->find($subscription['package_id']);

            // Get all payments for this subscription
            if (isset($subscription['subscription_reference'])) {
                $payments = $this->PaymentModel
                    ->where('subscription_id', $subscription['subscription_reference'])
                    ->where('payment_status', 'completed')
                    ->orWhere('payment_status', 'partially_refunded')
                    ->orderBy('payment_date', 'DESC')
                    ->limit(10)
                    ->findAll();

                log_message('debug', '[SubscriptionController] Found ' . count($payments) . ' completed or partially refunded payments for subscription ' . $subscription['subscription_reference']);

                // Calculate total payments considering partial refunds
                $totalPayments = 0;
                foreach ($payments as &$payment) {
                    if ($payment['payment_status'] === 'partially_refunded') {
                        $payment['amount'] = $payment['amount'] - $payment['refund_amount'];
                    } 
                }
            }

            if( ($subscription['subscription_status'] === 'cancelled') || ($subscription['is_reactivated'] === 'yes') )  {
                $currentDate = Time::now()->setTimezone('UTC');
                $endDate = $subscription['end_date'] ? Time::parse($subscription['end_date'], 'UTC') : Time::now()->setTimezone('UTC');
                $interval = $currentDate->diff($endDate);
                $data['daysRemaining'] = $interval->days;  
            }
            
            // Get the next billing date
            $nextBillingDate = $this->getSubscriptionNextBilling($subscription, $this->myConfig);
        }

        $data['payments'] = $payments;
        $data['subscription'] = $subscription;
        $data['currentPackageFeatures'] = $package;
        $data['nextBillingDate'] = $nextBillingDate;

        return $data;
    }

    /**
     * Display available subscription packages
     */
    public function packages_page()
    {
        $data['pageTitle'] = lang('Pages.Subscription') . ' | ' . lang('Pages.Packages');
        $data['section'] = 'Subscription';
        $data['subsection'] = 'Packages';
        $data['sideBarMenu'] = $this->sideBarMenu;
        $data['userData'] = $this->userAcctDetails;
        $data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
        $data['myConfig'] = $this->myConfig;
        $data['productVariations'] = $this->productVariations();

        $data = array_merge($data, $this->availablePackageAndUserSubscriptionData());

        // Load the view and pass the packages data
        return view('dashboard/subscription/packages', $data);
    }

    /**
     * Display subscription details and payment history
     */
    public function user_subscription_details_page()
    {
        $data['userData'] = $this->userAcctDetails;

        try {
            $data['pageTitle'] = lang('Pages.Subscription') . ' | ' . lang('Pages.My_Subscription');
            $data['section'] = 'Subscription';
            $data['subsection'] = 'My_Subscription';
            $data['sideBarMenu'] = $this->sideBarMenu;
            $data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
            $data['myConfig'] = $this->myConfig;
            $data['packages'] = $this->PackageModel->where('owner_id', 1)->findAll();
            
            $data = array_merge($data, $this->availablePackageAndUserSubscriptionData());
            
            // Load the view and pass the data
            return view('dashboard/subscription/subscription_details', $data);

        } catch (\Exception $e) {
            log_message('error', '[SubscriptionController] Error loading subscription details: ' . $e->getMessage());
            
            return view('layouts/single_page', array_merge($data,[
                'pageTitle' => lang('Notifications.Error'),
                'alert' => [
                    'success' => false,
                    'message' => lang('Notifications.Failed_to_load_subscription_details'),
                    'redirect' => base_url(),
                ]
            ]));
        }
    }

    /**
     * Display all payment history for a specific subscription
     */
    public function user_payment_history_page($subscriptionId)
    {
        $data['userData'] = $this->userAcctDetails;

        try {
            $data['pageTitle'] = lang('Pages.Subscription') . ' | ' . lang('Pages.Payment_History');
            $data['section'] = 'Subscription';
            $data['subsection'] = 'Payment_History';
            $data['sideBarMenu'] = $this->sideBarMenu;
            $data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
            $data['myConfig'] = $this->myConfig;

            // Fetch all payments for this subscription
            $data['payments'] = $this->PaymentModel
                    ->where('subscription_id', $subscriptionId)
                    ->where('payment_status', 'completed')
                    ->orWhere('payment_status', 'partially_refunded')
                    ->orderBy('payment_date', 'DESC')
                    ->findAll();

            // Fetch subscription details
            $data['subscription'] = $this->SubscriptionModel
                ->where('subscription_reference', $subscriptionId)
                ->first();

            if (empty($data['subscription'])) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Subscription not found');
            }

            // Calculate total payments
            $data['totalPayments'] = 0;
            foreach ($data['payments'] as &$payment) {
                if ($payment['payment_status'] === 'partially_refunded') {
                    $payment['adjusted_amount'] = $payment['amount'] - $payment['refund_amount'];
                    $data['totalPayments'] += $payment['adjusted_amount'];
                } else {
                    $data['totalPayments'] += $payment['amount'];
                }
            }

            // Load the view and pass the data
            return view('dashboard/subscription/payment_history', $data);

        } catch (\Exception $e) {
            log_message('error', '[SubscriptionController] Error loading payment history: ' . $e->getMessage());
            
            return view('layouts/single_page', array_merge($data, [
                'pageTitle' => lang('Notifications.Error'),
                'alert' => [
                    'success' => false,
                    'message' => lang('Notifications.Failed_to_load_payment_history'),
                    'redirect' => base_url('subscription/my-subscription'),
                ]
            ]));
        }
    }

    private function getSubscriptionNextBilling($subscription, $myConfig) {
        if ($subscription['is_reactivated'] === 'yes') {
            return 'Cancelled';
        }
        if ($subscription['subscription_status'] !== 'active') {
            return ucfirst(esc($subscription['subscription_status']));
            
        }
        return formatDate($subscription['next_payment_date'], $myConfig);
    }
    
    /**
     * Create a new subscription
     */
    public function create()
    {
        $data['userData'] = $this->userAcctDetails;

        try {
            // Validate request
            $rules = [
                'package_id' => 'required|integer|is_not_unique[package.id]'
            ];

            if (!$this->validate($rules)) {        
                return view('layouts/single_page', array_merge($data, [
                    'pageTitle' => lang('Notifications.Invalid_package'),
                    'alert' => [
                        'success' => false,
                        'message' => lang('Notifications.Invalid_package_selected') . $this->validator->getErrors(),
                        'redirect' => base_url('subscription/packages'),
                    ]
                ]));
            }

            // Get package details
            $packageId = $this->request->getPost('package_id');
            $paymentMethodName = $this->request->getPost('payment_method');
            $package = $this->PackageModel->find($packageId);

            if (!$package) {        
                return view('layouts/single_page', array_merge($data, [
                    'pageTitle' => lang('Notifications.Package_not_found'),
                    'alert' => [
                        'success' => false,
                        'message' => lang('Notifications.Package_not_found'),
                        'redirect' => base_url('subscription/packages'),
                    ]
                ]));           
            }

            // Get current user
            $userId = $this->userID;
            $user = $this->userAcctDetails;

            // Check if user already has an active subscription
            $activeSubscription = $this->SubscriptionModel->getActiveByUserId($userId);
            if ($activeSubscription) {
                return view('layouts/single_page', array_merge($data, [
                    'pageTitle' => lang('Notifications.User_has_active_subscription'),
                    'alert' => [
                        'success' => false,
                        'message' => lang('Notifications.User_has_active_subscription'),
                        'redirect' => base_url('subscription/my-subscription'),
                    ]
                ]));                
            }

            // Select the appropriate payment service based on the payment method
            $paymentMethod = $this->PaymentMethods[$paymentMethodName] ?? $paymentMethodName;
            $paymentServiceName = $paymentMethod['service_name'] ?? $paymentMethodName;
            $paymentService = $this->ModuleScanner->loadLibrary($paymentMethodName, $paymentServiceName);

            // Check if the payment service exists
            if (!$paymentService) {
                return view('layouts/single_page', array_merge($data, [
                    'pageTitle' => lang('Notifications.Error_in_payment_method'),
                    'alert' => [
                        'success' => false,
                        'message' => lang('Notifications.Error_in_the_selected_payment_method'),
                        'redirect' => base_url('subscription/packages'),
                    ]
                ]));
            }

            // Call the payment service's newSubscription method
            $approvalUrl = $paymentService->newSubscription($packageId);

            if ($approvalUrl) {
                return view('layouts/single_page', array_merge($data, [
                    'pageTitle' => lang('Notifications.Subscription_created'),
                    'alert' => [
                        'success' => true,
                        'message' => lang('Notifications.Subscription_created'),
                        'redirect' => $approvalUrl,
                    ]
                ]));    
            } else {
                return view('layouts/single_page', array_merge($data, [
                    'pageTitle' => lang('Notifications.Failed_to_create_subscription'),
                    'alert' => [
                        'success' => false,
                        'message' => lang('Notifications.Failed_to_create_subscription'),
                        'redirect' => base_url('subscription/packages'),
                    ]
                ]));
            }          

        } catch (\Exception $e) {
            log_message('error', '[SubscriptionController] Create subscription failed: ' . $e->getMessage());

            return view('layouts/single_page', array_merge($data, [
                'pageTitle' => lang('Notifications.Failed_to_create_subscription'),
                'alert' => [
                    'success' => false,
                    'message' => lang('Notifications.Failed_to_create_subscription') . ': ' . $e->getMessage(),
                    'redirect' => base_url('subscription/packages'),
                ]
            ]));            
        }
    }

    /**
     * Handle successful subscription
     */
    public function success()
    {
        $data['userData'] = $this->userAcctDetails;

        try {
            $subscriptionId = $this->request->getGet('subscription_id');
            if (!$subscriptionId) {
                return view('layouts/single_page', array_merge($data, [
                    'pageTitle' => lang('Notifications.Invalid_subscription_ID'),
                    'alert' => [
                        'success' => false,
                        'message' => lang('Notifications.Invalid_subscription_ID'),
                        'redirect' => base_url('subscription/packages'),
                    ]
                ]));
            }

            $getSubscription = $this->SubscriptionModel->where([
                'subscription_reference' => $subscriptionId,
                'user_id' => $this->userID
            ])->first();

            log_message('debug', '[SubscriptionController] Success new subscription data: ' . json_encode($getSubscription));

            // Select the appropriate payment service based on the payment method
            $paymentMethodName = $getSubscription['payment_method'];
            $paymentMethod = $this->PaymentMethods[$paymentMethodName] ?? $paymentMethodName;
            $paymentServiceName = $paymentMethod['service_name'] ?? $paymentMethodName;
            $paymentService = $this->ModuleScanner->loadLibrary($paymentMethodName, $paymentServiceName);

            // Get subscription details from the payment method
            $subscription = $paymentService->getSubscription($subscriptionId);
            
            // Verify subscription status
            if ($subscription->status !== 'ACTIVE') {
                return view('layouts/single_page', array_merge($data, [
                    'pageTitle' => lang('Notifications.Subscription_not_active'),
                    'alert' => [
                        'success' => false,
                        'message' => lang('Notifications.Subscription_not_active'),
                        'redirect' => base_url('subscription/packages'),
                    ]
                ]));
            }

            return view('layouts/single_page', array_merge($data, [
                'pageTitle' => lang('Notifications.Subscription_activated_successfully'),
                'alert' => [
                    'success' => true,
                    'message' => lang('Notifications.Subscription_activated_successfully'),
                    'redirect' => base_url('subscription/my-subscription'),
                ]
            ]));
        } catch (\Exception $e) {
            log_message('error', '[SubscriptionController] Success handler failed: ' . $e->getMessage());

            return view('layouts/single_page', array_merge($data, [
                'pageTitle' => lang('Notifications.Failed_to_process_subscription'),
                'alert' => [
                    'success' => false,
                    'message' => lang('Notifications.Failed_to_process_subscription') . ': ' . $e->getMessage(),
                    'redirect' => base_url('subscription/packages'),
                ]
            ]));
        }
    }

    /**
     * Handle cancelled subscription
     */
    public function cancel()
    {
        $data['userData'] = $this->userAcctDetails;

        return view('layouts/single_page', array_merge($data, [
            'pageTitle' => lang('Notifications.Subscription_process_cancelled'),
            'alert' => [
                'success' => false,
                'message' => lang('Notifications.Subscription_process_cancelled'),
                'redirect' => base_url('subscription/my-subscription'),
            ]
        ]));
    }

    /**
     * Cancel an active subscription
     */
    public function cancelSubscription()
    {
        $data['userData'] = $this->userAcctDetails;

        try {
            $userId = $this->userID;
            $activeSubscription = $this->SubscriptionModel->getActiveByUserId($userId);
            $reason = $this->request->getPost('reason');

            if (!$activeSubscription) {
                return view('layouts/single_page', array_merge($data, [
                    'pageTitle' => lang('Notifications.No_active_subscription_found'),
                    'alert' => [
                        'success' => false,
                        'message' => lang('Notifications.No_active_subscription_found'),
                        'redirect' => base_url('subscription/packages'),
                    ]
                ]));
            }

            // Select the appropriate payment service based on the payment method
            $paymentMethodName = $activeSubscription['payment_method'];
            $paymentMethod = $this->PaymentMethods[$paymentMethodName] ?? $paymentMethodName;
            $paymentServiceName = $paymentMethod['service_name'] ?? $paymentMethodName;
            $paymentService = $this->ModuleScanner->loadLibrary($paymentMethodName, $paymentServiceName);

            // Cancel subscription in selected payment using subscription_reference
            $paymentService->cancelSubscription(
                $activeSubscription['subscription_reference'],
                $reason ?? 'Cancelled by user'
            );

            // Update local subscription status with reason
            $this->SubscriptionModel->cancelSubscription(
                $activeSubscription['subscription_reference'],
                $reason
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => lang('Notifications.Subscription_cancelled_successfully')
            ]);

        } catch (\Exception $e) {
            log_message('error', '[SubscriptionController] Cancel subscription failed: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.Failed_to_cancel_subscription'). ': ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get subscription details
     */
    public function getDetails($subscriptionId)
    {
        try {
            $userId = $this->userID;
            $subscription = $this->SubscriptionModel->where([
                'subscription_reference' => $subscriptionId,
                'user_id' => $userId
            ])->first();

            if (!$subscription) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Subscription not found'
                ])->setStatusCode(404);
            }

            // Select the appropriate payment service based on the payment method
            $paymentMethodName = $subscription['payment_method'];
            $paymentMethod = $this->PaymentMethods[$paymentMethodName] ?? $paymentMethodName;
            $paymentServiceName = $paymentMethod['service_name'] ?? $paymentMethodName;
            $paymentService = $this->ModuleScanner->loadLibrary($paymentMethodName, $paymentServiceName);

            // Get ubscription details by payment method
            $paymentServiceSubscription = $paymentService->getSubscription($subscriptionId);

            // Get latest payment status
            $latestPayment = $this->PaymentModel
                ->where('subscription_id', $subscriptionId)
                ->orderBy('payment_date', 'DESC')
                ->first();

            // Merge local and payment method data
            $subscriptionDetails = array_merge($subscription, [
                'payment_status' => $paymentServiceSubscription->status,
                'next_billing' => $paymentServiceSubscription->billing_info->next_billing_time ?? null,
                'last_payment' => $paymentServiceSubscription->billing_info->last_payment->time ?? null,
                'latest_payment_status' => $latestPayment['payment_status'] ?? null
            ]);

            return $this->response->setJSON([
                'success' => true,
                'data' => $subscriptionDetails
            ]);

        } catch (\Exception $e) {
            log_message('error', '[SubscriptionController] Get details failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to get subscription details'
            ])->setStatusCode(500);
        }
    }

    /**
     * Handle Thank You Page
     */
    public function thankYou()
    {
        $data['userData'] = $this->userAcctDetails;
        $data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
        $data['myConfig'] = $this->myConfig;

        return view('layouts/single_page', array_merge($data, [
            'pageTitle' => lang('Notifications.Thank_you_title'),
            'alert' => [
                'success' => true,
                'message' => lang('Notifications.Thank_you_subscription_message', ['appName' => $this->myConfig['appName']]),
                'redirect' => base_url('subscription/my-subscription'),
            ]
        ]));
    }

    /**
     * Handle Default Error Page
     */
    public function error()
    {
        $data['userData'] = $this->userAcctDetails;

        return view('layouts/single_page', array_merge($data, [
            'pageTitle' => lang('Notifications.Subscription_general_error_title'),
            'alert' => [
                'success' => false,
                'message' => lang('Notifications.Subscription_general_error_message'),
                'redirect' => base_url('subscription/my-subscription'),
            ]
        ]));
    }
	
	/**
     * Reactivate a cancelled subscription
     */
    public function reactivate($id)
    {
        $data['userData'] = $this->userAcctDetails;

        try {
            // Get subscription details
            $subscription = $this->SubscriptionModel->where('id', $id)->first();

            // Verify subscription exists and belongs to user
            if (!$subscription || ((int)$subscription['user_id'] !== $this->userID)) {
                return view('layouts/single_page', array_merge($data,[
                    'pageTitle' => lang('Notifications.Invalid_subscription'),
                    'alert' => [
                        'success' => false,
                        'message' => lang('Notifications.Invalid_subscription'),
                        'redirect' => base_url('subscription/my-subscription'),
                    ]
                ]));
            }

            // Verify subscription is cancelled and has remaining days
            if ($subscription['subscription_status'] !== 'cancelled') {
                return view('layouts/single_page', array_merge($data,[
                    'pageTitle' => lang('Notifications.Subscription_not_cancelled'),
                    'alert' => [
                        'success' => false,
                        'message' => lang('Notifications.Subscription_not_cancelled'),
                        'redirect' => base_url('subscription/my-subscription'),
                    ]
                ]));
            }

            $currentDate = Time::now()->setTimezone('UTC');
            $endDate = Time::parse($subscription['end_date'], 'UTC');
            
            if ($currentDate > $endDate) {
                return view('layouts/single_page', array_merge($data,[
                    'pageTitle' => lang('Notifications.Subscription_expired'),
                    'alert' => [
                        'success' => false,
                        'message' => lang('Notifications.Subscription_expired'),
                        'redirect' => base_url('subscription/my-subscription'),
                    ]
                ]));
            }

            // Update local subscription status
            $this->SubscriptionModel->update($id, [
                'subscription_status' => 'active',
                'is_reactivated' => 'yes',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return view('layouts/single_page', array_merge($data,[
                'pageTitle' => lang('Notifications.Subscription_reactivated_successfully'),
                'alert' => [
                    'success' => true,
                    'message' => lang('Notifications.Subscription_reactivated_successfully'),
                    'redirect' => base_url('subscription/my-subscription'),
                ]
            ]));

        } catch (\Exception $e) {
            log_message('error', '[SubscriptionController] Reactivate subscription failed: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.Failed_to_reactivate_subscription') . ': ' . $e->getMessage()
            ]);
        }
    }
    
    public function subscribe_for_trial()
    {
        if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}
		
		// Check if user has had trial previously
		$hasHadTrialPackage = $this->SubscriptionModel->hasHadTrialPackage($this->userID);
        if($hasHadTrialPackage) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => lang('Notifications.User_has_already_had_a_trial'),
            ]);
        }
		
		// Get the default/trial packages
		$defaultPackage = !$hasHadTrialPackage ? $this->PackageModel->getDefaultPackage() : null;
		
		// Cancel current subscription if user is active before applying trial packages
		$activeSubscription = $this->SubscriptionModel->getActiveByUserId($this->userID);

        if($activeSubscription && $defaultPackage && ($activeSubscription['id'] !== $defaultPackage['id'])) {
            $reason = 'Claimed the trial package';

            // Select the appropriate payment service based on the payment method
            $paymentMethodName = $activeSubscription['payment_method'];
            $paymentMethod = $this->PaymentMethods[$paymentMethodName] ?? $paymentMethodName;
            $paymentServiceName = $paymentMethod['service_name'] ?? $paymentMethodName;
            $paymentService = $this->ModuleScanner->loadLibrary($paymentMethodName, $paymentServiceName);

            // Cancel subscription in selected payment using subscription_reference
            $paymentService->cancelSubscription(
                $activeSubscription['subscription_reference'],
                $reason ?? 'Cancelled by user'
            );

            // Update local subscription status with reason
            $this->SubscriptionModel->cancelSubscription(
                $activeSubscription['subscription_reference'],
                $reason
            );
        }

		// Apply the default/trial package
        $trialService = new \App\Libraries\TrialService();

        $createTrialSubscription = $trialService->newSubscription($defaultPackage['id']);
		
        if(!$createTrialSubscription) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => implode("\n", $trialService->getLastErrors()),
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'msg' => lang('Notifications.Trial_subscription_activated_successfully'),
            'redirect' => base_url('subscription/my-subscription'),
        ]);
    }
}
