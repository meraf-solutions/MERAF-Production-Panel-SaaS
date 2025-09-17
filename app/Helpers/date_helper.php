<?php

if (!function_exists('formatDate')) {
    /**
     * Format a UTC date string into user's preferred timezone and format
     * For display purposes only - database should always store UTC
     * Format: "MMM DD, YYYY HH:mm:ss (Timezone)"
     * Example: "Dec 06, 2024 14:30:00 (GMT+08:00)"
     * 
     * @param string|null $utcDate The UTC date string from database
     * @param array $myConfig The application configuration array containing defaultTimezone
     * @return string The formatted date string or empty string if date is null
     */
    function formatDate(?string $utcDate, array $myConfig = null): string 
    {
        if (empty($utcDate) || $utcDate === '0000-00-00 00:00:00') {
            return '';
        }
    
        try {
            // First check session for detected timezone
            $session = session();
            $userTimezone = $session->get('detected_timezone') ?? 
                            $myConfig['defaultTimezone'] ?? 
                            'UTC';

            // Create DateTime object from UTC input
            $datetime = new DateTime($utcDate, new DateTimeZone('UTC'));
            
            // Convert to detected/saved timezone
            $datetime->setTimezone(new DateTimeZone($userTimezone));
            
            // Get the GMT offset
            $offset = $datetime->format('P'); // Format: +00:00 or -00:00
            
            // Format date with GMT offset
            return $datetime->format('F d, Y H:i:s') . ' (GMT' . $offset . ')';
        } catch (Exception $e) {
            log_message('error', '[Helper/Date] Value to be converted: ' . $utcDate);
            log_message('error', '[Helper/Date] Error formatting date: ' . $e->getMessage());
            return '';
        }
    }
}