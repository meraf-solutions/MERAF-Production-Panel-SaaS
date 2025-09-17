<?php

if (!function_exists('setMyLocale')) {
    function setMyLocale()
    {
        $userID = auth()->id() ?? NULL;
        $myConfig = getMyConfig('Config\App', $userID);
        
        // Set the locale dynamically based on user preference
        $userLocale = service('request')->getCookie('user_locale');
        
        if ($userLocale !== null) {
            service('request')->setLocale($userLocale);
        } else {
            $defaultLocale = $myConfig['defaultLocale'] ?? 'en';
            service('request')->setLocale($defaultLocale);
        }
    }
}

if (!function_exists('setMyTimezone')) {
    function setMyTimezone()
    {
        $userID = auth()->id() ?? NULL;
        $myConfig = getMyConfig('Config\App', $userID);
        
        // Set the timezone dynamically based on user preference

        // First check session for detected timezone
        $session = session();
        $userTimezone = $session->get('detected_timezone') ?? 
                        $myConfig['defaultTimezone'] ?? 
                        'UTC';

        $defaultTimezone = $userTimezone;
        date_default_timezone_set($defaultTimezone);
    }
}
