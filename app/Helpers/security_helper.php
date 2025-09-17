<?php

/**
 * Security Helper Functions - SaaS Version
 *
 * Provides secure encryption/decryption and timing-safe comparison functions
 * Enhanced for multi-tenant SaaS architecture
 */

if (!function_exists('encrypt_secret_key')) {
    /**
     * Encrypt a secret key using AES-256-GCM
     *
     * @param string $plaintext The secret key to encrypt
     * @param int|null $userID Optional user ID for user-specific encryption
     * @return string The encrypted data (base64 encoded)
     */
    function encrypt_secret_key(string $plaintext, int $userID = null): string
    {
        // Get encryption key from environment or generate if not exists
        $key = get_encryption_key($userID);

        // Generate random IV
        $iv = random_bytes(16);

        // Encrypt using AES-256-GCM
        $ciphertext = openssl_encrypt($plaintext, 'AES-256-GCM', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($ciphertext === false) {
            throw new Exception('Encryption failed');
        }

        // Combine IV + tag + ciphertext and encode
        $encrypted_data = base64_encode($iv . $tag . $ciphertext);

        return $encrypted_data;
    }
}

if (!function_exists('decrypt_secret_key')) {
    /**
     * Decrypt a secret key using AES-256-GCM
     *
     * @param string $encrypted_data The encrypted data (base64 encoded)
     * @param int|null $userID Optional user ID for user-specific decryption
     * @return string The decrypted secret key
     */
    function decrypt_secret_key(string $encrypted_data, int $userID = null): string
    {
        // Check if data appears to be encrypted (base64 with proper length)
        if (!is_encrypted_key($encrypted_data)) {
            // Return as-is for backward compatibility with plaintext keys
            return $encrypted_data;
        }

        // Get encryption key
        $key = get_encryption_key($userID);

        // Decode the encrypted data
        $data = base64_decode($encrypted_data);

        if ($data === false) {
            // If decoding fails, might be plaintext key
            return $encrypted_data;
        }

        // Check minimum length for IV + tag + ciphertext
        if (strlen($data) < 32) {
            return $encrypted_data;
        }

        // Extract components
        $iv = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $ciphertext = substr($data, 32);

        // Decrypt
        $plaintext = openssl_decrypt($ciphertext, 'AES-256-GCM', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($plaintext === false) {
            // Decryption failed, might be plaintext key
            return $encrypted_data;
        }

        return $plaintext;
    }
}

if (!function_exists('is_encrypted_key')) {
    /**
     * Check if a key appears to be encrypted
     *
     * @param string $key The key to check
     * @return bool True if the key appears encrypted
     */
    function is_encrypted_key(string $key): bool
    {
        // Basic heuristics for encrypted keys
        if (strlen($key) < 44) return false; // Minimum base64 length for encrypted data
        if (!preg_match('/^[A-Za-z0-9+\/]+=*$/', $key)) return false; // Base64 pattern

        $decoded = base64_decode($key, true);
        if ($decoded === false) return false;
        if (strlen($decoded) < 32) return false; // Minimum for IV + tag

        return true;
    }
}

if (!function_exists('get_encryption_key')) {
    /**
     * Get or generate the encryption key
     *
     * @param int|null $userID Optional user ID for user-specific keys
     * @return string The encryption key
     */
    function get_encryption_key(int $userID = null): string
    {
        // Try to get key from environment first
        $key = env('SECRET_KEY_ENCRYPTION_KEY');

        if (!$key) {
            // Fallback to application key with additional salt
            $appKey = env('encryption.key') ?: config('Encryption')->key;

            if (!$appKey) {
                throw new Exception('No encryption key available. Set SECRET_KEY_ENCRYPTION_KEY in environment.');
            }

            // Derive a key specifically for secret key encryption
            $salt = 'meraf_saas_secret_key_encryption_salt_2025';

            // Add user-specific salt for multi-tenant isolation
            if ($userID) {
                $salt .= '_user_' . $userID;
            }

            $key = hash('sha256', $appKey . $salt, true);
        } else {
            // Ensure key is proper length for AES-256
            $userSalt = $userID ? '_user_' . $userID : '';
            $key = hash('sha256', $key . $userSalt, true);
        }

        return $key;
    }
}

if (!function_exists('timing_safe_equals')) {
    /**
     * Timing-safe string comparison to prevent timing attacks
     *
     * @param string $known_string The expected value
     * @param string $user_string The user-provided value
     * @return bool True if strings are equal
     */
    function timing_safe_equals(string $known_string, string $user_string): bool
    {
        return hash_equals($known_string, $user_string);
    }
}

if (!function_exists('validate_api_secret')) {
    /**
     * Validate API secret key with timing attack protection
     *
     * @param string $provided_key The key provided by the user
     * @param string $stored_key The stored key (may be encrypted)
     * @param bool $is_encrypted Whether the stored key is encrypted
     * @param int|null $userID User ID for user-specific decryption
     * @return bool True if keys match
     */
    function validate_api_secret(string $provided_key, string $stored_key, bool $is_encrypted = false, int $userID = null): bool
    {
        try {
            // Decrypt stored key if encrypted
            $actual_key = $is_encrypted ? decrypt_secret_key($stored_key, $userID) : $stored_key;

            // Use timing-safe comparison
            return timing_safe_equals($actual_key, $provided_key);

        } catch (Exception $e) {
            log_message('error', 'API secret validation failed: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('secure_hash_ip')) {
    /**
     * Create a secure hash of an IP address for rate limiting
     *
     * @param string $ip_address The IP address to hash
     * @param string $salt Optional salt (uses daily rotating salt if not provided)
     * @return string SHA-256 hash of the IP address
     */
    function secure_hash_ip(string $ip_address, string $salt = ''): string
    {
        if (empty($salt)) {
            // Use date-based salt that rotates daily
            $salt = 'saas_ip_hash_salt_' . date('Y-m-d');
        }

        return hash('sha256', $ip_address . $salt);
    }
}

if (!function_exists('validate_license_key_format')) {
    /**
     * Validate license key format
     *
     * @param string $license_key The license key to validate
     * @return bool True if format is valid
     */
    function validate_license_key_format(string $license_key): bool
    {
        // Flexible license key validation for custom admin-generated keys
        $trimmed_key = trim($license_key);

        // Basic length check (reasonable range for license keys)
        if (strlen($trimmed_key) < 10 || strlen($trimmed_key) > 100) {
            return false;
        }

        // Allow alphanumeric characters plus common separators (hyphens, underscores)
        // This accommodates custom prefixes/suffixes that admins might add
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $trimmed_key)) {
            return false;
        }

        return true;
    }
}

if (!function_exists('validate_domain_format')) {
    /**
     * Validate domain name format
     *
     * @param string $domain The domain name to validate
     * @return bool True if format is valid
     */
    function validate_domain_format(string $domain): bool
    {
        // Basic domain validation
        if (strlen($domain) > 253) {
            return false;
        }

        // Use filter_var for initial validation
        if (filter_var('http://' . $domain, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        // Additional checks for security
        if (preg_match('/[<>"\']/', $domain)) {
            return false;
        }

        return true;
    }
}

if (!function_exists('validate_device_identifier')) {
    /**
     * Validate device identifier format
     *
     * @param string $device_id The device identifier to validate
     * @return bool True if format is valid
     */
    function validate_device_identifier(string $device_id): bool
    {
        // Device ID should be reasonable length
        if (strlen($device_id) < 1 || strlen($device_id) > 100) {
            return false;
        }

        // Allow alphanumeric, hyphens, underscores
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $device_id)) {
            return false;
        }

        return true;
    }
}

if (!function_exists('sanitize_input')) {
    /**
     * Sanitize input to prevent XSS and other attacks
     *
     * @param mixed $input The input to sanitize
     * @return mixed The sanitized input
     */
    function sanitize_input($input)
    {
        if (is_string($input)) {
            // Remove HTML tags and special characters
            $input = strip_tags($input);
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

            // Remove null bytes
            $input = str_replace("\0", '', $input);

            return $input;
        }

        if (is_array($input)) {
            return array_map('sanitize_input', $input);
        }

        return $input;
    }
}

if (!function_exists('generate_secure_license_key')) {
    /**
     * Generate a cryptographically secure license key
     *
     * @param string $prefix Optional prefix
     * @param string $suffix Optional suffix
     * @param int $length Length of the random part (default 40)
     * @return string The generated license key
     */
    function generate_secure_license_key(string $prefix = '', string $suffix = '', int $length = 40): string
    {
        if ($length < 16 || $length > 128) {
            throw new InvalidArgumentException('License key length must be between 16 and 128 characters');
        }

        // Use random_bytes for maximum entropy
        $randomBytes = random_bytes($length);

        // Convert to alphanumeric string using base64 and clean up
        $licenseKey = base64_encode($randomBytes);

        // Remove special characters and make alphanumeric only
        $licenseKey = preg_replace('/[^a-zA-Z0-9]/', '', $licenseKey);

        // Ensure we have enough characters, pad if necessary
        while (strlen($licenseKey) < $length) {
            $additionalBytes = random_bytes(16);
            $additional = preg_replace('/[^a-zA-Z0-9]/', '', base64_encode($additionalBytes));
            $licenseKey .= $additional;
        }

        // Truncate to desired length
        $licenseKey = substr($licenseKey, 0, $length);

        // Add entropy mixing - combine with current timestamp and process ID
        $entropyData = $licenseKey . microtime(true) . getmypid() . uniqid('', true);
        $hash = hash('sha256', $entropyData);

        // Take first $length characters from hash and mix with original
        $hashPart = substr($hash, 0, $length);

        // Interleave the two strings for additional randomness
        $mixed = '';
        for ($i = 0; $i < $length; $i++) {
            $mixed .= ($i % 2 === 0) ? $licenseKey[$i] : $hashPart[$i];
        }

        // Final cleanup - ensure alphanumeric only
        $mixed = preg_replace('/[^a-zA-Z0-9]/', '', $mixed);

        // Pad if needed after cleanup
        while (strlen($mixed) < $length) {
            $mixed .= substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 1);
        }

        $finalKey = substr($mixed, 0, $length);

        return strtoupper($prefix . $finalKey . $suffix);
    }
}

if (!function_exists('validate_user_api_key')) {
    /**
     * Validate User API Key for SaaS multi-tenant access
     *
     * @param string $provided_key The API key provided in request
     * @return int|null User ID if valid, null if invalid
     */
    function validate_user_api_key(string $provided_key): ?int
    {
        if (empty($provided_key)) {
            return null;
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('api_key', $provided_key)->first();

        return $user ? $user->id : null;
    }
}

if (!function_exists('encrypt_user_secrets')) {
    /**
     * Encrypt all secret keys for a specific user
     *
     * @param int $userID The user ID
     * @param array $secrets Array of secret key values
     * @return array Encrypted secret keys
     */
    function encrypt_user_secrets(int $userID, array $secrets): array
    {
        $encrypted = [];

        foreach ($secrets as $key => $value) {
            if (!empty($value) && !is_encrypted_key($value)) {
                try {
                    $encrypted[$key] = encrypt_secret_key($value, $userID);
                } catch (Exception $e) {
                    log_message('error', "Failed to encrypt secret key {$key} for user {$userID}: " . $e->getMessage());
                    $encrypted[$key] = $value; // Keep original if encryption fails
                }
            } else {
                $encrypted[$key] = $value; // Already encrypted or empty
            }
        }

        return $encrypted;
    }
}

if (!function_exists('decrypt_user_secrets')) {
    /**
     * Decrypt all secret keys for a specific user
     *
     * @param int $userID The user ID
     * @param array $secrets Array of encrypted secret key values
     * @return array Decrypted secret keys
     */
    function decrypt_user_secrets(int $userID, array $secrets): array
    {
        $decrypted = [];

        foreach ($secrets as $key => $value) {
            if (!empty($value)) {
                try {
                    $decrypted[$key] = decrypt_secret_key($value, $userID);
                } catch (Exception $e) {
                    log_message('error', "Failed to decrypt secret key {$key} for user {$userID}: " . $e->getMessage());
                    $decrypted[$key] = $value; // Keep original if decryption fails
                }
            } else {
                $decrypted[$key] = $value; // Empty value
            }
        }

        return $decrypted;
    }
}