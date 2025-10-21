<?php
/*
Plugin Name: MERAF Production Panel - Saas Version
Description: This plugin extends the functionality of MERAF Production Panel - Saas Version by integrating it with WooCommerce. Digital products can be purchased using WooCommerce and successful purchases will automatically create a license and send an email notification to the buyer.
Version: 1.3.0
Author: MERAF Digital Solutions
*/

// Development log: tail -f /home/meraf/web/dev.merafsolutions.com/public_html/wp-content/debug.log | grep "\[MERAF\]"

// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

// Check if WooCommerce is active
function meraf_production_panel_requirements() {
    error_log('[MERAF] meraf_production_panel_requirements: Checking plugin requirements');

    if (!class_exists('WooCommerce')) {
        error_log('[MERAF] meraf_production_panel_requirements: WooCommerce not found - plugin cannot function');
        add_action('admin_notices', 'meraf_production_panel_woocommerce_missing_notice');
        return;
    }

    error_log('[MERAF] meraf_production_panel_requirements: WooCommerce is active');
    add_action('admin_menu', 'meraf_production_panel_menu');

    $prodPanelURL = get_option('prodPanelSaasURL', '');
    $prodPanelCreationSecretKey = get_option('prodPanelSaasCreationSecretKey', '');
    $prodPanelManageSecretKey = get_option('prodPanelSaasManageSecretKey', '');
    $prodPanelGeneralSecretKey = get_option('prodPanelSaasGeneralSecretKey', '');
    $prodPanelUserAPIKey = get_option('prodPanelSaasUserAPIKey', '');

    error_log('[MERAF] meraf_production_panel_requirements: Configuration check');
    error_log('[MERAF] meraf_production_panel_requirements: - URL: ' . ($prodPanelURL ? 'SET' : 'NOT SET'));
    error_log('[MERAF] meraf_production_panel_requirements: - Creation Key: ' . ($prodPanelCreationSecretKey ? 'SET' : 'NOT SET'));
    error_log('[MERAF] meraf_production_panel_requirements: - Manage Key: ' . ($prodPanelManageSecretKey ? 'SET' : 'NOT SET'));
    error_log('[MERAF] meraf_production_panel_requirements: - General Key: ' . ($prodPanelGeneralSecretKey ? 'SET' : 'NOT SET'));
    error_log('[MERAF] meraf_production_panel_requirements: - User API Key: ' . ($prodPanelUserAPIKey ? 'SET' : 'NOT SET'));

    if ($prodPanelURL && $prodPanelCreationSecretKey && $prodPanelManageSecretKey && $prodPanelGeneralSecretKey && $prodPanelUserAPIKey) {
        error_log('[MERAF] meraf_production_panel_requirements: All settings configured - Registering woocommerce_order_status_completed hook');
        add_action('woocommerce_order_status_completed', 'meraf_production_panel_create_license', 10, 1);
    } else {
        error_log('[MERAF] meraf_production_panel_requirements: Hook NOT registered - missing required configuration');
    }
}
add_action('plugins_loaded', 'meraf_production_panel_requirements');

function meraf_production_panel_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php _e('MERAF Production Panel - Saas Version requires WooCommerce to be installed and activated.', 'meraf-production-panel'); ?></p>
    </div>
    <?php
}

function meraf_production_panel_menu() {
    add_submenu_page(
        'woocommerce',
        'MERAF Production Panel - Saas Version',
        'MERAF Production Panel - Saas Version',
        'manage_options',
        'meraf-production-panel-settings',
        'meraf_production_panel_settings'
    );
}

function meraf_production_panel_settings() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $prodPanelURL = get_option('prodPanelSaasURL', '');
    $prodPanelCreationSecretKey = get_option('prodPanelSaasCreationSecretKey', '');
    $prodPanelManageSecretKey = get_option('prodPanelSaasManageSecretKey', '');
    $prodPanelGeneralSecretKey = get_option('prodPanelSaasGeneralSecretKey', '');
    $prodPanelUserAPIKey = get_option('prodPanelSaasUserAPIKey', '');
    $error_message = '';
    $success_message = '';

    if (isset($_POST['meraf_production_panel_save'])) {
        $prodPanelURL = esc_url_raw($_POST['prodPanelURL']);
        $prodPanelCreationSecretKey = sanitize_text_field($_POST['prodPanelCreationSecretKey']);
        $prodPanelManageSecretKey = sanitize_text_field($_POST['prodPanelManageSecretKey']);
        $prodPanelGeneralSecretKey = sanitize_text_field($_POST['prodPanelGeneralSecretKey']);
        $prodPanelUserAPIKey = sanitize_text_field($_POST['prodPanelUserAPIKey']);

        // Normalize URL first (ensure trailing slash)
        if (!empty($prodPanelURL) && substr($prodPanelURL, -1) !== '/') {
            $prodPanelURL .= '/';
        }

        // Now validate all fields
        if (strpos($prodPanelURL, 'https://') !== 0) {
            $error_message = 'URL must start with "https://".';
        } elseif (empty($prodPanelCreationSecretKey)) {
            $error_message = 'Please enter your license creation secret key from the Production Panel.';
        } elseif (empty($prodPanelManageSecretKey)) {
            $error_message = 'Please enter your license manage secret key from the Production Panel.';
        } elseif (empty($prodPanelGeneralSecretKey)) {
            $error_message = 'Please enter your license validation secret key from the Production Panel.';
        } elseif (empty($prodPanelUserAPIKey)) {
            $error_message = 'Please enter your User API Key from the Production Panel.';
        } else {
            update_option('prodPanelSaasURL', $prodPanelURL);
            update_option('prodPanelSaasCreationSecretKey', $prodPanelCreationSecretKey);
            update_option('prodPanelSaasManageSecretKey', $prodPanelManageSecretKey);
            update_option('prodPanelSaasGeneralSecretKey', $prodPanelGeneralSecretKey);
            update_option('prodPanelSaasUserAPIKey', $prodPanelUserAPIKey);
            $success_message = 'Settings saved successfully!';
        }
    }

    $api_validation_result = null;
    $api_status_message = '';

    if ($prodPanelURL && filter_var($prodPanelURL, FILTER_VALIDATE_URL) && strpos($prodPanelURL, 'https://') === 0) {
        // Test basic connectivity
        $response = wp_remote_get($prodPanelURL, array(
            'timeout' => 10,
            'sslverify' => true,
            'headers' => array(
                'User-API-Key' => $prodPanelUserAPIKey
            )
        ));

        if (is_wp_error($response)) {
            $api_validation_result = 'error';
            $api_status_message = 'Connection failed: ' . $response->get_error_message();
        } else {
            $response_code = wp_remote_retrieve_response_code($response);

            if ($response_code === 200) {
                // Test API endpoint if we have the general secret key
                if ($prodPanelGeneralSecretKey) {
                    $api_test_url = $prodPanelURL . 'api/user/settings';
                    $api_response = wp_remote_get($api_test_url, array(
                        'timeout' => 10,
                        'headers' => array(
                            'User-API-Key' => $prodPanelUserAPIKey
                        )
                    ));

                    if (is_wp_error($api_response)) {
                        $api_validation_result = 'warning';
                        $api_status_message = 'URL reachable but API test failed: ' . $api_response->get_error_message();
                    } else {
                        $api_response_code = wp_remote_retrieve_response_code($api_response);
                        $api_body = wp_remote_retrieve_body($api_response);
                        $api_data = json_decode($api_body, true);

                        if ($api_response_code === 200 && is_array($api_data) && !isset($api_data['error'])) {
                            $api_validation_result = 'success';
                            $api_status_message = 'API connection successful';
                        } else {
                            $api_validation_result = 'warning';
                            $api_status_message = 'URL reachable but API returned error (check secret key)';
                        }
                    }
                } else {
                    $api_validation_result = 'partial';
                    $api_status_message = 'URL reachable (API test requires secret key)';
                }
            } else if ($response_code === 403) {
                // For 403, try to test a basic API endpoint to see if it's just the root path that's protected
                if ($prodPanelGeneralSecretKey) {
                    $api_test_url = $prodPanelURL . 'api/user/settings';
                    $api_response = wp_remote_get($api_test_url, array(
                        'timeout' => 10,
                        'headers' => array(
                            'User-API-Key' => $prodPanelUserAPIKey
                        )
                    ));

                    if (!is_wp_error($api_response)) {
                        $api_response_code = wp_remote_retrieve_response_code($api_response);
                        if ($api_response_code >= 200 && $api_response_code < 300) {
                            $api_validation_result = 'success';
                            $api_status_message = 'API connection successful (root path protected by security measures)';
                        } else {
                            $api_validation_result = 'warning';
                            $api_status_message = 'Root path protected (403) but API endpoint returned HTTP ' . $api_response_code;
                        }
                    } else {
                        $api_validation_result = 'partial';
                        $api_status_message = 'Root path protected by security measures (this is normal) - API test requires secret key';
                    }
                } else {
                    $api_validation_result = 'partial';
                    $api_status_message = 'Root path protected by security measures (this is normal) - enter secret key to test API';
                }
            } else if ($response_code >= 400 && $response_code < 500) {
                $api_validation_result = 'warning';
                $api_status_message = 'URL reachable but returned HTTP ' . $response_code . ' (client error)';
            } else if ($response_code >= 500) {
                $api_validation_result = 'error';
                $api_status_message = 'Server error (HTTP ' . $response_code . ') - check server status';
            } else {
                $api_validation_result = 'warning';
                $api_status_message = 'URL reachable but returned unexpected HTTP ' . $response_code;
            }
        }
    } else if ($prodPanelURL) {
        $api_validation_result = 'error';
        if (!filter_var($prodPanelURL, FILTER_VALIDATE_URL)) {
            $api_status_message = 'Invalid URL format';
        } else if (strpos($prodPanelURL, 'https://') !== 0) {
            $api_status_message = 'URL must use HTTPS';
        }
    }    

    // Settings form HTML
    ?>
    <style>
    .meraf-settings-container {
        max-width: 800px;
        margin: 20px 0;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .content {
        padding: 20px;
    }

    .meraf-settings-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        margin: 0;
    }

    .meraf-settings-header h1 {
        margin: 0;
        color: white;
        font-size: 24px;
        font-weight: 600;
        text-align: center;
    }

    .meraf-settings-form {
        padding: 20px;
        padding-top: 0;
    }

    .meraf-form-group {
        margin-bottom: 25px;
    }

    .meraf-form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #23282d;
        font-size: 14px;
    }

    .meraf-form-group input[type="text"] {
        width: 100%;
        max-width: 500px;
        padding: 12px 16px;
        border: 2px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .meraf-form-group input[type="text"]:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .meraf-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .meraf-validation-status {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 200px;
    }

    .meraf-status-icon {
        font-size: 20px;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .meraf-status-icon.success {
        color: #00a32a;
    }

    .meraf-status-icon.error {
        color: #d63638;
    }

    .meraf-status-icon.warning {
        color: #f56e28;
    }

    .meraf-status-icon.partial {
        color: #0073aa;
    }

    .meraf-status-message {
        font-size: 13px;
        font-weight: 500;
    }

    .meraf-status-message.success {
        color: #00a32a;
    }

    .meraf-status-message.error {
        color: #d63638;
    }

    .meraf-status-message.warning {
        color: #f56e28;
    }

    .meraf-status-message.partial {
        color: #0073aa;
    }

    .meraf-form-group .description {
        font-size: 13px;
        color: #666;
        margin-top: 6px;
        font-style: italic;
    }

    .meraf-submit-wrapper {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .meraf-submit-wrapper .button-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 6px;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 600;
        text-shadow: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .meraf-submit-wrapper .button-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .meraf-about-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 30px;
        border-left: 4px solid #667eea;
    }

    .meraf-about-section h2 {
        margin-top: 0;
        color: #23282d;
        font-size: 18px;
    }

    .meraf-about-section p {
        margin-bottom: 8px;
        color: #666;
    }

    @media (max-width: 768px) {
        .meraf-input-wrapper {
            flex-direction: column;
            align-items: flex-start;
        }

        .meraf-validation-status {
            min-width: auto;
        }
    }
    </style>
    <div class="wrap">
        <div class="meraf-settings-container">
            <div class="meraf-settings-header">
                <h1>MERAF Production Panel - Saas Version - General Settings</h1>
            </div>
        </div>

        <div class="meraf-settings-container content">
            
            <?php if ($success_message): ?>
                <div class="meraf-custom-notice meraf-notice-success" style="background: #d4edda; border: 1px solid #c3e6cb; border-left: 4px solid #00a32a; color: #155724; margin: 20px 0; padding: 12px 16px; border-radius: 4px; max-width: 800px;">
                    <p style="color: #155724; font-weight: 600; margin: 0;">✓ <?php echo esc_html($success_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="meraf-custom-notice meraf-notice-error" style="background: #f8d7da; border: 1px solid #f5c6cb; border-left: 4px solid #d63638; color: #721c24; margin: 20px 0; padding: 12px 16px; border-radius: 4px; max-width: 800px;">
                    <p style="color: #721c24; font-weight: 600; margin: 0;">⚠ <?php echo esc_html($error_message); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" class="meraf-settings-form">
                <div class="meraf-form-group">
                    <label for="prodPanelURL">Production Panel URL</label>
                    <div class="meraf-input-wrapper">
                        <input type="text"
                               id="prodPanelURL"
                               name="prodPanelURL"
                               value="<?php echo $prodPanelURL ? esc_attr($prodPanelURL) : 'https://'; ?>"
                               placeholder="https://your-production-panel.com/" />

                        <?php if ($prodPanelURL): ?>
                            <div class="meraf-validation-status">
                                <?php
                                $icon_class = '';
                                $icon = '';

                                switch($api_validation_result) {
                                    case 'success':
                                        $icon_class = 'success';
                                        $icon = 'dashicons-yes-alt';
                                        break;
                                    case 'partial':
                                        $icon_class = 'partial';
                                        $icon = 'dashicons-info-outline';
                                        break;
                                    case 'warning':
                                        $icon_class = 'warning';
                                        $icon = 'dashicons-warning';
                                        break;
                                    case 'error':
                                        $icon_class = 'error';
                                        $icon = 'dashicons-dismiss';
                                        break;
                                }

                                if ($icon): ?>
                                    <span class="meraf-status-icon <?php echo $icon_class; ?>">
                                        <span class="dashicons <?php echo $icon; ?>"></span>
                                    </span>
                                    <span class="meraf-status-message <?php echo $icon_class; ?>">
                                        <?php echo esc_html($api_status_message); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="description">Enter the full URL of your MERAF Production Panel - Saas Version (must use HTTPS)</p>
                </div>

                <div class="meraf-form-group">
                    <label for="prodPanelCreationSecretKey">License Creation Secret Key</label>
                    <input type="text"
                           id="prodPanelCreationSecretKey"
                           name="prodPanelCreationSecretKey"
                           value="<?php echo $prodPanelCreationSecretKey ? esc_attr($prodPanelCreationSecretKey) : ''; ?>"
                           placeholder="Enter your license creation secret key" />
                    <p class="description">Secret key used for creating new licenses via API</p>
                </div>

                <div class="meraf-form-group">
                    <label for="prodPanelManageSecretKey">License Manage Secret Key</label>
                    <input type="text"
                           id="prodPanelManageSecretKey"
                           name="prodPanelManageSecretKey"
                           value="<?php echo $prodPanelManageSecretKey ? esc_attr($prodPanelManageSecretKey) : ''; ?>"
                           placeholder="Enter your license manage secret key" />
                    <p class="description">Secret key used for updating/managing existing licenses via API (used for subscription renewals)</p>
                </div>

                <div class="meraf-form-group">
                    <label for="prodPanelGeneralSecretKey">License General Info Secret Key</label>
                    <input type="text"
                           id="prodPanelGeneralSecretKey"
                           name="prodPanelGeneralSecretKey"
                           value="<?php echo $prodPanelGeneralSecretKey ? esc_attr($prodPanelGeneralSecretKey) : ''; ?>"
                           placeholder="Enter your license validation secret key" />
                    <p class="description">Secret key used for license validation and retrieving license information</p>
                </div>

                <div class="meraf-form-group">
                    <label for="prodPanelUserAPIKey">User API Key</label>
                    <input type="text"
                           id="prodPanelUserAPIKey"
                           name="prodPanelUserAPIKey"
                           value="<?php echo $prodPanelUserAPIKey ? esc_attr($prodPanelUserAPIKey) : ''; ?>"
                           placeholder="Enter your User API key for SaaS multi-tenant authentication" />
                    <p class="description">User API key for SaaS version authentication (sent as User-API-Key header)</p>
                </div>

                <div class="meraf-submit-wrapper">
                    <?php submit_button('Save Settings', 'primary', 'meraf_production_panel_save'); ?>
                </div>
            </form>
        </div>
    </div>
    <?php
    // Include plugin data function
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');

    // Get plugin data
    $plugin_data = get_plugin_data(__FILE__);
    $plugin_name = $plugin_data['Name'];
    $plugin_version = $plugin_data['Version'];
    $plugin_description = $plugin_data['Description'];    
    $plugin_author = $plugin_data['Author'];   
    ?>
    <div class="meraf-about-section">
        <h2>About <?= esc_html($plugin_name) ?></h2>
        <p><?= esc_html(strip_tags(str_replace('By MERAF Digital Solutions.', '', $plugin_description))) ?></p>
        <p><strong>Version:</strong> <?= esc_html($plugin_version) ?></p>
        <p><strong>By:</strong> <?= esc_html(strip_tags($plugin_author)) ?></p>
        <p><strong>Plugin Status:</strong>
            <?php if ($prodPanelURL && $prodPanelCreationSecretKey && $prodPanelManageSecretKey && $prodPanelGeneralSecretKey && $prodPanelUserAPIKey): ?>
                <span style="color: #00a32a; font-weight: 600;">✓ Configured and Ready</span>
            <?php else: ?>
                <span style="color: #f56e28; font-weight: 600;">⚠ Configuration Required</span>
            <?php endif; ?>
        </p>
    </div>
    <?php
    
    /***
     * START DEBUG AREA
     */
    


    /***
     * END DEBUG AREA
     */     
}

/**
 * Create Order Action to Refresh the license details in the Admin Edit Order page
 */
// Add custom action to the order action dropdown
add_filter('woocommerce_order_actions', 'add_custom_order_action');
function add_custom_order_action($actions) {
    $actions['refresh_license_details'] = __('Refresh License Details', 'meraf');
    return $actions;
}

// Process the custom action when selected
add_action('woocommerce_order_action_refresh_license_details', 'process_refresh_license_details');
function process_refresh_license_details($order) {
    $order_id = $order->get_id();

    // Get the item IDs
    $item_ids = [];
    $list_items = get_item_id_of_order($order_id);
    foreach ($list_items as $item_id => $product) {
        $item_ids[] = $item_id;
    }

    // Process the license update for each item
    foreach ($item_ids as $item_id) {
        $updated = update_stored_license_details($order_id, $item_id);

        // Log or display any error if needed
        if (!$updated) {
            error_log('Failed to update license details for Item ID #' . $item_id);
        }
        
        
    }

    // Optionally add a note to the order
    // $order->add_order_note(__('License details refreshed for all items.', 'meraf'));
}

/**
 * Get each item id for a specific Order ID
 */
function get_item_id_of_order($query_order_id) {

    $order = wc_get_order($query_order_id);

    if (!$order) {
        error_log('Order #' . $query_order_id . ' not found');
        return ['error' => 'Order #' . $query_order_id . ' not found'];
    }

    $products = $order->get_items();

    $list_of_product = [];

    foreach ($products as $item_id => $product) {
        $list_of_product[$item_id] = query_product_name_for_license_creation($product);
        
        if (!$list_of_product) {
            error_log('The purchased product is not in the MERAF Production Panel - Saas Version products (' . $product->get_name() . ')');
            continue; // Continue to the next product if not found
        }

        $list_of_product = $list_of_product;
    }

    if(count($list_of_product) === 0) {
        error_log('Order #' . $query_order_id . ' not found');
        return ['error' => 'Order #' . $query_order_id . ' has no associated product with Production Panel'];
    }

    return $list_of_product;
}

/**
 * Call API to get list of products in MERAF Production Panel - Saas Version
 */
function get_list_product_prod_panel() {
    error_log('[MERAF] get_list_product_prod_panel: Starting product list retrieval');

    // Retrieve plugin options
    $prodPanelURL = get_option('prodPanelSaasURL', '');
    $prodPanelUserAPIKey = get_option('prodPanelSaasUserAPIKey', '');

    if($prodPanelURL) {
        $api_url = $prodPanelURL . 'api/product/with-variations';
        error_log('[MERAF] get_list_product_prod_panel: Calling API - ' . $api_url);

        $response = wp_remote_get($api_url, [
            'headers' => [
                'User-API-Key' => $prodPanelUserAPIKey
            ]
        ]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('[MERAF] get_list_product_prod_panel: API ERROR - ' . $error_message);
            return "Error retrieving product list from MERAF Production Panel - Saas Version -> " . $error_message;
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (is_array($data)) {
                error_log('[MERAF] get_list_product_prod_panel: SUCCESS - Retrieved ' . count($data) . ' products');
                error_log('[MERAF] get_list_product_prod_panel: Products: ' . implode(', ', $data));
            } else {
                error_log('[MERAF] get_list_product_prod_panel: WARNING - Invalid response format');
            }

            return $data;
        }
    } else {
        error_log('[MERAF] get_list_product_prod_panel: ERROR - No Production Panel URL configured');
    }
}

/**
 * Retrieve the stored license details by order number
 * 
 * @param int $query_order_id The order ID to retrieve license details for.
 * @param int|null $query_item_id The specific item ID within the order to retrieve (optional).
 * @return array Returns an array of license data for the order or specific item.
 */
function get_stored_license_details($query_order_id, $query_item_id = null) {
    // Retrieve the order object
    $order = wc_get_order($query_order_id);

    // Check if the order exists
    if (!$order) {
        error_log('Order #' . $query_order_id . ' not found');
        return ['error' => 'Order #' . $query_order_id . ' not found'];
    }

    // Retrieve all products (order items)
    $products = $order->get_items();

    // Initialize the array to store returned data
    $returned_data = [];
    $item_found = false;

    // Loop through each product (item) in the order
    foreach ($products as $item_id => $product) {
        // If a specific item ID is queried
        if ($query_item_id) {
            if ($item_id === $query_item_id) {
                // Construct the meta key for the license key
                $license_meta_key = '_license_key_' . $item_id;
                // Retrieve the meta data for the license key
                $data = wc_get_order_item_meta($item_id, $license_meta_key, true);

                // Check if license data exists for the queried item
                if ($data) {
                    $returned_data[$item_id] = $data;
                } else {
                    $returned_data['error'] = 'License key not found for item ID ' . $item_id;
                }
                $item_found = true;
                break; // Stop looping once the specific item is found
            }
        } 
        // If no specific item ID is queried, retrieve license keys for all items
        else {
            $license_meta_key = '_license_key_' . $item_id;
            $data = wc_get_order_item_meta($item_id, $license_meta_key, true);

            // Add the license key data to the returned data array
            if ($data) {
                $returned_data[$item_id] = $data;
            } else {
                $returned_data['error'] = 'License key not found for item ID ' . $item_id;
            }
        }
    }

    // If a specific item was queried and not found, return an error message
    if ($query_item_id && !$item_found) {
        $returned_data['error'] = 'Item ID ' . $query_item_id . ' not found in Order #' . $query_order_id;
    }

    return $returned_data;
}

/**
 * Update the stored license details by order number and item ID
 *
 * @param int $query_order_id The order ID to update license details for.
 * @param int $query_item_id The specific item ID within the order to update.
 * @param array $licenseDetails The json encoded license details if already available
 * @return bool Returns true if successful, false otherwise.
 */
function update_stored_license_details($query_order_id, $query_item_id, $licenseDetails = NULL) {
    error_log('[MERAF] update_stored_license_details: Called for Order ID ' . $query_order_id . ', Item ID ' . $query_item_id);

    // Set the API vars
    $prodPanelURL = get_option('prodPanelSaasURL', '');
    $generalSecretKey = get_option('prodPanelSaasGeneralSecretKey', '');
    $prodPanelUserAPIKey = get_option('prodPanelSaasUserAPIKey', '');

    // Set the meta key
    $license_meta_key = '_license_key_' . $query_item_id;
    error_log('[MERAF] update_stored_license_details: Meta key to update: ' . $license_meta_key);

    // Get the order details by order ID
    $order = wc_get_order($query_order_id);

    // If JSON encoded license details declared
    if($licenseDetails) {
        error_log('[MERAF] update_stored_license_details: License details provided directly - updating meta');
        // Save the new meta data
        wc_update_order_item_meta($query_item_id, $license_meta_key, $licenseDetails);
        error_log('[MERAF] update_stored_license_details: SUCCESS - Meta updated: ' . $license_meta_key);
        return true;
    }

    // Check if order and API vars are valid
    if ($prodPanelURL && $generalSecretKey && $order) {
        error_log('[MERAF] update_stored_license_details: Fetching license data from API');

        // Retrieve the specific item by item ID
        $items = $order->get_items();
        $item = isset($items[$query_item_id]) ? $items[$query_item_id] : null;

        if (!$item) {
            error_log('[MERAF] update_stored_license_details: ERROR - Item ID ' . $query_item_id . ' not found in Order #' . $query_order_id);
            return false;
        }

        // Get transaction ID or fallback to order ID
        $txnID = $order->get_transaction_id() ? $order->get_transaction_id() : $order->get_id();

        // Get product details
        $product = $item->get_product();
        $productName = $product ? $product->get_name() : '';

        error_log('[MERAF] update_stored_license_details: Transaction ID: ' . $txnID . ', Product: ' . $productName);

        // CASCADING FALLBACK STRATEGY:
        // The /api/license/data endpoint now searches by BOTH purchase_id_ OR txn_id
        // This makes it robust enough to find the license in most cases
        // But we still implement fallbacks for maximum reliability

        error_log('[MERAF] ========== ATTEMPTING LICENSE RETRIEVAL ==========');

        // ATTEMPT 1: Use the improved /api/license/data endpoint (searches by BOTH purchase_id_ OR txn_id)
        $api_url = $prodPanelURL . 'api/license/data/' . $generalSecretKey . '/' . $txnID . '/' . urlencode($productName);
        error_log('[MERAF] update_stored_license_details: ATTEMPT 1 - Calling API: ' . $api_url);

        $response = wp_remote_get($api_url, [
            'timeout' => 30,
            'headers' => [
                'User-API-Key' => $prodPanelUserAPIKey
            ]
        ]);
        $data = null;

        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $response_code = wp_remote_retrieve_response_code($response);
            error_log('[MERAF] update_stored_license_details: ATTEMPT 1 Response Code: ' . $response_code);

            $data = json_decode($body, true);

            if (!isset($data['error']) && isset($data['license_key'])) {
                error_log('[MERAF] update_stored_license_details: ATTEMPT 1 SUCCESS - License found: ' . $data['license_key']);
            } else {
                error_log('[MERAF] update_stored_license_details: ATTEMPT 1 FAILED - No valid license data');
                $data = null; // Clear for next attempt
            }
        } else {
            error_log('[MERAF] update_stored_license_details: ATTEMPT 1 ERROR - ' . $response->get_error_message());
        }

        // ATTEMPT 2: If first attempt failed, try with parent order ID if this is a subscription renewal
        if (!$data && function_exists('wcs_order_contains_renewal')) {
            if (wcs_order_contains_renewal($order)) {
                $subscriptions = wcs_get_subscriptions_for_renewal_order($order);
                if (!empty($subscriptions)) {
                    $subscription = reset($subscriptions);
                    $parent_order_id = $subscription->get_parent_id();

                    if ($parent_order_id) {
                        error_log('[MERAF] update_stored_license_details: ATTEMPT 2 - Trying with parent order ID: ' . $parent_order_id);

                        $api_url = $prodPanelURL . 'api/license/data/' . $generalSecretKey . '/' . $parent_order_id . '/' . urlencode($productName);
                        error_log('[MERAF] update_stored_license_details: ATTEMPT 2 - Calling API: ' . $api_url);

                        $response = wp_remote_get($api_url, [
                            'timeout' => 30,
                            'headers' => [
                                'User-API-Key' => $prodPanelUserAPIKey
                            ]
                        ]);

                        if (!is_wp_error($response)) {
                            $body = wp_remote_retrieve_body($response);
                            $data = json_decode($body, true);

                            if (!isset($data['error']) && isset($data['license_key'])) {
                                error_log('[MERAF] update_stored_license_details: ATTEMPT 2 SUCCESS - License found: ' . $data['license_key']);
                            } else {
                                error_log('[MERAF] update_stored_license_details: ATTEMPT 2 FAILED - No valid license data');
                                $data = null;
                            }
                        } else {
                            error_log('[MERAF] update_stored_license_details: ATTEMPT 2 ERROR - ' . $response->get_error_message());
                        }
                    }
                }
            }
        }

        // ATTEMPT 3: As final fallback, try to get license key from previous order meta and use data-by-key endpoint
        if (!$data) {
            error_log('[MERAF] update_stored_license_details: ATTEMPT 3 - Trying to retrieve from previous order meta');

            // Try to get the license key from the current order's meta
            $existing_license_data = wc_get_order_item_meta($query_item_id, $license_meta_key, true);

            if ($existing_license_data) {
                $existing_license = json_decode($existing_license_data, true);

                if (isset($existing_license['license_key'])) {
                    $stored_license_key = $existing_license['license_key'];
                    error_log('[MERAF] update_stored_license_details: ATTEMPT 3 - Found stored license key: ' . $stored_license_key);

                    $api_url = $prodPanelURL . 'api/license/data-by-key/' . $generalSecretKey . '/' . $stored_license_key;
                    error_log('[MERAF] update_stored_license_details: ATTEMPT 3 - Calling API: ' . $api_url);

                    $response = wp_remote_get($api_url, [
                        'timeout' => 30,
                        'headers' => [
                            'User-API-Key' => $prodPanelUserAPIKey
                        ]
                    ]);

                    if (!is_wp_error($response)) {
                        $body = wp_remote_retrieve_body($response);
                        $data = json_decode($body, true);

                        if (!isset($data['error']) && isset($data['license_key'])) {
                            error_log('[MERAF] update_stored_license_details: ATTEMPT 3 SUCCESS - License found: ' . $data['license_key']);
                        } else {
                            error_log('[MERAF] update_stored_license_details: ATTEMPT 3 FAILED - No valid license data');
                            $data = null;
                        }
                    } else {
                        error_log('[MERAF] update_stored_license_details: ATTEMPT 3 ERROR - ' . $response->get_error_message());
                    }
                }
            } else {
                error_log('[MERAF] update_stored_license_details: ATTEMPT 3 SKIPPED - No existing license key in meta');
            }
        }

        // Final check: If we successfully retrieved license data, save it
        if ($data && !isset($data['error']) && isset($data['license_key'])) {
            error_log('[MERAF] update_stored_license_details: ========== FINAL SUCCESS ==========');
            error_log('[MERAF] update_stored_license_details: License retrieved - Key: ' . $data['license_key']);

            // Save the new meta data
            $saveData = json_encode($data, JSON_FORCE_OBJECT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            wc_update_order_item_meta($query_item_id, $license_meta_key, $saveData);
            error_log('[MERAF] update_stored_license_details: SUCCESS - Meta updated with license key: ' . $data['license_key']);
            return true;
        } else {
            error_log('[MERAF] update_stored_license_details: ========== ALL ATTEMPTS FAILED ==========');
            error_log('[MERAF] update_stored_license_details: ERROR - Unable to retrieve license after all attempts');
            return false;
        }
    }

    error_log('[MERAF] update_stored_license_details: FAILED - Missing required data (URL/Key/Order)');
    return false;
}

/***
 * Get ordered product's attributes
 */     
function get_ordered_product_with_attributes($order_id) {
    
    $productList = [];
    
    $order = wc_get_order($order_id); // Replace '2554' with your actual order ID
    
    // Loop through each order item
    foreach ($order->get_items() as $item_id => $item) {
        // Get the product object
        $product = $item->get_product();
    
        // Check if the product exists and is a variation
        if ($product && $product->is_type('variation')) {
            // Get the parent product
            $parent_product = wc_get_product($product->get_parent_id());
            $product_basename = $parent_product ? $parent_product->get_name() : '';
            
            $productList[] = $product_basename;
    
            // Get variation attributes
            $variation_attributes = $product->get_variation_attributes();
    
            // Loop through each variation attribute
            foreach ($variation_attributes as $attribute_name => $attribute_value) {
                // Get attribute name in readable format
                $productList[$product_basename][str_replace('attribute_', '', $attribute_name)] = $attribute_value;
                // $attribute_label = wc_attribute_label(str_replace('attribute_', '', $attribute_name), $product);
            }
        } else {
            // If it's a simple product or parent product (not a variation)
            $productList[] = $product->get_name();
        }
    }
    
    return $productList;
}

/**
 * Evaluate the ordered item with MERAF Production Panel - Saas Version product list for correct license query
 */
function query_product_name_for_license_creation($order_item) {
    error_log('[MERAF] query_product_name_for_license_creation: Starting product matching process');

    if (!$order_item) {
        error_log('[MERAF] query_product_name_for_license_creation: ERROR - No order item provided');
        return null;
    }

    // Initialize variables
    $productToCreateLicense = null;
    $productVariations = [];
    $productType = null;
    $productBasename = '';

    // Get the actual WC_Product object from the order item
    $product = $order_item->get_product();

    if (!$product) {
        error_log('[MERAF] query_product_name_for_license_creation: ERROR - Could not get product object from order item');
        return null;
    }

    // Log detailed product information
    error_log('[MERAF] query_product_name_for_license_creation: Order Item Type: ' . $order_item->get_type());
    error_log('[MERAF] query_product_name_for_license_creation: Product ID: ' . $order_item->get_product_id());
    error_log('[MERAF] query_product_name_for_license_creation: Variation ID: ' . $order_item->get_variation_id());
    error_log('[MERAF] query_product_name_for_license_creation: Product Object Type: ' . get_class($product));

    // Check if the product is a variation by checking variation_id
    $variation_id = $order_item->get_variation_id();

    if ($variation_id > 0 && $product->is_type('variation')) {
        error_log('[MERAF] query_product_name_for_license_creation: Product is a VARIATION (ID: ' . $variation_id . ')');

        // Get the parent product name
        $parent_product = wc_get_product($product->get_parent_id());
        $productBasename = $parent_product ? $parent_product->get_name() : $product->get_name();

        // Get variation attributes from the product object
        $productVariations = $product->get_variation_attributes();
        error_log('[MERAF] query_product_name_for_license_creation: Variation attributes from product: ' . json_encode($productVariations));

        // Also try to get attributes from order item meta data as fallback
        $item_meta_data = $order_item->get_meta_data();
        if (!empty($item_meta_data)) {
            error_log('[MERAF] query_product_name_for_license_creation: Order item meta data available, checking for attributes...');
            foreach ($item_meta_data as $meta) {
                // Check for attribute keys (they usually start with 'pa_' or are custom attributes)
                if (stripos($meta->key, 'package') !== false ||
                    stripos($meta->key, 'version') !== false ||
                    stripos($meta->key, 'pa_') === 0) {
                    // Add to variations array
                    $productVariations[$meta->key] = $meta->value;
                    error_log('[MERAF] query_product_name_for_license_creation: Found attribute in meta: ' . $meta->key . ' = ' . $meta->value);
                }
            }
        }
    } else {
        error_log('[MERAF] query_product_name_for_license_creation: Product is a SIMPLE product');
        // If it's not a variation, get the product name directly
        $productBasename = $product->get_name();
    }

    error_log('[MERAF] query_product_name_for_license_creation: Product basename: ' . $productBasename);

    // If variations exist, loop through them
    if (!empty($productVariations)) {
        foreach ($productVariations as $attribute_name => $attribute_value) {
            // Get attribute name in readable format
            $attribute_label = wc_attribute_label(str_replace('attribute_', '', $attribute_name), $product);
            error_log('[MERAF] query_product_name_for_license_creation: Attribute - ' . $attribute_label . ': ' . $attribute_value);

            // Check for "version" or "package-type" or "package type" attributes
            if (stripos($attribute_label, 'version') !== false ||
                stripos($attribute_label, 'package-type') !== false ||
                stripos($attribute_label, 'package type') !== false ||
                stripos($attribute_name, 'package-type') !== false ||
                stripos($attribute_name, 'package_type') !== false) {
                $productType = $attribute_value;
                error_log('[MERAF] query_product_name_for_license_creation: Found PACKAGE/VERSION attribute: ' . $productType . ' (from attribute: ' . $attribute_label . ')');
            }
        }
    }

    // Reconstruct the product name query
    $productQuery = $productType ? $productBasename . ' ' . $productType : $productBasename;
    error_log('[MERAF] query_product_name_for_license_creation: Constructed product query: ' . $productQuery);

    // Get the list of products from the production panel
    $productionPanelProductList = get_list_product_prod_panel();

    if (!is_array($productionPanelProductList)) {
        error_log('[MERAF] query_product_name_for_license_creation: ERROR - Failed to retrieve production panel product list');
        return null;
    }

    // Loop through the production panel product list
    foreach ($productionPanelProductList as $product) {
        if (stripos($product, $productQuery) !== false) {
            // If productQuery is found in the product name, set the product to create license
            $productToCreateLicense = $product;
            error_log('[MERAF] query_product_name_for_license_creation: MATCH FOUND - ' . $productToCreateLicense);
            break; // Exit loop after finding the product
        }
    }

    if (!$productToCreateLicense) {
        error_log('[MERAF] query_product_name_for_license_creation: NO MATCH - Product "' . $productQuery . '" not found in production panel');
    }

    // Return the found product to create a license
    return $productToCreateLicense;

    // return null; // Return null if no product found
}

/**
 * Call API in MERAF Production Panel - Saas Version to create new license and add meta key for each product.
 */
function meraf_production_panel_create_license($order_id) {
    error_log('[MERAF] ============================================================');
    error_log('[MERAF] meraf_production_panel_create_license: HOOK TRIGGERED for Order ID: ' . $order_id);
    error_log('[MERAF] ============================================================');

    $prodPanelURL = get_option('prodPanelSaasURL', '');
    $prodPanelCreationSecretKey = get_option('prodPanelSaasCreationSecretKey', '');
    $prodPanelManageSecretKey = get_option('prodPanelSaasManageSecretKey', '');
    $prodPanelGeneralSecretKey = get_option('prodPanelSaasGeneralSecretKey', '');
    $prodPanelUserAPIKey = get_option('prodPanelSaasUserAPIKey', '');

    error_log('[MERAF] meraf_production_panel_create_license: Configuration retrieved');
    error_log('[MERAF] meraf_production_panel_create_license: - URL: ' . ($prodPanelURL ? $prodPanelURL : 'NOT SET'));
    error_log('[MERAF] meraf_production_panel_create_license: - Creation Key: ' . ($prodPanelCreationSecretKey ? 'SET (length: ' . strlen($prodPanelCreationSecretKey) . ')' : 'NOT SET'));
    error_log('[MERAF] meraf_production_panel_create_license: - Manage Key: ' . ($prodPanelManageSecretKey ? 'SET (length: ' . strlen($prodPanelManageSecretKey) . ')' : 'NOT SET'));
    error_log('[MERAF] meraf_production_panel_create_license: - General Key: ' . ($prodPanelGeneralSecretKey ? 'SET (length: ' . strlen($prodPanelGeneralSecretKey) . ')' : 'NOT SET'));
    error_log('[MERAF] meraf_production_panel_create_license: - User API Key: ' . ($prodPanelUserAPIKey ? 'SET (length: ' . strlen($prodPanelUserAPIKey) . ')' : 'NOT SET'));

    // Validate all required settings are configured
    if (empty($prodPanelURL)) {
        error_log('[MERAF] meraf_production_panel_create_license: ERROR - Production Panel URL not configured');
        return;
    }
    if (empty($prodPanelCreationSecretKey)) {
        error_log('[MERAF] meraf_production_panel_create_license: ERROR - Creation Secret Key not configured');
        return;
    }
    if (empty($prodPanelManageSecretKey)) {
        error_log('[MERAF] meraf_production_panel_create_license: ERROR - Manage Secret Key not configured - subscription renewals will fail');
        return;
    }
    if (empty($prodPanelGeneralSecretKey)) {
        error_log('[MERAF] meraf_production_panel_create_license: ERROR - General Secret Key not configured');
        return;
    }
    if (empty($prodPanelUserAPIKey)) {
        error_log('[MERAF] meraf_production_panel_create_license: ERROR - User API Key not configured');
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        error_log('[MERAF] meraf_production_panel_create_license: ERROR - Order ID ' . $order_id . ' not found');
        return; // Exit if order id not found
    }

    error_log('[MERAF] meraf_production_panel_create_license: Order found - Status: ' . $order->get_status());

    // Retrieve the settings for new license from MERAF Production Panel - Saas Version
    $api_url = $prodPanelURL . 'api/user/settings';
    error_log('[MERAF] meraf_production_panel_create_license: Fetching license config from API: ' . $api_url);

    $response = wp_remote_get($api_url, [
        'headers' => [
            'User-API-Key' => $prodPanelUserAPIKey
        ]
    ]);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log('[MERAF] meraf_production_panel_create_license: API ERROR - Failed to retrieve config: ' . $error_message);
        return "Error retrieving MERAF Production Panel - Saas Version config -> " . $error_message;
    } else {
        $body = wp_remote_retrieve_body($response);
        error_log('[MERAF] meraf_production_panel_create_license: Config API response: ' . $body);

        $response_data = json_decode($body, true);

        // Extract settings from nested structure
        if (isset($response_data['result']) && $response_data['result'] === 'success' && isset($response_data['settings'])) {
            $data = $response_data['settings'];
            error_log('[MERAF] meraf_production_panel_create_license: Successfully extracted settings from API response');
            error_log('[MERAF] meraf_production_panel_create_license: - default_license_status: ' . ($data['default_license_status'] ?? 'NOT SET'));
            error_log('[MERAF] meraf_production_panel_create_license: - defaultAllowedDomains: ' . ($data['defaultAllowedDomains'] ?? 'NOT SET'));
            error_log('[MERAF] meraf_production_panel_create_license: - defaultAllowedDevices: ' . ($data['defaultAllowedDevices'] ?? 'NOT SET'));
        } else {
            error_log('[MERAF] meraf_production_panel_create_license: ERROR - Invalid API response structure');
            return "Error: Invalid configuration response from MERAF Production Panel";
        }

        $firstName = $order->get_billing_first_name();
        $lastName = $order->get_billing_last_name();
        $emailAddress = $order->get_billing_email();
        $companyName = $order->get_billing_company();
        $txnID = $order->get_id(); // WooCommerce order #
        $purchaseID = $order->get_transaction_id() ? $order->get_transaction_id() : $txnID; // Payment gateway transaction reference
        $products = $order->get_items();

        error_log('[MERAF] meraf_production_panel_create_license: Order details extracted');
        error_log('[MERAF] meraf_production_panel_create_license: - Customer: ' . $firstName . ' ' . $lastName);
        error_log('[MERAF] meraf_production_panel_create_license: - Email: ' . $emailAddress);
        error_log('[MERAF] meraf_production_panel_create_license: - Company: ' . ($companyName ? $companyName : 'N/A'));
        error_log('[MERAF] meraf_production_panel_create_license: - Transaction ID: ' . $txnID);
        error_log('[MERAF] meraf_production_panel_create_license: - Purchase ID: ' . $purchaseID);
        error_log('[MERAF] meraf_production_panel_create_license: - Total products in order: ' . count($products));

        foreach ($products as $item_id => $product) {
            error_log('[MERAF] ------------------------------------------------------------');
            error_log('[MERAF] meraf_production_panel_create_license: Processing Item ID: ' . $item_id);
            error_log('[MERAF] meraf_production_panel_create_license: - Product name: ' . $product->get_name());
            error_log('[MERAF] meraf_production_panel_create_license: - Product ID: ' . $product->get_product_id());
            error_log('[MERAF] meraf_production_panel_create_license: - Variation ID: ' . $product->get_variation_id());
            error_log('[MERAF] meraf_production_panel_create_license: - Product Type: ' . $product->get_type());
            error_log('[MERAF] meraf_production_panel_create_license: - Quantity: ' . $product->get_quantity());
            error_log('[MERAF] meraf_production_panel_create_license: - Total: ' . $product->get_total());

            // Log all item meta data
            $item_meta = $product->get_meta_data();
            if (!empty($item_meta)) {
                error_log('[MERAF] meraf_production_panel_create_license: - Item Meta Data:');
                foreach ($item_meta as $meta) {
                    error_log('[MERAF]     * ' . $meta->key . ': ' . print_r($meta->value, true));
                }
            } else {
                error_log('[MERAF] meraf_production_panel_create_license: - Item Meta Data: NONE');
            }

            $license_meta_key = '_license_key_' . $item_id;
            $withLicense = wc_get_order_item_meta($item_id, $license_meta_key, true);

            if(!$withLicense) {
                error_log('[MERAF] meraf_production_panel_create_license: No existing license for item ID ' . $item_id . ' - proceeding with creation');
            } else {
                error_log('[MERAF] meraf_production_panel_create_license: License ALREADY EXISTS for item ID ' . $item_id . ' - skipping');
            }

            if(!$withLicense) { // Proceed with license creation if no license exists
                error_log('[MERAF] meraf_production_panel_create_license: Starting license creation for item ID ' . $item_id);

                $product_name_for_license_creation = query_product_name_for_license_creation($product);

                if (!$product_name_for_license_creation) {
                    error_log('[MERAF] meraf_production_panel_create_license: ERROR - Product not found in Production Panel: ' . $product->get_name());
                    continue; // Continue to the next product if not found
                }

                error_log('[MERAF] meraf_production_panel_create_license: Product matched in Production Panel: ' . $product_name_for_license_creation);

                $productName = $product->get_name();
                $productReference = $product_name_for_license_creation;
    
                // Identify the license type
                error_log('[MERAF] meraf_production_panel_create_license: Retrieving product attributes for license type determination');
                $productAttributes = get_ordered_product_with_attributes($order_id);
                error_log('[MERAF] meraf_production_panel_create_license: Product attributes: ' . json_encode($productAttributes));

                $licenseType = 'trial'; // Default
                $billingLength = '';
                $billingInterval = 'onetime';
                $dateExpiry = '';

                // Check if WooCommerce Subscriptions plugin is active and if this order contains subscriptions
                $is_subscription_order = false;
                $subscription_product = null;
                $subscription_period = null;
                $subscription_interval = null;
                $subscription_is_trial = false;
                $subscription_obj = null;
                $is_renewal_order = false;
                $parent_order_id = null;

                if (function_exists('wcs_order_contains_subscription')) {
                    $is_subscription_order = wcs_order_contains_subscription($order_id);
                    error_log('[MERAF] meraf_production_panel_create_license: WooCommerce Subscriptions detected - Order is subscription: ' . ($is_subscription_order ? 'YES' : 'NO'));

                    // Check if this is a renewal order
                    if (function_exists('wcs_order_contains_renewal')) {
                        $is_renewal_order = wcs_order_contains_renewal($order_id);
                        error_log('[MERAF] meraf_production_panel_create_license: Order is RENEWAL: ' . ($is_renewal_order ? 'YES' : 'NO'));

                        if ($is_renewal_order) {
                            // Get the parent subscription for this renewal
                            $subscriptions = wcs_get_subscriptions_for_renewal_order($order_id);
                            if (!empty($subscriptions)) {
                                $subscription_obj = reset($subscriptions);
                                error_log('[MERAF] meraf_production_panel_create_license: Renewal - Parent Subscription ID: ' . $subscription_obj->get_id());

                                // Get parent/original order ID
                                $parent_order_id = $subscription_obj->get_parent_id();
                                error_log('[MERAF] meraf_production_panel_create_license: Renewal - Parent Order ID: ' . $parent_order_id);
                            }
                        }
                    }

                    if ($is_subscription_order) {
                        // Get subscription details from the order
                        $subscriptions = wcs_get_subscriptions_for_order($order_id);

                        if (!empty($subscriptions)) {
                            $subscription_obj = reset($subscriptions); // Get first subscription
                            error_log('[MERAF] meraf_production_panel_create_license: Subscription found - ID: ' . $subscription_obj->get_id());

                            // Get the subscription product from this specific item
                            // For variations, use variation_id; otherwise use product_id
                            $product_id_to_load = $product->get_variation_id() > 0 ? $product->get_variation_id() : $product->get_product_id();
                            $subscription_product = wc_get_product($product_id_to_load);

                            error_log('[MERAF] meraf_production_panel_create_license: Loading subscription product ID: ' . $product_id_to_load);
                            error_log('[MERAF] meraf_production_panel_create_license: Subscription product type: ' . ($subscription_product ? get_class($subscription_product) : 'NULL'));

                            if ($subscription_product && is_a($subscription_product, 'WC_Product_Subscription')) {
                                $subscription_period = $subscription_product->get_period();
                                $subscription_interval = $subscription_product->get_period_interval();

                                error_log('[MERAF] meraf_production_panel_create_license: Subscription product details:');
                                error_log('[MERAF] meraf_production_panel_create_license: - Period: ' . $subscription_period);
                                error_log('[MERAF] meraf_production_panel_create_license: - Interval: ' . $subscription_interval);
                                error_log('[MERAF] meraf_production_panel_create_license: - Trial Period: ' . $subscription_product->get_trial_period());
                                error_log('[MERAF] meraf_production_panel_create_license: - Trial Length: ' . $subscription_product->get_trial_length());

                                // Check if subscription has a trial period
                                if ($subscription_product->get_trial_length() > 0) {
                                    $subscription_is_trial = true;
                                    error_log('[MERAF] meraf_production_panel_create_license: Subscription HAS TRIAL PERIOD');
                                }
                            } else if ($subscription_product && is_a($subscription_product, 'WC_Product_Subscription_Variation')) {
                                // Handle subscription variation - use method_exists checks
                                error_log('[MERAF] meraf_production_panel_create_license: Detected WC_Product_Subscription_Variation - checking available methods');

                                if (method_exists($subscription_product, 'get_period')) {
                                    $subscription_period = $subscription_product->get_period();
                                    error_log('[MERAF] meraf_production_panel_create_license: - Period (from method): ' . $subscription_period);
                                } else {
                                    error_log('[MERAF] meraf_production_panel_create_license: - get_period() method NOT available on variation, will use attributes');
                                    $subscription_period = null; // Will trigger fallback to attributes
                                }

                                if (method_exists($subscription_product, 'get_period_interval')) {
                                    $subscription_interval = $subscription_product->get_period_interval();
                                    error_log('[MERAF] meraf_production_panel_create_license: - Interval (from method): ' . $subscription_interval);
                                } else {
                                    error_log('[MERAF] meraf_production_panel_create_license: - get_period_interval() method NOT available on variation');
                                    $subscription_interval = 1;
                                }

                                error_log('[MERAF] meraf_production_panel_create_license: Subscription VARIATION product details:');

                                if (method_exists($subscription_product, 'get_trial_period')) {
                                    $trial_period_val = $subscription_product->get_trial_period();
                                    error_log('[MERAF] meraf_production_panel_create_license: - Trial Period: ' . $trial_period_val);
                                } else {
                                    error_log('[MERAF] meraf_production_panel_create_license: - Trial Period: N/A (method not available)');
                                }

                                if (method_exists($subscription_product, 'get_trial_length')) {
                                    $trial_length_val = $subscription_product->get_trial_length();
                                    error_log('[MERAF] meraf_production_panel_create_license: - Trial Length: ' . $trial_length_val);

                                    if ($trial_length_val > 0) {
                                        $subscription_is_trial = true;
                                        error_log('[MERAF] meraf_production_panel_create_license: Subscription variation HAS TRIAL PERIOD');
                                    }
                                } else {
                                    error_log('[MERAF] meraf_production_panel_create_license: - Trial Length: N/A (method not available)');
                                }
                            } else {
                                // Handle other product types (like WC_Product_Variable_Subscription)
                                error_log('[MERAF] meraf_production_panel_create_license: Subscription product is not a simple subscription or variation');
                                error_log('[MERAF] meraf_production_panel_create_license: Product class: ' . get_class($subscription_product));
                                error_log('[MERAF] meraf_production_panel_create_license: Will use subscription object dates or variation attributes');
                            }

                            // Get subscription dates - these are the ACTUAL expiry dates we should use
                            $subscription_end_date = $subscription_obj->get_date('end');
                            $subscription_next_payment = $subscription_obj->get_date('next_payment');
                            $subscription_trial_end = $subscription_obj->get_date('trial_end');

                            error_log('[MERAF] meraf_production_panel_create_license: Subscription dates from WC:');
                            error_log('[MERAF] meraf_production_panel_create_license: - End Date: ' . ($subscription_end_date ? $subscription_end_date : 'N/A (ongoing)'));
                            error_log('[MERAF] meraf_production_panel_create_license: - Next Payment: ' . ($subscription_next_payment ? $subscription_next_payment : 'N/A'));
                            error_log('[MERAF] meraf_production_panel_create_license: - Trial End: ' . ($subscription_trial_end ? $subscription_trial_end : 'N/A'));
                        }
                    }
                } else {
                    error_log('[MERAF] meraf_production_panel_create_license: WooCommerce Subscriptions plugin NOT detected');
                }

                // Determine license type based on WooCommerce Subscription status first, then fallback to attributes
                if ($is_subscription_order && $subscription_is_trial) {
                    // Free trial subscription
                    $licenseType = 'trial';

                    // Safely get trial length with method_exists check
                    if ($subscription_product && method_exists($subscription_product, 'get_trial_length')) {
                        $billingLength = $subscription_product->get_trial_length();
                    } else {
                        $billingLength = $data['defaultTrialDays'];
                    }

                    // Map subscription trial period to billing interval with method_exists check
                    if ($subscription_product && method_exists($subscription_product, 'get_trial_period')) {
                        $trial_period = $subscription_product->get_trial_period();
                    } else {
                        $trial_period = 'day';
                    }
                    $billingInterval = $trial_period === 'month' ? 'months' : ($trial_period === 'year' ? 'years' : 'days');

                    // Use actual trial end date from subscription if available
                    // Convert from WordPress timezone to UTC for consistent storage
                    if ($subscription_trial_end && !empty($subscription_trial_end)) {
                        $wp_timezone = new DateTimeZone(wp_timezone_string());
                        $utc_timezone = new DateTimeZone('UTC');
                        $date_obj = new DateTime($subscription_trial_end, $wp_timezone);
                        $date_obj->setTimezone($utc_timezone);
                        $dateExpiry = $date_obj->format('Y-m-d H:i:s');
                        error_log('[MERAF] meraf_production_panel_create_license: Using ACTUAL trial end date from WC Subscription (converted to UTC): ' . $dateExpiry);
                    } else {
                        $currentDate = new DateTime('now', new DateTimeZone('UTC'));
                        $currentDate->modify('+' . $billingLength . ' ' . $trial_period);
                        $dateExpiry = $currentDate->format('Y-m-d H:i:s');
                        error_log('[MERAF] meraf_production_panel_create_license: Calculated trial end date (UTC): ' . $dateExpiry);
                    }

                    error_log('[MERAF] meraf_production_panel_create_license: License type determined: FREE TRIAL (WC Subscription)');
                    error_log('[MERAF] meraf_production_panel_create_license: - Trial Length: ' . $billingLength . ' ' . $billingInterval);
                    error_log('[MERAF] meraf_production_panel_create_license: - Expiry date: ' . $dateExpiry);

                } else if ($is_subscription_order && $subscription_product) {
                    // Paid subscription
                    $licenseType = 'subscription';
                    $billingLength = $subscription_interval ? $subscription_interval : '1';

                    // FALLBACK: If subscription_period is empty, check variation attributes for "duration"
                    if (empty($subscription_period) && isset($productAttributes[$productName]) && is_array($productAttributes[$productName])) {
                        error_log('[MERAF] meraf_production_panel_create_license: Subscription period is EMPTY, checking variation attributes for duration');

                        foreach ($productAttributes[$productName] as $attribute => $value) {
                            if (stripos($attribute, 'duration') !== false || stripos($attribute, 'period') !== false) {
                                error_log('[MERAF] meraf_production_panel_create_license: Found duration attribute: "' . $attribute . '" = "' . $value . '"');

                                $value_lower = strtolower(trim($value));

                                if (stripos($value_lower, 'year') !== false || stripos($value_lower, 'annual') !== false) {
                                    $subscription_period = 'year';
                                    error_log('[MERAF] meraf_production_panel_create_license: Parsed duration as: YEAR');
                                } else if (stripos($value_lower, 'month') !== false) {
                                    $subscription_period = 'month';
                                    error_log('[MERAF] meraf_production_panel_create_license: Parsed duration as: MONTH');
                                } else if (stripos($value_lower, 'week') !== false) {
                                    $subscription_period = 'week';
                                    error_log('[MERAF] meraf_production_panel_create_license: Parsed duration as: WEEK');
                                } else if (stripos($value_lower, 'day') !== false) {
                                    $subscription_period = 'day';
                                    error_log('[MERAF] meraf_production_panel_create_license: Parsed duration as: DAY');
                                } else if (stripos($value_lower, 'quarter') !== false) {
                                    $subscription_period = 'month';
                                    $billingLength = '3';
                                    error_log('[MERAF] meraf_production_panel_create_license: Parsed duration as: QUARTERLY (3 months)');
                                }

                                if (!empty($subscription_period)) {
                                    error_log('[MERAF] meraf_production_panel_create_license: Successfully parsed billing period from variation attribute');
                                    break;
                                }
                            }
                        }
                    }

                    // Map subscription period to billing interval
                    switch ($subscription_period) {
                        case 'year':
                            $billingInterval = 'years';
                            $modify_string = '+' . $billingLength . ' year';
                            break;
                        case 'month':
                            $billingInterval = 'months';
                            $modify_string = '+' . $billingLength . ' month';
                            break;
                        case 'week':
                            $billingInterval = 'weeks';
                            $modify_string = '+' . $billingLength . ' week';
                            break;
                        case 'day':
                        default:
                            $billingInterval = 'days';
                            $modify_string = '+' . $billingLength . ' day';
                            break;
                    }

                    // Use actual next payment date from subscription if available
                    // This is the CORRECT expiry date for the current billing cycle
                    // Note: Convert from WordPress timezone to UTC for consistent storage
                    if ($subscription_next_payment && !empty($subscription_next_payment)) {
                        // WC Subscription dates are in site timezone, convert to UTC
                        $wp_timezone = new DateTimeZone(wp_timezone_string());
                        $utc_timezone = new DateTimeZone('UTC');
                        $date_obj = new DateTime($subscription_next_payment, $wp_timezone);
                        $date_obj->setTimezone($utc_timezone);
                        $dateExpiry = $date_obj->format('Y-m-d H:i:s');
                        error_log('[MERAF] meraf_production_panel_create_license: Using ACTUAL next payment date from WC Subscription (converted to UTC): ' . $dateExpiry);
                    } else if ($subscription_end_date && !empty($subscription_end_date)) {
                        // If no next payment (maybe a fixed-term subscription), use end date
                        $wp_timezone = new DateTimeZone(wp_timezone_string());
                        $utc_timezone = new DateTimeZone('UTC');
                        $date_obj = new DateTime($subscription_end_date, $wp_timezone);
                        $date_obj->setTimezone($utc_timezone);
                        $dateExpiry = $date_obj->format('Y-m-d H:i:s');
                        error_log('[MERAF] meraf_production_panel_create_license: Using subscription end date (converted to UTC): ' . $dateExpiry);
                    } else {
                        // Fallback to calculated date (already in UTC)
                        $currentDate = new DateTime('now', new DateTimeZone('UTC'));
                        $currentDate->modify($modify_string);
                        $dateExpiry = $currentDate->format('Y-m-d H:i:s');
                        error_log('[MERAF] meraf_production_panel_create_license: Calculated expiry date (fallback, UTC): ' . $dateExpiry);
                    }

                    // Determine billing source safely
                    $billing_source = 'Unknown';
                    if ($subscription_product) {
                        // Check if the method exists before calling it
                        if (method_exists($subscription_product, 'get_period') && !empty($subscription_product->get_period())) {
                            $billing_source = 'WC Subscription Product';
                        } else {
                            $billing_source = 'Variation Attribute (duration)';
                        }
                    }

                    error_log('[MERAF] meraf_production_panel_create_license: License type determined: SUBSCRIPTION (WC Subscription)');
                    error_log('[MERAF] meraf_production_panel_create_license: - Subscription Period: ' . (!empty($subscription_period) ? $subscription_period : 'EMPTY/NULL'));
                    error_log('[MERAF] meraf_production_panel_create_license: - Billing Interval: ' . $billingLength . ' ' . $billingInterval);
                    error_log('[MERAF] meraf_production_panel_create_license: - Billing Source: ' . $billing_source);
                    error_log('[MERAF] meraf_production_panel_create_license: - Expiry date: ' . $dateExpiry);

                } else if (isset($productAttributes[$productName]) && is_array($productAttributes[$productName])) {
                    // Check product attributes (for variation products without WC Subscriptions)
                    error_log('[MERAF] meraf_production_panel_create_license: Product HAS ATTRIBUTES (variation product) - checking attributes');

                    foreach ($productAttributes[$productName] as $attribute => $value) {
                        error_log('[MERAF] meraf_production_panel_create_license: Checking attribute "' . $attribute . '" = "' . $value . '"');

                        if (stripos($value, 'lifetime') !== false) {
                            $licenseType = 'lifetime';
                            $billingLength = '';
                            $billingInterval = 'onetime';
                            $dateExpiry = '';
                            error_log('[MERAF] meraf_production_panel_create_license: License type determined: LIFETIME (from attribute)');
                            break; // Stop checking once we find lifetime

                        } else if (stripos($value, 'monthly') !== false) {
                            $licenseType = 'subscription';
                            $billingLength = '1';
                            $billingInterval = 'months';
                            $currentDate = new DateTime();
                            $currentDate->modify('+1 month');
                            $dateExpiry = $currentDate->format('Y-m-d H:i:s');
                            error_log('[MERAF] meraf_production_panel_create_license: License type determined: SUBSCRIPTION - Monthly (from attribute)');
                            error_log('[MERAF] meraf_production_panel_create_license: - Expiry date: ' . $dateExpiry);
                            // Don't break - continue checking in case there's a lifetime attribute

                        } else if (stripos($value, 'annually') !== false || stripos($value, 'yearly') !== false) {
                            $licenseType = 'subscription';
                            $billingLength = '1';
                            $billingInterval = 'years';
                            $currentDate = new DateTime();
                            $currentDate->modify('+1 year');
                            $dateExpiry = $currentDate->format('Y-m-d H:i:s');
                            error_log('[MERAF] meraf_production_panel_create_license: License type determined: SUBSCRIPTION - Annually (from attribute)');
                            error_log('[MERAF] meraf_production_panel_create_license: - Expiry date: ' . $dateExpiry);
                            // Don't break - continue checking in case there's a lifetime attribute

                        } else if (stripos($value, 'trial') !== false || stripos($value, 'free') !== false) {
                            // Only set to trial if not already set to subscription or lifetime
                            if ($licenseType === 'trial') {
                                $licenseType = 'trial';
                                $billingLength = $data['defaultTrialDays'];
                                $billingInterval = 'days';
                                $currentDate = new DateTime();
                                $currentDate->modify('+' . $data['defaultTrialDays'] . ' day');
                                $dateExpiry = $currentDate->format('Y-m-d H:i:s');
                                error_log('[MERAF] meraf_production_panel_create_license: License type determined: TRIAL (from attribute)');
                                error_log('[MERAF] meraf_production_panel_create_license: - Trial Days: ' . $data['defaultTrialDays']);
                                error_log('[MERAF] meraf_production_panel_create_license: - Expiry date: ' . $dateExpiry);
                            }
                        }
                    }

                    // If still trial after checking all attributes, log it
                    if ($licenseType === 'trial') {
                        error_log('[MERAF] meraf_production_panel_create_license: No specific license type found in attributes - defaulting to TRIAL');
                    }

                } else {
                    // Simple product - check product name for keywords
                    error_log('[MERAF] meraf_production_panel_create_license: Product is SIMPLE (no attributes) - checking product name for license type keywords');

                    if (stripos($productName, 'lifetime') !== false) {
                        $licenseType = 'lifetime';
                        $billingLength = '';
                        $billingInterval = 'onetime';
                        $dateExpiry = '';
                        error_log('[MERAF] meraf_production_panel_create_license: License type determined from name: LIFETIME');

                    } else if (stripos($productName, 'annually') !== false || stripos($productName, 'yearly') !== false || stripos($productName, 'annual') !== false) {
                        $licenseType = 'subscription';
                        $billingLength = '1';
                        $billingInterval = 'years';
                        $currentDate = new DateTime();
                        $currentDate->modify('+1 year');
                        $dateExpiry = $currentDate->format('Y-m-d H:i:s');
                        error_log('[MERAF] meraf_production_panel_create_license: License type determined from name: SUBSCRIPTION (annually)');
                        error_log('[MERAF] meraf_production_panel_create_license: - Expiry date: ' . $dateExpiry);

                    } else if (stripos($productName, 'monthly') !== false || stripos($productName, 'subscription') !== false) {
                        $licenseType = 'subscription';
                        $billingLength = '1';
                        $billingInterval = 'months';
                        $currentDate = new DateTime();
                        $currentDate->modify('+1 month');
                        $dateExpiry = $currentDate->format('Y-m-d H:i:s');
                        error_log('[MERAF] meraf_production_panel_create_license: License type determined from name: SUBSCRIPTION (monthly)');
                        error_log('[MERAF] meraf_production_panel_create_license: - Expiry date: ' . $dateExpiry);

                    } else if (stripos($productName, 'trial') !== false || stripos($productName, 'free') !== false) {
                        $licenseType = 'trial';
                        $billingLength = $data['defaultTrialDays'];
                        $billingInterval = 'days';
                        $currentDate = new DateTime();
                        $currentDate->modify('+' . $data['defaultTrialDays'] . ' day');
                        $dateExpiry = $currentDate->format('Y-m-d H:i:s');
                        error_log('[MERAF] meraf_production_panel_create_license: License type determined from name: TRIAL');
                        error_log('[MERAF] meraf_production_panel_create_license: - Expiry date: ' . $dateExpiry);

                    } else {
                        // Default to trial
                        $licenseType = 'trial';
                        $billingLength = $data['defaultTrialDays'];
                        $billingInterval = 'days';
                        $currentDate = new DateTime();
                        $currentDate->modify('+' . $data['defaultTrialDays'] . ' day');
                        $dateExpiry = $currentDate->format('Y-m-d H:i:s');
                        error_log('[MERAF] meraf_production_panel_create_license: License type defaulted to: TRIAL (' . $data['defaultTrialDays'] . ' days)');
                        error_log('[MERAF] meraf_production_panel_create_license: - Expiry date: ' . $dateExpiry);
                    }
                }

                // Final summary log
                error_log('[MERAF] ========== LICENSE TYPE DETERMINATION COMPLETE ==========');
                error_log('[MERAF] meraf_production_panel_create_license: FINAL LICENSE TYPE: ' . strtoupper($licenseType));
                error_log('[MERAF] meraf_production_panel_create_license: - Billing Length: ' . ($billingLength ? $billingLength : 'N/A'));
                error_log('[MERAF] meraf_production_panel_create_license: - Billing Interval: ' . $billingInterval);
                error_log('[MERAF] meraf_production_panel_create_license: - Expiry Date: ' . ($dateExpiry ? $dateExpiry : 'Never'));
                error_log('[MERAF] ===========================================================');

                // Check if this is a renewal order - if so, update existing license instead of creating new one
                if ($is_renewal_order && $parent_order_id) {
                    error_log('[MERAF] ===========================================================');
                    error_log('[MERAF] meraf_production_panel_create_license: RENEWAL ORDER DETECTED');
                    error_log('[MERAF] meraf_production_panel_create_license: Looking for existing license from parent order ID: ' . $parent_order_id);
                    error_log('[MERAF] ===========================================================');

                    // Get the parent order
                    $parent_order = wc_get_order($parent_order_id);

                    if ($parent_order) {
                        // Try to find the license key from the parent order
                        $existing_license_key = null;

                        error_log('[MERAF] meraf_production_panel_create_license: Searching for matching product in parent order items...');
                        error_log('[MERAF] meraf_production_panel_create_license: Current renewal item details:');
                        error_log('[MERAF] meraf_production_panel_create_license: - Item ID: ' . $item_id);
                        error_log('[MERAF] meraf_production_panel_create_license: - Product ID: ' . $product->get_product_id());
                        error_log('[MERAF] meraf_production_panel_create_license: - Variation ID: ' . $product->get_variation_id());

                        // Get all items from parent order
                        foreach ($parent_order->get_items() as $parent_item_id => $parent_item) {
                            error_log('[MERAF] meraf_production_panel_create_license: --- Checking parent order item ID: ' . $parent_item_id);
                            error_log('[MERAF] meraf_production_panel_create_license: ... Parent Product ID: ' . $parent_item->get_product_id());
                            error_log('[MERAF] meraf_production_panel_create_license: ... Parent Variation ID: ' . $parent_item->get_variation_id());

                            // Compare variation IDs if available, otherwise compare product IDs
                            $current_product_id = $product->get_product_id();
                            $current_variation_id = $product->get_variation_id();
                            $parent_product_id = $parent_item->get_product_id();
                            $parent_variation_id = $parent_item->get_variation_id();

                            // Match logic: if both have variations, compare variations; otherwise compare product IDs
                            $is_match = false;
                            if ($current_variation_id > 0 && $parent_variation_id > 0) {
                                $is_match = ($current_variation_id === $parent_variation_id);
                                error_log('[MERAF] meraf_production_panel_create_license: ... Comparing variation IDs: ' . $current_variation_id . ' vs ' . $parent_variation_id . ' = ' . ($is_match ? 'MATCH' : 'NO MATCH'));
                            } else {
                                $is_match = ($current_product_id === $parent_product_id);
                                error_log('[MERAF] meraf_production_panel_create_license: ... Comparing product IDs: ' . $current_product_id . ' vs ' . $parent_product_id . ' = ' . ($is_match ? 'MATCH' : 'NO MATCH'));
                            }

                            if ($is_match) {
                                error_log('[MERAF] meraf_production_panel_create_license: ... PRODUCT MATCH FOUND! Looking for license meta...');

                                $parent_license_meta_key = '_license_key_' . $parent_item_id;
                                error_log('[MERAF] meraf_production_panel_create_license: ... Checking parent order meta key: ' . $parent_license_meta_key);

                                $parent_license_data = $parent_order->get_meta($parent_license_meta_key, true);
                                error_log('[MERAF] meraf_production_panel_create_license: ... Meta data found: ' . ($parent_license_data ? 'YES' : 'NO'));

                                if ($parent_license_data) {
                                    error_log('[MERAF] meraf_production_panel_create_license: ... Raw meta data (first 200 chars): ' . substr($parent_license_data, 0, 200));

                                    $parent_license_json = json_decode($parent_license_data, true);

                                    // Try multiple formats: full license data format OR API response format
                                    if (isset($parent_license_json['license_key'])) {
                                        $existing_license_key = $parent_license_json['license_key'];
                                        error_log('[MERAF] meraf_production_panel_create_license: ... SUCCESS - Found license_key in JSON: ' . $existing_license_key);
                                    } else if (isset($parent_license_json['key'])) {
                                        // Fallback to 'key' field (API response format)
                                        $existing_license_key = $parent_license_json['key'];
                                        error_log('[MERAF] meraf_production_panel_create_license: ... SUCCESS - Found key in JSON (API response format): ' . $existing_license_key);
                                    } else {
                                        error_log('[MERAF] meraf_production_panel_create_license: ... WARNING - License key not found in expected JSON fields');
                                        error_log('[MERAF] meraf_production_panel_create_license: ... JSON structure: ' . json_encode($parent_license_json));
                                        error_log('[MERAF] meraf_production_panel_create_license: ... Available keys: ' . implode(', ', array_keys($parent_license_json)));
                                    }

                                    if ($existing_license_key) {
                                        error_log('[MERAF] meraf_production_panel_create_license: FOUND EXISTING LICENSE KEY: ' . $existing_license_key);
                                        break;
                                    }
                                } else {
                                    error_log('[MERAF] meraf_production_panel_create_license: ... WARNING - No meta data found for key: ' . $parent_license_meta_key);
                                }
                            }
                        }

                        if ($existing_license_key) {
                            // Update existing license instead of creating new one
                            error_log('[MERAF] meraf_production_panel_create_license: Updating existing license via API: ' . $existing_license_key);

                            // ========== RETRIEVE EXISTING LICENSE DATA TO CALCULATE PROPER EXPIRY ==========
                            // We need to get the current expiry date and add the billing period to it
                            // This ensures renewals extend from the current expiry, not from renewal date
                            error_log('[MERAF] ========== RETRIEVING EXISTING LICENSE DATA ==========');
                            error_log('[MERAF] meraf_production_panel_create_license: Fetching existing license data from Production Panel API...');

                            // IMPORTANT: Use base product name (productName), NOT productReference which includes package type
                            // The API expects just "Sessner", not "Sessner Premium"
                            $product_for_api = wc_get_product($product->get_product_id());
                            $base_product_name = $product_for_api ? $product_for_api->get_name() : $productName;

                            $parent_txn_id = $parent_order->get_id(); // Parent order ID is stored as txn_id in license

                            error_log('[MERAF] meraf_production_panel_create_license: - Parent Order ID (txn_id): ' . $parent_txn_id);
                            error_log('[MERAF] meraf_production_panel_create_license: - Current Renewal Order ID: ' . $order->get_id());
                            error_log('[MERAF] meraf_production_panel_create_license: - Current Renewal Purchase ID: ' . $purchaseID);
                            error_log('[MERAF] meraf_production_panel_create_license: - Base Product Name (for API): ' . $base_product_name);
                            error_log('[MERAF] meraf_production_panel_create_license: - Product Reference (with package): ' . $productReference);

                            // CASCADING FALLBACK STRATEGY FOR RETRIEVING LICENSE DATA:
                            $retrieve_data = null;

                            // ATTEMPT 1: Use the improved /api/license/data endpoint (searches by BOTH purchase_id_ OR txn_id)
                            // Try with parent order ID first (most reliable for renewals)
                            $retrieve_api_url = $prodPanelURL . 'api/license/data/' . $prodPanelGeneralSecretKey . '/' . $parent_txn_id . '/' . urlencode($base_product_name);
                            error_log('[MERAF] meraf_production_panel_create_license: ATTEMPT 1 - Calling API: ' . $retrieve_api_url);

                            $retrieve_response = wp_remote_get($retrieve_api_url, [
                                'timeout' => 30,
                                'headers' => [
                                    'User-API-Key' => $prodPanelUserAPIKey
                                ]
                            ]);

                            if (!is_wp_error($retrieve_response)) {
                                $retrieve_body = wp_remote_retrieve_body($retrieve_response);
                                $retrieve_data = json_decode($retrieve_body, true);

                                if ($retrieve_data && isset($retrieve_data['date_expiry']) && !isset($retrieve_data['error'])) {
                                    error_log('[MERAF] meraf_production_panel_create_license: ATTEMPT 1 SUCCESS - License data retrieved');
                                } else {
                                    error_log('[MERAF] meraf_production_panel_create_license: ATTEMPT 1 FAILED - No valid license data');
                                    $retrieve_data = null;
                                }
                            } else {
                                error_log('[MERAF] meraf_production_panel_create_license: ATTEMPT 1 ERROR - ' . $retrieve_response->get_error_message());
                            }

                            // ATTEMPT 2: If we have the existing license key from parent order meta, use data-by-key endpoint
                            if (!$retrieve_data && $existing_license_key) {
                                error_log('[MERAF] meraf_production_panel_create_license: ATTEMPT 2 - Using license key directly: ' . $existing_license_key);

                                $retrieve_api_url = $prodPanelURL . 'api/license/data-by-key/' . $prodPanelGeneralSecretKey . '/' . $existing_license_key;
                                error_log('[MERAF] meraf_production_panel_create_license: ATTEMPT 2 - Calling API: ' . $retrieve_api_url);

                                $retrieve_response = wp_remote_get($retrieve_api_url, [
                                    'timeout' => 30,
                                    'headers' => [
                                        'User-API-Key' => $prodPanelUserAPIKey
                                    ]
                                ]);

                                if (!is_wp_error($retrieve_response)) {
                                    $retrieve_body = wp_remote_retrieve_body($retrieve_response);
                                    $retrieve_data = json_decode($retrieve_body, true);

                                    if ($retrieve_data && isset($retrieve_data['date_expiry']) && !isset($retrieve_data['error'])) {
                                        error_log('[MERAF] meraf_production_panel_create_license: ATTEMPT 2 SUCCESS - License data retrieved');
                                    } else {
                                        error_log('[MERAF] meraf_production_panel_create_license: ATTEMPT 2 FAILED - No valid license data');
                                        $retrieve_data = null;
                                    }
                                } else {
                                    error_log('[MERAF] meraf_production_panel_create_license: ATTEMPT 2 ERROR - ' . $retrieve_response->get_error_message());
                                }
                            }

                            $existing_expiry_date = null;
                            $calculated_new_expiry = null;

                            if (!$retrieve_data) {
                                error_log('[MERAF] meraf_production_panel_create_license: ALL ATTEMPTS FAILED - Could not retrieve existing license data');
                                error_log('[MERAF] meraf_production_panel_create_license: Will fallback to next_payment date calculation');
                            } else {
                                error_log('[MERAF] meraf_production_panel_create_license: ========== SUCCESSFULLY RETRIEVED LICENSE DATA ==========');

                                if ($retrieve_data && isset($retrieve_data['date_expiry'])) {
                                    $existing_expiry_date = $retrieve_data['date_expiry'];
                                    error_log('[MERAF] meraf_production_panel_create_license: SUCCESS - Retrieved existing expiry date: ' . $existing_expiry_date);

                                    // Calculate new expiry by adding billing period to existing expiry
                                    // This properly handles early/late renewals
                                    try {
                                        $expiry_date_obj = new DateTime($existing_expiry_date, new DateTimeZone('UTC'));
                                        error_log('[MERAF] meraf_production_panel_create_license: Existing expiry parsed: ' . $expiry_date_obj->format('Y-m-d H:i:s'));

                                        // Check if license is already expired
                                        $now_utc = new DateTime('now', new DateTimeZone('UTC'));
                                        $is_expired = $expiry_date_obj < $now_utc;

                                        if ($is_expired) {
                                            error_log('[MERAF] meraf_production_panel_create_license: WARNING - License is EXPIRED');
                                            error_log('[MERAF] meraf_production_panel_create_license: - Existing expiry: ' . $expiry_date_obj->format('Y-m-d H:i:s'));
                                            error_log('[MERAF] meraf_production_panel_create_license: - Current time: ' . $now_utc->format('Y-m-d H:i:s'));
                                            error_log('[MERAF] meraf_production_panel_create_license: - Will calculate from renewal date instead');

                                            // For expired licenses, use renewal date (now) + billing period
                                            $expiry_date_obj = clone $now_utc;
                                        } else {
                                            error_log('[MERAF] meraf_production_panel_create_license: License is still ACTIVE - will extend from current expiry');
                                        }

                                        // Add billing period to the appropriate base date
                                        $modify_string = '+' . $billingLength . ' ' . rtrim($billingInterval, 's');
                                        error_log('[MERAF] meraf_production_panel_create_license: Adding billing period: ' . $modify_string);

                                        $expiry_date_obj->modify($modify_string);
                                        $calculated_new_expiry = $expiry_date_obj->format('Y-m-d H:i:s');

                                        error_log('[MERAF] meraf_production_panel_create_license: ========== EXPIRY CALCULATION COMPLETE ==========');
                                        error_log('[MERAF] meraf_production_panel_create_license: - Original expiry: ' . $existing_expiry_date);
                                        error_log('[MERAF] meraf_production_panel_create_license: - Billing period: ' . $billingLength . ' ' . $billingInterval);
                                        error_log('[MERAF] meraf_production_panel_create_license: - CALCULATED new expiry: ' . $calculated_new_expiry);
                                        error_log('[MERAF] meraf_production_panel_create_license: ==================================================');

                                        // Use the calculated expiry
                                        $dateExpiry = $calculated_new_expiry;

                                    } catch (Exception $e) {
                                        error_log('[MERAF] meraf_production_panel_create_license: ERROR - Exception during expiry calculation: ' . $e->getMessage());
                                        error_log('[MERAF] meraf_production_panel_create_license: Will fallback to next_payment date calculation');
                                    }
                                } else {
                                    error_log('[MERAF] meraf_production_panel_create_license: WARNING - Could not find date_expiry in API response');
                                    if ($retrieve_data) {
                                        error_log('[MERAF] meraf_production_panel_create_license: Response keys: ' . implode(', ', array_keys($retrieve_data)));
                                    }
                                    error_log('[MERAF] meraf_production_panel_create_license: Will fallback to next_payment date calculation');
                                }
                            }

                            // If we couldn't calculate new expiry, log the fallback
                            if (!$calculated_new_expiry) {
                                error_log('[MERAF] meraf_production_panel_create_license: FALLBACK - Using next_payment date: ' . $dateExpiry);
                            }
                            error_log('[MERAF] ===========================================================');

                            // Calculate renewal timestamp in UTC
                            $renewal_timestamp = new DateTime('now', new DateTimeZone('UTC'));
                            $renewal_timestamp_string = $renewal_timestamp->format('Y-m-d H:i:s');

                            error_log('[MERAF] meraf_production_panel_create_license: RENEWAL - Setting date_renewed to: ' . $renewal_timestamp_string);

                            $update_params = [
                                'license_key' => $existing_license_key,
                                'license_status' => $data['default_license_status'] ?? 'active',
                                'license_type' => $licenseType,
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'email' => $emailAddress,
                                'subscr_id' => $parent_order_id, // Keep original parent order ID as subscr_id
                                'company_name' => $companyName,
                                'max_allowed_domains' => $data['defaultAllowedDomains'] ?? '1',
                                'max_allowed_devices' => $data['defaultAllowedDevices'] ?? '1',
                                'product_ref' => $productReference,
                                'txn_id' => $parent_order_id, // Keep original transaction ID
                                'purchase_id_' => $purchaseID,
                                'date_expiry' => $dateExpiry, // Update with new expiry date (calculated or fallback)
                                'date_renewed' => $renewal_timestamp_string, // Set renewal timestamp in UTC
                                'billing_length' => $billingLength,
                                'billing_interval' => $billingInterval,
                                'item_reference' => 'woocommerce',
                            ];

                            error_log('[MERAF] meraf_production_panel_create_license: UPDATE API parameters:');
                            error_log('[MERAF] meraf_production_panel_create_license: - license_key: ' . $existing_license_key);
                            error_log('[MERAF] meraf_production_panel_create_license: - NEW expiry date: ' . $dateExpiry);
                            error_log('[MERAF] meraf_production_panel_create_license: - date_renewed: ' . $renewal_timestamp_string);
                            error_log('[MERAF] meraf_production_panel_create_license: - subscr_id (parent order): ' . $parent_order_id);

                            // Use Manage Secret Key for updating existing licenses (for renewals)
                            $update_api_url = $prodPanelURL . 'api/license/edit/' . $prodPanelManageSecretKey;

                            error_log('[MERAF] meraf_production_panel_create_license: Calling license UPDATE API...');
                            error_log('[MERAF] meraf_production_panel_create_license: - Endpoint: ' . $update_api_url);

                            // Use POST request for update
                            $response = wp_remote_post($update_api_url, [
                                'body' => $update_params,
                                'timeout' => 30,
                                'headers' => [
                                    'User-API-Key' => $prodPanelUserAPIKey
                                ]
                            ]);

                            if (is_wp_error($response)) {
                                error_log('[MERAF] meraf_production_panel_create_license: API ERROR - License update failed: ' . $response->get_error_message());
                            } else {
                                $response_code = wp_remote_retrieve_response_code($response);
                                $response_body = wp_remote_retrieve_body($response);
                                error_log('[MERAF] meraf_production_panel_create_license: UPDATE API Response Code: ' . $response_code);
                                error_log('[MERAF] meraf_production_panel_create_license: UPDATE API Response Body: ' . $response_body);

                                $apiFeedback = json_decode($response_body, true);

                                if ($apiFeedback['result'] === 'success') {
                                    error_log('[MERAF] meraf_production_panel_create_license: SUCCESS - License UPDATED: ' . $existing_license_key);

                                    // Save the updated license data to current renewal order meta
                                    $order->update_meta_data($license_meta_key, $response_body);
                                    $order->save();

                                    error_log('[MERAF] meraf_production_panel_create_license: License data saved to renewal order ID ' . $order_id);
                                } else {
                                    error_log('[MERAF] meraf_production_panel_create_license: API FAILURE - License update failed');
                                    error_log('[MERAF] meraf_production_panel_create_license: Error: ' . ($apiFeedback['message'] ?? 'Unknown error'));
                                }
                            }

                            // Skip license creation since we updated existing one
                            error_log('[MERAF] meraf_production_panel_create_license: Skipping license creation - renewal handled via UPDATE');
                            error_log('[MERAF] meraf_production_panel_create_license: Finished processing renewal for item ID ' . $item_id);
                            continue; // Move to next item
                        } else {
                            error_log('[MERAF] meraf_production_panel_create_license: WARNING - No existing license found in parent order');
                            error_log('[MERAF] meraf_production_panel_create_license: Will create new license as fallback');
                        }
                    } else {
                        error_log('[MERAF] meraf_production_panel_create_license: ERROR - Parent order not found: ' . $parent_order_id);
                        error_log('[MERAF] meraf_production_panel_create_license: Will create new license as fallback');
                    }
                }

                // Construct the API URL for creating new license
                error_log('[MERAF] meraf_production_panel_create_license: Constructing license CREATION API request');

                $query_params = [
                    'license_status' => $data['default_license_status'] ?? 'active',
                    'license_type' => $licenseType,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $emailAddress,
                    'subscr_id' => $txnID,
                    'company_name' => $companyName,
                    'max_allowed_domains' => $data['defaultAllowedDomains'] ?? '1',
                    'max_allowed_devices' => $data['defaultAllowedDevices'] ?? '1',
                    'product_ref' => $productReference,
                    'txn_id' => $txnID,
                    'purchase_id_' => $purchaseID,
                    'date_expiry' => $dateExpiry,
                    'billing_length' => $billingLength,
                    'billing_interval' => $billingInterval,
                    'item_reference' => 'woocommerce',
                ];

                error_log('[MERAF] meraf_production_panel_create_license: API parameters:');
                error_log('[MERAF] meraf_production_panel_create_license: - license_status: ' . ($data['default_license_status'] ?? 'active'));
                error_log('[MERAF] meraf_production_panel_create_license: - license_type: ' . $licenseType);
                error_log('[MERAF] meraf_production_panel_create_license: - product_ref: ' . $productReference);
                error_log('[MERAF] meraf_production_panel_create_license: - max_allowed_domains: ' . ($data['defaultAllowedDomains'] ?? '1'));
                error_log('[MERAF] meraf_production_panel_create_license: - max_allowed_devices: ' . ($data['defaultAllowedDevices'] ?? '1'));
                error_log('[MERAF] meraf_production_panel_create_license: - billing_interval: ' . $billingInterval);
                error_log('[MERAF] meraf_production_panel_create_license: - billing_length: ' . $billingLength);

                $api_url = $prodPanelURL . 'api/license/create/' . $prodPanelCreationSecretKey . '/data?' . http_build_query($query_params);

                error_log('[MERAF] meraf_production_panel_create_license: Full API URL constructed (length: ' . strlen($api_url) . ')');
                error_log('[MERAF] meraf_production_panel_create_license: API endpoint: ' . $prodPanelURL . 'api/license/create/[SECRET]/data');

                // Call the API
                error_log('[MERAF] meraf_production_panel_create_license: Calling license creation API...');
                $response = wp_remote_get($api_url, [
                    'headers' => [
                        'User-API-Key' => $prodPanelUserAPIKey
                    ]
                ]);

                if (is_wp_error($response)) {
                    error_log('[MERAF] meraf_production_panel_create_license: API ERROR - License creation failed: ' . $response->get_error_message());
                } else {
                    $response_code = wp_remote_retrieve_response_code($response);
                    error_log('[MERAF] meraf_production_panel_create_license: API Response Code: ' . $response_code);
                    error_log('[MERAF] meraf_production_panel_create_license: API Response Body: ' . $response['body']);

                    // Add the generated license key to the product meta
                    $apiFeedback = json_decode($response['body'], true);

                    if ($apiFeedback['result'] === 'success') {
                        $key = $apiFeedback['key'];
                        error_log('[MERAF] meraf_production_panel_create_license: SUCCESS - License created: ' . $key);

                        // Generate a unique meta key for each item
                        $license_meta_value = $response['body'];

                        error_log('[MERAF] meraf_production_panel_create_license: Saving license to order meta...');
                        error_log('[MERAF] meraf_production_panel_create_license: - Meta key: ' . $license_meta_key);
                        error_log('[MERAF] meraf_production_panel_create_license: - Order ID: ' . $order_id);
                        error_log('[MERAF] meraf_production_panel_create_license: - Item ID: ' . $item_id);

                        // Save the new meta data
                        $order->update_meta_data($license_meta_key, $license_meta_value);
                        $order->save();

                        error_log('[MERAF] meraf_production_panel_create_license: Meta data SAVED successfully for item ID ' . $item_id);
                        error_log('[MERAF] meraf_production_panel_create_license: License key stored: ' . $key);
                    } else {
                        error_log('[MERAF] meraf_production_panel_create_license: API FAILURE - Result: ' . ($apiFeedback['result'] ?? 'unknown'));

                        // Extract error message - could be in 'error' or 'message' field
                        $error_message = 'No error message provided';
                        if (isset($apiFeedback['message'])) {
                            if (is_array($apiFeedback['message'])) {
                                $error_message = json_encode($apiFeedback['message']);
                            } else {
                                $error_message = $apiFeedback['message'];
                            }
                        } elseif (isset($apiFeedback['error'])) {
                            $error_message = $apiFeedback['error'];
                        }

                        error_log('[MERAF] meraf_production_panel_create_license: Error details: ' . $error_message);
                    }
                }

                error_log('[MERAF] meraf_production_panel_create_license: Finished processing item ID ' . $item_id);
            }
        }

        error_log('[MERAF] ============================================================');
        error_log('[MERAF] meraf_production_panel_create_license: COMPLETED for Order ID: ' . $order_id);
        error_log('[MERAF] ============================================================');
    }
}

/**
 * Add custom <li> as the last item in the <ul class="wc-item-meta"> in the order view page through the API.
 */
function show_license_key_view_order_page($item_id, $item, $order, $plain_text) {
    if ($order->get_status() !== 'completed') {
        return;
    }

    $prodPanelURL = get_option('prodPanelSaasURL', '');
    $order_id = $order->get_id();
    $data = get_stored_license_details($order_id, $item_id);
    
    if (!$data || isset($data['error'])) {
        if (update_stored_license_details($order_id, $item_id)) {
            $data = get_stored_license_details($order_id, $item_id);
        } else {
            error_log('Failed to update order meta.');
            return;
        }
    } 

    if ($data) {
        $licenseData = json_decode($data[$item_id], true);
        $licenseKey = $licenseData['license_key'];
        $licenseStatus = isset($licenseData['license_status']) ? sanitize_text_field($licenseData['license_status']) : null;
        $licenseType = isset($licenseData['license_type']) ? sanitize_text_field($licenseData['license_type']) : null;
        $licenseExpiry = isset($licenseData['date_expiry']) ? sanitize_text_field($licenseData['date_expiry']) : null;

        $button = 'info';
        switch ($licenseStatus) {
            case "active":
                $button = 'success';
                break;
            case "pending":
                $button = 'warning';
                break;
            case "blocked":
            case "expired":
                $button = 'error';
                break;
        }
    
        if (!$plain_text) {
            echo '<ul class="wc-item-meta" style="padding-top: 0;">
                    <li><p class="wc-item-meta-label">License Key:</p> <p>' . esc_html($licenseKey) . '</p></li>
                  </ul>';

            if ($licenseStatus === 'active') {
                echo '<ul class="wc-item-meta" style="padding-top: 0;">
                        <li><p class="wc-item-meta-label">Reset:</p> <p><a href="' . esc_url($prodPanelURL . 'reset-own-license?s=' . $licenseKey) . '" target="_blank">here</a></p></li>
                      </ul>';
            }            
    
            if ($licenseStatus) {
                echo '<ul class="wc-item-meta" style="padding-top: 0;">
                        <li><p class="wc-item-meta-label">License Status:</p> <p class="sku_wrapper alert_' . $button . '">' . esc_html($licenseStatus) . '</p></li>
                      </ul>';
            }

            if (($licenseType === 'trial' || $licenseType === 'subscription') && $licenseExpiry) {
                echo '<ul class="wc-item-meta" style="padding-top: 0;">
                        <li><p class="wc-item-meta-label">License Expiration:</p> <p>' . esc_html($licenseExpiry) . ' (UTC) (<a href="https://www.timeanddate.com/worldclock/converter.html?iso='.date('Ymd\THis', strtotime($licenseExpiry)).'&p1=1440" target="_blank">date converter</a>)</p></li>
                      </ul>';
            }
        } else {
            echo 'License Key: ' . esc_html($licenseKey) . "\n";

            if ($licenseStatus === 'active') {
                echo 'Reset: ' . esc_url($prodPanelURL . 'reset-own-license?s=' . $licenseKey) . "\n";
            }            
    
            if ($licenseStatus) {
                echo 'License Status: ' . esc_html($licenseStatus) . "\n";
            }

            if (($licenseType === 'trial' || $licenseType === 'subscription') && $licenseExpiry) {
                echo 'License Expiration: ' . esc_html($licenseExpiry) . ' (UTC)' . "\n";
                echo esc_html('(<a href="https://www.timeanddate.com/worldclock/converter.html?iso='.date('Ymd\THis', strtotime($licenseExpiry)).'&p1=1440" target="_blank">date converter</a>)');
            }
        } 
    }   
}
add_action('woocommerce_order_item_meta_end', 'show_license_key_view_order_page', 10, 4);

/**
 * Automatically set the order "completed" whenever the payment is successful
 */
function auto_complete_order($order_id) {
    error_log('[MERAF] auto_complete_order: Hook triggered for Order ID ' . $order_id);

    if (!$order_id) {
        error_log('[MERAF] auto_complete_order: ERROR - No order ID provided');
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        error_log('[MERAF] auto_complete_order: ERROR - Order not found for ID ' . $order_id);
        return;
    }

    $current_status = $order->get_status();
    error_log('[MERAF] auto_complete_order: Current order status: ' . $current_status);

    if ($order && 'completed' !== $current_status && $order->has_status('processing')) {
        error_log('[MERAF] auto_complete_order: Changing status from "processing" to "completed"');
        $order->update_status('completed');
        error_log('[MERAF] auto_complete_order: Order status updated to "completed" - this will trigger license creation hook');
    } else {
        error_log('[MERAF] auto_complete_order: No status change needed (current: ' . $current_status . ')');
    }
}
add_action('woocommerce_payment_complete', 'auto_complete_order');

/***
 * Remove quantity field on the product page
 */
function custom_remove_quantity_field($return, $product) {
    return true;
}
add_filter('woocommerce_is_sold_individually', 'custom_remove_quantity_field', 10, 2);

/***
 * Remove quantity field on the view-order page
 */
function remove_order_item_quantity_html($quantity_html, $item) {
    return is_wc_endpoint_url('view-order') ? '' : $quantity_html;
}
add_filter('woocommerce_order_item_quantity_html', 'remove_order_item_quantity_html', 10, 2);

/***
 * Remove quantity field on the checkout page
 */
function remove_product_quantity_checkout($quantity, $cart_item, $cart_item_key) {
    return '';
}
add_filter('woocommerce_checkout_cart_item_quantity', 'remove_product_quantity_checkout', 10, 3);

/***
 * Automatically set quantity to one for each product added to the cart
 */
function custom_set_cart_quantity_to_one($quantity, $product_id) {
    return 1;
}
add_filter('woocommerce_add_to_cart_quantity', 'custom_set_cart_quantity_to_one', 10, 2);

/***
 * Remove the "Downloads" and "Payment Methods" menu items from the "My Account" menu
 */
function custom_remove_my_account_menu_items($items) {
    unset($items['downloads'], $items['payment-methods']);
    return $items;
}
add_filter('woocommerce_account_menu_items', 'custom_remove_my_account_menu_items', 999);

/**
 * API Key Validation
 */
function meraf_validate_api_key() {
    error_log('[MERAF] meraf_validate_api_key: Validating API key for incoming request');

    $headers = getallheaders();
    $headers = array_change_key_case($headers, CASE_UPPER);

    $expected_key = get_option('prodPanelSaasGeneralSecretKey', '');
    $provided_key = isset($headers['X-API-KEY']) ? $headers['X-API-KEY'] : '';

    error_log('[MERAF] meraf_validate_api_key: Expected key length: ' . strlen($expected_key));
    error_log('[MERAF] meraf_validate_api_key: Provided key length: ' . strlen($provided_key));

    if (isset($headers['X-API-KEY']) && $headers['X-API-KEY'] === $expected_key) {
        error_log('[MERAF] meraf_validate_api_key: API key validation SUCCESSFUL');
        return true;
    }

    error_log('[MERAF] meraf_validate_api_key: API key validation FAILED - Invalid or missing key');
    return new WP_Error('invalid_api_key', 'Invalid API Key', array('status' => 403));
}

/**
 * Update order meta via REST API
 */
function meraf_update_order_meta(WP_REST_Request $request) {
    error_log('[MERAF] ============================================================');
    error_log('[MERAF] meraf_update_order_meta: REST API endpoint called');
    error_log('[MERAF] ============================================================');

    $body = $request->get_body();
    error_log('[MERAF] meraf_update_order_meta: Request body: ' . $body);

    $decoded_body = json_decode($body, true);

    $order_id = isset($decoded_body['txn_id']) ? $decoded_body['txn_id'] : null;
    $license_key = isset($decoded_body['license_key']) ? $decoded_body['license_key'] : null;
    $license_data = $body !== '' ? $body : null;

    error_log('[MERAF] meraf_update_order_meta: Extracted data:');
    error_log('[MERAF] meraf_update_order_meta: - Order ID: ' . ($order_id ? $order_id : 'NULL'));
    error_log('[MERAF] meraf_update_order_meta: - License Key: ' . ($license_key ? $license_key : 'NULL'));

    if (!$order_id || !$license_key) {
        error_log('[MERAF] meraf_update_order_meta: ERROR - Missing required data (Order ID or License Key)');
        return new WP_Error('missing_data', 'Order ID and License Key are required.', array('status' => 400));
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        error_log('[MERAF] meraf_update_order_meta: ERROR - Order not found for ID: ' . $order_id);
        return new WP_Error('order_not_found', 'Order not found for ID: ' . $order_id, array('status' => 404));
    }

    error_log('[MERAF] meraf_update_order_meta: Order found - searching for matching item');

    $products = $order->get_items();
    $returned_item_id = null;

    foreach ($products as $item_id => $product) {
        $license_meta_key = '_license_key_' . $item_id;
        $data = wc_get_order_item_meta($item_id, $license_meta_key, true);

        if ($data) {
            $stored_meta = json_decode($data, true);

            if (isset($stored_meta['license_key']) && $stored_meta['license_key'] === $license_key) {
                $returned_item_id = $item_id;
                error_log('[MERAF] meraf_update_order_meta: License key MATCHED for Item ID: ' . $item_id);
                break;
            }
        }
    }

    if ($returned_item_id) {
        error_log('[MERAF] meraf_update_order_meta: Updating meta for Item ID: ' . $returned_item_id);

        if ($license_data) {
            $license_meta_key = '_license_key_' . $returned_item_id;
            wc_update_order_item_meta($returned_item_id, $license_meta_key, $license_data);
            error_log('[MERAF] meraf_update_order_meta: SUCCESS - Order meta updated for Order ID ' . $order_id);
            return rest_ensure_response(array('message' => 'Order meta updated successfully'));
        } else {
            error_log('[MERAF] meraf_update_order_meta: No license data provided - calling update_stored_license_details');
            if (update_stored_license_details($order_id, $returned_item_id, $body)) {
                error_log('[MERAF] meraf_update_order_meta: SUCCESS - Order meta updated for Order ID ' . $order_id);
                return rest_ensure_response(array('message' => 'Order meta updated successfully'));
            } else {
                error_log('[MERAF] meraf_update_order_meta: ERROR - Failed to update order meta for Order ID: ' . $order_id);
                return new WP_Error('update_failed', 'Failed to update order meta.', array('status' => 500));
            }
        }
    } else {
        error_log('[MERAF] meraf_update_order_meta: No matching item found - trying product reference match');

        $list_of_product_in_order = get_item_id_of_order($order_id);
        $licenseProduct = $decoded_body['product_ref'];

        error_log('[MERAF] meraf_update_order_meta: Product reference from request: ' . $licenseProduct);
        error_log('[MERAF] meraf_update_order_meta: Products in order: ' . json_encode($list_of_product_in_order));

        $found_key = array_search($licenseProduct, $list_of_product_in_order);

        if ($found_key !== false) {
            error_log('[MERAF] meraf_update_order_meta: Product reference MATCHED - Item ID: ' . $found_key);
            $returned_item_id = $found_key;

            $license_meta_key = '_license_key_' . $returned_item_id;
            wc_update_order_item_meta($returned_item_id, $license_meta_key, $license_data);
            error_log('[MERAF] meraf_update_order_meta: SUCCESS - Order meta updated for Order ID ' . $order_id);
            return rest_ensure_response(array('message' => 'Order meta updated successfully'));
        } else {
            error_log('[MERAF] meraf_update_order_meta: ERROR - License data not found in the specified Order ID: ' . $order_id);
            return new WP_Error('license_not_found', 'License Data not found in the specified order.', array('status' => 404));
        }
    }
}

add_action('rest_api_init', function () {
    register_rest_route('meraf/v1', '/update-order-meta/(?P<order_id>\d+)', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'meraf_update_order_meta',
        'permission_callback' => 'meraf_validate_api_key',
    ));
});