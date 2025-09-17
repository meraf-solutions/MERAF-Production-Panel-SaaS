# MERAF Production Panel SaaS - API Documentation

## API Overview

The MERAF Production Panel SaaS provides a comprehensive RESTful API for multi-tenant license management operations. The API is built using CodeIgniter 4's ResourceController and follows REST principles with JSON responses, featuring tenant isolation and enterprise-grade security.

### Base URL
```
https://your-domain.com/api/
```

### Authentication ✅ **ENTERPRISE-GRADE MULTI-TENANT SECURITY**

The SaaS API uses **dual authentication layers** with **timing-safe validation** and **AES-256-GCM encryption**:

#### 1. Admin Secret Keys (Global Operations)
- **Creation Secret Key**: For license creation operations
- **Validation Secret Key**: For license validation operations
- **Activation Secret Key**: For domain/device registration operations
- **Management Secret Key**: For license management operations
- **General Secret Key**: For general information operations

#### 2. User API Keys (Tenant-Specific Operations)
- **User-API-Key**: 6-character alphanumeric tenant authentication
- **Tenant Isolation**: Each user's operations restricted to their data
- **Automatic Encryption**: All user API keys encrypted with user-specific keys
- **Multi-Tenant Security**: Complete data isolation between tenants

**Security Features**:
- **AES-256-GCM Encryption**: All API secret keys encrypted at rest with authenticated encryption
- **Timing-Safe Validation**: All secret key comparisons use `hash_equals()` to prevent timing attacks
- **Automatic Key Management**: Transparent encryption/decryption with zero operational impact
- **Smart Migration**: Seamless upgrade from plaintext to encrypted keys
- **Comprehensive Input Validation**: Multi-layer validation with format checking and sanitization
- **Generic Error Messages**: Prevents information disclosure about system internals
- **Audit Logging**: All encryption/decryption operations logged for security monitoring

**Multi-Tenant Encryption Specifications**:
- **Algorithm**: AES-256-GCM (Authenticated Encryption with Additional Data)
- **IV Generation**: Cryptographically secure random bytes for each operation
- **Key Derivation**: SHA-256 with user-specific salt for tenant isolation
- **Storage**: Base64 encoded encrypted keys in `user_settings` table
- **Tenant Isolation**: Each user's keys encrypted with unique encryption keys
- **Backward Compatibility**: Existing installations automatically migrate on next settings save

**User API Key Features**:
- **Format**: 6-character alphanumeric (e.g., "A1B2C3")
- **Generation**: Cryptographically secure random generation excluding ambiguous characters
- **Auto-Save**: Generated keys automatically encrypted and saved to database
- **Header Authentication**: `User-API-Key: A1B2C3` header for tenant-specific operations

## API Endpoints

### Tenant-Specific Operations (User API Key Authentication)

All tenant-specific operations require the `User-API-Key` header for authentication and automatically enforce data isolation.

#### Headers Required
```
User-API-Key: A1B2C3
Content-Type: application/json
```

#### User Dashboard Data
**Endpoint**: `GET /dashboard-data`
**Purpose**: Retrieve comprehensive dashboard data for authenticated user
**Authentication**: User API Key (header)

**Response Format**:
```json
{
    "user": {
        "id": 123,
        "username": "john_doe",
        "email": "john@example.com",
        "first_name": "John",
        "last_name": "Doe",
        "api_key": "A1B2C3"
    },
    "licenses": [
        {
            "id": 1,
            "license_key": "ABC123...",
            "status": "active",
            "type": "lifetime",
            "customer_email": "customer@example.com"
        }
    ],
    "statistics": {
        "total_licenses": 15,
        "active_licenses": 12,
        "expired_licenses": 2,
        "blocked_licenses": 1
    },
    "recent_activities": [
        {
            "license_key": "ABC123...",
            "action": "validation_attempt",
            "timestamp": "2024-01-15 10:30:00"
        }
    ]
}
```

#### User License Management
**Endpoint**: `POST /user/licenses`
**Purpose**: Create license for authenticated user's tenant
**Authentication**: User API Key (header)

**Request Body**:
```json
{
    "license_type": "lifetime|trial|subscription",
    "license_status": "active|pending|blocked",
    "first_name": "Customer Name",
    "last_name": "Customer Last",
    "email": "customer@example.com",
    "max_allowed_domains": 1,
    "max_allowed_devices": 1,
    "product_ref": "my-product"
}
```

#### User Settings Management
**Endpoint**: `GET /user/settings`
**Purpose**: Retrieve user's application settings
**Authentication**: User API Key (header)

**Endpoint**: `POST /user/settings`
**Purpose**: Update user's application settings
**Authentication**: User API Key (header)

### 1. Routine Validation (Special Endpoint)

#### Validate License with Domain/Device
**Endpoint**: `GET /validate?t={product_name}&s={license_key}&d={name}`  
**Purpose**: Routine validation for web applications and mobile apps  
**Authentication**: None (special endpoint)

**Parameters**:
- `t` (query, required): Product name the license is registered to
- `s` (query, required): License key for validation  
- `d` (query, required): Domain name or device unique identification

**Use Cases**:
- **Web Applications**: Validate against active domain name
- **Mobile/Desktop Applications**: Validate against device identifier

**Response Format**:
```json
// Success
"1"

// Error  
"0"
```

**Logging**: All validation attempts are logged separately:
- **Error Logs**: Available at `/error-logs` (unsuccessful attempts)  
- **Success Logs**: Available at `/success-logs` (successful attempts)
- **Activity Logs**: Available at `/license-manager/activity-logs` (all license activities)

**Important**: This endpoint uses a different URL format from the main API.

### 2. License Validation

#### Validate License Key
**Endpoint**: `POST /api/license/validate/{secret_key}/{license_key}`  
**Purpose**: Validate the authenticity and status of a license key  
**Authentication**: Validation Secret Key  

**Parameters**:
- `secret_key` (path, required): Validation secret key
- `license_key` (path, required): License key to validate

**Response Format**:
```json
{
    "result": "success|error",
    "message": "Validation result message",
    "license_key": "ABC123...",
    "status": "active|pending|blocked|expired",
    "license_type": "trial|subscription|lifetime", 
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "company_name": "Company Inc",
    "registered_domains": [
        {
            "domain_name": "example.com",
            "registered_domain": "example.com", 
            "date": "2024-01-15 10:30:00"
        }
    ],
    "registered_devices": [
        {
            "device_name": "device123",
            "registered_device": "device123",
            "date": "2024-01-15 10:30:00"
        }
    ],
    "date_created": "2024-01-01 12:00:00",
    "date_activated": "2024-01-01 12:00:00",
    "date_expiry": "2025-01-01 12:00:00",
    "product_ref": "my-product",
    "current_ver": "1.0.0",
    "until": "2.0.0",
    "error_code": 100
}
```

**Status Codes**:
- `100`: Valid license
- `101`: Invalid license 
- `403`: Forbidden (invalid API key)

### 2. License Creation

#### Create New License
**Endpoint**: `GET /api/license/create/{secret_key}/data`  
**Purpose**: Create a new license with specified parameters  
**Authentication**: Creation Secret Key  

**Parameters** (Query String):
- `secret_key` (path, required): Creation secret key
- `license_key` (optional): Custom license key (auto-generated if not provided)
- `license_status` (required): `pending|active|blocked|expired`
- `license_type` (required): `trial|subscription|lifetime`
- `first_name` (required): Customer first name
- `last_name` (required): Customer last name  
- `email` (required): Customer email address
- `max_allowed_domains` (required): Maximum domains allowed
- `max_allowed_devices` (required): Maximum devices allowed
- `product_ref` (required): Product reference identifier
- `txn_id` (required): Transaction ID
- `purchase_id_` (required): Purchase ID
- `company_name` (optional): Customer company name
- `subscr_id` (optional): Subscription ID for recurring payments
- `billing_length` (conditional): Required for subscription type
- `billing_interval` (conditional): Required for subscription type (`days|months|years`)
- `date_expiry` (conditional): Required for trial/subscription types
- `until` (optional): Supported version limit
- `current_ver` (optional): Current product version
- `item_reference` (optional): Defaults to product_ref
- `manual_reset_count` (optional): Manual reset tracking

**Example Request**:
```
GET /api/license/create/your_secret_key/data?license_type=lifetime&license_status=active&first_name=John&last_name=Doe&email=john@example.com&max_allowed_domains=1&max_allowed_devices=1&product_ref=my-product&txn_id=txn123&purchase_id_=purchase123
```

**Response Format**:
```json
{
    "result": "success|error",
    "message": "License creation result message",
    "key": "generated_license_key",
    "code": 200
}
```

### 3. License Management

#### List All Licenses
**Endpoint**: `GET /api/license/all/{secret_key}`  
**Purpose**: Retrieve paginated list of all licenses with filtering  
**Authentication**: Management Secret Key  

**Parameters**:
- `secret_key` (path, required): Management secret key
- `draw` (query, optional): DataTables draw parameter
- `start` (query, optional): Pagination start (default: 0)
- `length` (query, optional): Records per page (default: 10, max: 500)
- `status` (query, optional): Filter by license status
- `type` (query, optional): Filter by license type  
- `search` (query, optional): Search term across multiple fields

**Response Format**:
```json
{
    "draw": 1,
    "recordsTotal": 1000,
    "recordsFiltered": 50,
    "data": [
        {
            "id": 1,
            "license_key": "ABC123...",
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "license_status": "active",
            "license_type": "lifetime",
            "date_created": "2024-01-01 12:00:00",
            "product_ref": "my-product"
        }
    ]
}
```

#### Export Licenses to CSV
**Endpoint**: `GET /api/license/export/{secret_key}`  
**Purpose**: Export filtered licenses to CSV format  
**Authentication**: Management Secret Key  

**Parameters**:
- `secret_key` (path, required): Management secret key
- `status` (query, optional): Filter by status
- `type` (query, optional): Filter by type
- `search` (query, optional): Search term

**Response**: CSV file download

#### Edit License
**Endpoint**: `POST /api/license/edit/{secret_key}`  
**Purpose**: Update existing license information  
**Authentication**: Management Secret Key  

**Request Body** (JSON):
```json
{
    "license_key": "ABC123...",
    "first_name": "Updated Name",
    "last_name": "Updated Last",
    "email": "updated@example.com",
    "license_status": "active",
    "max_allowed_domains": 2,
    "max_allowed_devices": 2
}
```

#### Delete License
**Endpoint**: `GET /api/license/delete/{option}/{secret_key}/{license_key}`  
**Purpose**: Delete license and related data  
**Authentication**: Management Secret Key  

**Parameters**:
- `option` (path, required): Deletion scope (`license_only|license_and_domains|license_and_devices|all`)
- `secret_key` (path, required): Management secret key
- `license_key` (path, required): License key to delete

### 4. Domain & Device Management

#### Register Domain/Device
**Endpoint**: `GET /api/license/register/{type}/{name}/{secret_key}/{license_key}`  
**Purpose**: Register a domain or device to a license  
**Authentication**: Activation Secret Key  

**Parameters**:
- `type` (path, required): `domain` or `device`
- `name` (path, required): Domain name or device identifier
- `secret_key` (path, required): Activation secret key
- `license_key` (path, required): Target license key

**Response Format**:
```json
{
    "result": "success|error",
    "message": "Registration result message",
    "error_code": 300
}
```

**Status Codes**:
- `300`: Registration successful
- `301`: Registration failed - limit reached
- `302`: Already registered
- `101`: Invalid license

#### Unregister Domain/Device  
**Endpoint**: `GET /api/license/unregister/{type}/{name}/{secret_key}/{license_key}`  
**Purpose**: Unregister a domain or device from a license  
**Authentication**: Activation Secret Key  

**Parameters**: Same as registration endpoint

**Status Codes**:
- `400`: Deactivation successful
- `401`: Not registered
- `101`: Invalid license

### 5. License Data Retrieval

#### Retrieve License by Purchase ID
**Endpoint**: `GET /api/license/data/{secret_key}/{purchase_id}/{product_name}`  
**Purpose**: Get license details using purchase ID and product name  
**Authentication**: General Secret Key  

**Parameters**:
- `secret_key` (path, required): General secret key
- `purchase_id` (path, required): Purchase identifier
- `product_name` (path, required): Product name

**Response Format**:
```json
{
    "id": 1,
    "license_key": "ABC123...",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com", 
    "purchase_id_": "purchase123",
    "product_ref": "my-product",
    "date_created": "2024-01-01 12:00:00"
}
```

#### Get License Activity Logs
**Endpoint**: `GET /api/license/logs/{license_key}/{secret_key}`  
**Purpose**: Retrieve activity logs for a specific license  
**Authentication**: Management Secret Key  

**Response Format**:
```json
{
    "result": "success",
    "data": [
        {
            "id": 1,
            "license_key": "ABC123...",
            "action_type": "validation_attempt",
            "details": "Valid license key",
            "ip_address": "192.168.1.1",
            "user_agent": "Mozilla/5.0...",
            "time": "2024-01-15 10:30:00",
            "is_valid": "yes"
        }
    ]
}
```

### 6. Product Information

#### List Products
**Endpoint**: `GET /api/info/products/{secret_key}`  
**Purpose**: Get list of all available products  
**Authentication**: General Secret Key  

**Response Format**:
```json
{
    "result": "success",
    "products": ["product1", "product2", "product3"]
}
```

#### List Products with Variations  
**Endpoint**: `GET /api/info/products/variations/{secret_key}`  
**Purpose**: Get products with their variations  
**Authentication**: General Secret Key  

#### List Product Versions
**Endpoint**: `GET /api/info/products/versions/{secret_key}`  
**Purpose**: Get current versions of all products  
**Authentication**: General Secret Key  

#### Get Product Files
**Endpoint**: `GET /api/product/files/{product_name}/{secret_key}`  
**Purpose**: Retrieve download files for a product  
**Authentication**: General Secret Key  

#### Get Product Changelog
**Endpoint**: `GET /api/product/changelog/{product_name}/{secret_key}`  
**Purpose**: Get version history and changelog for a product  
**Authentication**: General Secret Key  

### 7. System Information

#### Generate License Key
**Endpoint**: `GET /api/license/generate`  
**Purpose**: Generate a new license key based on application settings  
**Authentication**: None required

**Response Format**:
```json
"V3NYHLOKZ4IRIZ4D9Y8KDM8VZL16BOQ3HD8VZY7J"
```

#### Get Subscribers List
**Endpoint**: `GET /api/license/subscribers/{secret_key}`  
**Purpose**: Get list of all license subscribers  
**Authentication**: Management Secret Key

**Response Format**:
```json
[
    {
        "id": "128",
        "license_key": "QLK08J7Z2GE4TQ834QXFO1EDT51MAF2UEXZWLITP",
        "sent_to": "johndoe@gmail.com",
        "status": "success",
        "sent": "yes",
        "date_sent": "2024-04-28 12:35:12",
        "disable_notifications": ""
    }
]
```

### 8. Automated Tasks (Cronjobs)

#### Remind Expiring Licenses
**Endpoint**: `GET /cron/run/remind-expiring-license`  
**Purpose**: Send email reminders for expiring licenses  
**Authentication**: None required

**Setup**:
1. Configure reminder schedule in App Settings → License Manager → Notifications
2. Set hours before expiry to send reminder

**Response Format**:
```json
{
    "success": true,
    "status": 1,
    "msg": "Reminder on expiring license cron job run successfully! Updated a total of 4 license(s)."
}
```

#### Auto-Expire Licenses
**Endpoint**: `GET /cron/run/autoexpiry-license`  
**Purpose**: Automatically change status to "expired" for expired licenses  
**Authentication**: None required

**Response Format**:
```json
{
    "success": true,
    "status": 1,
    "msg": "Auto-expiry cron job run successfully! Updated a total of 2 license(s)."
}
```

**Automated Setup**: Add cronjob to run every minute:
```bash
cd web/{path_to_root_folder}/public_html && php spark tasks:run >> /dev/null 2>&1
```  

## Error Handling

### Standard Error Response Format
```json
{
    "result": "error",
    "message": "Human-readable error description",
    "error_code": 101
}
```

### Common Error Codes
- **10**: `CREATE_FAILED` - New license creation request has failed
- **50**: `REACHED_MAX_DOMAINS` - Domain registration failed (max domains reached)
- **60**: `LICENSE_INVALID` - Invalid license key was submitted
- **120**: `REACHED_MAX_DEVICES` - Device registration failed (max devices reached)
- **200**: `LICENSE_EXIST` - The license key found in the records
- **204**: `RETURNED_EMPTY` - The requested data returned empty
- **220**: `KEY_UPDATE_FAILED` - The license detail update failed
- **240**: `KEY_UPDATE_SUCCESS` - The license detail update success
- **340**: `KEY_DEACTIVATE_SUCCESS` - Domain/device deactivation successful
- **400**: `LICENSE_CREATED` - New license creation request was successful
- **403**: `FORBIDDEN_ERROR` - Invalid API secret key or unauthorized request
- **404**: `QUERY_DOMAINorDEVICE_NOT_EXISTING` - Domain/device not registered with license
- **405**: `METHOD_NOT_ALLOWED` - The requested method not allowed
- **429**: `TOO_MANY_REQUESTS` - Rate limit exceeded
- **500**: `QUERY_NOT_FOUND` - Requested resource/domain/device/license not found
- **503**: `QUERY_ERROR` - Encountered a problem processing the request

## Rate Limiting ✅ **TIERED PROTECTION**

The API implements **enterprise-grade tiered rate limiting** based on endpoint sensitivity:

### Rate Limit Tiers
- **Authentication Endpoints** (validate, activate, create): **10 requests/minute** (strictest)
- **Management Endpoints** (manage, update, deactivate): **30 requests/minute** (moderate)  
- **Information Endpoints** (general info, logs): **60 requests/minute** (relaxed)
- **Excluded Endpoints**: No rate limiting applied

### Enhanced Security Features
- **SHA-256 IP Hashing**: Replaces weak MD5 with cryptographically secure hashing
- **Daily Salt Rotation**: Prevents hash table attacks with rotating daily salts
- **JSON Error Response**: Standardized error format with proper HTTP status codes
- **Endpoint-Specific Limits**: Granular protection based on operation sensitivity

### Rate Limit Response
```json
{
    "result": "error",
    "message": "Rate limit exceeded. Please try again later.",
    "code": "RATE_LIMIT_EXCEEDED"
}
```
**HTTP Status**: 429 Too Many Requests

### Secure IP Processing
```php
// Enhanced IP hashing with daily salt rotation
$ipHash = secure_hash_ip($request->getIPAddress());
$limit = $this->getRateLimitForEndpoint($endpoint);

if ($throttler->check($ipHash, $limit['requests'], $limit['period']) === false) {
    // Rate limit exceeded
}
```

## Security Features ✅ **ENTERPRISE-GRADE**

### Multi-Layer Security Architecture
```
Request → Security Headers → Rate Limiting → IP Blocking → Authentication → Input Validation → Database
```

### 1. Security Headers (Defense-in-Depth)
**Comprehensive Browser Protection**:
- **X-Content-Type-Options**: `nosniff` - Prevents MIME-type sniffing attacks
- **X-Frame-Options**: `DENY` - Prevents clickjacking attacks
- **X-XSS-Protection**: `1; mode=block` - Legacy XSS protection for older browsers
- **Strict-Transport-Security**: `max-age=31536000; includeSubDomains; preload` - Enforces HTTPS
- **Content-Security-Policy**: Restrictive CSP preventing code injection (includes CDN support for DataTables)
- **Referrer-Policy**: `strict-origin-when-cross-origin` - Controls referrer information
- **X-DNS-Prefetch-Control**: `off` - Prevents DNS prefetching for privacy
- **Permissions-Policy**: `interest-cohort=()` - Disables Google FLoC tracking

### 2. Enhanced IP Blocking & Rate Limiting
- **Automatic IP blocking**: Suspicious activity detection with configurable thresholds
- **SHA-256 IP Hashing**: Cryptographically secure IP processing with daily salt rotation
- **Tiered Rate Limiting**: Endpoint-specific limits (10/30/60 requests per minute)
- **Manual Control**: IP whitelist/blacklist support for administrative control

### 3. Timing-Safe Authentication  
```php
// All secret key validations use timing-safe comparison
function validate_api_secret($provided_key, $stored_key, $is_encrypted = false): bool 
{
    $actual_key = $is_encrypted ? decrypt_secret_key($stored_key) : $stored_key;
    return timing_safe_equals($actual_key, $provided_key); // Constant-time comparison
}
```

### 4. Comprehensive Input Validation
**Multi-Layer Validation Pipeline**:
1. **Format Validation**: License keys (40 char alphanumeric), domains (RFC-compliant), device IDs
2. **Sanitization**: XSS prevention, HTML tag stripping, null byte removal
3. **Business Logic Validation**: Domain-specific rules and constraints
4. **Database Constraints**: Schema-level integrity enforcement
5. **SQL Injection Prevention**: ORM parameterized queries

**Validation Functions**:
```php
validate_license_key_format($license_key)  // 40-char alphanumeric validation
validate_domain_format($domain)            // RFC-compliant domain validation  
validate_device_identifier($device_id)     // Device ID format validation
sanitize_input($input)                     // XSS and injection prevention
```

### 5. Enhanced Request Logging  
- **Comprehensive Logging**: All API requests logged with full context
- **Security Context**: IP address, User Agent, endpoint accessed
- **Request/Response Data**: Parameters sent and responses returned
- **Success/Failure Tracking**: Detailed status and error information
- **UTC Timestamps**: Consistent timezone handling for audit trails
- **Secure IP Hashing**: Privacy-preserving IP logging for analytics

### 6. Multi-Tenant Encryption Infrastructure ✅ **FULLY IMPLEMENTED**
**AES-256-GCM Multi-Tenant Protection**:
```php
encrypt_secret_key($plaintext, $userID)     // Encrypt with user-specific key
decrypt_secret_key($encrypted_data, $userID) // Decrypt with user-specific key
get_encryption_key($userID)                  // User-specific key derivation
generateUserApiKey()                         // Generate 6-char alphanumeric key
```

**SaaS Implementation Features**:
- ✅ **Multi-Tenant Isolation**: User-specific encryption keys for complete data separation
- ✅ **UserSettingsModel Integration**: Auto-encrypt settings using `setUserSetting()`
- ✅ **User API Key Encryption**: 6-character keys encrypted while preserving format
- ✅ **Auto-Save Functionality**: Generated keys automatically saved to database
- ✅ **Backward Compatibility**: Seamless migration from plaintext to encrypted keys
- ✅ **Timing-Safe Authentication**: `getUserID()` method uses constant-time comparison
- ✅ **Header Authentication**: `User-API-Key` header support for tenant operations

### 7. Generic Error Response Pattern
**Information Disclosure Prevention**:
```json
{
    "result": "error",
    "message": "Request failed due to configuration restrictions.",
    "code": "REQUEST_FAILED"
}
```
- **Generic Messages**: No internal system details exposed
- **Consistent Format**: Standardized error response structure
- **Proper HTTP Codes**: Appropriate status codes (400, 401, 429, 500)
- **Security Logging**: Detailed errors logged server-side only

## Integration Examples

### PHP Integration
```php
<?php
$secretKey = 'your_validation_secret_key';
$licenseKey = 'customer_license_key';

$url = "https://your-domain.com/api/license/validate/{$secretKey}/{$licenseKey}";

$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['result'] === 'success') {
    // License is valid
    echo "License valid for: " . $data['email'];
} else {
    // License is invalid
    echo "Error: " . $data['message'];
}
?>
```

### JavaScript Integration
```javascript
const secretKey = 'your_validation_secret_key';
const licenseKey = 'customer_license_key';

fetch(`/api/license/validate/${secretKey}/${licenseKey}`)
    .then(response => response.json())
    .then(data => {
        if (data.result === 'success') {
            console.log('License valid for:', data.email);
        } else {
            console.log('Error:', data.message);
        }
    })
    .catch(error => console.error('API Error:', error));
```

### cURL Examples
```bash
# Validate License
curl -X POST "https://your-domain.com/api/license/validate/secret123/ABC123"

# Create License  
curl -X GET "https://your-domain.com/api/license/create/secret123/data?license_type=lifetime&first_name=John&last_name=Doe&email=john@example.com&max_allowed_domains=1&max_allowed_devices=1&product_ref=my-product&txn_id=txn123&purchase_id_=purchase123"

# Register Domain
curl -X GET "https://your-domain.com/api/license/register/domain/example.com/secret123/ABC123"
```

## Webhook Support

### License Events
The API can send webhook notifications for license events:

**Supported Events**:
- License created
- License activated
- License expired
- Domain/device registered
- Domain/device unregistered

**Webhook Payload Format**:
```json
{
    "event": "license.created",
    "timestamp": "2024-01-15T10:30:00Z",
    "data": {
        "license_key": "ABC123...",
        "email": "customer@example.com",
        "product_ref": "my-product"
    }
}
```

This API documentation provides comprehensive coverage of all available endpoints, authentication methods, and integration examples for the MERAF Production Panel license management system.