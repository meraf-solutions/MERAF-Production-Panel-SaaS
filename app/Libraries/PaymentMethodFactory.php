<?php

namespace App\Libraries;

use App\Services\ModuleScanner;

/**
 * Payment Method Factory
 *
 * Secure factory for creating payment method instances
 * Prevents arbitrary class loading and ensures only whitelisted payment methods
 *
 * Usage:
 * $factory = new PaymentMethodFactory();
 * $paymentService = $factory->create('PayPal');
 */
class PaymentMethodFactory
{
    // Whitelisted payment methods - only these can be instantiated
    private static $allowedMethods = [
        'PayPal' => [
            'service_name' => 'PayPalService',
            'namespace' => 'App\\Modules\\PayPal\\Libraries\\',
            'class' => 'PayPalService',
            'config_required' => true
        ],
        'Offline' => [
            'service_name' => 'OfflineService',
            'namespace' => 'App\\Modules\\Offline\\Libraries\\',
            'class' => 'OfflineService',
            'config_required' => false
        ],
        'Trial' => [
            'service_name' => 'TrialService',
            'namespace' => 'App\\Libraries\\',
            'class' => 'TrialService',
            'config_required' => false
        ]
    ];

    protected $moduleScanner;

    public function __construct()
    {
        $this->moduleScanner = new ModuleScanner();
    }

    /**
     * Create payment method instance
     *
     * @param string $methodName Payment method name
     * @return object|null Payment service instance or null if invalid
     * @throws \InvalidArgumentException If payment method is not allowed
     */
    public function create(string $methodName): ?object
    {
        // Validate payment method is whitelisted
        if (!$this->isAllowedMethod($methodName)) {
            log_message('warning', '[PaymentMethodFactory] Attempted to create unauthorized payment method: ' . $methodName);
            throw new \InvalidArgumentException("Payment method '{$methodName}' is not allowed");
        }

        $methodConfig = self::$allowedMethods[$methodName];

        try {
            // Check if configuration is required and validate
            if ($methodConfig['config_required'] && !$this->isMethodConfigured($methodName)) {
                log_message('warning', "[PaymentMethodFactory] Payment method '{$methodName}' is not properly configured");
                return null;
            }

            // Use ModuleScanner for secure instantiation
            $service = $this->moduleScanner->loadLibrary($methodName, $methodConfig['service_name']);

            if ($service === null) {
                log_message('error', "[PaymentMethodFactory] Failed to instantiate payment service: {$methodName}");
                return null;
            }

            // Validate the service implements required interface
            if (!$this->validatePaymentService($service)) {
                log_message('error', "[PaymentMethodFactory] Payment service '{$methodName}' does not implement required methods");
                return null;
            }

            log_message('info', "[PaymentMethodFactory] Successfully created payment service: {$methodName}");
            return $service;

        } catch (\Exception $e) {
            log_message('error', "[PaymentMethodFactory] Error creating payment method '{$methodName}': " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if payment method is in whitelist
     *
     * @param string $methodName
     * @return bool
     */
    public function isAllowedMethod(string $methodName): bool
    {
        return array_key_exists($methodName, self::$allowedMethods);
    }

    /**
     * Get all allowed payment methods
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return array_keys(self::$allowedMethods);
    }

    /**
     * Get payment method configuration
     *
     * @param string $methodName
     * @return array|null
     */
    public function getMethodConfig(string $methodName): ?array
    {
        return self::$allowedMethods[$methodName] ?? null;
    }

    /**
     * Check if payment method is properly configured
     *
     * @param string $methodName
     * @return bool
     */
    protected function isMethodConfigured(string $methodName): bool
    {
        switch ($methodName) {
            case 'PayPal':
                return $this->isPayPalConfigured();
            case 'Offline':
            case 'Trial':
                return true; // These don't require external configuration
            default:
                return false;
        }
    }

    /**
     * Check if PayPal is properly configured
     *
     * @return bool
     */
    protected function isPayPalConfigured(): bool
    {
        try {
            $config = config('PayPal');
            return $config && $config->checkConfiguration();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate that payment service implements required methods
     *
     * @param object $service
     * @return bool
     */
    protected function validatePaymentService(object $service): bool
    {
        $requiredMethods = [
            'newSubscription',
            'getSubscription',
            'cancelSubscription'
        ];

        foreach ($requiredMethods as $method) {
            if (!method_exists($service, $method)) {
                log_message('error', "[PaymentMethodFactory] Payment service missing required method: {$method}");
                return false;
            }
        }

        return true;
    }

    /**
     * Get available and configured payment methods
     *
     * @return array Array of method names that are both allowed and configured
     */
    public function getAvailableMethods(): array
    {
        $available = [];

        foreach (self::$allowedMethods as $methodName => $config) {
            if (!$config['config_required'] || $this->isMethodConfigured($methodName)) {
                $available[] = $methodName;
            }
        }

        return $available;
    }

    /**
     * Create multiple payment method instances
     *
     * @param array $methodNames
     * @return array Array of method_name => service_instance
     */
    public function createMultiple(array $methodNames): array
    {
        $services = [];

        foreach ($methodNames as $methodName) {
            try {
                $service = $this->create($methodName);
                if ($service !== null) {
                    $services[$methodName] = $service;
                }
            } catch (\Exception $e) {
                log_message('warning', "[PaymentMethodFactory] Skipping invalid method '{$methodName}': " . $e->getMessage());
            }
        }

        return $services;
    }

    /**
     * Get payment method display information
     *
     * @param string $methodName
     * @return array
     */
    public function getMethodDisplayInfo(string $methodName): array
    {
        $displayInfo = [
            'PayPal' => [
                'name' => 'PayPal',
                'description' => 'Pay with PayPal - Secure online payments',
                'icon' => 'fab fa-paypal',
                'type' => 'external'
            ],
            'Offline' => [
                'name' => 'Offline Payment',
                'description' => 'Manual payment processing (bank transfer, etc.)',
                'icon' => 'fas fa-university',
                'type' => 'manual'
            ],
            'Trial' => [
                'name' => 'Free Trial',
                'description' => 'Start with a free trial period',
                'icon' => 'fas fa-gift',
                'type' => 'trial'
            ]
        ];

        return $displayInfo[$methodName] ?? [
            'name' => $methodName,
            'description' => 'Unknown payment method',
            'icon' => 'fas fa-question',
            'type' => 'unknown'
        ];
    }

    /**
     * Register new payment method (for future extensibility)
     *
     * @param string $methodName
     * @param array $config
     * @return bool
     */
    public static function registerMethod(string $methodName, array $config): bool
    {
        $requiredKeys = ['service_name', 'namespace', 'class', 'config_required'];

        foreach ($requiredKeys as $key) {
            if (!isset($config[$key])) {
                log_message('error', "[PaymentMethodFactory] Missing required config key '{$key}' for method '{$methodName}'");
                return false;
            }
        }

        self::$allowedMethods[$methodName] = $config;
        log_message('info', "[PaymentMethodFactory] Registered new payment method: {$methodName}");
        return true;
    }

    /**
     * Get method statistics for admin dashboard
     *
     * @return array
     */
    public function getMethodStatistics(): array
    {
        $stats = [
            'total_methods' => count(self::$allowedMethods),
            'configured_methods' => count($this->getAvailableMethods()),
            'methods' => []
        ];

        foreach (self::$allowedMethods as $methodName => $config) {
            $stats['methods'][$methodName] = [
                'configured' => !$config['config_required'] || $this->isMethodConfigured($methodName),
                'config_required' => $config['config_required']
            ];
        }

        return $stats;
    }
}