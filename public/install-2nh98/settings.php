<?php

$appName = 'MERAF Production Panel'; // Enter your app's name
$documentationURL = '/documentation/prodpanel'; // Enter the URL for your app's documentations
$companyName = 'MERAF Digital Solutions'; // Enter the app's maker name (you company or individual name)
$companyContact = 'contact@merafsolutions.com'; // Enter your email for app support
$companyURL = 'https://merafsolutions.com'; // Enter your URL
$successInstallationMsg = 'Installation success! Please proceed to admin registration <a href="/register" class="alert-link">here</a> or the page will redirect you in 5 seconds.';

/**
 * DO NOT EDIT BEYOND THIS POINT
 */
if (!function_exists('base_url'))
{
    function base_url(): string {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . $host;
        return $baseUrl;
    }
}
?>