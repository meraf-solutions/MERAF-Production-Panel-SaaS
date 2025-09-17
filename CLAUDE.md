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
  - `Controllers/` - HTTP request handlers (Api.php, Home.php, etc.)
  - `Models/` - Tenant-aware database layer (LicensesModel.php, UserModel.php, UserSettingsModel.php, SubscriptionModel.php, etc.)
  - `Views/` - Multi-tenant template files organized by feature
  - `Config/` - Application configuration
  - `Database/Migrations/` - Multi-tenant database schema changes
  - `Database/Seeds/` - Test data seeding
  - `Helpers/` - Custom helper functions (including security_helper.php)
  - `Filters/` - Security filters (SecurityHeaders.php, APIThrottle.php, IPBlockFilter.php)
  - `Language/` - Internationalization files
- `system/` - CodeIgniter 4 framework core (do not modify)
- `writable/` - Application logs, cache, uploads
  - `tenant-data/{user-id}/` - Per-tenant data directories
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

### Code Quality
The project includes these development dependencies:
- **PHPUnit** for testing (version 10.5.16+ or 11.2+)
- **PHP-CS-Fixer** for code formatting (version 3.47.1+)
- **CodeIgniter Coding Standard** for style consistency
- **Kint** for debugging and development

## Database Configuration

Tests require database configuration in `app/Config/Database.php` under the 'tests' group. Set up a test database before running the full test suite.

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

## Environment Setup

1. Ensure PHP 8.1+ with required extensions (intl, mbstring, etc.)
2. Set up database connection in `.env` or `app/Config/Database.php`
3. Configure application settings in `app/Config/` files
4. Set proper file permissions on `writable/` directory
5. Run `composer install` to install dependencies

## Development Notes

- All new controllers should extend `BaseController`
- Use CodeIgniter's built-in helpers and libraries when possible
- Follow PSR-4 autoloading conventions
- Database interactions should go through Models, not direct queries in Controllers
- Use migrations for database schema changes
- The application uses output compression for performance optimization

### Important SaaS Helper Functions
- `getMyConfig($key, $userID)` - Multi-tenant configuration loading from database
- `generateLicenseKey($userID)` - Secure tenant-specific license key generation
- `generateUserApiKey()` - 6-character User API key generation
- `encrypt_secret_key($plaintext, $userID)` - User-specific AES-256-GCM encryption
- `decrypt_secret_key($encrypted, $userID)` - User-specific decryption
- `timing_safe_equals($known, $user)` - Timing-safe string comparison
- `setMyTimezone()` - User timezone management
- `setMyLocale()` - Internationalization support

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