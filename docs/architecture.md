# MERAF Production Panel SaaS - Architecture Documentation

## System Overview

The MERAF Production Panel SaaS is a comprehensive multi-tenant license management system built on CodeIgniter 4 framework. It provides a scalable SaaS platform for managing digital licenses with complete tenant isolation, user authentication, subscription management, and product lifecycle management across multiple customers.

## Multi-Tenant SaaS Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                        SaaS Presentation Layer                      │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────┐  │
│  │  Tenant Portal  │  │   Multi-Tenant  │  │  Admin Dashboard    │  │
│  │  (User Views)   │  │   REST API      │  │  (Global Mgmt)      │  │
│  │  User-API-Key   │  │  User-API-Key   │  │  Super Admin        │  │
│  └─────────────────┘  └─────────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
                                    │
┌─────────────────────────────────────────────────────────────────────┐
│                       Multi-Tenant Business Layer                   │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────┐  │
│  │  Tenant Service │  │ Subscription    │  │  Notification       │  │
│  │  Isolation      │  │ Management      │  │  Service            │  │
│  │  owner_id       │  │ Billing         │  │  Multi-tenant       │  │
│  └─────────────────┘  └─────────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
                                    │
┌─────────────────────────────────────────────────────────────────────┐
│                       Multi-Tenant Data Layer                       │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────┐  │
│  │  Tenant-Aware   │  │  User Settings  │  │  Subscription       │  │
│  │  Models         │  │  Per-Tenant     │  │  Management         │  │
│  │  owner_id FK    │  │  Encryption     │  │  billing, packages  │  │
│  └─────────────────┘  └─────────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

## Core SaaS Components

### 1. Multi-Tenant License Management System

**Purpose**: Tenant-isolated digital product license management with complete data separation

**Key Components**:
- **Tenant Isolation**: Every license operation scoped to `owner_id`
- **License Creation**: Generate unique license keys per tenant
- **License Validation**: Verify license authenticity with tenant context
- **Device/Domain Registration**: Track activations per tenant
- **License Lifecycle**: Manage activation, renewal, expiration per tenant

**Multi-Tenant Data Flow**:
```
API Request → User-API-Key Authentication → Tenant Resolution → License Operation → Tenant Database → Response
```

### 2. Subscription Management System

**Purpose**: SaaS billing and package management per tenant

**Key Components**:
- **Package Management**: Define subscription tiers and limits
- **Billing Integration**: Automated billing cycles and payments
- **Usage Tracking**: Monitor tenant resource consumption
- **Trial Management**: Free trial and conversion tracking
- **Payment Processing**: Secure payment handling with webhooks

### 3. Multi-Tenant Authentication & Security System

**Purpose**: Secure tenant access and API protection with complete isolation

**Components**:
- **CodeIgniter Shield**: Multi-tenant user authentication framework
- **User-API-Key Authentication**: 6-character tenant-specific API keys
- **Dual Authentication Layers**: Admin secret keys + tenant User-API-Keys
- **IP Blocking**: Prevent access from malicious IPs per tenant
- **API Key Authorization**: Timing-safe secret key validation with user-specific AES-256-GCM encryption
- **Session Management**: Handle tenant sessions securely
- **Security Headers**: Comprehensive browser-level protection with tenant awareness
- **Rate Limiting**: Tiered throttling by endpoint sensitivity and tenant
- **Input Validation**: Multi-layer sanitization and format validation

**Security Layers** ✅ **ENTERPRISE-GRADE**:
1. **Security Headers**: CSP, HSTS, X-Frame-Options, X-Content-Type-Options
2. **Input Validation**: Comprehensive sanitization and format validation  
3. **CSRF Protection**: Session-based token validation
4. **Timing-Safe Authentication**: Hash-equals comparison for all secret keys
5. **Tiered Rate Limiting**: 10/30/60 requests per minute by endpoint type
6. **SQL Injection Prevention**: Parameterized queries via ORM
7. **Secret Key Encryption**: AES-256-GCM encryption at rest (FULLY IMPLEMENTED)
8. **IP-based Security**: SHA-256 hashing with daily salt rotation

### 4. Multi-Tenant Data Management System

**Purpose**: Tenant-isolated persistent data storage and retrieval

**Multi-Tenant Database Schema**:
```
users (Primary Tenant Table)
├── id (Primary Key)
├── username, email, password
├── api_key (encrypted per user)
├── first_name, last_name
└── created_at, updated_at

subscriptions (SaaS Billing)
├── id (Primary Key)
├── user_id (FK → users.id)
├── package_id (FK → packages.id)
├── subscription_status
├── start_date, end_date
└── payment_details

packages (SaaS Tiers)
├── id (Primary Key)
├── package_name, description
├── max_licenses, max_domains, max_devices
├── billing_amount, billing_interval
└── features_json

user_settings (Tenant Configuration)
├── id (Primary Key)
├── user_id (FK → users.id) [TENANT ISOLATION]
├── setting_name
├── setting_value (encrypted if secret)
└── timestamps

licenses (Tenant-Scoped Licenses)
├── id (Primary Key)
├── owner_id (FK → users.id) [TENANT ISOLATION]
├── license_key (unique identifier)
├── max_allowed_domains/devices
├── license_status (pending/active/blocked/expired)
├── license_type (trial/subscription/lifetime)
├── customer information
└── timestamps

license_registered_domains (Tenant-Scoped)
├── id (Primary Key)
├── owner_id (FK → users.id) [TENANT ISOLATION]
├── license_key (FK)
├── registered_domain
└── registration_date

license_registered_devices (Tenant-Scoped)
├── id (Primary Key)
├── owner_id (FK → users.id) [TENANT ISOLATION]
├── license_key (FK)
├── registered_device
└── registration_date

license_logs (Tenant-Scoped)
├── id (Primary Key)
├── owner_id (FK → users.id) [TENANT ISOLATION]
├── license_key (FK)
├── action_type
├── details
└── timestamp
```

## System Architecture Patterns

### 1. Model-View-Controller (MVC)

**Implementation**:
- **Models**: Data access and business rules (`LicensesModel`, `UserModel`)
- **Views**: Presentation layer (`dashboard.php`, template files)
- **Controllers**: Request handling and orchestration (`Home`, `Api`, `LicenseManager`)

### 2. Repository Pattern

**Implementation**: Models act as repositories for database operations
```php
class LicensesModel extends Model
{
    protected $table = 'licenses';
    protected $allowedFields = [...];
    
    // Custom business methods
    public function findByLicenseKey($key) { ... }
    public function validateLicense($key) { ... }
}
```

### 3. Service Layer Pattern

**Implementation**: Helper functions and utilities provide reusable business logic
- Configuration management (`getMyConfig()`)
- License operations (`initLicenseManager()`)
- Timezone handling (`setMyTimezone()`)
- Internationalization (`setMyLocale()`)

### 4. Multi-Tenant API Design Pattern

**Dual Authentication API Structure**:

**Admin API Endpoints** (Secret Key Authentication):
```
POST /api/license/create/{secret_key}     → Admin license creation
POST /api/license/verify/{secret_key}     → License validation
GET  /api/license/register/{type}/{name}/{secret_key}/{license_key} → Device/domain registration
GET  /api/license/all/{secret_key}        → Admin license management
GET  /api/product/all                     → Product information
```

**Tenant API Endpoints** (User-API-Key Header Authentication):
```
GET  /api/dashboard-data                  → Tenant dashboard data
POST /api/user/licenses                   → Tenant license creation
GET  /api/user/settings                   → Tenant settings retrieval
POST /api/user/settings                   → Tenant settings update
```

**Authentication Patterns**:
- **Admin Operations**: URL path secret key authentication
- **Tenant Operations**: `User-API-Key: A1B2C3` header authentication
- **Multi-Tenant Isolation**: All tenant operations automatically scoped to authenticated user's `owner_id`

## Component Interactions

### License Validation Flow
```
1. API Request (POST /api/license/validate)
   ↓
2. Secret Key Authorization (Api::authorizeSecretKey)
   ↓
3. Input Validation & Sanitization
   ↓
4. Database Query (LicensesModel::validate)
   ↓
5. Business Logic (status checks, expiry validation)
   ↓
6. Audit Logging (LicenseLogsModel::log)
   ↓
7. JSON Response (success/error with details)
```

### User Dashboard Flow
```
1. HTTP Request (GET /dashboard)
   ↓
2. Authentication Check (Home::checkIfLoggedIn)
   ↓
3. User Data Retrieval (auth()->user())
   ↓
4. Configuration Loading (getMyConfig())
   ↓
5. View Rendering (dashboard.php template)
   ↓
6. HTML Response with dynamic content
```

## Configuration Architecture

### Multi-Environment Configuration
```php
app/Config/
├── App.php          → Application settings
├── Database.php     → Database connections
├── Routes.php       → URL routing
├── Security.php     → Security settings
└── MyConfig.php     → Custom application config
```

### Dynamic Configuration Loading
```php
// Custom configuration system
$this->myConfig = getMyConfig();
$secretKey = $this->myConfig['License_Validate_SecretKey'];
```

## Security Architecture ✅ **ENTERPRISE-GRADE**

### Multi-Layer Security Model
```
External Request
       ↓
1. Web Server (Apache/Nginx) - Basic filtering, HTTPS enforcement
       ↓
2. Security Headers - Enhanced CSP (DataTables CDN), HSTS, X-Frame-Options
       ↓
3. CodeIgniter Security - CSRF, XSS protection, session management
       ↓
4. IP Blocking & Rate Limiting - SHA-256 IP hashing, tiered throttling
       ↓
5. API Authentication - Timing-safe secret key validation
       ↓
6. Input Validation - Comprehensive sanitization & format validation
       ↓
7. Encryption Layer - AES-256-GCM for API secret keys (FULLY IMPLEMENTED)
       ↓
8. Database Layer - Parameterized queries, UTC timestamps
       ↓
9. External Integration - Secure key decryption for plugin compatibility
```

### Enhanced Security Components ✅

**Security Helper Functions** (`app/Helpers/security_helper.php`):
- `encrypt_secret_key()` / `decrypt_secret_key()` - AES-256-GCM encryption
- `timing_safe_equals()` - Constant-time string comparison
- `validate_api_secret()` - Timing-safe API key validation
- `secure_hash_ip()` - SHA-256 IP hashing with daily salt
- `validate_license_key_format()` - License key format validation
- `validate_domain_format()` - RFC-compliant domain validation
- `sanitize_input()` - XSS and injection prevention

**Security Filters**:
- `SecurityHeaders` - Comprehensive HTTP security headers
- `APIThrottle` - Tiered rate limiting by endpoint sensitivity
- `IPBlockFilter` - Malicious IP prevention

### Multi-Tenant AES-256-GCM Encryption Architecture ✅ **FULLY IMPLEMENTED**

**Multi-Tenant Encryption Overview**:
The SaaS encryption system provides comprehensive protection with complete tenant isolation:
- ✅ **User-Specific Encryption Keys**: Unique encryption keys per tenant for complete data isolation
- ✅ **User API Key Encryption**: 6-character alphanumeric keys encrypted while preserving format
- ✅ **Auto-Save Functionality**: Generated keys automatically encrypted and saved to database
- ✅ **UserSettings Integration**: Seamless integration with `UserSettingsModel->setUserSetting()`
- ✅ **Timing-Safe Authentication**: Constant-time comparison for encrypted key validation
- ✅ **Backward Compatibility**: Seamless migration from plaintext to encrypted keys

**Multi-Tenant Encryption Infrastructure**:
```
User Registration → User-Specific Encryption Key Derivation → SHA-256 with user-specific salt
                                    ↓
User API Keys & Settings → User-Specific AES-256-GCM Encryption → user_settings table
                                    ↓
Tenant API Operations ← User-Specific Decryption ← getUserApiKey($userID)
                                    ↓
Multi-Tenant Authentication ← Timing-Safe Comparison ← Encrypted Key Validation
```

**Multi-Tenant Key Management Lifecycle**:
1. **User Registration**: User-specific encryption keys derived using SHA-256 with user ID salt
2. **User API Key Generation**: 6-character keys generated and automatically encrypted via `UserSettingsModel->setUserSetting()`
3. **Runtime Authentication**: User-specific decryption in `Api::getUserID()` with timing-safe comparison
4. **Settings Management**: Real-time encryption during save in tenant settings operations
5. **Display**: Secure decryption for UI in user dashboard and settings pages
6. **Multi-Tenant Isolation**: All encryption operations scoped to specific user ID for complete data separation

**Encryption Specifications**:
- **Algorithm**: AES-256-GCM (Authenticated Encryption with Additional Data)
- **Key Derivation**: SHA-256 with application-specific salt
- **IV Generation**: Cryptographically secure random bytes (16 bytes)
- **Authentication**: Built-in authentication tags prevent tampering
- **Storage Format**: Base64 encoding for database compatibility

**Protected Secret Keys**:
- `License_Validate_SecretKey` - API validation endpoint authentication
- `License_Create_SecretKey` - License creation endpoint authentication
- `License_DomainDevice_Registration_SecretKey` - Registration endpoint authentication
- `Manage_License_SecretKey` - License management endpoint authentication
- `General_Info_SecretKey` - General information endpoint authentication

### Authentication Flow
```
Login Request → Shield Authentication → Session Creation → Dashboard Access
              ↓
         User Verification → Database Query → Session Storage
```

## SaaS Scalability Considerations

### Multi-Tenant Horizontal Scaling Points
1. **API Layer**: Stateless tenant-aware design allows load balancing with session affinity
2. **Database Layer**: Read replicas for license validation with tenant sharding support
3. **Caching Layer**: Tenant-specific session and configuration caching
4. **File Storage**: User-data directories can be distributed across storage nodes
5. **Subscription Processing**: Async billing queue processing for payment handling

### SaaS Performance Optimizations
1. **Tenant Data Isolation**: Efficient `owner_id` indexing for fast tenant data retrieval
2. **Database Sharding**: Tenant data can be distributed across multiple databases
3. **Caching Strategy**: Redis/Memcached with tenant-specific cache keys
4. **API Rate Limiting**: Per-tenant rate limiting to prevent resource abuse
5. **Subscription Processing**: Background job processing for billing operations
6. **License Validation**: Optimized queries with composite indexes on `owner_id` + `license_key`

## Integration Architecture

### External System Integration
```
MERAF Panel
    ↓
┌─────────────────┐
│ Envato API      │ → Purchase verification
├─────────────────┤
│ Email System    │ → License notifications  
├─────────────────┤
│ Third-party     │ → License validation
│ Applications    │   (via REST API)
└─────────────────┘
```

### Plugin Architecture
- **Modular Design**: Support for SLM WP Plugin integration
- **Configurable Backends**: Multiple license manager support
- **Extension Points**: Helper functions for customization

## Deployment Architecture

### SaaS File Structure in Production
```
public_html/                    (Document Root)
├── public/                     (Web accessible files)
├── app/                        (Multi-tenant application logic)
│   ├── Controllers/Admin/      (Super admin controllers)
│   ├── Models/                 (Tenant-aware models)
│   └── Views/                  (Multi-tenant views)
├── system/                     (Framework core)
├── writable/                   (Logs, cache, uploads)
│   └── tenant-data/{user-id}/  (Per-tenant data directories)
├── vendor/                     (Composer dependencies)
├── tests/                      (Test suites)
└── subscription-data/          (Billing and payment data)
```

### SaaS Environment Configuration
- **Development**: Multi-tenant local development with debug enabled
- **Staging**: Production-like environment for testing subscription flows
- **Production**: Optimized for multi-tenant performance, security, and billing
- **Testing**: Isolated tenant testing with separate databases

### SaaS-Specific Features
- **Subscription Management**: Automated billing, trial management, package upgrades
- **Tenant Isolation**: Complete data separation with `owner_id` foreign keys
- **Multi-Tenant APIs**: Dual authentication for admin and tenant operations
- **Usage Analytics**: Per-tenant resource consumption tracking
- **Notification System**: Multi-tenant push notifications and email alerts

This multi-tenant SaaS architecture provides a robust, scalable foundation for license management as a service while maintaining complete tenant isolation, security, and performance standards.