<?php

/**
 * Subscription Helper Functions
 *
 * This helper provides utility functions for consistent subscription handling
 * across the MERAF Production Panel system.
 *
 * @package    MERAF Production Panel
 * @subpackage Helpers
 * @category   Subscription Management
 * @author     MERAF Solutions
 * @created    2025-10-19
 */

if (!function_exists('normalizeBillingInterval')) {
    /**
     * Normalize billing interval to consistent plural format
     *
     * Ensures all billing intervals use the same format regardless of input source.
     * The database and all internal operations use plural format.
     *
     * @param string $interval Input interval (can be singular or plural)
     * @return string Normalized interval in plural format
     *
     * @example
     * normalizeBillingInterval('year')   => 'years'
     * normalizeBillingInterval('years')  => 'years'
     * normalizeBillingInterval('month')  => 'months'
     * normalizeBillingInterval('week')   => 'weeks'
     */
    function normalizeBillingInterval(string $interval): string
    {
        $interval = strtolower(trim($interval));

        // Map of all possible inputs to normalized plural format
        $normalizationMap = [
            // Singular forms
            'year'    => 'years',
            'month'   => 'months',
            'week'    => 'weeks',
            'day'     => 'days',

            // Already plural (return as-is)
            'years'   => 'years',
            'months'  => 'months',
            'weeks'   => 'weeks',
            'days'    => 'days',

            // Special cases
            'onetime' => 'onetime',
            'lifetime'=> 'lifetime',
        ];

        return $normalizationMap[$interval] ?? 'onetime';
    }
}

if (!function_exists('billingIntervalToSingular')) {
    /**
     * Convert billing interval to singular form for DateTime modifications
     *
     * DateTime::modify() requires singular form (e.g., '+1 year', not '+1 years')
     *
     * @param string $interval Billing interval (plural format)
     * @return string Singular form for DateTime
     *
     * @example
     * billingIntervalToSingular('years')  => 'year'
     * billingIntervalToSingular('months') => 'month'
     */
    function billingIntervalToSingular(string $interval): string
    {
        $interval = normalizeBillingInterval($interval);

        $singularMap = [
            'years'   => 'year',
            'months'  => 'month',
            'weeks'   => 'week',
            'days'    => 'day',
            'onetime' => '', // No modification for one-time
            'lifetime'=> '', // No modification for lifetime
        ];

        return $singularMap[$interval] ?? '';
    }
}

if (!function_exists('billingIntervalToModifyString')) {
    /**
     * Convert billing interval and length to DateTime modify string
     *
     * Creates a string suitable for DateTime::modify() to add/subtract time
     *
     * @param int $length Number of intervals (1, 2, 3, etc.)
     * @param string $interval Billing interval (years, months, etc.)
     * @return string DateTime modify string (e.g., '+1 year', '+3 months')
     *
     * @example
     * billingIntervalToModifyString(1, 'years')  => '+1 year'
     * billingIntervalToModifyString(3, 'months') => '+3 months'
     * billingIntervalToModifyString(7, 'days')   => '+7 days'
     */
    function billingIntervalToModifyString(int $length, string $interval): string
    {
        $singularInterval = billingIntervalToSingular($interval);

        if (empty($singularInterval)) {
            return ''; // No modification for onetime/lifetime
        }

        return '+' . $length . ' ' . $singularInterval;
    }
}

if (!function_exists('calculateSubscriptionExpiry')) {
    /**
     * Calculate subscription expiry date based on billing period
     *
     * Handles renewals properly by extending from current expiry (not renewal date)
     * Handles expired licenses by starting from current date
     *
     * @param string|null $currentExpiry Current expiry date (UTC, Y-m-d H:i:s format) or null for new subscriptions
     * @param int $billingLength Number of billing intervals
     * @param string $billingInterval Billing interval (years, months, weeks, days)
     * @param bool $forceFromNow Force calculation from current date (for expired licenses)
     * @return string New expiry date (UTC, Y-m-d H:i:s format)
     *
     * @throws Exception If date parsing fails
     *
     * @example
     * // New subscription: no current expiry
     * calculateSubscriptionExpiry(null, 1, 'years')
     * => '2026-10-19 12:00:00' (1 year from now)
     *
     * // Renewal: extend from current expiry
     * calculateSubscriptionExpiry('2026-10-19 12:00:00', 1, 'years')
     * => '2027-10-19 12:00:00' (1 year from current expiry)
     *
     * // Expired license renewal
     * calculateSubscriptionExpiry('2024-10-19 12:00:00', 1, 'years', true)
     * => '2026-10-19 12:00:00' (1 year from now, not from expired date)
     */
    function calculateSubscriptionExpiry(?string $currentExpiry, int $billingLength, string $billingInterval, bool $forceFromNow = false): string
    {
        $utcTimezone = new DateTimeZone('UTC');

        // Normalize the interval
        $normalizedInterval = normalizeBillingInterval($billingInterval);

        // Get the modify string
        $modifyString = billingIntervalToModifyString($billingLength, $normalizedInterval);

        if (empty($modifyString)) {
            // Lifetime or onetime - no expiry (return far future date)
            $farFuture = new DateTime('+100 years', $utcTimezone);
            return $farFuture->format('Y-m-d H:i:s');
        }

        // Determine the base date
        if ($currentExpiry && !$forceFromNow) {
            // Use current expiry as base (for active subscriptions)
            try {
                $baseDate = new DateTime($currentExpiry, $utcTimezone);

                // Check if already expired
                $now = new DateTime('now', $utcTimezone);
                if ($baseDate < $now) {
                    // Expired - start from now instead
                    log_message('info', 'calculateSubscriptionExpiry: License expired (' . $currentExpiry . '), calculating from current date instead');
                    $baseDate = $now;
                }
            } catch (Exception $e) {
                // Invalid date format - fall back to now
                log_message('error', 'calculateSubscriptionExpiry: Invalid current expiry date: ' . $e->getMessage());
                $baseDate = new DateTime('now', $utcTimezone);
            }
        } else {
            // New subscription or forced from now - use current date as base
            $baseDate = new DateTime('now', $utcTimezone);
        }

        // Add the billing period
        $baseDate->modify($modifyString);

        return $baseDate->format('Y-m-d H:i:s');
    }
}

if (!function_exists('validateBillingInterval')) {
    /**
     * Validate that a billing interval is supported
     *
     * @param string $interval Billing interval to validate
     * @return bool True if valid, false otherwise
     */
    function validateBillingInterval(string $interval): bool
    {
        $validIntervals = ['days', 'weeks', 'months', 'years', 'onetime', 'lifetime'];
        $normalized = normalizeBillingInterval($interval);

        return in_array($normalized, $validIntervals, true);
    }
}

if (!function_exists('isSubscriptionActive')) {
    /**
     * Check if a subscription license is currently active
     *
     * Checks both license_status and date_expiry
     *
     * @param array $licenseData License data array from database
     * @return bool True if subscription is active
     */
    function isSubscriptionActive(array $licenseData): bool
    {
        // Check license status
        if (!isset($licenseData['license_status']) || $licenseData['license_status'] !== 'active') {
            return false;
        }

        // Check if it's a subscription
        if (!isset($licenseData['license_type']) || $licenseData['license_type'] !== 'subscription') {
            return false; // Not a subscription
        }

        // Check expiry date
        if (!isset($licenseData['date_expiry']) || empty($licenseData['date_expiry'])) {
            return false; // No expiry date
        }

        try {
            $expiry = new DateTime($licenseData['date_expiry'], new DateTimeZone('UTC'));
            $now = new DateTime('now', new DateTimeZone('UTC'));

            return $expiry > $now; // Active if not yet expired
        } catch (Exception $e) {
            log_message('error', 'isSubscriptionActive: Invalid date format: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * End of subscription helper functions
 */
