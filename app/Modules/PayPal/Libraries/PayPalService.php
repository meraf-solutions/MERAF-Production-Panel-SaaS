<?php

namespace App\Modules\PayPal\Libraries;

use Config\Services;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPaymentModel;
use App\Models\PackageModel;

class PayPalService
{
    protected $config;
    protected $SubscriptionModel;
    protected $PaymentModel;
    protected $client;
    protected $myConfig;
    protected $isConfigured;

    // Valid interval units for plans
    const VALID_INTERVAL_UNITS = ['DAY', 'WEEK', 'MONTH', 'YEAR'];

    public function __construct()
    {
        $this->config = config('PayPal');
        $this->SubscriptionModel = new SubscriptionModel();
        $this->PaymentModel = new SubscriptionPaymentModel();
        $this->myConfig = getMyConfig('', 0);
        $this->isConfigured = $this->config->checkConfiguration();
        $this->initializeClient();

        // Log configuration status
        if ($this->isConfigured) {
            log_message('debug', '[PayPal Service] Initialized with environment: ' . $this->config->environment);
            log_message('debug', '[PayPal Service] Base URL: ' . $this->config->baseUrl);
        } else {
            log_message('warning', '[PayPal Service] PayPal is not fully configured. Some features may be unavailable.');
        }
    }

    public function moduleDetails()
    {
        return $this->config->adminMenu;
    }    

    protected function initializeClient()
    {
        $this->client = Services::curlrequest();
    }

    protected function checkConfigured()
    {
        if (!$this->isConfigured) {
            log_message('warning', '[PayPal Service] Attempted to use PayPal service when not fully configured.');
            throw new \Exception('PayPal is not fully configured. Please set up your PayPal credentials before using this feature.');
        }
    }

    /**
     * Get PayPal access token
     */
    public function getAccessToken($clientId = null, $clientSecret = null)
    {
        $this->checkConfigured();

        try {
            $credentials = base64_encode(($clientId ?? $this->config->clientId) . ':' . ($clientSecret ?? $this->config->clientSecret));
            
            // Log the request details (excluding sensitive data)
            log_message('debug', '[PayPal Service] Requesting access token from: ' . $this->config->baseUrl . '/v1/oauth2/token');
            
            // Use application/x-www-form-urlencoded for token requests
            $response = $this->client->request('POST', $this->config->baseUrl . '/v1/oauth2/token', [
                'headers' => [
                    'Authorization' => 'Basic ' . $credentials,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            $responseBody = $response->getBody();
            
            // Log response (excluding sensitive data)
            log_message('debug', '[PayPal Service] Token response status: ' . $response->getStatusCode());

            $result = json_decode($responseBody);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            if (!isset($result->access_token)) {
                throw new \Exception('Invalid access token response: ' . $responseBody);
            }

            return $result->access_token;
        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Get access token failed: ' . $e->getMessage());
            throw $e;
        }
    }
	
	/**
     * Check if PayPal is fully configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }
	
	/**
     * Get PayPal configuration status
     *
     * @return object
     */
    public function getConfigurationStatus(): object
    {
        return (object) [
            'isConfigured' => $this->isConfigured,
            'environment' => $this->config->environment,
            'clientIdSet' => !empty($this->config->clientId),
            'clientSecretSet' => !empty($this->config->clientSecret),
            'webhookIdSet' => !empty($this->config->webhookId),
        ];
    }

    /**
     * Test PayPal API connection
     */
    public function testConnection($environment, $clientId, $clientSecret)
    {
        try {
            // Store the original config values
            $originalBaseUrl = $this->config->baseUrl;
            $originalClientId = $this->config->clientId;
            $originalClientSecret = $this->config->clientSecret;
            $originalIsConfigured = $this->isConfigured;

            // Temporarily set the config values for this test
            $this->config->baseUrl = $environment === 'sandbox' 
                ? 'https://api-m.sandbox.paypal.com' 
                : 'https://api-m.paypal.com';
            $this->config->clientId = $clientId;
            $this->config->clientSecret = $clientSecret;
            $this->isConfigured = true;

            // Attempt to get an access token
            $accessToken = $this->getAccessToken($clientId, $clientSecret);

            // If we get here, it means we successfully obtained an access token
            $result = [
                'success' => true,
                'message' => 'Successfully connected to PayPal API',
                'environment' => $environment,
                'access_token' => $accessToken
            ];

            // Restore the original config values
            $this->config->baseUrl = $originalBaseUrl;
            $this->config->clientId = $originalClientId;
            $this->config->clientSecret = $originalClientSecret;
            $this->isConfigured = $originalIsConfigured;

            return $result;
        } catch (\Exception $e) {
            // Restore the original config values in case of an exception
            $this->config->baseUrl = $originalBaseUrl ?? $this->config->baseUrl;
            $this->config->clientId = $originalClientId ?? $this->config->clientId;
            $this->config->clientSecret = $originalClientSecret ?? $this->config->clientSecret;
            $this->isConfigured = $originalIsConfigured;

            log_message('error', '[PayPal Service] Test connection failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to connect to PayPal API: ' . $e->getMessage(),
                'environment' => $environment
            ];
        }
    }

    /**
     * Verify webhook signature
     * 
     * @param array $headers Webhook request headers
     * @param string $body Webhook request body
     * @return bool True if signature is valid
     */
    public function verifyWebhookSignature(array $headers, string $body): bool
    {
        try {
            $accessToken = $this->getAccessToken();

            $verificationData = [
                'auth_algo' => $headers['PAYPAL-AUTH-ALGO'],
                'cert_url' => $headers['PAYPAL-CERT-URL'],
                'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'],
                'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'],
                'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'],
                'webhook_id' => $this->config->webhookId,
                'webhook_event' => json_decode($body)
            ];

            log_message('debug', '[PayPal Service] Verifying webhook signature with data: ' . json_encode($verificationData));

            $response = makeApiCall(
                $this->config->baseUrl . '/v1/notifications/verify-webhook-signature',
                [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'POST',
                $verificationData
            );

            $responseBody = $response->getBody();
            $statusCode = $response->getStatusCode();
            log_message('debug', '[PayPal Service] Webhook verification response status: ' . $statusCode);
            log_message('debug', '[PayPal Service] Webhook verification raw response: ' . $responseBody);

            if ($statusCode !== 200) {
                throw new \Exception('Webhook verification failed with status ' . $statusCode . ': ' . $responseBody);
            }

            $result = json_decode($responseBody);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            return $result->verification_status === 'SUCCESS';

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Webhook signature verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create PayPal product
     */
    protected function createProduct($packageData)
    {
        try {
            $accessToken = $this->getAccessToken();

            $productData = [
                'name' => $packageData['name'],
                'description' => $packageData['description'],
                'type' => 'SERVICE',
                'category' => 'SOFTWARE'
            ];

            log_message('debug', '[PayPal Service] Creating product with data: ' . json_encode($productData));

            $url = rtrim($this->config->baseUrl, '/') . '/v1/catalogs/products';
            log_message('debug', '[PayPal Service] Using product creation URL: ' . $url);

            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'PayPal-Request-Id' => uniqid('prod_'),
                    'Prefer' => 'return=representation'
                ],
                'json' => $productData,
                'http_errors' => false
            ]);

            $responseBody = $response->getBody();
            $statusCode = $response->getStatusCode();
            log_message('debug', '[PayPal Service] Product creation response status: ' . $statusCode);
            log_message('debug', '[PayPal Service] Product creation raw response: ' . $responseBody);

            if ($statusCode !== 201) {
                throw new \Exception('Product creation failed with status ' . $statusCode . ': ' . $responseBody);
            }

            $result = json_decode($responseBody);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            if (!isset($result->id)) {
                throw new \Exception('Invalid product creation response: ' . $responseBody);
            }

            log_message('info', '[PayPal Service] Product created successfully: ' . $result->id);
            return $result->id;

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Create product failed: ' . $e->getMessage());
            throw new \Exception('Failed to create PayPal product: ' . $e->getMessage());
        }
    }

    /**
     * Get PayPal product details
     */
    protected function getProduct($productId)
    {
        try {
            $accessToken = $this->getAccessToken();

            $url = rtrim($this->config->baseUrl, '/') . '/v1/catalogs/products/' . $productId;
            log_message('debug', '[PayPal Service] Getting product from: ' . $url);

            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'http_errors' => false
            ]);

            $responseBody = $response->getBody();
            $statusCode = $response->getStatusCode();
            log_message('debug', '[PayPal Service] Get product response status: ' . $statusCode);
            log_message('debug', '[PayPal Service] Get product raw response: ' . $responseBody);

            if ($statusCode === 404) {
                return null;
            }

            if ($statusCode !== 200) {
                throw new \Exception('Get product failed with status ' . $statusCode . ': ' . $responseBody);
            }

            $result = json_decode($responseBody);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            return $result;

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Get product failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create subscription plan
     */
    public function createPlan($packageData)
    {
        try {
            // Validate required fields
            $requiredFields = ['name', 'description', 'price', 'validity', 'validity_duration'];
            foreach ($requiredFields as $field) {
                if (!isset($packageData[$field]) || empty($packageData[$field])) {
                    throw new \Exception("Missing required field: {$field}");
                }
            }

            // Log incoming package data
            log_message('debug', '[PayPal Service] Creating plan with package data: ' . json_encode($packageData));

            $accessToken = $this->getAccessToken();

            // Check if product exists
            $product = $this->getProduct($packageData['product_id']);
            if (!$product) {
                log_message('debug', '[PayPal Service] Product not found, creating new product');
                $productId = $this->createProduct([
                    'name' => $packageData['name'],
                    'description' => $packageData['description']
                ]);
                $packageData['product_id'] = $productId;
            }

            // Validate interval unit
            $intervalUnit = strtoupper($packageData['validity_duration']);
            if (!in_array($intervalUnit, self::VALID_INTERVAL_UNITS)) {
                throw new \Exception('Invalid interval unit. Must be one of: ' . implode(', ', self::VALID_INTERVAL_UNITS));
            }

            // Validate and format price
            $price = (float)$packageData['price'];
            if ($price <= 0) {
                throw new \Exception('Price must be greater than 0');
            }

            // Validate interval count
            $intervalCount = (int)$packageData['validity'];
            if ($intervalCount <= 0) {
                throw new \Exception('Validity period must be greater than 0');
            }

            $planData = [
                'product_id' => $packageData['product_id'],
                'name' => $packageData['name'],
                'description' => $packageData['description'],
                'status' => 'ACTIVE',
                'billing_cycles' => [
                    [
                        'frequency' => [
                            'interval_unit' => $intervalUnit,
                            'interval_count' => $intervalCount
                        ],
                        'tenure_type' => 'REGULAR',
                        'sequence' => 1,
                        'total_cycles' => 0,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value' => number_format($price, 2, '.', ''),
                                'currency_code' => $this->myConfig['packageCurrency'] ?? 'USD'
                            ]
                        ]
                    ]
                ],
                'payment_preferences' => [
                    'auto_bill_outstanding' => true,
                    'setup_fee' => [
                        'value' => '0',
                        'currency_code' => $this->myConfig['packageCurrency'] ?? 'USD'
                    ],
                    'setup_fee_failure_action' => 'CONTINUE',
                    'payment_failure_threshold' => 3
                ]
            ];

            log_message('debug', '[PayPal Service] Creating plan with data: ' . json_encode($planData));

            // Use the correct API endpoint for plan creation
            $url = rtrim($this->config->baseUrl, '/') . '/v1/billing/plans';
            log_message('debug', '[PayPal Service] Using plan creation URL: ' . $url);

            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'PayPal-Request-Id' => uniqid('plan_'),
                    'Prefer' => 'return=representation'
                ],
                'json' => $planData,
                'http_errors' => false
            ]);

            $responseBody = $response->getBody();
            $statusCode = $response->getStatusCode();
            log_message('debug', '[PayPal Service] Plan creation response status: ' . $statusCode);
            log_message('debug', '[PayPal Service] Plan creation raw response: ' . $responseBody);

            if ($statusCode !== 201) {
                // Try to parse error details from response
                $errorDetails = json_decode($responseBody);
                $errorMessage = 'Plan creation failed';
                if ($errorDetails && isset($errorDetails->message)) {
                    $errorMessage .= ': ' . $errorDetails->message;
                }
                if ($errorDetails && isset($errorDetails->details)) {
                    $errorMessage .= ' - ' . json_encode($errorDetails->details);
                }
                throw new \Exception($errorMessage);
            }

            $result = json_decode($responseBody);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            if (!isset($result->id)) {
                throw new \Exception('Invalid plan creation response: ' . $responseBody);
            }

            log_message('info', '[PayPal Service] Plan created successfully: ' . $result->id);
            return $result;

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Create plan failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * New subscription details
     * 
     * @param int $packageId Package identifier
     * @return string|false Approval URL or false if creation fails
     */
    public function newSubscription($packageId)
    {
        try {
            // Validate package ID
            if (!$packageId) {
                log_message('error', '[PayPal Service] Invalid package ID');
                return false;
            }

            // Load package model to get package details
            $packageModel = new PackageModel();
            $package = $packageModel->find($packageId);

            if (!$package) {
                log_message('error', '[PayPal Service] Package not found: ' . $packageId);
                return false;
            }

            // Get current user
            $user = auth()->user();
            if (!$user) {
                log_message('error', '[PayPal Service] No authenticated user');
                return false;
            }

            // Get PayPal plan ID for the package
            $planId = $this->getPlanId($packageId, $package);

            // If no plan exists, create a new plan
            if (!$planId) {
                try {
                    $planResult = $this->createPlan([
                        'product_id' => 'PROD_' . $package['id'],
                        'name' => $package['package_name'],
                        'description' => $package['package_name'] . ' Subscription',
                        'price' => $package['price'],
                        'validity' => $package['validity'],
                        'validity_duration' => $package['validity_duration']
                    ]);
                    $planId = $planResult->id;
                } catch (\Exception $e) {
                    log_message('error', '[PayPal Service] Failed to create plan: ' . $e->getMessage());
                    return false;
                }
            }

            // Prepare subscription data
            $subscriptionData = [
                'plan_id' => $planId,
                'subscriber' => [
                    'name' => [
                        'given_name' => $user->first_name ?? '',
                        'surname' => $user->last_name ?? ''
                    ],
                    'email_address' => $user->email
                ],
                'application_context' => $this->config->getSubscriptionDefaults()['application_context'],
                'custom_id' => $packageId . ':' . $user->id // Store both package_id and user_id for webhook processing
            ];

            // Get access token
            $accessToken = $this->getAccessToken();

            // Create subscription in PayPal
            $response = makeApiCall(
                $this->config->baseUrl . '/v1/billing/subscriptions',
                [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'PayPal-Request-Id' => uniqid('sub_')
                ],
                'POST',
                $subscriptionData
            );

            $result = json_decode($response->getBody());

            // Find the approval URL
            $approvalUrl = '';
            foreach ($result->links as $link) {
                if ($link->rel === 'approve') {
                    $approvalUrl = $link->href;
                    break;
                }
            }

            // Validate approval URL
            if (!$approvalUrl) {
                log_message('error', '[PayPal Service] No approval URL found in PayPal response');
                return false;
            }

            log_message('info', '[PayPal Service] Subscription created successfully for package: ' . $packageId);
            return $approvalUrl;

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] New subscription failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get subscription details
     */
    public function getSubscription($subscriptionId)
    {
        try {
            $accessToken = $this->getAccessToken();

            $url = rtrim($this->config->baseUrl, '/') . '/v1/billing/subscriptions/' . $subscriptionId;
            log_message('debug', '[PayPal Service] Getting subscription from: ' . $url);

            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'http_errors' => false
            ]);

            $responseBody = $response->getBody();
            $statusCode = $response->getStatusCode();
            log_message('debug', '[PayPal Service] Get subscription response status: ' . $statusCode);
            log_message('debug', '[PayPal Service] Get subscription raw response: ' . $responseBody);

            if ($statusCode !== 200) {
                throw new \Exception('Get subscription failed with status ' . $statusCode . ': ' . $responseBody);
            }

            $result = json_decode($responseBody);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            return $result;

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Get subscription failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription($subscriptionId, $reason = 'Cancelled by user')
    {
        try {
            $accessToken = $this->getAccessToken();

            $url = rtrim($this->config->baseUrl, '/') . '/v1/billing/subscriptions/' . $subscriptionId . '/cancel';
            log_message('debug', '[PayPal Service] Cancelling subscription at: ' . $url);

            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'reason' => $reason
                ],
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            log_message('debug', '[PayPal Service] Cancel subscription response status: ' . $statusCode);

            if ($statusCode !== 204) {
                $responseBody = $response->getBody();
                throw new \Exception('Cancel subscription failed with status ' . $statusCode . ': ' . $responseBody);
            }

            return true;

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Cancel subscription failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Suspend subscription
     */
    public function suspendSubscription($subscriptionId, $reason = 'Suspended by system')
    {
        try {
            $accessToken = $this->getAccessToken();

            $url = rtrim($this->config->baseUrl, '/') . '/v1/billing/subscriptions/' . $subscriptionId . '/suspend';
            log_message('debug', '[PayPal Service] Suspending subscription at: ' . $url);

            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'reason' => $reason
                ],
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody();
            log_message('debug', '[PayPal Service] Suspend subscription response status: ' . $statusCode);
            log_message('debug', '[PayPal Service] Suspend subscription response body: ' . $responseBody);

            if ($statusCode !== 204) {
                $errorDetails = json_decode($responseBody);
                $errorMessage = 'Suspend subscription failed with status ' . $statusCode;
                if ($errorDetails && isset($errorDetails->message)) {
                    $errorMessage .= ': ' . $errorDetails->message;
                }
                if ($errorDetails && isset($errorDetails->details)) {
                    $errorMessage .= ' - ' . json_encode($errorDetails->details);
                }
                throw new \Exception($errorMessage);
            }

            return true;

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Suspend subscription failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Activate subscription
     */
    public function activateSubscription($subscriptionId, $reason = 'Activated by system')
    {
        try {
            $accessToken = $this->getAccessToken();

            $url = rtrim($this->config->baseUrl, '/') . '/v1/billing/subscriptions/' . $subscriptionId . '/activate';
            log_message('debug', '[PayPal Service] Activating subscription at: ' . $url);

            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'reason' => $reason
                ],
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            log_message('debug', '[PayPal Service] Activate subscription response status: ' . $statusCode);

            if ($statusCode !== 204) {
                $responseBody = $response->getBody();
                throw new \Exception('Activate subscription failed with status ' . $statusCode . ': ' . $responseBody);
            }

            return true;

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Activate subscription failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get transaction details
     */
    public function getTransactionDetails($transactionId)
    {
        try {
            $accessToken = $this->getAccessToken();

            $url = rtrim($this->config->baseUrl, '/') . '/v2/payments/captures/' . $transactionId;

            log_message('debug', '[PayPal Service] Getting transaction details from: ' . $url);

            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'http_errors' => false
            ]);

            $responseBody = $response->getBody();
            $statusCode = $response->getStatusCode();
            log_message('debug', '[PayPal Service] Get transaction response status: ' . $statusCode);
            log_message('debug', '[PayPal Service] Get transaction raw response: ' . $responseBody);

            if ($statusCode !== 200) {
                throw new \Exception('Get transaction failed with status ' . $statusCode . ': ' . $responseBody);
            }

            $result = json_decode($responseBody);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            return $result;

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Get transaction details failed: ' . $e->getMessage());
            throw $e;
        }
    }
	
	/**
     * Reactivate subscription
     */
    public function reactivateSubscription($subscriptionId, $reason = 'Reactivated by admin')
    {
        log_message('info', '[PayPal Service] Attempting to reactivate subscription: ' . $subscriptionId);

        try {
            // Get the current subscription status from PayPal
            $subscription = $this->getSubscription($subscriptionId);

            switch ($subscription->status) {
                case 'ACTIVE':
                    log_message('info', '[PayPal Service] Subscription is already active: ' . $subscriptionId);
                    return (object)[
                        'success' => true,
                        'message' => 'Subscription is already active',
                    ];

                case 'SUSPENDED':
                    // Reactivate suspended subscription
                    $activationResult = $this->activateSubscription($subscriptionId, $reason);
                    if (!$activationResult) {
                        throw new \Exception('Failed to activate suspended subscription in PayPal');
                    }
                    break;

                case 'CANCELLED':
                case 'EXPIRED':
                    // For cancelled or expired subscriptions, we need to create a new subscription
                    throw new \Exception('Subscription is cancelled or expired. A new subscription needs to be created.');

                default:
                    throw new \Exception('Unexpected subscription status: ' . $subscription->status);
            }

            // Get updated subscription details
            $updatedSubscription = $this->getSubscription($subscriptionId);

            log_message('info', '[PayPal Service] Successfully reactivated subscription: ' . $subscriptionId);

            return (object)[
                'success' => true,
                'message' => 'Subscription reactivated successfully',
                'subscription' => $updatedSubscription
            ];

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Error reactivating subscription: ' . $e->getMessage());
            return (object)[
                'success' => false,
                'message' => 'Failed to reactivate subscription: ' . $e->getMessage(),
            ];
        }
    }
	
	/**
     * Process a payment for a subscription
     */
    public function processPayment($subscriptionId)
    {
        log_message('info', '[PayPal Service] Attempting to process payment for subscription: ' . $subscriptionId);

        try {
            $accessToken = $this->getAccessToken();

            // Get subscription details
            $subscription = $this->getSubscription($subscriptionId);

            if ($subscription->status !== 'ACTIVE') {
                throw new \Exception('Cannot process payment for inactive subscription');
            }

            // Prepare the capture request
            $captureUrl = rtrim($this->config->baseUrl, '/') . '/v2/payments/authorizations/' . $subscription->last_failed_payment->authorization_id . '/capture';
            
            $captureData = [
                'amount' => [
                    'currency_code' => $subscription->billing_info->last_payment->amount->currency_code,
                    'value' => $subscription->billing_info->last_payment->amount->value
                ],
                'final_capture' => true
            ];

            log_message('debug', '[PayPal Service] Capturing payment with data: ' . json_encode($captureData));

            $response = $this->client->request('POST', $captureUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'PayPal-Request-Id' => uniqid('capture_')
                ],
                'json' => $captureData,
                'http_errors' => false
            ]);

            $responseBody = $response->getBody();
            $statusCode = $response->getStatusCode();
            log_message('debug', '[PayPal Service] Payment capture response status: ' . $statusCode);
            log_message('debug', '[PayPal Service] Payment capture raw response: ' . $responseBody);

            if ($statusCode !== 201) {
                throw new \Exception('Payment capture failed with status ' . $statusCode . ': ' . $responseBody);
            }

            $result = json_decode($responseBody);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            if ($result->status !== 'COMPLETED') {
                throw new \Exception('Payment capture was not completed. Status: ' . $result->status);
            }

            log_message('info', '[PayPal Service] Payment processed successfully for subscription: ' . $subscriptionId);

            return (object)[
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction_id' => $result->id
            ];

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Error processing payment: ' . $e->getMessage());
            return (object)[
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage(),
            ];
        }
    }
	
	/**
     * Get or create a subscription plan ID for a package
     * 
     * @param int $packageId Package identifier
     * @param array $packageDetails Package details for plan creation
     * @return string|null PayPal plan ID
     */
    public function getPlanId($packageId, $packageDetails = null)
    {
        try {
            // First, try to find an existing plan
            $existingPlans = $this->listPlans();
            
            // Search for a plan with a matching product ID
            foreach ($existingPlans->plans as $plan) {
                if ($plan->product_id === 'PROD_' . $packageId) {
                    return $plan->id;
                }
            }

            // If no existing plan is found and package details are provided, create a new plan
            if ($packageDetails) {
                $newPlan = $this->createPlan([
                    'product_id' => 'PROD_' . $packageId,
                    'name' => $packageDetails['package_name'],
                    'description' => $packageDetails['package_name'] . ' Subscription',
                    'price' => $packageDetails['price'],
                    'validity' => $packageDetails['validity'],
                    'validity_duration' => $packageDetails['validity_duration']
                ]);

                return $newPlan->id;
            }

            // If no plan found and no details provided to create a new one
            return null;

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] Error retrieving or creating plan: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * List existing PayPal plans
     * 
     * @return object Plans list
     */
    public function listPlans()
    {
        try {
            $accessToken = $this->getAccessToken();

            $url = rtrim($this->config->baseUrl, '/') . '/v1/billing/plans';
            log_message('debug', '[PayPal Service] Listing plans from: ' . $url);

            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'http_errors' => false
            ]);

            $responseBody = $response->getBody();
            $statusCode = $response->getStatusCode();
            log_message('debug', '[PayPal Service] List plans response status: ' . $statusCode);
            log_message('debug', '[PayPal Service] List plans raw response: ' . $responseBody);

            if ($statusCode !== 200) {
                $errorDetails = json_decode($responseBody);
                $errorMessage = 'List plans failed with status ' . $statusCode;
                
                if ($errorDetails) {
                    if (isset($errorDetails->message)) {
                        $errorMessage .= ': ' . $errorDetails->message;
                    }
                    if (isset($errorDetails->details)) {
                        $errorMessage .= ' - Details: ' . json_encode($errorDetails->details);
                    }
                }

                throw new \Exception($errorMessage);
            }

            $result = json_decode($responseBody);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            // If no plans are found, return an object with an empty plans array
            if (!isset($result->plans)) {
                return (object)['plans' => []];
            }

            return $result;

        } catch (\Exception $e) {
            log_message('error', '[PayPal Service] List plans failed: ' . $e->getMessage());
            
            // Rethrow the exception to maintain the original error handling
            throw $e;
        }
    }
}
