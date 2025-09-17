<?php

namespace App\Modules\PayPal\Controllers\Admin;

use App\Controllers\Subscription;
use App\Models\UserSettingsModel;
use App\Modules\PayPal\Libraries\PayPalService;

class SubscriptionController extends Subscription
{
    protected $UserSettingsModel;
    protected $PayPalService;
    protected $requiredSettings = [
        'PAYPAL_MODE',
        'PAYPAL_SANDBOX_CLIENT_ID',
        'PAYPAL_SANDBOX_CLIENT_SECRET',
        'PAYPAL_SANDBOX_WEBHOOK_ID',
        'PAYPAL_LIVE_CLIENT_ID',
        'PAYPAL_LIVE_CLIENT_SECRET',
        'PAYPAL_LIVE_WEBHOOK_ID'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->UserSettingsModel = new UserSettingsModel();
        $this->PayPalService = new PayPalService();

        // Initialize PayPal settings if they don't exist
        $this->initializePayPalSettings();
    }

    /**
     * Initialize PayPal settings in the database
     */
    protected function initializePayPalSettings()
    {
        foreach ($this->requiredSettings as $key) {
            if (!isset($this->myConfig[$key])) {
                $this->UserSettingsModel->setUserSetting($key, NULL, 0);
            }
        }
    }

    /**
     * Display PayPal settings page
     */
    public function settings()
    {
        // Check admin authorization
        if (!auth()->user() || !auth()->user()->inGroup('admin')) {
            return redirect()->to('forbidden')->with('error', lang('Pages.forbidden_error_msg'));
        }

        // Load form helper
        helper('form');

        // Required data for dashboard layout
        $data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Payment_Options') . ' | ' . lang('Pages.PayPal_Settings');
        $data['section'] = 'Payment_Options';
        $data['subsection'] = 'PayPal_Settings';
        $data['sideBarMenu'] = $this->sideBarMenu;
        $data['userData'] = $this->userAcctDetails;
        $data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
        $data['myConfig'] = $this->myConfig;

        // Get PayPal configuration status
        $data['paypalConfigStatus'] = $this->PayPalService->getConfigurationStatus();

        return view('App\Modules\PayPal\Views\settings', $data);
    }

    /**
     * Save PayPal settings
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

        // Validate required fields based on environment
        $environment = $this->request->getPost('PAYPAL_MODE');
        if ($environment === 'sandbox') {
            if (!$this->request->getPost('PAYPAL_SANDBOX_CLIENT_ID') || !$this->request->getPost('PAYPAL_SANDBOX_CLIENT_SECRET')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Sandbox credentials are required'
                ]);
            }
        } else {
            if (!$this->request->getPost('PAYPAL_LIVE_CLIENT_ID') || !$this->request->getPost('PAYPAL_LIVE_CLIENT_SECRET')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Production credentials are required'
                ]);
            }
        }

        try {
            // Save settings to database
            $settings = [
                'PAYPAL_MODE' => $this->request->getPost('PAYPAL_MODE'),
                'PAYPAL_SANDBOX_CLIENT_ID' => $this->request->getPost('PAYPAL_SANDBOX_CLIENT_ID'),
                'PAYPAL_SANDBOX_CLIENT_SECRET' => $this->request->getPost('PAYPAL_SANDBOX_CLIENT_SECRET'),
                'PAYPAL_SANDBOX_WEBHOOK_ID' => $this->request->getPost('PAYPAL_SANDBOX_WEBHOOK_ID'),
                'PAYPAL_LIVE_CLIENT_ID' => $this->request->getPost('PAYPAL_LIVE_CLIENT_ID'),
                'PAYPAL_LIVE_CLIENT_SECRET' => $this->request->getPost('PAYPAL_LIVE_CLIENT_SECRET'),
                'PAYPAL_LIVE_WEBHOOK_ID' => $this->request->getPost('PAYPAL_LIVE_WEBHOOK_ID')
            ];

            foreach ($settings as $key => $value) {
                $this->UserSettingsModel->setUserSetting($key, $value, 0);
            }

            // Clear cache to refresh settings
            clearCache();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'PayPal settings saved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', '[PayPal Admin SubscriptionController]  Save failed: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save PayPal settings: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test PayPal API connection
     */
    public function testConnection()
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
            $environment = $this->request->getPost('environment');
            $clientId = $this->request->getPost('client_id');
            $clientSecret = $this->request->getPost('client_secret');

            // Test connection using provided credentials
            $result = $this->PayPalService->testConnection($environment, $clientId, $clientSecret);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Successfully connected to PayPal API',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            log_message('error', '[PayPal Admin SubscriptionController]  Test connection failed: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to connect to PayPal API: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get PayPal menu for sidebar
     */
    public function getSideBarMenu()
    {
        $config = config('PayPal');
        return [
            'payment_methods' => [
                $config->adminMenu
            ]
        ];
    }
}
