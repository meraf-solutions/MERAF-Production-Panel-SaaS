<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use App\Controllers\Home;
use CodeIgniter\API\ResponseTrait;

class NotificationController extends Home
{
    use ResponseTrait;

    protected $notificationModel;

    public function __construct()
    {
        parent::__construct();
        $this->notificationModel = new NotificationModel();
    }

    public function getUnreadNotifications()
    {
        $this->checkIfLoggedIn(); // Check if user is logged before to proceed
        
        $userId = $this->userID;
        $page = $this->request->getGet('page', FILTER_VALIDATE_INT) ?? 1;
        $limit = 5; // Number of notifications per page
        $offset = ($page - 1) * $limit;

        // log_message('debug', "[NotificationController] getUnreadNotifications called for user: $userId, page: $page, limit: $limit, offset: $offset");

        $notifications = $this->notificationModel->getUnreadNotifications($userId, $limit, $offset);
        $unreadCount = $this->notificationModel->getUnreadCount($userId);

        // log_message('debug', "[NotificationController] Retrieved " . count($notifications) . " notifications, unread count: $unreadCount");

        return $this->respond([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    public function markAsRead($id = null)
    {
        $this->checkIfLoggedIn(); // Check if user is logged before to proceed
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}

        if(!$id || !is_numeric($id)) {
            return $this->respond(['success' => false, 'message' => 'Invalid notification ID'], 400);    
        }

        $success = $this->notificationModel->markAsRead($id);
        return $this->respond(['success' => $success]);
    }

    public function markAllAsRead()
    {
        $this->checkIfLoggedIn(); // Check if user is logged before to proceed

        // log_message('debug', '[NotificationController] markAllAsRead method called');

        if ($this->request->getMethod() !== 'POST') {
            log_message('error', 'Invalid method for markAllAsRead: ' . $this->request->getMethod());
            return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
        }

        $userId = $this->userID;
        // log_message('debug', '[NotificationController] Attempting to mark all notifications as read for user: ' . $userId);

        $success = $this->notificationModel->markAllAsRead($userId);
        
        if ($success) {
            log_message('info', '[NotificationController] Successfully marked all notifications as read for user: ' . $userId);
            return $this->respond(['success' => true, 'message' => 'All notifications marked as read']);
        } else {
            log_message('error', 'Failed to mark all notifications as read for user: ' . $userId);
            return $this->respond(['success' => false, 'message' => 'Failed to mark all notifications as read'], 500);
        }
    }

    /**
     * Register or update FCM token
     */
    public function registerToken()
    {
        $this->checkIfLoggedIn();
        
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
        }
        
        $json = $this->request->getJSON();
        $token = $json->token ?? '';
        $device = $json->device ?? '';
        
        if (empty($token)) {
            return $this->respond(['success' => false, 'message' => 'Token is required'], 400);
        }
        
        $firebaseService = new \App\Libraries\FirebaseService();
        $success = $firebaseService->saveToken($this->userID, $token, $device);
        
        if ($success) {
            return $this->respond(['success' => true, 'message' => 'Token registered successfully']);
        } else {
            return $this->respond(['success' => false, 'message' => 'Failed to register token'], 500);
        }
    }

    /**
     * Delete FCM token
     */
    public function deleteToken()
    {
        $this->checkIfLoggedIn();
        
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
        }
        
        $json = $this->request->getJSON();
        $token = $json->token ?? '';
        
        if (empty($token)) {
            return $this->respond(['success' => false, 'message' => 'Token is required'], 400);
        }
        
        $firebaseService = new \App\Libraries\FirebaseService();
        $success = $firebaseService->deleteToken($token);
        
        if ($success) {
            return $this->respond(['success' => true, 'message' => 'Token deleted successfully']);
        } else {
            return $this->respond(['success' => false, 'message' => 'Failed to delete token'], 500);
        }
    }

    /**
     * Send a test notification
     */
    public function testPushNotification()
    {
        $this->checkIfLoggedIn();
        
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
        }
        
        // Only allow admins to send test notifications
        if (!$this->userAcctDetails->inGroup('admin')) {
            return $this->respond(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        helper('notification');
        
        // Send a test notification to the current user
        $result = add_notification(
            "This is a test push notification",
            "system_test",
            base_url('dashboard'),
            $this->userID
        );
        
        return $this->respond([
            'success' => $result,
            'message' => $result ? 'Test notification sent successfully' : 'Failed to send test notification'
        ]);
    }

    /**
     * Check if a device is registered for the current user
     */
    public function checkDeviceRegistration()
    {
        $this->checkIfLoggedIn();
        
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
        }
        
        $json = $this->request->getJSON();
        $deviceId = $json->deviceId ?? '';
        $token = $json->token ?? '';
        
        if (empty($deviceId) || empty($token)) {
            return $this->respond(['isRegistered' => false], 400);
        }
        
        // Check if this device is registered for this user using the device_id column
        $db = \Config\Database::connect();
        $result = $db->table('fcm_tokens')
            ->where('user_id', $this->userID)
            ->where('device_id', $deviceId)
            ->get()
            ->getRowArray();
        
        return $this->respond(['isRegistered' => !empty($result)]);
    }

    /**
     * Save device ID in session
     * to retrieve:
     * $session = session();
     * $deviceId = $session->get('deviceId');
     */
    public function saveToSessionDeviceId()
    {
        $this->checkIfLoggedIn();
        
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
        }
        
        $json = $this->request->getJSON();
        $deviceId = $json->deviceId ?? null;
        log_message('debug', '[Notification Controller] Received current device ID:' .  $deviceId);
        log_message('debug', '[Notification Controller] Saving the device ID to the current session and cookie...');

        if ($deviceId) {
            $session = session();
        
            // Save the current device ID in the session
            if (!$session->has('deviceId')) {
                $session->set('deviceId', $deviceId);
                log_message('debug', 'Device ID saved in the current session: ' . $deviceId);
            } else {
                log_message('debug', 'Existing device ID from the session: ' . $session->get('deviceId'));
            }
        
            // Save the device ID in the cookie named device_id for 365 days if not yet set
            $cookieName = 'device_id';
            $existingCookie = $this->request->getCookie($cookieName);
        
            if (!$existingCookie) {
                $this->response->setCookie(
                    $cookieName,
                    $deviceId,
                    [
                        'expire'   => 60 * 60 * 24 * 365, // 365 days
                        'httponly' => true,               // For security
                        'secure'   => $this->request->isSecure(), // Only send over HTTPS
                        'path'     => '/',                // Make available for whole domain
                    ]
                );
                log_message('debug', 'Device ID saved in cookie: ' . $deviceId);
            } else {
                log_message('debug', 'Existing device ID from cookie: ' . $existingCookie);
            }
        
            return $this->response->setJSON(['success' => true]);
        }
    }
}
