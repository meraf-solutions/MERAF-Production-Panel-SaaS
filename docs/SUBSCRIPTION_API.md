# Subscription System API Documentation

This document provides comprehensive documentation for the enhanced subscription system libraries and their APIs.

## Table of Contents

1. [Payment Method Factory](#payment-method-factory)
2. [Subscription State Machine](#subscription-state-machine)
3. [Transaction ID Manager](#transaction-id-manager)
4. [Subscription Usage Tracker](#subscription-usage-tracker)
5. [Payment Retry Manager](#payment-retry-manager)
6. [Webhook Security Manager](#webhook-security-manager)
7. [Enhanced Subscription Checker](#enhanced-subscription-checker)
8. [Database Schema Changes](#database-schema-changes)
9. [Integration Examples](#integration-examples)

---

## Payment Method Factory

**Location**: `app/Libraries/PaymentMethodFactory.php`

### Purpose
Secure factory for creating payment method instances with whitelisting to prevent arbitrary class loading vulnerabilities.

### Usage

```php
use App\Libraries\PaymentMethodFactory;

$factory = new PaymentMethodFactory();

// Create a payment service
$paymentService = $factory->create('PayPal');

// Check if method is allowed
if ($factory->isAllowedMethod('PayPal')) {
    // Proceed with payment
}

// Get all available methods
$availableMethods = $factory->getAvailableMethods();
```

### Methods

#### `create(string $methodName): ?object`
Creates a payment method instance securely.

**Parameters:**
- `$methodName`: Payment method name ('PayPal', 'Offline', 'Trial')

**Returns:** Payment service instance or null if invalid

**Throws:** `InvalidArgumentException` if method not whitelisted

#### `isAllowedMethod(string $methodName): bool`
Checks if payment method is whitelisted.

#### `getAvailableMethods(): array`
Returns array of configured and available payment methods.

#### `getMethodDisplayInfo(string $methodName): array`
Returns display information for UI rendering.

### Security Features
- Whitelisted payment methods only
- Configuration validation
- Service interface validation
- Comprehensive error logging

---

## Subscription State Machine

**Location**: `app/Libraries/SubscriptionStateMachine.php`

### Purpose
Enforces valid subscription status transitions with comprehensive logging and audit trails.

### Usage

```php
use App\Libraries\SubscriptionStateMachine;

$stateMachine = new SubscriptionStateMachine();

// Transition subscription status
$result = $stateMachine->transitionTo(
    $subscriptionId,
    'active',
    'Payment completed successfully',
    'webhook',
    $userId
);

// Check if transition is valid
if ($stateMachine->canTransition('pending', 'active')) {
    // Transition is allowed
}

// Bulk transition for cronjobs
$results = $stateMachine->bulkTransition(
    $subscriptionIds,
    'expired',
    'Bulk expiry process'
);
```

### Valid State Transitions

```
pending → [active, failed, cancelled]
active → [cancelled, expired, suspended]
suspended → [active, cancelled]
cancelled → [] (terminal state)
expired → [active] (reactivation allowed)
failed → [active, cancelled]
```

### Methods

#### `transitionTo(int $subscriptionId, string $newStatus, string $reason, string $source, ?int $changedBy, array $metadata): array`
Performs validated state transition with logging.

#### `canTransition(string $fromState, string $toState): bool`
Validates if transition is allowed.

#### `getStateHistory(int $subscriptionId, int $limit): array`
Returns subscription state change history.

#### `bulkTransition(array $subscriptionIds, string $newStatus, string $reason, string $source): array`
Processes multiple subscriptions in bulk.

### Database Integration
- Automatic logging to `subscription_state_log` table
- Transaction safety with rollback on failure
- Metadata storage for debugging

---

## Transaction ID Manager

**Location**: `app/Libraries/TransactionIdManager.php`

### Purpose
Standardizes transaction ID generation and parsing across all payment methods.

### Format
`[PREFIX]-[METHOD]-[TIMESTAMP]-[UNIQUE_ID]`

Examples:
- `SUB-PAYPAL-1642680000-ABC123` (Subscription)
- `PAY-OFFLINE-1642680000-DEF456` (Payment)
- `INV-TRIAL-1642680000-GHI789` (Invoice)

### Usage

```php
use App\Libraries\TransactionIdManager;

// Generate subscription transaction ID
$subscriptionId = TransactionIdManager::generateSubscription('PAYPAL', false);

// Generate pending payment ID
$pendingId = TransactionIdManager::generatePayment('OFFLINE', true);

// Parse transaction ID
$parsed = TransactionIdManager::parse($transactionId);
// Returns: ['prefix' => 'SUB', 'method' => 'PAYPAL', 'timestamp' => 1642680000, ...]

// Convert pending to completed
$completedId = TransactionIdManager::completePending($pendingId, 'external_tx_123');

// Check if expired
if (TransactionIdManager::isExpired($transactionId, 60)) {
    // Transaction is older than 60 minutes
}
```

### Methods

#### Static Generation Methods
- `generateSubscription(string $method, bool $isPending): string`
- `generatePayment(string $method, bool $isPending): string`
- `generateInvoice(string $method): string`
- `generateRefund(string $method): string`

#### Parsing and Validation
- `parse(string $transactionId): ?array`
- `isValid(string $transactionId): bool`
- `isPending(string $transactionId): bool`
- `completePending(string $pendingId, string $externalId): string`

#### Utility Methods
- `getMethod(string $transactionId): ?string`
- `getAgeInMinutes(string $transactionId): ?int`
- `isExpired(string $transactionId, int $expiryMinutes): bool`

---

## Subscription Usage Tracker

**Location**: `app/Libraries/SubscriptionUsageTracker.php`

### Purpose
Real-time tracking of feature usage against subscription limits with analytics.

### Usage

```php
use App\Libraries\SubscriptionUsageTracker;

$tracker = new SubscriptionUsageTracker();

// Track usage
$tracker->trackUsage($userId, 'License_Creation', 1);

// Check limits before allowing action
$result = $tracker->checkUsageLimit($userId, 'License_Creation', 5);
if ($result['can_use']) {
    // Allow the action
    $tracker->trackUsage($userId, 'License_Creation', 5);
}

// Get usage statistics
$stats = $tracker->getUserUsageStats($userId, ['License_Creation'], 30);

// Get usage trends
$trend = $tracker->getUsageTrend($userId, 'License_Creation', 30);

// Find users approaching limits
$approachingLimits = $tracker->getUsersApproachingLimits(0.8); // 80% threshold
```

### Methods

#### Core Tracking
- `trackUsage(int $userId, string $featureName, int $usageCount, array $metadata): bool`
- `checkUsageLimit(int $userId, string $featureName, int $requestedUsage): array`
- `getCurrentUsage(int $userId, string $featureName): int`

#### Analytics
- `getUserUsageStats(int $userId, array $features, int $days): array`
- `getUsageTrend(int $userId, string $featureName, int $days): array`
- `getUsersApproachingLimits(float $threshold): array`
- `getBillingPeriodUsage(int $userId, string $startDate, string $endDate): array`

### Database Schema
Uses `subscription_usage_tracking` table with daily usage aggregation.

---

## Payment Retry Manager

**Location**: `app/Libraries/PaymentRetryManager.php`

### Purpose
Automated payment retry with exponential backoff and dunning management.

### Usage

```php
use App\Libraries\PaymentRetryManager;

$retryManager = new PaymentRetryManager();

// Schedule retry for failed payment
$result = $retryManager->scheduleRetry(
    $subscriptionId,
    'payment_failed',
    ['message' => 'Card declined', 'code' => '4001']
);

// Process all pending retries (cronjob)
$results = $retryManager->processRetries();

// Get retry statistics
$stats = $retryManager->getRetryStatistics(30);
```

### Retry Configuration

```php
// Default retry intervals (hours)
const RETRY_INTERVALS = [
    1 => 24,   // 1st retry: 24 hours
    2 => 72,   // 2nd retry: 72 hours (3 days)
    3 => 168   // 3rd retry: 168 hours (7 days)
];

// Retry reasons and limits
const RETRY_REASONS = [
    'payment_failed' => ['max_retries' => 3, 'auto_suspend' => true],
    'insufficient_funds' => ['max_retries' => 4, 'auto_suspend' => false],
    'card_expired' => ['max_retries' => 2, 'auto_suspend' => true],
    'network_error' => ['max_retries' => 5, 'auto_suspend' => false],
    'gateway_timeout' => ['max_retries' => 3, 'auto_suspend' => false]
];
```

### Methods

#### Core Functionality
- `scheduleRetry(int $subscriptionId, string $reason, array $errorDetails): array`
- `processRetries(): array`

#### Statistics
- `getRetryStatistics(int $days): array`

### Integration
- Works with subscription state machine
- Sends automated customer notifications
- Integrates with payment method factory

---

## Webhook Security Manager

**Location**: `app/Libraries/WebhookSecurityManager.php`

### Purpose
Enhanced security for webhook processing with rate limiting, IP whitelisting, and signature verification.

### Usage

```php
use App\Libraries\WebhookSecurityManager;

$security = new WebhookSecurityManager();

// Validate webhook
$result = $security->validateWebhook(
    $headers,
    $body,
    $_SERVER['REMOTE_ADDR'],
    'paypal'
);

if ($result['valid']) {
    // Process webhook
    $validationId = $result['details']['validation_id'];
} else {
    // Reject webhook
    log_message('warning', 'Webhook rejected: ' . $result['message']);
}
```

### Security Features

#### Rate Limiting
- PayPal/Stripe: 100 requests per 5 minutes
- Default: 50 requests per 5 minutes
- IP-based rate limiting with cache

#### IP Whitelisting
```php
const IP_WHITELIST = [
    'paypal' => [
        '173.0.80.0/20',
        '64.4.240.0/21',
        // ... PayPal IP ranges
    ],
    'stripe' => [
        '54.187.174.169/32',
        // ... Stripe IP ranges
    ]
];
```

#### Validation Chain
1. Rate limiting check
2. IP whitelist verification
3. Timestamp validation (5-minute tolerance)
4. Signature verification
5. Duplicate detection

### Methods

#### Core Validation
- `validateWebhook(array $headers, string $body, string $sourceIP, string $provider): array`

#### Statistics
- `getSecurityStats(string $provider, int $hours): array`

---

## Enhanced Subscription Checker

**Location**: `app/Libraries/SubscriptionChecker.php` (Enhanced)

### New Features
Integration with usage tracking for comprehensive limit enforcement.

### Usage

```php
use App\Libraries\SubscriptionChecker;

$checker = new SubscriptionChecker();

// Check and track usage in one call
$result = $checker->checkAndTrackUsage($userId, 'License_Creation', 1, true);

if ($result['can_use']) {
    // Feature allowed and usage tracked
    echo "Remaining: " . $result['remaining'];
} else {
    // Feature blocked
    echo "Limit exceeded: " . $result['reason'];
}

// Traditional limit checking (backward compatible)
$canUse = $checker->checkLimit($userId, 'Feature_Name', 'value', $currentCount);
```

### Methods

#### Enhanced Methods
- `checkAndTrackUsage(int $userId, string $featureName, int $usageAmount, bool $trackUsage): array`

#### Existing Methods (Unchanged)
- `getLimit(int $userId, string $featureName, string $limitKey): ?int`
- `checkLimit(int $userId, string $featureName, string $limitKey, int $currentCount): ?bool`
- `isFeatureEnabled(int $userId, string $featureName): bool`

---

## Database Schema Changes

### New Tables

#### `subscription_usage_tracking`
```sql
CREATE TABLE `subscription_usage_tracking` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `subscription_id` int UNSIGNED NOT NULL,
  `feature_name` varchar(100) NOT NULL,
  `usage_count` int UNSIGNED NOT NULL DEFAULT 0,
  `limit_value` int UNSIGNED DEFAULT NULL,
  `usage_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_daily_usage` (`user_id`, `subscription_id`, `feature_name`, `usage_date`),
  KEY `idx_user_feature_date` (`user_id`, `feature_name`, `usage_date`),
  CONSTRAINT `fk_usage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);
```

#### `subscription_state_log`
```sql
CREATE TABLE `subscription_state_log` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `subscription_id` int UNSIGNED NOT NULL,
  `old_status` enum('active','cancelled','expired','pending','failed','suspended') DEFAULT NULL,
  `new_status` enum('active','cancelled','expired','pending','failed','suspended') NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `changed_by` int UNSIGNED DEFAULT NULL,
  `change_source` enum('user','admin','system','webhook','cronjob') NOT NULL DEFAULT 'system',
  `metadata` JSON DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_subscription_log` (`subscription_id`),
  CONSTRAINT `fk_state_log_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE
);
```

### Enhanced Indexes
```sql
-- Performance optimizations
KEY `idx_user_status` (`user_id`, `subscription_status`),
KEY `idx_next_payment_status` (`next_payment_date`, `subscription_status`),
KEY `idx_created_at` (`created_at`),

-- Payment tracking
KEY `idx_payments_subscription_status` (`subscription_id`, `payment_status`),
KEY `idx_payments_transaction_id` (`transaction_id`),
```

### Triggers
```sql
-- Prevent multiple active subscriptions per user
CREATE TRIGGER prevent_multiple_active_subscriptions
BEFORE INSERT ON subscriptions
FOR EACH ROW
BEGIN
    -- Validation logic
END;
```

---

## Integration Examples

### Complete Subscription Creation Flow

```php
use App\Libraries\PaymentMethodFactory;
use App\Libraries\TransactionIdManager;
use App\Libraries\SubscriptionStateMachine;

// 1. Create payment service securely
$factory = new PaymentMethodFactory();
$paymentService = $factory->create('PayPal');

// 2. Generate standardized transaction ID
$transactionId = TransactionIdManager::generateSubscription('PAYPAL', true);

// 3. Create subscription with payment service
$subscriptionResult = $paymentService->newSubscription($packageId);

// 4. Use state machine for status updates
$stateMachine = new SubscriptionStateMachine();
$stateMachine->transitionTo($subscriptionId, 'active', 'Payment completed', 'webhook');
```

### Usage Tracking Integration

```php
use App\Libraries\SubscriptionChecker;

$checker = new SubscriptionChecker();

// Check and track license creation
$result = $checker->checkAndTrackUsage($userId, 'License_Creation', 1);

if ($result['can_use']) {
    // Create license
    createLicense($userId, $licenseData);

    // Usage automatically tracked
    echo "License created. Remaining: " . $result['remaining'];
} else {
    // Show upgrade prompt
    showUpgradePrompt($result['limit'], $result['current_usage']);
}
```

### Payment Retry Handling

```php
use App\Libraries\PaymentRetryManager;

// In webhook handler
if ($paymentFailed) {
    $retryManager = new PaymentRetryManager();
    $retryResult = $retryManager->scheduleRetry(
        $subscriptionId,
        'payment_failed',
        ['gateway_response' => $gatewayResponse]
    );

    if ($retryResult['success']) {
        log_message('info', "Retry scheduled for " . $retryResult['next_retry_date']);
    }
}

// In cronjob
public function process_payment_retries()
{
    $retryManager = new PaymentRetryManager();
    $results = $retryManager->processRetries();

    return json_encode([
        'processed' => $results['processed'],
        'successful' => $results['successful'],
        'failed' => $results['failed']
    ]);
}
```

### Webhook Security Implementation

```php
use App\Libraries\WebhookSecurityManager;

public function handlePayPalWebhook()
{
    $headers = getallheaders();
    $body = file_get_contents('php://input');
    $sourceIP = $_SERVER['REMOTE_ADDR'];

    $security = new WebhookSecurityManager();
    $validation = $security->validateWebhook($headers, $body, $sourceIP, 'paypal');

    if (!$validation['valid']) {
        http_response_code(403);
        return json_encode(['error' => $validation['message']]);
    }

    // Process webhook safely
    $this->processPayPalWebhook($body);
}
```

---

## Best Practices

### Error Handling
- Always check return values from library methods
- Use comprehensive logging for debugging
- Implement graceful degradation for non-critical failures

### Security
- Always use PaymentMethodFactory instead of direct instantiation
- Validate all webhook requests with WebhookSecurityManager
- Use TransactionIdManager for consistent ID generation

### Performance
- Use bulk operations for cronjob processing
- Implement proper caching for frequently accessed data
- Monitor usage tracking performance with large datasets

### Monitoring
- Track state machine transitions for audit trails
- Monitor retry success rates and adjust configurations
- Use usage analytics for capacity planning

---

## Troubleshooting

### Common Issues

#### PaymentMethodFactory Returns Null
- Check if payment method is in whitelist
- Verify payment service configuration
- Check logs for detailed error messages

#### State Machine Transition Fails
- Verify transition is valid according to state matrix
- Check database constraints and foreign keys
- Review subscription state log for history

#### Usage Tracking Performance
- Ensure proper indexing on usage_date and user_id
- Consider archiving old usage data
- Monitor daily aggregation performance

#### Webhook Validation Fails
- Check IP whitelist configuration
- Verify timestamp tolerance settings
- Review rate limiting thresholds

### Debug Commands

```bash
# Check subscription state history
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "SubscriptionStateMachine"

# Monitor webhook security
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "WebhookSecurity"

# Track usage analytics
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "SubscriptionUsageTracker"
```