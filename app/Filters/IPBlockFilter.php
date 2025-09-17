<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\IpBlockModel;

class IPBlockFilter implements FilterInterface
{
    /**
     * Check if the requesting IP is blocked
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $ipBlockModel = new IpBlockModel();
        $clientIP = $request->getIPAddress();

        // Check if the IP is in the block list
        $blockedIP = $ipBlockModel->where('ip_address', $clientIP)->first();

        if ($blockedIP) {
            // If IP is blocked, return 403 Forbidden response
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'result' => 'error',
                    'messages' => 'Access denied. Your IP has been temporarily blocked due to suspicious activity.',
                    'error_code' => FORBIDDEN_ERROR
                ]);
        }

        return $request;
    }

    /**
     * We don't have anything to do after the request
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing after the request
    }
}
