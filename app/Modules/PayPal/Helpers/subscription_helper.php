<?php

/**
 * Subscription Helper Functions
 * 
 * This file contains utility functions for handling subscription-related operations.
 */

if (!function_exists('formatModuleName')) {
    /**
     * Format a module name from snake_case to Title Case
     */
    function formatModuleName(string $module): string
    {
        return ucwords(str_replace('_', ' ', $module));
    }
}

if (!function_exists('formatModuleSettings')) {
    /**
     * Format module settings into a readable string
     */
    function formatModuleSettings($settings): string
    {
        if (!is_array($settings)) {
            return '';
        }

        $formatted = [];
        foreach ($settings as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            } elseif (is_array($value)) {
                $value = implode(', ', $value);
            }
            $formatted[] = formatModuleName($key) . ': ' . $value;
        }

        return implode(', ', $formatted);
    }
}

if (!function_exists('getStatusBadgeClass')) {
    /**
     * Get the appropriate Bootstrap badge class for a subscription status
     */
    function getStatusBadgeClass(string $status): string
    {
        return match (strtolower($status)) {
            'active' => 'success',
            'suspended' => 'warning',
            'cancelled' => 'danger',
            'expired' => 'secondary text-light',
            default => 'info',
        };
    }
}

if (!function_exists('getStatusSoftBadgeClass')) {
    /**
     * Get the appropriate Bootstrap badge class for a subscription status
     */
    function getStatusSoftBadgeClass(string $status): string
    {
        return match (strtolower($status)) {
            'active' => 'soft-success',
            'suspended' => 'soft-warning',
            'cancelled' => 'soft-danger',
            'expired' => 'soft-secondary',
            default => 'soft-info',
        };
    }
}

if (!function_exists('formatSubscriptionDuration')) {
    /**
     * Format subscription duration into a readable string
     */
    function formatSubscriptionDuration(int $validity, string $duration): string
    {
        $duration = strtolower($duration);
        $plural = $validity > 1 ? 's' : '';
        
        return match ($duration) {
            'day' => "{$validity} day{$plural}",
            'week' => "{$validity} week{$plural}",
            'month' => "{$validity} month{$plural}",
            'year' => "{$validity} year{$plural}",
            'lifetime' => 'Lifetime',
            default => "{$validity} {$duration}{$plural}",
        };
    }
}

if (!function_exists('calculateNextBillingDate')) {
    /**
     * Calculate the next billing date based on current date and subscription settings
     */
    function calculateNextBillingDate(string $startDate, int $validity, string $duration): string
    {
        $date = new DateTime($startDate);
        
        switch (strtolower($duration)) {
            case 'day':
                $date->modify("+{$validity} days");
                break;
            case 'week':
                $date->modify("+{$validity} weeks");
                break;
            case 'month':
                $date->modify("+{$validity} months");
                break;
            case 'year':
                $date->modify("+{$validity} years");
                break;
            case 'lifetime':
                return null;
        }

        return $date->format('Y-m-d H:i:s');
    }
}

if (!function_exists('calculateProRatedAmount')) {
    /**
     * Calculate pro-rated amount for subscription changes
     */
    function calculateProRatedAmount(float $oldAmount, float $newAmount, string $nextBillingDate): float
    {
        $now = new DateTime();
        $nextBilling = new DateTime($nextBillingDate);
        $interval = $now->diff($nextBilling);
        $daysRemaining = $interval->days;
        $totalDays = 30; // Assuming monthly billing

        $oldAmountPerDay = $oldAmount / $totalDays;
        $newAmountPerDay = $newAmount / $totalDays;

        return ($newAmountPerDay - $oldAmountPerDay) * $daysRemaining;
    }
}

if (!function_exists('getModuleIcon')) {
    /**
     * Get Font Awesome icon class for a module
     */
    function getModuleIcon(string $module): string
    {
        return match ($module) {
            'api_access' => 'code',
            'storage' => 'database',
            'users' => 'users',
            'reports' => 'chart-bar',
            'support' => 'headset',
            'analytics' => 'chart-line',
            'automation' => 'robot',
            'backup' => 'cloud-upload-alt',
            'security' => 'shield-alt',
            'customization' => 'paint-brush',
            'integration' => 'plug',
            'notification' => 'bell',
            default => 'cube',
        };
    }
}

if (!function_exists('formatSubscriptionPrice')) {
    /**
     * Format subscription price with currency and billing cycle
     */
    function formatSubscriptionPrice(float $price, int $validity, string $duration, string $currency = 'USD'): string
    {
        $formattedPrice = number_format($price, 2);
        $formattedDuration = formatSubscriptionDuration($validity, $duration);
        
        if ($duration === 'lifetime') {
            return "\${$formattedPrice} {$currency} (One-time payment)";
        }

        return "\${$formattedPrice} {$currency} / {$formattedDuration}";
    }
}

if (!function_exists('isSubscriptionExpired')) {
    /**
     * Check if a subscription is expired
     */
    function isSubscriptionExpired(?string $nextBillingDate): bool
    {
        if (!$nextBillingDate) {
            return true;
        }

        $now = new DateTime();
        $nextBilling = new DateTime($nextBillingDate);

        return $now > $nextBilling;
    }
}

if (!function_exists('getRemainingDays')) {
    /**
     * Get remaining days until next billing
     */
    function getRemainingDays(?string $nextBillingDate): ?int
    {
        if (!$nextBillingDate) {
            return null;
        }

        $now = new DateTime();
        $nextBilling = new DateTime($nextBillingDate);
        $interval = $now->diff($nextBilling);

        return $interval->invert ? 0 : $interval->days;
    }
}

if (!function_exists('formatPaymentStatus')) {
    /**
     * Format payment status with appropriate badge class
     */
    function formatPaymentStatus(string $status): array
    {
        return match (strtolower($status)) {
            'completed' => ['class' => 'success', 'text' => 'Completed'],
            'pending' => ['class' => 'warning', 'text' => 'Pending'],
            'failed' => ['class' => 'danger', 'text' => 'Failed'],
            'refunded' => ['class' => 'info', 'text' => 'Refunded'],
            default => ['class' => 'secondary', 'text' => ucfirst($status)],
        };
    }
}

if (!function_exists('validateSubscriptionData')) {
    /**
     * Validate subscription data before processing
     */
    function validateSubscriptionData(array $data): array
    {
        $errors = [];

        if (empty($data['package_id'])) {
            $errors[] = 'Package ID is required';
        }

        if (empty($data['user_id'])) {
            $errors[] = 'User ID is required';
        }

        if (!isset($data['price']) || $data['price'] < 0) {
            $errors[] = 'Invalid price';
        }

        if (!isset($data['validity']) || $data['validity'] < 1) {
            $errors[] = 'Invalid validity period';
        }

        if (!in_array($data['validity_duration'] ?? '', ['day', 'week', 'month', 'year', 'lifetime'])) {
            $errors[] = 'Invalid validity duration';
        }

        return $errors;
    }
}

if (!function_exists('generateSubscriptionSummary')) {
    /**
     * Generate a summary of subscription details
     */
    function generateSubscriptionSummary(array $subscription, array $package): array
    {
        $remainingDays = getRemainingDays($subscription['next_billing_time']);
        $isExpired = isSubscriptionExpired($subscription['next_billing_time']);
        $status = $subscription['status'];

        if ($isExpired && $status === 'active') {
            $status = 'expired';
        }

        return [
            'status' => $status,
            'badge_class' => getStatusBadgeClass($status),
            'package_name' => $package['package_name'],
            'price' => formatSubscriptionPrice(
                $package['price'],
                $package['validity'],
                $package['validity_duration']
            ),
            'start_date' => $subscription['start_time'],
            'next_billing' => $subscription['next_billing_time'],
            'remaining_days' => $remainingDays,
            'is_expired' => $isExpired,
            'features' => json_decode($package['package_modules'] ?? '{}', true)
        ];
    }
}
