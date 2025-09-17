<?php

namespace App\Controllers\Admin;

use CodeIgniter\I18n\Time;
use App\Controllers\Admin\AdminController;
use App\Models\SubscriptionModel;
use App\Services\ModuleScanner;

class SubscriptionController extends AdminController
{
    protected $SubscriptionModel;
    protected $ModuleScanner;

    public function __construct()
    {
        parent::__construct();

        // Initialize
        $this->SubscriptionModel = new SubscriptionModel();
        $this->ModuleScanner = new ModuleScanner();
    }

    public function subscription_list_page()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }

        $data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Subscription_Manager') . ' | ' . lang('Pages.Subscription_List');
        $data['section'] = 'Subscription_Manager';
        $data['subsection'] = 'Subscription_List';
        $data['sideBarMenu'] = $this->sideBarMenu;
        $data['userData'] = auth()->user();
        $data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
        $data['myConfig'] = $this->myConfig;

        try {
            log_message('debug', '[Admin/SubscriptionController] Starting subscription list query');
            
            $subscriptions = $this->SubscriptionModel
                ->select('subscriptions.id, subscriptions.subscription_status, subscriptions.payment_method, subscriptions.start_date, subscriptions.next_payment_date, auth_identities.secret as email, users.username, users.first_name, users.last_name, package.package_name')
                ->join('users', 'users.id = subscriptions.user_id')
                ->join('auth_identities', 'auth_identities.user_id = users.id')
                ->join('package', 'package.id = subscriptions.package_id')
                ->where('auth_identities.type', 'email_password')
                ->orderBy('subscriptions.created_at', 'DESC')
                ->findAll();
            
            log_message('debug', '[Admin/SubscriptionController] Subscription list query completed successfully: ' . json_encode($subscriptions));
            $data['subscriptions'] = $subscriptions;
        } catch (\Exception $e) {
            log_message('error', '[Admin/SubscriptionController] Fetch subscriptions failed: ' . $e->getMessage());
            log_message('error', '[Admin/SubscriptionController] Stack trace: ' . $e->getTraceAsString());
            $data['subscriptions'] = [];
            $data['error'] = lang('Notifications.failed_to_fetch_subscriptions');
        }

        return view('dashboard/admin/subscription/subscription_list', $data);
    }

    /**
     * View subscription details
     */
    public function subscription_view_page($id)
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }

        $data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Subscription_Manager') . ' | ' . lang('Pages.Subscription_Details');
        $data['section'] = 'Subscription_Manager';
        $data['subsection'] = 'Subscription_Details';
        $data['sideBarMenu'] = $this->sideBarMenu;
        $data['userData'] = auth()->user();
        $data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
        $data['myConfig'] = $this->myConfig;

        $subscription = $this->SubscriptionModel
            ->select('subscriptions.*, auth_identities.secret as email, users.first_name, users.last_name, package.package_name')
            ->join('users', 'users.id = subscriptions.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id')
            ->join('package', 'package.id = subscriptions.package_id')
            ->where('auth_identities.type', 'email_password')
            ->where('subscriptions.id', $id)
            ->first();

        if (!$subscription) {
            return redirect()->back()->with('error', lang('Notifications.subscription_not_found'));
        }

        // Select the appropriate payment service based on the payment method
        $paymentMethodName = $subscription['payment_method'];
        $paymentMethod = $this->PaymentMethods[$paymentMethodName] ?? $paymentMethodName;
        $paymentServiceName = $paymentMethod['service_name'] ?? $paymentMethodName;
        $paymentService = $this->ModuleScanner->loadLibrary($paymentMethodName, $paymentServiceName);

        // Get subscription details from the payment method
        try {
            $paymentSubscription = (object)$paymentService->getSubscription($subscription['subscription_reference']);

            log_message('debug', '[Admin/SubscriptionController] Payment Subscription raw data: ' . json_encode($paymentSubscription));
            
            $subscription['payment_method_details'] = $paymentSubscription;
        } catch (\Exception $e) {
            log_message('error', '[Admin/SubscriptionController] Get Payment Method details failed: ' . $e->getMessage());
            $subscription['payment_method_details'] = null;
        }

        // Get payment history
        $payments = $this->PaymentModel
            ->where('subscription_id', $subscription['subscription_reference'])
            ->orderBy('payment_date', 'DESC')
            ->findAll();

        $data['subscription'] = $subscription;
        $data['payments'] = $payments;

        return view('dashboard/admin/subscription/subscription_view', $data);
    }

    /**
     * View payment history
     */
    public function subscription_payments_page($id)
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }

        $data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Subscription_Manager') . ' | ' . lang('Pages.Payments_History');
        $data['section'] = 'Subscription_Manager';
        $data['subsection'] = 'Payments_History';
        $data['sideBarMenu'] = $this->sideBarMenu;
        $data['userData'] = auth()->user();
        $data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
        $data['myConfig'] = $this->myConfig;

        $subscription = $this->SubscriptionModel
            ->select('subscriptions.*, auth_identities.secret as email, package.package_name')
            ->join('users', 'users.id = subscriptions.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id')
            ->join('package', 'package.id = subscriptions.package_id')
            ->where('auth_identities.type', 'email_password')
            ->where('subscriptions.id', $id)
            ->first();

        if (!$subscription) {
            return redirect()->back()->with('error', lang('Notifications.subscription_not_found'));
        }

        $payments = $this->PaymentModel
            ->where('subscription_id', $subscription['subscription_reference'])
            ->orderBy('payment_date', 'DESC')
            ->findAll();

        $data['subscription'] = $subscription;
        $data['payments'] = $payments;

        return view('dashboard/admin/subscription/payment_history', $data);
    }

    /**
     * View payment details
     */
    public function subscription_payments_details($transactionId)
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }

        // Find the subscription by transaction ID
        $data = $this->SubscriptionModel->where('transaction_id', $transactionId)->first();
        
        // Check if subscription exists
        if (!$data) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.transaction_not_found')
            ])->setStatusCode(404);
        }

        // Find the user
        $user = $this->UserModel->where('id', $data['user_id'])->first();
        
        // Check if user exists
        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.user_not_found')
            ])->setStatusCode(404);
        }

        // Add email to data
        $data['email_address'] = $user->email;

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);        
    }

    /**
     * View reports page
     */
    public function subscription_reports_page()
    {
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		$data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Subscription_Manager') . ' | ' . lang('Pages.Reports');
		$data['section'] = 'Subscription_Manager';
		$data['subsection'] = 'Reports';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = auth()->user();
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;

		$data['summary'] = $this->getSubscriptionSummary();

		return view('dashboard/admin/subscription/reports', $data);
    }

    /**
     * Suspend subscription
     */
	public function subscription_suspend($id)
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }

        try {
            $subscription = $this->SubscriptionModel->find($id);
            if (!$subscription) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Notifications.subscription_not_found')
                ])->setStatusCode(404);
            }

            // Select the appropriate payment service based on the payment method
            $paymentMethodName = $subscription['payment_method'];

            $paymentMethod = $this->PaymentMethods[$paymentMethodName] ?? $paymentMethodName;
            $paymentServiceName = $paymentMethod['service_name'] ?? $paymentMethodName;
            $paymentService = $this->ModuleScanner->loadLibrary($paymentMethodName, $paymentServiceName);

            $paymentSubscription = $paymentService->getSubscription($subscription['subscription_reference']);

            // Get subscription details from the payment method
            if (strtoupper($paymentSubscription->status) === 'SUSPENDED') {
                // Update our local status to match Payment Method's status if it's not already suspended
                if ($subscription['subscription_status'] !== 'suspended') {
                    try {
                        $updateResult = $this->SubscriptionModel->updateSubscriptionStatus(
                            $subscription['subscription_reference'],
                            'suspended'
                        );
                        if ($updateResult) {
                            log_message('info', "[Admin/SubscriptionController] Local database updated for subscription ID: {$id}. Status set to suspended.");
                        } else {
                            log_message('error', "[Admin/SubscriptionController] Failed to update local database for subscription ID: {$id} with Payment Subscription Status ".$paymentSubscription->status." to suspended. Update result: " . print_r($updateResult, true));
                            log_message('error', "[Admin/SubscriptionController] Last database error: " . print_r($this->SubscriptionModel->db->error(), true));
                        }
                    } catch (\Exception $e) {
                        log_message('error', "[Admin/SubscriptionController] Exception when updating local database for subscription ID: {$id}. Error: " . $e->getMessage());
                    }
                }
                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Notifications.subscription_already_suspended', ['0' => $paymentMethodName])
                ]);
            }

            // If the subscription is active in subscription's payment method, proceed with suspension
            if (strtoupper($paymentSubscription->status) === 'ACTIVE') {
                // Suspend in subscription's payment method
                $paymentService->suspendSubscription(
                    $subscription['subscription_reference'],
                    $this->request->getPost('reason') ?? 'Suspended by admin'
                );

                // Update local status
                try {
                    $updateResult = $this->SubscriptionModel->updateSubscriptionStatus(
                        $subscription['subscription_reference'],
                        'suspended'
                    );
                    if ($updateResult) {
                        log_message('info', "[Admin/SubscriptionController] Local database updated for subscription ID: {$id}. Status set to suspended.");
                    } else {
                        log_message('error', "[Admin/SubscriptionController] Failed to update local database for subscription ID: {$id} with Payment Subscription Status ".$paymentSubscription->status." to suspended. Update result: " . print_r($updateResult, true));
                        log_message('error', "[Admin/SubscriptionController] Last database error: " . print_r($this->SubscriptionModel->db->error(), true));
                    }
                } catch (\Exception $e) {
                    log_message('error', "[Admin/SubscriptionController] Exception when updating local database for subscription ID: {$id}. Error: " . $e->getMessage());
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Notifications.subscription_suspended_successfully')
                ]);
            }

            // If the subscription is in any other state
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.Cannot_suspend_subscription', ['s' => $paymentSubscription->status])
            ])->setStatusCode(400);

        } catch (\Exception $e) {
            log_message('error', '[Admin/SubscriptionController] Suspend failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.failed_to_suspend_subscription', ['0' => $e->getMessage()])
            ])->setStatusCode(500);
        }
    }

    /**
     * Activate subscription
     */
	public function subscription_activate($id)
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }

        try {
            $subscription = $this->SubscriptionModel->find($id);
            if (!$subscription) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Notifications.subscription_not_found')
                ])->setStatusCode(404);
            }

            // Select the appropriate payment service based on the payment method
            $paymentMethodName = $subscription['payment_method'];

            $paymentMethod = $this->PaymentMethods[$paymentMethodName] ?? $paymentMethodName;
            $paymentServiceName = $paymentMethod['service_name'] ?? $paymentMethodName;
            $paymentService = $this->ModuleScanner->loadLibrary($paymentMethodName, $paymentServiceName);
            
            $paymentSubscription = $paymentService->getSubscription($subscription['subscription_reference']);

            // Get subscription details from the payment method
            if (strtoupper($paymentSubscription->status) === 'ACTIVE') {
                // Update our local status to match Payment Method's status if it's not already active
                if ($subscription['subscription_status'] !== 'active') {
                    try {
                        $updateResult = $this->SubscriptionModel->updateSubscriptionStatus(
                            $subscription['subscription_reference'],
                            'active'
                        );
                        if ($updateResult) {
                            log_message('info', "[Admin/SubscriptionController] Local database updated for subscription ID: {$id}. Status set to active.");
                        } else {
                            log_message('error', "[Admin/SubscriptionController] Failed to update local database for subscription ID: {$id} with Payment Subscription Status ".$paymentSubscription->status." to active. Update result: " . print_r($updateResult, true));
                            log_message('error', "[Admin/SubscriptionController] Last database error: " . print_r($this->SubscriptionModel->db->error(), true));
                        }
                    } catch (\Exception $e) {
                        log_message('error', "[Admin/SubscriptionController] Exception when updating local database for subscription ID: {$id}. Error: " . $e->getMessage());
                    }
                }
                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Notifications.subscription_already_active', ['0' => $paymentMethodName])
                ]);
            }

            // If the subscription is suspended in subscription's payment method, proceed with activation
            if (strtoupper($paymentSubscription->status) === 'SUSPENDED') {
                // Activate in subscription's payment method
                $paymentService->activateSubscription(
                    $subscription['subscription_reference'],
                    $this->request->getPost('reason') ?? 'Activated by admin'
                );

                // Update local status
                try {
                    $updateResult = $this->SubscriptionModel->updateSubscriptionStatus(
                        $subscription['subscription_reference'],
                        'active'
                    );
                    if ($updateResult) {
                        log_message('info', "[Admin/SubscriptionController] Local database updated for subscription ID: {$id}. Status set to active.");
                    } else {
                        log_message('error', "[Admin/SubscriptionController] Failed to update local database for subscription ID: {$id} with Payment Subscription Status ".$paymentSubscription->status." to suspended. Update result: " . print_r($updateResult, true));
                        log_message('error', "[Admin/SubscriptionController] Last database error: " . print_r($this->SubscriptionModel->db->error(), true));
                    }
                } catch (\Exception $e) {
                    log_message('error', "[Admin/SubscriptionController] Exception when updating local database for subscription ID: {$id}. Error: " . $e->getMessage());
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Notifications.subscription_activated_successfully')
                ]);
            }

            // If the subscription is in any other state
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.Cannot_activate_subscription', ['s' => $paymentSubscription->status])
            ])->setStatusCode(400);

        } catch (\Exception $e) {
            log_message('error', '[Admin/SubscriptionController] Activate failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to activate subscription: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Cancel subscription
     */
	public function subscription_cancel($id)
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }

        try {
            $subscription = $this->SubscriptionModel->find($id);
            if (!$subscription) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Notifications.subscription_not_found')
                ])->setStatusCode(404);
            }

            // Select the appropriate payment service based on the payment method
            $paymentMethodName = $subscription['payment_method'];
            
            $paymentMethod = $this->PaymentMethods[$paymentMethodName] ?? $paymentMethodName;
            $paymentServiceName = $paymentMethod['service_name'] ?? $paymentMethodName;
            $paymentService = $this->ModuleScanner->loadLibrary($paymentMethodName, $paymentServiceName);

            // Get subscription details from the payment method
            $paymentSubscription = $paymentService->getSubscription($subscription['subscription_reference']);

            if (strtoupper($paymentSubscription->status) === 'CANCELLED') {
                // Update our local status to match Payment Method's status if it's not already cancelled
                if ($subscription['subscription_status'] !== 'cancelled') {
                    try {
                        $updateResult = $this->SubscriptionModel->cancelSubscription(
                            $subscription['subscription_reference'],
                            'Synced DB status with Payment Method',
                            true
                        );

                        if ($updateResult) {
                            log_message('info', "[Admin/SubscriptionController] Local database updated for subscription ID: {$id}. Status set to cancelled.");
                        } else {
                            log_message('error', "[Admin/SubscriptionController] ailed to update local database for subscription ID: {$id} with Payment Subscription Status ".$paymentSubscription->status." to cancelled. Update result: " . print_r($updateResult, true));
                            log_message('error', "[Admin/SubscriptionController] Last database error: " . print_r($this->SubscriptionModel->db->error(), true));
                        }
                    } catch (\Exception $e) {
                        log_message('error', "[Admin/SubscriptionController] Exception when updating local database for subscription ID: {$id}. Error: " . $e->getMessage());
                    }
                }
                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Notifications.subscription_already_cancelled', ['0' => $paymentMethodName])
                ]);
            }

            // If the subscription is active or suspended in subscription's payment method, proceed with cancellation
            if (in_array(strtoupper($paymentSubscription->status), ['ACTIVE', 'SUSPENDED'])) {
                // Cancel using the subscription's payment method
                $reason = isset($this->request) ? $this->request->getPost('reason') : 'Cancelled by admin';

                $paymentService->cancelSubscription(
                                        $subscription['subscription_reference'],
                                        $reason,
                                    );

                // Update local status
                try {
                    $updateResult = $this->SubscriptionModel->cancelSubscription(
                        $subscription['subscription_reference'],
                        'Cancelled by admin',
                        true
                    );

                    if ($updateResult) {
                        log_message('info', "[Admin/SubscriptionController] Local database updated for subscription ID: {$id}. Status set to cancelled.");
                    } else {
                        log_message('error', "[Admin/SubscriptionController] Failed to update local database for subscription ID: {$id} with Payment Subscription Status ".$paymentSubscription->status." to cancelled. Update result: " . print_r($updateResult, true));
                        log_message('error', "[Admin/SubscriptionController] Last database error: " . print_r($this->SubscriptionModel->db->error(), true));
                    }
                } catch (\Exception $e) {
                    log_message('error', "[Admin/SubscriptionController] Exception when updating local database for subscription ID: {$id}. Error: " . $e->getMessage());
                }

                $response = [
                    'success' => true,
                    'message' => lang('Notifications.subscription_cancelled_successfully')
                ];

                return json_encode($response);
            }

            // If the subscription is in any other state
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.Cannot_cancel_subscription', ['s' => $paymentSubscription->status])
            ])->setStatusCode(400);

        } catch (\Exception $e) {
            log_message('error', '[Admin/SubscriptionController] Cancellation failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.failed_to_cancel_subscription', ['0' => $e->getMessage()])
            ])->setStatusCode(500);
        }
    }

    private function generateSubscriptionReport($startDate, $endDate, $type)
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }

        try {
            switch ($type) {
                case 'revenue':
                    return $this->PaymentModel->getPaymentsSummary($startDate, $endDate);
                case 'subscriptions':
                    return $this->PaymentModel->getSubscriptionReport($startDate, $endDate);
                default:
                    return [];
            }
        } catch (\Exception $e) {
            log_message('error', '[Admin/SubscriptionController] Generate report failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get subscription summary
     */
    private function getSubscriptionSummary()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }

        $activeCount = $this->SubscriptionModel->where('subscription_status', 'active')->countAllResults();
        
        // Calculate total revenue using the new getPaymentsSummary method
        $allTimeRevenue = $this->PaymentModel->getPaymentsSummary('1970-01-01', date('Y-m-d'));
        $totalRevenue = array_reduce($allTimeRevenue, function($carry, $item) {
            return $carry + $item['net_revenue'];
        }, 0);
        
        $recentPayments = $this->PaymentModel->select('subscription_payments.*, subscriptions.user_id, auth_identities.secret as email')
                                            ->join('subscriptions', 'subscriptions.subscription_reference = subscription_payments.subscription_id')
                                            ->join('auth_identities', 'auth_identities.user_id = subscriptions.user_id')
                                            ->where('auth_identities.type', 'email_password')
                                            ->where('subscription_payments.payment_status', 'completed')
                                            ->orderBy('subscription_payments.payment_date', 'DESC')
                                            ->limit(5)
                                            ->findAll();

        $pendingPayments = $this->PaymentModel->where('payment_status', 'pending')->countAllResults();
        $failedPayments = $this->PaymentModel->where('payment_status', 'failed')->countAllResults();

        return [
            'active_subscriptions' => $activeCount,
            'total_revenue' => $totalRevenue,
            'recent_payments' => $recentPayments,
            'pending_payments' => $pendingPayments,
            'failed_payments' => $failedPayments
        ];
    }
	
    /**
     * Generate subscription report
     */
    public function subscription_generateReport()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }

        $startDate = Time::parse($this->request->getPost('start_date'))->toDateString();
        $endDate = Time::parse($this->request->getPost('end_date'))->toDateString();
        $type = $this->request->getPost('type');

        try {
            $report = $this->generateSubscriptionReport($startDate, $endDate, $type);

            return $this->response->setJSON([
                'success' => true,
                'data' => $report
            ]);

            log_message('debug', '[Admin/SubscriptionController] Generated report: ' . json_encode($report));
        } catch (\Exception $e) {
            log_message('error', '[Admin/SubscriptionController] Generate report failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.failed_to_generate_report', ['0' => $e->getMessage()])
            ])->setStatusCode(500);
        }
    }
	
	/**
     * Export subscription report
     */
    public function subscription_exportReport()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }
    
        // Get parameters from request
        $startDate = $this->request->getGet('start_date') ?? Time::now()->subDays(30)->toDateString();
        $endDate = $this->request->getGet('end_date') ?? Time::now()->toDateString();
        $type = $this->request->getGet('type') ?? 'revenue';
    
        try {
            // Generate report data
            $reportData = $this->generateSubscriptionReport($startDate, $endDate, $type);
    
            // Prepare CSV filename
            $filename = sprintf('subscription_%s_report_%s_to_%s.csv', 
                $type, 
                str_replace('-', '', $startDate), 
                str_replace('-', '', $endDate)
            );
    
            // Set headers for file download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
    
            // Create CSV output
            $output = fopen('php://output', 'w');
    
            // Normalize reportData to ensure it's an array
            $reportData = is_array($reportData) ? $reportData : [];
    
            // Write headers based on report type
            if ($type === 'revenue') {
                // Add UTF-8 BOM at the start
                fputs($output, "\xEF\xBB\xBF");
            
                fputcsv($output, [
                    lang('Pages.Currency'), 
                    lang('Pages.Number_of_Payments'), 
                    lang('Pages.Total_Amount'), 
                    lang('Pages.Refunded_Amount'), 
                    lang('Pages.Net_Revenue')
                ]);
                
                if (empty($reportData)) {
                    fputcsv($output, [lang('Notifications.No_revenue_found_for_the_period')]);
                } else {
                    foreach ($reportData as $row) {
                        // Ensure each row has the expected keys with default values
                        $safeRow = [
                            'currency' => $row['currency'] ?? 'N/A',
                            'count' => $row['count'] ?? 0,
                            'total' => $row['total'] ?? 0,
                            'refunded_total' => $row['refunded_total'] ?? 0,
                            'net_revenue' => $row['net_revenue'] ?? 0
                        ];
                        
                        fputcsv($output, [
                            $safeRow['currency'], 
                            $safeRow['count'], 
                            $safeRow['total'],
                            $safeRow['refunded_total'],
                            $safeRow['net_revenue']
                        ]);
                    }
                }
            } else {
                // Add UTF-8 BOM at the start
                fputs($output, "\xEF\xBB\xBF");
                
                fputcsv($output, [
                    lang('Pages.Date'),
                    lang('Pages.Package'),
                    lang('Pages.Status'),
                    lang('Pages.User_Email')
                ]);
                
                if (empty($reportData)) {
                    fputcsv($output, [lang('Notifications.No_subscription_found_for_the_period')]);
                } else {
                    foreach ($reportData as $row) {
                        // Ensure each row has the expected keys with default values
                        $safeRow = [
                            'created_at' => $row['created_at'] ?? 'N/A',
                            'package_name' => $row['package_name'] ?? 'N/A',
                            'subscription_status' => $row['subscription_status'] ?? 'N/A',
                            'email' => $row['email'] ?? 'N/A'
                        ];
                        
                        fputcsv($output, [
                            $safeRow['created_at'], 
                            $safeRow['package_name'], 
                            $safeRow['subscription_status'], 
                            $safeRow['email']
                        ]);
                    }
                }
            }
    
            fclose($output);
            exit;
    
        } catch (\Exception $e) {
            log_message('error', '[Admin/SubscriptionController] Export report failed: ' . $e->getMessage());
            log_message('error', '[Admin/SubscriptionController] Export details - Start Date: ' . $startDate . ', End Date: ' . $endDate . ', Type: ' . $type);
            
            // Return a JSON response if headers haven't been sent
            if (!headers_sent()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Notifications.failed_to_export_report', ['0' => $e->getMessage()])
                ])->setStatusCode(500);
            } else {
                // If headers are already sent, just log the error
                die(lang('Notifications.error_generating_report'));
            }
        }
    }
}
