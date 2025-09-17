<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Authorization\Authorization;

class UserModel extends ShieldUserModel
{
    protected $lastErrors = [];

    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields = [
            ...$this->allowedFields,
            'avatar',
            'api_key',
            'first_name',
            'last_name',
        ];
    }
	
	/**
     * Update user details
     *
     * @param int $userId
     * @param array $userData
     * @return bool
     */
    public function updateUserDetails(int $userId, array $userData): bool
    {
        $user = $this->find($userId);
        
        if (!$user) {
            $this->lastErrors = ['general' => 'User not found'];
            return false;
        }

        log_message('debug', '[UserModel] Received User Data for update: ' . json_encode($userData));

        try {
            // Filter out any empty values
            $userData = array_filter($userData, fn($value) => $value !== '' && $value !== null);
            
            // Ensure we're only updating allowed fields
            $allowedFields = ['username', 'email', 'first_name', 'last_name'];
            $userData = array_intersect_key($userData, array_flip($allowedFields));

            // Check if email is being updated
            if (isset($userData['email'])) {
                $db = \Config\Database::connect();
                
                // Get current user's email from auth_identities
                $currentEmail = $db->table('auth_identities')
                    ->where('user_id', $userId)
                    ->where('type', 'email_password')
                    ->get()
                    ->getRow('secret');

                if ($userData['email'] !== $currentEmail) {
                    // Check if email exists for any other user
                    $existingEmail = $db->table('auth_identities')
                        ->where('secret', $userData['email'])
                        ->where('type', 'email_password')
                        ->where('user_id !=', $userId)
                        ->get()
                        ->getRow();

                    if ($existingEmail) {
                        $this->lastErrors = ['email' => 'This email is already in use by another account'];
                        return false;
                    }

                    // Update email in auth_identities table
                    $db->table('auth_identities')
                        ->where('user_id', $userId)
                        ->where('type', 'email_password')
                        ->update(['secret' => $userData['email']]);

                    // Remove email from userData as it's handled separately
                    unset($userData['email']);
                }
            }

            // Check if username is being updated and if it's already in use
            if (isset($userData['username']) && $userData['username'] !== $user->username) {
                $existingUser = $this->where('username', $userData['username'])->first();
                if ($existingUser && $existingUser->id != $userId) {
                    $this->lastErrors = ['username' => 'This username is already in use by another account'];
                    return false;
                }
            }

            // Use Shield's User entity to update and validate data
            foreach ($userData as $key => $value) {
                $user->$key = $value;
            }

            // Save the user entity
            if ($this->save($user) === false) {
                $this->lastErrors = $this->errors();
                return false;
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', '[UserModel] Error updating user details: ' . $e->getMessage());
            $this->lastErrors = ['general' => 'An error occurred while updating user details'];
            return false;
        }
    }

    /**
     * Change user password by admin
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function adminChangePassword(int $userId, string $newPassword): bool
    {
        try {
            $user = $this->find($userId);
            
            if (!$user) {
                $this->lastErrors = ['user' => 'User not found'];
                return false;
            }

            // Use Shield's built-in password change method
            $user->password = $newPassword;
            
            // The save method will handle password hashing and validation
            if ($this->save($user) === false) {
                $this->lastErrors = $this->errors();
                return false;
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', '[UserModel] Error changing user password: ' . $e->getMessage());
            $this->lastErrors = ['general' => 'An error occurred while changing password'];
            return false;
        }
    }

    /**
     * Set user role/group
     *
     * @param int $userId
     * @param string $group
     * @return bool
     */
    public function setUserGroup(int $userId, string $group): bool
    {
        $user = $this->find($userId);
        
        if (!$user) {
            $this->lastErrors = ['user' => 'User not found'];
            return false;
        }
    
        // Validate group
        $allowedGroups = ['user', 'admin'];
        if (!in_array($group, $allowedGroups)) {
            $this->lastErrors = ['group' => 'Invalid group'];
            return false;
        }
    
        try {
            // Get the groups service
            $groupModel = model('GroupModel');
            
            // Remove all existing groups
            foreach ($user->getGroups() as $existingGroup) {
                $user->removeGroup($existingGroup);
            }
            
            // Add new group
            $user->addGroup($group);
    
            // Save the changes
            if ($this->save($user) === false) {
                $this->lastErrors = $this->errors();
                return false;
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', '[UserModel] Error setting user group: ' . $e->getMessage());
            $this->lastErrors = ['general' => 'An error occurred while setting user group'];
            return false;
        }
    }

    /**
     * Get the user's current group
     *
     * @param int $userID
     * @return string|null The user's group, or null if no group found
     */
    public function getUserGroup(int $userID): ?string
    {
        $user = $this->find($userID);
        
        if (!$user) {
            return null;
        }

        // Get the user's groups
        $groups = $user->getGroups();
        
        // Return the first group (assuming a user belongs to only one group)
        return !empty($groups) ? $groups[0] : null;
    }

    /**
     * Generate and set an API key for a user
     *
     * @param int $userID
     * @return string|null The generated API key, or null if the operation failed
     */
    public function generateUserApiKey(int $userID): ?string
    {
        $user = $this->find($userID);
        
        if (!$user) {
            $this->lastErrors = ['user' => 'User not found'];
            return null;
        }

        $apiKey = generateApiKey(3);
        $user->api_key = $apiKey;

        if ($this->save($user) === false) {
            $this->lastErrors = $this->errors();
            return null;
        }

        return $apiKey;
    }

    /**
     * Revoke the API key for a user
     *
     * @param int $userID
     * @return bool True if the operation was successful, false otherwise
     */
    public function revokeUserApiKey(int $userID): bool
    {
        $user = $this->find($userID);
        
        if (!$user) {
            $this->lastErrors = ['user' => 'User not found'];
            return false;
        }

        $user->api_key = null;

        if ($this->save($user) === false) {
            $this->lastErrors = $this->errors();
            return false;
        }

        return true;
    }

    /**
     * Get the API key for a user
     *
     * @param int $userID
     * @return string|null The API key, or null if not set
     */
    public function getUserApiKey(int $userID): ?string
    {
        $user = $this->find($userID);
        return $user ? $user->api_key : null;
    }
	
    /**
     * Soft delete a user
     *
     * @param int $userID
     * @return bool
     */
    public function softDeleteUser(int $userID): bool
    {
        $user = $this->find($userID);
    
        if (!$user) {
            $this->lastErrors = ['user' => 'User not found'];
            return false;
        }
    
        try {
            // ✅ First cancel any active subscriptions BEFORE deleting the user
            $subscriptionModel = new \App\Models\SubscriptionModel();
            if (!$subscriptionModel->cancelUserActiveSubscription($userID, 'User deletion')) {
                $this->lastErrors = ['subscription' => 'Failed to cancel active subscription.'];
                return false;
            }
    
            // ✅ Now perform soft delete
            if ($this->delete($userID) === false) {
                $this->lastErrors = $this->errors();
                return false;
            }
    
            // Delete the user's data folder
            $userDataPath = USER_DATA_PATH . $userID;
    
            if (!deleteDirectory($userDataPath)) {
                log_message('error', '[UserModel] Failed to delete user directory: ' . $userDataPath);
                $this->lastErrors = ['filesystem' => 'User directory could not be deleted'];
                return false;
            }
    
            // Delete user's licenses
            $licensesModel = new \App\Models\LicensesModel();
            if (!$licensesModel->deleteByOwnerID($userID)) {
                log_message('error', '[UserModel] Failed to delete user\'s license entries from the license table.');
                $this->lastErrors = ['license' => 'User\'s license entries could not be deleted'];
                return false;
            }
    
            return true;
    
        } catch (\Throwable $e) {
            log_message('error', '[UserModel] Error soft deleting user: ' . $e->getMessage());
            $this->lastErrors = ['general' => 'An error occurred while deleting the user'];
            return false;
        }
    }    
	
	/**
     * Get last error messages
     *
     * @return array
     */
    public function getLastErrors(): array
    {
        return $this->lastErrors;
    }

    /**
     * Assigns the 'admin' role to the user if their ID is 1.
     *
     * @param int $userId The ID of the user to check.
     * @return bool True if the role was assigned, false otherwise.
     */
    public function assignAdminRole()
    {
        return $this->setUserGroup(1, 'admin');
    }     
}
