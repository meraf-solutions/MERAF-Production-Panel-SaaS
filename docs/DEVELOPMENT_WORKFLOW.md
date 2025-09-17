# Development Workflow Guide

This document outlines the enhanced development workflow for the MERAF Production Panel SaaS, incorporating the new subscription system improvements and best practices.

## Table of Contents

1. [Development Setup](#development-setup)
2. [Code Architecture Guidelines](#code-architecture-guidelines)
3. [Subscription System Development](#subscription-system-development)
4. [Security Best Practices](#security-best-practices)
5. [Testing Guidelines](#testing-guidelines)
6. [Database Management](#database-management)
7. [Performance Optimization](#performance-optimization)
8. [Deployment Process](#deployment-process)

---

## Development Setup

### Prerequisites
- PHP 8.1+ with required extensions (intl, mbstring, curl, json, mysqli)
- Composer for dependency management
- MySQL 8.0+ or MariaDB 10.3+
- Node.js (for frontend assets if applicable)

### Environment Configuration

1. **Database Setup**
```bash
# Import the enhanced schema
mysql -u username -p database_name < public/install-2nh98/assets/install.sql
```

2. **Environment Variables**
```bash
# Copy and configure environment
cp env.example .env

# Key configurations
CI_ENVIRONMENT = development
database.default.hostname = localhost
database.default.database = your_database
database.default.username = your_username
database.default.password = your_password
```

3. **Dependencies**
```bash
composer install
```

### IDE Configuration
- Use PSR-4 autoloading standards
- Configure code formatting with PHP-CS-Fixer
- Enable CodeIgniter 4 code completion

---

## Code Architecture Guidelines

### Directory Structure Adherence

```
app/
├── Controllers/           # HTTP request handlers
│   ├── Admin/            # Admin-specific controllers
│   └── Api.php           # API endpoints
├── Libraries/            # Business logic libraries
│   ├── PaymentMethodFactory.php
│   ├── SubscriptionStateMachine.php
│   ├── TransactionIdManager.php
│   ├── SubscriptionUsageTracker.php
│   ├── PaymentRetryManager.php
│   └── WebhookSecurityManager.php
├── Models/               # Database interaction layer
├── Modules/              # Payment provider modules
│   ├── PayPal/
│   └── Offline/
└── Views/                # Template files
```

### Coding Standards

1. **Use Factory Pattern for Payment Services**
```php
// ❌ Direct instantiation (vulnerable)
$paymentService = $this->ModuleScanner->loadLibrary($methodName, $serviceName);

// ✅ Secure factory pattern
$factory = new PaymentMethodFactory();
$paymentService = $factory->create($methodName);
```

2. **Use State Machine for Status Changes**
```php
// ❌ Direct database updates
$this->SubscriptionModel->update($id, ['subscription_status' => 'active']);

// ✅ Validated state transitions
$stateMachine = new SubscriptionStateMachine();
$stateMachine->transitionTo($id, 'active', 'Payment completed', 'webhook');
```

3. **Standardized Transaction IDs**
```php
// ❌ Manual ID generation
$transactionId = 'TXN_' . uniqid();

// ✅ Standardized format
$transactionId = TransactionIdManager::generatePayment('PAYPAL', false);
```

### Namespace Conventions
- All new libraries in `App\Libraries` namespace
- Payment modules in `App\Modules\{Provider}\Libraries`
- Follow PSR-4 autoloading standards

---

## Subscription System Development

### Creating New Payment Methods

1. **Whitelist the Method**
```php
// In PaymentMethodFactory.php
private static $allowedMethods = [
    'NewMethod' => [
        'service_name' => 'NewMethodService',
        'namespace' => 'App\\Modules\\NewMethod\\Libraries\\',
        'class' => 'NewMethodService',
        'config_required' => true
    ]
];
```

2. **Implement Required Interface**
```php
class NewMethodService
{
    // Required methods
    public function newSubscription($packageId) { }
    public function getSubscription($subscriptionId) { }
    public function cancelSubscription($subscriptionId, $reason = '') { }

    // Optional methods
    public function suspendSubscription($subscriptionId, $reason = '') { }
    public function activateSubscription($subscriptionId, $reason = '') { }
}
```

3. **Use Standardized Components**
```php
class NewMethodService
{
    public function newSubscription($packageId)
    {
        // Generate standardized transaction ID
        $transactionId = TransactionIdManager::generateSubscription('NEWMETHOD', true);

        // Create subscription with proper state
        $subscriptionData = [
            'transaction_id' => $transactionId,
            'payment_method' => 'NewMethod',
            // ... other fields
        ];

        // Use state machine for status updates
        $stateMachine = new SubscriptionStateMachine();
        // ... implementation
    }
}
```

### Handling Subscription Events

1. **Status Changes**
```php
// Always use state machine
$stateMachine = new SubscriptionStateMachine();
$result = $stateMachine->transitionTo(
    $subscriptionId,
    $newStatus,
    $reason,
    $source, // 'user', 'admin', 'webhook', 'cronjob'
    $changedBy
);

if (!$result['success']) {
    log_message('error', 'Status transition failed: ' . $result['message']);
}
```

2. **Usage Tracking**
```php
// Check limits before allowing actions
$checker = new SubscriptionChecker();
$result = $checker->checkAndTrackUsage($userId, 'Feature_Name', $usageAmount);

if ($result['can_use']) {
    // Perform action
    performAction();
    // Usage automatically tracked
} else {
    // Show upgrade prompt
    return response(['error' => 'Feature limit exceeded']);
}
```

3. **Payment Failures**
```php
// In webhook handlers or payment processing
if ($paymentFailed) {
    $retryManager = new PaymentRetryManager();
    $retryManager->scheduleRetry($subscriptionId, $failureReason, $errorDetails);
}
```

### Webhook Implementation

1. **Security Validation**
```php
public function handleWebhook()
{
    $headers = getallheaders();
    $body = file_get_contents('php://input');
    $sourceIP = $_SERVER['REMOTE_ADDR'];

    $security = new WebhookSecurityManager();
    $validation = $security->validateWebhook($headers, $body, $sourceIP, 'provider');

    if (!$validation['valid']) {
        http_response_code(403);
        return json_encode(['error' => $validation['message']]);
    }

    // Process webhook
    $this->processWebhookData($body);
}
```

---

## Security Best Practices

### Input Validation

1. **Payment Method Validation**
```php
// Always validate payment methods
$factory = new PaymentMethodFactory();
if (!$factory->isAllowedMethod($paymentMethod)) {
    throw new InvalidArgumentException('Invalid payment method');
}
```

2. **Transaction ID Validation**
```php
// Validate transaction ID format
if (!TransactionIdManager::isValid($transactionId)) {
    log_message('warning', 'Invalid transaction ID format: ' . $transactionId);
    return false;
}
```

3. **Subscription Status Validation**
```php
// Use state machine for validation
$stateMachine = new SubscriptionStateMachine();
if (!$stateMachine->canTransition($currentStatus, $newStatus)) {
    return ['error' => 'Invalid status transition'];
}
```

### Webhook Security

1. **Always Validate Webhooks**
```php
// Required for all webhook handlers
$security = new WebhookSecurityManager();
$validation = $security->validateWebhook($headers, $body, $sourceIP, $provider);
```

2. **Rate Limiting Implementation**
- Automatic rate limiting in WebhookSecurityManager
- Configurable limits per payment provider
- IP-based blocking for abuse prevention

3. **Signature Verification**
- Provider-specific signature validation
- Timestamp validation to prevent replay attacks
- Duplicate detection with caching

### Database Security

1. **Use Prepared Statements**
- CodeIgniter 4 Query Builder provides automatic protection
- Never concatenate user input into SQL queries

2. **Transaction Safety**
```php
$this->db->transStart();
try {
    // Multiple database operations
    $this->db->transComplete();
} catch (Exception $e) {
    $this->db->transRollback();
    throw $e;
}
```

---

## Testing Guidelines

### Unit Testing

1. **Test New Libraries**
```php
class SubscriptionStateMachineTest extends CodeIgniter\Test\CIUnitTestCase
{
    public function testValidTransition()
    {
        $stateMachine = new SubscriptionStateMachine();
        $this->assertTrue($stateMachine->canTransition('pending', 'active'));
    }

    public function testInvalidTransition()
    {
        $stateMachine = new SubscriptionStateMachine();
        $this->assertFalse($stateMachine->canTransition('cancelled', 'active'));
    }
}
```

2. **Test Payment Factory**
```php
class PaymentMethodFactoryTest extends CodeIgniter\Test\CIUnitTestCase
{
    public function testSecureInstantiation()
    {
        $factory = new PaymentMethodFactory();
        $service = $factory->create('PayPal');
        $this->assertInstanceOf(PayPalService::class, $service);
    }

    public function testInvalidMethodThrowsException()
    {
        $factory = new PaymentMethodFactory();
        $this->expectException(InvalidArgumentException::class);
        $factory->create('InvalidMethod');
    }
}
```

### Integration Testing

1. **Subscription Flow Testing**
```php
public function testCompleteSubscriptionFlow()
{
    // Create subscription
    $factory = new PaymentMethodFactory();
    $service = $factory->create('Trial');
    $result = $service->newSubscription($packageId);

    // Verify state machine logging
    $stateMachine = new SubscriptionStateMachine();
    $history = $stateMachine->getStateHistory($subscriptionId);
    $this->assertGreaterThan(0, count($history));
}
```

2. **Usage Tracking Testing**
```php
public function testUsageTracking()
{
    $tracker = new SubscriptionUsageTracker();

    // Track usage
    $result = $tracker->trackUsage($userId, 'TestFeature', 1);
    $this->assertTrue($result);

    // Verify tracking
    $usage = $tracker->getCurrentUsage($userId, 'TestFeature');
    $this->assertEquals(1, $usage);
}
```

### Database Testing

1. **Use Test Database**
```php
// In test configuration
$default = [
    'hostname' => 'localhost',
    'database' => 'test_database',
    // ... other config
];
```

2. **Reset State Between Tests**
```php
protected function setUp(): void
{
    parent::setUp();
    $this->resetDatabase();
}
```

---

## Database Management

### Migration Strategy

1. **For New Installations**
- All improvements included in `install.sql`
- No migration needed

2. **For Existing Installations**
```sql
-- Add new tables
SOURCE public/install-2nh98/assets/install.sql;

-- Verify triggers
SHOW TRIGGERS;

-- Check indexes
SHOW INDEX FROM subscriptions;
```

### Performance Monitoring

1. **Query Optimization**
```sql
-- Monitor slow queries
SHOW PROCESSLIST;

-- Analyze subscription queries
EXPLAIN SELECT * FROM subscriptions WHERE user_id = ? AND subscription_status = 'active';
```

2. **Index Usage**
```sql
-- Check index efficiency
SELECT * FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_NAME = 'subscriptions';
```

### Data Integrity

1. **Constraint Validation**
```sql
-- Verify foreign key constraints
SELECT * FROM information_schema.KEY_COLUMN_USAGE
WHERE CONSTRAINT_NAME LIKE 'fk_%';
```

2. **Trigger Verification**
```sql
-- Test trigger functionality
INSERT INTO subscriptions (user_id, subscription_status) VALUES (1, 'active');
-- Should prevent duplicate active subscriptions
```

---

## Performance Optimization

### Database Performance

1. **Query Optimization**
- Use composite indexes for common queries
- Avoid N+1 query problems in controllers
- Use proper JOIN statements instead of multiple queries

2. **Connection Pooling**
```php
// Use persistent connections for high-traffic scenarios
$config['DBDriver'] = 'MySQLi';
$config['pConnect'] = true;
```

### Caching Strategy

1. **Payment Plan Caching**
```php
// Enhanced PayPal plan caching with status validation
public function getPlanId($packageId)
{
    $cacheKey = "paypal_plan_{$packageId}";
    $planId = cache()->get($cacheKey);

    if ($planId) {
        // Verify plan is still active
        if ($this->validatePlanStatus($planId)) {
            return $planId;
        }
        cache()->delete($cacheKey);
    }

    // Create new plan and cache
    $planId = $this->createPlan($packageId);
    cache()->save($cacheKey, $planId, 3600);
    return $planId;
}
```

2. **Usage Tracking Optimization**
```php
// Cache usage data for high-frequency checks
$usage = cache()->remember("usage_{$userId}_{$featureName}", 300, function() use ($userId, $featureName) {
    return $this->getCurrentUsage($userId, $featureName);
});
```

### Memory Management

1. **Large Dataset Processing**
```php
// Use chunking for bulk operations
$subscriptions = $this->SubscriptionModel->findAll();
foreach (array_chunk($subscriptions, 100) as $chunk) {
    $this->processBatch($chunk);
}
```

---

## Deployment Process

### Pre-deployment Checklist

1. **Code Quality**
```bash
# Run syntax checks
find app/ -name "*.php" -exec php -l {} \;

# Run tests
./vendor/bin/phpunit

# Check code standards
./vendor/bin/php-cs-fixer fix --dry-run
```

2. **Database Validation**
```bash
# Verify schema
mysql -u user -p database < public/install-2nh98/assets/install.sql

# Check triggers and constraints
mysql -u user -p database -e "SHOW TRIGGERS;"
```

3. **Configuration Review**
- Verify payment method configurations
- Check webhook URLs and security settings
- Validate environment variables

### Deployment Steps

1. **Backup Current System**
```bash
# Database backup
mysqldump -u user -p database > backup_$(date +%Y%m%d_%H%M%S).sql

# Code backup
tar -czf code_backup_$(date +%Y%m%d_%H%M%S).tar.gz app/
```

2. **Deploy Code**
```bash
# Update codebase
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Clear caches
rm -rf writable/cache/*
```

3. **Database Migration**
```bash
# For new installations
mysql -u user -p database < public/install-2nh98/assets/install.sql

# For existing installations
# Run specific migration scripts if needed
```

4. **Post-deployment Validation**
```bash
# Test critical endpoints
curl -X POST /cronjob/check_subscription_expiry
curl -X POST /cronjob/process_payment_retries

# Verify webhook endpoints
curl -X POST /webhook/paypal -H "Content-Type: application/json" -d '{}'
```

### Rollback Plan

1. **Code Rollback**
```bash
# Revert to previous version
git checkout previous_release_tag

# Restore dependencies
composer install
```

2. **Database Rollback**
```bash
# Restore database from backup
mysql -u user -p database < backup_file.sql
```

### Monitoring

1. **Log Monitoring**
```bash
# Monitor application logs
tail -f writable/logs/log-$(date +%Y-%m-%d).log

# Monitor specific components
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "SubscriptionStateMachine"
```

2. **Performance Monitoring**
```sql
-- Monitor database performance
SHOW PROCESSLIST;

-- Check slow queries
SELECT * FROM mysql.slow_log WHERE start_time > NOW() - INTERVAL 1 HOUR;
```

3. **Error Tracking**
```bash
# Monitor error logs
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "ERROR"

# Monitor webhook failures
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "WebhookSecurity"
```

---

## Best Practices Summary

### Development
- Always use factory pattern for payment services
- Implement proper state machine transitions
- Follow standardized transaction ID formats
- Use comprehensive error logging

### Security
- Validate all webhook requests
- Use whitelisted payment methods only
- Implement proper rate limiting
- Follow secure coding practices

### Performance
- Use proper database indexing
- Implement efficient caching strategies
- Monitor query performance
- Optimize for high-traffic scenarios

### Testing
- Write comprehensive unit tests
- Test integration flows end-to-end
- Use proper test data isolation
- Validate security measures

### Deployment
- Follow systematic deployment process
- Maintain comprehensive backups
- Implement proper rollback procedures
- Monitor system health post-deployment

This workflow ensures consistent, secure, and maintainable development of the subscription system while leveraging all the enhanced features and security improvements.