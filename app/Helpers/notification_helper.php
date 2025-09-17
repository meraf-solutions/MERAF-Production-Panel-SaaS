<?php

use App\Models\NotificationModel;

/***
How to use the notification system:

To add a notification for a specific user:
add_notification(
    "Your message here",
    "notification_type",
    "https://link-to-related-page.com",
    $user_id
);
*
To add a notification for all users:
add_notification(
    "Your message here",
    "notification_type",
    "https://link-to-related-page.com"
);

Here's a list of suggested notification types you can use in your application:

License-related:
'license_created'
'license_expiring'
'license_expired'
'license_renewed'
'license_activated'
'license_deactivated'
'license_limit_reached'
'license_validation'

Product-related:
'product_update_available'
'product_downloaded'
'product_added'
'product_removed'

Account-related:
'account_created'
'password_changed'
'email_changed'
'profile_updated'

Support-related:
'new_support_ticket'
'support_ticket_replied'
'support_ticket_resolved'

Security-related:
'unusual_login_attempt'
'password_reset_requested'
'two_factor_enabled'
'two_factor_disabled'
'ip_blocked'

User-related:
'user_deletion'

System-related:
'system_maintenance'
'system_update'
'service_outage'
'cronjob_error'
'email_sending_failed'

Usage:
add_notification(
    "Your subscription will expire in 7 days.",
    "subscription_expiring",
    base_url("subscriptions/view/{$subscription_id}"),
    $user_id
);
***/

if (!function_exists('add_notification')) {
    /**
     * Add a notification for a specific user or all users
     *
     * @param string $message The notification message
     * @param string $type The type of notification (e.g., 'subscription_expiring', 'license_expired')
     * @param string|null $link Optional link associated with the notification
     * @param int|null $user_id The ID of the specific user, or null for all users
     * @return bool True if the notification was added successfully, false otherwise
     */
    function add_notification($message, $type, $link = null, $user_id = null)
    {
        $notificationModel = new NotificationModel();

        if ($user_id === null) {
            // Add notification for all users
            $userModel = new \App\Models\UserModel();
            $users = $userModel->findAll();

            $success = true;
            foreach ($users as $user) {
                // Insert base notification (link will be updated afterward)
                $data = [
                    'user_id' => $user->id,
                    'type'    => $type,
                    'message' => $message,
                    'link'    => null, // temporarily null
                ];

                if ($notificationModel->insert($data)) {
                    $notificationId = $notificationModel->getInsertID();

                    // Add ?notification_id= or &notification_id=
                    $finalLink = $link;
                    if ($link !== null) {
                        $separator = (parse_url($link, PHP_URL_QUERY)) ? '&' : '?';
                        $finalLink = $link . $separator . 'notification_id=' . $notificationId;
                    }

                    // Update notification with final link
                    $notificationModel->update($notificationId, ['link' => $finalLink]);

                    // Send personalized push notification
                    try {
                        $firebaseService = new \App\Libraries\FirebaseService();
                        $firebaseService->sendNotification($user->id, $message, $type, $finalLink);
                    } catch (\Exception $e) {
                        log_message('error', 'FCM to user ' . $user->id . ' failed: ' . $e->getMessage());
                    }

                } else {
                    $success = false;
                }
            }

            return $success;
        } else {
            // Add notification for a specific user
            $data = [
                'user_id' => $user_id,
                'type'    => $type,
                'message' => $message,
                'link'    => null, // temporarily null
            ];

            if ($notificationModel->insert($data)) {
                $notificationId = $notificationModel->getInsertID();

                $finalLink = $link;
                if ($link !== null) {
                    $separator = (parse_url($link, PHP_URL_QUERY)) ? '&' : '?';
                    $finalLink = $link . $separator . 'notification_id=' . $notificationId;
                }

                $notificationModel->update($notificationId, ['link' => $finalLink]);

                try {
                    $firebaseService = new \App\Libraries\FirebaseService();
                    $firebaseService->sendNotification($user_id, $message, $type, $finalLink);
                } catch (\Exception $e) {
                    log_message('error', 'FCM to user ' . $user_id . ' failed: ' . $e->getMessage());
                }

                return true;
            }

            return false;
        }
    }
}
