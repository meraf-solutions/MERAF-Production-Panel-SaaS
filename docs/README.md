# MERAF Production Panel SaaS Documentation

Welcome to the comprehensive documentation for the MERAF Production Panel SaaS subscription system. This documentation covers the enhanced subscription and payment system with advanced security features.

## Quick Navigation

### 📋 Core Documentation
- **[CLAUDE.md](../CLAUDE.md)** - Primary development guidance and architecture overview
- **[SUBSCRIPTION_API.md](SUBSCRIPTION_API.md)** - Complete API documentation for subscription libraries
- **[DEVELOPMENT_WORKFLOW.md](DEVELOPMENT_WORKFLOW.md)** - Development guidelines and best practices

### 🚀 Getting Started

#### For New Developers
1. Read **[CLAUDE.md](../CLAUDE.md)** for project overview and architecture
2. Follow **[DEVELOPMENT_WORKFLOW.md](DEVELOPMENT_WORKFLOW.md)** for setup and coding standards
3. Reference **[SUBSCRIPTION_API.md](SUBSCRIPTION_API.md)** for detailed API usage

#### For Existing Developers
1. Review **Enhanced Development Guidelines** in [CLAUDE.md](../CLAUDE.md)
2. Update code to use new libraries per [DEVELOPMENT_WORKFLOW.md](DEVELOPMENT_WORKFLOW.md)
3. Implement security best practices from documentation

## 🏗️ Architecture Overview

### Enhanced Subscription System
The system now includes comprehensive improvements addressing:
- **Security vulnerabilities** (race conditions, injection attacks)
- **Performance bottlenecks** (database optimization, caching)
- **Data consistency** (state machines, audit trails)
- **Payment reliability** (retry mechanisms, webhook security)

### Key Components

#### Core Libraries (`app/Libraries/`)
- **PaymentMethodFactory** - Secure payment service instantiation
- **SubscriptionStateMachine** - Validated status transitions
- **TransactionIdManager** - Standardized transaction IDs
- **SubscriptionUsageTracker** - Real-time usage analytics
- **PaymentRetryManager** - Automated retry with backoff
- **WebhookSecurityManager** - Enhanced webhook security

#### Database Enhancements
- **New Tables**: `subscription_usage_tracking`, `subscription_state_log`
- **Performance Indexes**: Composite indexes for common queries
- **Race Condition Prevention**: Triggers and constraints
- **Audit Trails**: Complete subscription state change history

## 🔐 Security Features

### Payment Security
- **Whitelisted Payment Methods**: Prevents arbitrary class loading
- **Webhook Validation**: Rate limiting, IP whitelisting, signature verification
- **Transaction ID Security**: Structured format with validation
- **Replay Attack Prevention**: Timestamp validation and duplicate detection

### Database Security
- **Race Condition Prevention**: Database triggers prevent duplicate subscriptions
- **Data Integrity**: CHECK constraints and foreign key relationships
- **Audit Logging**: Complete trail of all subscription changes

## 📊 Performance & Analytics

### Real-time Usage Tracking
- Daily feature usage analytics
- Limit enforcement and overage detection
- Billing integration for usage-based pricing
- Trend analysis and capacity planning

### Database Performance
- Optimized composite indexes for subscription queries
- Efficient query patterns reducing N+1 problems
- Enhanced caching with validation

## 🛠️ Development Guidelines

### Required Patterns

#### ✅ Use PaymentMethodFactory
```php
$factory = new \App\Libraries\PaymentMethodFactory();
$paymentService = $factory->create('PayPal');
```

#### ✅ Use SubscriptionStateMachine
```php
$stateMachine = new \App\Libraries\SubscriptionStateMachine();
$stateMachine->transitionTo($id, 'active', 'Payment completed', 'webhook');
```

#### ✅ Use TransactionIdManager
```php
$transactionId = \App\Libraries\TransactionIdManager::generateSubscription('PAYPAL', false);
```

#### ✅ Validate Webhooks
```php
$security = new \App\Libraries\WebhookSecurityManager();
$validation = $security->validateWebhook($headers, $body, $sourceIP, 'paypal');
```

### Deprecated Patterns

#### ❌ Direct Payment Service Instantiation
```php
// DEPRECATED - Security vulnerability
$paymentService = $this->ModuleScanner->loadLibrary($methodName, $serviceName);
```

#### ❌ Direct Database Status Updates
```php
// DEPRECATED - No validation or logging
$this->SubscriptionModel->update($id, ['subscription_status' => 'active']);
```

## 📚 API Reference

### Quick Reference

#### PaymentMethodFactory
```php
$factory = new PaymentMethodFactory();
$service = $factory->create('PayPal');           // Create service
$allowed = $factory->isAllowedMethod('PayPal');  // Check if allowed
$methods = $factory->getAvailableMethods();      // Get all available
```

#### SubscriptionStateMachine
```php
$stateMachine = new SubscriptionStateMachine();
$result = $stateMachine->transitionTo($id, $status, $reason, $source);
$canTransition = $stateMachine->canTransition('pending', 'active');
$history = $stateMachine->getStateHistory($subscriptionId);
```

#### SubscriptionUsageTracker
```php
$tracker = new SubscriptionUsageTracker();
$tracker->trackUsage($userId, 'Feature_Name', 1);
$result = $tracker->checkUsageLimit($userId, 'Feature_Name', 5);
$stats = $tracker->getUserUsageStats($userId, ['Feature_Name'], 30);
```

#### WebhookSecurityManager
```php
$security = new WebhookSecurityManager();
$validation = $security->validateWebhook($headers, $body, $sourceIP, 'paypal');
```

## 🧪 Testing

### Test Coverage Requirements
- **Unit Tests**: All new libraries with mocked dependencies
- **Integration Tests**: Complete subscription flows
- **Security Tests**: Webhook validation and payment method security
- **Performance Tests**: Usage tracking under load

### Example Test
```php
public function testSecureSubscriptionCreation()
{
    $factory = new PaymentMethodFactory();
    $service = $factory->create('Trial');
    $result = $service->newSubscription($packageId);

    $this->assertTrue($result);

    // Verify state machine logging
    $stateMachine = new SubscriptionStateMachine();
    $history = $stateMachine->getStateHistory($subscriptionId);
    $this->assertGreaterThan(0, count($history));
}
```

## 🚀 Deployment

### Pre-deployment Checklist
- [ ] Run PHP syntax checks: `find app/ -name "*.php" -exec php -l {} \;`
- [ ] Execute test suite: `./vendor/bin/phpunit`
- [ ] Verify database schema
- [ ] Check payment method configurations
- [ ] Validate webhook endpoints

### Database Migration
```bash
# For new installations
mysql -u user -p database < public/install-2nh98/assets/install.sql

# Verify triggers and constraints
mysql -u user -p database -e "SHOW TRIGGERS;"
```

## 📞 Support & Troubleshooting

### Common Issues

#### PaymentMethodFactory Returns Null
- Check if payment method is whitelisted
- Verify service configuration
- Review error logs

#### State Machine Transition Fails
- Verify transition validity
- Check subscription exists
- Review state change history

#### Usage Tracking Performance
- Monitor database query performance
- Check index usage
- Consider data archiving

### Debug Commands
```bash
# Monitor subscription system
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "SubscriptionStateMachine"

# Monitor webhook security
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "WebhookSecurity"

# Track usage analytics
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "SubscriptionUsageTracker"
```

## 📈 What's New

### Recent Enhancements (January 2025)
- **Enhanced Security**: Payment method whitelisting, webhook validation
- **Performance Optimization**: Database indexes, caching improvements
- **Audit Trails**: Complete subscription state change logging
- **Usage Analytics**: Real-time feature usage tracking
- **Retry Mechanisms**: Automated payment retry with exponential backoff
- **Race Condition Prevention**: Database-level constraints

### Breaking Changes
- PaymentMethodFactory replaces direct ModuleScanner usage
- SubscriptionModel::updateSubscriptionStatus() requires additional parameters
- Transaction ID format standardization may require migration

---

*This documentation is generated and maintained as part of the MERAF Production Panel SaaS development process. For updates and contributions, please follow the development workflow guidelines.*