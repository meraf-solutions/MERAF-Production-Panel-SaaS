<?php

namespace App\Modules\Offline\Controllers;

use App\Controllers\Home;
use App\Modules\Offline\Libraries\OfflineService;

class Offline extends Home
{
    protected $offlineService;

    public function __construct()
    {
        parent::__construct();
        $this->offlineService = new OfflineService();
    }

    public function index()
    {
        return $this->showPaymentForm();
    }

    public function payment($subscriptionReference = null)
    {
        return $this->showPaymentForm($subscriptionReference);
    }

    private function showPaymentForm($subscriptionReference = null)
    {
        $subscription = [];
        $selectedPackage = [];
    
        // Required data for dashboard layout
        $data['pageTitle'] = lang('Pages.Payment_Options') . ' | ' . lang('Pages.index_offline_payment');
        $data['section'] = 'Payment_Options';
        $data['subsection'] = 'Offline_Payment';
        $data['sideBarMenu'] = $this->sideBarMenu;
        $data['userData'] = $this->userAcctDetails;
        $data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
        $data['myConfig'] = $this->myConfig;
    
        // Load the form helper
        helper('form');
    
        // Get the offline payment instructions from settings
        $data['paymentInstructions'] = $this->myConfig['OFFLINE_PAYMENT_INSTRUCTIONS'] ?? '';
    
        if ($subscriptionReference) {
            $subscription = $this->offlineService->getSubscription($subscriptionReference);
            $pendingInvoice = $this->PaymentModel->where('payment_status', 'pending')
                                                 ->where('subscription_id', $subscriptionReference)
                                                 ->first();
    
            log_message('debug', '[Offline/OfflineController] Pending invoice for payment: ' . json_encode($pendingInvoice));
    
            if (!$subscription) {
                return redirect()->to('/subscription/packages')->with('error', lang('Notifications.Invalid_Subscription'));
            }
    
            if (empty($pendingInvoice)) {
                return redirect()->to('/subscription/my-subscription')->with('error', lang('Notifications.Error'));
            }
    
            // Check if transaction_id has completed payment reference contains the separator
            if (isset($pendingInvoice['transaction_id']) && strpos($pendingInvoice['transaction_id'], ':') !== false) {
                // New format: package_id:user_id
                list($subscription->payment_reference, $subscription->transaction_id) = explode(':', $pendingInvoice['transaction_id']);
            } else {
                // Handle the case where transaction_id is not set or doesn't contain the separator
                $subscription->payment_reference = $pendingInvoice['transaction_id'] ?? null;
                $subscription->transaction_id = null;
            }
    
            $selectedPackage = $this->PackageModel->find($subscription->package_id);
        }
    
        $data['selectedPackage'] = $selectedPackage;
        $data['subscription'] = $subscription;
    
        // Display the form for entering the reference ID
        return view('App\Modules\Offline\Views\reference_form', $data);
    }

    public function processPayment()
    {
        $data['userData'] = $this->userAcctDetails;
        
        log_message('info', '[Offline/OfflineController] Starting processPayment');
        
        $referenceId = $this->request->getPost('reference_id');
        $subscriptionReference = $this->request->getPost('subscription_reference');

        log_message('info', '[Offline/OfflineController] Reference ID: ' . $referenceId);
        log_message('info', '[Offline/OfflineController] Subscription Reference: ' . $subscriptionReference);

        if (empty($referenceId)) {
            log_message('error', '[Offline/OfflineController] Reference ID is empty');
            // return redirect()->back()->withInput()->with('error', lang('Notifications.Offline_Payment_Failed'));
            return view('layouts/single_page', array_merge($data,[
                'pageTitle' => lang('Notifications.Error'),
                'alert' => [
                    'success' => false,
                    'message' => lang('Notifications.Offline_Payment_Failed'),
                    'redirect' => base_url('subscription/my-subscription'),
                ]
            ]));
        }

        if ($subscriptionReference) {
            log_message('info', '[Offline/OfflineController] Updating existing subscription');
            // Update existing subscription
            $subscription = $this->offlineService->getSubscription($subscriptionReference);
            if (!$subscription) {
                log_message('error', '[Offline/OfflineController] Invalid subscription: ' . $subscriptionReference);
                // return redirect()->back()->withInput()->with('error', lang('Notifications.Invalid_Subscription'));
                return view('layouts/single_page', array_merge($data,[
                    'pageTitle' => lang('Notifications.Error'),
                    'alert' => [
                        'success' => false,
                        'message' => lang('Notifications.Invalid_Subscription'),
                        'redirect' => base_url('subscription/my-subscription'),
                    ]
                ]));
            }

            $updated = $this->offlineService->updateSubscriptionPayment($subscriptionReference, $referenceId);
        } else {
            log_message('info', '[Offline/OfflineController] Creating new subscription');
            // Create a new subscription
            $packageId = session()->get('selected_plan_id');
            log_message('info', '[Offline/OfflineController] Package ID: ' . $packageId);
            $updated = $this->offlineService->newSubscription($packageId, $referenceId);
        }

        if (!$updated) {
            log_message('error', '[Offline/OfflineController] Payment update failed');
            // return redirect()->back()->withInput()->with('error', lang('Notifications.Payment_Update_Failed'));
            return view('layouts/single_page', array_merge($data,[
                'pageTitle' => lang('Notifications.Error'),
                'alert' => [
                    'success' => false,
                    'message' => lang('Notifications.Payment_Update_Failed'),
                    'redirect' => base_url('subscription/my-subscription'),
                ]
            ]));
        }

        log_message('info', '[Offline/OfflineController] Payment processed successfully');

        // Clear session data
        $session = session();
        $session->remove(['selected_plan_id', 'plan_amount', 'plan_currency']);

        // Redirect to a confirmation page
        // return redirect()->to('/payment/confirmation')->with('success', lang('Notifications.Offline_Payment_Pending'));
        return view('layouts/single_page', array_merge($data,[
            'pageTitle' => lang('Notifications.Offline_Payment_Pending'),
            'alert' => [
                'success' => true,
                'message' => lang('Notifications.Offline_Payment_Pending'),
                'redirect' => base_url('subscription/my-subscription'),
            ]
        ]));
    }
}
