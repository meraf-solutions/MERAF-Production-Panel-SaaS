<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class NotificationReadFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $notificationId = $request->getGet('notification_id');

        if ($notificationId) {
            $notificationModel = new \App\Models\NotificationModel();

            $notification = $notificationModel->find($notificationId);

            if ($notification && isset($notification['user_id']) && $notification['user_id'] == auth()->id()) {
                // Mark notification as read
                $notificationModel->update($notificationId, ['is_read' => 1]);
            }
        }

        // Continue normal request flow
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
