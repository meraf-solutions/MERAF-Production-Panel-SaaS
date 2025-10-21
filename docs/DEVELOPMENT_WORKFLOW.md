# Development Workflow Guide

This document outlines the essential development workflow for the MERAF Production Panel SaaS, focusing on practical development processes and team collaboration.

**For detailed information, see:**
- **Architecture**: [architecture.md](architecture.md) - System design and multi-tenant architecture
- **Technical Implementation**: [technical.md](technical.md) - Code implementation details
- **Development Guidelines**: [../CLAUDE.md](../CLAUDE.md) - Coding patterns and best practices
- **API Documentation**: [api.md](api.md) - Complete API reference
- **Subscription Libraries**: [subscription_api.md](subscription_api.md) - Business logic libraries

## Table of Contents

1. [Quick Start](#quick-start)
2. [Development Best Practices](#development-best-practices)
3. [Code Review Process](#code-review-process)
4. [Testing Strategy](#testing-strategy)
5. [Deployment Workflow](#deployment-workflow)

---

## Quick Start

### Environment Setup
```bash
# 1. Clone and install dependencies
git clone <repository-url> meraf-panel-saas
cd meraf-panel-saas
composer install

# 2. Configure environment
cp env .env
# Edit .env with your database credentials

# 3. Run installation
# Navigate to https://yourdomain.com/install/

# 4. Start development
./spark serve
```

### Essential Commands
```bash
# Development server
./spark serve

# Database operations
./spark migrate
./spark db:seed

# Testing
./phpunit

# Clear cache
./spark cache:clear
```

---

## Development Best Practices

### Subscription System Development

**Always use the enhanced libraries:**
```php
// ✅ Secure payment method creation
$factory = new PaymentMethodFactory();
$paymentService = $factory->create('PayPal');

// ✅ Validated status transitions
$stateMachine = new SubscriptionStateMachine();
$stateMachine->transitionTo($id, 'active', 'Payment completed', 'webhook');

// ✅ Standardized transaction IDs
$transactionId = TransactionIdManager::generateSubscription('PAYPAL', false);
```

### Security Requirements

**Always validate and secure:**
- Use `PaymentMethodFactory` for payment service instantiation
- Validate all webhooks with `WebhookSecurityManager`
- Check subscription limits with `SubscriptionChecker->checkAndTrackUsage()`
- Use timing-safe authentication for all API operations

### API Development

**For subscription API endpoints:**
1. Require `User-API-Key` header authentication
2. Scope all data queries with `owner_id` for tenant isolation
3. Return consistent JSON structure with `status` and `data` fields
4. Log all API access for analytics and security

---

## Code Review Process

### Required Checks

**Before submitting PR:**
```bash
# 1. Run syntax checks
find app/ -name "*.php" -exec php -l {} \;

# 2. Run tests
./phpunit

# 3. Check code standards
./vendor/bin/php-cs-fixer fix --dry-run
```

### Review Criteria

**Must verify:**
- [ ] Uses secure factory patterns for payment services
- [ ] Implements proper state machine transitions
- [ ] Includes comprehensive error handling
- [ ] Has appropriate test coverage
- [ ] Follows tenant isolation patterns
- [ ] Uses standardized transaction ID formats

---

## Testing Strategy

### Test Types

**Unit Tests**
```php
// Test business logic libraries
./phpunit tests/Libraries/

// Test models with tenant isolation
./phpunit tests/Models/
```

**Integration Tests**
```php
// Test complete subscription flows
./phpunit tests/Integration/SubscriptionTest.php

// Test API endpoints with authentication
./phpunit tests/Integration/ApiTest.php
```

### Test Database

```php
// Use separate test database
$config['tests'] = [
    'hostname' => 'localhost',
    'database' => 'test_database',
    // ... other config
];
```

---

## Deployment Workflow

### Pre-deployment Checklist

```bash
# 1. Code quality checks
./vendor/bin/phpunit
./vendor/bin/php-cs-fixer fix --dry-run

# 2. Database validation
mysql -u user -p database < public/install/assets/install.sql

# 3. Configuration review
# - Verify payment method configurations
# - Check webhook URLs and security settings
# - Validate environment variables
```

### Deployment Steps

```bash
# 1. Backup current system
mysqldump -u user -p database > backup_$(date +%Y%m%d_%H%M%S).sql
tar -czf code_backup_$(date +%Y%m%d_%H%M%S).tar.gz app/

# 2. Deploy code
git pull origin main
composer install --no-dev --optimize-autoloader
rm -rf writable/cache/*

# 3. Database migration (if needed)
# For new installations: mysql -u user -p database < public/install/assets/install.sql
# For existing: run specific migration scripts

# 4. Verify deployment
curl -X GET /subscription/status -H "User-API-Key: TEST123"
```

### Post-deployment Monitoring

```bash
# Monitor application logs
tail -f writable/logs/log-$(date +%Y-%m-%d).log

# Monitor subscription system
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "SubscriptionStateMachine"

# Monitor webhook security
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "WebhookSecurity"
```

### Rollback Plan

```bash
# 1. Revert code
git checkout previous_release_tag
composer install

# 2. Restore database
mysql -u user -p database < backup_file.sql
```

---

## Team Collaboration

### Git Workflow

```bash
# 1. Create feature branch
git checkout -b feature/subscription-api-endpoints

# 2. Implement changes following guidelines
# - Use secure factory patterns
# - Implement proper error handling
# - Add comprehensive tests

# 3. Run quality checks
./phpunit
./vendor/bin/php-cs-fixer fix

# 4. Create pull request
git push origin feature/subscription-api-endpoints
```

### Documentation Updates

**Always update documentation when:**
- Adding new API endpoints → Update `docs/api.md`
- Adding new libraries → Update `docs/subscription_api.md`
- Changing architecture → Update `docs/architecture.md`
- Modifying development patterns → Update `CLAUDE.md`

### Issue Tracking

**Use clear issue titles:**
- `[BUG] Dashboard data endpoint returns array key errors`
- `[FEATURE] Add subscription usage analytics API`
- `[SECURITY] Implement webhook signature validation`

This streamlined workflow focuses on practical development processes while referencing comprehensive documentation in other files for detailed technical information.