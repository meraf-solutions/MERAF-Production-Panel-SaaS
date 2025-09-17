<?php

namespace App\Libraries;

use App\Models\SubscriptionModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\Database\ConnectionInterface;

/**
 * Subscription Usage Tracker
 *
 * Tracks feature usage against subscription limits
 * Provides analytics and usage insights for billing
 *
 * Usage:
 * $tracker = new SubscriptionUsageTracker();
 * $tracker->trackUsage($userId, 'License_Creation', 1);
 * $canUse = $tracker->checkUsageLimit($userId, 'License_Creation', 10);
 */
class SubscriptionUsageTracker
{
    protected $db;
    protected $subscriptionModel;
    protected $subscriptionChecker;

    public function __construct(?ConnectionInterface $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->subscriptionModel = new SubscriptionModel();
        $this->subscriptionChecker = new SubscriptionChecker();
    }

    /**
     * Track feature usage for a user
     *
     * @param int $userId User ID
     * @param string $featureName Feature being used
     * @param int $usageCount Usage count to add (default 1)
     * @param array $metadata Additional metadata
     * @return bool Success status
     */
    public function trackUsage(int $userId, string $featureName, int $usageCount = 1, array $metadata = []): bool
    {
        try {
            // Get active subscription
            $subscription = $this->subscriptionModel->getActiveByUserId($userId);
            if (!$subscription) {
                log_message('warning', "[SubscriptionUsageTracker] No active subscription found for user: {$userId}");
                return false;
            }

            // Get current usage limit
            $limit = $this->subscriptionChecker->getLimit($userId, $featureName, 'value');

            $today = date('Y-m-d');

            // Check if usage record exists for today
            $existingUsage = $this->db->table('subscription_usage_tracking')
                ->where([
                    'user_id' => $userId,
                    'subscription_id' => $subscription['id'],
                    'feature_name' => $featureName,
                    'usage_date' => $today
                ])
                ->get()
                ->getRowArray();

            if ($existingUsage) {
                // Update existing usage
                $newCount = $existingUsage['usage_count'] + $usageCount;

                $this->db->table('subscription_usage_tracking')
                    ->where('id', $existingUsage['id'])
                    ->update([
                        'usage_count' => $newCount,
                        'updated_at' => Time::now('UTC')->toDateTimeString()
                    ]);

                log_message('info', "[SubscriptionUsageTracker] Updated usage for user {$userId}, feature {$featureName}: {$newCount}");
            } else {
                // Create new usage record
                $usageData = [
                    'user_id' => $userId,
                    'subscription_id' => $subscription['id'],
                    'feature_name' => $featureName,
                    'usage_count' => $usageCount,
                    'limit_value' => $limit,
                    'usage_date' => $today,
                    'created_at' => Time::now('UTC')->toDateTimeString(),
                    'updated_at' => Time::now('UTC')->toDateTimeString()
                ];

                $this->db->table('subscription_usage_tracking')->insert($usageData);

                log_message('info', "[SubscriptionUsageTracker] Created usage record for user {$userId}, feature {$featureName}: {$usageCount}");
            }

            // Store metadata if provided
            if (!empty($metadata)) {
                $this->storeUsageMetadata($userId, $featureName, $metadata);
            }

            return true;

        } catch (\Exception $e) {
            log_message('error', '[SubscriptionUsageTracker] Failed to track usage: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user can use a feature (within limits)
     *
     * @param int $userId User ID
     * @param string $featureName Feature to check
     * @param int $requestedUsage How much usage is requested (default 1)
     * @return array Result with can_use boolean and usage info
     */
    public function checkUsageLimit(int $userId, string $featureName, int $requestedUsage = 1): array
    {
        try {
            // Check if feature is enabled
            if (!$this->subscriptionChecker->isFeatureEnabled($userId, $featureName)) {
                return [
                    'can_use' => false,
                    'reason' => 'Feature not enabled in subscription',
                    'current_usage' => 0,
                    'limit' => 0,
                    'remaining' => 0
                ];
            }

            // Get limit (0 or negative means unlimited)
            $limit = $this->subscriptionChecker->getLimit($userId, $featureName, 'value');

            if ($limit <= 0) {
                return [
                    'can_use' => true,
                    'reason' => 'Unlimited usage',
                    'current_usage' => $this->getCurrentUsage($userId, $featureName),
                    'limit' => 'unlimited',
                    'remaining' => 'unlimited'
                ];
            }

            // Get current usage
            $currentUsage = $this->getCurrentUsage($userId, $featureName);
            $remaining = max(0, $limit - $currentUsage);

            $canUse = ($currentUsage + $requestedUsage) <= $limit;

            return [
                'can_use' => $canUse,
                'reason' => $canUse ? 'Within limits' : 'Usage limit exceeded',
                'current_usage' => $currentUsage,
                'limit' => $limit,
                'remaining' => $remaining,
                'requested' => $requestedUsage
            ];

        } catch (\Exception $e) {
            log_message('error', '[SubscriptionUsageTracker] Error checking usage limit: ' . $e->getMessage());
            return [
                'can_use' => false,
                'reason' => 'Error checking limits',
                'current_usage' => 0,
                'limit' => 0,
                'remaining' => 0
            ];
        }
    }

    /**
     * Get current usage for a feature today
     *
     * @param int $userId
     * @param string $featureName
     * @return int
     */
    public function getCurrentUsage(int $userId, string $featureName): int
    {
        $today = date('Y-m-d');

        $usage = $this->db->table('subscription_usage_tracking')
            ->select('usage_count')
            ->where([
                'user_id' => $userId,
                'feature_name' => $featureName,
                'usage_date' => $today
            ])
            ->get()
            ->getRowArray();

        return $usage ? (int) $usage['usage_count'] : 0;
    }

    /**
     * Get usage statistics for a user
     *
     * @param int $userId
     * @param array $features Optional specific features to check
     * @param int $days Number of days to look back (default 30)
     * @return array
     */
    public function getUserUsageStats(int $userId, array $features = [], int $days = 30): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        $endDate = date('Y-m-d');

        $builder = $this->db->table('subscription_usage_tracking')
            ->select('feature_name, SUM(usage_count) as total_usage, MAX(limit_value) as limit_value, COUNT(DISTINCT usage_date) as active_days')
            ->where('user_id', $userId)
            ->where('usage_date >=', $startDate)
            ->where('usage_date <=', $endDate)
            ->groupBy('feature_name');

        if (!empty($features)) {
            $builder->whereIn('feature_name', $features);
        }

        $results = $builder->get()->getResultArray();

        $stats = [
            'user_id' => $userId,
            'period_days' => $days,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'features' => []
        ];

        foreach ($results as $result) {
            $stats['features'][$result['feature_name']] = [
                'total_usage' => (int) $result['total_usage'],
                'limit' => $result['limit_value'] ? (int) $result['limit_value'] : 'unlimited',
                'active_days' => (int) $result['active_days'],
                'avg_daily_usage' => round($result['total_usage'] / max(1, $result['active_days']), 2)
            ];
        }

        return $stats;
    }

    /**
     * Get usage trends over time
     *
     * @param int $userId
     * @param string $featureName
     * @param int $days
     * @return array
     */
    public function getUsageTrend(int $userId, string $featureName, int $days = 30): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $results = $this->db->table('subscription_usage_tracking')
            ->select('usage_date, usage_count')
            ->where([
                'user_id' => $userId,
                'feature_name' => $featureName
            ])
            ->where('usage_date >=', $startDate)
            ->orderBy('usage_date', 'ASC')
            ->get()
            ->getResultArray();

        $trend = [];
        foreach ($results as $result) {
            $trend[$result['usage_date']] = (int) $result['usage_count'];
        }

        return [
            'user_id' => $userId,
            'feature_name' => $featureName,
            'period_days' => $days,
            'daily_usage' => $trend,
            'total_usage' => array_sum($trend),
            'peak_usage' => !empty($trend) ? max($trend) : 0,
            'avg_usage' => !empty($trend) ? round(array_sum($trend) / count($trend), 2) : 0
        ];
    }

    /**
     * Get users approaching their limits
     *
     * @param float $threshold Threshold percentage (0.8 = 80%)
     * @return array
     */
    public function getUsersApproachingLimits(float $threshold = 0.8): array
    {
        $today = date('Y-m-d');

        $query = "
            SELECT
                u.user_id,
                u.feature_name,
                u.usage_count,
                u.limit_value,
                (u.usage_count / u.limit_value) as usage_percentage
            FROM subscription_usage_tracking u
            WHERE u.usage_date = ?
            AND u.limit_value > 0
            AND (u.usage_count / u.limit_value) >= ?
            ORDER BY usage_percentage DESC
        ";

        $results = $this->db->query($query, [$today, $threshold])->getResultArray();

        $users = [];
        foreach ($results as $result) {
            $users[] = [
                'user_id' => (int) $result['user_id'],
                'feature_name' => $result['feature_name'],
                'usage_count' => (int) $result['usage_count'],
                'limit_value' => (int) $result['limit_value'],
                'usage_percentage' => round($result['usage_percentage'] * 100, 1),
                'remaining' => $result['limit_value'] - $result['usage_count']
            ];
        }

        return $users;
    }

    /**
     * Reset daily usage counters (for testing or manual reset)
     *
     * @param int $userId
     * @param string|null $featureName Optional specific feature
     * @return bool
     */
    public function resetDailyUsage(int $userId, ?string $featureName = null): bool
    {
        try {
            $today = date('Y-m-d');

            $builder = $this->db->table('subscription_usage_tracking')
                ->where('user_id', $userId)
                ->where('usage_date', $today);

            if ($featureName) {
                $builder->where('feature_name', $featureName);
            }

            $result = $builder->delete();

            log_message('info', "[SubscriptionUsageTracker] Reset daily usage for user {$userId}" . ($featureName ? ", feature {$featureName}" : ''));
            return $result;

        } catch (\Exception $e) {
            log_message('error', '[SubscriptionUsageTracker] Failed to reset daily usage: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Store additional usage metadata
     *
     * @param int $userId
     * @param string $featureName
     * @param array $metadata
     */
    protected function storeUsageMetadata(int $userId, string $featureName, array $metadata): void
    {
        try {
            // Store in a separate metadata table or log file
            $metadataEntry = [
                'user_id' => $userId,
                'feature_name' => $featureName,
                'metadata' => json_encode($metadata),
                'timestamp' => Time::now('UTC')->toDateTimeString()
            ];

            // For now, just log it - can be extended to use a metadata table
            log_message('info', '[SubscriptionUsageTracker] Usage metadata: ' . json_encode($metadataEntry));

        } catch (\Exception $e) {
            log_message('error', '[SubscriptionUsageTracker] Failed to store metadata: ' . $e->getMessage());
        }
    }

    /**
     * Get usage summary for billing period
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getBillingPeriodUsage(int $userId, string $startDate, string $endDate): array
    {
        $results = $this->db->table('subscription_usage_tracking')
            ->select('feature_name, SUM(usage_count) as total_usage, MAX(limit_value) as limit_value')
            ->where('user_id', $userId)
            ->where('usage_date >=', $startDate)
            ->where('usage_date <=', $endDate)
            ->groupBy('feature_name')
            ->get()
            ->getResultArray();

        $summary = [
            'user_id' => $userId,
            'billing_period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'features' => [],
            'total_features_used' => count($results),
            'over_limit_features' => 0
        ];

        foreach ($results as $result) {
            $isOverLimit = $result['limit_value'] > 0 && $result['total_usage'] > $result['limit_value'];
            if ($isOverLimit) {
                $summary['over_limit_features']++;
            }

            $summary['features'][$result['feature_name']] = [
                'total_usage' => (int) $result['total_usage'],
                'limit' => $result['limit_value'] ? (int) $result['limit_value'] : 'unlimited',
                'over_limit' => $isOverLimit,
                'overage' => $isOverLimit ? $result['total_usage'] - $result['limit_value'] : 0
            ];
        }

        return $summary;
    }
}