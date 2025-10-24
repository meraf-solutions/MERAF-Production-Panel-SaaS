<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SecurityHeaders implements FilterInterface
{
    /**
     * Add comprehensive security headers to all responses
     * Implements defense-in-depth security practices for SaaS application
     *
     * @param RequestInterface $request
     * @param array|null $arguments
     * @return void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // No action needed before request
    }

    /**
     * Add security headers to response
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Prevent MIME type sniffing
        $response->setHeader('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking attacks
        $response->setHeader('X-Frame-Options', 'DENY');

        // Enable XSS protection (legacy, but still useful for older browsers)
        $response->setHeader('X-XSS-Protection', '1; mode=block');

        // Enforce HTTPS (only add if request is already HTTPS)
        if ($request->isSecure()) {
            $response->setHeader(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content Security Policy - restrictive but functional for SaaS
        $cspPolicy = implode('; ', [
            "default-src 'self'",
            // Allow Firebase SDK, Cloudflare analytics, and other CDN scripts
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.gstatic.com https://www.googleapis.com https://static.cloudflareinsights.com https://cdn.jsdelivr.net https://unpkg.com https://cdn.datatables.net https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdn.datatables.net",
            "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https:",
            // Explicitly allow Firebase API endpoints and Cloudflare analytics
            "connect-src 'self' https://fcm.googleapis.com https://firebaseinstallations.googleapis.com https://*.googleapis.com https://cloudflareinsights.com https:",
            "frame-src 'none'",
            "object-src 'none'",
            "media-src 'self'",
            // Allow service workers from same origin for PWA and Firebase background messaging
            "worker-src 'self'"
        ]);
        $response->setHeader('Content-Security-Policy', $cspPolicy);

        // Control referrer information
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Prevent DNS prefetching for enhanced privacy
        $response->setHeader('X-DNS-Prefetch-Control', 'off');

        // Disable FLoC (Google's Federated Learning of Cohorts)
        $response->setHeader('Permissions-Policy', 'interest-cohort=()');

        // Remove server information disclosure
        $response->removeHeader('Server');
        $response->removeHeader('X-Powered-By');

        // Set secure cache control for sensitive pages
        if ($this->isSensitivePage($request)) {
            $response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
        }

        // Add SaaS-specific security headers
        $this->addSaasSecurityHeaders($request, $response);
    }

    /**
     * Determine if the current page should have strict cache headers
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isSensitivePage(RequestInterface $request): bool
    {
        $path = $request->getPath();

        $sensitivePaths = [
            'api/',
            'dashboard/',
            'license-manager/',
            'auth/',
            'profile/',
            'admin/',
            'subscription/',
            'billing/',
            'settings/'
        ];

        foreach ($sensitivePaths as $sensitivePath) {
            if (strpos($path, $sensitivePath) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add SaaS-specific security headers
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    private function addSaasSecurityHeaders(RequestInterface $request, ResponseInterface $response): void
    {
        // Add cross-origin policies for API security
        if (strpos($request->getPath(), 'api/') !== false) {
            $response->setHeader('Cross-Origin-Embedder-Policy', 'require-corp');
            $response->setHeader('Cross-Origin-Opener-Policy', 'same-origin');
            $response->setHeader('Cross-Origin-Resource-Policy', 'same-origin');
        }

        // Add CORP for sensitive admin areas
        if (strpos($request->getPath(), 'admin/') !== false) {
            $response->setHeader('Cross-Origin-Resource-Policy', 'same-origin');
        }

        // Feature policy for enhanced privacy
        $featurePolicy = implode(', ', [
            'camera=()',
            'microphone=()',
            'geolocation=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'speaker=()',
            'vibrate=()',
            'fullscreen=(self)',
            'sync-xhr=()'
        ]);
        $response->setHeader('Feature-Policy', $featurePolicy);

        // Add security headers specific to multi-tenant architecture
        if ($this->isUserSpecificContent($request)) {
            $response->setHeader('Vary', 'User-API-Key, Authorization, Accept-Encoding');
        }
    }

    /**
     * Check if content is user-specific and should not be cached globally
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isUserSpecificContent(RequestInterface $request): bool
    {
        $userSpecificPaths = [
            'api/',
            'dashboard/',
            'profile/',
            'license-manager/',
            'subscription/',
            'billing/'
        ];

        $path = $request->getPath();
        foreach ($userSpecificPaths as $userPath) {
            if (strpos($path, $userPath) !== false) {
                return true;
            }
        }

        return false;
    }
}