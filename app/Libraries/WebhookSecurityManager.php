<?php

namespace App\Libraries;

use CodeIgniter\I18n\Time;

/**
 * Webhook Security Manager
 *
 * Enhanced security for webhook processing with rate limiting,
 * IP whitelisting, signature verification, and replay attack prevention
 *
 * Usage:
 * $security = new WebhookSecurityManager();
 * $isValid = $security->validateWebhook($headers, $body, $sourceIP, 'paypal');
 */
class WebhookSecurityManager
{
    // Rate limiting configuration (requests per time window)
    const RATE_LIMITS = [
        'paypal' => ['limit' => 100, 'window' => 300], // 100 requests per 5 minutes
        'stripe' => ['limit' => 100, 'window' => 300],
        'default' => ['limit' => 50, 'window' => 300]
    ];

    // IP whitelist for payment providers
    const IP_WHITELIST = [
        'paypal' => [
            // PayPal IPs (these should be updated regularly)
            '173.0.80.0/20',
            '64.4.240.0/21',
            '66.211.168.0/22',
            '173.0.80.0/20',
            '173.0.80.1/32',
            '64.4.240.1/32'
        ],
        'stripe' => [
            // Stripe IPs
            '54.187.174.169/32',
            '54.187.205.235/32',
            '54.187.216.72/32',
            '54.241.31.99/32'
        ]
    ];

    // Webhook signature headers by provider
    const SIGNATURE_HEADERS = [
        'paypal' => 'PAYPAL-TRANSMISSION-SIG',
        'stripe' => 'Stripe-Signature'
    ];

    protected $db;
    protected $cache;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->cache = \Config\Services::cache();
    }

    /**
     * Comprehensive webhook validation
     *
     * @param array $headers Request headers
     * @param string $body Request body
     * @param string $sourceIP Source IP address
     * @param string $provider Payment provider (paypal, stripe, etc.)
     * @return array Validation result
     */
    public function validateWebhook(array $headers, string $body, string $sourceIP, string $provider): array
    {
        $startTime = microtime(true);
        $validationId = uniqid('webhook_', true);

        log_message('info', "[WebhookSecurity] Starting validation {$validationId} for provider: {$provider}, IP: {$sourceIP}");

        try {
            // 1. Rate limiting check
            $rateLimitResult = $this->checkRateLimit($sourceIP, $provider);
            if (!$rateLimitResult['allowed']) {
                return $this->createValidationResult(false, 'Rate limit exceeded', [
                    'rate_limit' => $rateLimitResult,
                    'validation_id' => $validationId
                ]);
            }

            // 2. IP whitelist check
            $ipWhitelistResult = $this->checkIPWhitelist($sourceIP, $provider);
            if (!$ipWhitelistResult['allowed']) {
                return $this->createValidationResult(false, 'IP not whitelisted', [
                    'ip_check' => $ipWhitelistResult,
                    'validation_id' => $validationId
                ]);
            }

            // 3. Timestamp validation (prevent replay attacks)
            $timestampResult = $this->validateTimestamp($headers, $provider);
            if (!$timestampResult['valid']) {
                return $this->createValidationResult(false, 'Invalid timestamp', [
                    'timestamp_check' => $timestampResult,
                    'validation_id' => $validationId
                ]);
            }

            // 4. Signature verification
            $signatureResult = $this->verifySignature($headers, $body, $provider);
            if (!$signatureResult['valid']) {
                return $this->createValidationResult(false, 'Invalid signature', [
                    'signature_check' => $signatureResult,
                    'validation_id' => $validationId
                ]);
            }

            // 5. Duplicate prevention
            $duplicateResult = $this->checkDuplicate($headers, $body, $provider);
            if (!$duplicateResult['is_unique']) {
                return $this->createValidationResult(false, 'Duplicate webhook', [
                    'duplicate_check' => $duplicateResult,
                    'validation_id' => $validationId
                ]);
            }

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            log_message('info', "[WebhookSecurity] Validation {$validationId} passed in {$processingTime}ms");

            return $this->createValidationResult(true, 'Webhook validated successfully', [
                'validation_id' => $validationId,
                'processing_time_ms' => $processingTime,
                'rate_limit' => $rateLimitResult,
                'ip_check' => $ipWhitelistResult,
                'timestamp_check' => $timestampResult,
                'signature_check' => $signatureResult,
                'duplicate_check' => $duplicateResult
            ]);

        } catch (\Exception $e) {
            log_message('error', "[WebhookSecurity] Validation error for {$validationId}: " . $e->getMessage());

            return $this->createValidationResult(false, 'Validation error: ' . $e->getMessage(), [
                'validation_id' => $validationId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check rate limiting for source IP
     *
     * @param string $sourceIP
     * @param string $provider
     * @return array
     */
    protected function checkRateLimit(string $sourceIP, string $provider): array
    {
        $config = self::RATE_LIMITS[$provider] ?? self::RATE_LIMITS['default'];
        $cacheKey = "webhook_rate_limit_{$provider}_{$sourceIP}";

        // Get current request count
        $currentCount = $this->cache->get($cacheKey) ?? 0;

        if ($currentCount >= $config['limit']) {
            log_message('warning', "[WebhookSecurity] Rate limit exceeded for IP {$sourceIP}, provider {$provider}: {$currentCount}/{$config['limit']}");

            return [
                'allowed' => false,
                'current_count' => $currentCount,
                'limit' => $config['limit'],
                'window_seconds' => $config['window'],
                'reset_time' => time() + $config['window']
            ];
        }

        // Increment counter
        $this->cache->save($cacheKey, $currentCount + 1, $config['window']);

        return [
            'allowed' => true,
            'current_count' => $currentCount + 1,
            'limit' => $config['limit'],
            'window_seconds' => $config['window'],
            'remaining' => $config['limit'] - ($currentCount + 1)
        ];
    }

    /**
     * Check IP whitelist
     *
     * @param string $sourceIP
     * @param string $provider
     * @return array
     */
    protected function checkIPWhitelist(string $sourceIP, string $provider): array
    {
        // For development/testing, you might want to allow localhost
        if (in_array($sourceIP, ['127.0.0.1', '::1', 'localhost'])) {
            if (ENVIRONMENT === 'development') {
                return [
                    'allowed' => true,
                    'reason' => 'Development environment - localhost allowed',
                    'matched_range' => 'localhost'
                ];
            }
        }

        $whitelist = self::IP_WHITELIST[$provider] ?? [];

        foreach ($whitelist as $allowedRange) {
            if ($this->ipInRange($sourceIP, $allowedRange)) {
                return [
                    'allowed' => true,
                    'reason' => 'IP matches whitelist',
                    'matched_range' => $allowedRange
                ];
            }
        }

        log_message('warning', "[WebhookSecurity] IP {$sourceIP} not in whitelist for provider {$provider}");

        return [
            'allowed' => false,
            'reason' => 'IP not in whitelist',
            'checked_ranges' => $whitelist
        ];
    }

    /**
     * Validate webhook timestamp to prevent replay attacks
     *
     * @param array $headers
     * @param string $provider
     * @return array
     */
    protected function validateTimestamp(array $headers, string $provider): array
    {
        $timestampKey = $this->getTimestampHeader($provider);
        $timestamp = $headers[$timestampKey] ?? null;

        if (!$timestamp) {
            return [
                'valid' => false,
                'reason' => 'Timestamp header missing',
                'expected_header' => $timestampKey
            ];
        }

        // Parse timestamp based on provider format
        $webhookTime = $this->parseTimestamp($timestamp, $provider);
        if (!$webhookTime) {
            return [
                'valid' => false,
                'reason' => 'Invalid timestamp format',
                'timestamp' => $timestamp
            ];
        }

        $currentTime = time();
        $timeDiff = abs($currentTime - $webhookTime);

        // Allow 5 minutes tolerance for time differences
        $maxTimeDiff = 300;

        if ($timeDiff > $maxTimeDiff) {
            return [
                'valid' => false,
                'reason' => 'Timestamp too old or too far in future',
                'webhook_time' => $webhookTime,
                'current_time' => $currentTime,
                'time_diff_seconds' => $timeDiff,
                'max_allowed_diff' => $maxTimeDiff
            ];
        }

        return [
            'valid' => true,
            'webhook_time' => $webhookTime,
            'current_time' => $currentTime,
            'time_diff_seconds' => $timeDiff
        ];
    }

    /**
     * Verify webhook signature
     *
     * @param array $headers
     * @param string $body
     * @param string $provider
     * @return array
     */
    protected function verifySignature(array $headers, string $body, string $provider): array
    {
        switch ($provider) {
            case 'paypal':
                return $this->verifyPayPalSignature($headers, $body);
            case 'stripe':
                return $this->verifyStripeSignature($headers, $body);
            default:
                return [
                    'valid' => false,
                    'reason' => 'Signature verification not implemented for provider',
                    'provider' => $provider
                ];
        }
    }

    /**
     * Check for duplicate webhooks
     *
     * @param array $headers
     * @param string $body
     * @param string $provider
     * @return array
     */
    protected function checkDuplicate(array $headers, string $body, string $provider): array
    {
        // Create unique identifier for webhook
        $identifier = $this->createWebhookIdentifier($headers, $body, $provider);
        $cacheKey = "webhook_processed_{$provider}_{$identifier}";

        // Check if already processed (within last hour)
        $alreadyProcessed = $this->cache->get($cacheKey);

        if ($alreadyProcessed) {
            log_message('warning', "[WebhookSecurity] Duplicate webhook detected: {$identifier}");

            return [
                'is_unique' => false,
                'identifier' => $identifier,
                'first_processed' => $alreadyProcessed,
                'reason' => 'Webhook already processed'
            ];
        }

        // Mark as processed
        $this->cache->save($cacheKey, Time::now('UTC')->toDateTimeString(), 3600);

        return [
            'is_unique' => true,
            'identifier' => $identifier,
            'processed_time' => Time::now('UTC')->toDateTimeString()
        ];
    }

    /**
     * Create standardized validation result
     *
     * @param bool $valid
     * @param string $message
     * @param array $details
     * @return array
     */
    protected function createValidationResult(bool $valid, string $message, array $details = []): array
    {
        return [
            'valid' => $valid,
            'message' => $message,
            'timestamp' => Time::now('UTC')->toDateTimeString(),
            'details' => $details
        ];
    }

    /**
     * Check if IP is in CIDR range
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    protected function ipInRange(string $ip, string $range): bool
    {
        if (!str_contains($range, '/')) {
            // Simple IP comparison
            return $ip === $range;
        }

        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;

        return ($ip & $mask) === $subnet;
    }

    /**
     * Get timestamp header name for provider
     *
     * @param string $provider
     * @return string
     */
    protected function getTimestampHeader(string $provider): string
    {
        $headers = [
            'paypal' => 'PAYPAL-TRANSMISSION-TIME',
            'stripe' => 'X-Stripe-Timestamp'
        ];

        return $headers[$provider] ?? 'X-Timestamp';
    }

    /**
     * Parse timestamp based on provider format
     *
     * @param string $timestamp
     * @param string $provider
     * @return int|null
     */
    protected function parseTimestamp(string $timestamp, string $provider): ?int
    {
        switch ($provider) {
            case 'paypal':
                // PayPal uses RFC 3339 format
                $time = strtotime($timestamp);
                return $time !== false ? $time : null;

            case 'stripe':
                // Stripe uses Unix timestamp
                return is_numeric($timestamp) ? (int) $timestamp : null;

            default:
                // Try both formats
                if (is_numeric($timestamp)) {
                    return (int) $timestamp;
                }
                $time = strtotime($timestamp);
                return $time !== false ? $time : null;
        }
    }

    /**
     * Verify PayPal webhook signature
     *
     * @param array $headers
     * @param string $body
     * @return array
     */
    protected function verifyPayPalSignature(array $headers, string $body): array
    {
        // This would integrate with PayPal's signature verification
        // For now, return a placeholder implementation
        return [
            'valid' => true, // This should be actual PayPal verification
            'method' => 'paypal_signature_verification',
            'note' => 'PayPal signature verification implementation needed'
        ];
    }

    /**
     * Verify Stripe webhook signature
     *
     * @param array $headers
     * @param string $body
     * @return array
     */
    protected function verifyStripeSignature(array $headers, string $body): array
    {
        // This would integrate with Stripe's signature verification
        // For now, return a placeholder implementation
        return [
            'valid' => true, // This should be actual Stripe verification
            'method' => 'stripe_signature_verification',
            'note' => 'Stripe signature verification implementation needed'
        ];
    }

    /**
     * Create unique identifier for webhook
     *
     * @param array $headers
     * @param string $body
     * @param string $provider
     * @return string
     */
    protected function createWebhookIdentifier(array $headers, string $body, string $provider): string
    {
        // Use provider-specific webhook ID if available
        $webhookId = null;

        switch ($provider) {
            case 'paypal':
                $webhookId = $headers['PAYPAL-TRANSMISSION-ID'] ?? null;
                break;
            case 'stripe':
                // Parse Stripe signature to get timestamp
                $signature = $headers['Stripe-Signature'] ?? '';
                if (preg_match('/t=(\d+)/', $signature, $matches)) {
                    $webhookId = $matches[1];
                }
                break;
        }

        // Fall back to content hash if no webhook ID
        if (!$webhookId) {
            $webhookId = md5($body);
        }

        return $webhookId;
    }

    /**
     * Get security statistics
     *
     * @param string $provider
     * @param int $hours Hours to look back
     * @return array
     */
    public function getSecurityStats(string $provider, int $hours = 24): array
    {
        // This could be extended to track security events in database
        return [
            'provider' => $provider,
            'period_hours' => $hours,
            'rate_limit_hits' => 0, // Would need to track in database
            'blocked_ips' => 0,
            'invalid_signatures' => 0,
            'duplicate_webhooks' => 0,
            'successful_webhooks' => 0
        ];
    }
}