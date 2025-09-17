<?php

use CodeIgniter\Cache\CacheFactory;
use Config\Services;

// Load the URL helper
helper('url');

if (!function_exists('getMyConfig')) {
    function getMyConfig($requestedAppKey = '', $userID = NULL)
    {
        $myConfig = [];
        $adminUserID = 0; // Super Admin ID, can be modified as per requirement

        // Set the Super Admin settings
        try {
            // Get a single instance of the database connection
            $db = db_connect();

            $defaultAppKey = 'Config\App';

            // Query the database to retrieve all super admin settings
            $query = $db->table('settings')
                        ->where('owner_id', $adminUserID); // Super Admin

            $result = $query->get();

            if (!$result) {
                log_message('error', '[Helper/MyConfig] Database query failed in getMyConfig: ' . $db->error()['message']);
                return [];
            }

            $settings = $result->getResult();

            // Process each setting and store it in the configuration array
            foreach ($settings as $setting) {
                $class = $setting->class;

                if (!isset($myConfig[$class])) {
                    $myConfig[$class] = [];
                }

                $myConfig[$class][$setting->key] = $setting->value;
            }

            // Configure app-related assets and fallback values
            $myConfig[$defaultAppKey]['appLogo_light'] = !empty($myConfig[$defaultAppKey]['appLogo_light'])
                ? base_url('writable/uploads/app-custom-assets/' . $myConfig[$defaultAppKey]['appLogo_light'])
                : base_url('assets/images/meraf-appLogo_light.png');

            $myConfig[$defaultAppKey]['appLogo_dark'] = !empty($myConfig[$defaultAppKey]['appLogo_dark'])
                ? base_url('writable/uploads/app-custom-assets/' . $myConfig[$defaultAppKey]['appLogo_dark'])
                : base_url('assets/images/meraf-appLogo_dark.png');

            $myConfig[$defaultAppKey]['appIcon'] = !empty($myConfig[$defaultAppKey]['appIcon'])
                ? base_url('writable/uploads/app-custom-assets/' . $myConfig[$defaultAppKey]['appIcon'])
                : base_url('assets/images/meraf-appIcon.png');

            $myConfig[$defaultAppKey]['appName'] = !empty($myConfig[$defaultAppKey]['appName'])
                ? $myConfig[$defaultAppKey]['appName']
                : 'MERAF Production Panel';
                
            // If Push Notifiation enabled
            $myConfig[$defaultAppKey]['hasEnabledNotifications'] = false;
            
            // Get the device ID from a cookie or session
            $session = session(); // load session library
            helper('cookie'); // load cookie helper
            $deviceId = get_cookie('device_id') ?? ($session->has('deviceId') ? $session->get('deviceId') : '');
            
            if ($deviceId && auth()->id()) {

                $result = $db->table('fcm_tokens')
                ->where('user_id', auth()->id())
                ->where('device_id', $deviceId)
                ->countAllResults();
            
                $myConfig[$defaultAppKey]['hasEnabledNotifications'] = $result > 0;
            }

            // Regenerate the 'push_notification_badge' using the correct URL format
            if($myConfig[$defaultAppKey]['push_notification_badge']) {
                $myConfig[$defaultAppKey]['push_notification_badge'] = base_url('writable/uploads/app-custom-assets/' . $myConfig[$defaultAppKey]['push_notification_badge']);
            }
            else {
                $myConfig[$defaultAppKey]['push_notification_badge'] = base_url('assets/images/meraf-push_notification_badge.png');
            }

        } catch (\Exception $e) {
            log_message('error', '[Helper/MyConfig] Exception in getMyConfig (Super Admin settings): ' . $e->getMessage());
            return [];
        }

        // Get the user-specific settings if a valid user ID is provided
        if ($userID !== NULL && $userID !== 0) {
            try {
                // Query the database for the user's settings
                $query = $db->table('settings')
                            ->where('owner_id', $userID);

                $result = $query->get();

                if (!$result) {
                    log_message('error', '[Helper/MyConfig] Database query failed in getMyConfig (User settings): ' . $db->error()['message']);
                    return [];
                }

                $settings = $result->getResult();

                // Load security helper for decryption
                helper('security');

                // List of secret keys that should be decrypted for display
                $secretKeys = [
                    'License_Create_SecretKey',
                    'License_Validate_SecretKey',
                    'License_DomainDevice_Registration_SecretKey',
                    'Manage_License_SecretKey',
                    'General_Info_SecretKey',
                    'reCAPTCHA_Secret_Key'
                ];

                // Process each user setting and merge it into the configuration array
                foreach ($settings as $setting) {
                    $class = $setting->class;

                    if (!isset($myConfig[$class])) {
                        $myConfig[$class] = [];
                    }

                    $value = $setting->value;

                    // Decrypt secret keys for display purposes
                    if (in_array($setting->key, $secretKeys) && !empty($value)) {
                        try {
                            // Check if the value is encrypted and decrypt it
                            if (is_encrypted_key($value)) {
                                $value = decrypt_secret_key($value, $userID);
                                log_message('debug', "Decrypted secret key '{$setting->key}' for display to user {$userID}");
                            }
                        } catch (Exception $e) {
                            // If decryption fails, keep the original value (backward compatibility)
                            log_message('warning', "Failed to decrypt secret key '{$setting->key}' for user {$userID}: " . $e->getMessage());
                            $value = $setting->value;
                        }
                    }

                    $myConfig[$class][$setting->key] = $value;
                }

            } catch (\Exception $e) {
                log_message('error', '[Helper/MyConfig] Exception in getMyConfig (User settings): ' . $e->getMessage());
                return [];
            }
        }

        // Set the default class if not provided
        $requestedAppKey = $requestedAppKey ?: $defaultAppKey;

        // Return the configuration for the requested class, or an empty array if not found
        return $myConfig[$requestedAppKey] ?? [];
    }
}

if (!function_exists('generateApiKey')) {
    function generateApiKey($length = 15)
    {

        $apiKey = bin2hex(random_bytes($length));

        // // Generate random bytes using a CSPRNG
        // $randomBytes = random_bytes($length);

        // // Encode the random bytes in Base64 format
        // $apiKey = base64_encode($randomBytes);

        // // Optionally, you can remove any characters that are not suitable for an API key
        // $apiKey = str_replace(['+', '/', '=', '-', '_'], [''], $apiKey);

        return $apiKey;
    }
}

if (!function_exists('generateUserApiKey')) {
    /**
     * Generate a 6-character alphanumeric user API key
     * Uses uppercase letters and numbers for better readability
     *
     * @return string 6-character alphanumeric key
     */
    function generateUserApiKey()
    {
        // Character set: A-Z and 0-9 (excluding easily confused characters like 0, O, I, 1)
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $keyLength = 6;
        $apiKey = '';

        // Generate cryptographically secure random key
        for ($i = 0; $i < $keyLength; $i++) {
            $randomIndex = random_int(0, strlen($characters) - 1);
            $apiKey .= $characters[$randomIndex];
        }

        return $apiKey;
    }
}

if (!function_exists('disableRegistration')) {
    function disableRegistration() {
		// File path
		$file_path = APPPATH . 'Config/Auth.php';
	
		// Open the file for reading
		$file_content = file_get_contents($file_path);
	
		// Line to find and replace
		$line_to_find = 'public bool $allowRegistration = true;';
		$new_line = 'public bool $allowRegistration = false;';
	
		// Replace the line
		$file_content = str_replace($line_to_find, $new_line, $file_content);
	
		// Open the file for writing
		file_put_contents($file_path, $file_content);
	}
}

if (!function_exists('enableRegistration')) {
    function enableRegistration() {
		// File path
		$file_path = APPPATH . 'Config/Auth.php';
	
		// Open the file for reading
		$file_content = file_get_contents($file_path);
	
		// Line to find and replace
		$line_to_find = 'public bool $allowRegistration = false;';
		$new_line = 'public bool $allowRegistration = true;';
	
		// Replace the line
		$file_content = str_replace($line_to_find, $new_line, $file_content);
	
		// Open the file for writing
		file_put_contents($file_path, $file_content);
	}
}

if (!function_exists('clearCache')) {
    function clearCache(): bool
    {
        try {
            // Get cache configuration
            $config = config(\Config\Cache::class);
            
            // Create cache factory instance
            $cacheFactory = new \CodeIgniter\Cache\CacheFactory();
            
            // Get handler instance from factory
            $handler = $cacheFactory->getHandler($config);

            // Check handler type and clean
            if ($config->handler === 'file' && $handler instanceof \CodeIgniter\Cache\Handlers\FileHandler) {
                $handler->clean();
            }

            if ($config->handler === 'redis' && $handler instanceof \CodeIgniter\Cache\Handlers\RedisHandler) {
                $handler->clean();
            }

            if ($config->handler === 'memcached' && $handler instanceof \CodeIgniter\Cache\Handlers\MemcachedHandler) {
                $handler->clean();
            }

            if ($config->handler === 'predis' && $handler instanceof \CodeIgniter\Cache\Handlers\PredisHandler) {
                $handler->clean();
            }

            if ($config->handler === 'wincache' && $handler instanceof \CodeIgniter\Cache\Handlers\WincacheHandler) {
                $handler->clean();
            }

            // Fallback for other handlers
            $handler->clean();

            // Clear debugbar folder
            $debugbarPath = WRITEPATH . 'debugbar';
            if (is_dir($debugbarPath)) {
                $files = glob($debugbarPath . '/*'); // Get all files in debugbar
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file); // Delete file
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Failed to clear cache: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('deleteDirectory')) {
    /**
     * Delete a directory and its contents recursively
     * 
     * @param string $dirPath Path to the directory to delete
     * @param bool $keepParent Whether to keep the parent directory (default: false)
     * @return bool True on success, false on failure
     */
    function deleteDirectory($dirPath, $keepParent = false) {
        if (!is_dir($dirPath)) {
            return false;
        }

        // Normalize directory path
        $dirPath = rtrim($dirPath, '/\\');

        try {
            $items = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            // Delete all files and subdirectories
            foreach ($items as $item) {
                if ($item->isDir()) {
                    if (!rmdir($item->getRealPath())) {
                        throw new Exception("Failed to remove directory: " . $item->getRealPath());
                    }
                } else {
                    if (!unlink($item->getRealPath())) {
                        throw new Exception("Failed to remove file: " . $item->getRealPath());
                    }
                }
            }

            // Delete the parent directory if required
            if (!$keepParent) {
                if (!rmdir($dirPath)) {
                    throw new Exception("Failed to remove parent directory: " . $dirPath);
                }
            }

            return true;
        } catch (Exception $e) {
            // Log the error if you have logging configured
            log_message('error', $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('copyDirectory')) {
    /**
     * Copy a directory and its contents recursively
     * 
     * @param string $source Source directory path
     * @param string $destination Destination directory path
     * @param array $exclusions List of files/folders to exclude
     * @return bool True on success, false on failure
     */
    function copyDirectory($source, $destination, $exclusions = []) {
        try {
            // Normalize paths
            $source = rtrim($source, '/\\');
            $destination = rtrim($destination, '/\\');

            // Check if source exists and is readable
            if (!is_dir($source) || !is_readable($source)) {
                throw new Exception("Source directory '$source' does not exist or is not readable");
            }

            // Create destination directory if it doesn't exist
            if (!is_dir($destination)) {
                if (!mkdir($destination, 0755, true)) {
                    throw new Exception("Failed to create destination directory '$destination'");
                }
            }

            // Check if destination is writable
            if (!is_writable($destination)) {
                throw new Exception("Destination directory '$destination' is not writable");
            }

            $dir = @opendir($source);
            if ($dir === false) {
                throw new Exception("Failed to open source directory '$source'");
            }

            $success = true;

            while (($file = readdir($dir)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                // Check if file/directory should be excluded
                if (in_array($file, $exclusions)) {
                    continue;
                }

                $src = $source . DIRECTORY_SEPARATOR . $file;
                $dst = $destination . DIRECTORY_SEPARATOR . $file;

                if (is_dir($src)) {
                    // Recursively copy subdirectory
                    if (!copyDirectory($src, $dst, $exclusions)) {
                        $success = false;
                    }
                } else {
                    // Copy file and preserve permissions
                    if (!copy($src, $dst)) {
                        throw new Exception("Failed to copy file '$src' to '$dst'");
                    }
                    
                    // Preserve file permissions
                    chmod($dst, fileperms($src));
                }
            }

            closedir($dir);
            return $success;

        } catch (Exception $e) {
            // Log the error if you have logging configured
            log_message('error', $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('makeApiCall')) {
    function makeApiCall(string $url, array $headers = ['Accept' => 'application/json'], string $method = 'GET', $body = null): \CodeIgniter\HTTP\Response {
        $curl = \Config\Services::curlrequest();

        $options = [
            'headers' => $headers,
            'connect_timeout' => 30,
            'timeout' => 30,
            'http_version' => '1.1',
            'allow_redirects' => [
                'max' => 10,
            ],
        ];

        // Add body for POST/PUT requests
        if ($body !== null && in_array(strtoupper($method), ['POST', 'PUT'])) {
            if (is_array($body)) {
                $options['json'] = $body;
            } else {
                $options['body'] = $body;
            }
        }

        // Make the request
        $response = $curl->request($method, $url, $options);
        
        // For other API calls, check for success status
        if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
            throw new \Exception('Received API Status Code: ' . $response->getStatusCode());
        }

        return $response;
    }
}

if (!function_exists('fetchVersionDetails')) {
    function fetchVersionDetails()
    {
        $productJSON = 'https://prod.merafsolutions.com/products/MERAF%20Production%20Panel%20SaaS';
        $appDetailsPath = USER_DATA_PATH . 'MERAF.json';
        $productDetails = null;

        try {
            // Attempt to make the API call
            $response = makeApiCall($productJSON);
            $body = trim($response->getBody());

            // Check if the response body is empty
            if (!empty($body)) {
                // Ensure the body is UTF-8 encoded
                if (!mb_check_encoding($body, 'UTF-8')) {
                    $body = utf8_encode($body);
                }

                // Write the response body to the app details path
                file_put_contents($appDetailsPath, $body);
            } else {
                // Log an error if the response body is empty
                log_message('error', '[Helper/MyConfig] Received an empty body from product details API');
            }

            // Check if the app details file exists and always return its content
            if (file_exists($appDetailsPath)) {
                $productDetails = json_decode(file_get_contents($appDetailsPath), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Handle JSON decoding error if necessary
                    log_message('error', '[Helper/MyConfig] Failed to decode JSON from ' . $appDetailsPath . ': ' . json_last_error_msg());
                    return false;
                }

                return $productDetails;
            } else {
                log_message('error', '[Helper/MyConfig] The file ' . $appDetailsPath . ' does not exist.');
                return false;
            }
        } catch (\Exception $e) {
            // Handle any exceptions during the API call
            log_message('error', '[Helper/MyConfig] Failed to fetch product details: ' . $e->getMessage());
            
            // In case of error, return the existing app details
            if (file_exists($appDetailsPath)) {
                return json_decode(file_get_contents($appDetailsPath), true);
            }

            return false;
        }
    }
}

if (!function_exists('importDefaultUserSettings')) {
    function importDefaultUserSettings($userID)
	{
        if(auth()->user()) { 
            $file = APPPATH . 'Database/Migrations/defaultUserSettings.sql';
            $sql = file_get_contents($file);
        
            // Replace the `user_id` placeholder with the actual user ID
            $sql = str_replace(
                array(
                    '{{user_id}}',
                    '{{domain_name}}',
                    '{{user_email}}'
                ),
                array(
                    $userID,
                    getHostFromCurrentUrl(),
                    auth()->user()->email
                ),
                $sql);
        
            $db = db_connect();
            $db->transStart();
        
            try {
                $db->query($sql);
                $db->transComplete();
            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }
         }


    }
}

if (!function_exists('_dissectArray')) {
    function _dissectArray($value) {
        if (is_array($value) || is_object($value)) {
            $subchildArray = [];
            foreach ($value as $point2 => $subchild) {							
                if (is_array($value) || is_object($value)) {
                    
                    return '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>- ' . $point2 . ':</strong> ' . _dissectArray($subchild);
                }
                else {
                    return '<br>&nbsp;↳'. $point2 . ' => ' . $subchild;
                }
            }
        } else {
            return $value;
        }
    }
}

if (!function_exists('setAppInfoCookie')) {
    function setAppInfoCookie() {

        $myConfig = getMyConfig('', 0);

        if($myConfig['appLogo_light'] !== NULL) {
            $logo = $myConfig['appLogo_light'];
        }

        if($myConfig['appIcon'] !== NULL) {
            $icon = $myConfig['appIcon'];
        }        
    
        $merafAppData = [
            'name' => $myConfig['appName'],
            'logo' => $logo,
            'icon' => $icon
        ];
    
        // Convert the array to JSON
        $merafAppDataJson = json_encode($merafAppData);
    
        // Set the cookie
        // setcookie('meraf_app_info', $merafAppDataJson, time() + (3 * 30 * 24 * 60 * 60), '/'); // 3 months expiration
        setcookie('meraf_app_info', $merafAppDataJson, time() + 3600, '/'); // 1 hour expiration
    }
}

if (!function_exists('removeAppInfoCookie')) {
    function removeAppInfoCookie() {
        if (isset($_COOKIE['meraf_app_info'])) {
            setcookie('meraf_app_info', '', time() - 3600, '/');
            unset($_COOKIE['meraf_app_info']);
        }
    }
}


