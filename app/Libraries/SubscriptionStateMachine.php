<?php

namespace App\Libraries;

use App\Models\SubscriptionModel;
use CodeIgniter\I18n\Time;

/**
 * Subscription State Machine
 *
 * Manages subscription status transitions with validation and logging
 * Prevents invalid state changes and provides audit trail
 *
 * Usage:
 * $stateMachine = new SubscriptionStateMachine();
 * $result = $stateMachine->transitionTo($subscriptionId, 'active', 'Payment completed', 'webhook');
 */
class SubscriptionStateMachine
{
    protected $subscriptionModel;
    protected $db;

    // Valid state transitions
    const VALID_TRANSITIONS = [
        'pending' => ['active', 'failed', 'cancelled'],
        'active' => ['cancelled', 'expired', 'suspended'],
        'suspended' => ['active', 'cancelled'],
        'cancelled' => [], // Terminal state - no transitions allowed
        'expired' => ['active'], // Allow reactivation
        'failed' => ['active', 'cancelled'] // Allow retry or cancellation
    ];

    // State descriptions for logging
    const STATE_DESCRIPTIONS = [
        'pending' => 'Awaiting payment or activation',
        'active' => 'Subscription is active and billing',
        'cancelled' => 'Subscription cancelled by user or admin',
        'expired' => 'Subscription expired due to non-payment',
        'suspended' => 'Subscription temporarily suspended',
        'failed' => 'Subscription failed due to payment issues'
    ];

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Check if transition from one state to another is valid
     *
     * @param string $fromState Current state
     * @param string $toState Desired state
     * @return bool
     */
    public function canTransition(string $fromState, string $toState): bool
    {
        if (!isset(self::VALID_TRANSITIONS[$fromState])) {
            return false;
        }

        return in_array($toState, self::VALID_TRANSITIONS[$fromState]);
    }

    /**
     * Transition subscription to new state with validation and logging
     *
     * @param int $subscriptionId Subscription ID
     * @param string $newStatus Target status
     * @param string $reason Reason for transition
     * @param string $source Source of change (user, admin, system, webhook, cronjob)
     * @param int|null $changedBy User ID who made the change
     * @param array $metadata Additional metadata
     * @return array Result array with success status and message
     */
    public function transitionTo(
        int $subscriptionId,
        string $newStatus,
        string $reason = '',
        string $source = 'system',
        ?int $changedBy = null,
        array $metadata = []
    ): array {
        try {
            // Begin transaction
            $this->db->transStart();

            // Get current subscription
            $subscription = $this->subscriptionModel->find($subscriptionId);
            if (!$subscription) {
                return [
                    'success' => false,
                    'message' => 'Subscription not found'
                ];
            }

            $currentStatus = $subscription['subscription_status'];

            // Check if transition is valid
            if (!$this->canTransition($currentStatus, $newStatus)) {
                return [
                    'success' => false,
                    'message' => "Invalid transition from '{$currentStatus}' to '{$newStatus}'"
                ];
            }

            // Log the state change
            $this->logStateChange(
                $subscriptionId,
                $currentStatus,
                $newStatus,
                $reason,
                $changedBy,
                $source,
                $metadata
            );

            // Update subscription status with additional logic based on new state
            $updateData = $this->prepareUpdateData($newStatus, $reason, $subscription);

            $updated = $this->subscriptionModel->update($subscriptionId, $updateData);

            if (!$updated) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Failed to update subscription status'
                ];
            }

            // Complete transaction
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Transaction failed during status update'
                ];
            }

            log_message('info', "[SubscriptionStateMachine] Subscription {$subscriptionId} transitioned from '{$currentStatus}' to '{$newStatus}' by {$source}");

            return [
                'success' => true,
                'message' => "Subscription status updated to '{$newStatus}'",
                'old_status' => $currentStatus,
                'new_status' => $newStatus
            ];

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[SubscriptionStateMachine] State transition failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'State transition failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Prepare update data based on new status
     *
     * @param string $newStatus
     * @param string $reason
     * @param array $subscription
     * @return array
     */
    protected function prepareUpdateData(string $newStatus, string $reason, array $subscription): array
    {
        $data = ['subscription_status' => $newStatus];
        $currentTime = Time::now('UTC')->toDateTimeString();

        switch ($newStatus) {
            case 'active':
                $data['transaction_token'] = null; // Clear any pending payment tokens
                if ($subscription['subscription_status'] !== 'active') {
                    // Only set start_date if transitioning to active for first time
                    $data['start_date'] = $subscription['start_date'] ?: $currentTime;
                }
                break;

            case 'cancelled':
                $data['cancelled_at'] = $currentTime;
                $data['transaction_token'] = null;
                $data['next_payment_date'] = null;
                if ($reason) {
                    $data['cancellation_reason'] = $reason;
                }
                break;

            case 'expired':
                $data['transaction_token'] = null;
                $data['next_payment_date'] = null;
                break;

            case 'suspended':
                $data['transaction_token'] = null;
                break;

            case 'failed':
                if ($reason) {
                    $data['last_payment_failure_reason'] = $reason;
                }
                break;
        }

        return $data;
    }

    /**
     * Log state change to subscription_state_log table
     *
     * @param int $subscriptionId
     * @param string $oldStatus
     * @param string $newStatus
     * @param string $reason
     * @param int|null $changedBy
     * @param string $source
     * @param array $metadata
     */
    protected function logStateChange(
        int $subscriptionId,
        string $oldStatus,
        string $newStatus,
        string $reason,
        ?int $changedBy,
        string $source,
        array $metadata
    ): void {
        $logData = [
            'subscription_id' => $subscriptionId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason ?: null,
            'changed_by' => $changedBy,
            'change_source' => $source,
            'metadata' => !empty($metadata) ? json_encode($metadata) : null,
            'created_at' => Time::now('UTC')->toDateTimeString()
        ];

        $this->db->table('subscription_state_log')->insert($logData);
    }

    /**
     * Get valid transitions for a given state
     *
     * @param string $state
     * @return array
     */
    public function getValidTransitions(string $state): array
    {
        return self::VALID_TRANSITIONS[$state] ?? [];
    }

    /**
     * Get all possible states
     *
     * @return array
     */
    public function getAllStates(): array
    {
        return array_keys(self::VALID_TRANSITIONS);
    }

    /**
     * Get state description
     *
     * @param string $state
     * @return string
     */
    public function getStateDescription(string $state): string
    {
        return self::STATE_DESCRIPTIONS[$state] ?? 'Unknown state';
    }

    /**
     * Get subscription state history
     *
     * @param int $subscriptionId
     * @param int $limit
     * @return array
     */
    public function getStateHistory(int $subscriptionId, int $limit = 50): array
    {
        return $this->db->table('subscription_state_log')
            ->where('subscription_id', $subscriptionId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Check if subscription is in terminal state
     *
     * @param string $state
     * @return bool
     */
    public function isTerminalState(string $state): bool
    {
        return empty(self::VALID_TRANSITIONS[$state]);
    }

    /**
     * Bulk transition multiple subscriptions (for cronjobs)
     *
     * @param array $subscriptionIds
     * @param string $newStatus
     * @param string $reason
     * @param string $source
     * @return array Results array
     */
    public function bulkTransition(
        array $subscriptionIds,
        string $newStatus,
        string $reason = '',
        string $source = 'cronjob'
    ): array {
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($subscriptionIds)
        ];

        foreach ($subscriptionIds as $subscriptionId) {
            $result = $this->transitionTo($subscriptionId, $newStatus, $reason, $source);

            if ($result['success']) {
                $results['success'][] = $subscriptionId;
            } else {
                $results['failed'][] = [
                    'subscription_id' => $subscriptionId,
                    'error' => $result['message']
                ];
            }
        }

        return $results;
    }
}