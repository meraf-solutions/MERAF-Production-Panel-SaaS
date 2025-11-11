<?php

declare(strict_types=1);

/**
 * Custom Session Authenticator - Extends CodeIgniter Shield Session Authenticator
 *
 * This class fixes a critical bug in Shield's remember-me functionality where token
 * expiry validation was missing in checkRememberMeToken() method.
 *
 * BUG FIX: The original Shield Session authenticator does not validate token expiry
 * in the checkRememberMeToken() method (lines 619-636). This allows expired tokens
 * to be accepted, effectively making tokens never expire.
 *
 * SOLUTION: Override checkRememberMeToken() to add proper expiry validation using
 * CodeIgniter\I18n\Time for accurate date comparison.
 *
 * @see vendor/codeigniter4/shield/src/Authentication/Authenticators/Session.php
 */

namespace App\Authentication\Authenticators;

use CodeIgniter\I18n\Time;
use stdClass;

class Session extends \CodeIgniter\Shield\Authentication\Authenticators\Session
{
    /**
     * Validates a remember-me token with proper expiry checking
     *
     * This method overrides the parent to add missing token expiry validation.
     * The original implementation only checks selector and validator matching
     * but never validates if the token has expired.
     *
     * @param string $remember The remember-me token from cookie (format: selector:validator)
     *
     * @return false|stdClass Returns token object if valid, false otherwise
     */
    protected function checkRememberMeToken(string $remember)
    {
        // Call parent method to perform basic token validation
        // (selector lookup, validator hash comparison)
        $token = parent::checkRememberMeToken($remember);

        // If parent validation failed, return false
        if ($token === false) {
            log_message('info', '[SHIELD-FIX] Remember-me token validation failed (invalid selector or validator)');
            return false;
        }

        // CRITICAL FIX: Validate token expiry (missing from parent implementation)
        // Parse the expiry datetime from the token record
        $expiryTime = Time::parse($token->expires);
        $currentTime = Time::now();

        // Check if token has expired
        if ($currentTime->isAfter($expiryTime)) {
            log_message('info', '[SHIELD-FIX] Remember-me token expired. Expiry: {expiry}, Current: {current}', [
                'expiry' => $expiryTime->format('Y-m-d H:i:s'),
                'current' => $currentTime->format('Y-m-d H:i:s'),
            ]);

            // Token expired - clean up by deleting the expired token
            $this->getRememberModel()->purgeRememberTokens($this->provider->findById($token->user_id));

            return false;
        }

        // Token is valid and not expired
        log_message('debug', '[SHIELD-FIX] Remember-me token validated successfully. User ID: {user_id}, Expires: {expiry}', [
            'user_id' => $token->user_id,
            'expiry' => $expiryTime->format('Y-m-d H:i:s'),
        ]);

        return $token;
    }

    /**
     * Helper method to access the RememberModel from parent class
     *
     * Since rememberModel is protected in parent, we need to access it
     * via reflection or by using a helper method. This provides clean access.
     *
     * @return \CodeIgniter\Shield\Models\RememberModel
     */
    private function getRememberModel(): \CodeIgniter\Shield\Models\RememberModel
    {
        // Access protected rememberModel property from parent class
        $reflection = new \ReflectionClass(parent::class);
        $property = $reflection->getProperty('rememberModel');
        $property->setAccessible(true);

        return $property->getValue($this);
    }
}
