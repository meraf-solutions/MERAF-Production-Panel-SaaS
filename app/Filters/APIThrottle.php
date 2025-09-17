<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class APIThrottle implements FilterInterface
{
    /**
     * This is a demo implementation of using the Throttler class
     * to implement rate limiting for your application.
     *
     * @param list<string>|null $arguments
     *
     * @return ResponseInterface|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Define the paths to exclude
        $excludedPaths = [
            'api/product/with-variations',
            'api/product/all',
            'api/product/current-versions',
            'api/variation/all',
            'api/license/generate'
        ];

        // Get the current path
        $path = $request->getPath();

        // Initialize the Throttler service
        $throttler = Services::throttler();

        // Restrict an IP address to no more than 10 requests per minute
        if (!in_array($path, $excludedPaths)) {
            if ($throttler->check(md5($request->getIPAddress()), 15, MINUTE) === false) {
                return Services::response()->setStatusCode(429);
            }
        }
    }

    /**
     * We don't have anything to do here.
     *
     * @param list<string>|null $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing required
    }
}