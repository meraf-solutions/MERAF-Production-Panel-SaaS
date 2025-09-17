<?php

/**
 * Offline Payment Module Routes
 * 
 * This file contains all routes related to Offline payment integration,
 * including payment processing and admin management.
 */

$routes->group('payment-options', ['namespace' => '\App\Modules\Offline\Controllers'], function ($routes) {
    $routes->get('offline', 'Offline::index', ['as' => 'offline-payment']);
    $routes->post('offline/process-payment', 'Offline::processPayment', ['as' => 'offline-payment-process']);
    $routes->get('offline/payment/(:segment)', 'Offline::payment/$1', ['as' => 'offline-payment-form']);

    // Admin Routes (requires admin authentication)
    $routes->group('offline', [
        'namespace' => '\App\Modules\Offline\Controllers\Admin',
        'filter' => ['auth', 'group:admin']
    ], function ($routes) {
        $routes->get('admin', 'OfflineController::index', ['as' => 'manage-offline-payment']);
        
        // Offline Payment Settings
        $routes->post('admin/save', 'OfflineController::saveSettings', ['as' => 'offline-settings-save']);
        
        // Offline Payment Management
        $routes->post('payments/update-status', 'OfflineController::updatePaymentStatus', ['as' => 'offline-payment-update-status']);
    });
});
