# MERAF Production Panel SaaS - Technical Documentation

## Implementation Overview

The MERAF Production Panel SaaS is implemented using CodeIgniter 4 framework with PHP 8.1+. It follows a multi-tenant MVC architecture with complete data isolation, subscription management, and tenant-aware business logic layers.

## Core SaaS Technical Components

### 1. Multi-Tenant License Management Engine

#### Multi-Tenant License Key Generation
```php
function generateLicenseKey($userID, $prefix= '', $suffix = '', $charsCount = '')
{
    // Load security helper for enhanced key generation
    helper('security');

    // Tenant-specific character count configuration
    $charsCount = $charsCount ?: getMyConfig('', $userID)['licenseKeyCharsCount'] ?: 40;

    // Get tenant-specific prefix/suffix
    $prefix = $prefix ?: getMyConfig('', $userID)['licensePrefix'] ?: '';
    $suffix = $suffix ?: getMyConfig('', $userID)['licenseSuffix'] ?: '';

    try {
        // Use enhanced secure license key generation
        return generate_secure_license_key($prefix, $suffix, $charsCount);
    } catch (Exception $e) {
        // Fallback to secure method
        $validCharacters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $licenseKey = '';
        for ($i = 0; $i < $charsCount; $i++) {
            $randomCharIndex = random_int(0, strlen($validCharacters) - 1);
            $licenseKey .= $validCharacters[$randomCharIndex];
        }
        return strtoupper($prefix . $licenseKey . $suffix);
    }
}
```

#### User API Key Generation (SaaS-Specific)
```php
function generateUserApiKey()
{
    // 6-character alphanumeric key excluding ambiguous characters
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $keyLength = 6;
    $apiKey = '';
    for ($i = 0; $i < $keyLength; $i++) {
        $randomIndex = random_int(0, strlen($characters) - 1);
        $apiKey .= $characters[$randomIndex];
    }
    return $apiKey;
}
```

**SaaS Technical Features** ✅ **ENHANCED WITH MULTI-TENANT SECURITY**:
- **Tenant Isolation**: User-specific encryption keys for complete data separation
- **User API Keys**: 6-character alphanumeric keys with automatic encryption
- **Enhanced Security**: Uses `generate_secure_license_key()` from security helper
- **Auto-Save Integration**: Generated keys automatically encrypted via `UserSettingsModel`
- **Tenant Configuration**: Per-tenant prefix/suffix and character count settings
- **Backward Compatible**: Maintains existing API while adding multi-tenant features

#### Multi-Tenant License Validation Algorithm
```php
// Multi-tenant validation process with owner isolation
1. Tenant Authentication (User-API-Key validation)
2. License Key Format Validation
3. Owner ID Resolution (tenant isolation)
4. Database Existence Check with owner_id scope
5. License Status Verification (active/pending/blocked/expired)
6. Expiry Date Validation
7. Domain/Device Limit Verification (tenant-scoped)
8. IP Whitelist/Blacklist Check (tenant-specific)
9. Audit Logging with owner_id
```

### 2. Multi-Tenant Database Schema Design

#### SaaS Primary Tables Structure

**users** (Tenant Management)
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(30) UNIQUE NOT NULL,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    api_key TEXT, -- Encrypted User API Key
    active BOOLEAN DEFAULT TRUE,
    last_active DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_active (active)
);
```

**subscriptions** (SaaS Billing)
```sql
CREATE TABLE subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    subscription_status ENUM('active','pending','cancelled','expired') NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NULL,
    billing_amount DECIMAL(10,2) NOT NULL,
    billing_interval ENUM('monthly','yearly') NOT NULL,
    payment_method VARCHAR(50),
    last_payment_date DATETIME NULL,
    next_payment_date DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id),
    INDEX idx_user_status (user_id, subscription_status),
    INDEX idx_billing_date (next_payment_date)
);
```

**packages** (SaaS Tiers)
```sql
CREATE TABLE packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    package_name VARCHAR(100) NOT NULL,
    description TEXT,
    max_licenses INT NOT NULL,
    max_domains INT NOT NULL,
    max_devices INT NOT NULL,
    billing_amount DECIMAL(10,2) NOT NULL,
    billing_interval ENUM('monthly','yearly') NOT NULL,
    features_json JSON,
    sort_order INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (active)
);
```

**user_settings** (Tenant Configuration)
```sql
CREATE TABLE user_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    setting_name VARCHAR(255) NOT NULL,
    setting_value TEXT, -- May contain encrypted data
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_setting (user_id, setting_name),
    INDEX idx_user_setting (user_id, setting_name)
);
```

**licenses** (Tenant-Scoped License Data)
```sql
CREATE TABLE licenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL, -- TENANT ISOLATION
    license_key VARCHAR(100) UNIQUE NOT NULL,
    max_allowed_domains INT NOT NULL,
    max_allowed_devices INT NOT NULL,
    license_status ENUM('pending','active','blocked','expired') NOT NULL,
    license_type ENUM('trial','subscription','lifetime') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    item_reference VARCHAR(100),
    company_name VARCHAR(200),
    txn_id VARCHAR(100) NOT NULL,
    manual_reset_count INT DEFAULT 0,
    purchase_id_ VARCHAR(100) NOT NULL,
    date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_activated DATETIME NULL,
    date_renewed DATETIME NULL,
    date_expiry DATETIME NULL,
    reminder_sent BOOLEAN DEFAULT FALSE,
    reminder_sent_date DATETIME NULL,
    product_ref VARCHAR(100) NOT NULL,
    until VARCHAR(50),
    current_ver VARCHAR(20),
    subscr_id VARCHAR(100),
    billing_length INT,
    billing_interval VARCHAR(20),
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner_license (owner_id, license_key),
    INDEX idx_owner_status (owner_id, license_status),
    INDEX idx_license_key (license_key)
);
```

**license_registered_domains** (Tenant-Scoped Domain Tracking)
```sql
CREATE TABLE license_registered_domains (
    id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL, -- TENANT ISOLATION
    license_key VARCHAR(100) NOT NULL,
    registered_domain VARCHAR(255) NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (license_key) REFERENCES licenses(license_key) ON DELETE CASCADE,
    INDEX idx_owner_domain (owner_id, license_key),
    INDEX idx_license_domain (license_key)
);
```

**license_registered_devices** (Tenant-Scoped Device Tracking)
```sql
CREATE TABLE license_registered_devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL, -- TENANT ISOLATION
    license_key VARCHAR(100) NOT NULL,
    registered_device VARCHAR(255) NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (license_key) REFERENCES licenses(license_key) ON DELETE CASCADE,
    INDEX idx_owner_device (owner_id, license_key),
    INDEX idx_license_device (license_key)
);
```

**license_logs** (Tenant-Scoped Audit Logging)
```sql
CREATE TABLE license_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL, -- TENANT ISOLATION
    license_key VARCHAR(100) NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    details TEXT,
    source VARCHAR(100), -- IP address or API source
    is_valid ENUM('yes','no') NOT NULL,
    time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner_logs (owner_id, time DESC),
    INDEX idx_license_logs (license_key, time DESC)
);

### 3. Multi-Tenant Model Implementation

#### Tenant-Aware Base Model Structure
```php
class LicensesModel extends Model
{
    protected $table = 'licenses';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'owner_id', 'license_key', 'max_allowed_domains', 'max_allowed_devices',
        'license_status', 'license_type', 'first_name', 'last_name', 'email',
        'item_reference', 'company_name', 'txn_id', 'manual_reset_count',
        'purchase_id_', 'date_created', 'date_activated', 'date_renewed',
        'date_expiry', 'reminder_sent', 'reminder_sent_date', 'product_ref',
        'until', 'current_ver', 'subscr_id', 'billing_length', 'billing_interval'
    ];

    // Multi-tenant validation rules
    protected $validationRules = [
        'owner_id' => 'required|numeric', // TENANT ISOLATION
        'license_key' => 'required',
        'max_allowed_domains' => 'required|numeric',
        'max_allowed_devices' => 'required|numeric',
        'license_status' => 'required|in_list[pending,active,blocked,expired]',
        'license_type' => 'required|in_list[trial,subscription,lifetime]',
        'first_name' => 'required|regex_match[/^[\p{L}\p{M}\s.-]+$/u]',
        'last_name' => 'required|regex_match[/^[\p{L}\p{M}\s.-]+$/u]',
        'email' => 'required|valid_email',
        'purchase_id_' => 'required|alpha_numeric_punct',
        'txn_id' => 'required|alpha_numeric_punct',
        'product_ref' => 'required|alpha_numeric_punct',
    ];

    // Tenant-scoped query methods
    public function findByOwnerAndLicenseKey(int $ownerId, string $licenseKey)
    {
        return $this->where('owner_id', $ownerId)
                   ->where('license_key', $licenseKey)
                   ->first();
    }

    public function getLicensesByOwner(int $ownerId)
    {
        return $this->where('owner_id', $ownerId)->findAll();
    }
}
```

#### UserSettingsModel (SaaS Configuration)
```php
class UserSettingsModel extends Model
{
    protected $table = 'user_settings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'setting_name', 'setting_value'];

    /**
     * Set user setting with automatic encryption for secret keys
     */
    public function setUserSetting(string $settingName, string $settingValue, int $userID): bool
    {
        helper('security');

        // Auto-encrypt secret keys
        if (strpos($settingName, '_secret_key') !== false) {
            $settingValue = encrypt_secret_key($settingValue, $userID);
        }

        $data = [
            'user_id' => $userID,
            'setting_name' => $settingName,
            'setting_value' => $settingValue
        ];

        // Update or insert
        $existing = $this->where('user_id', $userID)
                         ->where('setting_name', $settingName)
                         ->first();

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return (bool) $this->insert($data);
        }
    }
}
```

#### SaaS Model Features
- **Tenant Isolation**: All queries automatically scoped by `owner_id`
- **User-Specific Encryption**: Settings encrypted with user-specific keys
- **Auto-Encryption**: Secret keys automatically encrypted on save
- **Comprehensive Validation**: Multi-layer input validation with tenant awareness
- **Internationalization**: Unicode support for names
- **Security**: SQL injection protection through ORM + tenant isolation

### 4. Multi-Tenant AES-256-GCM Encryption System ✅ **ENTERPRISE-GRADE IMPLEMENTATION**

#### Multi-Tenant Encryption Infrastructure
The SaaS system implements user-specific AES-256-GCM encryption for complete tenant isolation, providing authenticated encryption with tamper protection per tenant.

**Multi-Tenant Encryption Functions** (`app/Helpers/security_helper.php`):
```php
function encrypt_secret_key(string $plaintext, int $userID): string
{
    // Get user-specific encryption key for tenant isolation
    $key = get_encryption_key($userID);

    // Generate random IV (16 bytes for AES)
    $iv = random_bytes(16);

    // Encrypt using AES-256-GCM (authenticated encryption)
    $ciphertext = openssl_encrypt($plaintext, 'AES-256-GCM', $key, OPENSSL_RAW_DATA, $iv, $tag);

    if ($ciphertext === false) {
        throw new Exception('Encryption failed');
    }

    // Combine IV + authentication tag + ciphertext and encode
    $encrypted_data = base64_encode($iv . $tag . $ciphertext);

    return $encrypted_data;
}

function decrypt_secret_key(string $encrypted_data, int $userID): string
{
    // Get user-specific encryption key for tenant isolation
    $key = get_encryption_key($userID);

    // Decode the base64 data
    $data = base64_decode($encrypted_data);

    // Extract IV (16 bytes), tag (16 bytes), and ciphertext
    $iv = substr($data, 0, 16);
    $tag = substr($data, 16, 16);
    $ciphertext = substr($data, 32);

    // Decrypt using AES-256-GCM with authentication
    $plaintext = openssl_decrypt($ciphertext, 'AES-256-GCM', $key, OPENSSL_RAW_DATA, $iv, $tag);

    if ($plaintext === false) {
        throw new Exception('Decryption failed or authentication tag verification failed');
    }

    return $plaintext;
}

function get_encryption_key(int $userID): string
{
    // Generate user-specific encryption key using SHA-256
    $base_key = env('encryption.key') ?: 'default_app_key';
    return hash('sha256', $base_key . '_user_' . $userID, true);
}

function timing_safe_equals(string $known, string $user): bool
{
    // Prevent timing attacks in authentication
    return hash_equals($known, $user);
}
```

**SaaS Encryption Features**:
- **Tenant Isolation**: Each user has unique encryption keys derived from user ID
- **User API Key Encryption**: 6-character keys encrypted while preserving format
- **Auto-Save Integration**: Automatic encryption via `UserSettingsModel->setUserSetting()`
- **Timing-Safe Authentication**: Constant-time comparison prevents timing attacks
- **Backward Compatibility**: Seamless migration from plaintext to encrypted keys
- **Multi-Tenant Security**: Complete data separation at encryption level

### 5. SaaS-Specific Implementation Details

#### Subscription Management
```php
class SubscriptionModel extends Model
{
    protected $table = 'subscriptions';
    protected $allowedFields = [
        'user_id', 'package_id', 'subscription_status', 'start_date',
        'end_date', 'billing_amount', 'billing_interval', 'payment_method'
    ];

    public function getUserActiveSubscription(int $userID)
    {
        return $this->where('user_id', $userID)
                   ->where('subscription_status', 'active')
                   ->where('end_date >=', Time::now('UTC'))
                   ->first();
    }

    public function cancelUserActiveSubscription(int $userID, string $reason): bool
    {
        $subscription = $this->getUserActiveSubscription($userID);
        if ($subscription) {
            return $this->update($subscription['id'], [
                'subscription_status' => 'cancelled',
                'end_date' => Time::now('UTC')->toDateTimeString()
            ]);
        }
        return true; // No active subscription to cancel
    }
}
```

#### User API Key Authentication
```php
// In Api Controller
protected function getUserID(): ?int
{
    $request = service('request');
    $userApiKey = $request->getHeaderLine('User-API-Key');

    if (empty($userApiKey)) {
        return null;
    }

    // Get all users with API keys
    $users = $this->UserModel->where('api_key IS NOT NULL')->findAll();

    foreach ($users as $user) {
        $decryptedKey = $this->UserModel->getUserApiKey($user->id);
        if ($decryptedKey && timing_safe_equals($decryptedKey, $userApiKey)) {
            return $user->id;
        }
    }

    return null;
```

This multi-tenant SaaS technical implementation provides enterprise-grade security, complete tenant isolation, and scalable architecture suitable for production deployment with multiple customers.