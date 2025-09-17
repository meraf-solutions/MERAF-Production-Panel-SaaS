<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UserSettingsModel extends Model
{
    protected $table = 'settings';
    protected $allowedFields = ['class', 'key', 'value', 'owner_id'];

    public function setUserSetting($key, $value, $owner_id)
    {
        // Load security helper for encryption
        helper('security');

        // List of secret keys that should be encrypted
        $secretKeys = [
            'License_Create_SecretKey',
            'License_Validate_SecretKey',
            'License_DomainDevice_Registration_SecretKey',
            'Manage_License_SecretKey',
            'General_Info_SecretKey',
            'reCAPTCHA_Secret_Key'
        ];

        // Encrypt the value if it's a secret key and not already encrypted
        $finalValue = $value;
        if (in_array($key, $secretKeys) && !empty($value)) {
            try {
                // Only encrypt if it's not already encrypted
                if (!is_encrypted_key($value)) {
                    $finalValue = encrypt_secret_key($value, $owner_id);
                    log_message('info', "Auto-encrypted secret key '{$key}' for user {$owner_id}");
                } else {
                    // Already encrypted, keep as-is
                    $finalValue = $value;
                }
            } catch (Exception $e) {
                // If encryption fails, log error but continue with plaintext
                log_message('error', "Failed to encrypt secret key '{$key}' for user {$owner_id}: " . $e->getMessage());
                $finalValue = $value;
            }
        }

        // Check if the setting for this user already exists
        $existing = $this->where('key', $key)->where('owner_id', $owner_id)->first();

        if ($existing) {
            // Update the existing setting
            $result = $this->update($existing['id'], ['value' => $finalValue]);
        } else {
            // Insert new setting
            $result = $this->insert([
                'class'    => 'Config\App',
                'key'      => $key,
                'value'    => $finalValue,
                'owner_id' => $owner_id
            ]);
        }

        return $result;
    }

    public function getUserSetting($key, $owner_id)
    {
        return $this->where('key', $key)->where('owner_id', $owner_id)->first();
    }

    public function getOwnerBySecretKey($type, $secretKey)
    {
        helper('security');

        switch ($type) {
            case 'create':
                $key = 'License_Create_SecretKey';
                break;

            case 'validate':
                $key = 'License_Validate_SecretKey';
                break;

            case 'activation':
                $key = 'License_DomainDevice_Registration_SecretKey';
                break;

            case 'manage':
                $key = 'Manage_License_SecretKey';
                break;

            default:
                $key = 'General_Info_SecretKey';
        }

        // Get all settings with this key (could be multiple users)
        $settings = $this->where('key', $key)->findAll();

        foreach ($settings as $setting) {
            try {
                // Decrypt the stored value for comparison
                $decryptedValue = decrypt_secret_key($setting['value'], $setting['owner_id']);

                // Use timing-safe comparison
                if (timing_safe_equals($decryptedValue, $secretKey)) {
                    return (int) $setting['owner_id'];
                }
            } catch (Exception $e) {
                // If decryption fails, try direct comparison (backward compatibility)
                if (timing_safe_equals($setting['value'], $secretKey)) {
                    return (int) $setting['owner_id'];
                }
            }
        }

        return false;
    }
}
