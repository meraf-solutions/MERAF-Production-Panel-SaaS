<?php

namespace App\Libraries;

use CodeIgniter\I18n\Time;
use CodeIgniter\Config\Services;
use Exception;

class FirebaseService
{
    protected $db;
    protected $firebaseConfig;
    protected $adminSdkPath;
    protected $myConfig;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        
        $this->myConfig = getMyConfig('', 0);
        
        // Check if any Firebase Web SDK config is missing
        $requiredKeys = [
            'fcm_apiKey',
            'fcm_authDomain',
            'fcm_projectId',
            'fcm_storageBucket',
            'fcm_messagingSenderId',
            'fcm_appId',
            'fcm_measurementId',
            'fcm_private_key_file'
        ];
    
        foreach ($requiredKeys as $key) {
            if (empty($this->myConfig[$key])) {
                log_message('error', "Missing Firebase config key: {$key}");
                return false;
            }
        }

        // Firebase Web SDK Config
        $this->firebaseConfig = [
            'apiKey' => $this->myConfig['fcm_apiKey'],
            'authDomain' => $this->myConfig['fcm_authDomain'],
            'projectId' => $this->myConfig['fcm_projectId'],
            'storageBucket' => $this->myConfig['fcm_storageBucket'],
            'messagingSenderId' => $this->myConfig['fcm_messagingSenderId'],
            'appId' => $this->myConfig['fcm_appId'],
            'measurementId' => $this->myConfig['fcm_measurementId'],
        ];
        
        // Path to Firebase Admin SDK private key file
        $this->adminSdkPath = USER_DATA_PATH . $this->myConfig['fcm_private_key_file'];
        
        if(!file_exists($this->adminSdkPath)) {
            log_message('error', "Firebase private key file not found.");
            $this->UserSettingsModel->setUserSetting('push_notification_feature_enabled', null, 0);
            $this->UserSettingsModel->setUserSetting('fcm_private_key_file', null, 0);
            return false;
        }
    }
    
    /**
     * Send a notification to a specific user via Firebase Cloud Messaging
     *
     * @param int $userId The user ID to send notification to
     * @param string $message The notification message
     * @param string $type The notification type
     * @param string|null $link Optional link associated with the notification
     * @return bool True if notification was sent successfully, false otherwise
     */
    public function sendNotification($userId, $message, $type, $link = null)
    {
        try {
            // Get user's FCM tokens from database
            $tokens = $this->getUserTokens($userId);
            
            if (empty($tokens)) {
                log_message('info', "No FCM tokens found for user ID: $userId");
                return false;
            }
            
            // Prepare notification data
            $notification = [
                'title' => 'New Notification',
                'body' => $message,
                'icon' => $this->myConfig['appIcon'],
                'click_action' => $link ?? base_url(),
                'badge' => $this->myConfig['push_notification_badge']
            ];

            $data = [
                'type' => $type,
                'message' => $message,
                'link' => $link ?? '',
                'timestamp' => (string)time(),
                'id' => uniqid(),
                'foreground' => 'false' // Default to false
            ];
            
            // Send to each token
            $successCount = 0;
            $tokenCount = count($tokens);
            
            foreach ($tokens as $token) {
                if ($this->sendFcmMessage($token, $notification, $data)) {
                    $successCount++;
                }
            }
            
            log_message('info', "[sendNotification] FCM notification sent to $successCount/$tokenCount devices for user ID: $userId");
            return $successCount > 0;
        } catch (Exception $e) {
            log_message('error', "Error sending FCM notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send a notification to all users via Firebase Cloud Messaging
     *
     * @param string $message The notification message
     * @param string $type The notification type
     * @param string|null $link Optional link associated with the notification
     * @return bool True if notification was sent successfully, false otherwise
     */
    public function sendNotificationToAll($message, $type, $link = null)
    {
        try {
            // Get all FCM tokens from database
            $tokens = $this->getAllTokens();
            
            if (empty($tokens)) {
                log_message('info', "No FCM tokens found for any users");
                return false;
            }
            
            // Prepare notification data
            $notification = [
                'title' => 'New Notification',
                'body' => $message,
                'icon' => $this->myConfig['appIcon'],
                'click_action' => $link ?? base_url(),
                'badge' => $this->myConfig['push_notification_badge']
            ];
            
            $data = [
                'type' => $type,
                'message' => $message,
                'link' => $link ?? '',
                'timestamp' => (string)time(),
                'id' => uniqid(),
                'foreground' => 'false' // Default to false
            ];
            
            // Send to each token
            $successCount = 0;
            $tokenCount = count($tokens);
            
            foreach ($tokens as $token) {
                if ($this->sendFcmMessage($token, $notification, $data)) {
                    $successCount++;
                }
            }
            
            log_message('info', "[sendNotificationToAll] FCM notification sent to $successCount/$tokenCount devices");
            return $successCount > 0;
        } catch (Exception $e) {
            log_message('error', "Error sending FCM notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Save or update a user's FCM token
     *
     * @param int $userId The user ID
     * @param string $token The FCM token
     * @param string $device Device information
     * @return bool True if token was saved successfully, false otherwise
     */
    public function saveToken($userId, $token, $device = '')
    {
        try {
            // Parse device info to extract device ID
            $deviceInfo = json_decode($device, true);
            $deviceId = $deviceInfo['deviceId'] ?? '';

            // Check if token already exists
            $existingToken = $this->db->table('fcm_tokens')
                ->where('token', $token)
                ->get()
                ->getRowArray();
            
            if ($existingToken) {
                // Update existing token
                $this->db->table('fcm_tokens')
                    ->where('token', $token)
                    ->update([
                        'user_id' => $userId,
                        'device_id' => $deviceId,
                        'device' => $device,
                        'updated_at' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                        'last_used' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s') // Track when token was last used
                    ]);
            } else {
                // Insert new token
                $this->db->table('fcm_tokens')
                    ->insert([
                        'user_id' => $userId,
                        'token' => $token,
                        'device_id' => $deviceId,
                        'device' => $device,
                        'created_at' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                        'updated_at' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                        'last_used' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s') // Track when token was created
                    ]);
            }
            
            return true;
        } catch (Exception $e) {
            log_message('error', "Error saving FCM token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a user's FCM token
     *
     * @param string $token The FCM token to delete
     * @return bool True if token was deleted successfully, false otherwise
     */
    public function deleteToken($token)
    {
        try {
            $this->db->table('fcm_tokens')
                ->where('token', $token)
                ->delete();
            
            return true;
        } catch (Exception $e) {
            log_message('error', "Error deleting FCM token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all FCM tokens for a specific user
     *
     * @param int $userId The user ID
     * @return array Array of FCM tokens
     */
    protected function getUserTokens($userId)
    {
        $result = $this->db->table('fcm_tokens')
            ->select('token')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();
        
        return array_column($result, 'token');
    }

    /**
     * Check if a specific device has FCM token registered for a user
     *
     * @param int $userId The user ID
     * @param string $deviceId The device ID
     * @return bool True if the device has a token, false otherwise
     */
    public function deviceHasToken($userId, $deviceId)
    {
        $result = $this->db->table('fcm_tokens')
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->countAllResults();
        
        return $result > 0;
    }
    
    /**
     * Get all FCM tokens for all users
     *
     * @return array Array of FCM tokens
     */
    protected function getAllTokens()
    {
        $result = $this->db->table('fcm_tokens')
            ->select('token')
            ->get()
            ->getResultArray();
        
        return array_column($result, 'token');
    }
    
    /**
     * Send FCM message to a specific token
     *
     * @param string $token The FCM token
     * @param array $notification The notification payload
     * @param array $data Additional data payload
     * @return bool True if message was sent successfully, false otherwise
     */
    protected function sendFcmMessage($token, $notification, $data)
    {
        try {
            // Load service account credentials
            $serviceAccount = json_decode(file_get_contents($this->adminSdkPath), true);

            // FCM API endpoint (HTTP v1 API)
            $url = "https://fcm.googleapis.com/v1/projects/{$serviceAccount['project_id']}/messages:send";
            
            // Get access token for authentication
            $accessToken = $this->getAccessToken($serviceAccount);
            
            if (empty($accessToken)) {
                throw new Exception("Failed to get access token for Firebase");
            }
            
            // Convert all data values to strings
            $stringData = [];
            foreach ($data as $key => $value) {
                $stringData[$key] = (string)$value; // Convert all values to strings
            }
            
            // Prepare message payload for HTTP v1 API - DATA ONLY MESSAGE
            // Move notification content to data payload to prevent duplicate notifications
            $stringData['title'] = $notification['title'];
            $stringData['body'] = $notification['body'];
            $stringData['icon'] = $notification['icon'] ?? $this->myConfig['appIcon'];
            $stringData['badge'] = $this->myConfig['push_notification_badge'];
            $stringData['click_action'] = $notification['click_action'] ?? base_url();
            
            // Prepare message payload for HTTP v1 API - DATA ONLY MESSAGE
            $message = [
                'message' => [
                    'token' => $token,
                    'data' => $stringData,
                    'webpush' => [
                        'headers' => [
                            'Urgency' => 'high'
                        ],
                        'fcm_options' => [
                            'link' => $notification['click_action'] ?? base_url()
                        ]
                    ]
                ]
            ];
            
            // Set headers with OAuth access token
            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ];
            
            // Send request to FCM
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                throw new Exception(curl_error($ch));
            }
            
            curl_close($ch);
            
            // Check if request was successful
            if ($httpCode >= 200 && $httpCode < 300) {
                log_message('info', "FCM message sent successfully: " . $result);
                
                // Update last_used timestamp
                $this->db->table('fcm_tokens')
                    ->where('token', $token)
                    ->update(['last_used' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s')]);
                
                return true;
            } else {
                // Parse the JSON response
                $response = json_decode($result, true);
                
                // Check for the specific UNREGISTERED error
                if ($httpCode === 404 && 
                    isset($response['error']['details']) && 
                    is_array($response['error']['details']) && 
                    isset($response['error']['details'][0]['errorCode']) && 
                    $response['error']['details'][0]['errorCode'] === 'UNREGISTERED') {
                    
                    $deletionResult = $this->deleteToken($token);
                    log_message('info', "FCM token unregistered, deletion " . 
                        ($deletionResult ? "successful" : "failed") . ": " . $token);
                    return false; // Indicate message was not delivered
                } else {
                    // Handle other errors
                    log_message('error', "FCM request failed with HTTP code: $httpCode, Response: $result");
                    throw new Exception("FCM request failed with HTTP code: $httpCode, Response: $result");
                }
            }
        } catch (Exception $e) {
            log_message('error', "Error sending FCM message: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get OAuth access token for Firebase API
     *
     * @param array $serviceAccount Service account credentials
     * @return string|null Access token or null on failure
     */
    protected function getAccessToken($serviceAccount)
    {
        try {
            // JWT header
            $header = [
                'alg' => 'RS256',
                'typ' => 'JWT'
            ];
            
            // Current time
            $now = time();
            
            // JWT claim set
            $claim = [
                'iss' => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now
            ];
            
            // Encode JWT header and claim
            $base64Header = $this->base64UrlEncode(json_encode($header));
            $base64Claim = $this->base64UrlEncode(json_encode($claim));
            
            // Create JWT signature
            $dataToSign = $base64Header . '.' . $base64Claim;
            $privateKey = $serviceAccount['private_key'];
            
            $signature = '';
            openssl_sign($dataToSign, $signature, $privateKey, 'SHA256');
            $base64Signature = $this->base64UrlEncode($signature);
            
            // Create JWT
            $jwt = $dataToSign . '.' . $base64Signature;
            
            // Request access token
            log_message('debug', 'Attempting to get access token for Firebase');
            $ch = curl_init('https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                $data = json_decode($response, true);
                return $data['access_token'] ?? null;
            } else {
                log_message('error', "Failed to get access token: $response");
                return null;
            }
        } catch (Exception $e) {
            log_message('error', "Error getting access token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Base64 URL encode
     *
     * @param string $data Data to encode
     * @return string Base64 URL encoded string
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Get Firebase configuration for client-side use
     *
     * @return array Firebase configuration
     */
    public function getFirebaseConfig()
    {
        return $this->firebaseConfig;
    }

    /**
     * Check if a user has any FCM tokens registered
     *
     * @param int $userId The user ID
     * @return bool True if the user has tokens, false otherwise
     */
    public function userHasTokens($userId)
    {
        $tokens = $this->getUserTokens($userId);
        return !empty($tokens);
    }
    
    /**
     * Clean up 270 day old token from last_used
     * 
     */ 
    public function cleanupOldTokens(): string
    {
        $db = \Config\Database::connect();
    
        // Calculate threshold date: 270 days ago
        $thresholdDate = Time::now()->subDays(270)->toDateTimeString();
    
        $builder = $db->table('fcm_tokens');
    
        // Build the cleanup query
        $builder->groupStart()
                    ->where('last_used <', $thresholdDate)
                    ->orGroupStart()
                        ->where('last_used', null)
                        ->where('created_at <', $thresholdDate)
                    ->groupEnd()
                ->groupEnd();
        
        try {
            // Perform delete
            $deletedRows = $builder->delete();
        
            // Prepare response
            $response = [
                'success' => true,
                'status'  => 1,
                'msg'     => $deletedRows 
                    ? "Old FCM token cleanup completed. {$deletedRows} " . ($deletedRows === 1 ? 'token' : 'tokens') . " deleted."
                    : "Old FCM token cleanup completed. No tokens to delete."
            ];
        
            log_message('info', $response['msg']);
        
            return json_encode($response);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to cleanup FCM tokens: ' . $e->getMessage());
        
            $response = [
                'success' => false,
                'status'  => 0,
                'msg'     => 'Error during FCM token cleanup: ' . $e->getMessage(),
            ];
        
            return json_encode($response);
        }
    }
}
