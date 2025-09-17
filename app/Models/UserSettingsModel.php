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
        // Check if the setting for this user already exists
        $existing = $this->where('key', $key)->where('owner_id', $owner_id)->first();

        if ($existing) {
            // Update the existing setting
            $result = $this->update($existing['id'], ['value' => $value]);
        } else {
            // Insert new setting
            $result = $this->insert([
                'class'    => 'Config\App',
                'key'      => $key,
                'value'    => $value,
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

        $search = $this->where('key', $key)->where('value', $secretKey)->first();

        if($search) {
            return (int) $search['owner_id'];
        }
        else {
            return false;
        }
    }
}
