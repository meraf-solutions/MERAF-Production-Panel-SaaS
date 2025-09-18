# MERAF Production Panel SaaS

> A comprehensive multi-tenant license management platform built with CodeIgniter 4

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-orange.svg)](https://codeigniter.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## Overview

The MERAF Production Panel SaaS is a multi-tenant Software-as-a-Service platform for managing digital licenses and subscriptions. Built on CodeIgniter 4, it provides complete tenant isolation, subscription management, billing integration, and enterprise-grade security for managing digital product licenses across multiple customers.

### Key SaaS Features

- 🏢 **Multi-Tenancy** - Complete tenant isolation with data separation and security
- 💳 **Subscription Management** - Package-based billing with automated payment processing
- 🔐 **Advanced License Management** - Create, validate, and manage digital licenses per tenant
- 🌐 **Multi-Tenant API** - RESTful API with tenant-specific authentication
- 📊 **Usage Analytics** - Real-time tracking and enforcement of subscription limits
- 🔒 **Enterprise Security** - Enhanced security with tenant isolation and encryption
- 💰 **Payment Integration** - PayPal, Stripe, and offline payment processing
- 📧 **Automated Billing** - Invoice generation, payment reminders, and dunning management
- 🎯 **Trial Management** - Free trial periods with automatic conversion tracking

## Architecture

Built on **CodeIgniter 4** framework with multi-tenant SaaS architecture:

```
┌─────────────────────────────────────────┐
│         Multi-Tenant Presentation       │
│  Tenant Dashboard │ API │ Admin Panel   │
└─────────────────────────────────────────┘
┌─────────────────────────────────────────┐
│         SaaS Business Logic Layer       │
│ Subscriptions │ Billing │ Usage Limits  │
└─────────────────────────────────────────┘
┌─────────────────────────────────────────┐
│        Tenant-Isolated Data Layer       │
│ Multi-Tenant Models │ Payment Services  │
└─────────────────────────────────────────┘
```

### Multi-Tenant Data Isolation
- **Complete tenant separation** with `owner_id` foreign keys on all tenant data
- **Isolated file storage** with per-tenant directory structure
- **Tenant-specific configurations** via UserSettingsModel
- **Separate encryption keys** per tenant for complete data security

## Requirements

- **PHP 8.1+** with extensions:
  - `intl` - Internationalization
  - `mbstring` - Multibyte string handling
  - `curl` - HTTP requests and payment processing
  - `gd` or `imagick` - Image processing
  - `openssl` - Encryption and security
- **MySQL 5.7+** or **MariaDB 10.2+**
- **Apache/Nginx** web server with SSL/TLS
- **Composer** for dependency management
- **SSL Certificate** (required for payment processing)

## Installation

### 1. Clone & Setup
```bash
# Clone the repository
git clone <repository-url> meraf-panel-saas
cd meraf-panel-saas

# Install dependencies
composer install

# Set file permissions
chmod -R 755 writable/
chmod -R 755 user-data/
```

### 2. Database Configuration
```bash
# Copy environment file
cp env .env

# Edit database settings in .env
DB_DATABASE=saas_panel_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Secure Installation Process
Navigate to `https://yourdomain.com/install/` to run the secure installer:

1. **Database Setup** - Configure database connection with enhanced validation
2. **Admin Account** - Create the first admin user account
3. **Security Configuration** - Generate encryption keys and API secrets
4. **Payment Setup** - Configure payment gateways (PayPal, Stripe)
5. **Email Configuration** - Set up SMTP for transactional emails

**Installation Features:**
- ✅ **Enhanced SQL parsing** - Handles MySQL triggers and complex statements
- ✅ **Database validation** - Accepts hyphens and underscores in database names
- ✅ **Foreign key resolution** - Auto-creates system user for global settings
- ✅ **Directory structure setup** - Proper tenant data directory initialization
- ✅ **Default package creation** - Pre-configured subscription packages

### 4. Web Server Configuration

#### Apache (.htaccess included)
Ensure `mod_rewrite` and `mod_ssl` are enabled. SSL is required for payment processing.

#### Nginx
```nginx
server {
    listen 443 ssl http2;
    server_name your-saas-domain.com;
    root /path/to/meraf-panel-saas/public;
    index index.php;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## SaaS Configuration

### Subscription Packages
Configure subscription packages through the admin interface:
1. Navigate to **Admin** → **Package Manager**
2. Create packages with features and limits:
   - **License creation limits** per month
   - **Storage limits** for tenant data
   - **API request limits** per minute
   - **Email sending limits** per month
   - **Feature toggles** (advanced features, analytics, etc.)

### Payment Gateway Setup
Configure payment methods in **Admin** → **Payment Settings**:
- **PayPal** - Business account with webhook URLs
- **Stripe** - API keys and webhook endpoints
- **Offline Payments** - Bank transfer instructions

### Multi-Tenant Authentication
The SaaS system uses dual authentication layers:
- **Admin Secret Keys** - For system administration
- **User-API-Keys** - 6-character tenant-specific keys for API access
- **Tenant Isolation** - Complete data separation per user

## Development

### SaaS-Specific Commands

```bash
# Development server
./spark serve

# Database operations
./spark migrate          # Run SaaS schema migrations
./spark db:seed          # Seed packages and default data
./spark cache:clear      # Clear tenant-specific cache

# Subscription management
./spark subscription:check-expiry    # Check expired subscriptions
./spark subscription:process-retries # Process payment retries

# Testing
./phpunit                # Run all SaaS tests
./phpunit app/Libraries  # Test subscription libraries
```

### SaaS Project Structure

```
├── app/
│   ├── Controllers/
│   │   ├── Admin/              # Multi-tenant admin controllers
│   │   │   ├── AdminController.php     # SaaS administration
│   │   │   ├── PackageController.php   # Package management
│   │   │   └── UserController.php      # Tenant management
│   │   ├── Api.php                     # Multi-tenant API endpoints
│   │   ├── Home.php                    # Tenant dashboard
│   │   ├── SubscriptionController.php  # Billing management
│   │   └── AuthController.php          # Multi-tenant auth
│   ├── Libraries/          # Enhanced SaaS business logic
│   │   ├── PaymentMethodFactory.php    # Secure payment services
│   │   ├── SubscriptionStateMachine.php # Status transitions
│   │   ├── SubscriptionUsageTracker.php # Usage analytics
│   │   ├── PaymentRetryManager.php     # Automated retries
│   │   ├── WebhookSecurityManager.php  # Webhook security
│   │   └── TrialService.php            # Trial management
│   ├── Models/             # Multi-tenant data models
│   │   ├── SubscriptionModel.php       # Subscription management
│   │   ├── PackageModel.php            # Package definitions
│   │   ├── UserSettingsModel.php       # Tenant configurations
│   │   └── SubscriptionPaymentModel.php # Payment tracking
│   ├── Modules/            # Payment provider modules
│   │   ├── PayPal/Libraries/PayPalService.php
│   │   ├── Stripe/Libraries/StripeService.php
│   │   └── Offline/Libraries/OfflineService.php
│   └── Views/              # Multi-tenant templates
├── user-data/             # Tenant-isolated data storage
│   └── {user-id}/          # Per-tenant directories
│       ├── products/       # Tenant product files
│       ├── email-templates/ # Tenant email templates
│       ├── logs/           # Tenant-specific logs
│       └── settings/       # Tenant configurations
├── public/
│   └── install/            # Secure installation system
├── docs/                   # Comprehensive SaaS documentation
│   ├── SUBSCRIPTION_API.md # Complete API documentation
│   └── DEVELOPMENT_WORKFLOW.md # Development guidelines
└── tests/                  # PHPUnit test files
```

## SaaS API Usage

### Tenant Authentication
All API requests require tenant-specific authentication:
```php
// Tenant API request with User-API-Key
$headers = [
    'User-API-Key: ABC123',  // 6-character tenant key
    'Content-Type: application/json'
];

$response = file_get_contents("https://your-saas.com/api/license/validate/{secret_key}/{license_key}",
    false, stream_context_create(['http' => ['header' => implode("\r\n", $headers)]])
);
```

### Subscription Management API

The SaaS platform provides comprehensive subscription management through three REST API endpoints:

#### 1. Subscription Status API
Get detailed subscription information including package details and billing status:
```php
// Check subscription status
$response = file_get_contents(
    "https://your-saas.com/subscription/status",
    false, stream_context_create(['http' => ['header' => 'User-API-Key: ABC123']])
);

$data = json_decode($response, true);
echo "Package: " . $data['data']['package']['package_name'];
echo "Status: " . $data['data']['subscription']['subscription_status'];
echo "Days Remaining: " . $data['data']['days_remaining'];
```

#### 2. Usage Analytics API
Monitor feature usage with daily breakdown and trend analysis:
```php
// Get usage analytics
$usage = file_get_contents(
    "https://your-saas.com/subscription/usage",
    false, stream_context_create(['http' => ['header' => 'User-API-Key: ABC123']])
);

$analytics = json_decode($usage, true);
foreach ($analytics['data']['current_usage'] as $feature => $count) {
    echo "{$feature}: {$count} used\n";
}
```

#### 3. Feature Limits API
Check feature availability and usage limits in real-time:
```php
// Check feature limits
$limits = file_get_contents(
    "https://your-saas.com/subscription/limits",
    false, stream_context_create(['http' => ['header' => 'User-API-Key: ABC123']])
);

$features = json_decode($limits, true);
foreach ($features['data']['features'] as $feature => $details) {
    if ($details['can_use']) {
        echo "{$feature}: {$details['remaining']} remaining\n";
    } else {
        echo "{$feature}: LIMIT EXCEEDED\n";
    }
}
```

#### JavaScript/Fetch Examples
```javascript
// Modern async/await API usage
const apiKey = 'ABC123';
const baseUrl = 'https://your-saas.com';

const getSubscriptionData = async () => {
    try {
        // Get all subscription data in parallel
        const [status, usage, limits] = await Promise.all([
            fetch(`${baseUrl}/subscription/status`, {
                headers: { 'User-API-Key': apiKey }
            }),
            fetch(`${baseUrl}/subscription/usage`, {
                headers: { 'User-API-Key': apiKey }
            }),
            fetch(`${baseUrl}/subscription/limits`, {
                headers: { 'User-API-Key': apiKey }
            })
        ]);

        const [statusData, usageData, limitsData] = await Promise.all([
            status.json(),
            usage.json(),
            limits.json()
        ]);

        return { statusData, usageData, limitsData };
    } catch (error) {
        console.error('API Error:', error);
    }
};
```

### Multi-Tenant License Validation
```javascript
// Tenant-specific license validation
fetch('/api/license/validate/SECRET_KEY/LICENSE_KEY', {
    headers: {
        'User-API-Key': 'ABC123'
    }
})
.then(response => response.json())
.then(data => {
    if (data.result === 'success') {
        console.log('License valid for tenant');
    }
});
```

## SaaS Features

### Subscription Management System
- **Package-Based Billing** - Flexible packages with feature limits
- **Automated Billing** - Recurring payments with retry logic
- **Usage Tracking** - Real-time feature usage monitoring
- **Trial Management** - Free trials with conversion tracking
- **Dunning Management** - Automated payment failure handling

### Multi-Tenant Security ✅ **ENTERPRISE-GRADE**
- **🔐 AES-256-GCM Encryption** - Tenant-specific encryption keys
- **🛡️ Complete Tenant Isolation** - Data separation with foreign keys
- **🌐 Enhanced IP Blocking** - Per-tenant abuse protection
- **⚡ Tiered Rate Limiting** - Subscription-based API limits
- **🔍 Comprehensive Audit Logging** - Full tenant activity tracking
- **🏛️ Payment Security** - PCI-compliant payment processing
- **🔄 Webhook Security** - Signature verification and rate limiting

### Billing & Payment Features
- **Multiple Payment Gateways** - PayPal, Stripe, offline payments
- **Automated Invoicing** - PDF invoice generation and delivery
- **Payment Retry Logic** - Intelligent retry with exponential backoff
- **Subscription Analytics** - Revenue tracking and churn analysis
- **Tax Management** - Configurable tax rates and compliance

### Usage Analytics & Limits
- **Real-Time Tracking** - Live usage monitoring per feature
- **Subscription Limits** - Automatic enforcement of package limits
- **Usage Reports** - Detailed analytics for tenants and admins
- **Overage Handling** - Upgrade prompts and overage billing
- **REST API Access** - Three dedicated endpoints for subscription management:
  - `/subscription/status` - Complete subscription and package information
  - `/subscription/usage` - Usage analytics with daily breakdown and trends
  - `/subscription/limits` - Feature limits with real-time availability status

## Testing

### SaaS-Specific Testing
```bash
# Run all SaaS tests
./phpunit

# Test subscription system
./phpunit tests/Libraries/SubscriptionTest.php

# Test payment processing
./phpunit tests/Libraries/PaymentTest.php

# Test multi-tenant isolation
./phpunit tests/Models/TenantIsolationTest.php

# Run with coverage
./phpunit --coverage-html=tests/coverage/
```

## Deployment

### Production SaaS Checklist
- [ ] Set `CI_ENVIRONMENT=production` in `.env`
- [ ] Configure SSL/TLS certificates (required)
- [ ] Set up payment gateway webhooks
- [ ] Configure SMTP for transactional emails
- [ ] Set up automated backups with tenant data
- [ ] Configure monitoring and alerting
- [ ] Set up subscription cronjobs
- [ ] Configure CDN for static assets
- [ ] Set up database replication for high availability

### SaaS Cronjob Setup
Add to your server's crontab for automated SaaS operations:
```bash
# Subscription management (every 5 minutes)
*/5 * * * * cd /path/to/saas-panel && php spark cronjob/check_subscription_expiry

# Payment retry processing (every hour)
0 * * * * cd /path/to/saas-panel && php spark cronjob/process_payment_retries

# Usage analytics (daily at 2 AM)
0 2 * * * cd /path/to/saas-panel && php spark cronjob/process_daily_usage

# Invoice generation (daily at 3 AM)
0 3 * * * cd /path/to/saas-panel && php spark cronjob/generate_monthly_invoices

# Cleanup old logs (weekly)
0 1 * * 0 cd /path/to/saas-panel && php spark cronjob/cleanup_old_logs
```

### Webhook Configuration
Configure payment gateway webhooks:

**PayPal Webhooks:**
- URL: `https://your-saas.com/webhook/paypal`
- Events: `BILLING.SUBSCRIPTION.ACTIVATED`, `BILLING.SUBSCRIPTION.CANCELLED`, `PAYMENT.SALE.COMPLETED`

**Stripe Webhooks:**
- URL: `https://your-saas.com/webhook/stripe`
- Events: `customer.subscription.created`, `customer.subscription.deleted`, `invoice.payment_succeeded`

## Contributing

### SaaS Development Guidelines
- Follow multi-tenant security principles
- Always use `owner_id` scoping for tenant data
- Test subscription flows thoroughly
- Implement proper error handling for payment failures
- Use the enhanced libraries for all SaaS operations
- Follow the secure coding patterns documented in CLAUDE.md

## Security

### SaaS Security Features ✅ **ENTERPRISE-GRADE IMPLEMENTATION**

#### 🔐 **Multi-Tenant Security Architecture**
- **Tenant Data Isolation** - Complete separation with `owner_id` foreign keys
- **Encrypted Tenant Storage** - AES-256-GCM encryption per tenant
- **Secure Payment Processing** - PCI-compliant payment handling
- **Webhook Security** - Signature verification and replay protection

#### 🛡️ **Subscription Security**
- **Usage Validation** - Cryptographic validation of usage limits
- **Payment Security** - Secure transaction ID generation and validation
- **State Machine Protection** - Validated subscription status transitions
- **Audit Trail** - Complete subscription lifecycle logging

#### 🌐 **Enhanced Network Security**
- **Subscription-Based Rate Limiting** - Package-specific API limits
- **Payment Gateway IP Whitelisting** - Webhook source validation
- **Multi-Layer Authentication** - Admin + tenant-specific keys
- **Comprehensive Security Headers** - Full browser protection suite

## Documentation

Comprehensive SaaS documentation is available:

- **[CLAUDE.md](CLAUDE.md)** - Complete development guide and coding patterns
- **[docs/api.md](docs/api.md)** - Complete API reference including subscription endpoints
- **[docs/architecture.md](docs/architecture.md)** - System architecture and multi-tenant design
- **[docs/technical.md](docs/technical.md)** - Technical implementation details
- **[docs/SUBSCRIPTION_API.md](docs/SUBSCRIPTION_API.md)** - Subscription business logic libraries
- **[docs/DEVELOPMENT_WORKFLOW.md](docs/DEVELOPMENT_WORKFLOW.md)** - Development workflow and team processes

## Support

- **Documentation** - Comprehensive guides in the `docs/` directory
- **Issues** - Report SaaS-specific bugs and feature requests
- **Enterprise Support** - Available for production deployments

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Changelog

### Version 2.0.0 ✅ **SAAS EDITION RELEASE**
- **🏢 Multi-Tenancy** - Complete tenant isolation and data separation
- **💳 Subscription System** - Package-based billing with automated payments
- **🔐 Enhanced Security** - Tenant-specific encryption and isolation
- **📊 Usage Analytics** - Real-time tracking and limit enforcement
- **💰 Payment Integration** - PayPal, Stripe, and offline payment processing
- **🎯 Trial Management** - Free trial periods with conversion tracking
- **📧 Automated Billing** - Invoice generation and dunning management
- **🛡️ Webhook Security** - Enhanced security with rate limiting and validation

### Version 1.2.0 ✅ **SECURITY ENHANCEMENT RELEASE**
- **🔐 AES-256-GCM Encryption** - Enterprise-grade encryption for API keys
- **🔧 Installation Fixes** - Enhanced installer with SQL parsing improvements
- **🛡️ Enhanced Security** - Timing-safe operations and comprehensive validation
- **📱 UI Security Indicators** - Visual encryption status in admin interface

---

Built with ❤️ for SaaS by [MERAF Solutions](https://merafsolutions.com)