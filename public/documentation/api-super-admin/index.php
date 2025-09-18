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
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8">
		<title>Super Admin API - Documentation</title>
		<meta name="description" content="">
		<meta name="author" content="MERAF Digitial Solutions">

		<meta http-equiv="cleartype" content="on">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="shortcut icon" href="<?= base_url() ?>/assets/images/meraf-appIcon.png" />

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
					<a href="<?= base_url() ?>"><img alt="MERAF Production Panel" title="Go to main site" src="../assets/images/logo.png" height="48" style="padding-bottom: 3px;"/></a>
					<span style="padding-top: 10px;">MERAF Production Panel - Super Admin API</span>
				</div>
				<button class="burger-menu-icon" id="button-menu-mobile">
					<svg width="34" height="34" viewBox="0 0 100 100"><path class="line line1" d="M 20,29.000046 H 80.000231 C 80.000231,29.000046 94.498839,28.817352 94.532987,66.711331 94.543142,77.980673 90.966081,81.670246 85.259173,81.668997 79.552261,81.667751 75.000211,74.999942 75.000211,74.999942 L 25.000021,25.000058"></path><path class="line line2" d="M 20,50 H 80"></path><path class="line line3" d="M 20,70.999954 H 80.000231 C 80.000231,70.999954 94.498839,71.182648 94.532987,33.288669 94.543142,22.019327 90.966081,18.329754 85.259173,18.331003 79.552261,18.332249 75.000211,25.000058 75.000211,25.000058 L 25.000021,74.999942"></path></svg>
				</button>
			</div>
			<div class="mobile-menu-closer"></div>
			<div class="content-menu">
				<div class="content-infos">
					<div class="info"><b>Version:</b> 2.1.0</div>
					<div class="info"><b>Last Updated:</b> 17th Sep, 2025</div>
				</div>
				<ul>
					<li class="scroll-to-link active" data-target="content-get-started">
						<a>GET STARTED</a>
					</li>
					<li class="scroll-to-link" data-target="content-cronjob-remind-expiring-license">
						<a>Remind Expiring License</a>
					</li>
					<li class="scroll-to-link" data-target="content-cronjob-autoexpiry-license">
						<a>Auto-change the Status of An Expired License</a>
					</li>
					<li class="scroll-to-link" data-target="content-cronjob-subscription-expiry">
						<a>Check Subscription Expiry</a>
					</li>
					<li class="scroll-to-link" data-target="content-cronjob-payment-retries">
						<a>Process Payment Retries</a>
					</li>
					<li class="scroll-to-link" data-target="content-cronjob-ip-blocking">
						<a>Check Abusive IPs</a>
					</li>
					<li class="scroll-to-link" data-target="content-cronjob-ip-cleanup">
						<a>Clean Blocked IPs</a>
					</li>
                    <li class="scroll-to-link" data-target="content-list-all-user">
						<a>List All Registered User</a>
					</li>
					<li class="scroll-to-link" data-target="content-list-all-package">
						<a>List All Packages</a>
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
						This API Documentation is intended for Super Admin users only. Please refer in this <a href="/documentation/api/">API Documentation</a> for the general API usage.
					</p>
					<p>
						<strong>API Rate Limiting:</strong>
					</p>
					<p>
						The SaaS platform implements a tiered rate limiting system based on endpoint categories. Super Admin endpoints follow the management tier limits:
					</p>
					<p>
						<ul>
							<li><strong>Management Endpoints</strong>: 30 requests per minute per IP address (user management, package operations)</li>
							<li><strong>Cronjob Endpoints</strong>: 60 requests per minute per IP address (automated tasks)</li>
							<li><strong>Exceeding Limits</strong>: Returns HTTP 429 (Too Many Requests) error response</li>
							<li><strong>Security Features</strong>: IP blocking for abusive behavior, automated cleanup</li>
						</ul>
					</p>
					<p>
						<strong>Modify The API Rate Limit:</strong>
					</p>
					<p>
						You can customize the rate limit by modifying the tiered system in:
						<code class="higlighted break-word">/app/Filters/APIThrottle.php</code>

						<br>
						The system uses different limits based on endpoint patterns:
						<table class="central-overflow-x">
							<thead>
							<tr>
								<th>Endpoint Type</th>
								<th>Rate Limit</th>
								<th>Examples</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td>Authentication</td>
								<td>10/minute</td>
								<td>Login, token verification</td>
							</tr>
							<tr>
								<td>Management</td>
								<td>30/minute</td>
								<td>User/package CRUD operations</td>
							</tr>
							<tr>
								<td>Information</td>
								<td>60/minute</td>
								<td>Cronjobs, listing, logs</td>
							</tr>
							</tbody>
						</table>
					</p>
					<p>
						<span style="color: red">NOTE</span>: After making changes, clear the cache:
						<code class="higlighted break-word">php spark cache:clear</code>
						<br>
						Or manually delete: <code class="higlighted break-word">/writable/cache/</code>
					</p>
				</div>
				<div class="overflow-hidden content-section" id="content-cronjob-remind-expiring-license">
					<h2>Remind Expiring License (cron job)</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/cron/run/remind-expiring-license'
					</code></pre>
					<p>
						To set up email reminders for clients with expiring licenses, follow these steps: <br>
						<span style="color: red">WARNING</span>: This task requires utilizing a different API endpoint and adhering to the URL format outlined in the provided guide and example.
					</p>
					<p>
						<strong>Set the reminder schedule:</strong>
					</p>
					<p>
						<ol>
							<li>Go to the Production Panel App Settings.</li>
							<li>Navigate to the License Manager tab.</li>
							<li>Select Built-in License Manager</li>
							<li>Choose Notifications</li>
							<li>Set the number of hours under Reminder For Expiring License Key Email settings</li>
						</ol>
					</p>
					<p>
						Make a <span class="method-get">GET</span> call to the following url :<br>
						<code class="higlighted break-word"><?= base_url() ?>/cron/run/remind-expiring-license</code>
					</p>
					<p>
						<span style="color: red">NOTE</span>: If you have already added the above cron job in your web server control panel, one entry is sufficient to handle all the cron jobs of the production panel and skip the below step.
					</p>
					<p>
						To set it automated, add a cronjob from your Webhosting Panel with the following line, editing the path to your root folder as per your webserver's settings:<br>
						<code class="higlighted break-word">cd web/{path_to_root_folder}/public_html && php spark tasks:run >> /dev/null 2>&1</code>
						Set it to run '<strong>every minute</strong>'
					</p>
					
					<br>
					<pre><code class="json">
Success result example :

{
	"success": true,
	"status": 1,
	"msg": "Reminder on expiring license cron job run successfully! Updated a total of 4 license(s)."
}
						</code></pre>
				</div>
				<div class="overflow-hidden content-section" id="content-cronjob-autoexpiry-license">
					<h2>Auto-change the Status of An Expired License (cron job)</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/cron/run/autoexpiry-license'
					</code></pre>
					<p>
						To automatically change the status to "expired" for licenses that have expired, follow these steps: <br>
						<span style="color: red">WARNING</span>: This task requires utilizing a different API endpoint and adhering to the URL format outlined in the provided guide and example.
					</p>
					<p>
						Make a <span class="method-get">GET</span> call to the following url :<br>
						<code class="higlighted break-word"><?= base_url() ?>/cron/run/autoexpiry-license</code>
					</p>
					<p>
						<span style="color: red">NOTE</span>: If you have already added the above cron job in your web server control panel, one entry is sufficient to handle all the cron jobs of the production panel and skip the below step.
					</p>
					<p>
						To set it automated, add a cronjob from your Webhosting Panel with the following line, editing the path to your root folder as per your webserver's settings:<br>
						<code class="higlighted break-word">cd web/{path_to_root_folder}/public_html && php spark tasks:run >> /dev/null 2>&1</code>
						Set it to run '<strong>every minute</strong>'
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"success": true,
	"status": 1,
	"msg": "Auto-expiry cron job run successfully! Updated a total of 2 license(s)."
}
						</code></pre>
				</div>
				<div class="overflow-hidden content-section" id="content-cronjob-subscription-expiry">
					<h2>Check Subscription Expiry (cron job)</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/cronjob/check_subscription_expiry'
					</code></pre>
					<p>
						To automatically process expired subscriptions and handle billing renewals, follow these steps: <br>
						<span style="color: red">WARNING</span>: This SaaS-specific task manages subscription billing and tenant access controls.
					</p>
					<p>
						Make a <span class="method-get">GET</span> call to the following url :<br>
						<code class="higlighted break-word"><?= base_url() ?>/cronjob/check_subscription_expiry</code>
					</p>
					<p>
						This cronjob handles:
						<ul>
							<li>Processing expired subscriptions</li>
							<li>Disabling access for non-paying tenants</li>
							<li>Sending subscription renewal notifications</li>
							<li>Managing grace periods for payment processing</li>
						</ul>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"success": true,
	"status": 1,
	"msg": "Subscription expiry check completed! Processed 5 expired subscriptions."
}
					</code></pre>
				</div>
				<div class="overflow-hidden content-section" id="content-cronjob-payment-retries">
					<h2>Process Payment Retries (cron job)</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/cronjob/process_payment_retries'
					</code></pre>
					<p>
						To automatically retry failed payments with exponential backoff strategy: <br>
						<span style="color: red">WARNING</span>: This manages the automated payment retry system for subscription billing.
					</p>
					<p>
						Make a <span class="method-get">GET</span> call to the following url :<br>
						<code class="higlighted break-word"><?= base_url() ?>/cronjob/process_payment_retries</code>
					</p>
					<p>
						This cronjob handles:
						<ul>
							<li>Retrying failed payment attempts</li>
							<li>Implementing exponential backoff delays</li>
							<li>Managing dunning sequences</li>
							<li>Sending payment failure notifications</li>
							<li>Suspending accounts after max retry attempts</li>
						</ul>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"success": true,
	"status": 1,
	"msg": "Payment retry processing completed! Attempted 12 retries, 8 successful."
}
					</code></pre>
				</div>
				<div class="overflow-hidden content-section" id="content-cronjob-ip-blocking">
					<h2>Check Abusive IPs (cron job)</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/cronjob/check_abusive_ips'
					</code></pre>
					<p>
						To automatically detect and block abusive IP addresses based on request patterns: <br>
						<span style="color: red">WARNING</span>: This security feature protects the platform from abuse and attacks.
					</p>
					<p>
						Make a <span class="method-get">GET</span> call to the following url :<br>
						<code class="higlighted break-word"><?= base_url() ?>/cronjob/check_abusive_ips</code>
					</p>
					<p>
						This cronjob handles:
						<ul>
							<li>Analyzing request patterns for abuse detection</li>
							<li>Blocking IPs exceeding rate limits</li>
							<li>Identifying potential brute force attacks</li>
							<li>Protecting against API abuse</li>
							<li>Maintaining IP whitelist/blacklist</li>
						</ul>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"success": true,
	"status": 1,
	"msg": "IP abuse check completed! Blocked 3 abusive IPs, analyzed 1247 requests."
}
					</code></pre>
				</div>
				<div class="overflow-hidden content-section" id="content-cronjob-ip-cleanup">
					<h2>Clean Blocked IPs (cron job)</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/cronjob/clean_blocked_ips'
					</code></pre>
					<p>
						To automatically clean up old IP blocks and maintain the IP blocking database: <br>
						<span style="color: red">WARNING</span>: This maintenance task prevents the IP blocking table from growing indefinitely.
					</p>
					<p>
						Make a <span class="method-get">GET</span> call to the following url :<br>
						<code class="higlighted break-word"><?= base_url() ?>/cronjob/clean_blocked_ips</code>
					</p>
					<p>
						This cronjob handles:
						<ul>
							<li>Removing expired IP blocks</li>
							<li>Cleaning up old abuse records</li>
							<li>Optimizing IP blocking database performance</li>
							<li>Maintaining reasonable block durations</li>
							<li>Preventing database bloat</li>
						</ul>
					</p>
					<br>
					<pre><code class="json">
Success result example :

{
	"success": true,
	"status": 1,
	"msg": "IP cleanup completed! Removed 45 expired blocks, cleaned 128 old records."
}
					</code></pre>
				</div>
				<div class="overflow-hidden content-section" id="content-list-all-user">
					<h2>List all registered user</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/user/all/{secret_key}'
					</code></pre>
					<p>
						To retrieve the complete list of registered users, initiate a <span class="method-get">GET</span> request using the following URL:<br>
						<code class="higlighted break-word">/user/all/{secret_key}</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

[
    {
        "2": {
            "avatar": null,
            "username": "User2",
            "email": "user2@greatdomain.com",
            "registered": {
                "date": "2024-11-05 12:32:09.000000",
                "timezone_type": 3,
                "timezone": "UTC"
            },
            "package": "",
            "status": "",
            "package_expiry": "",
            "last_login": {
                "date": "2024-11-11 09:25:34.000000",
                "timezone_type": 3,
                "timezone": "UTC"
            },
            "last_ip": "127.0.0.1",
            "deleted_at": ""
        },
        "1": {
            "avatar": "96f073b92681b6ff1f785c92c984ed5df4f84752.jpeg",
            "username": "MERAF",
            "email": "contact@merafsolutions.com",
            "registered": {
                "date": "2024-11-05 09:37:18.000000",
                "timezone_type": 3,
                "timezone": "UTC"
            },
            "package": "",
            "status": "",
            "package_expiry": "",
            "last_login": {
                "date": "2024-11-12 09:12:31.000000",
                "timezone_type": 3,
                "timezone": "UTC"
            },
            "last_ip": "127.0.0.1",
            "deleted_at": ""
        }
    }
]

Error result example :

{
    "result": "error",
    "message": "Unauthorized access",
    "error_code": 403
}r
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
				</div>
				<div class="overflow-hidden content-section" id="content-list-all-package">
					<h2>List all packagesr</h2>
					<pre><code class="bash">
curl -H 'User-API-Key: 123abc' -X GET '<?= base_url() ?>/api/package/all/{secret_key}'
					</code></pre>
					<p>
						To retrieve the complete list of packages, initiate a <span class="method-get">GET</span> request using the following URL:<br>
						<code class="higlighted break-word">/package/all/{secret_key}</code>
					</p>
					<br>
					<pre><code class="json">
Success result example :

[
    {
        "id": "1",
        "owner_id": "1",
        "package_name": "Trial",
        "price": "0.00",
        "validity": "14",
        "validity_duration": "day",
        "visible": "on",
        "highlight": "off",
        "is_default": "on",
        "status": "active",
        "sort_order": "0",
        "package_modules": "{\"license_management\": {\"licenseprefix\": {\"value\": \"true\", \"enabled\": \"true\"}, \"licensesuffix\": {\"value\": \"true\", \"enabled\": \"true\"}}, \"digital_product_management\": {\"filestorage\": {\"value\": \"30\", \"enabled\": \"true\"}, \"productcountlimit\": {\"value\": \"3\", \"enabled\": \"true\"}}}",
        "created_at": "2024-11-20 08:44:01",
        "updated_at": "2024-11-20 08:48:31"
    }
]

Error result example :

{
    "result": "error",
    "message": "Unauthorized access",
    "error_code": 403
}r
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
								<td>429</td>
								<td>
									<code class="higlighted">TOO_MANY_REQUESTS</code> The server has received repeated requests from the same user within a short span of time. For security reasons, the server will cease processing further requests
								</td>
							</tr>
							<tr>
								<td>500</td>
								<td>
									<code class="higlighted">QUERY_NOT_FOUND</code> Received a request which is not existing
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