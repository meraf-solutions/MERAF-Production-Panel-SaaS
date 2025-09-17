<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class APIThrottle implements FilterInterface
{
    /**
     * Enterprise-grade tiered rate limiting for SaaS API endpoints
     * Implements different rate limits based on endpoint sensitivity
     *
     * @param list<string>|null $arguments
     *
     * @return ResponseInterface|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Load security helper for secure IP hashing
        helper('security');

        // Get the current path and method
        $path = $request->getPath();
        $method = $request->getMethod();

        // Initialize the Throttler service
        $throttler = Services::throttler();

        // Get rate limit configuration for this endpoint
        $limit = $this->getRateLimitForEndpoint($path, $method);

        // Skip rate limiting if endpoint is excluded
        if ($limit === null) {
            return;
        }

        // Create secure hash of IP address with daily rotating salt
        $ipHash = secure_hash_ip($request->getIPAddress());

        // Check rate limit
        if ($throttler->check($ipHash, $limit['requests'], $limit['period']) === false) {
            // Log rate limit violation
            log_message('warning', "Rate limit exceeded for IP: {$request->getIPAddress()}, Path: {$path}, Limit: {$limit['requests']}/{$limit['period']}s");

            // Return standardized JSON error response
            return Services::response()
                ->setStatusCode(429)
                ->setJSON([
                    'result' => 'error',
                    'message' => 'Rate limit exceeded. Please try again later.',
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'retry_after' => 60
                ])
                ->setHeader('Retry-After', '60')
                ->setHeader('X-RateLimit-Limit', (string)$limit['requests'])
                ->setHeader('X-RateLimit-Remaining', '0')
                ->setHeader('X-RateLimit-Reset', (string)(time() + $limit['period']));
        }
    }

    /**
     * Get rate limit configuration for a specific endpoint
     *
     * @param string $path
     * @param string $method
     * @return array|null Rate limit config or null if excluded
     */
    private function getRateLimitForEndpoint(string $path, string $method): ?array
    {
        // Excluded endpoints (no rate limiting)
        $excludedPaths = [
            'api/product/with-variations',
            'api/product/all',
            'api/product/current-versions',
            'api/variation/all',
            'api/license/generate',
            'api/info/products'
        ];

        // Check if path is excluded
        foreach ($excludedPaths as $excludedPath) {
            if (strpos($path, $excludedPath) !== false) {
                return null;
            }
        }

        // Authentication endpoints (strictest limits)
        $authenticationEndpoints = [
            'api/license/validate',
            'api/license/create',
            'api/license/register',
            'api/license/unregister',
            'api/license/activate',
            'api/license/deactivate'
        ];

        foreach ($authenticationEndpoints as $authEndpoint) {
            if (strpos($path, $authEndpoint) !== false) {
                return [
                    'requests' => 10,
                    'period' => MINUTE,
                    'category' => 'authentication'
                ];
            }
        }

        // Management endpoints (moderate limits)
        $managementEndpoints = [
            'api/license/edit',
            'api/license/delete',
            'api/license/manage',
            'api/license/update',
            'api/license/export',
            'api/license/bulk'
        ];

        foreach ($managementEndpoints as $mgmtEndpoint) {
            if (strpos($path, $mgmtEndpoint) !== false) {
                return [
                    'requests' => 30,
                    'period' => MINUTE,
                    'category' => 'management'
                ];
            }
        }

        // Information endpoints (relaxed limits)
        $informationEndpoints = [
            'api/license/all',
            'api/license/data',
            'api/license/logs',
            'api/license/subscribers',
            'api/info/',
            'api/product/'
        ];

        foreach ($informationEndpoints as $infoEndpoint) {
            if (strpos($path, $infoEndpoint) !== false) {
                return [
                    'requests' => 60,
                    'period' => MINUTE,
                    'category' => 'information'
                ];
            }
        }

        // Default rate limit for API endpoints not specifically categorized
        if (strpos($path, 'api/') !== false) {
            return [
                'requests' => 30,
                'period' => MINUTE,
                'category' => 'default'
            ];
        }

        // Non-API requests (web interface) - higher limits
        return [
            'requests' => 120,
            'period' => MINUTE,
            'category' => 'web'
        ];
    }

    /**
     * Add additional security headers after request processing
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add security context for API requests
        if (strpos($request->getPath(), 'api/') !== false) {
            // Ensure JSON responses for API endpoints
            if ($response->getStatusCode() === 429) {
                $response->setHeader('Content-Type', 'application/json');
            }

            // Add API-specific headers
            $response->setHeader('X-API-Version', '1.0');
            $response->setHeader('X-Content-Type-Options', 'nosniff');
        }
    }

    /**
     * Get current rate limit status for debugging/monitoring
     *
     * @param RequestInterface $request
     * @return array
     */
    public function getRateLimitStatus(RequestInterface $request): array
    {
        helper('security');

        $path = $request->getPath();
        $ipHash = secure_hash_ip($request->getIPAddress());
        $limit = $this->getRateLimitForEndpoint($path, $request->getMethod());

        if ($limit === null) {
            return ['status' => 'excluded', 'path' => $path];
        }

        $throttler = Services::throttler();

        // Test if rate limit is active by doing a check with 0 cost
        $isActive = $throttler->check($ipHash, $limit['requests'], $limit['period'], 0);

        return [
            'status' => 'active',
            'path' => $path,
            'category' => $limit['category'],
            'limit' => $limit['requests'],
            'available' => $isActive,
            'reset_time' => time() + $limit['period']
        ];
    }
}