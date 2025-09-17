<?php

namespace App\Libraries;

use CodeIgniter\I18n\Time;

/**
 * Transaction ID Manager
 *
 * Standardizes transaction ID generation and parsing across all payment methods
 * Ensures consistent format and provides validation utilities
 *
 * Format: [PREFIX]-[METHOD]-[TIMESTAMP]-[UNIQUE_ID]
 * Examples:
 * - TXN-PAYPAL-1642680000-ABC123
 * - TXN-OFFLINE-1642680000-DEF456
 * - TXN-TRIAL-1642680000-GHI789
 */
class TransactionIdManager
{
    // Standard prefixes for different transaction types
    const PREFIX_SUBSCRIPTION = 'SUB';
    const PREFIX_PAYMENT = 'PAY';
    const PREFIX_INVOICE = 'INV';
    const PREFIX_REFUND = 'REF';

    // Payment method identifiers
    const METHOD_PAYPAL = 'PAYPAL';
    const METHOD_OFFLINE = 'OFFLINE';
    const METHOD_TRIAL = 'TRIAL';

    // Transaction ID pattern for validation
    const PATTERN = '/^([A-Z]{3})-([A-Z]+)-(\d{10})-([A-Z0-9]{6,12})$/';

    /**
     * Generate standardized transaction ID
     *
     * @param string $prefix Transaction prefix (SUB, PAY, INV, REF)
     * @param string $method Payment method (PAYPAL, OFFLINE, TRIAL)
     * @param bool $isPending Whether this is a pending transaction
     * @return string Standardized transaction ID
     */
    public static function generate(string $prefix, string $method, bool $isPending = false): string
    {
        $timestamp = Time::now('UTC')->getTimestamp();
        $uniqueId = strtoupper(substr(uniqid(), -8));

        if ($isPending) {
            $uniqueId = 'PENDING-' . substr($uniqueId, 0, 6);
        }

        return "{$prefix}-{$method}-{$timestamp}-{$uniqueId}";
    }

    /**
     * Generate subscription transaction ID
     *
     * @param string $method Payment method
     * @param bool $isPending Whether pending payment
     * @return string
     */
    public static function generateSubscription(string $method, bool $isPending = false): string
    {
        return self::generate(self::PREFIX_SUBSCRIPTION, strtoupper($method), $isPending);
    }

    /**
     * Generate payment transaction ID
     *
     * @param string $method Payment method
     * @param bool $isPending Whether pending payment
     * @return string
     */
    public static function generatePayment(string $method, bool $isPending = false): string
    {
        return self::generate(self::PREFIX_PAYMENT, strtoupper($method), $isPending);
    }

    /**
     * Generate invoice transaction ID
     *
     * @param string $method Payment method
     * @return string
     */
    public static function generateInvoice(string $method): string
    {
        return self::generate(self::PREFIX_INVOICE, strtoupper($method), false);
    }

    /**
     * Generate refund transaction ID
     *
     * @param string $method Payment method
     * @return string
     */
    public static function generateRefund(string $method): string
    {
        return self::generate(self::PREFIX_REFUND, strtoupper($method), false);
    }

    /**
     * Parse transaction ID into components
     *
     * @param string $transactionId
     * @return array|null Array with keys: prefix, method, timestamp, unique_id, is_pending
     */
    public static function parse(string $transactionId): ?array
    {
        if (!self::isValid($transactionId)) {
            return null;
        }

        preg_match(self::PATTERN, $transactionId, $matches);

        if (count($matches) !== 5) {
            return null;
        }

        [, $prefix, $method, $timestamp, $uniqueId] = $matches;

        return [
            'prefix' => $prefix,
            'method' => $method,
            'timestamp' => (int) $timestamp,
            'unique_id' => $uniqueId,
            'is_pending' => strpos($uniqueId, 'PENDING') === 0,
            'created_at' => date('Y-m-d H:i:s', (int) $timestamp)
        ];
    }

    /**
     * Validate transaction ID format
     *
     * @param string $transactionId
     * @return bool
     */
    public static function isValid(string $transactionId): bool
    {
        return preg_match(self::PATTERN, $transactionId) === 1;
    }

    /**
     * Check if transaction ID is pending
     *
     * @param string $transactionId
     * @return bool
     */
    public static function isPending(string $transactionId): bool
    {
        $parsed = self::parse($transactionId);
        return $parsed ? $parsed['is_pending'] : false;
    }

    /**
     * Convert pending transaction ID to completed
     *
     * @param string $pendingTransactionId
     * @param string $externalTransactionId Optional external ID (e.g., PayPal transaction ID)
     * @return string
     */
    public static function completePending(string $pendingTransactionId, string $externalTransactionId = null): string
    {
        $parsed = self::parse($pendingTransactionId);

        if (!$parsed || !$parsed['is_pending']) {
            // If not pending or invalid, generate new completed ID
            return self::generate(
                $parsed['prefix'] ?? self::PREFIX_PAYMENT,
                $parsed['method'] ?? self::METHOD_OFFLINE,
                false
            );
        }

        // Create new unique ID, optionally incorporating external ID
        $newUniqueId = $externalTransactionId ?
            substr(strtoupper(preg_replace('/[^A-Z0-9]/', '', $externalTransactionId)), 0, 12) :
            strtoupper(substr(uniqid(), -8));

        return "{$parsed['prefix']}-{$parsed['method']}-{$parsed['timestamp']}-{$newUniqueId}";
    }

    /**
     * Get transaction method from ID
     *
     * @param string $transactionId
     * @return string|null
     */
    public static function getMethod(string $transactionId): ?string
    {
        $parsed = self::parse($transactionId);
        return $parsed ? $parsed['method'] : null;
    }

    /**
     * Get transaction type from ID
     *
     * @param string $transactionId
     * @return string|null
     */
    public static function getType(string $transactionId): ?string
    {
        $parsed = self::parse($transactionId);
        return $parsed ? $parsed['prefix'] : null;
    }

    /**
     * Get transaction age in minutes
     *
     * @param string $transactionId
     * @return int|null
     */
    public static function getAgeInMinutes(string $transactionId): ?int
    {
        $parsed = self::parse($transactionId);

        if (!$parsed) {
            return null;
        }

        $now = Time::now('UTC')->getTimestamp();
        return intval(($now - $parsed['timestamp']) / 60);
    }

    /**
     * Check if transaction ID is expired (older than specified minutes)
     *
     * @param string $transactionId
     * @param int $expiryMinutes
     * @return bool
     */
    public static function isExpired(string $transactionId, int $expiryMinutes = 60): bool
    {
        $age = self::getAgeInMinutes($transactionId);
        return $age !== null && $age > $expiryMinutes;
    }

    /**
     * Generate transaction reference for subscriptions
     * Format: [METHOD_PREFIX]-[TIMESTAMP]-[SHORT_UNIQUE]
     *
     * @param string $method
     * @return string
     */
    public static function generateSubscriptionReference(string $method): string
    {
        $methodPrefix = substr(strtoupper($method), 0, 1);
        $timestamp = Time::now('UTC')->getTimestamp();
        $shortUnique = strtoupper(substr(uniqid(), -6));

        return "{$methodPrefix}-{$timestamp}-{$shortUnique}";
    }

    /**
     * Migrate legacy transaction IDs to new format
     * Handles existing transaction IDs that don't follow the standard format
     *
     * @param string $legacyId
     * @param string $method
     * @param string $prefix
     * @return string
     */
    public static function migrateLegacy(string $legacyId, string $method, string $prefix = self::PREFIX_PAYMENT): string
    {
        // If already in correct format, return as-is
        if (self::isValid($legacyId)) {
            return $legacyId;
        }

        // Extract any useful information from legacy ID
        $timestamp = Time::now('UTC')->getTimestamp();

        // Try to extract timestamp from legacy ID if possible
        if (preg_match('/(\d{10})/', $legacyId, $matches)) {
            $extractedTimestamp = (int) $matches[1];
            // Only use if it's a reasonable timestamp (after 2020)
            if ($extractedTimestamp > 1577836800) {
                $timestamp = $extractedTimestamp;
            }
        }

        // Create a unique suffix based on legacy ID
        $uniqueSuffix = strtoupper(substr(md5($legacyId), 0, 8));

        return "{$prefix}-{$method}-{$timestamp}-{$uniqueSuffix}";
    }

    /**
     * Get all valid transaction prefixes
     *
     * @return array
     */
    public static function getValidPrefixes(): array
    {
        return [
            self::PREFIX_SUBSCRIPTION,
            self::PREFIX_PAYMENT,
            self::PREFIX_INVOICE,
            self::PREFIX_REFUND
        ];
    }

    /**
     * Get all valid payment methods
     *
     * @return array
     */
    public static function getValidMethods(): array
    {
        return [
            self::METHOD_PAYPAL,
            self::METHOD_OFFLINE,
            self::METHOD_TRIAL
        ];
    }

    /**
     * Generate batch of transaction IDs for testing
     *
     * @param int $count
     * @param string $method
     * @return array
     */
    public static function generateBatch(int $count, string $method = self::METHOD_OFFLINE): array
    {
        $ids = [];
        for ($i = 0; $i < $count; $i++) {
            $ids[] = self::generatePayment($method);
            // Small delay to ensure unique timestamps
            usleep(1000);
        }
        return $ids;
    }
}