<?php
if (! function_exists('base_url')) {
    function base_url(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . $host;
        return $baseUrl;
    }
}

if (isset($_COOKIE['meraf_app_info'])) {
    // Get the JSON string from the cookie
    $merafAppDataJson = $_COOKIE['meraf_app_info'];

    // Decode the JSON string back into an array
    $merafAppData = json_decode($merafAppDataJson, true);

	$appName = $merafAppData['name'];
	$appLogo = $merafAppData['logo'];
	$appIcon = $merafAppData['icon'];

} else {
	$appName = 'License and Digital Product Manager';
	$appLogo = NULL;
	$appIcon = base_url() . '/assets/images/meraf-appIcon.png';
}

?>
<!DOCTYPE html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8">
		<title>API - Documentation</title>
		<meta name="description" content="">
		<meta name="author" content="MERAF Digitial Solutions">

		<meta http-equiv="cleartype" content="on">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="shortcut icon" href="<?= $appIcon ?>" />

		<link rel="stylesheet" href="../assets/css/hightlightjs-dark.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.8.0/highlight.min.js"></script>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;1,300&family=Source+Code+Pro:wght@300&display=swap" rel="stylesheet"> 
		<link rel="stylesheet" href="../assets/css/style.css" media="all">
		<script>hljs.initHighlightingOnLoad();</script>
	</head>
	 
	<body>
		<div class="left-menu">
			<div class="content-logo">
				<div class="logo">
					<a href="<?= base_url() ?>">
						<?php if($appLogo !== NULL) { ?>
							<img alt="<?= $appName ?>" title="Go to main site" src="<?= $appLogo ?>" height="48" style="padding-bottom: 3px;"/>
						<?php } ?>
						<span style="padding-top: 10px;"><?= $appName ?> - API</span>
					</a>
					
				</div>
				<button class="burger-menu-icon" id="button-menu-mobile">
					<svg width="34" height="34" viewBox="0 0 100 100"><path class="line line1" d="M 20,29.000046 H 80.000231 C 80.000231,29.000046 94.498839,28.817352 94.532987,66.711331 94.543142,77.980673 90.966081,81.670246 85.259173,81.668997 79.552261,81.667751 75.000211,74.999942 75.000211,74.999942 L 25.000021,25.000058"></path><path class="line line2" d="M 20,50 H 80"></path><path class="line line3" d="M 20,70.999954 H 80.000231 C 80.000231,70.999954 94.498839,71.182648 94.532987,33.288669 94.543142,22.019327 90.966081,18.329754 85.259173,18.331003 79.552261,18.332249 75.000211,25.000058 75.000211,25.000058 L 25.000021,74.999942"></path></svg>
				</button>
			</div>
			<div class="mobile-menu-closer"></div>
			<div class="content-menu">
				<div class="content-infos">
					<div class="info"><b>Version:</b> 2.2.0</div>
					<div class="info"><b>Last Updated:</b> 20th Oct, 2025</div>
				</div>
				<ul>
					<li class="scroll-to-link active" data-target="content-get-started">
						<a>GET STARTED</a>
					</li>
					<li class="scroll-to-link" data-target="content-routine-validation">
						<a>Routine Validation</a>
					</li>
					<li class="scroll-to-link" data-target="content-dashboard-data">
						<a>Dashboard Data</a>
					</li>
					<li class="scroll-to-link" data-target="content-user-settings">
						<a>User Settings</a>
					</li>
					<li class="scroll-to-link" data-target="content-subscription-management">
						<a>Subscription Management</a>
					</li>
					<li class="scroll-to-link" data-target="content-list-license">
						<a>List License</a>
					</li>
					<li class="scroll-to-link" data-target="content-export-license">
						<a>Export License</a>
					</li>
					<li class="scroll-to-link" data-target="content-verify-license">
						<a>Verify License</a>
					</li>
					<li class="scroll-to-link" data-target="content-retrieve-license">
						<a>Retrieve License Data</a>
					</li>
					<li class="scroll-to-link" data-target="content-retrieve-license-by-txn">
						<a>Retrieve by Transaction ID</a>
					</li>
					<li class="scroll-to-link" data-target="content-retrieve-license-by-key">
						<a>Retrieve by License Key</a>
					</li>
                    <li class="scroll-to-link" data-target="content-create-new-license">
						<a>Create New License</a>
					</li>
					<li class="scroll-to-link" data-target="content-edit-license-details">
						<a>Edit License Details</a>
					</li>
					<li class="scroll-to-link" data-target="content-register-domain-device">
						<a>Register Domain or Device</a>
					</li>
					<li class="scroll-to-link" data-target="content-deactivate-domain-device">
						<a>Deactivate Domain or Device</a>
					</li>
					<li class="scroll-to-link" data-target="content-license-acitivity-log">
						<a>View License Activity Logs</a>
					</li>
					<li class="scroll-to-link" data-target="content-email-subscribers">
						<a>View Email Subscribers</a>
					</li>
					<li class="scroll-to-link" data-target="content-generate-license-key">
						<a>Generate License Key</a>
					</li>
					<li class="scroll-to-link" data-target="content-all-product">
						<a>Products (basename only)</a>
					</li>
					<li class="scroll-to-link" data-target="content-all-product-with-variations">
						<a>Product With Variations</a>
					</li>
					<li class="scroll-to-link" data-target="content-product-files">
						<a>Product Files</a>
					</li>
					<li class="scroll-to-link" data-target="content-product-changelog">
						<a>Product Data/Changelog</a>
					</li>
					<li class="scroll-to-link" data-target="content-product-versions">
						<a>Product versions</a>
					</li>
					<li class="scroll-to-link" data-target="content-all-variations">
						<a>Variations</a>
					</li>
					<li class="scroll-to-link" data-target="content-delete-db-records">
						<a>Delete DB Records</a>
					</li>
					<li class="scroll-to-link" data-target="content-errors">
						<a>Errors</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="content-page">
			<div class="content-code"></div>
			<div class="content">
				<div class="overflow-hidden content-section" id="content-get-started">
					<h1>Get started</h1>
					<pre>
API Endpoint
		 
<?= base_url() ?>/api
						</pre>
					<p>
						<strong>Overview:</strong>
					</p>
					<p>
						The MERAF Production Panel API follows the principles of REST architecture with multi-tenant capabilities. Key features include:
					</p>
					<p>
						<ul>
							<li><strong>Secure Data Management</strong>: Complete tenant data isolation for privacy and security.</li>
							<li><strong>Resource-oriented URLs</strong>: The API utilizes predictable URLs to access resources.</li>
							<li><strong>Form-encoded request bodies</strong>: Requests are encoded using form data.</li>
							<li><strong>JSON-encoded responses</strong>: Responses from the API are encoded in JSON format.</li>
							<li><strong>Common HTTP verbs</strong>: Standard HTTP methods such as GET, POST, PUT, and DELETE are used for CRUD operations.</li>
							<li><strong>Dual authentication</strong>: API secret key + User-API-Key for enhanced security.</li>
							<li><strong>Subscription Integration</strong>: Usage tracking and limit enforcement for billing management.</li>
							<li><strong>Enhanced Security</strong>: Timing-safe authentication, encrypted storage, IP blocking.</li>
						</ul>
					</p>
					<p>
						<strong style="color: red">API Rate Limiting:</strong>
					</p>
					<p>
						To ensure fair usage and maintain optimal performance, the API implements a tiered rate limiting system based on endpoint categories:
					</p>
					<p>
						<ul>
							<li><strong>Authentication Endpoints</strong>: 10 requests per minute per IP address</li>
							<li><strong>Management Endpoints</strong>: 30 requests per minute per IP address (license creation, editing, deletion)</li>
							<li><strong>Information Endpoints</strong>: 60 requests per minute per IP address (listing, verification, logs)</li>
							<li><strong>Exceeding Limits</strong>: Returns HTTP 429 (Too Many Requests) error</li>
						</ul>
					</p>
					<p>
						<strong style="color: red">Data Privacy & Security:</strong>
					</p>
					<p>
						All API responses are automatically secured and isolated to your account data using the User-API-Key:
					</p>
					<p>
						<ul>
							<li><strong>Private Data Access</strong>: You can only access your own licenses, settings, and data</li>
							<li><strong>Secure Authentication</strong>: Dual-layer authentication with API keys for enhanced security</li>
							<li><strong>Encrypted Storage</strong>: Your sensitive data is encrypted with secure encryption methods</li>
							<li><strong>Usage Analytics</strong>: Track your API usage and subscription metrics</li>
						</ul>
					</p>
					<p>
						<strong style="color: red">User API Key Authentication:</strong>
					</p>
					<p>
						It is always required to add a header <strong>'User-API-Key'</strong> in every API call (except for Routine Validation). This acts as a second layer of security to prevent unauthorized access to your Production Panel account.
					</p>
					<p>
						<strong>To view your unique User API key:</strong>
					</p>
					<p>
						<ol>
							<li>Login to your Production Panel website.</li>
							<li>On the top menu, click your avatar.</li>
							<li>Copy the User API key by clicking the underlined value.</li>
						</ol>
					</p>

					<p>
						<img src="../assets/images/user-api-key.gif">
					</p>					
					<p>
						<strong>To obtain your API secret key:</strong>
					</p>
					<p>
						<ol>
							<li>Go to the Production Panel App Settings.</li>
							<li>Navigate to the License Manager tab.</li>
							<li>Select Built-in License Manager.</li>
							<li>Generate Secret Keys and save them for authentication.</li>
						</ol>
					</p>
					<p>
						<img src="../assets/images/generate-api-key.gif">
					</p>
					<p>
						<strong>API Secret Key Usage:</strong>
					</p>
					<p>
						<ol>
							<li><strong>Creation API Secret Key</strong>: Used for creating new license keys.</li>
							<li><strong>Validation API Secret Key</strong>: Employed for validating license keys.</li>
							<li><strong>Domain/Device Registration API Secret Key</strong>: Utilized for registering and deactivating domains/devices associated with specific license keys.</li>
							<li><strong>Managing License Data API Secret Key</strong>: Employed for managing license keys, including tasks such as updating, deleting, and listing all licenses.</li>
							<li><strong>General Info API Secret Key</strong>: Used for general tasks, such as viewing license activity logs and subscriber information.</li>
						</ol>
					</p>
					<p>
						<strong style="color: red">Enhanced Security Features:</strong>
					</p>
					<p>
						MERAF Production Panel implements multiple layers of security protection beyond basic authentication:
					</p>
					<p>
						<ul>
							<li><strong>Advanced Authentication</strong>: Secure API key validation with multiple verification layers</li>
							<li><strong>Encrypted Data Storage</strong>: Your API keys and sensitive data are encrypted using industry-standard methods</li>
							<li><strong>Abuse Protection</strong>: Automatic detection and blocking of suspicious activity patterns</li>
							<li><strong>Activity Logging</strong>: All API operations are logged for security and audit purposes</li>
							<li><strong>Rate Limiting</strong>: Intelligent rate limiting prevents abuse while ensuring optimal performance</li>
							<li><strong>Input Validation</strong>: Comprehensive validation and sanitization of all API requests</li>
							<li><strong>Session Management</strong>: Secure session handling with automatic security features</li>
						</ul>
					</p>
					<p>
						<strong>IP Blocking and Abuse Prevention:</strong>
					</p>
					<p>
						<ul>
							<li><strong>Smart Detection</strong>: System monitors request patterns to identify potential abuse</li>
							<li><strong>Flexible Protection</strong>: Adjustable security thresholds for different activity types</li>
							<li><strong>Progressive Security</strong>: Escalating protection measures based on threat severity</li>
							<li><strong>Trusted Access</strong>: Support for whitelisting known secure connections</li>
							<li><strong>Live Monitoring</strong>: Real-time security monitoring and threat detection</li>
						</ul>
					</p>
				</div>
				<div class="overflow-hidden content-section" id="content-routine-validation">
					<h2>Routine Validation</h2>
					<pre><code class="bash">
curl -X GET '<?= base_url() ?>/validate?t={product_name}&s={license_key}&d={name}'
					</code></pre>
					<p>
						This routine validation checks if the queried domain or device name is registered under the provided license key. Simultaneously, it verifies the license's validity based on parameters such as status, type, expiration date, and the permitted number of registered domains and devices. <span style="color: red">WARNING</span>: This task requires utilizing a different API endpoint and adhering to the URL format outlined in the provided guide and example.
					</p>
					<p>
						Use case:<br>
						1. <strong>Web Applications Validation</strong>: Validates the license's validity against the active domain name of the web application.<br>
						2. <strong>Mobile and Other Applications Validation</strong>: Validates the license's validity against the currently active device.
						<br><br>Make a <span class="method-get">GET</span> call to the following url :<br>
						<code class="higlighted break-word"><?= base_url() ?>/validate?t={product_name}&s={license_key}&d={name}</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

"1"

Error result example :

"0"
						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>product_name</td>
							<td>String</td>
							<td>Product name the license registered to</td>
						</tr>
						<tr>
							<td>license_key</td>
							<td>String</td>
							<td>License key for validation</td>
						</tr>
						<tr>
							<td>name</td>
							<td>String</td>
							<td>The current domain or device name's unique identification for validation with the license</td>
						</tr>
						</tbody>
					</table>
					<p>
						Result Logs:<br>
						All results from this API endpoint are recorded and logged separately. These logs can be accessed as follows:
						<ul>
							<li><strong><a href="<?= base_url() ?>/error-logs" target="_blank">Error Logs</a></strong>: View unsuccessful validation attempts.</li>
							<li><strong><a href="<?= base_url() ?>/success-logs" target="_blank">Success Logs</a></strong>: View successful validation attempts.</li>
						</ul>
					</p>
					<p>
						Both logs are available in the production panel page and can be exported as CSV files.
					</p>
					<p>
						<span style="color: red">NOTE</span>: The <strong><a href="<?= base_url() ?>/license-manager/activity-logs" target="_blank">Activity Log</a></strong> page under License Manager is distinct from these validation logs. The License Activity log records all activities for each license key, providing a broader overview of license-related actions.
					</p>					
				</div>
				<div class="overflow-hidden content-section" id="content-dashboard-data">
					<h2>Dashboard Data</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/dashboard-data'
					</code></pre>
					<p>
						To retrieve tenant-specific dashboard analytics and metrics, initiate a <span class="method-get">GET</span> request using the following URL:<br>
						<code class="higlighted break-word">/dashboard-data</code>
					</p>
					<p>
						This endpoint provides comprehensive dashboard data including license statistics, subscription status, recent activities, and usage metrics for the authenticated tenant.
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
    "license_stats": {
        "total": 25,
        "active": 18,
        "expired": 4,
        "pending": 2,
        "blocked": 1
    },
    "subscription": {
        "package_name": "Professional",
        "status": "active",
        "expires_at": "2025-12-17 12:00:00",
        "usage": {
            "licenses_used": 18,
            "licenses_limit": 100,
            "storage_used": "2.4 GB",
            "storage_limit": "10 GB"
        }
    },
    "recent_activities": [
        {
            "type": "license_created",
            "description": "New license created for Product ABC",
            "timestamp": "2025-09-17 10:30:00"
        }
    ],
    "alerts": [
        {
            "type": "subscription_expiring",
            "message": "Your subscription expires in 30 days",
            "priority": "medium"
        }
    ]
}

Error result example :

{
    "result": "error",
    "message": "Unauthorized access",
    "error_code": 403
}
					</code></pre>
					<p>
						<span style="color: red">NOTE</span>: This endpoint requires User-API-Key authentication and returns data specific to the authenticated tenant only.
					</p>
				</div>
				<div class="overflow-hidden content-section" id="content-user-settings">
					<h2>User Settings</h2>
					<pre><code class="bash">
# Get user settings
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/user/settings'

# Update user settings
curl -H 'User-API-Key: 123abc' -X POST '<?= base_url() ?>/api/user/settings' \
-H 'Content-Type: application/x-www-form-urlencoded' \
-d 'timezone=UTC&locale=en&email_notifications=true'
					</code></pre>
					<p>
						To retrieve or update tenant-specific settings and preferences:<br>
						<code class="higlighted break-word">GET /user/settings</code> - Retrieve current settings<br>
						<code class="higlighted break-word">POST /user/settings</code> - Update settings
					</p>
					<br>
					<pre><code class="json">
Success result example (GET):

{
    "timezone": "UTC",
    "locale": "en",
    "email_notifications": true,
    "default_license_status": "active",
    "default_allowed_domains": 1,
    "default_allowed_devices": 0,
    "license_key_format": "alphanumeric",
    "license_key_length": 40,
    "package_info": {
        "name": "Professional",
        "features": ["license_management", "api_access", "email_support"]
    }
}

Success result example (POST):

{
    "result": "success",
    "message": "Settings updated successfully",
    "updated_settings": ["timezone", "email_notifications"]
}

Error result example :

{
    "result": "error",
    "message": "Invalid timezone value",
    "error_code": 400
}
					</code></pre>
					<h4>SETTABLE PARAMETERS (POST)</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>timezone</td>
							<td>String</td>
							<td>User timezone (e.g., "UTC", "America/New_York")</td>
						</tr>
						<tr>
							<td>locale</td>
							<td>String</td>
							<td>Language locale (e.g., "en", "es", "fr")</td>
						</tr>
						<tr>
							<td>email_notifications</td>
							<td>Boolean</td>
							<td>Enable/disable email notifications</td>
						</tr>
						<tr>
							<td>default_license_status</td>
							<td>String</td>
							<td>Default status for new licenses (active, pending, blocked)</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-subscription-management">
					<h2>Subscription Management</h2>
					<pre><code class="bash">
# Check subscription status
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/subscription/status'

# Get usage analytics
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/subscription/usage'

# Check feature limits
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/subscription/limits'
					</code></pre>
					<p>
						The subscription management endpoints provide access to billing information, usage tracking, and feature limits:
					</p>
					<p>
						<ul>
							<li><code class="higlighted break-word">GET /subscription/status</code> - Current subscription details</li>
							<li><code class="higlighted break-word">GET /subscription/usage</code> - Usage analytics and tracking</li>
							<li><code class="higlighted break-word">GET /subscription/limits</code> - Feature limits and restrictions</li>
						</ul>
					</p>
					<br>
					<pre><code class="json">
Subscription Status example:

{
    "subscription_id": "sub_123456",
    "package_name": "Professional",
    "status": "active",
    "billing_period": "monthly",
    "current_period_start": "2025-09-01 00:00:00",
    "current_period_end": "2025-10-01 00:00:00",
    "next_payment_date": "2025-10-01 00:00:00",
    "amount": "29.99",
    "currency": "USD",
    "payment_method": "PayPal",
    "trial_end": null,
    "auto_renewal": true
}

Usage Analytics example:

{
    "period": "2025-09",
    "usage": {
        "licenses_created": 15,
        "licenses_limit": 100,
        "api_calls": 2843,
        "api_calls_limit": 10000,
        "storage_used_mb": 2458,
        "storage_limit_mb": 10240
    },
    "daily_usage": [
        {"date": "2025-09-01", "licenses": 2, "api_calls": 145},
        {"date": "2025-09-02", "licenses": 1, "api_calls": 203}
    ]
}

Feature Limits example:

{
    "package": "Professional",
    "limits": {
        "max_licenses": 100,
        "max_api_calls_per_month": 10000,
        "max_storage_mb": 10240,
        "features": {
            "license_management": true,
            "api_access": true,
            "email_support": true,
            "phone_support": false,
            "custom_branding": false
        }
    },
    "current_usage": {
        "licenses": 15,
        "api_calls_this_month": 2843,
        "storage_used_mb": 2458
    }
}

Error result example :

{
    "result": "error",
    "message": "Subscription not found or expired",
    "error_code": 404
}
					</code></pre>
					<p>
						<span style="color: red">NOTE</span>: Subscription endpoints automatically enforce usage limits. When limits are exceeded, license creation and other operations may be restricted.
					</p>
				</div>
				<div class="overflow-hidden content-section" id="content-list-license">
					<h2>List license</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/all/{secret_key}'
					</code></pre>
					<p>
						To retrieve the complete list of licenses, irrespective of their status, type, or other attributes, initiate a <span class="method-get">GET</span> request using the following URL:<br>
						<code class="higlighted break-word">/license/all/{secret_key}</code>
					</p>
					<p>
						To retrieve the list of licenses according to available filter and keyword search, initiate a <span class="method-get">GET</span> request using the following URL:<br>
						<code class="higlighted break-word">/license/all/{secret_key}</code>
						<code class="higlighted break-word">/license/all/{secret_key}?status=active</code>
						<code class="higlighted break-word">/license/all/{secret_key}?type=standard</code>
						<code class="higlighted break-word">/license/all/{secret_key}?search=test</code>
						<code class="higlighted break-word">/license/all/{secret_key}?status=active&type=standard&search=test</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
    "draw": 1,
    "recordsTotal": 5,
    "recordsFiltered": 5,
    "data": [
        {
            "id": "2",
            "owner_id": "1",
            "license_key": "0AAHB",
            "max_allowed_domains": "1",
            "max_allowed_devices": "0",
            "license_status": "active",
            "license_type": "lifetime",
            "first_name": "John",
            "last_name": "Doe",
            "email": "otheruser@example.com",
            "item_reference": "",
            "company_name": "N/A",
            "txn_id": "2752",
            "manual_reset_count": "",
            "purchase_id_": "3CM07149U9011364X",
            "date_created": "2024-12-10 16:14:40",
            "date_activated": "2024-12-10 16:14:40",
            "date_renewed": null,
            "date_expiry": null,
            "reminder_sent": "0",
            "reminder_sent_date": null,
            "product_ref": "The special app",
            "until": "",
            "current_ver": "",
            "subscr_id": "",
            "billing_length": "",
            "billing_interval": "onetime"
        },
        {
            "id": "1",
            "owner_id": "1",
            "license_key": "AM8N7",
            "max_allowed_domains": "1",
            "max_allowed_devices": "0",
            "license_status": "expired",
            "license_type": "trial",
            "first_name": "Jane",
            "last_name": "Doe",
            "email": "otheruser@example.com",
            "item_reference": "",
            "company_name": "N/A",
            "txn_id": "",
            "manual_reset_count": "",
            "purchase_id_": "",
            "date_created": "2024-12-04 19:43:28",
            "date_activated": "2024-12-04 19:43:28",
            "date_renewed": null,
            "date_expiry": "2024-12-12 03:23:00",
            "reminder_sent": "2",
            "reminder_sent_date": "2024-12-07 19:45:02",
            "product_ref": "One great app",
            "until": "",
            "current_ver": "",
            "subscr_id": "2751",
            "billing_length": "3",
            "billing_interval": "days"
        }
    ]
}

Empty data:
{
	"draw": 1,
	"recordsTotal": 0,
	"recordsFiltered": 0,
	"data": []
}

Error result example :

{
    "result": "error",
    "message": "Unauthorized access",
    "error_code": 403
}
						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your managing license data secret key</td>
						</tr>
						</tbody>
					</table>
					<h4>OPTIONAL QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>status</td>
							<td>String</td>
							<td>Filter licenses by status (active,pending,blocked,expired)</td>
						</tr>
						<tr>
							<td>type</td>
							<td>String</td>
							<td>Filter licenses by type (trial,subscription,lifetime)</td>
						</tr>
						<tr>
							<td>search</td>
							<td>String</td>
							<td>Search term to filter licenses</td>
						</tr>
						<tr>
							<td>start</td>
							<td>String</td>
							<td>Starting record for pagination</td>
						</tr>
						<tr>
							<td>length</td>
							<td>String</td>
							<td>Number of records to return</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-export-license">
					<h2>Export license</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/export/{secret_key}'
					</code></pre>
					<p>
						To export the list of licenses, irrespective of their status, type, or other attributes, initiate a <span class="method-get">GET</span> request using the following URL:<br>
						<code class="higlighted break-word">/license/export/{secret_key}</code>
					</p>
					<p>
						To retrieve the list of licenses according to available filter and keyword search, initiate a <span class="method-get">GET</span> request using the following URL:<br>
						<code class="higlighted break-word">/license/export/{secret_key}</code>
						<code class="higlighted break-word">/license/export/{secret_key}?status=active</code>
						<code class="higlighted break-word">/license/export/{secret_key}?type=standard</code>
						<code class="higlighted break-word">/license/export/{secret_key}?search=test</code>
						<code class="higlighted break-word">/license/export/{secret_key}?status=active&type=standard&search=test</code>
					</p>
					<br>
					<pre><code class="csv">
ID,License Key,Status,Type,First Name,Last Name,Email,Product,Created Date,Expiry Date
"2","0AAHB","active","lifetime","John","Genç","otheruser@example.com","The special app","2024-12-10 16:14:40",""
"1","AM8N7","expired","trial","Jane","Doe","otheruser@example.com","One great app","2024-12-04 19:43:28","2024-12-12 03:23:00"

Empty data:
{
	"result": "error",
	"message": "No data to export"
	"error_code": 204
}

Error result example :

{
    "result": "error",
    "message": "Unauthorized access",
    "error_code": 403
}
						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your managing license data secret key</td>
						</tr>
						</tbody>
					</table>
					<h4>OPTIONAL QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>status</td>
							<td>String</td>
							<td>Filter licenses by status (active,pending,blocked,expired)</td>
						</tr>
						<tr>
							<td>type</td>
							<td>String</td>
							<td>Filter licenses by type (trial,subscription,lifetime)</td>
						</tr>
						<tr>
							<td>search</td>
							<td>String</td>
							<td>Search term to filter licenses</td>
						</tr>
						<tr>
							<td>start</td>
							<td>String</td>
							<td>Starting record for pagination</td>
						</tr>
						<tr>
							<td>length</td>
							<td>String</td>
							<td>Number of records to return</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-verify-license">
					<h2>Verify license</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/verify/{secret_key}/VN8S8UOUH0E9YM780E3E3WR2M4CBKQBO8QGYU7TG'
					</code></pre>
					<p>
						To retrieve and validate a specific license key, execute a <span class="method-get">GET</span> request using the following URL:<br>
						<code class="higlighted break-word">/license/verify/{secret_key}/{license_key}</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
    "result": "success",
    "code": 200,
    "message": "License key details retrieved.",
    "status": "active",
    "subscr_id": "",
    "first_name": "John",
    "last_name": "Doe",
    "company_name": "",
    "email": "contact@merafsolutions.com",
    "license_key": "VN8S8UOUH0E9YM780E3E3WR2M4CBKQBO8QGYU7TG",
    "license_type": "lifetime",
    "lic_type": "lifetime",
    "max_allowed_domains": "2",
    "max_allowed_devices": "1",
    "item_reference": "Special Product Sample",
    "registered_domains": [
        {
            "id": "999",
            "license_key_id": "50",
            "license_key": "VN8S8UOUH0E9YM780E3E3WR2M4CBKQBO8QGYU7TG",
            "domain_name": "example.com",
            "item_reference": ""
        },
        {
            "id": "1000",
            "license_key_id": "50",
            "license_key": "VN8S8UOUH0E9YM780E3E3WR2M4CBKQBO8QGYU7TG",
            "domain_name": "greatdomain.com",
            "item_reference": ""
        }
    ],
    "registered_devices": [
        {
            "id": "1001",
            "license_key_id": "50",
            "license_key": "VN8S8UOUH0E9YM780E3E3WR2M4CBKQBO8QGYU7TG",
            "device_name": "WINusername",
            "item_reference": ""
        }        
    ],
    "date_created": "2022-03-27 00:00:00",
    "date_renewed": "2022-03-27 00:00:00",
    "date_expiry": "0000-00-00 00:00:00",
    "product_ref": "Special Product Sample",
    "txn_id": "UBSKC8G6NS",
    "until": "",
    "current_ver": ""
}

Error result example :

{
    "result": "error",
    "message": "License key not found",
    "error_code": 60
}
						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your license validation secret key</td>
						</tr>
						<tr>
							<td>license_key</td>
							<td>String</td>
							<td>License key for validation</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-retrieve-license">
					<h2>Retrieve License Data (Robust - Primary Method)</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/data/{secret_key}/{purchase_id}/{product_name}
					</code></pre>
					<p>
						Primary method for retrieving license data with intelligent dual-field search.<br>
						<code class="higlighted break-word">/license/data/{secret_key}/{purchase_id}/{product_name}</code>
					</p>
					<p>
						This endpoint searches for licenses using BOTH the purchase_id_ field OR the txn_id field, making it robust for initial orders and renewals. For more specific searches, see the specialized endpoints below.
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"id": "170",
	"license_key": "VN8S8UOUH0E9YM780E3E3WR2M4CBKQBO8QGYU7TG",
	"max_allowed_domains": "1",
	"max_allowed_devices": "0",
	"license_status": "active",
	"license_type": "lifetime",
	"first_name": "John",
	"last_name": "Doe",
	"email": "contact@merafsolutions.com",
	"item_reference": "Special Product Sample",
	"company_name": "ABC Corp",
	"txn_id": "UBSKC8G6NS",
	"manual_reset_count": "",
	"purchase_id_": "88S72039A8975545M",
	"date_created": "2024-10-01 08:41:18",
	"date_activated": "2024-10-01 08:41:18",
	"date_renewed": null,
	"date_expiry": null,
	"reminder_sent": "1",
	"reminder_sent_date": "2024-10-02 08:46:28",
	"product_ref": "Special Product Sample",
	"until": "",
	"current_ver": "",
	"subscr_id": "2679",
	"billing_length": "",
	"billing_interval": "onetime"
}

Error result example :

{
    "result": "error",
    "message": "License key not found",
    "error_code": 60
}
						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your license general secret key</td>
						</tr>
						<tr>
							<td>purchase_id</td>
							<td>String</td>
							<td>Purchase ID for query</td>
						</tr>
						<tr>
							<td>product_name</td>
							<td>String</td>
							<td>Product name the license registered to</td>
						</tr>				
						</tbody>
					</table>
				</div>				
				<div class="overflow-hidden content-section" id="content-retrieve-license-by-txn">
					<h2>Retrieve License by Transaction ID</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/data-by-txn/{secret_key}/{txn_id}/{product_name}'
					</code></pre>
					<p>
						Specifically retrieves license data using transaction ID (txn_id field) as the primary search criterion.<br>
						<code class="higlighted break-word">/license/data-by-txn/{secret_key}/{txn_id}/{product_name}</code>
					</p>
					<p>
						This endpoint provides more precise matching by searching ONLY the txn_id field, unlike the general /license/data endpoint which searches both purchase_id_ OR txn_id fields.
					</p>
					<p>
						<strong>Use cases:</strong>
						<ul>
							<li>When you need to retrieve licenses specifically by their transaction ID</li>
							<li>When the transaction ID is distinct from the purchase ID</li>
							<li>When you want to avoid ambiguity in dual-field searches</li>
						</ul>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"id": "170",
	"license_key": "VN8S8UOUH0E9YM780E3E3WR2M4CBKQBO8QGYU7TG",
	"max_allowed_domains": "1",
	"max_allowed_devices": "0",
	"license_status": "active",
	"license_type": "lifetime",
	"first_name": "John",
	"last_name": "Doe",
	"email": "contact@merafsolutions.com",
	"item_reference": "Special Product Sample",
	"company_name": "ABC Corp",
	"txn_id": "UBSKC8G6NS",
	"manual_reset_count": "",
	"purchase_id_": "88S72039A8975545M",
	"date_created": "2024-10-01 08:41:18",
	"date_activated": "2024-10-01 08:41:18",
	"date_renewed": null,
	"date_expiry": null,
	"reminder_sent": "1",
	"reminder_sent_date": "2024-10-02 08:46:28",
	"product_ref": "Special Product Sample",
	"until": "",
	"current_ver": "",
	"subscr_id": "2679",
	"billing_length": "",
	"billing_interval": "onetime"
}

Error result example :

{
	"result": "error",
	"message": "License key not found",
	"error_code": 60
}
					</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your license general secret key</td>
						</tr>
						<tr>
							<td>txn_id</td>
							<td>String</td>
							<td>Transaction ID for query (searches ONLY txn_id field)</td>
						</tr>
						<tr>
							<td>product_name</td>
							<td>String</td>
							<td>Product name the license registered to</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-retrieve-license-by-key">
					<h2>Retrieve License by License Key (Ultimate Fallback)</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/data-by-key/{secret_key}/{license_key}'
					</code></pre>
					<p>
						Retrieves license data using ONLY the license key as the search criterion.<br>
						<code class="higlighted break-word">/license/data-by-key/{secret_key}/{license_key}</code>
					</p>
					<p>
						This endpoint is the most reliable retrieval method when you have the license key. It does NOT require product name, making it ideal as an ultimate fallback in cascading retrieval strategies.
					</p>
					<p>
						<strong>Use cases:</strong>
						<ul>
							<li><strong>Subscription renewals:</strong> Retrieve existing license using stored license key from previous order</li>
							<li><strong>Ultimate fallback:</strong> When other retrieval methods fail (purchase ID, transaction ID)</li>
							<li><strong>Direct lookup:</strong> When you only have the license key and not the product name</li>
							<li><strong>Cross-product queries:</strong> When the license key is known but product reference might vary</li>
						</ul>
					</p>
					<p>
						<strong style="color: red">Cascading Fallback Strategy:</strong>
					</p>
					<p>
						This endpoint is designed as part of a bulletproof three-tier retrieval strategy:
						<ol>
							<li><strong>ATTEMPT 1:</strong> Try /license/data with current purchase/order ID</li>
							<li><strong>ATTEMPT 2:</strong> Try /license/data with parent order ID (for renewals)</li>
							<li><strong>ATTEMPT 3:</strong> Try /license/data-by-key with stored license key (ULTIMATE FALLBACK)</li>
						</ol>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"id": "170",
	"license_key": "VN8S8UOUH0E9YM780E3E3WR2M4CBKQBO8QGYU7TG",
	"max_allowed_domains": "1",
	"max_allowed_devices": "0",
	"license_status": "active",
	"license_type": "subscription",
	"first_name": "John",
	"last_name": "Doe",
	"email": "contact@merafsolutions.com",
	"item_reference": "Special Product Sample",
	"company_name": "ABC Corp",
	"txn_id": "UBSKC8G6NS",
	"manual_reset_count": "",
	"purchase_id_": "88S72039A8975545M",
	"date_created": "2024-10-01 08:41:18",
	"date_activated": "2024-10-01 08:41:18",
	"date_renewed": "2024-11-01 08:41:18",
	"date_expiry": "2024-12-01 08:41:18",
	"reminder_sent": "1",
	"reminder_sent_date": "2024-10-02 08:46:28",
	"product_ref": "Special Product Sample",
	"until": "",
	"current_ver": "",
	"subscr_id": "2679",
	"billing_length": "1",
	"billing_interval": "months"
}

Error result example (invalid license key format):

{
	"result": "error",
	"message": "Invalid license key format. License key must be exactly 40 characters.",
	"error_code": 60
}

Error result example (license not found):

{
	"result": "error",
	"message": "License key not found",
	"error_code": 60
}
					</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your license general secret key</td>
						</tr>
						<tr>
							<td>license_key</td>
							<td>String</td>
							<td>40-character license key for direct lookup (no product name required)</td>
						</tr>
						</tbody>
					</table>
					<p>
						<span style="color: red">NOTE</span>: This endpoint validates that the license key is exactly 40 characters before attempting lookup, ensuring data integrity.
					</p>
				</div>
                <div class="overflow-hidden content-section" id="content-create-new-license">
					<h2>Create new license</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/create/{secret_key}/data?license_status=active&license_type=subscription&first_name=John%20Doe&last_name=Smith&email=name@domain.com&subscr_id=54&company_name=ABS%20Inc&max_allowed_domains=2&max_allowed_devices=4&billing_length=30&billing_interval=months&date_expiry=2024-05-27&product_ref=Demo%20Product&txn_id=23NF6K353&purchase_id_=JGHVH865&until=4.2.3&#38;current_ver=3.1.5&item_reference=Direct%20Sale'
					</code></pre>
					<p>
						To create a new license, initiate a <span class="method-get">GET</span> request to the following URL:<br>
						<code class="higlighted break-word">/license/create/{secret_key}/data?{field_parameters}</code>
					</p>
					<p>
						<strong style="color: red">Subscription Integration:</strong>
					</p>
					<p>
						License creation includes automatic subscription validation and usage tracking:
					</p>
					<p>
						<ul>
							<li><strong>Usage Limits</strong>: Checks current license count against subscription limits</li>
							<li><strong>Feature Access</strong>: Validates license management feature availability in user's package</li>
							<li><strong>Usage Tracking</strong>: Automatically tracks license creation for billing analytics</li>
							<li><strong>Trial Restrictions</strong>: Enforces trial account limitations</li>
							<li><strong>Auto-Blocking</strong>: Prevents creation if subscription is expired or limits exceeded</li>
						</ul>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"result": "success",
	"message": "License successfully created. Email license details status was successful.",
	"key": "RYZ26W6E2ZGH3CZVM2OJHUE5R452Y25X5UZGQC1C",
	"code": 400
}

Error result example (product not found) :

{
	"result": "error",
	"message": "License creation failed. Value of 'product_ref' is not in the product list.",
	"error_code": 10
}

Error result example (no value for date_expiry) :

{
	"result": "error",
	"message": "License creation failed. Specify license 'date_expiry'.",
	"error_code": 10
}

Error result example (subscription limit exceeded) :

{
	"result": "error",
	"message": "License creation failed. You have reached your subscription limit of 100 licenses.",
	"error_code": 429
}

Error result example (subscription expired) :

{
	"result": "error",
	"message": "License creation failed. Your subscription has expired. Please renew to continue.",
	"error_code": 403
}


						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
						</thead>
						<tbody>
                            <tr>
                                <td>secret_key</td>
                                <td>String</td>
                                <td>Your license creation secret key</td>
                            </tr>
                            <tr>
                                <td>field_parameters</td>
                                <td>String</td>
                                <td>New license information</td>
                            </tr>
						</tbody>
					</table>
					<br>
                    <h4>FIELD PARAMETERS</h4>
                    <table class="central-overflow-x">
						<thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
						</thead>
						<tbody>
                            <tr>
                                <td>license_key</td>
                                <td>String</td>
                                <td><span style="color: blue">OPTIONAL</span>: If not provided, it will automatically generate license key</td>
                            </tr>
                            <tr>
                                <td>license_status</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: License status after creation. options: 'pending', 'active', 'blocked', 'expired'</td>
                            </tr>
                            <tr>
                                <td>license_type</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: Type of license. options: 'trial', 'subscription', 'lifetime
                            </tr>
                            <tr>
                                <td>first_name</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: License user's first name</td>
                            </tr>
                            <tr>
                                <td>last_name</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: License user's last name</td>
                            </tr>
                            <tr>
                                <td>email</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: Client email address</td>
                            </tr>
                            <tr>
                                <td>subscr_id</td>
                                <td>String</td>
                                <td><span style="color: blue">OPTIONAL</span>: The Subscriber ID (if any). Can be useful if you are using the license key with a recurring payment plan.</td>
                            </tr>
                            <tr>
                                <td>company_name</td>
                                <td>String</td>
                                <td><span style="color: blue">OPTIONAL</span>: License user's company name (if any)</td>
                            </tr>
                            <tr>
                                <td>max_allowed_domains</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: Number of domains/installs in which this license can be used</td>
                            </tr>
                            <tr>
                                <td>max_allowed_devices</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: Number of devices/installs in which this license can be used</td>
                            </tr>
                            <tr>
                                <td>billing_length</td>
                                <td>Numeric</td>
                                <td><span style="color: red">REQUIRED</span>: If license_type = 'subscription'. Length in days or months or years for next renewal or as applicable depending on the billing_interval value</td>
                            </tr>
                            <tr>
                                <td>billing_interval</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: If license_type = 'subscription'. Frequency period of renewal or as applicable. Options:  'days', 'months', 'years' or 'onetime'</td>
                            </tr>
                            <tr>
                                <td>date_expiry</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: If license_type = 'subscription' or 'trial'. The license_status will automatically become expired if the date reached. Format: Y-m-d H:i:s (e.g. 2024-01-30 22:15:00)</td>
                            </tr>
                            <tr>
                                <td>product_ref</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: The product that this license gives access to</td>
                            </tr>
                            <tr>
                                <td>txn_id</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: The unique transaction ID associated with this license key</td>
                            </tr>
                            <tr>
                                <td>purchase_id_</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: This is associated with the purchase ID for third party payment app or platform. </td>
                            </tr>
                            <tr>
                                <td>until</td>
                                <td>String</td>
                                <td><span style="color: blue">OPTIONAL</span>: Until what version this product is supported (if applicable)</td>
                            </tr>
                            <tr>
                                <td>current_ver</td>
                                <td>String</td>
                                <td><span style="color: blue">OPTIONAL</span>: What is the current version of this product</td>
                            </tr>
                            <tr>
                                <td>item_reference</td>
                                <td>String</td>
                                <td><span style="color: blue">OPTIONAL</span>: By the default, it will be the same as product_ref but it can be used for other purpose or internal reference</td>
                            </tr>
							<tr>
                                <td>manual_reset_count</td>
                                <td>numeric</td>
                                <td><span style="color: blue">OPTIONAL</span>: The number of times this license has been manually reset by the admin (use it if you want to keep track of it). It can be helpful for the admin to keep track of manual reset counts</td>
                            </tr>
						</tbody>
					</table>
				</div>
                <div class="overflow-hidden content-section" id="content-edit-license-details">
					<h2>Edit License Details</h2>
					<pre><code class="bash">
curl -X POST '<?= base_url() ?>/api/license/edit/{secret_key}' \
-H 'User-API-Key: 123abc' \
-H 'Content-Type: application/x-www-form-urlencoded' \
-d 'license_key=VN8S8UOUH0E9YM780E3E3WR2M4CBKQBO8QGYU7TG&license_status=active&license_type=trial&first_name=John&last_name=Doe&email=otheruser@example.com&subscr_id=54&company_name=ABS%20Inc&max_allowed_domains=1&max_allowed_devices=0&billing_length=3&billing_interval=days&date_expiry=2025-02-02 10:01:00&product_ref=Demo%20Product&txn_id=23NF6K353&purchase_id_=JGHVH865&until=4.2.3&current_ver=3.1.5&item_reference=Direct%20Sale'
					</code></pre>
					<p>
						To edit/modify the license details, initiate a <span class="method-post">POST</span> request to the following URL:<br>
						<code class="higlighted break-word">/license/edit/{secret_key}</code>
					</p>
					<p>
						<strong style="color: red">Subscription Integration:</strong>
					</p>
					<p>
						License editing includes validation against your subscription features and usage tracking:
					</p>
					<p>
						<ul>
							<li><strong>Feature Validation</strong>: Ensures modifications are allowed under current subscription</li>
							<li><strong>Audit Logging</strong>: Tracks license modifications for compliance and billing</li>
							<li><strong>Secure Access</strong>: Only allows editing of licenses owned by your authenticated account</li>
							<li><strong>Usage Analytics</strong>: Records modification patterns for subscription analytics</li>
						</ul>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"result": "success",
	"message": "License details successfully updated.",
	"key": "VN8S8UOUH0E9YM780E3E3WR2M4CBKQBO8QGYU7TG",
	"code": 240
}

Error result example (incomplete required parameters) :

{
	"result": "error",
	"message": "License detail update failed. Please complete the required parameters",
	"error_code": 220
}

Error result example (product not found) :

{
	"result": "error",
	"message": "License detail update failed. Value of 'product_ref' is not in the product list.",
	"error_code": 220
}


						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
						</thead>
						<tbody>
                            <tr>
                                <td>secret_key</td>
                                <td>String</td>
                                <td>Your managing license data secret key</td>
                            </tr>
						</tbody>
					</table>
					<br>
                    <h4>FIELD PARAMETERS</h4>
                    <table class="central-overflow-x">
						<thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
						</thead>
						<tbody>
							<tr>
                                <td>license_key</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: License key for editing the data</td>
                            </tr>
                            <tr>
                                <td>license_status</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: License status after creation. options: 'pending', 'active', 'blocked', 'expired'</td>
                            </tr>
                            <tr>
                                <td>license_type</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: Type of license. options: 'trial', 'subscription', 'lifetime
                            </tr>
                            <tr>
                                <td>first_name</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: License user's first name</td>
                            </tr>
                            <tr>
                                <td>last_name</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: License user's last name</td>
                            </tr>
                            <tr>
                                <td>email</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: Client email address</td>
                            </tr>
                            <tr>
                                <td>subscr_id</td>
                                <td>String</td>
                                <td><span style="color: blue">OPTIONAL</span>: The Subscriber ID (if any). Can be useful if you are using the license key with a recurring payment plan.</td>
                            </tr>
                            <tr>
                                <td>company_name</td>
                                <td>String</td>
                                <td><span style="color: blue">OPTIONAL</span>: License user's company name (if any)</td>
                            </tr>
                            <tr>
                                <td>max_allowed_domains</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: Number of domains/installs in which this license can be used</td>
                            </tr>
                            <tr>
                                <td>max_allowed_devices</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: Number of devices/installs in which this license can be used</td>
                            </tr>
                            <tr>
                                <td>billing_length</td>
                                <td>Numeric</td>
                                <td><span style="color: red">REQUIRED</span>: If license_type = 'subscription'. Length in days or months or years for next renewal or as applicable depending on the billing_interval value</td>
                            </tr>
                            <tr>
                                <td>billing_interval</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: If license_type = 'subscription'. Frequency period of renewal or as applicable. Options:  'days', 'months', 'years' or 'onetime'</td>
                            </tr>
                            <tr>
                                <td>date_expiry</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: If license_type = 'subscription' or 'trial'. The license_status will automatically become expired if the date reached. Format: Y-m-d H:i:s (e.g. 2024-01-30 22:15:00)</td>
                            </tr>
                            <tr>
                                <td>product_ref</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: The product that this license gives access to</td>
                            </tr>
                            <tr>
                                <td>txn_id</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: The unique transaction ID associated with this license key</td>
                            </tr>
                            <tr>
                                <td>purchase_id_</td>
                                <td>String</td>
                                <td><span style="color: red">REQUIRED</span>: This is associated with the purchase ID for third party payment app or platform. </td>
                            </tr>
                            <tr>
                                <td>until</td>
                                <td>String</td>
                                <td><span style="color: blue">OPTIONAL</span>: Until what version this product is supported (if applicable)</td>
                            </tr>
                            <tr>
                                <td>current_ver</td>
                                <td>String</td>
                                <td><span style="color: blue">OPTIONAL</span>: What is the current version of this product</td>
                            </tr>
                            <tr>
                                <td>item_reference</td>
                                <td>String</td>
                                <td><span style="color: blue">OPTIONAL</span>: By the default, it will be the same as product_ref but it can be used for other purpose or internal reference</td>
                            </tr>
							<tr>
                                <td>manual_reset_count</td>
                                <td>numeric</td>
                                <td><span style="color: blue">OPTIONAL</span>: The number of times this license has been manually reset by the admin (use it if you want to keep track of it). It can be helpful for the admin to keep track of manual reset counts</td>
                            </tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-register-domain-device">
					<h2>Register domain/device to a license</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/register/domain/sampledomain.com/{secret_key}/a16da9b45ff3e8c63579e8c42457393dfa70d798'
					</code></pre>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/register/device/XYZ-Android/{secret_key}/a16da9b45ff3e8c63579e8c42457393dfa70d798'
					</code></pre>
					<p>
						To register a domain or device name to a license, make a <span class="method-get">GET</span> call to the following URL:
						<code class="higlighted break-word">/license/register/{type}/{name}/{secret_key}/{license_key}</code>
					</p>
					<p>
						<span style="color: red">NOTE</span>: By default, if a trial license has already registered a domain or device, and another trial license attempts to register the same domain or device within 7 days of the original license's creation date, the registration will fail. This restriction is in place to prevent potential abuse by creating multiple trial licenses for the same product using the same domain or device.
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"result": "success",
	"message": "Registration of the domain (merafic.test) was successful",
	"error_code": ""
}

Error result example :

{
	"result": "error",
	"message": "The allowed limit for registered domain has been reached",
	"error_code": 50
}
						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>type</td>
							<td>String</td>
							<td>'domain' or 'device'</td>
						</tr>
						<tr>
							<td>name</td>
							<td>String</td>
							<td>Domain or device name's unique identification</td>
						</tr>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your domain/device registration secret key</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-deactivate-domain-device">
					<h2>Deactivate domain/device from a license</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/unregister/domain/sampledomain.com/{secret_key}/a16da9b45ff3e8c63579e8c42457393dfa70d798'
					</code></pre>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/unregister/device/XYZ-Android/{secret_key}/a16da9b45ff3e8c63579e8c42457393dfa70d798'
					</code></pre>
					<p>
						To unregister/deactivate a domain or device name from a license, make a <span class="method-get">GET</span> call to the following URL:<br>
						<code class="higlighted break-word">/license/unregister/{type}/{name}/{secret_key}/{license_key}</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"result": "success",
	"message": "The license key has been deactivated for this domain (merafic.test)",
	"error_code": 340
}

Error result example :

{
	"result": "error",
	"message": "The query domain(sampledomain.com) is not registered in the provided license key",
	"error_code": 404
}
						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>type</td>
							<td>String</td>
							<td>'domain' or 'device'</td>
						</tr>
						<tr>
							<td>name</td>
							<td>String</td>
							<td>Domain or device name's unique identification</td>
						</tr>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your domain/device registration secret key</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-license-acitivity-log">
					<h2>View license activity logs</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/logs/a16da9b45ff3e8c63579e8c42457393dfa70d798/{secret_key}'
					</code></pre>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/logs/all/{secret_key}'
					</code></pre>
					<p>
						To retrieve all license activity logs, initiate a <span class="method-get">GET</span> request using the following URL:<br>
						<code class="higlighted break-word">/license/logs/{options}/{secret_key}</code> <br>
						<code class="higlighted break-word">/license/logs/all/{secret_key}</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

[
	{
		"id": "6702",
		"license_key": "a16da9b45ff3e8c63579e8c42457393dfa70d798",
		"action": "registration: Reached maximum allowable domain",
		"time": "2024-04-28 22:24:14",
		"source": "127.0.0.1"
	},
	{
		"id": "6701",
		"license_key": "a16da9b45ff3e8c63579e8c42457393dfa70d798",
		"action": "registration: Registration of the domain (sampledomain.com) was successful",
		"time": "2024-04-28 22:23:52",
		"source": "127.0.0.1"
	},
	{
		"id": "6700",
		"license_key": "a16da9b45ff3e8c63579e8c42457393dfa70d798",
		"action": "registration: Domain (anotherdomain.com) is already registered under the license",
		"time": "2024-04-28 22:23:40",
		"source": "127.0.0.1"
	},
	{
		"id": "6697",
		"license_key": "a16da9b45ff3e8c63579e8c42457393dfa70d798",
		"action": "registration: The domain (sampledomain.com) is not registered in the provided license key",
		"time": "2024-04-28 22:18:07",
		"source": "127.0.0.1"
	}
]

Error result example :

{
	"result": "error",
	"message": "Invalid API key",
	"error_code": 403
}
						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>options</td>
							<td>String</td>
							<td>'all' or a 'license_key' for specific log</td>
						</tr>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your general info secret key</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-email-subscribers">
					<h2>View email subscribers</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/subscribers/{secret_key}'
					</code></pre>
					<p>
						To retrieve a list of all subscribers, make a <span class="method-get">GET</span> request to the following URL:
						<code class="higlighted break-word">/license/subscribers/{secret_key}</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

[
	{
		"id": "128",
		"license_key": "QLK08J7Z2GE4TQ834QXFO1EDT51MAF2UEXZWLITP",
		"sent_to": "johndoe@gmail.com",
		"status": "success",
		"sent": "yes",
		"date_sent": "2024-04-28 12:35:12",
		"disable_notifications": ""
	},
	{
		"id": "127",
		"license_key": "SZ9MH1UXHQGQJL6AVVD2PYYBXVKW1ACFS0G09FR5",
		"sent_to": "janedoe@gmail.com",
		"status": "success",
		"sent": "yes",
		"date_sent": "2024-04-28 12:10:29",
		"disable_notifications": ""
	}
]

Error result example :

{
	"result": "error",
	"message": "Invalid API key",
	"error_code": 403
}
						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your general info secret key</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-generate-license-key">
					<h2>Generate license key</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/license/generate'
					</code></pre>
					<p>
						To generate a new license key based on the options set in the application settings, make a <span class="method-get">GET</span> call to the following URL:<br>
						<code class="higlighted break-word">/license/generate</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

"V3NYHLOKZ4IRIZ4D9Y8KDM8VZL16BOQ3HD8VZY7J"

						</code></pre>
				</div>
				<div class="overflow-hidden content-section" id="content-all-product">
					<h2>Products (base name only)</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/product/all'
					</code></pre>
					<p>
						To retrieve a list of all products (base name only, excluding variations), make a <span class="method-get">GET</span> request to the following URL:<br>
						<code class="higlighted break-word">/product/all</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

[
	"Demo Product",
	"Another Great Product",
	"Unique Product",
	"My Great Software",
	"Amazing App"
]

Error result example :

{
    "result": "error",
    "message": "Requested data is empty",
    "error_code": 204
}

						</code></pre>
				</div>
				<div class="overflow-hidden content-section" id="content-all-product-with-variations">
					<h2>Product with variations</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/product/with-variations'
					</code></pre>
					<p>
						To retrieve a list of all products including their variations, make a <span class="method-get">GET</span> call to the following URL:<br>
						<code class="higlighted break-word">/product/with-variations</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

[
	"AmazingApp Pro",
	"AmazingApp Lite",	
	"Unique Product Pro",
	"Unique Product Lite",
]

Error result example :

{
    "result": "error",
    "message": "Requested data is empty",
    "error_code": 204
}
						</code></pre>
				</div>
				<div class="overflow-hidden content-section" id="content-product-files">
				<h2>Product Files</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/product/packages/all/{secret_key}'
					</code></pre>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/product/packages/{product_name}/{secret_key}'
					</code></pre>					
					<p>
						To retrieve the list of all uploaded files for either all products or a specific product, make a <span class="method-get">GET</span> call to the following URL:<br>
						<code class="higlighted break-word">/product/packages/all/{secret_key}</code> <br>
						<code class="higlighted break-word">/product/packages/{product_name}/{secret_key}</code>
					</p>
					<br>
					<pre><code class="json">
Success result example (all) :

{
	"AmazingApp": {
		"2": "AmazingApp_v3.0.0.zip",
		"3": "AmazingApp_v2.8.0.zip",
		"4": "AmazingApp_v2.4.3.zip",
		"5": "AmazingApp_v2.1.1.zip"
	},
	"AnotherGreat Software": {
		"2": "AnotherGreat_Software_v1.5.2_Build2325.zip"
	},
	"MyDemo Product": [],
	"Unique Product": {
		"2": "Unique_Product_v3.7.0.zip"
	}
}

Success result example (speicific product) :

{
	"2": "AmazingApp_v3.0.0.zip",
	"3": "AmazingApp_v2.8.0.zip",
	"4": "AmazingApp_v2.4.3.zip",
	"5": "AmazingApp_v2.1.1.zip"
}

Error result example :

{
	"result": "error",
	"message": "Requested product is not existing.",
	"error_code": 500
}
						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>product_name</td>
							<td>String</td>
							<td>'all' to get all the list of files for each product or product name for a specific product only</td>
						</tr>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your general info secret key</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-product-changelog">
				<h2>Product Data/Changelog</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/product/changelog/all/{secret_key}'
					</code></pre>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/product/changelog/{product_name}/{secret_key}'
					</code></pre>					
					<p>
						To retrieve the data or changelog for either all products or a specific product, make a <span class="method-get">GET</span> call to the following URL:<br>
						<code class="higlighted break-word">/product/changelog/all/{secret_key}</code> <br> 
						<code class="higlighted break-word">/product/changelog/{product_name}/{secret_key}</code>
					</p>
					<br>
					<pre><code class="json">
Success result example (all) :

{
	"AmazingApp": {
		"version": "3.7.0",
		"url": "<?= base_url() ?>/download/AmazingApp/AmazingApp_v3.0.0.zip",
		"changelog": "3.0.0~date: 2024-01-21\r\n- [fix] minor bug in ajax submission forms\r\n\r\n2.0.0~date: 2024-02-04\r\n- [new] Maintenance mode feature\r\n\r\n1.0.0~date: 2024-02-04\r\n- Initial release"
	},
	"AnotherGreat Software": {
		"version": "1.5.2",
		"url": "<?= base_url() ?>/download/MerafOne/AnotherGreat_Software_v1.5.2_Build2325.zip",
		"changelog": "1.5.2~date: 2024-01-21\r\n- [new] Automated new theme version notification\r\n\r\n1.0.0~date: 2024-01-15\r\n- Initial release"
	}
}

Success result example (speicific product) :

{
	"version": "3.7.0",
	"url": "<?= base_url() ?>/download/AmazingApp/AmazingApp_v3.0.0.zip",
	"changelog": "3.0.0~date: 2024-01-21\r\n- [fix] minor bug in ajax submission forms\r\n\r\n2.0.0~date: 2024-02-04\r\n- [new] Maintenance mode feature\r\n\r\n1.0.0~date: 2024-02-04\r\n- Initial release"
}

Error result example :

{
	"result": "error",
	"message": "Requested product is not existing.",
	"error_code": 500
}
						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>product_name</td>
							<td>String</td>
							<td>'all' to get all changelog for each product or product name for a specific product only</td>
						</tr>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your general info secret key</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-product-versions">
					<h2>Product versions</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/product/current-versions'
					</code></pre>
					<p>
						To list all product versions including their variations, make a <span class="method-get">GET</span> call to the following URL:<br>
						<code class="higlighted break-word">/product/current-versions</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"AmazingApp Pro": "3.7.0",
	"AmazingApp Lite": "3.7.0",
	"Unique Product Pro": "2.4.0",
	"Unique Product Lite": "2.4.0",
}

Error result example :

{
    "result": "error",
    "message": "Requested data is empty",
    "error_code": 204
}
						</code></pre>
				</div>
				<div class="overflow-hidden content-section" id="content-all-variations">
					<h2>Variations</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/variation/all'
					</code></pre>
					<p>
						To list all variations for products, make a <span class="method-get">GET</span> call to the following URL:<br>
						<code class="higlighted break-word">/variation/all</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"Pro": "AmazingApp,Unique Product",
	"Lite": "AmazingApp,Unique Product"
}

Error result example :

{
    "result": "error",
    "message": "Requested data is empty",
    "error_code": 204
}		

						</code></pre>
				</div>				
				<div class="overflow-hidden content-section" id="content-delete-db-records">
				<h2>Delete DB Records</h2>
					<pre><code class="bash">
* Posted data example:
name="selectedLicense[]" value="143"
name="selectedLicense[]" value="142"
					</code></pre>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X POST '<?= base_url() ?>/api/license/delete/key/{secret_key}/3OQKVS5GT42D8P2NTHJRA0AF0CRL0QUFFG3T06OV'
					</code></pre>
					<p>
						To delete entries in the database by ID(s), make a <span class="method-post">POST</span> call to the following URL:<br>
						<code class="higlighted break-word">/license/delete/{option}/{secret_key}/{license_key}</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"result": "success",
	"message": "Success: Selected license(s) have been successfully deleted.",
	"error_code": "",
	"deleted_licenses": "3OQKVS5GT42D8P2NTHJRA0AF0CRL0QUFFG3T06OV"
	"failed_licenses": ""
}

Error result example :

{
	"result": "error",
	"message": "Validation Error: No license selected for deletion.",
	"error_code": 500,
	"deleted_licenses": "",
	"failed_licenses": ""
}
						</code></pre>
					<h4>QUERY PARAMETERS</h4>
					<table class="central-overflow-x">
						<thead>
						<tr>
							<th>Field</th>
							<th>Type</th>
							<th>Description</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>option</td>
							<td>String</td>
							<td>'key' for license deleteion, 'logs' for log entry, 'all-logs' for all log entires, 'subscriber' for email subscriber</td>
						</tr>
						<tr>
							<td>secret_key</td>
							<td>String</td>
							<td>Your managing license data secret key</td>
						</tr>
						<tr>
							<td>license_key</td>
							<td>String</td>
							<td><span style="color: blue">OPTIONAL</span>: If deletion is for specific license key only in the activity log</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="overflow-hidden content-section" id="content-errors">
					<h2>Errors</h2>
					<p>
						The MERAF Production Panel API uses the following error codes:
					</p>
					<table>
						<thead>
						<tr>
							<th>Error Code</th>
							<th>Meaning</th>
						</tr>
						</thead>
						<tbody>
							<tr>
								<td>10</td>
								<td>
									<code class="higlighted">CREATE_FAILED</code> New license creation request has failed
								</td>
							</tr>
							<tr>
								<td>50</td>
								<td>
									<code class="higlighted">REACHED_MAX_DOMAINS</code> Domain registration has failed as the allowed number of domain reached
								</td>
							</tr>
							<tr>
								<td>60</td>
								<td>
									<code class="higlighted">LICENSE_INVALID</code> Invalid license key was submitted
								</td>
							</tr>
							<tr>
								<td>120</td>
								<td>
									<code class="higlighted">REACHED_MAX_DEVICES</code> Device registration has failed as the allowed number of device reached
								</td>
							</tr>
							<tr>
								<td>200</td>
								<td>
									<code class="higlighted">LICENSE_EXIST</code> The license key found in the records
								</td>
							</tr>
							<tr>
								<td>204</td>
								<td>
									<code class="higlighted">RETURNED_EMPTY</code> The requested data returned empty
								</td>
							</tr>
							<tr>
								<td>220</td>
								<td>
									<code class="higlighted">KEY_UPDATE_FAILED</code> The license detail update failed
								</td>
							</tr>
							<tr>
								<td>240</td>
								<td>
									<code class="higlighted">KEY_UPDATE_SUCCESS</code> The license detail update success
								</td>
							</tr>
							<tr>
								<td>340</td>
								<td>
									<code class="higlighted">KEY_DEACTIVATE_SUCCESS</code> The deactivation request of device or domain was successful
								</td>
							</tr>
							<tr>
								<td>400</td>
								<td>
									<code class="higlighted">LICENSE_CREATED</code> New license creation request was successful
								</td>
							</tr>
							<tr>
								<td>403</td>
								<td>
									<code class="higlighted">FORBIDDEN_ERROR</code> The received request is forbidden. It is either not authorized of the request or received an invalid API secret key
								</td>
							</tr>
							<tr>
								<td>404</td>
								<td>
									<code class="higlighted">QUERY_DOMAINorDEVICE_NOT_EXISTING</code> The query domain/device is not registered in the provided license key
								</td>
							</tr>
							<tr>
								<td>405</td>
								<td>
									<code class="higlighted">METHOD_NOT_ALLOWED</code> The requested method not allowed
								</td>
							</tr>
							<tr>
								<td>429</td>
								<td>
									<code class="higlighted">TOO_MANY_REQUESTS</code> The server has received repeated requests from the same user within a short span of time. For security reasons, the server will cease processing further requests
								</td>
							</tr>
							<tr>
								<td>500</td>
								<td>
									<code class="higlighted">QUERY_NOT_FOUND</code> The requested resource/domain/device/license is not found with the provided parameters
								</td>
							</tr>
							<tr>
								<td>503</td>
								<td>
									<code class="higlighted">QUERY_ERROR</code> Encountered a problem in processing the request
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="content-code"></div>
		</div>
		<script src="../assets/js/script.js"></script>
    </body>
 </html>