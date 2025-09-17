<?php

namespace App\Modules\Offline\Controllers\Admin;

use App\Controllers\Subscription;
use App\Models\UserSettingsModel;
use App\Modules\Offline\Libraries\OfflineService;

class OfflineController extends Subscription
{
    protected $UserSettingsModel;
    protected $OfflineService;
    protected $requiredSettings = [
        'OFFLINE_PAYMENT_INSTRUCTIONS'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->UserSettingsModel = new UserSettingsModel();
        $this->OfflineService = new OfflineService();

        // Initialize Offline payment settings if they don't exist
        $this->initializeOfflineSettings();
    }

    /**
     * Initialize Offline payment settings in the database
     */
    protected function initializeOfflineSettings()
    {
        foreach ($this->requiredSettings as $key) {
            if (!isset($this->myConfig[$key])) {
                $this->UserSettingsModel->setUserSetting($key, NULL, 0);
            }
        }
    }

    /**
     * Display Offline payment settings page
     */
    public function index()
    {
        // Check admin authorization
        if (!auth()->user() || !auth()->user()->inGroup('admin')) {
            return redirect()->to('forbidden')->with('error', lang('Pages.forbidden_error_msg'));
        }

        // Load form helper
        helper('form');

        // Required data for dashboard layout
        $data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Payment_Options') . ' | ' . lang('Pages.index_offline_payment');
        $data['section'] = 'Payment_Options';
        $data['subsection'] = 'Offline_Payment';
        $data['sideBarMenu'] = $this->sideBarMenu;
        $data['userData'] = $this->userAcctDetails;
        $data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
        $data['myConfig'] = $this->myConfig;
        $data['payments'] = $this->OfflineService->getOfflinePayments();

        return view('App\Modules\Offline\Views\Admin\index', $data);
    }

    /**
     * Save Offline payment settings
     */
    public function saveSettings()
    {
        // Check admin authorization
        if (!auth()->user() || !auth()->user()->inGroup('admin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Pages.forbidden_error_msg')
            ])->setStatusCode(403);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.Method_Not_Allowed')
            ])->setStatusCode(405);
        }

        try {
            // Save settings to database
            $settings = [
                'OFFLINE_PAYMENT_INSTRUCTIONS' => $this->request->getPost('payment_instructions')
            ];

            foreach ($settings as $key => $value) {
                $this->UserSettingsModel->setUserSetting($key, $value, 0);
            }

            // Clear cache to refresh settings
            clearCache();

            return $this->response->setJSON([
                'success' => true,
                'message' => lang('Notifications.Offline_Settings_Saved')
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Offline/AdminOfflineController] Save failed: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.Offline_Settings_Save_Failed') . ': ' . $e->getMessage()
            ]);
        }
    }

    public function updatePaymentStatus()
    {
        // Check admin authorization
        if (!auth()->user() || !auth()->user()->inGroup('admin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Pages.forbidden_error_msg')
            ])->setStatusCode(403);
        }

        $paymentId = $this->request->getPost('payment_id');
        $newStatus = $this->request->getPost('status');
        $refundAmount = $this->request->getPost('refund_amount');

        if ($newStatus === 'refunded' && !$refundAmount) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Notifications.Refund_Amount_Required')
            ]);
        }

        $result = $this->OfflineService->updatePaymentStatus($paymentId, $newStatus, $refundAmount);

        if ($result) {
            $message = $newStatus === 'refunded' 
                ? lang('Notifications.Offline_Payment_Refunded')
                : lang('Notifications.Offline_Payment_Status_Updated');

            return $this->response->setJSON([
                'success' => true,
                'message' => $message
            ]);
        } else {
            $message = $newStatus === 'refunded'
                ? lang('Notifications.Offline_Payment_Refund_Failed')
                : lang('Notifications.Offline_Payment_Status_Update_Failed');

            return $this->response->setJSON([
                'success' => false,
                'message' => $message
            ]);
        }
    }
}
