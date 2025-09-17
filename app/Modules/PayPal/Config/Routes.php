<?php

/**
 * PayPal Module Routes
 * 
 * This file contains all routes related to PayPal integration,
 * including webhooks and subscription management.
 */

$routes->group('paypal', ['namespace' => '\App\Modules\PayPal\Controllers'], function ($routes) {
    // Webhook route (no authentication required)
    $routes->post('webhook', 'WebhookController::handle', [
        'as' => 'paypal-webhook',
        'filter' => 'paypalWebhook'
    ]);
});

// Admin Routes (requires admin authentication)
$routes->group('payment-options', [
    'namespace' => '\App\Modules\PayPal\Controllers\Admin',
    'filter' => ['auth', 'group:admin']
], function ($routes) {
    // PayPal Settings
    $routes->get('paypal-settings', 'SubscriptionController::settings', [
        'as' => 'paypal-settings'
    ]);
    $routes->post('paypal-settings/save', 'SubscriptionController::saveSettings', [
        'as' => 'paypal-settings-save'
    ]);
    $routes->post('paypal-settings/test-connection', 'SubscriptionController::testConnection', [
        'as' => 'paypal-settings-test-connection'
    ]);
});
