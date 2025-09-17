<?php
/*
# Test with default event
php spark paypal:test-webhook

# Test specific event
php spark paypal:test-webhook BILLING.SUBSCRIPTION.CREATED
*/
namespace App\Modules\PayPal\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Modules\PayPal\Libraries\PayPalService;
use Exception;

class WebhookTest extends BaseCommand
{
    protected $group = 'PayPal';
    protected $name = 'paypal:test-webhook';
    protected $description = 'Test PayPal webhook with simulated events';
    protected $usage = 'paypal:test-webhook [event-type]';
    protected $arguments = [
        'event-type' => 'Type of webhook event to simulate (optional)'
    ];

    private $supportedEvents = [
        'BILLING.SUBSCRIPTION.CREATED',
        'BILLING.SUBSCRIPTION.ACTIVATED',
        'BILLING.SUBSCRIPTION.UPDATED',
        'BILLING.SUBSCRIPTION.CANCELLED',
        'BILLING.SUBSCRIPTION.SUSPENDED',
        'BILLING.SUBSCRIPTION.PAYMENT.FAILED',
        'PAYMENT.SALE.COMPLETED',
        'PAYMENT.SALE.REFUNDED'
    ];

    public function run(array $params)
    {
        helper('myconfig');

        try {
            // Validate PayPal configuration
            $paypalService = new PayPalService();
            $configStatus = $paypalService->getConfigurationStatus();
            
            if (!$configStatus->isConfigured) {
                CLI::error('PayPal is not fully configured. Please check your settings.');
                log_message('error', '[PayPal WebhookTest] PayPal configuration is incomplete');
                return;
            }

            // Select event type
            $eventType = array_shift($params) ?? CLI::prompt('Enter event type', $this->supportedEvents, 'BILLING.SUBSCRIPTION.CREATED');

            // Validate event type
            if (!in_array($eventType, $this->supportedEvents)) {
                throw new Exception("Invalid event type: {$eventType}. Supported events are: " . implode(', ', $this->supportedEvents));
            }

            // Get webhook URL
            $webhookUrl = rtrim(base_url(), '/') . '/paypal/webhook';

            // Create test webhook event data
            $eventData = $this->createTestEvent($eventType);

            // Generate webhook headers
            $headers = $this->generateWebhookHeaders($eventData);

            // Add development test header to bypass webhook authentication
            $headers['X-WEBHOOK-TEST'] = 'true';

            // Display test details
            $this->displayTestDetails($webhookUrl, $eventType, $headers, $eventData);

            // Send webhook test
            $response = makeApiCall(
                $webhookUrl,
                $headers,
                'POST',
                json_encode($eventData)
            );
            
            // Log and display response
            $this->handleResponse($response);

        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            CLI::error('Webhook test failed: ' . $errorMessage);
            log_message('error', '[PayPal WebhookTest] Test failed: ' . $errorMessage);
            log_message('error', '[PayPal WebhookTest] Error trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Create a test webhook event
     */
    protected function createTestEvent($eventType)
    {
        $resourceId = 'TEST_' . time();
        $timestamp = gmdate("Y-m-d\TH:i:s\Z");
        $customId = '1'; // Test package ID

        $baseEvent = [
            'id' => 'WH-' . uniqid(),
            'create_time' => $timestamp,
            'resource_type' => 'subscription',
            'event_type' => $eventType,
            'summary' => "Test {$eventType} event",
            'resource' => $this->getResourceData($eventType, $resourceId, $customId),
            'links' => [],
            'event_version' => '1.0',
            'resource_version' => '2.0'
        ];

        // Add additional context for logging
        log_message('debug', '[PayPal WebhookTest] Generated test event: ' . json_encode($baseEvent, JSON_PRETTY_PRINT));

        return $baseEvent;
    }

    /**
     * Generate resource data based on event type
     */
    protected function getResourceData($eventType, $resourceId, $customId)
    {
        $baseData = [
            'id' => $resourceId,
            'status' => 'ACTIVE',
            'create_time' => gmdate("Y-m-d\TH:i:s\Z"),
            'update_time' => gmdate("Y-m-d\TH:i:s\Z"),
            'custom_id' => $customId // Package ID
        ];

        switch ($eventType) {
            case 'BILLING.SUBSCRIPTION.CREATED':
            case 'BILLING.SUBSCRIPTION.ACTIVATED':
            case 'BILLING.SUBSCRIPTION.UPDATED':
            case 'BILLING.SUBSCRIPTION.CANCELLED':
            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                return array_merge($baseData, [
                    'plan_id' => 'P-TEST123',
                    'start_time' => gmdate("Y-m-d\TH:i:s\Z"),
                    'quantity' => '1',
                    'subscriber' => [
                        'email_address' => 'test@example.com',
                        'payer_id' => 'TESTPAYER123',
                        'name' => [
                            'given_name' => 'John',
                            'surname' => 'Doe'
                        ]
                    ],
                    'billing_info' => [
                        'outstanding_balance' => [
                            'currency_code' => 'USD',
                            'value' => '0.00'
                        ],
                        'cycle_executions' => [
                            [
                                'tenure_type' => 'REGULAR',
                                'sequence' => 1,
                                'cycles_completed' => 1,
                                'cycles_remaining' => 0,
                                'total_cycles' => 0
                            ]
                        ],
                        'last_payment' => [
                            'amount' => [
                                'currency_code' => 'USD',
                                'value' => '10.00'
                            ],
                            'time' => gmdate("Y-m-d\TH:i:s\Z")
                        ],
                        'next_billing_time' => gmdate("Y-m-d\TH:i:s\Z", strtotime('+1 month')),
                        'failed_payments_count' => 0
                    ],
                    'shipping_amount' => [
                        'currency_code' => 'USD',
                        'value' => '0.00'
                    ]
                ]);

            case 'PAYMENT.SALE.COMPLETED':
            case 'PAYMENT.SALE.REFUNDED':
                return array_merge($baseData, [
                    'amount' => [
                        'total' => '10.00',
                        'currency' => 'USD'
                    ],
                    'payment_mode' => 'INSTANT_TRANSFER',
                    'transaction_fee' => [
                        'value' => '0.59',
                        'currency' => 'USD'
                    ],
                    'billing_agreement_id' => $resourceId,
                    'payment_status' => $eventType === 'PAYMENT.SALE.REFUNDED' ? 'REFUNDED' : 'COMPLETED',
                    'create_time' => gmdate("Y-m-d\TH:i:s\Z"),
                    'update_time' => gmdate("Y-m-d\TH:i:s\Z")
                ]);

            default:
                return $baseData;
        }
    }

    /**
     * Generate webhook headers for testing
     */
    protected function generateWebhookHeaders($eventData)
    {
        $headers = [
            'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
            'PAYPAL-CERT-URL' => 'https://api-m.sandbox.paypal.com/v1/notifications/certs/CERT-360caa42-fca2a594-1d93a270',
            'PAYPAL-TRANSMISSION-ID' => 'TEST-' . uniqid(),
            'PAYPAL-TRANSMISSION-SIG' => 'test_signature',
            'PAYPAL-TRANSMISSION-TIME' => gmdate("Y-m-d\TH:i:s\Z"),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        log_message('debug', '[PayPal WebhookTest] Generated webhook headers: ' . json_encode($headers, JSON_PRETTY_PRINT));

        return $headers;
    }

    /**
     * Display test details in CLI
     */
    private function displayTestDetails($webhookUrl, $eventType, $headers, $eventData)
    {
        CLI::write('Sending webhook test to: ' . $webhookUrl, 'yellow');
        CLI::write('Event type: ' . $eventType, 'yellow');
        CLI::write('Headers: ' . json_encode($headers, JSON_PRETTY_PRINT), 'yellow');
        CLI::write('Payload: ' . json_encode($eventData, JSON_PRETTY_PRINT), 'yellow');
    }

    /**
     * Handle and display webhook test response
     */
    private function handleResponse($response)
    {
        // Log raw response for debugging
        log_message('debug', '[PayPal WebhookTest] Response status: ' . $response->getStatusCode());
        log_message('debug', '[PayPal WebhookTest] Response body: ' . $response->getBody());

        // Display response details
        CLI::write('Webhook test sent successfully', 'green');
        CLI::write('Response status: ' . $response->getStatusCode());
        
        // Only display response body if it's not empty
        $responseBody = $response->getBody();
        if (!empty($responseBody)) {
            CLI::write('Response body: ' . $responseBody);
        }
    }
}
