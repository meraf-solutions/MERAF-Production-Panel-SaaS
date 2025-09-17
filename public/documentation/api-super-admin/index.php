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
					<div class="info"><b>Version:</b> 1.0.0</div>
					<div class="info"><b>Last Updated:</b> 28th Mar, 2024</div>
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
						To ensure fair usage and maintain optimal performance for all users, our API implements rate limiting. Each IP address is allowed a maximum of 15 requests per minute. Exceeding this limit will result in a Error 429 (Too Many Requests) error response.
					</p>
					<p>
						<ul>
							<li><strong>Maximum Requests</strong>: Each IP address is limited to a maximum of 15 requests per minute.</li>
							<li><strong>Exceeding the Limit</strong>: If the rate limit is exceeded, subsequent requests from the same IP address within the same minute will be rejected with a 429 Too Many Requests status code.</li>
							<li><strong>Fair Usage:</strong>: Rate limiting helps ensure fair usage of the API and prevents abuse, ensuring a consistent experience for all users.</li>
							<li><strong>Considerations</strong>: If you anticipate requiring a higher rate limit due to specific use cases or higher traffic volumes, please contact our support team to discuss your requirements.</li>
						</ul>
					</p>
					<p>
						<strong>Modify The API Rate Limit:</strong>
					</p>
					<p>
						You can customize the rate limit as per your requirements by modifying the file
						<code class="higlighted break-word">/app/Filters/APIThrottle.php</code>

						<br>
						On line #39, change the values according to your needs:
						<code class="higlighted break-word">15, MINUTE</code>
						<table class="central-overflow-x">
							<thead>
							<tr>
								<th>Value</th>
								<th>Description</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td>15</td>
								<td>The number of requests allowed</td>
							</tr>
							<tr>
								<td>MINUTE</td>
								<td>The duration of the allowed number of request</td>
							</tr>
							</tbody>
						</table>
					</p>
					<p>
						<span style="color: red">NOTE</span>: Do not forget to clear/delete cache after the above changes.
						<code class="higlighted break-word">/writable/cache/FactoriesCache_config</code>
						<br>
						<code class="higlighted break-word">/writable/cache/FileLocatorCache</code>
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