<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class EnvatoAPI
{
    protected $apiKey;
    // protected $baseUrl = 'https://api.envato.com/v3/';
    protected $baseUrl = 'https://sandbox.bailey.sh/v3/'; // for debug
    protected $client;
    protected $myConfig;
    protected $cacheTime = 3600; // 1 hour cache by default

    public function __construct()
    {
        $this->client = Services::curlrequest();
    }

    /**
     * Get author sales from Envato
     * 
     * @param int $page Page number for pagination
     * @param int $limit Number of sales to retrieve per page
     * @return array|null
     */
    public function getAuthorSales($userID, $page = 1, $limit = 20)
    {
        return $this->makeRequest($userID, 'market/author/sales', [
            'page' => $page,
            'limit' => $limit
        ]);
    }
    
    /**
     * Get user statement (earnings) from Envato
     * 
     * @param string $fromDate Start date in format YYYY-MM-DD
     * @param string $toDate End date in format YYYY-MM-DD
     * @param int $page Page number for pagination
     * @param int $limit Number of records to retrieve per page
     * @return array|null
     */
    public function getUserStatement($userID, $fromDate = null, $toDate = null, $page = 1, $limit = 20)
    {
        $params = [
            'page' => $page,
            'limit' => $limit
        ];
        
        if ($fromDate) {
            $params['from_date'] = $fromDate;
        }
        
        if ($toDate) {
            $params['to_date'] = $toDate;
        }
        
        return $this->makeRequest($userID, 'market/user/statement', $params);
    }

    /**
     * Get recent purchases (as a buyer)
     * 
     * @param int $page Page number for pagination
     * @return array|null
     */
    public function getRecentPurchases($userID, $page = 1)
    {
        return $this->makeRequest($userID, 'market/buyer/list-purchases', [
            'page' => $page,
            'include_all_item_details' => false
        ]);
    }

    /**
     * Verify a purchase code
     * 
     * @param string $code Purchase code to verify
     * @return array|null
     */
    public function verifyPurchase($userID, $code)
    {
        return $this->makeRequest($userID, "market/author/sale?code={$code}");
    }
    
    /**
     * Get details about the current authenticated user
     * 
     * @return array|null
     */
    public function getUserDetails($userID)
    {
        return $this->makeRequest($userID, 'market/private/user/account');
    }

    /**
     * Make a request to the Envato API with caching and retry logic
     * 
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @param int $retries Number of retry attempts for failed requests
     * @return array|null
     */
    protected function makeRequest($userID, $endpoint, $params = [], $retries = 2)
    {
        $this->myConfig = getMyConfig('', $userID);
        $this->apiKey = $this->myConfig['userEnvatoAPIKey'];

        $url = $this->baseUrl . $endpoint;
        
        // Add query parameters if any
        if (!empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }
        
        // Check cache first if caching is enabled
        if ($this->cacheTime > 0) {
            $cache = \Config\Services::cache();
            $cacheKey = 'envato_api_' . md5($url);
            $cachedResponse = $cache->get($cacheKey);
            
            if ($cachedResponse !== null) {
                return $cachedResponse;
            }
        }

        $attempt = 0;
        $maxAttempts = $retries + 1;
        
        while ($attempt < $maxAttempts) {
            try {
                $response = $this->client->request('GET', $url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'User-Agent' => $this->myConfig['userCompanyName'] . ' License Manager'
                    ],
                    'timeout' => 30, // Increase timeout to 30 seconds
                    'connect_timeout' => 10
                ]);

                if ($response->getStatusCode() === 200) {
                    $result = json_decode($response->getBody(), true);
                    
                    // Cache the successful response if caching is enabled
                    if ($this->cacheTime > 0) {
                        $cache = \Config\Services::cache();
                        $cache->save($cacheKey, $result, $this->cacheTime);
                    }
                    
                    return $result;
                }
                
                // Log the error with status code
                $statusCode = $response->getStatusCode();
                $errorBody = $response->getBody();
                log_message('error', "[EnvatoAPI] API request failed: {$statusCode} - {$errorBody}");
                
                // Only retry on server errors (5xx) or specific recoverable errors
                if ($statusCode >= 500 || in_array($statusCode, [429, 408])) {
                    $attempt++;
                    if ($attempt < $maxAttempts) {
                        // Exponential backoff: 1s, 2s, 4s, etc.
                        sleep(pow(2, $attempt - 1));
                        continue;
                    }
                }
                
                return null;
            } catch (\Exception $e) {
                log_message('error', '[EnvatoAPI] Exception: ' . $e->getMessage());
                
                $attempt++;
                if ($attempt < $maxAttempts) {
                    // Exponential backoff for exceptions too
                    sleep(pow(2, $attempt - 1));
                    continue;
                }
                
                return null;
            }
        }
        
        return null;
    }
}