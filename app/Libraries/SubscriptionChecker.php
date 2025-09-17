<?php

namespace App\Libraries;

use App\Models\SubscriptionModel;
use App\Models\PackageModel;

/***
 * Usage examples:
 * 
 * $subscriptionChecker = new \App\Libraries\SubscriptionChecker();

// Get a feature's limit
$storageLimit = $subscriptionChecker->getLimit($userId, 'File_Storage', 'value');

// Check if within limits
$canAddMore = $subscriptionChecker->checkLimit($userId, 'Product_Count_Limit', 'value', $currentCount);

// Check if feature is enabled
$isFeatureEnabled = $subscriptionChecker->isFeatureEnabled($userId, 'License_Prefix');
 * 
 * */

class SubscriptionChecker
{
    protected $subscriptionModel;
    protected $packageModel;

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        $this->packageModel = new PackageModel();
    }

    /**
     * Gets the subscription limit value for a specific feature
     *
     * @param int    $userId      The ID of the user
     * @param string $featureName The name of the feature to check
     * @param string $limitKey    The specific limit key to check
     *
     * @return int|null Returns the limit value or null if not found/disabled
     */
    public function getLimit(int $userId, string $featureName, string $limitKey): ?int
    {
        $featureData = $this->getFeatureData($userId, $featureName);
        
        if ($featureData === null) {
            return null;
        }

        if (!isset($featureData[$limitKey])) {
            return null;
        }

        if ($featureData['enabled'] !== 'true') {
            return null;
        }

        return (int) $featureData[$limitKey];
    }

    /**
     * Checks if a user's subscription allows access to a feature and its usage limit
     *
     * @param int    $userId       The ID of the user
     * @param string $featureName  The name of the feature to check
     * @param string $limitKey     The specific limit key to check
     * @param int    $currentCount The current usage count to check against the limit
     *
     * @return bool|null Returns true if within limit/unlimited, false if exceeded/disabled, null if not found
     */
    public function checkLimit(int $userId, string $featureName, string $limitKey, int $currentCount): ?bool
    {
        // Basic input validation
        if ($userId <= 0 || empty($featureName) || empty($limitKey) || $currentCount < 0) {
            return false;
        }

        $limit = $this->getLimit($userId, $featureName, $limitKey);

        if ($limit === null) {
            return null;
        }

        // Check if unlimited (0 or negative) or within limit
        return $limit <= 0 || $currentCount < $limit;
    }

    /**
     * Gets feature data from user's active subscription package
     *
     * @param int    $userId      The ID of the user
     * @param string $featureName The name of the feature to get
     *
     * @return array|null Returns feature data array or null if not found
     */
    public function getFeatureData(int $userId, string $featureName): ?array
    {
        // Get active subscription
        $activeSubscription = $this->subscriptionModel->getActiveByUserId($userId);
        if (empty($activeSubscription)) {
            return null;
        }

        // Get package details
        $package = $this->packageModel->find($activeSubscription['package_id']);
        if (empty($package)) {
            return null;
        }

        // Safely decode JSON
        $packageModules = json_decode($package['package_modules'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', '[SubscriptionChecker] Invalid package modules JSON format for package ID: ' . $activeSubscription['package_id']);
            return null;
        }

        // Find and return feature data
        foreach ($packageModules as $features) {
            if (isset($features[$featureName])) {
                return $features[$featureName];
            }
        }

        return null;
    }

    /**
     * Checks if a feature is enabled for a user's subscription
     *
     * @param int    $userId      The ID of the user
     * @param string $featureName The name of the feature to check
     *
     * @return bool Returns true if feature is enabled, false otherwise
     */
    public function isFeatureEnabled(int $userId, string $featureName): bool
    {
        $featureData = $this->getFeatureData($userId, $featureName);
        
        if ($featureData === null || !isset($featureData['enabled'])) {
            return false;
        }

        return $featureData['enabled'] === 'true';
    }
}