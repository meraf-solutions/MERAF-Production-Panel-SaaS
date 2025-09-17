<?php

namespace App\Libraries;

use App\Models\SubscriptionModel;
use App\Models\SubscriptionPaymentModel;
use CodeIgniter\I18n\Time;

/**
 * Payment Retry Manager
 *
 * Handles failed payment retries with exponential backoff,
 * dunning management, and automated subscription handling
 *
 * Usage:
 * $retryManager = new PaymentRetryManager();
 * $result = $retryManager->scheduleRetry($subscriptionId, 'payment_failed');
 */
class PaymentRetryManager
{
    protected $subscriptionModel;
    protected $paymentModel;
    protected $stateMachine;
    protected $db;

    // Retry configuration
    const MAX_RETRIES = 3;
    const RETRY_INTERVALS = [
        1 => 24,   // 1st retry: 24 hours
        2 => 72,   // 2nd retry: 72 hours (3 days)
        3 => 168   // 3rd retry: 168 hours (7 days)
    ];

    // Retry reasons and their handling
    const RETRY_REASONS = [
        'payment_failed' => ['max_retries' => 3, 'auto_suspend' => true],
        'insufficient_funds' => ['max_retries' => 4, 'auto_suspend' => false],
        'card_expired' => ['max_retries' => 2, 'auto_suspend' => true],
        'network_error' => ['max_retries' => 5, 'auto_suspend' => false],
        'gateway_timeout' => ['max_retries' => 3, 'auto_suspend' => false],
        'webhook_failed' => ['max_retries' => 10, 'auto_suspend' => false]
    ];

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        $this->paymentModel = new SubscriptionPaymentModel();
        $this->stateMachine = new SubscriptionStateMachine();
        $this->db = \Config\Database::connect();
    }

    /**
     * Schedule a retry for failed payment
     *
     * @param int $subscriptionId Subscription ID
     * @param string $reason Failure reason
     * @param array $errorDetails Additional error details
     * @return array Result of retry scheduling
     */
    public function scheduleRetry(int $subscriptionId, string $reason, array $errorDetails = []): array
    {
        try {
            $subscription = $this->subscriptionModel->find($subscriptionId);
            if (!$subscription) {
                return [
                    'success' => false,
                    'message' => 'Subscription not found'
                ];
            }

            // Get retry configuration for this reason
            $retryConfig = self::RETRY_REASONS[$reason] ?? self::RETRY_REASONS['payment_failed'];
            $currentRetryCount = (int) $subscription['retry_count'];

            // Check if max retries exceeded
            if ($currentRetryCount >= $retryConfig['max_retries']) {
                return $this->handleMaxRetriesExceeded($subscription, $reason, $retryConfig);
            }

            // Calculate next retry time with exponential backoff
            $nextRetryHours = $this->calculateRetryInterval($currentRetryCount + 1, $reason);
            $nextRetryDate = Time::now('UTC')->addHours($nextRetryHours);

            // Update subscription with retry information
            $retryDates = json_decode($subscription['retry_dates'] ?? '[]', true);
            $retryDates[] = $nextRetryDate->toDateTimeString();

            $updateData = [
                'retry_count' => $currentRetryCount + 1,
                'next_retry_date' => $nextRetryDate->toDateTimeString(),
                'retry_dates' => json_encode($retryDates),
                'last_payment_failure_reason' => $reason . ': ' . ($errorDetails['message'] ?? 'Unknown error')
            ];

            $this->subscriptionModel->update($subscriptionId, $updateData);

            // Log the retry schedule
            $this->logRetryEvent($subscriptionId, 'retry_scheduled', [
                'reason' => $reason,
                'retry_count' => $currentRetryCount + 1,
                'next_retry' => $nextRetryDate->toDateTimeString(),
                'error_details' => $errorDetails
            ]);

            // Send retry notification email
            $this->sendRetryNotification($subscription, $currentRetryCount + 1, $nextRetryDate);

            log_message('info', "[PaymentRetryManager] Scheduled retry #" . ($currentRetryCount + 1) . " for subscription {$subscriptionId} at " . $nextRetryDate->toDateTimeString());

            return [
                'success' => true,
                'message' => 'Retry scheduled successfully',
                'retry_count' => $currentRetryCount + 1,
                'next_retry_date' => $nextRetryDate->toDateTimeString(),
                'max_retries' => $retryConfig['max_retries']
            ];

        } catch (\Exception $e) {
            log_message('error', '[PaymentRetryManager] Failed to schedule retry: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to schedule retry: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process pending retries (called by cronjob)
     *
     * @return array Summary of processed retries
     */
    public function processRetries(): array
    {
        $currentTime = Time::now('UTC')->toDateTimeString();

        // Get subscriptions due for retry
        $subscriptions = $this->subscriptionModel
            ->where('next_retry_date <=', $currentTime)
            ->where('next_retry_date IS NOT NULL')
            ->where('subscription_status', 'active')
            ->findAll();

        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'max_retries_reached' => 0,
            'details' => []
        ];

        foreach ($subscriptions as $subscription) {
            $result = $this->processSubscriptionRetry($subscription);
            $results['processed']++;

            if ($result['success']) {
                $results['successful']++;
            } elseif ($result['max_retries_reached']) {
                $results['max_retries_reached']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'subscription_id' => $subscription['id'],
                'subscription_reference' => $subscription['subscription_reference'],
                'result' => $result
            ];
        }

        log_message('info', "[PaymentRetryManager] Processed {$results['processed']} retries: {$results['successful']} successful, {$results['failed']} failed, {$results['max_retries_reached']} reached max retries");

        return $results;
    }

    /**
     * Process retry for a specific subscription
     *
     * @param array $subscription
     * @return array
     */
    protected function processSubscriptionRetry(array $subscription): array
    {
        try {
            log_message('info', "[PaymentRetryManager] Processing retry for subscription {$subscription['subscription_reference']}");

            // Get payment service for this subscription
            $paymentFactory = new PaymentMethodFactory();
            $paymentService = $paymentFactory->create($subscription['payment_method']);

            if (!$paymentService) {
                log_message('error', "[PaymentRetryManager] Payment service not available for {$subscription['payment_method']}");
                return [
                    'success' => false,
                    'message' => 'Payment service not available',
                    'max_retries_reached' => false
                ];
            }

            // Attempt to process payment retry
            // This would depend on the specific payment method implementation
            $retryResult = $this->attemptPaymentRetry($paymentService, $subscription);

            if ($retryResult['success']) {
                // Clear retry information on success
                $this->clearRetryInformation($subscription['id']);

                $this->logRetryEvent($subscription['id'], 'retry_successful', [
                    'retry_count' => $subscription['retry_count'],
                    'payment_result' => $retryResult
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment retry successful',
                    'max_retries_reached' => false
                ];
            } else {
                // Schedule next retry or handle max retries
                return $this->handleRetryFailure($subscription, $retryResult);
            }

        } catch (\Exception $e) {
            log_message('error', "[PaymentRetryManager] Retry processing failed: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Retry processing failed: ' . $e->getMessage(),
                'max_retries_reached' => false
            ];
        }
    }

    /**
     * Attempt payment retry using payment service
     *
     * @param object $paymentService
     * @param array $subscription
     * @return array
     */
    protected function attemptPaymentRetry(object $paymentService, array $subscription): array
    {
        // This is a placeholder - actual implementation would depend on payment method
        // For PayPal subscriptions, this might involve checking subscription status
        // For other methods, might involve re-attempting charge

        try {
            // Get latest subscription status from payment provider
            $providerSubscription = $paymentService->getSubscription($subscription['subscription_reference']);

            if ($providerSubscription && isset($providerSubscription->status)) {
                if ($providerSubscription->status === 'ACTIVE') {
                    // Payment was successful
                    return [
                        'success' => true,
                        'message' => 'Subscription is now active',
                        'provider_status' => $providerSubscription->status
                    ];
                }
            }

            // For now, return failure to schedule next retry
            return [
                'success' => false,
                'message' => 'Payment still failing',
                'provider_status' => $providerSubscription->status ?? 'unknown'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment retry attempt failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle max retries exceeded
     *
     * @param array $subscription
     * @param string $reason
     * @param array $retryConfig
     * @return array
     */
    protected function handleMaxRetriesExceeded(array $subscription, string $reason, array $retryConfig): array
    {
        log_message('warning', "[PaymentRetryManager] Max retries exceeded for subscription {$subscription['id']}");

        // Determine action based on configuration
        if ($retryConfig['auto_suspend']) {
            $newStatus = 'suspended';
            $statusReason = "Max payment retries exceeded for reason: {$reason}";
        } else {
            $newStatus = 'failed';
            $statusReason = "Payment failed after {$retryConfig['max_retries']} retries: {$reason}";
        }

        // Update subscription status
        $this->stateMachine->transitionTo(
            $subscription['id'],
            $newStatus,
            $statusReason,
            'system'
        );

        // Clear retry information
        $this->clearRetryInformation($subscription['id']);

        // Log the event
        $this->logRetryEvent($subscription['id'], 'max_retries_exceeded', [
            'reason' => $reason,
            'final_status' => $newStatus,
            'total_retries' => $subscription['retry_count']
        ]);

        // Send max retries notification
        $this->sendMaxRetriesNotification($subscription, $newStatus);

        return [
            'success' => false,
            'message' => "Max retries exceeded. Subscription {$newStatus}.",
            'max_retries_reached' => true,
            'new_status' => $newStatus
        ];
    }

    /**
     * Handle retry failure
     *
     * @param array $subscription
     * @param array $retryResult
     * @return array
     */
    protected function handleRetryFailure(array $subscription, array $retryResult): array
    {
        $reason = 'retry_failed';
        $errorDetails = ['message' => $retryResult['message']];

        // Schedule next retry
        $nextRetryResult = $this->scheduleRetry($subscription['id'], $reason, $errorDetails);

        if ($nextRetryResult['success']) {
            return [
                'success' => false,
                'message' => 'Retry failed, next retry scheduled',
                'max_retries_reached' => false,
                'next_retry' => $nextRetryResult
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Retry failed and could not schedule next retry',
                'max_retries_reached' => $nextRetryResult['max_retries_reached'] ?? false
            ];
        }
    }

    /**
     * Calculate retry interval with exponential backoff
     *
     * @param int $retryCount
     * @param string $reason
     * @return int Hours until next retry
     */
    protected function calculateRetryInterval(int $retryCount, string $reason): int
    {
        // Use predefined intervals or exponential backoff
        if (isset(self::RETRY_INTERVALS[$retryCount])) {
            return self::RETRY_INTERVALS[$retryCount];
        }

        // Exponential backoff: 2^(retry_count-1) * base_hours
        $baseHours = 24;
        return min(pow(2, $retryCount - 1) * $baseHours, 336); // Max 2 weeks
    }

    /**
     * Clear retry information after successful payment
     *
     * @param int $subscriptionId
     */
    protected function clearRetryInformation(int $subscriptionId): void
    {
        $this->subscriptionModel->update($subscriptionId, [
            'retry_count' => 0,
            'next_retry_date' => null,
            'retry_dates' => null,
            'last_payment_failure_reason' => null
        ]);
    }

    /**
     * Log retry event
     *
     * @param int $subscriptionId
     * @param string $eventType
     * @param array $eventData
     */
    protected function logRetryEvent(int $subscriptionId, string $eventType, array $eventData): void
    {
        $logData = [
            'subscription_id' => $subscriptionId,
            'event_type' => $eventType,
            'event_data' => json_encode($eventData),
            'created_at' => Time::now('UTC')->toDateTimeString()
        ];

        // Log to file for now - could be extended to use database table
        log_message('info', "[PaymentRetryManager] Event: {$eventType} for subscription {$subscriptionId}: " . json_encode($eventData));
    }

    /**
     * Send retry notification email
     *
     * @param array $subscription
     * @param int $retryCount
     * @param Time $nextRetryDate
     */
    protected function sendRetryNotification(array $subscription, int $retryCount, Time $nextRetryDate): void
    {
        try {
            $emailService = new \App\Libraries\EmailService();

            $emailData = [
                'subscription_id' => $subscription['subscription_reference'],
                'retry_count' => $retryCount,
                'next_retry_date' => $nextRetryDate->toDateTimeString(),
                'max_retries' => self::MAX_RETRIES,
                'failure_reason' => $subscription['last_payment_failure_reason']
            ];

            $emailService->sendSubscriptionEmail([
                'userID' => $subscription['user_id'],
                'template' => 'payment_retry_scheduled',
                'data' => $emailData
            ]);

        } catch (\Exception $e) {
            log_message('error', '[PaymentRetryManager] Failed to send retry notification: ' . $e->getMessage());
        }
    }

    /**
     * Send max retries exceeded notification
     *
     * @param array $subscription
     * @param string $finalStatus
     */
    protected function sendMaxRetriesNotification(array $subscription, string $finalStatus): void
    {
        try {
            $emailService = new \App\Libraries\EmailService();

            $emailData = [
                'subscription_id' => $subscription['subscription_reference'],
                'final_status' => $finalStatus,
                'total_retries' => $subscription['retry_count'],
                'failure_reason' => $subscription['last_payment_failure_reason']
            ];

            $emailService->sendSubscriptionEmail([
                'userID' => $subscription['user_id'],
                'template' => 'payment_retries_exhausted',
                'data' => $emailData
            ]);

        } catch (\Exception $e) {
            log_message('error', '[PaymentRetryManager] Failed to send max retries notification: ' . $e->getMessage());
        }
    }

    /**
     * Get retry statistics
     *
     * @param int $days Days to look back
     * @return array
     */
    public function getRetryStatistics(int $days = 30): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $stats = [
            'period_days' => $days,
            'start_date' => $startDate,
            'total_subscriptions_with_retries' => 0,
            'successful_retries' => 0,
            'failed_retries' => 0,
            'max_retries_reached' => 0,
            'average_retries_per_subscription' => 0
        ];

        // This would need actual database queries to get real statistics
        // For now, return placeholder structure

        return $stats;
    }
}