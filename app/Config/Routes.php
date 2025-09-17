<?php

use CodeIgniter\Router\RouteCollection;
use App\Services\ModuleScanner;

/**
* @var RouteCollection $routes
*/

// Initialize ModuleScanner and load module routes first
$scanner = new ModuleScanner();
$scanner->loadRoutes($routes);

// log_message('info', '[Routes] Module routes loaded successfully');

/*****************
* Set the locale *
*****************/
$routes->get('setlocale/(:any)', 'Localization::setLocale/$1');

/*****************************
 * Set the timezone per user *
 ****************************/
$routes->post('set-timezone', 'Home::setTimezone');

/******************************
* Custom login with recaptcha *
******************************/
$reCAPTCHA_enabled = false; // Initialize reCAPTCHA enabled flag
try {
    if (!isset($myConfig)) { // Load the configuration
        $myConfig = getMyConfig('', 0);
    }
    if (isset($myConfig['reCAPTCHA_enabled']) && $myConfig['reCAPTCHA_enabled'] &&
        isset($myConfig['reCAPTCHA_Site_Key']) && $myConfig['reCAPTCHA_Site_Key'] &&
        isset($myConfig['reCAPTCHA_Secret_Key']) && $myConfig['reCAPTCHA_Secret_Key']) {
        $reCAPTCHA_enabled = true; // Check if reCAPTCHA is enabled and has the required keys
    }
} catch (Exception $e) {
    // If configuration loading fails (e.g., during installation), disable reCAPTCHA
    $reCAPTCHA_enabled = false;
}
// $routes->get('login', 'AuthController::login', ['filter' => 'guest']);

if ($reCAPTCHA_enabled) {
    $routes->post('login', 'AuthController::login', ['as' => 'custom-login']);
    $routes->post('register', 'AuthController::register', ['as' => 'custom-register']);
} else {
    $routes->post('register', 'RegisterController::registerAction');
}

// Add custom routes for registration
$routes->get('register', 'RegisterController::register');
$routes->post('register', 'RegisterController::registerAction');

/***************
 * Error Pages *
 **************/
$routes->get('/forbidden', 'ErrorController::forbidden');
$routes->get('/unavailable', 'ErrorController::unavailable');

/*********************
* API key management *
*********************/
$routes->post('auth/generate-api-key', 'AuthController::generateUserApiKey');
$routes->post('auth/revoke-api-key', 'AuthController::revokeUserApiKey');
$routes->post('auth/regenerate-api-key', 'AuthController::regenerateUserApiKey');

$routes->post('delete-my-account', 'Home::delete_user_account_action');

/*************
* Main Menus *
*************/
// Dashboard
$routes->get('/', 'Home::dashboard');
$routes->get("validate", 'Api::routineValidation');

// Product Changelog
$routes->get('product-changelog', 'Home::product_changelog');
$routes->get('product-changelog/(:any)?', 'Home::product_changelog/$1');

// Product Getting Started Guide
$routes->get('product-manager/gettings-started-guide', 'Home::product_guide');
$routes->get('product-manager/gettings-started-guide/(:any)?', 'Home::product_guide/$1');
$routes->post('product-manager/gettings-started-guide-update', 'Home::product_guide_update_action');

// Product Manager
$routes->get('product-manager', 'Home::product_manager');
$routes->get('product-manager/create-product', 'Home::product_manager/create_product');
$routes->get('product-manager/modify-product', 'Home::product_manager/modify_product');
$routes->get('product-manager/version-files', 'Home::product_manager/version_files');
$routes->get('product-manager/assign-product-variation', 'Home::product_manager/assign_product_variation');
$routes->get('product-manager/product-variations', 'Home::product_manager/product_variations');

// License Manager
$routes->get('license-manager/create-new-license', 'Home::create_license');
$routes->get('license-manager/list-all', 'Home::manage_licenses');
$routes->get('license-manager/resend-license', 'Home::resend_license');
$routes->get('license-manager/reset-license', 'Home::reset_license');
$routes->get('license-manager/activity-logs', 'Home::license_acitivty_logs');
$routes->get('license-manager/subscribers', 'Home::subscribers');
$routes->get('reset-own-license', 'Home::reset_license_public');

// Email Service
$routes->group("email-service", function ($routes) {
    $routes->group("template", function ($routes) {
        $routes->get('', 'Home::email_template_setup');
        $routes->post('upload-email-logo-action', 'Home::upload_email_logo_action');
        $routes->post('delete-email-logo-action', 'Home::delete_email_logo_action');
        $routes->post('upload-template-action', 'Home::upload_email_template_action');
        $routes->post('delete-template-action', 'Home::delete_email_templates_action');
        $routes->post('save-product-template-settings', 'Home::set_product_email_templates_action');
        $routes->get('fetch-template-list-only', 'Home::public_email_template_list');
        $routes->get('fetch-templates-config', 'Home::public_email_template_config');
    });

    $routes->group("notifications", function ($routes) {
        $routes->get('', 'Home::email_notifications_setup');
        $routes->post('save', 'Home::email_notifications_action');
    });

    $routes->group("settings", function ($routes) {
        $routes->get('', 'Home::email_service_settings');
        $routes->post('save', 'Home::email_settings_action');
        $routes->post('test', 'Home::testEmailSending');
    });
    
    $routes->group("logs", function ($routes) {
        $routes->get('', 'Home::email_logs_page');
        $routes->get('data', 'Home::email_logs_data', [
            'as' => 'user-email-logs-data'
        ]);
        $routes->get('view/(:num)', 'Home::view_email_log/$1', [
            'as' => 'user-email-logs-item'
        ]);
        $routes->post('resend/(:num)', 'Home::resend_email/$1', [
            'as' => 'user-email-logs-resend'
        ]);
        $routes->get('body/(:num)', 'Home::view_email_body/$1', [
            'as' => 'user-email-logs-body'
        ]);
    });
});

// Email templates utility
$routes->get('all-email-templates', 'Home::getEmailTemplateDetails');
$routes->get('email-template/(:any)', 'Home::getEmailTemplateDetails/$1');
$routes->get('send-email-new-license/(:segment)/(:segment)/(:segment)/(:segment)/(:segment)', 'LicenseManager::sendEmailtoClient_new_license/$1/$2/$3/$4/$5');

// Logs
$routes->get('error-logs', 'Home::log_page/error');
$routes->get('success-logs', 'Home::log_page/success');

// App Settings Page
$routes->get('app-settings', 'Home::app_settings');

/***********************************
 * Routes for requests and actions *
 **********************************/
// Product's json file retrieve
$routes->get('products/(:any)', 'Home::provide_product_json_only/$1');

// Log report handler
$routes->get('download-reports/(:any)', 'Home::downloadLogs/$1');
$routes->get('delete-reports/(:any)', 'Home::deleteLogs/$1');

// New license handler
$routes->post('license-manager/create-new-license/submit', 'LicenseManager::new_license_action');
$routes->post('license-manager/edit-license/submit', 'LicenseManager::edit_license_action');

// Resend license handler
$routes->post('resend-license/request', 'LicenseManager::resend_license_details_action');

// Reset license handler
$routes->post('reset-license/search', 'LicenseManager::reset_license_search_action');
$routes->post('reset-license/delete-selected', 'LicenseManager::reset_delete_selected_action');

// Product variation handler
$routes->post('manage-products/product-variations/save', 'Home::save_product_variation_list_action');
$routes->post('manage-products/product-variations/rename', 'Home::rename_product_variation_list_action');

// Product Changelog handler
$routes->post('product-changelog/update', 'Home::product_changelog_update_action');

// Download product's release package handler
$routes->get('download/(:segment)/(:segment)', 'Home::serve/$1/$2');

// Manage product handler
$routes->post('product-manager/new-product-action', 'Home::new_product_action');
$routes->post('product-manager/version-files-action', 'Home::version_files_action');
$routes->post('product-manager/delete-product-files-action', 'Home::delete_product_files_action');
$routes->post('product-manager/rename-product-action', 'Home::rename_product_action');
$routes->post('product-manager/delete-whole-product-action', 'Home::delete_whole_product_action');
$routes->post('product-manager/variations/save', 'Home::set_product_variations_action');

// User profile handler
$routes->post('user/change-password', 'ProfileController::change_password_action');
$routes->post('user/upload-avatar', 'ProfileController::upload_new_avatar_action');

// App Settings handler
$routes->post('app-settings/save', 'Home::app_settings_action');
$routes->get('app-settings/delete-cookies', 'Home::app_settings_delete_cookies');
$routes->get('app-settings/generate-new-key/(:segment)/(:segment)', 'Home::app_settings_generate_new_key/$1/$2');
$routes->get('app-settings/registration', 'Home::app_registration');
$routes->post('app-settings/registration/submit', 'Home::app_registration_action');
$routes->post('app-settings/registration/deactivate', 'Home::app_unregister_action');

/************************
 * Other utility routes *
 ***********************/
// Page logs utility
$routes->get('load-rows/(:any)', 'Home::loadRows/$1');

// Product manage utility
$routes->post('product-files/show', 'Home::list_product_files');

// Product variation utility
$routes->get('manage-products/product-variations/fetch-variation-list', 'Home::public_variations_only');

/******************
 * Updater routes *
 *****************/
$routes->get('update-production-panel', 'AppUpdater::index');
$routes->get('update-codeigniter', 'AppUpdater::update_codeigniter');
$routes->get('reinstall-production-panel', 'AppUpdater::index/reinstall');
$routes->get('reinstall-production-panel/force-update', 'AppUpdater::update_process/force_update');
$routes->get('process-app-updater', 'AppUpdater::update_process');
$routes->get('process-codeigniter-updater', 'AppUpdater::update_codeigniter_process');

/*************************************
 * For debugging & building purposes *
 ************************************/
$routes->get('debug', 'AdminController::debug', [
    'namespace' => '\App\Controllers\Admin',
    'filter' => ['auth', 'group:admin']
]);

$routes->get('create-backup', 'AdminController::backup_project_files', [
    'namespace' => '\App\Controllers\Admin',
    'filter' => ['auth', 'group:admin']
]);

$routes->get('build-release', 'AdminController::build_release_package', [
    'namespace' => '\App\Controllers\Admin',
    'filter' => ['auth', 'group:admin']
]);

/**************
 * API Routes *
 *************/
$routes->group("api", function ($routes) {
    // Tenant-Specific Operations (User API Key Authentication)
    $routes->get("dashboard-data", 'Api::getDashboardData');
    $routes->post("user/licenses", 'Api::createUserLicense');
    $routes->get("user/settings", 'Api::getUserSettings');
    $routes->post("user/settings", 'Api::updateUserSettings');

    // License Management API - Fixed from resource() to specific HTTP methods
    $routes->get("license/generate", 'Api::generateLicenseKeySLM');
    $routes->get("license/all/(:any)", 'Api::listLicenses/$1');
    $routes->get("license/export/(:any)", 'Api::exportLicensesCsv/$1');
    $routes->post("license/verify/(:any)/(:any)", 'Api::checkLicense/$1/$2');
    $routes->get("license/data/(:any)/(:any)/(:any)", 'Api::retrieveLicense/$1/$2/$3');
    $routes->get("license/config/(:any)", 'Api::retrieveNewLicenseSettings/$1');
    $routes->get("license/create/(:any)/(:any)", 'Api::createLicense/$1/$2');
    $routes->post("license/edit/(:any)", 'Api::editLicense/$1');

    // Domain/Device Registration - Fixed route parameter handling
    $routes->get("license/unregister/(:segment)/(:segment)/(:segment)/(:segment)", 'Api::unregisterDomainAndDevice/$1/$2/$3/$4');
    $routes->get("license/register/(:segment)/(:segment)/(:segment)/(:segment)", 'Api::registerDomainAndDevice/$1/$2/$3/$4');

    // License Management
    $routes->get("license/logs/(:segment)/(:segment)", 'Api::license_acitivty_logs/$1/$2');
    $routes->get("license/delete/(:segment)/(:any)", 'Api::delete_license_action/$1/$2/$3');
    $routes->get("license/subscribers/(:segment)", 'Api::subscribers/$1');

    // Product Information API
    $routes->get("product/all", 'Api::listProducts');
    $routes->get("product/with-variations", 'Api::listProductsWithVariations');
    $routes->get("product/current-versions", 'Api::listProductCurrentVersions');
    $routes->get("product/packages/(:segment)/(:segment)", 'Api::fetchProductFiles/$1/$2');
    $routes->get("product/changelog/(:segment)/(:segment)", 'Api::fetchProductChangelog/$1/$2');
    $routes->get("variation/all", 'Api::listVariations');

    // Super Admin Only
    $routes->get("user/all/(:any)", 'Api::listUsers/$1');
    $routes->get("package/all/(:any)", 'Api::listPackages/$1');
});

/*************
 * Cron jobs *
 ************/
$routes->group("cron/run", function ($routes) {
    $routes->resource("autoexpiry-license", ['controller' => 'Cronjob::do_auto_key_expiry']);
    $routes->resource("remind-expiring-license", ['controller' => 'Cronjob::do_expiry_reminder']);
    $routes->resource("check-abusive-ips", ['controller' => 'Cronjob::check_abusive_ips']);
    $routes->resource("clean-blocked-ips", ['controller' => 'Cronjob::clean_blocked_ips']);
});

/***************
 * Admin Setup *
 **************/
$routes->group("admin-options", [
    'namespace' => '\App\Controllers\Admin',
    'filter' => ['auth', 'group:admin']
], function ($routes) {
    // Global Settings
    $routes->resource("global-settings", ['controller' => 'AdminController::global_settings_page']);
    $routes->post("global-settings/save", 'AdminController::global_settings_action');
    $routes->post("global-settings/reset/(:segment)", 'AdminController::global_settings_reset_action/$1');
    $routes->post("global-settings/upload-notification-badge", 'AdminController::upload_notification_badge_action');
    $routes->post("global-settings/upload-private-key-file", 'AdminController::upload_private_key_file_action');
    $routes->post("global-settings/delete-private-key-file", 'AdminController::delete_private_key_file_action');
    $routes->post('global-settings/send-test-push-notification', 'AdminController::testPushNotification');
    $routes->post('global-settings/test-cache-connection', 'AdminController::testCacheConnection');
    
    // Email Settings
    $routes->resource("email-settings", ['controller' => 'AdminController::email_settings_page']);
    $routes->post("email-settings/save", 'AdminController::email_settings_action');
    $routes->post("email-settings/save-email-service", 'AdminController::updateEmailServiceSettings');
    
    // Cache and Logs
    $routes->resource("cronjob-logs", ['controller' => 'AdminController::cron_job_logs_page']);
    $routes->get("clear-server-cache", 'AdminController::clear_server_cache');

    // Blocked IPs
    $routes->group("blocked-ip-logs", function ($routes) {
        $routes->get('/', 'AdminController::blocked_ip_log');
        $routes->get('get', 'AdminController::blocked_ip_log_data');
        $routes->post('delete', 'AdminController::blocked_ip_action');
    });    
    
    // User Management for Admin
    $routes->group('user-manager', [
        'namespace' => '\App\Controllers\Admin',
        'filter' => ['auth', 'group:admin']
    ], function ($routes) {
        // List all subscriptions
        $routes->get('', 'AdminController::user_manager_page', [
            'as' => 'user-manager-list'
        ]);
    
        $routes->post('get-user-details', 'AdminController::get_user_details', [
            'as' => 'user-manager-get-details'
        ]);
        $routes->post('update-user-details', 'AdminController::update_user_details', [
            'as' => 'user-manager-update-details'
        ]);
        $routes->post('change-user-password', 'AdminController::change_user_password', [
            'as' => 'user-manager-change-user-pass'
        ]);
		$routes->post('get-user-group', 'AdminController::get_user_group', [
            'as' => 'user-manager-get-user-group'
        ]);
        $routes->post('set-user-group', 'AdminController::set_user_group', [
            'as' => 'user-manager-set-user-group'
        ]);
        $routes->post('generate-user-api-key', 'AdminController::generate_user_api_key', [
            'as' => 'user-manager-generate-user-api'
        ]);
        $routes->post('revoke-user-api-key', 'AdminController::revoke_user_api_key', [
            'as' => 'user-manager-revoke-user-api'
        ]);
        $routes->post('get-user-api-key', 'AdminController::get_user_api_key', [
            'as' => 'user-manager-get-user-api'
        ]);
        $routes->post('delete-user', 'AdminController::delete_user', [
            'as' => 'user-manager-delete-user'
        ]);
        $routes->post('update-user-subscription', 'AdminController::update_user_subscription', [
            'as' => 'user-manager-update-user-subscription'
        ]);
    });

    // Email Logs for Admin
    $routes->group('email-logs', [
        'namespace' => '\App\Controllers\Admin',
        'filter' => ['auth', 'group:admin']
    ], function ($routes) {
        $routes->get('', 'AdminController::email_logs_page', [
            'as' => 'email-logs-list'
        ]);
        $routes->get('data', 'AdminController::email_logs_data', [
            'as' => 'email-logs-data'
        ]);
        $routes->get('view/(:num)', 'AdminController::view_email_log/$1', [
            'as' => 'email-logs-item'
        ]);
        $routes->post('resend/(:num)', 'AdminController::resend_email/$1', [
            'as' => 'email-logs-resend'
        ]);
        $routes->get('body/(:num)', 'AdminController::view_email_body/$1', [
            'as' => 'email-logs-body'
        ]);
    });
    
    // Package Management
    $routes->group('package-manager', [
        'namespace' => '\App\Controllers\Admin',
        'filter' => ['auth', 'group:admin']
    ], function ($routes) {
        $routes->get("list-packages/data", 'AdminController::listPackages');
        $routes->resource("list-packages", ['controller' => 'AdminController::list_packages_page']);
        $routes->get("new", 'AdminController::manage_individual_package/new');
        $routes->get("edit/select-package", 'AdminController::manage_individual_package/select_package');
        $routes->resource("edit/(:num)/select-package", ['controller' => 'AdminController::manage_individual_package/edit/$1']);
        $routes->post("save-package", 'AdminController::save_package');
        $routes->post("delete/(:num)", 'AdminController::deletePackage/$1');
        $routes->get("modules", 'AdminController::packageModules');
        $routes->post("save-category", 'AdminController::saveCategory');
        $routes->post("update-category", 'AdminController::updateCategory');
        $routes->post("save-module", 'AdminController::saveModule');
        $routes->post("update-module", 'AdminController::updateModule');
        $routes->post("delete-module/(:num)", 'AdminController::deleteModule/$1');
    });

    // Language Editor
    $routes->group('language-editor', [
        'namespace' => '\App\Controllers\Admin',
        'filter' => ['auth', 'group:admin']
    ], function ($routes) {
        $routes->get("", 'AdminController::languageEditor');
        $routes->get("get-languages", 'AdminController::getLanguages');
        $routes->get("get-files", 'AdminController::getFiles');
        $routes->get("get-keys", 'AdminController::getKeys');
        $routes->post("add-language", 'AdminController::addLanguage');
        $routes->post("add-file", 'AdminController::addFile');
        $routes->post("add-key", 'AdminController::addKey');
        $routes->post("update-key", 'AdminController::updateKey');
        $routes->post("delete-key", 'AdminController::deleteKey');
    });
});

/***************************
 * Subscription Management *
 **************************/
$routes->group('subscription', [
    'filter' => ['auth']
], function ($routes) {
    $routes->get("packages", 'Subscription::packages_page', [
        'as' => 'subscription-packages'
    ]);
    $routes->get("my-subscription", 'Subscription::user_subscription_details_page', [
        'as' => 'subscription-current'
    ]);
    $routes->get("payment-history/(:any)", 'Subscription::user_payment_history_page/$1', [
        'as' => 'subscription-payment-history'
    ]);
    // Create new subscription
    $routes->post('create', 'Subscription::create', [
        'as' => 'subscription-create'
    ]);

    // Success and cancel URLs for redirect
    $routes->get('success', 'Subscription::success', [
        'as' => 'subscription-success'
    ]);
    $routes->get('cancel', 'Subscription::cancel', [
        'as' => 'subscription-cancel'
    ]);

    // Cancel active subscription
    $routes->post('cancel-subscription', 'Subscription::cancelSubscription', [
        'as' => 'subscription-cancel-active'
    ]);

    // Reactivate cancelled subscription
    $routes->get('reactivate/(:num)', 'Subscription::reactivate/$1', [
        'as' => 'subscription-reactivate'
    ]);

    // Get subscription details
    $routes->get('details/(:num)', 'Subscription::getDetails/$1', [
        'as' => 'subscription-details'
    ]);

    // Status pages
    $routes->get('thank-you', 'Subscription::thankYou', [
        'as' => 'subscription-thank-you'
    ]);
    $routes->get('error', 'Subscription::error', [
        'as' => 'subscription-error'
    ]);
    $routes->post('trial', 'Subscription::subscribe_for_trial', [
        'as' => 'subscription-trial'
    ]);
});

/****************************************
 * Admin Subscription Management Routes *
 ***************************************/ 
$routes->group('subscription-manager', [
    'namespace' => '\App\Controllers\Admin',
    'filter' => ['auth', 'group:admin']
], function ($routes) {
    // List all subscriptions
    $routes->get('list', 'SubscriptionController::subscription_list_page', [
        'as' => 'admin-subscription-list'
    ]);

    // View subscription details
    $routes->get('subscription/view/(:num)', 'SubscriptionController::subscription_view_page/$1', [
        'as' => 'admin-subscription-view'
    ]);

    // Manage subscription status
    $routes->post('subscription/suspend/(:num)', 'SubscriptionController::subscription_suspend/$1', [
        'as' => 'admin-subscription-suspend'
    ]);
    $routes->post('subscription/activate/(:num)', 'SubscriptionController::subscription_activate/$1', [
        'as' => 'admin-subscription-activate'
    ]);
    $routes->post('subscription/cancel/(:num)', 'SubscriptionController::subscription_cancel/$1', [
        'as' => 'admin-subscription-cancel'
    ]);

    // Payment history
    $routes->get('subscription/payments/(:num)', 'SubscriptionController::subscription_payments_page/$1', [
        'as' => 'admin-subscription-payments'
    ]);

    // Payment details
    $routes->get('subscription/payment-details/(:any)', 'SubscriptionController::subscription_payments_details/$1', [
        'as' => 'admin-subscription-payment-details'
    ]);

    // Reports
    $routes->get('reports', 'SubscriptionController::subscription_reports_page', [
        'as' => 'admin-subscription-reports'
    ]);
    $routes->post('reports/generate', 'SubscriptionController::subscription_generateReport', [
        'as' => 'admin-subscription-reports-generate'
    ]);
    $routes->get('reports/export', 'SubscriptionController::subscription_exportReport', [
        'as' => 'admin-subscription-reports-export'
    ]);
});

/***********************
 * Notification routes *
 **********************/
$routes->get('notification/getUnreadNotifications', 'NotificationController::getUnreadNotifications');
$routes->post('notification/markAsRead/(:num)', 'NotificationController::markAsRead/$1');
$routes->post('notification/markAllAsRead', 'NotificationController::markAllAsRead');
$routes->post('notification/test-push-notification', 'NotificationController::testPushNotification');

// Firebase push notifications
$routes->post('notification/registerToken', 'NotificationController::registerToken');
$routes->post('notification/deleteToken', 'NotificationController::deleteToken');
$routes->post('notification/test-notification', 'NotificationController::testNotification');
$routes->post('notification/checkDeviceRegistration', 'NotificationController::checkDeviceRegistration');
$routes->post('notification/current-device', 'NotificationController::saveToSessionDeviceId');

/***************************************
 * Codeigniter Shield (authentication) *
 **************************************/
service('auth')->routes($routes);
