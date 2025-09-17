<?php

namespace App\Modules\PayPal\Config;

use CodeIgniter\Config\BaseConfig;
use App\Modules\PayPal\Libraries\ConfigLoader;

class PayPal extends BaseConfig
{
    public $environment;
    public $baseUrl;
    public $clientId = '';
    public $clientSecret = '';
    public $webhookId = '';
    public $webhookUrl = '';
    public $returnUrl = '';
    public $cancelUrl = '';
    public $currency = 'USD';
    public $locale = 'en_US';
    public $isConfigured = false;

    public $adminMenu = [
        'category' => 'payment_method',
        'title' => 'PayPal',
        'logo' => MODULESPATH . 'PayPal/Views/assets/paypal_logo.svg',
        'url' => 'payment-options/paypal-settings',
        'config' => 'PayPal',
        'service_name' => 'PayPalService'
    ];

    protected $myConfig;

    public function __construct()
    {
        parent::__construct();

        $this->myConfig = ConfigLoader::getMyConfig('', 0);

        if (empty($this->myConfig)) {
            log_message('debug', '[PayPal Config] No configuration loaded from ConfigLoader::getMyConfig()');
            return;
        }

        $this->environment = $this->myConfig['PAYPAL_MODE'] ?? 'sandbox';
        log_message('debug', '[PayPal Config] Environment set to: ' . $this->environment);

        if ($this->environment === 'production') {
            $this->baseUrl = 'https://api-m.paypal.com';
            $this->clientId = $this->myConfig['PAYPAL_LIVE_CLIENT_ID'] ?? '';
            $this->clientSecret = $this->myConfig['PAYPAL_LIVE_CLIENT_SECRET'] ?? '';
            $this->webhookId = $this->myConfig['PAYPAL_LIVE_WEBHOOK_ID'] ?? '';
        } else {
            $this->baseUrl = 'https://api-m.sandbox.paypal.com';
            $this->clientId = $this->myConfig['PAYPAL_SANDBOX_CLIENT_ID'] ?? '';
            $this->clientSecret = $this->myConfig['PAYPAL_SANDBOX_CLIENT_SECRET'] ?? '';
            $this->webhookId = $this->myConfig['PAYPAL_SANDBOX_WEBHOOK_ID'] ?? '';
        }

        $this->isConfigured = $this->checkConfiguration();

        if ($this->isConfigured) {
            log_message('debug', '[PayPal Config] PayPal is configured for ' . $this->environment . ' environment');
        } else {
            log_message('debug', '[PayPal Config] PayPal is not fully configured for ' . $this->environment . ' environment');
        }

        $this->webhookUrl = base_url('paypal/webhook');
        $this->returnUrl = base_url('subscription/success');
        $this->cancelUrl = base_url('subscription/cancel');
    }

    /**
     * Check if PayPal is properly configured
     */
    public function checkConfiguration(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * Get webhook events to subscribe to
     */
    public function getWebhookEvents()
    {
        return [
            'BILLING.SUBSCRIPTION.CREATED',
            'BILLING.SUBSCRIPTION.ACTIVATED',
            'BILLING.SUBSCRIPTION.UPDATED',
            'BILLING.SUBSCRIPTION.CANCELLED',
            'BILLING.SUBSCRIPTION.SUSPENDED',
            'BILLING.SUBSCRIPTION.EXPIRED',
            'PAYMENT.SALE.COMPLETED',
            'PAYMENT.SALE.DENIED',
            'PAYMENT.SALE.REFUNDED',
            'PAYMENT.SALE.REVERSED'
        ];
    }

    /**
     * Get PayPal button style configuration
     */
    public function getButtonStyle()
    {
        return [
            'layout' => 'vertical',
            'color' => 'blue',
            'shape' => 'rect',
            'label' => 'subscribe',
            'tagline' => false
        ];
    }

    /**
     * Get subscription configuration defaults
     */
    public function getSubscriptionDefaults()
    {
        return [
            'application_context' => [
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'SUBSCRIBE_NOW',
                'return_url' => $this->returnUrl,
                'cancel_url' => $this->cancelUrl
            ],
            'plan' => [
                'payment_preferences' => [
                    'auto_bill_outstanding' => true,
                    'setup_fee_failure_action' => 'CONTINUE',
                    'payment_failure_threshold' => 3
                ],
                'taxes' => [
                    'percentage' => '0',
                    'inclusive' => false
                ]
            ]
        ];
    }
}
