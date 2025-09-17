<?php
/*
Plugin Name: MERAF Production Panel
Description: This plugin extends the functionality of MERAF Production Panel by integrating it with WooCommerce. Digital products can be purchased using WooCommerce and successful purchases will automatically create a license and send an email notification to the buyer.
Version: 1.2.3
Author: MERAF Digital Solutions
*/

// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

// Check if WooCommerce is active
function meraf_production_panel_requirements() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'meraf_production_panel_woocommerce_missing_notice');
        return;
    }

    add_action('admin_menu', 'meraf_production_panel_menu');

    $prodPanelURL = get_option('prodPanelURL', '');
    $prodPanelCreationSecretKey = get_option('prodPanelCreationSecretKey', '');
    
    if ($prodPanelURL && $prodPanelCreationSecretKey) {
        add_action('woocommerce_order_status_completed', 'meraf_production_panel_create_license', 10, 1);
    }
}
add_action('plugins_loaded', 'meraf_production_panel_requirements');

function meraf_production_panel_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php _e('MERAF Production Panel requires WooCommerce to be installed and activated.', 'meraf-production-panel'); ?></p>
    </div>
    <?php
}

function meraf_production_panel_menu() {
    add_submenu_page(
        'woocommerce',
        'MERAF Production Panel',
        'MERAF Production Panel',
        'manage_options',
        'meraf-production-panel-settings',
        'meraf_production_panel_settings'
    );
}

function meraf_production_panel_settings() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $prodPanelURL = get_option('prodPanelURL', '');
    $prodPanelCreationSecretKey = get_option('prodPanelCreationSecretKey', '');
    $prodPanelGeneralSecretKey = get_option('prodPanelGeneralSecretKey', '');
    $error_message = '';
    $success_message = '';

    if (isset($_POST['meraf_production_panel_save'])) {
        $prodPanelURL = esc_url_raw($_POST['prodPanelURL']);
        $prodPanelCreationSecretKey = sanitize_text_field($_POST['prodPanelCreationSecretKey']);
        $prodPanelGeneralSecretKey = sanitize_text_field($_POST['prodPanelGeneralSecretKey']);
        
        if (strpos($prodPanelURL, 'https://') !== 0) {
            $error_message = 'URL must start with "https://".';
        } elseif (substr($prodPanelURL, -1) !== '/') {
            $prodPanelURL .= '/';
        } elseif (empty($prodPanelCreationSecretKey)) {
            $error_message = 'Please enter your license creation secret key from the Production Panel.';
        } elseif (empty($prodPanelGeneralSecretKey)) {
            $error_message = 'Please enter your license validation secret key from the Production Panel.';
        } else {
            update_option('prodPanelURL', $prodPanelURL);
            update_option('prodPanelCreationSecretKey', $prodPanelCreationSecretKey);
            update_option('prodPanelGeneralSecretKey', $prodPanelGeneralSecretKey);
            $success_message = 'Settings saved successfully!';
        }
    }

    $api_validation_result = null;
    $api_status_message = '';

    if ($prodPanelURL && filter_var($prodPanelURL, FILTER_VALIDATE_URL) && strpos($prodPanelURL, 'https://') === 0) {
        // Test basic connectivity
        $response = wp_remote_get($prodPanelURL, array(
            'timeout' => 10,
            'sslverify' => true
        ));

        if (is_wp_error($response)) {
            $api_validation_result = 'error';
            $api_status_message = 'Connection failed: ' . $response->get_error_message();
        } else {
            $response_code = wp_remote_retrieve_response_code($response);

            if ($response_code === 200) {
                // Test API endpoint if we have the general secret key
                if ($prodPanelGeneralSecretKey) {
                    $api_test_url = $prodPanelURL . 'api/license/config/' . $prodPanelGeneralSecretKey;
                    $api_response = wp_remote_get($api_test_url, array('timeout' => 10));

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
                    $api_test_url = $prodPanelURL . 'api/license/config/' . $prodPanelGeneralSecretKey;
                    $api_response = wp_remote_get($api_test_url, array('timeout' => 10));

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
                <h1>MERAF Production Panel - General Settings</h1>
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
                    <p class="description">Enter the full URL of your MERAF Production Panel (must use HTTPS)</p>
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
                    <label for="prodPanelGeneralSecretKey">License General Info Secret Key</label>
                    <input type="text"
                           id="prodPanelGeneralSecretKey"
                           name="prodPanelGeneralSecretKey"
                           value="<?php echo $prodPanelGeneralSecretKey ? esc_attr($prodPanelGeneralSecretKey) : ''; ?>"
                           placeholder="Enter your license validation secret key" />
                    <p class="description">Secret key used for license validation and retrieving license information</p>
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
            <?php if ($prodPanelURL && $prodPanelCreationSecretKey && $prodPanelGeneralSecretKey): ?>
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
            error_log('The purchased product is not in the MERAF Production Panel products (' . $product->get_name() . ')');
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
 * Call API to get list of products in MERAF Production Panel
 */
function get_list_product_prod_panel() {
    // Retrieve plugin options
    $prodPanelURL = get_option('prodPanelURL', '');
    
    if($prodPanelURL) {

        $response = wp_remote_get($prodPanelURL . 'api/product/with-variations');

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log("Error retrieving product list from MERAF Production Panel -> " . $error_message);
            return "Error retrieving product list from MERAF Production Panel -> " . $error_message;
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true); // Assuming the response is JSON
            // Process the $data as needed

            return $data;        
        }
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
    // Set the API vars
    $prodPanelURL = get_option('prodPanelURL', '');
    $generalSecretKey = get_option('prodPanelGeneralSecretKey', '');

    // Set the meta key
    $license_meta_key = '_license_key_' . $query_item_id;

    // Get the order details by order ID
    $order = wc_get_order($query_order_id);

    // If JSON encoded license details declared
    if($licenseDetails) {
        // Save the new meta data
        wc_update_order_item_meta($query_item_id, $license_meta_key, $licenseDetails);
        error_log('Updated: ' . $license_meta_key . ' -> ' . $licenseDetails);
        return true;
    }

    // Check if order and API vars are valid
    if ($prodPanelURL && $generalSecretKey && $order) {
        // Retrieve the specific item by item ID
        $items = $order->get_items();
        $item = isset($items[$query_item_id]) ? $items[$query_item_id] : null;

        if (!$item) {
            error_log('Item ID ' . $query_item_id . ' not found in Order #' . $query_order_id);
            return false;
        }

        // Get transaction ID or fallback to order ID
        $txnID = $order->get_transaction_id() ? $order->get_transaction_id() : $order->get_id();

        // Get product details
        $product = $item->get_product();
        $productName = $product ? $product->get_name() : '';

        // Retrieve the license key from the API
        error_log('Calling API: ' . $prodPanelURL . 'api/license/data/' . $generalSecretKey . '/' . $txnID . '/' . urlencode($productName));
        $response = wp_remote_get($prodPanelURL . 'api/license/data/' . $generalSecretKey . '/' . $txnID . '/' . urlencode($productName));

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log("Error retrieving the license key: " . esc_html($error_message));
            return false;
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true); // Assuming the response is JSON

            // Process the $data as needed
            if (!isset($data['error']) && isset($data['license_key'])) {
                // Save the new meta data
                $saveData = json_encode($data, JSON_FORCE_OBJECT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);                
                wc_update_order_item_meta($query_item_id, $license_meta_key, $saveData);
                error_log('Updated: ' . $license_meta_key . ' -> ' . $saveData);
                return true;
            } else {
                error_log('Unable to retrieve license for order item ID #' . $query_item_id);
                return false;
            }
        }
    }

    error_log('Failed to update Order ID ' . $query_order_id . ' with Item ID ' . $query_item_id);
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
 * Evaluate the ordered item with MERAF Production Panel product list for correct license query
 */
function query_product_name_for_license_creation($product) {

    if (!$product) {
        return; // Exit if order ID not found
    }

    // Initialize variables
    $productToCreateLicense = null;

    // Get the product object
    // $product = $item->get_product();
    $productVariations = [];
    $productType = null;
    $productBasename = '';

    // Check if the product is a variation
    if ($product->is_type('variation')) {
        // If it's a variation, get the parent product name
        $parent_product = wc_get_product($product->get_parent_id());
        $productBasename = $parent_product ? $parent_product->get_name() : $product->get_name();

        // Get the variation attributes
        $productVariations = $product->get_variation_attributes();
    } else {
        // If it's not a variation, get the product name directly
        $productBasename = $product->get_name();
    }

    // If variations exist, loop through them
    if (!empty($productVariations)) {
        foreach ($productVariations as $attribute_name => $attribute_value) {
            // Get attribute name in readable format
            $attribute_label = wc_attribute_label(str_replace('attribute_', '', $attribute_name), $product);

            // Check for "version" attribute
            if (stripos($attribute_label, 'version') !== false) {
                $productType = $attribute_value;
            }         
        }
    }

    // Reconstruct the product name query
    $productQuery = $productType ? $productBasename . ' ' . $productType : $productBasename;

    // Get the list of products from the production panel
    $productionPanelProductList = get_list_product_prod_panel();

    // Loop through the production panel product list
    foreach ($productionPanelProductList as $product) {
        if (stripos($product, $productQuery) !== false) {
            // If productQuery is found in the product name, set the product to create license
            $productToCreateLicense = $product;
            break; // Exit loop after finding the product
        }
    }

    // Return the found product to create a license
    return $productToCreateLicense;

    // return null; // Return null if no product found
}

/**
 * Call API in MERAF Production Panel to create new license and add meta key for each product.
 */
function meraf_production_panel_create_license($order_id) {
    $prodPanelURL = get_option('prodPanelURL', '');
    $prodPanelCreationSecretKey = get_option('prodPanelCreationSecretKey', '');
    $prodPanelGeneralSecretKey = get_option('prodPanelGeneralSecretKey', '');

    if (empty($prodPanelURL) || empty($prodPanelCreationSecretKey)) {
        error_log('License creation aborted as no plugin settings defined');
        return; // Exit if settings are not defined
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        error_log('Order ID not found');
        return; // Exit if order id not found
    }    

    // Retrieve the settings for new license from MERAF Production Panel
    $api_url = $prodPanelURL . 'api/license/config/' . $prodPanelGeneralSecretKey;
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("Error retrieving MERAF Production Panel config -> " . $error_message);
        return "Error retrieving MERAF Production Panel config -> " . $error_message;
    } else {
        $body = wp_remote_retrieve_body($response);
        error_log('API result for new license config -> ' . $body);

        $data = json_decode($body, true);

        $firstName = $order->get_billing_first_name();
        $lastName = $order->get_billing_last_name();
        $emailAddress = $order->get_billing_email();
        $companyName = $order->get_billing_company();
        $txnID = $order->get_id(); // WooCommerce order #
        $purchaseID = $order->get_transaction_id() ? $order->get_transaction_id() : $txnID; // Payment gateway transaction reference
        $products = $order->get_items();

        foreach ($products as $item_id => $product) {
            $license_meta_key = '_license_key_' . $item_id;
            $withLicense = wc_get_order_item_meta($item_id, $license_meta_key, true);

            if(!$withLicense) { 
                error_log('No license saved for item ID ' . $item_id);
            } else {
                error_log('License already created for item ID ' . $item_id);
            }

            if(!$withLicense) { // Proceed with license creation if no license exists

                $product_name_for_license_creation = query_product_name_for_license_creation($product);

                if (!$product_name_for_license_creation) {
                    error_log('The purchased product is not in the MERAF Production Panel products (' . $product->get_name() . ')');
                    continue; // Continue to the next product if not found
                }
    
                $productName = $product->get_name();
                $productReference = $product_name_for_license_creation;
    
                // Identify the license type
                $productAttributes = get_ordered_product_with_attributes($order_id);
    
                foreach ($productAttributes[$productName] as $attribute => $value) {
                    if (stripos($value, 'lifetime') !== false) {
                        $licenseType = 'lifetime';
                        $billingLength = '';
                        $billingInterval = 'onetime';
                        $dateExpiry = '';
                    } else if (stripos($value, 'monthly') !== false) {
                        $licenseType = 'subscription';
                        $billingLength = '1';
                        $billingInterval = 'months';
                        $currentDate = new DateTime();
                        $currentDate->modify('+1 month');
                        $dateExpiry = $currentDate->format('Y-m-d H:i:s');
                    } else {
                        $licenseType = 'trial';
                        $billingLength = $data['defaultTrialDays'];
                        $billingInterval = 'days';
                        $currentDate = new DateTime();
                        $currentDate->modify('+' . $data['defaultTrialDays'] . ' day');
                        $dateExpiry = $currentDate->format('Y-m-d H:i:s');
                    }
                }
    
                // Construct the API URL
                $query_params = [
                    'license_status' => $data['default_license_status'],
                    'license_type' => $licenseType,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $emailAddress,
                    'subscr_id' => $txnID,
                    'company_name' => $companyName,
                    'max_allowed_domains' => $data['defaultAllowedDomains'],
                    'max_allowed_devices' => $data['defaultAllowedDevices'],
                    'product_ref' => $productReference,
                    'txn_id' => $txnID,
                    'purchase_id_' => $purchaseID,
                    'date_expiry' => $dateExpiry,
                    'billing_length' => $billingLength,
                    'billing_interval' => $billingInterval,
                    'item_reference' => 'woocommerce',
                ];
                $api_url = $prodPanelURL . 'api/license/create/' . $prodPanelCreationSecretKey . '/data?' . http_build_query($query_params);
    
                error_log('API URL: ' . $api_url);
    
                // Call the API
                $response = wp_remote_get($api_url);
    
                if (is_wp_error($response)) {
                    error_log('Encountered error in API call for license creation: ' . $response->get_error_message());
                } else {
                    error_log('License creation API call result: ' . $response['body']);
    
                    // Add the generated license key to the product meta
                    $apiFeedback = json_decode($response['body'], true);
                    if ($apiFeedback['result'] === 'success') {
                        $key = $apiFeedback['key'];
    
                        // Generate a unique meta key for each item
                        $license_meta_value = $response['body'];
    
                        // Save the new meta data
                        $order->update_meta_data($license_meta_key, $license_meta_value);
                        $order->save();
                        error_log('Updated: ' . $license_meta_key . '->' . $license_meta_value);
                    }
                }
            }
        }
    }
}

/**
 * Add custom <li> as the last item in the <ul class="wc-item-meta"> in the order view page through the API.
 */
function show_license_key_view_order_page($item_id, $item, $order, $plain_text) {
    if ($order->get_status() !== 'completed') {
        return;
    }

    $prodPanelURL = get_option('prodPanelURL', '');
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
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if ($order && 'completed' !== $order->get_status() && $order->has_status('processing')) {
        $order->update_status('completed');
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
    $headers = getallheaders();
    $headers = array_change_key_case($headers, CASE_UPPER);

    if (isset($headers['X-API-KEY']) && $headers['X-API-KEY'] === get_option('prodPanelGeneralSecretKey', '')) {
        return true;
    }
    error_log('Invalid API Key to process the received Order meta update');
    return new WP_Error('invalid_api_key', 'Invalid API Key', array('status' => 403));
}

/**
 * Update order meta via REST API
 */
function meraf_update_order_meta(WP_REST_Request $request) {
    $body = $request->get_body();
    $decoded_body = json_decode($body, true);

    $order_id = isset($decoded_body['txn_id']) ? $decoded_body['txn_id'] : null;
    $license_key = isset($decoded_body['license_key']) ? $decoded_body['license_key'] : null;
    $license_data = $body !== '' ? $body : null;

    if (!$order_id || !$license_key) {
        error_log('Order ID and License Key are required.');
        return new WP_Error('missing_data', 'Order ID and License Key are required.', array('status' => 400));
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        error_log('Order not found for ID: ' . $order_id);
        return new WP_Error('order_not_found', 'Order not found for ID: ' . $order_id, array('status' => 404));
    }

    $products = $order->get_items();
    $returned_item_id = null;

    foreach ($products as $item_id => $product) {
        $license_meta_key = '_license_key_' . $item_id;
        $data = wc_get_order_item_meta($item_id, $license_meta_key, true);

        if ($data) {
            $stored_meta = json_decode($data, true);

            if (isset($stored_meta['license_key']) && $stored_meta['license_key'] === $license_key) {
                $returned_item_id = $item_id;
                break;
            }
        }    
    }

    if ($returned_item_id) {
        if ($license_data) {
            $license_meta_key = '_license_key_' . $returned_item_id;
            wc_update_order_item_meta($returned_item_id, $license_meta_key, $license_data);
            error_log('Order meta for ID '.$order_id.' updated successfully');
            return rest_ensure_response(array('message' => 'Order meta updated successfully'));
        } else {
            if (update_stored_license_details($order_id, $returned_item_id, $body)) {
                error_log('Order meta for ID '.$order_id.' updated successfully');
                return rest_ensure_response(array('message' => 'Order meta updated successfully'));
            } else {
                error_log('Failed to update order meta under Order ID:'.$order_id);
                return new WP_Error('update_failed', 'Failed to update order meta.', array('status' => 500));
            }
        }
    } else {
        
        $list_of_product_in_order = get_item_id_of_order($order_id);
        $licenseProduct = $decoded_body['product_ref'];
        
        $found_key = array_search($licenseProduct, $list_of_product_in_order);
        
        if ($found_key !== false) {
            error_log('Order item ID is empty. Attempting to update the Order meta if matched with the license product reference and Order ID:'.$order_id);
            $returned_item_id = $found_key;

            $license_meta_key = '_license_key_' . $returned_item_id;
            wc_update_order_item_meta($returned_item_id, $license_meta_key, $license_data);
            error_log('Order meta for ID '.$order_id.' updated successfully');
            return rest_ensure_response(array('message' => 'Order meta updated successfully'));
        } else {
            error_log('License Data not found in the specified Order ID:'.$order_id);
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