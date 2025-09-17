# MERAF Production Panel - Architecture Documentation

## System Overview

The MERAF Production Panel is a comprehensive license management system built on CodeIgniter 4 framework. It provides a centralized platform for managing digital licenses, user authentication, and product lifecycle management.

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                       │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────┐  │
│  │   Dashboard UI  │  │   API Endpoints │  │  CLI Tools  │  │
│  │   (Views)       │  │   (REST API)    │  │   (Spark)   │  │
│  └─────────────────┘  └─────────────────┘  └─────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    Business Logic Layer                     │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────┐  │
│  │   Controllers   │  │    Services     │  │   Helpers   │  │
│  │   (MVC)         │  │   (Business)    │  │  (Utilities)│  │
│  └─────────────────┘  └─────────────────┘  └─────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    Data Access Layer                        │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────┐  │
│  │     Models      │  │   Migrations    │  │   Database  │  │
│  │   (ORM)         │  │   (Schema)      │  │  (Storage)  │  │
│  └─────────────────┘  └─────────────────┘  └─────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

## Core Components

### 1. License Management System

**Purpose**: Central system for managing digital product licenses

**Key Components**:
- **License Creation**: Generate unique license keys for products
- **License Validation**: Verify license authenticity and status
- **Device/Domain Registration**: Track where licenses are activated
- **License Lifecycle**: Manage activation, renewal, expiration

**Data Flow**:
```
API Request → Authentication → License Validation → Database Query → Response
```

### 2. Authentication & Security System

**Purpose**: Secure user access and API protection

**Components**:
- **CodeIgniter Shield**: User authentication framework
- **IP Blocking**: Prevent access from malicious IPs  
- **API Key Authorization**: Timing-safe secret key validation with AES-256-GCM encryption
- **Session Management**: Handle user sessions securely
- **Security Headers**: Comprehensive browser-level protection
- **Rate Limiting**: Tiered throttling by endpoint sensitivity
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

### 3. Data Management System

**Purpose**: Persistent data storage and retrieval

**Database Schema**:
```
licenses (Primary Table)
├── license_key (unique identifier)
├── max_allowed_domains/devices
├── license_status (pending/active/blocked/expired)
├── license_type (trial/subscription/lifetime)
├── customer information
└── timestamps

license_registered_domains
├── license_key (FK)
├── registered_domain
└── registration_date

license_registered_devices  
├── license_key (FK)
├── registered_device
└── registration_date

license_logs
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

### 4. API Design Pattern

**RESTful API Structure**:
```
POST /api/license/create     → License creation
POST /api/license/validate   → License validation  
POST /api/license/activate   → Device/domain registration
POST /api/license/manage     → License management operations
GET  /api/info/general       → System information
```

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

### AES-256-GCM Encryption Architecture ✅ **FULLY IMPLEMENTED**

**Complete Implementation Overview**:
The encryption system now provides comprehensive protection across all components:
- ✅ **Installation Security**: Unique encryption keys per installation
- ✅ **Runtime Protection**: Automatic encryption/decryption with zero operational impact
- ✅ **Plugin Compatibility**: Fixed WooCommerce and SLM plugin integrations
- ✅ **Internal Security**: Resolved built-in license manager vulnerability
- ✅ **CSP Enhancement**: Updated Content Security Policy for CDN compatibility

**Encryption Infrastructure**:
```
Installation Process → Unique Encryption Key Generation → app/Config/Encryption.php
                                    ↓
API Secret Keys (at rest) → AES-256-GCM Encryption → Base64 Database Storage
                                    ↓
Runtime API Operations ← Automatic Decryption ← Encrypted Key Retrieval
                                    ↓
External Plugin APIs ← Decrypted Keys ← Smart Detection & Decryption
```

**Key Management Lifecycle**:
1. **Installation**: Unique encryption keys generated in `action_secure.php`, secret keys encrypted during `InitializeNewUser::initializeSecretKeys()`
2. **Runtime**: Automatic decryption in `Api::loadSecretKey()` with smart detection
3. **Settings**: Real-time encryption during save in `Home::app_settings_action()`
4. **Display**: Secure decryption for UI in `Home::decryptSecretKeysForDisplay()`
5. **Plugin Integration**: Automatic decryption for external plugins in `LicenseManager.php`
6. **Security Fix**: Resolved vulnerability in built-in license manager (line 1233)

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

## Scalability Considerations

### Horizontal Scaling Points
1. **API Layer**: Stateless design allows load balancing
2. **Database Layer**: Read replicas for license validation
3. **Caching Layer**: Session and configuration caching
4. **File Storage**: Writable directory can be moved to shared storage

### Performance Optimizations
1. **Output Compression**: Gzip compression enabled in BaseController
2. **Timezone Optimization**: Cached timezone detection
3. **Database Indexing**: License keys and email fields indexed
4. **Query Optimization**: Efficient model relationships

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

### File Structure in Production
```
public_html/                    (Document Root)
├── public/                     (Web accessible files)
├── app/                        (Application logic)
├── system/                     (Framework core)
├── writable/                   (Logs, cache, uploads)
├── vendor/                     (Composer dependencies)
├── tests/                      (Test suites)
└── user-data/                  (Custom user data)
```

### Environment Configuration
- **Development**: Local development with debug enabled
- **Production**: Optimized for performance and security
- **Testing**: Separate database and configuration

This architecture provides a robust, scalable foundation for license management while maintaining security and performance standards.