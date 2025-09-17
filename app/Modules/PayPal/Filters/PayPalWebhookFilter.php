<?php

namespace App\Modules\PayPal\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Modules\PayPal\Libraries\PayPalService;
use Config\Services;

class PayPalWebhookFilter implements FilterInterface
{
    /**
     * @var array Webhook data storage
     */
    private static $webhookData = [];

    /**
     * Provide filter configuration for auto-loading
     */
    public static function getConfig(): array
    {
        return [
            'aliases' => [
                'paypalWebhook' => self::class
            ],
            'filters' => [
                'paypalWebhook' => [
                    'before' => ['paypal/webhook']
                ]
            ],
            'globals' => [
                'before' => [
                    'session' => [
                        'except' => [
                            'paypal/*'  // Routes that should bypass session auth
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Create JSON response
     */
    private function jsonResponse(int $statusCode, array $data): ResponseInterface
    {
        $response = Services::response();
        
        return $response
            ->setStatusCode($statusCode)
            ->setJSON($data)
            ->setHeader('Content-Type', 'application/json');
    }

    /**
     * Get webhook data for a request
     */
    public static function getWebhookData(RequestInterface $request): ?object
    {
        $key = spl_object_hash($request);
        return self::$webhookData[$key] ?? null;
    }

    /**
     * Set webhook data for a request
     */
    private static function setWebhookData(RequestInterface $request, object $data): void
    {
        $key = spl_object_hash($request);
        self::$webhookData[$key] = $data;
    }

    /**
     * Verify PayPal webhook request
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        log_message('debug', '[PayPal WebhookFilter] Starting webhook verification');

        try {
            // Check if it's a webhook request
            if ($request->getMethod() !== 'POST') {
                log_message('error', '[PayPal WebhookFilter] Invalid request method: ' . $request->getMethod());
                return $this->jsonResponse(405, [
                    'success' => false,
                    'message' => 'Method not allowed'
                ]);
            }

            // Verify Content-Type
            $contentType = $request->getHeaderLine('Content-Type');
            if (strpos($contentType, 'application/json') === false) {
                log_message('error', '[PayPal WebhookFilter] Invalid Content-Type: ' . $contentType);
                return $this->jsonResponse(400, [
                    'success' => false,
                    'message' => 'Invalid Content-Type. Expected application/json'
                ]);
            }

            // Get request body
            $body = $request->getBody();
            if (empty($body)) {
                log_message('error', '[PayPal WebhookFilter] Empty request body');
                return $this->jsonResponse(400, [
                    'success' => false,
                    'message' => 'Empty request body'
                ]);
            }

            // Parse JSON body
            $jsonBody = json_decode($body);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', '[PayPal WebhookFilter] Invalid JSON body: ' . json_last_error_msg());
                return $this->jsonResponse(400, [
                    'success' => false,
                    'message' => 'Invalid JSON body',
                    'error' => json_last_error_msg()
                ]);
            }

            // Verify event type
            if (empty($jsonBody->event_type)) {
                log_message('error', '[PayPal WebhookFilter] Missing event_type in payload');
                return $this->jsonResponse(400, [
                    'success' => false,
                    'message' => 'Missing event_type in payload'
                ]);
            }

            // Check for test request
            $isTestRequest = ENVIRONMENT === 'development' && $request->getHeaderLine('X-WEBHOOK-TEST') === 'true';

            if ($isTestRequest) {
                log_message('debug', '[PayPal WebhookFilter] Processing test webhook request');
                self::setWebhookData($request, (object)[
                    'headers' => $request->headers(),
                    'body' => $jsonBody,
                    'verified' => true,
                    'test' => true
                ]);
                return $request;
            }

            // For production, verify required headers
            $requiredHeaders = [
                'PAYPAL-AUTH-ALGO',
                'PAYPAL-CERT-URL',
                'PAYPAL-TRANSMISSION-ID',
                'PAYPAL-TRANSMISSION-SIG',
                'PAYPAL-TRANSMISSION-TIME'
            ];

            $headers = [];
            foreach ($requiredHeaders as $header) {
                $value = $request->header($header);
                if (!$value) {
                    log_message('error', '[PayPal WebhookFilter] Missing required header: ' . $header);
                    return $this->jsonResponse(400, [
                        'success' => false,
                        'message' => 'Missing required header: ' . $header
                    ]);
                }
                $headers[$header] = $value->getValue();
            }

            // Verify webhook signature
            $PayPalService = new PayPalService();
            $isValid = $PayPalService->verifyWebhookSignature($headers, $body);

            if (!$isValid) {
                log_message('error', '[PayPal WebhookFilter] Invalid webhook signature');
                return $this->jsonResponse(401, [
                    'success' => false,
                    'message' => 'Invalid webhook signature'
                ]);
            }

            // Store verified webhook data
            self::setWebhookData($request, (object)[
                'headers' => $headers,
                'body' => $jsonBody,
                'verified' => true,
                'test' => false
            ]);

            log_message('info', '[PayPal WebhookFilter] Webhook verification successful');
            return $request;

        } catch (\Exception $e) {
            log_message('error', '[PayPal WebhookFilter] Error verifying webhook: ' . $e->getMessage());
            log_message('error', '[PayPal WebhookFilter] Stack trace: ' . $e->getTraceAsString());

            return $this->jsonResponse(500, [
                'success' => false,
                'message' => 'Error verifying webhook',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Internal server error'
            ]);
        }
    }

    /**
     * Ensure JSON response for webhook endpoints
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ResponseInterface
    {
        // Set JSON content type if not already set
        if (!$response->hasHeader('Content-Type')) {
            $response->setHeader('Content-Type', 'application/json');
        }

        // If response is empty but status is 200, set a default success response
        if ($response->getBody() === '' && $response->getStatusCode() === 200) {
            $response->setJSON([
                'success' => true,
                'message' => 'Webhook processed successfully'
            ]);
        }

        return $response;
    }
}
