# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the MERAF Production Panel SaaS, a CodeIgniter 4-based multi-tenant web application for managing digital licenses as a service. The application handles license validation, subscription management, user authentication, billing, and various administrative features for digital product management across multiple tenants with complete data isolation.

## Architecture

### SaaS Framework Structure
- **Framework**: CodeIgniter 4 (PHP 8.1+)
- **Architecture**: Multi-tenant MVC (Model-View-Controller) pattern
- **Main entry point**: `public/index.php` (web) and `spark` (CLI)
- **Configuration**: `app/Config/` directory contains all configuration files
- **Multi-tenancy**: Complete tenant isolation with `owner_id` foreign keys
- **Subscription System**: Integrated billing and package management

### SaaS Directory Structure
- `app/` - Multi-tenant application code
  - `Controllers/` - HTTP request handlers (Api.php, Home.php, Subscription.php, Cronjob.php)
  - `Models/` - Tenant-aware database layer with enhanced validation
    - Core Models: `SubscriptionModel.php`, `UserModel.php`, `PackageModel.php`
    - Payment Models: `SubscriptionPaymentModel.php`, `SubscriptionInvoiceModel.php`
  - `Libraries/` - **Enhanced business logic libraries**
    - `PaymentMethodFactory.php` - Secure payment service instantiation
    - `SubscriptionStateMachine.php` - Validated status transitions
    - `TransactionIdManager.php` - Standardized transaction IDs
    - `SubscriptionUsageTracker.php` - Real-time usage analytics
    - `PaymentRetryManager.php` - Automated retry with backoff
    - `WebhookSecurityManager.php` - Enhanced webhook security
    - `SubscriptionChecker.php` - Feature limit enforcement
    - `TrialService.php` - Trial subscription management
  - `Modules/` - **Payment provider modules**
    - `PayPal/Libraries/PayPalService.php` - Enhanced PayPal integration
    - `Offline/Libraries/OfflineService.php` - Manual payment processing
  - `Views/` - Multi-tenant template files organized by feature
  - `Config/` - Application configuration
  - `Database/Migrations/` - Multi-tenant database schema changes
  - `Database/Seeds/` - Test data seeding
  - `Helpers/` - Custom helper functions (including security_helper.php)
  - `Filters/` - Security filters (SecurityHeaders.php, APIThrottle.php, IPBlockFilter.php)
  - `Language/` - Internationalization files
- `docs/` - **Comprehensive documentation**
  - `SUBSCRIPTION_API.md` - Complete API documentation
  - `DEVELOPMENT_WORKFLOW.md` - Development guidelines
- `system/` - CodeIgniter 4 framework core (do not modify)
- `writable/` - Application logs, cache, uploads
- `user-data/` - Per-tenant data directories
  - `{user-id}/products/` - Product files and metadata
  - `{user-id}/email-templates/` - User-specific email templates
  - `{user-id}/settings/` - User configuration and variations
  - `{user-id}/logs/` - User-specific logs
- `tests/` - PHPUnit test files
- `vendor/` - Composer dependencies

### Key SaaS Controllers
- `Home.php` - Multi-tenant dashboard and subscription management
- `Api.php` - Multi-tenant API endpoints with User-API-Key authentication
- `AuthController.php` - Multi-tenant user authentication
- `SubscriptionController.php` - Billing and subscription management
- `Cronjob.php` - Scheduled task handlers for billing and maintenance

## Development Commands

### Dependency Management
```bash
composer install          # Install PHP dependencies
composer update           # Update dependencies
```

### Testing
```bash
# Run all tests
./phpunit
# or on Windows
vendor\bin\phpunit

# Run specific test directory
./phpunit app/Models

# Run tests with coverage
./phpunit --colors --coverage-text=tests/coverage.txt --coverage-html=tests/coverage/ -d memory_limit=1024m
```

### CLI Commands (Spark)
```bash
./spark                   # List available commands
./spark migrate           # Run database migrations
./spark db:seed           # Run database seeders
./spark cache:clear       # Clear application cache
./spark routes            # List all routes
```

### Automated Cronjob Tasks
```bash
# Subscription Management
/cronjob/check_subscription_expiry    # Process expired subscriptions
/cronjob/process_payment_retries      # Handle failed payment retries

# License Management
/cronjob/do_auto_key_expiry          # Auto-expire license keys
/cronjob/do_expiry_reminder          # Send expiry reminders

# Security & Maintenance
/cronjob/check_abusive_ips           # Monitor and block abusive IPs
/cronjob/clean_blocked_ips           # Clean old IP blocks
/cronjob/deleteOldEmailLogs          # Cleanup old email logs
```

### Code Quality
The project includes these development dependencies:
- **PHPUnit** for testing (version 10.5.16+ or 11.2+)
- **PHP-CS-Fixer** for code formatting (version 3.47.1+)
- **CodeIgniter Coding Standard** for style consistency
- **Kint** for debugging and development

## Database Configuration

Tests require database configuration in `app/Config/Database.php` under the 'tests' group. Set up a test database before running the full test suite.

### Enhanced Database Schema Features

#### Performance Optimizations
- **Optimized Indexes**: Composite indexes for common query patterns
  - `idx_user_status` on `(user_id, subscription_status)`
  - `idx_next_payment_status` on `(next_payment_date, subscription_status)`
  - `idx_payments_subscription_status` on subscription payments
- **Database Constraints**: CHECK constraints for data validation
- **Triggers**: MySQL triggers to prevent multiple active subscriptions per user

#### New Tables for Enhanced Functionality
- **`subscription_usage_tracking`**: Daily usage tracking per feature
- **`subscription_state_log`**: Comprehensive audit trail for status changes
- **Enhanced Indexes**: Performance-optimized for subscription queries

#### Data Integrity Features
- **Race Condition Prevention**: Database-level constraints prevent duplicate active subscriptions
- **Audit Trail**: Complete subscription state change history
- **Usage Analytics**: Granular tracking of feature usage against limits
- **Foreign Key Resolution**: Automatic system user creation for global settings
- **Constraint Validation**: CHECK constraints for data validation and integrity

#### Installation Schema Enhancements
- **System User Management**: Auto-creates user ID 0 for system-wide settings
- **Default Package Setup**: Pre-configured Super Admin and Trial packages
- **Module Categories**: License Management, Digital Product Management, Email Features
- **Package Modules**: Feature definitions with JSON configuration
- **Trigger Installation**: Graceful handling of database privilege limitations

## Key SaaS Features

### Multi-Tenant License Management System
- Tenant-isolated license validation and activation
- Device/domain registration tracking per tenant
- Email-based license distribution
- Complete data separation with `owner_id` scoping

### Subscription Management System
- Package-based billing (monthly/yearly)
- Usage tracking and limits enforcement
- Automated billing and payment processing
- Trial management and conversion tracking

### Multi-Tenant Authentication & Security
- User authentication with CodeIgniter Shield
- User-API-Key authentication (6-character alphanumeric)
- Dual authentication layers (Admin + Tenant)
- User-specific AES-256-GCM encryption
- Timing-safe authentication for security
- IP blocking functionality per tenant
- Session management with tenant isolation
- Role-based access control

### SaaS-Specific Features
- Complete tenant data isolation
- Per-tenant configuration via UserSettingsModel
- Usage analytics and resource monitoring
- Multi-tenant notification system
- Subscription billing integration
- Automated user directory setup with security protections
- Default email template extraction on first login

## Environment Setup

1. Ensure PHP 8.1+ with required extensions (intl, mbstring, etc.)
2. Set up database connection in `.env` or `app/Config/Database.php`
3. Configure application settings in `app/Config/` files
4. Set proper file permissions on `writable/` directory
5. Run `composer install` to install dependencies

## Installation System

### Enhanced Secure Installer
- **Location**: `public/install/` (auto-renamed after installation)
- **Security Features**: CSRF protection, rate limiting, input validation
- **Database Support**: MySQL 5.7+, handles hyphens/underscores in names
- **Privilege Management**: Graceful handling of limited database privileges
- **Trigger Support**: Automatically creates database triggers when possible

### Installation Process
1. **System Requirements Check**: PHP version, extensions, permissions
2. **Database Configuration**: Host, name, user credentials validation
3. **Email Setup**: SMTP, Sendmail, or PHP Mail configuration
4. **SQL Execution**: Robust parsing with trigger and delimiter support
5. **Security Setup**: Creates system user, default packages, security files

### Database Schema Setup
- **System User**: Creates user ID 0 for global settings
- **Default Packages**: Super Admin and Trial packages pre-configured
- **Foreign Key Handling**: Resolves constraint dependencies automatically
- **Index Optimization**: Performance-optimized indexes for subscriptions
- **Security Triggers**: Race condition prevention (when privileges allow)

### Installation Troubleshooting & Fixes

#### Database Name/Username Validation
- **Problem**: Database names/usernames with hyphens or underscores rejected
- **Solution**: Updated validation regex to accept `[a-zA-Z0-9_-]+` pattern
- **Files**: `install_secure.php`, `action_secure.php`

#### SQL Parsing & Trigger Support
- **Problem**: MySQL triggers with `DELIMITER` statements causing syntax errors
- **Solution**: Enhanced SQL parser with multi-line statement support
- **Features**:
  - Proper `DELIMITER` handling for trigger creation
  - Individual statement execution with trigger error isolation
  - Graceful fallback when database privileges insufficient

#### Foreign Key Constraint Resolution
- **Problem**: Settings table references missing system user (ID 0)
- **Solution**: Auto-creates system user before inserting settings
- **Implementation**:
  ```sql
  -- Create system user for global settings
  ALTER TABLE `users` AUTO_INCREMENT = 0;
  INSERT INTO `users` (id, username, ...) VALUES (0, 'system', ...);
  ALTER TABLE `users` AUTO_INCREMENT = 1;
  ```

#### Configuration Path Fixes
- **Problem**: Extra `tenant-data/` nesting in user directories
- **Solution**: Updated configuration paths to match expected structure
- **Paths**:
  - `userProductPath`: `'products/'`
  - `userEmailTemplatesPath`: `'email-templates/'`
  - `userLogsPath`: `'logs/'`
  - `userAppSettings`: `'settings/'`

## Development Notes

### Code Standards & Architecture
- All new controllers should extend `BaseController`
- Use CodeIgniter's built-in helpers and libraries when possible
- Follow PSR-4 autoloading conventions
- Database interactions should go through Models, not direct queries in Controllers
- Use migrations for database schema changes
- The application uses output compression for performance optimization

### Enhanced Development Guidelines

#### Payment Service Integration
- **ALWAYS use PaymentMethodFactory** instead of direct ModuleScanner instantiation
- **NEVER directly instantiate payment services** - use the secure factory pattern
- **Validate payment methods** using the whitelist before processing

```php
// ❌ DEPRECATED - Security vulnerability
$paymentService = $this->ModuleScanner->loadLibrary($methodName, $serviceName);

// ✅ REQUIRED - Secure factory pattern
$factory = new \App\Libraries\PaymentMethodFactory();
$paymentService = $factory->create($methodName);
```

#### Subscription Status Management
- **ALWAYS use SubscriptionStateMachine** for status transitions
- **NEVER directly update subscription_status** in database
- **Ensure proper logging** of all state changes

```php
// ❌ DEPRECATED - No validation or logging
$this->SubscriptionModel->update($id, ['subscription_status' => 'active']);

// ✅ REQUIRED - Validated transitions with audit trail
$stateMachine = new \App\Libraries\SubscriptionStateMachine();
$stateMachine->transitionTo($id, 'active', 'Payment completed', 'webhook');
```

#### Transaction ID Standardization
- **ALWAYS use TransactionIdManager** for ID generation
- **MAINTAIN consistent format** across all payment methods
- **Validate transaction IDs** before processing

```php
// ❌ DEPRECATED - Inconsistent formats
$transactionId = 'TXN_' . uniqid();

// ✅ REQUIRED - Standardized format
$transactionId = \App\Libraries\TransactionIdManager::generateSubscription('PAYPAL', false);
```

#### Usage Tracking & Limits
- **IMPLEMENT usage tracking** for all billable features
- **CHECK limits before allowing actions** using SubscriptionChecker
- **TRACK usage in real-time** with SubscriptionUsageTracker

```php
// ✅ REQUIRED - Check and track usage
$checker = new \App\Libraries\SubscriptionChecker();
$result = $checker->checkAndTrackUsage($userId, 'License_Creation', 1);

if ($result['can_use']) {
    // Perform action - usage automatically tracked
} else {
    // Show upgrade prompt
}
```

#### Error Handling & Retry Logic
- **IMPLEMENT retry mechanisms** for failed payments using PaymentRetryManager
- **MAKE email sending non-blocking** to prevent subscription failures
- **LOG comprehensive error details** for debugging

#### Webhook Security
- **VALIDATE ALL webhooks** using WebhookSecurityManager
- **IMPLEMENT rate limiting** and IP whitelisting
- **VERIFY signatures** and prevent replay attacks

```php
// ✅ REQUIRED - Secure webhook validation
$security = new \App\Libraries\WebhookSecurityManager();
$validation = $security->validateWebhook($headers, $body, $sourceIP, 'paypal');

if (!$validation['valid']) {
    http_response_code(403);
    return;
}
```

### Important SaaS Helper Functions
- `getMyConfig($key, $userID)` - Multi-tenant configuration loading from database
- `generateLicenseKey($userID)` - Secure tenant-specific license key generation
- `generateUserApiKey()` - 6-character User API key generation
- `encrypt_secret_key($plaintext, $userID)` - User-specific AES-256-GCM encryption
- `decrypt_secret_key($encrypted, $userID)` - User-specific decryption
- `timing_safe_equals($known, $user)` - Timing-safe string comparison
- `setMyTimezone()` - User timezone management
- `setMyLocale()` - Internationalization support

### User Initialization System

#### FirstLoginFilter Integration
- **Automatic Setup**: Triggers on first user login (`last_active` is null)
- **Directory Creation**: Creates secure user data directories
- **Template Extraction**: Extracts default email templates from zip
- **Key Generation**: Creates user-specific API keys and encryption keys
- **Security Setup**: Applies `.htaccess` protection and access restrictions

#### InitializeNewUser Library
- **Directory Structure**: Creates `products/`, `email-templates/`, `settings/`, `logs/`
- **Security Files**: Adds `.htaccess` and `index.php` protection files
- **Default Content**: Extracts `default_email_template_v1.0.0.zip`
- **JSON Configuration**: Sets up `product-variations.json` and `product-email-templates.json`
- **Permissions**: Applies secure file/directory permissions (0750/0755)

#### User Data Directory Structure
```
user-data/{userId}/
├── .htaccess                    # Security protection
├── index.php                   # Access denied script
├── products/                   # Product files and metadata
│   ├── .htaccess              # Directory protection
│   ├── index.php              # Access denied
│   ├── {product_name}/        # Product directories
│   └── {sha1_hash}.json       # Product metadata files
├── email-templates/           # User email templates
│   ├── .htaccess
│   ├── index.php
│   └── default_email_template/ # Extracted default template
├── settings/                  # User configuration
│   ├── .htaccess
│   ├── index.php
│   ├── product-variations.json
│   └── product-email-templates.json
└── logs/                     # User-specific logs
    ├── .htaccess
    └── index.php
```

### Advanced Subscription & Payment Libraries

#### Payment Method Factory (`PaymentMethodFactory`)
- Secure payment method instantiation with whitelisting
- Prevents arbitrary class loading vulnerabilities
- Validates payment service configuration
- Usage: `$factory = new PaymentMethodFactory(); $service = $factory->create('PayPal');`

#### Subscription State Machine (`SubscriptionStateMachine`)
- Enforces valid subscription status transitions
- Comprehensive logging of all state changes
- Prevents invalid status updates
- Usage: `$stateMachine->transitionTo($subscriptionId, 'active', 'Payment completed');`

#### Transaction ID Manager (`TransactionIdManager`)
- Standardized transaction ID generation across all payment methods
- Format: `[PREFIX]-[METHOD]-[TIMESTAMP]-[UNIQUE_ID]`
- Parsing and validation utilities
- Usage: `TransactionIdManager::generateSubscription('PAYPAL', false);`

#### Subscription Usage Tracker (`SubscriptionUsageTracker`)
- Real-time usage tracking against subscription limits
- Daily usage analytics and reporting
- Feature limit enforcement
- Usage: `$tracker->checkAndTrackUsage($userId, 'License_Creation', 1);`

#### Payment Retry Manager (`PaymentRetryManager`)
- Automated payment retry with exponential backoff
- Configurable retry limits per failure type
- Dunning management and customer notifications
- Usage: `$retryManager->scheduleRetry($subscriptionId, 'payment_failed');`

#### Webhook Security Manager (`WebhookSecurityManager`)
- Rate limiting and IP whitelisting for webhooks
- Signature verification and replay attack prevention
- Comprehensive security logging
- Usage: `$security->validateWebhook($headers, $body, $sourceIP, 'paypal');`

#### Subscription Checker (Enhanced)
- Feature-based access control
- Usage limit validation
- Integration with usage tracking
- Usage: `$checker->checkAndTrackUsage($userId, 'Feature_Name', 1);`

### SaaS Security Considerations
- All timestamps stored in UTC timezone
- Multi-tenant data isolation with `owner_id` foreign keys
- User-specific encryption keys for complete tenant separation
- IP blocking system for abuse prevention per tenant
- Tiered rate limiting (10/30/60 requests/minute by endpoint type)
- Multi-layer input validation and sanitization
- Dual authentication: Admin secret keys + User-API-Keys
- User-API-Key header authentication for tenant operations
- Timing-safe authentication to prevent timing attacks
- Comprehensive security filters (SecurityHeaders, APIThrottle, IPBlock)

#### Enhanced Security Features
- **Payment Method Whitelisting**: Prevents arbitrary class loading attacks
- **Webhook Rate Limiting**: 100 requests per 5 minutes for PayPal/Stripe
- **IP Whitelisting**: Payment provider IP validation for webhooks
- **Signature Verification**: Enhanced webhook signature validation
- **Replay Attack Prevention**: Timestamp validation and duplicate detection
- **Transaction ID Security**: Structured format with validation
- **Database Race Condition Prevention**: Triggers prevent duplicate active subscriptions

## Database Schema & Performance

### Enhanced Database Features

#### New Tables
- **`subscription_usage_tracking`**: Daily feature usage analytics with composite indexes
- **`subscription_state_log`**: Complete audit trail for subscription status changes
- **Enhanced indexes**: Performance-optimized for subscription and payment queries

#### Performance Optimizations
```sql
-- Core subscription indexes
KEY `idx_user_status` (`user_id`, `subscription_status`)
KEY `idx_next_payment_status` (`next_payment_date`, `subscription_status`)
KEY `idx_created_at` (`created_at`)

-- Payment tracking indexes
KEY `idx_payments_subscription_status` (`subscription_id`, `payment_status`)
KEY `idx_payments_transaction_id` (`transaction_id`)

-- Usage tracking indexes
UNIQUE KEY `unique_daily_usage` (`user_id`, `subscription_id`, `feature_name`, `usage_date`)
KEY `idx_user_feature_date` (`user_id`, `feature_name`, `usage_date`)
```

#### Data Integrity & Race Condition Prevention
```sql
-- MySQL triggers prevent multiple active subscriptions (when privileges allow)
CREATE TRIGGER prevent_multiple_active_subscriptions
BEFORE INSERT ON subscriptions
FOR EACH ROW
BEGIN
    DECLARE active_count INT DEFAULT 0;
    IF NEW.subscription_status = 'active' THEN
        SELECT COUNT(*) INTO active_count
        FROM subscriptions
        WHERE user_id = NEW.user_id AND subscription_status = 'active';
        IF active_count > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User already has an active subscription';
        END IF;
    END IF;
END;
```

#### CHECK Constraints (MySQL 8.0+)
```sql
CONSTRAINT `chk_subscription_amount` CHECK (`amount_paid` >= 0)
CONSTRAINT `chk_subscription_billing_period` CHECK (`billing_period` > 0)
CONSTRAINT `chk_subscription_retry_count` CHECK (`retry_count` >= 0)
```

## Testing Requirements

### Unit Testing Standards
- **Test all new libraries** with comprehensive unit tests
- **Mock external dependencies** (payment gateways, email services)
- **Test security validations** and error conditions
- **Verify state machine transitions** and audit logging

### Integration Testing
- **Test complete subscription flows** from creation to completion
- **Validate webhook security** with various attack scenarios
- **Test usage tracking** under high-load conditions
- **Verify database constraints** and race condition prevention

### Security Testing
- **Test payment method whitelisting** against injection attacks
- **Validate webhook security** with malicious payloads
- **Test rate limiting** and IP blocking functionality
- **Verify transaction ID validation** against tampering

### Example Test Structure
```php
class SubscriptionSystemTest extends CodeIgniter\Test\CIUnitTestCase
{
    public function testSecurePaymentMethodCreation()
    {
        $factory = new PaymentMethodFactory();

        // Test valid method
        $service = $factory->create('PayPal');
        $this->assertInstanceOf(PayPalService::class, $service);

        // Test invalid method throws exception
        $this->expectException(InvalidArgumentException::class);
        $factory->create('InvalidMethod');
    }

    public function testSubscriptionStateTransitions()
    {
        $stateMachine = new SubscriptionStateMachine();

        // Test valid transition
        $this->assertTrue($stateMachine->canTransition('pending', 'active'));

        // Test invalid transition
        $this->assertFalse($stateMachine->canTransition('cancelled', 'active'));
    }
}
```

## Documentation References

### Comprehensive API Documentation
- **`docs/SUBSCRIPTION_API.md`**: Complete API reference for all libraries
- **`docs/DEVELOPMENT_WORKFLOW.md`**: Development guidelines and best practices
- **Inline code documentation**: PHPDoc standards for all methods

### Code Examples & Integration
- Payment service integration patterns
- Webhook security implementation
- Usage tracking integration
- Error handling best practices