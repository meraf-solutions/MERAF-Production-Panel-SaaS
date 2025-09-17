<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\LicensesModel;
use App\Models\LicenseRegisteredDomainsModel;
use App\Models\LicenseRegisteredDevicesModel;
use App\Models\LicenseLogsModel;
use App\Models\LicenseEmailListModel;
use App\Models\UserSettingsModel;
use App\Models\UserModel;
use App\Models\PackageModel;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPaymentModel;
use App\Libraries\SubscriptionChecker;

class Api extends ResourceController
{
    use ResponseTrait;

    protected $userID;
    protected $userDataPath;
    protected $myConfig;
    protected $ValidationSecretKey;
    protected $CreationSecretKey;
    protected $ActivationSecretKey;
    protected $ManageSecretKey;
    protected $GeneralSecretKey;

    protected $PackageModel;
    protected $UserModel;
    protected $LicensesModel;
    protected $LicenseRegisteredDomainsModel;
    protected $LicenseRegisteredDevicesModel;
    protected $LicenseLogsModel;
    protected $LicenseEmailListModel;
    protected $UserSettingsModel;
    protected $SubscriptionModel;
    protected $PaymentModel;

    protected $subscriptionChecker;

    public function __construct()
    {
        // Initialize Models
        $this->PackageModel = new PackageModel();
        $this->UserModel = new UserModel();
        $this->LicensesModel = new LicensesModel();
        $this->LicenseRegisteredDomainsModel = new LicenseRegisteredDomainsModel();
        $this->LicenseRegisteredDevicesModel = new LicenseRegisteredDevicesModel();
        $this->LicenseLogsModel = new LicenseLogsModel();
        $this->LicenseEmailListModel = new LicenseEmailListModel();
        $this->UserSettingsModel = new UserSettingsModel();
        $this->PaymentModel = new SubscriptionPaymentModel();
        $this->SubscriptionModel = new SubscriptionModel();
        $this->subscriptionChecker = new SubscriptionChecker();

        // Get the current user's ID
        $this->userID = $this->getUserID();

        // Define the path to the user's data folder
        $this->userDataPath = $this->userID ? USER_DATA_PATH . $this->userID . '/' : NULL;

        // Get user-specific configurations
        $this->myConfig = $this->getUserConfig();

        $this->ValidationSecretKey = $this->userID ? $this->myConfig['License_Validate_SecretKey'] : NULL;
        $this->CreationSecretKey = $this->userID ? $this->myConfig['License_Create_SecretKey'] : NULL;
        $this->ActivationSecretKey = $this->userID ? $this->myConfig['License_DomainDevice_Registration_SecretKey'] : NULL;
        $this->ManageSecretKey = $this->userID ? $this->myConfig['Manage_License_SecretKey'] : NULL;
        $this->GeneralSecretKey = $this->userID ? $this->myConfig['General_Info_SecretKey'] : NULL;

        // Set the locale dynamically based on user preference
        setMyLocale();
    }

    protected function getUserID()
    {
        // Get the User-API-Key from the request header
        $request = \Config\Services::request();
        $apiKey = $request->getHeaderLine('User-API-Key');
    
        // Log the received API key for debugging
        // log_message('debug', '[API] Received User-API-Key: ' . $apiKey);
    
        // If the API key is not present, return null
        if (empty($apiKey)) {
            return null;
        }
    
        // Find the user with the corresponding API key
        $user = $this->UserModel->where('api_key', $apiKey)->first();
    
        // Log the fetched user ID with the given User-API-Key for debugging
        // log_message('debug', '[API] Fetched User ID: ' . $user->id);

        // If the user is found, return their ID, otherwise return null
        return $user ? $user->id : NULL;
    }
    
	protected function checkAdminAuthorization()
	{
		if($this->userID !== 1) {
            $response = [
                'result'     => 'error',
                'message'    => 'Unauthorized access',
                'error_code' => 500
            ];
    
            return $this->respondCreated($response);
		}
		return true;
	}    
    
    protected function getUserConfig()
    {
        return getMyConfig('', $this->userID);
    }    

    private function stripValue($value)
    {
        $pattern = '/::(index|show|create)$/i'; // Regex pattern to match dynamic suffixes
        return preg_replace($pattern, '', $value);
    }

    private function authorizeSecretKey($type, $secretKey)
    {
        if(!$this->userID) {
            $response = [
                'result'     => 'error',
                'message'    => 'Unauthorized access',
                'error_code' => FORBIDDEN_ERROR
            ];
    
            return $this->respondCreated($response);
        }
        
        $secretKey = $this->stripValue($secretKey);
    
        // Check if the set license manager is the built-in
        if ($this->myConfig['licenseManagerOnUse'] === 'slm') {
            $response = [
                'result'     => 'error',
                'message'    => 'Your request has failed. The configured license manager in the Production Panel is SLM WP Plugin. Please use the provided SLM WP Plugin API instead.',
                'error_code' => FORBIDDEN_ERROR
            ];
    
            return $this->respondCreated($response);
        }
    
        // Authorization according to the type of API request
        switch ($type) {
            case 'create':
                if ($secretKey === $this->CreationSecretKey) {
                    return true;
                }
                break;
        
            case 'validate':
                if ($secretKey === $this->ValidationSecretKey) {
                    return true;
                }
                break;
        
            case 'activation':
                if ($secretKey === $this->ActivationSecretKey) {
                    return true;
                }
                break;
        
            case 'manage':
                if ($secretKey === $this->ManageSecretKey) {
                    return true;
                }
                break;
        
            case 'general':
                if ($secretKey === $this->GeneralSecretKey) {
                    return true;
                }
                break;
        
            default:
                $response = [
                    'result'     => 'error',
                    'message'    => 'Invalid API key',
                    'error_code' => FORBIDDEN_ERROR
                ];
                return $this->respondCreated($response);
        }
    
        $response = [
            'result'     => 'error',
            'message'    => 'Invalid API key',
            'error_code' => FORBIDDEN_ERROR
        ];
    
        return $this->respondCreated($response);
    }

    /**
     * Action: all
     * URI: /api/license/all/{secret_key}
     * Method: GET
     * Description: Retrieve the list of all licenses with optional filtering for DataTables.
     * Requirements:
     * {secret_key} (required): A secret key for authorization.
     * Query Params:
     * - status (optional): Filter licenses by status
     * - type (optional): Filter licenses by type
     * - search (optional): Search term to filter licenses
     * - start (optional): Starting record for pagination
     * - length (optional): Number of records to return
     * @return mixed The response containing the list of licenses in DataTables format
     */
    public function listLicenses($secretKey)
    {
        $authResult = $this->authorizeSecretKey('manage', $secretKey);
        if ($authResult !== true) {
            return $authResult;
        }

        // Get DataTables parameters and ensure they are integers
        $draw = (int)($this->request->getGet('draw') ?? 1);
        $start = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 10);

        // Ensure length is not negative and has a reasonable maximum
        $length = max(1, min($length, 500));

        // Get query parameters
        $status = $this->request->getGet('status');
        $type = $this->request->getGet('type');
        
        // Improved search parameter handling
        $search = trim($this->request->getGet('search') ?? '');
        // Handle DataTables-style search parameter
        if (is_array($search) && isset($search['value'])) {
            $search = trim($search['value']);
        }

        try {
            // Start with base query
            $query = $this->LicensesModel->where('owner_id', $this->userID);

            // Apply status filter if provided
            if (!empty($status)) {
                $query->where('license_status', $status);
            }

            // Apply type filter if provided
            if (!empty($type)) {
                $query->where('license_type', $type);
            }

            // Apply search filter if provided
            if (!empty($search)) {
                $query->groupStart()
                    ->like('LOWER(license_key)', strtolower($search))
                    ->orLike('LOWER(first_name)', strtolower($search))
                    ->orLike('LOWER(last_name)', strtolower($search))
                    ->orLike('LOWER(license_status)', strtolower($search))
                    ->orLike('LOWER(license_type)', strtolower($search))
                    ->orLike('LOWER(item_reference)', strtolower($search))
                    ->orLike('LOWER(company_name)', strtolower($search))
                    ->orLike('LOWER(txn_id)', strtolower($search))
                    ->orLike('LOWER(purchase_id_)', strtolower($search))
                    ->orLike('LOWER(product_ref)', strtolower($search))
                    ->orLike('LOWER(subscr_id)', strtolower($search))
                    ->groupEnd();
            }

            // Count total records before filtering
            $totalRecords = $this->LicensesModel
                ->where('owner_id', $this->userID)
                ->countAllResults(false);

            // Count filtered records
            $filteredRecords = clone $query;
            $filteredRecordsCount = $filteredRecords->countAllResults(false);

            // Order and paginate results
            $query->orderBy('id', 'DESC')
                 ->limit($length, $start);

            // Fetch data
            $data = $query->findAll();

            // Prepare DataTables response
            $response = [
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecordsCount,
                'data' => $data
            ];

            return $this->respondCreated($response);

        } catch (\Exception $e) {
            // Log the full error
            log_message('error', '[API] License List Error: ' . $e->getMessage());
            
            return $this->respond([
                'result'     => 'error',
                'message'    => 'An error occurred: ' . $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * Export licenses to CSV
     * URI: /api/license/export/{secret_key}
     * Method: GET
     * Description: Export filtered licenses to CSV
     */
    public function exportLicensesCsv($secretKey)
    {
        $authResult = $this->authorizeSecretKey('manage', $secretKey);
        if ($authResult !== true) {
            return $authResult;
        }

        // Get query parameters
        $status = $this->request->getGet('status');
        $type = $this->request->getGet('type');
        
        // Improved search parameter handling
        $search = trim($this->request->getGet('search') ?? '');
        // Handle DataTables-style search parameter
        if (is_array($search) && isset($search['value'])) {
            $search = trim($search['value']);
        }

        try {
            // Start with base query
            $query = $this->LicensesModel->where('owner_id', $this->userID);

            // Apply status filter if provided
            if (!empty($status)) {
                $query->where('license_status', $status);
            }

            // Apply type filter if provided
            if (!empty($type)) {
                $query->where('license_type', $type);
            }

            // Apply search filter if provided
            if (!empty($search)) {
                $query->groupStart()
                    ->like('LOWER(license_key)', strtolower($search))
                    ->orLike('LOWER(first_name)', strtolower($search))
                    ->orLike('LOWER(last_name)', strtolower($search))
                    ->orLike('LOWER(license_status)', strtolower($search))
                    ->orLike('LOWER(license_type)', strtolower($search))
                    ->orLike('LOWER(item_reference)', strtolower($search))
                    ->orLike('LOWER(company_name)', strtolower($search))
                    ->orLike('LOWER(txn_id)', strtolower($search))
                    ->orLike('LOWER(purchase_id_)', strtolower($search))
                    ->orLike('LOWER(product_ref)', strtolower($search))
                    ->orLike('LOWER(subscr_id)', strtolower($search))
                    ->groupEnd();
            }

            // Order results
            $query->orderBy('id', 'DESC');

            // Fetch data
            $data = $query->findAll();

            if(empty($data)) {
                return $this->respond([
                    'result'     => 'error',
                    'message'    => 'No data to export',
                    'error_code' => RETURNED_EMPTY
                ]);
            }

            // Prepare CSV
            $csv = $this->generateCsv($data);

            // Generate filename
            $filename = 'licenses_export_' . date('YmdHis') . '.csv';

            // Send file to browser
            return $this->response
                ->setContentType('text/csv')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($csv);

        } catch (\Exception $e) {
            log_message('error', '[API] License Export Error: ' . $e->getMessage());
            
            return $this->respond([
                'result'     => 'error',
                'message'    => 'An error occurred during export: ' . $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * Generate CSV from license data
     * @param array $data License data
     * @return string CSV content
     */
    private function generateCsv($data)
    {
        // Define the columns to export
        $columns = [
            'id' => 'ID',
            'license_key' => 'License Key',
            'license_status' => 'Status',
            'license_type' => 'Type',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'product_ref' => 'Product',
            'date_created' => 'Created Date',
            'date_expiry' => 'Expiry Date'
        ];

        // Start CSV with headers
        $csv = implode(',', array_values($columns)) . "\n";

        // Add data rows
        foreach ($data as $row) {
            $csvRow = [];
            foreach (array_keys($columns) as $field) {
                // Escape special characters
                $value = isset($row[$field]) ? $row[$field] : '';
                $value = str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $value);
                $csvRow[] = '"' . $value . '"';
            }
            $csv .= implode(',', $csvRow) . "\n";
        }

        return $csv;
    }
    
    /**
     * This API is being used only by woocommerce  plugin for MERAF Production Panel
     **/
    public function retrieveNewLicenseSettings($secretKey)
    {
        $authResult = $this->authorizeSecretKey('general', $secretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }
    
        $settings = [
            'defaultAllowedDomains' => $this->myConfig['defaultAllowedDomains'],
            'defaultAllowedDevices' => $this->myConfig['defaultAllowedDevices'],
            'defaultTrialDays' => $this->myConfig['defaultTrialDays'],
            'default_license_status' => $this->myConfig['default_license_status'],
        ];

        return $this->respondCreated($settings);
    }
    
    /**
     * Action: verify
     * URI: /api/license/verify/{secret_key}/{license_key}
     * Method: GET
     * Description: Retrieve and check a specific license
     * Parameters:
     * $secretKey (required): A secret key for authorization.
     * @return mixed The response containing the license details or an error message.
    */
    public function checkLicense($secretKey, $licenseKey)
    {

        $authResult = $this->authorizeSecretKey('validate', $secretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }
        
        $licenseKey = $this->stripValue($licenseKey);

        $data = getLicenseData($licenseKey);

        if($data['result'] === 'success') {
            // Log the activity
            licenseManagerLogger($licenseKey, 'verify: Valid license key', 'yes');

            return $this->respondCreated($data);
        }
        else {
            // Log the activity
            licenseManagerLogger($licenseKey, 'verify: License key not found', 'no', $this->userID);

            // Add notification for validation error of license
            $notificationMessage = 'License not found error received';
            $notificationType = 'license_validation';
            $url = base_url('license-manager/activity-logs?s=' . $licenseKey);
            $recipientUserId = $this->userID;	
            add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

            $response = [
                'result'     => 'error',
                'message'    => 'License key not found',
                'error_code' => LICENSE_INVALID
            ];

            return $this->respondCreated($response);
        }
    }

    /**
     * Action: data
     * URI: /api/license/data/{secret_key}/{purchase_id}/{product_name}
     * Method: GET
     * Description: Retrieve the license data using purchase ID and product name as references.
     * Parameters:
     * - $secretKey (required): A secret key for authorization.
     * - $purchaseID (required): The purchase ID of the license.
     * - $productName (required): The name of the product.
     * @return mixed The response containing the license details or an error message.
     */
    public function retrieveLicense($secretKey, $purchaseID, $productName)
    {
        $authResult = $this->authorizeSecretKey('general', $secretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }

        $productName = $this->stripValue($productName);

        // Check if the queried product exists
        if (!in_array($productName, productList($this->userID))) {
            $response = [
                'result'     => 'error',
                'message'    => 'Product does not exist.',
                'error_code' => QUERY_NOT_FOUND
            ];

            return $this->respondCreated($response);
        } else {            
            $licenseDetails = $this->LicensesModel->where('owner_id', $this->userID)
                                                    ->where('purchase_id_', $purchaseID)
                                                    ->like('product_ref', $productName)
                                                    ->first();

            if ($licenseDetails) {
                return $this->respondCreated($licenseDetails);
            } else {
                $response = [
                    'result'     => 'error',
                    'message'    => 'License key not found.',
                    'error_code' => RETURNED_EMPTY
                ];

                return $this->respondCreated($response);
            }                                            
        }
    }   

    /**
     * Action: create new license
     * URI: /api/license/create/{secret_key}/data?{field_parameters}
     * Method: GET
     * Description: Create a new license
     * Parameters:
     * license_key (optional)       : If not provided, it will generate license key
     * license_status               : 'pending', 'active', 'blocked', 'expired'
     * license_type                 : Type of license. 'trial', 'subscription', 'lifetime
     * first_name                   : License user's first name
     * last_name                    : License user's last name
     * email                        : Client email address
     * subscr_id (optional)         : The Subscriber ID (if any). Can be useful if you are using the license key with a recurring payment plan.
     * company_name (optional)      : License user's company name (if any)
     * max_allowed_domains          : Number of domains/installs in which this license can be used
     * max_allowed_devices          : Number of devices/installs in which this license can be used
     * billing_length (required: if license_type = 'subscription')      : Amount in days or months or years
     * billing_interval (required: if license_type = 'subscription')    : Frequency period: in days, months, years or onetime   
     * date_expiry (required: if license_type = 'subscription') : The license license_status will automatically become expired if the date reached
     * product_ref                  : The product that this license gives access to
     * txn_id                       : The unique transaction ID associated with this license key
     * purchase_id_                 : This is associated with the purchase ID for third party payment app or platform. 
     * until (optional)             : Until what version this product is supported (if applicable)
     * current_ver (optional)       : What is the current version of this product
     * item_reference (optional)    : By the default, it will be the same as product_ref
     * manual_reset_count (optional): The number of times this license has been manually reset by the admin (use it if you want to keep track of it). It can be helpful for the admin to keep track of manual reset counts
     * @return mixed The response containing the created license details or an error message.
    */
    public function createLicense($secretKey, $data)
    {

        $authResult = $this->authorizeSecretKey('create', $secretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }
        
        $license_type = $this->request->getGet('license_type');

        $license_status = $this->request->getGet('license_status');

        if ($license_status) {
            $license_status = $license_status;
        } elseif ($this->myConfig['default_license_status']) {
            $license_status = $this->myConfig['default_license_status'];
        } else {
            $license_status = 'pending';
        }

        $data = [
            'owner_id'              => $this->userID,
            'license_key'           => $this->request->getGet('license_key') ?? generateLicenseKey($this->userID),
            'license_status'        => $license_status,
            'license_type'          => $license_type,
            'first_name'            => $this->request->getGet('first_name'),
            'last_name'             => $this->request->getGet('last_name'),
            'email'                 => $this->request->getGet('email'),
            'subscr_id'             => $this->request->getGet('subscr_id') ?? '',
            'company_name'          => $this->request->getGet('company_name') ?? '',
            'max_allowed_domains'   => $this->request->getGet('max_allowed_domains'),
            'max_allowed_devices'   => $this->request->getGet('max_allowed_devices'),
            'billing_length'        => $this->request->getGet('billing_length') ?? '',
            'billing_interval'      => $this->request->getGet('billing_interval') ?? 'days',
            'date_expiry'           => $license_type === 'trial' || $license_type === 'subscription' ? $this->request->getGet('date_expiry') : NULL,
            'product_ref'           => $this->request->getGet('product_ref'),
            'txn_id'                => $this->request->getGet('txn_id'),
            'purchase_id_'          => $this->request->getGet('purchase_id_'),
            'until'                 => $this->request->getGet('until') ?? '',
            'current_ver'           => $this->request->getGet('current_ver') ?? '',
            'item_reference'        => $this->request->getGet('item_reference') ?? $this->request->getGet('product_ref'),
            'manual_reset_count'    => $this->request->getGet('manual_reset_count') ?? '',
            'date_created'          => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
            'date_activated'        => $license_status === 'active' ? Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s') : NULL,
        ];

        // Individual validation
        $individualLicenseParamValidations = individualLicenseParamValidations($data, $this->userID);

        if($individualLicenseParamValidations !== true) {
            // returned error upon validation

            $errorDetails = json_decode($individualLicenseParamValidations, true);

            if($errorDetails['msg'] === lang('Notifications.error_not_in_product_list')) {
                $response = [
                    'result'     => 'error',
                    'message'    => 'License creation failed. Value of \'product_ref\' is not in the product list.',
                    'error_code' => CREATE_FAILED
                ];
            }
            else if($errorDetails['msg'] === lang('Notifications.error_no_billing_length')) {
                $response = [
                    'result'     => 'error',
                    'message'    => 'License creation failed. Specify license \'billing_length\'.',
                    'error_code' => CREATE_FAILED
                ];
            }
            else if($errorDetails['msg'] === lang('Notifications.error_no_billing_interval')) {
                $response = [
                    'result'     => 'error',
                    'message'    => 'License creation failed. Specify license \'billing_interval\'.',
                    'error_code' => CREATE_FAILED
                ];
            }
            else if($errorDetails['msg'] === lang('Notifications.exp_date_required_subscription_type')) {
                $response = [
                    'result'     => 'error',
                    'message'    => 'License creation failed. Specify license \'date_expiry\'.',
                    'error_code' => CREATE_FAILED
                ];
            }
            else if($errorDetails['msg'] === lang('Notifications.exp_date_required_trial_type')) {
                $response = [
                    'result'     => 'error',
                    'message'    => 'License creation failed. Specify license length \'date_expiry\'.',
                    'error_code' => CREATE_FAILED
                ];
            }
            else {
                $response = [
                    'result'     => 'error',
                    'message'    => 'License creation failed due to error on validation: ' . $errorDetails['msg'],
                    'error_code' => CREATE_FAILED
                ];
            }

            return $this->respondCreated($response);
        }

        // reformat the date_expiry if present
        if($data['license_type'] !== 'lifetime') {
            if ( ($data['date_expiry'] !== null) && ($data['date_expiry'] !== '')) {
                // App's default timezone
                $appTimezone = app_timezone();
    
                // Parse the date with the user's timezone
                $dateCreated = $data['date_created'];
                $dateCreated = Time::parse($dateCreated, $appTimezone);
    
                // Remove AM/PM and convert to 24-hour format
                $dateExpiry = $data['date_expiry'];
                $is_pm = stripos($dateExpiry, 'PM') !== false;
                $dateExpiry = trim(str_replace(['AM', 'PM'], '', $dateExpiry));
    
                // Parse the expiration date in the user's timezone
                $expirationDate = Time::parse($dateExpiry, $appTimezone);
    
                // Convert to app's default timezone
                $expirationDate = $expirationDate->setTimezone($appTimezone);
    
                // Convert to 24-hour format if needed
                if ($is_pm) {
                    $hour = $expirationDate->getHour();
                    if ($hour !== 12) {
                        $expirationDate = $expirationDate->setTime($hour + 12, $expirationDate->getMinute(), $expirationDate->getSecond());
                    }
                } else {
                    // Handle midnight (12 AM)
                    $hour = $expirationDate->getHour();
                    if ($hour === 12) {
                        $expirationDate = $expirationDate->setTime(0, $expirationDate->getMinute(), $expirationDate->getSecond());
                    }
                }
    
                // Check if the time is '00:00:00'
                if ($expirationDate->getHour() === 0 && $expirationDate->getMinute() === 0) {
                    // Set the time to the same as the creation date
                    $data['date_expiry'] = $expirationDate
                        ->setTime(
                            $dateCreated->getHour(), 
                            $dateCreated->getMinute(), 
                            $dateCreated->getSecond()
                        )
                        ->format('Y-m-d H:i:s');
                } else {
                    // Convert to standard database format
                    $data['date_expiry'] = $expirationDate->format('Y-m-d H:i:s');
                }
            }
            else {
                $response = [
                    'result'     => 'error',
                    'message'    => lang('Notifications.incorrectDateFormatEditLicense'),
                    'error_code' => CREATE_FAILED
                ];
                
                return $this->respondCreated($response);
            }
        }

        // Validation rules & messages
        $validationRules = [
            // 'license_key' => 'required',
            'max_allowed_domains' => 'required|numeric',
            'max_allowed_devices' => 'required|numeric',
            'license_status' => 'required|in_list[pending,active,blocked,expired]',
            'license_type' => 'required|in_list[trial,subscription,lifetime]',
            'first_name' => 'required|regex_match[/^[\p{L}\p{M}\s.-]+$/u]',
            'last_name' => 'required|regex_match[/^[\p{L}\p{M}\s.-]+$/u]',
            'email' => 'required|valid_email',
            'purchase_id_' => 'required|alpha_numeric_punct',
            'txn_id' => 'required|alpha_numeric_punct',
            'product_ref' => 'required|alpha_numeric_punct',
        ];

        $validationMessages = [
            // 'license_key' => [
            //     'required'              => 'The license key field is required.',
            // ],
            'max_allowed_domains' => [
                'required'              => 'The max allowed domains field is required.',
                'numeric'                   => 'Please enter a numeric value in the max allowed domains field.',
            ],
            'max_allowed_devices' => [
                'required'              => 'The max allowed devices field is required.',
                'numeric'                   => 'Please enter a numeric value in the max allowed devices field.',
            ],
            'license_status' => [
                'required'              => 'The license status field selection is required.',
            ],
            'license_type' => [
                'required'              => 'The license type field selection is required.',
            ],
            'first_name' => [
                'required'              => 'The first name field is required.',
                'regex_match'           => 'The first name field may only contain letters, spaces, periods, and hyphens.'
            ],
            'last_name' => [
                'required'              => 'The last name field is required.',
                'regex_match'           => 'The last name field may only contain letters, spaces, periods, and hyphens.'
            ],
            'email' => [
                'required'              => 'The email field is required.',
                'valid_email'           => 'Please provide a valid email address.',
            ],
            'purchase_id_' => [
                'required'              => 'The purchase ID field is required.',
                'alpha_numeric_punct'   => 'The purchase ID field should only contains alphanumeric characters.',
            ],
            'txn_id' => [
                'required'              => 'The transaction ID field is required.',
                'alpha_numeric_punct'   => 'The transaction ID field should only contains alphanumeric characters.',
            ],
            'product_ref' => [
                'required'              => 'The product reference field is required.',
                'alpha_numeric_punct'   => 'The product reference field should only contains alphanumeric characters.',
            ],
        ];      

        // Run validation
        if (!$this->validate($validationRules, $validationMessages)) {
            $errors = $this->validator->getErrors();
            $response = [
                'result'     => 'error',
                'message'    => $errors,
                'error_code' => CREATE_FAILED
            ];

            return $this->respondCreated($response);
        } else {
            try {
                $return = $this->LicensesModel->where('owner_id', $this->userID)->insert($data);

                if ($return) {
                    // Get the created license key
                    $createdLicense = $this->LicensesModel->where('owner_id', $this->userID)
                                                            ->where('id', $return)
                                                            ->first();
            
                    // Log the activity
                    licenseManagerLogger($createdLicense['license_key'], 'create: License creation initiated', 'yes');
            
                    // Initiate sending email if enabled
                    if ($this->myConfig['sendEmailNewLicense']) {
                        $clientFullName = $createdLicense['first_name'] . ' ' . $createdLicense['last_name'];
                        $bccAdmin = $this->myConfig['sendBCCtoLicenseClientNotifications'] ? true : false;

                        $emailService = new \App\Libraries\EmailService();
		
                        try {
                            $licenseNotificationResult = $emailService->sendLicenseDetails([
                                'license_key' => $createdLicense['license_key'],
                                'recipient_email' => $createdLicense['email'],
                                'email_format' => 'html',
                                'with_bcc' => $bccAdmin
                            ]);
                        } catch (\Throwable $e) {
                            // Handle exceptions
                            log_message(
                                'error',
                                '[License Manager] Error sending license notification: ' . $e->getMessage()
                            );
                        }

                        $emailListData = [
                            'owner_id'    => $createdLicense['email'],
                            'license_key' => $createdLicense['license_key'],
                            'sent_to'     => $createdLicense['email'],
                            'date_sent'   => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                        ];

                        if ($licenseNotificationResult['success']) {
                            $emailListData['status'] = 'success';
                            $emailListData['sent'] = 'yes';
                        }                           
                        else {
                            $emailListData['status'] = 'error';
                            $emailListData['sent'] = 'no';
                        } 

                        // Check if the email already exists in the database
                        $existingEmailAddress = $this->LicenseEmailListModel
                            ->where('sent_to', $createdLicense['email'])
                            ->where('owner_id', $createdLicense['owner_id'])
                            ->first();
                    
                        if (!$existingEmailAddress) {
                            try {
                                // Insert email sending status into the database
                                $this->LicenseEmailListModel->insertOrUpdate($emailListData);
                            } catch (\Throwable $e) {
                                // Handle exceptions
                                log_message(
                                    'error',
                                    '[API] Error occurred while inserting new email list data: ' . $e->getMessage() .
                                    '. Data: ' . json_encode($emailListData)
                                );
                            }
                        }
                    }

                    // Add notification if new license was from woocommerce order
                    if($createdLicense['item_reference'] === 'woocommerce') {
                        // Add notification for new license from Envato purchase to User
                        $notificationMessage = 'License created from a WooCommerce order.';
                        $notificationType = 'license_created';
                        $url = base_url('license-manager/list-all?s=' . $createdLicense['license_key']);
                        $recipientUserId = $createdLicense['owner_id'];
                        add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
                    }
            
                    // Prepare success response
                    $response = [
                        'result' => 'success',
                        'message' => 'License successfully created. ' . ($licenseNotificationResult['message'] ?? ''),
                        'key' => $createdLicense['license_key'],
                        'code' => LICENSE_CREATED
                    ];
                } else {
                    // Prepare error response for failed insertion
                    $response = [
                        'result' => 'error',
                        'message' => 'License creation failed.',
                        'error_code' => CREATE_FAILED
                    ];
                }
            }
            catch (\Throwable $e) {
                // Handle exceptions
                $response = [
                    'result' => 'error',
                    'message' => 'License creation failed: ' . $e->getMessage(),
                    'error_code' => CREATE_FAILED
                ];
            }
            
            return $this->respondCreated($response);            
        }
    }

    /**
     * Action:  unregister domain name or device name
     * URI: /api/license/unregister/{type}/{name}/{secret_key}/{license_key}
     * Method: GET
     * Description: Unregister the domain or device name from a license
     * Parameters:
     * {type}     : domain or device
     * {name}     : domain name or device unique identification
     * {secret_key} (required): A secret key for authorization.
     * @return mixed The response containing the status of deactivation of domain/device
    */
    public function unregisterDomainAndDevice($type, $name, $secretKey, $licenseKey)
    {
        $authResult = $this->authorizeSecretKey('activation', $secretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }
        $licenseKey = $this->stripValue($licenseKey);

        // Validate the query type
        if($type === 'domain') {
            $model = $this->LicenseRegisteredDomainsModel;
            $queryType = 'registered_domains';
            $columnName = 'domain_name';
        }
        else if($type === 'device') {
            $model = $this->LicenseRegisteredDevicesModel;
            $queryType = 'registered_devices';
            $columnName = 'device_name';
        }
        else {
            // Log the activity
            licenseManagerLogger($licenseKey, 'registration: Received registration request type is not existing', 'no');

            $response = [
                'result'     => 'error',
                'message'    => 'Received request is not existing. Please check query type and try again',
                'error_code' => QUERY_NOT_FOUND
            ];

            return $this->respondCreated($response);
        }

        $data = getLicenseData($licenseKey); // Get the license details

        if($data['result'] === 'success') {
            // Validate if license status is active
            if($data['status'] !== 'active') {
                // Log the activity
                licenseManagerLogger($licenseKey, 'registration: Unable to process ' . $type . ' deactivation as the license status is not active', 'no');

                $response = [
                    'result'     => 'error',
                    'message'    => 'Unable to process request as the license status is not active',
                    'error_code' => LICENSE_INVALID
                ];
    
                return $this->respondCreated($response);
            }
            
            // Validate if name exists in the registered domain or device            
            if (searchArrayRecursive($data[$queryType], $name)) {
                $registeredDomainAndDevice = $model->where('owner_id', $this->userID)->where($columnName, $name)->first();

                if($registeredDomainAndDevice){
                    $model->where('owner_id', $this->userID)->delete($registeredDomainAndDevice['id'], true);

                    // Initiate email notification to customer
                    if($this->myConfig['sendUnregisteredDomainDeviceRegistration']) {
                        $bccAdmin = $this->myConfig['sendBCCtoLicenseClientNotifications'] ? true : false;

                        $emailService = new \App\Libraries\EmailService();
                        $licenseNotificationResult = $emailService->sendLicenseNotification([
                            'license_key' => $data['license_key'],
                        	'recipient_email' => $data['email'],
                        	'date_activity' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                            'template' => 'license_deactivation_notification',
                        	'email_format' => 'html',
                            'with_bcc' => $bccAdmin
                        ]);
                    }

                    // Log the activity
                    licenseManagerLogger($licenseKey, 'registration: '.ucwords($type).' (' . $name . ') deactivation request successful', 'yes');

                    $response = [
                        'result'     => 'success',
                        'message'    => 'The license key has been deactivated for this ' . $type .' (' . $name . ')',
                        'error_code' => KEY_DEACTIVATE_SUCCESS
                    ];  
                    
                    return $this->respondDeleted($response);
                }else{
                    // Log the activity
                    licenseManagerLogger($licenseKey, 'registration: Encountered a problem in processing the deactivation of ' . $type .' (' . $name . ')', 'no');

                    $response = [
                        'result'     => 'error',
                        'message'    => 'Encountered a problem in processing the request',
                        'error_code' => QUERY_ERROR
                    ];

                    return $this->respondCreated($response);
                }       

            } else {
                // Log the activity
                licenseManagerLogger($licenseKey, 'registration: The ' . $type . ' (' . $name . ') is not registered in the provided license key', 'no');

                $response = [
                    'result'     => 'error',
                    'message'    => 'The query ' . $type . '(' . $name . ') is not registered in the provided license key',
                    'error_code' => QUERY_DOMAINorDEVICE_NOT_EXISTING
                ];
    
                return $this->respondCreated($response);
            } 
        }
        else {
            // Log the acvitivity
            licenseManagerLogger($licenseKey, 'verify: License key not found', 'no', $this->userID);

            // Add notification for validation error of license
            $notificationMessage = 'License not found error received';
            $notificationType = 'license_validation';
            $url = base_url('license-manager/activity-logs?s=' . $licenseKey);
            $recipientUserId = $this->userID;	
            add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

            $response = [
                'result'     => 'error',
                'message'    => 'License key not found',
                'error_code' => LICENSE_INVALID
            ];

            return $this->respondCreated($response);
        }
    }

    /**
     * Action:  register domain name or device name
     * URI: /api/license/register/{type}/{name}/{secret_key}/{license_key}
     * Method: GET
     * Description: Register the domain or device name in license key
     * Parameters:
     * {type}     : domain or device
     * {name}     : domain name or device unique identification
     * {secret_key} (required): A secret key for authorization.
     * @return mixed The response containing the status of domain/device registration to the license
    */
    public function registerDomainAndDevice($type, $name, $SecretKey, $licenseKey)
    {
        $authResult = $this->authorizeSecretKey('activation', $SecretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }
        $licenseKey = $this->stripValue($licenseKey);

        // Validate the query type
        if($type === 'domain') {
            $model = $this->LicenseRegisteredDomainsModel;
            $allowedCount = 'max_allowed_domains';
            $queryType = 'registered_domains';
            $columnName = 'domain_name';
            $errorCode = REACHED_MAX_DOMAINS;
        }
        else if($type === 'device') {
            $model = $this->LicenseRegisteredDevicesModel;
            $allowedCount = 'max_allowed_devices';
            $queryType = 'registered_devices';
            $columnName = 'device_name';
            $errorCode = REACHED_MAX_DEVICES;
        }
        else {
            // Log the activity
            licenseManagerLogger($licenseKey, 'registration: Received registration request type is not existing', 'no');

            $response = [
                'result'     => 'error',
                'message'    => 'Received request is not existing. Please check query type and try again',
                'error_code' => QUERY_NOT_FOUND
            ];

            return $this->respondCreated($response);
        }
        
        $licenseData = getLicenseData($licenseKey); // Get the license details

        if(($licenseData['result'] === 'error') && ($licenseData['error_code'] === 60) ) {
            // Not a valid license key or Envato purchase code
            // Log the activity
            licenseManagerLogger($licenseKey, 'verify: License key not found', 'no', $this->userID);

            // Add notification for validation error of license
            $notificationMessage = 'License not found error received';
            $notificationType = 'license_validation';
            $url = base_url('license-manager/activity-logs?s=' . $licenseKey);
            $recipientUserId = $this->userID;	
            add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

            $response = [
                'result'     => 'error',
                'message'    => 'License key not found.',
                'error_code' => LICENSE_INVALID
            ];

            return $this->respondCreated($response);
        }

        $appTimezone = app_timezone(); // Set the default timezone

        /***
         * START: Envato Purchase Verification
         * Reference: https://forums.envato.com/t/how-to-verify-a-purchase-code-using-the-envato-api/150813
         */

        // Check if user has enabled feature
        $isEnvatoSyncEnabled = $this->subscriptionChecker->isFeatureEnabled($this->userID, 'Envato_Sync');
        
        // If license doesn't exist, check if it's an Envato purchase code
        if( $isEnvatoSyncEnabled && ($licenseData['result'] === 'error') ) {
            // Check if it matches Envato purchase code format
            if (preg_match('/^([a-f0-9]{8})-(([a-f0-9]{4})-){3}([a-f0-9]{12})$/i', $licenseKey)) {
                // Check if Envato sync is enabled
                if (!isset($this->myConfig['userEnvatoSyncEnabled']) || $this->myConfig['userEnvatoSyncEnabled'] !== 'on') {
                    log_message('debug', '[API Controller] Envato synchronization is disabled in settings.');

                    $response = [
                        'result'     => 'error',
                        'message'    => 'License key not found.',
                        'error_code' => LICENSE_INVALID
                    ];
                    return $this->respondCreated($response);
                }
                
                // Check if API key is set
                if (empty($this->myConfig['userEnvatoAPIKey'])) {
                    log_message('debug', '[API Controller] Envato API key is not configured.');

                    $response = [
                            'result'     => 'error',
                            'message'    => 'License key not found.',
                            'error_code' => LICENSE_INVALID
                        ];
                    return $this->respondCreated($response);
                }
                
                // Initialize Envato API
                $envatoAPI = new \App\Libraries\EnvatoAPI();
                
                // Verify purchase code
                $purchaseData = $envatoAPI->verifyPurchase($this->userID, $licenseKey);

                log_message('debug', '[API Controller] Envato API Sale Result: ' . json_encode($purchaseData, JSON_PRETTY_PRINT));
                
                if ($purchaseData && isset($purchaseData['item']['id'])) {
                    // Get the item ID from the purchase data
                    $itemId = $purchaseData['item']['id'];
                    $itemFound = false;
                    $productName = '';
                    
                    // Get all products and check if any match the Envato item ID
                    $products = productDetails('', $this->userID);
                    
                    foreach ($products as $product => $details) {
                        if (isset($details['envato_item_code']) && $details['envato_item_code'] == $itemId) {
                            $itemFound = true;
                            $productName = $product;
                            break;
                        }
                    }
                    
                    if (!$itemFound) {
                        log_message('debug', '[API Controller] The purchase code is valid but not for any of our products.');

                        $response = [
                            'result'     => 'error',
                            'message'    => 'License key not found.',
                            'error_code' => LICENSE_INVALID
                        ];
                        return $this->respondCreated($response);
                    }
                    
                    // Check if purchase already exists in our database
                    $envatoPurchasesModel = new \App\Models\EnvatoPurchasesModel();
                    $existingPurchase = $envatoPurchasesModel->where('owner_id', $this->userID)
                                                            ->where('purchase_code', $licenseKey)
                                                            ->first();
                    
                    // Handle different scenarios based on purchase record existence and status
                    if (!$existingPurchase) {
                        // No purchase record exists yet - create new license and purchase record
                        log_message('info', '[API Controller] Valid purchase code not yet in database. Creating new license for: ' . $licenseKey);

                        // Create license data array
                        $licenseData = [
                            'owner_id'              => $this->userID,
                            'license_key'           => $licenseKey,
                            'license_status'        => $this->myConfig['default_license_status'] ?? 'active',
                            'license_type'          => 'lifetime',
                            'first_name'            => 'Envato',
                            'last_name'             => 'Customer',
                            'email'                 => $this->myConfig['adminEmail'],
                            'subscr_id'             => $licenseKey,
                            'company_name'          => '',
                            'max_allowed_domains'   => (int)($type === 'domain' ? ($this->myConfig['defaultAllowedDomains'] ?? 1) : 1),
                            'max_allowed_devices'   => (int)($type === 'device' ? ($this->myConfig['defaultAllowedDevices'] ?? 1) : 1),
                            'billing_length'        => '',
                            'billing_interval'      => 'onetime',
                            'date_expiry'           => NULL,
                            'product_ref'           => $productName,
                            'txn_id'                => $licenseKey,
                            'purchase_id_'          => $licenseKey,
                            'until'                 => isset($purchaseData['supported_until']) ? Time::parse($purchaseData['supported_until'], $appTimezone)->format('Y-m-d H:i:s') : '',
                            'current_ver'           => $products[$productName]['version'] ?? '',
                            'item_reference'        => 'envato',
                            'manual_reset_count'    => '',
                            'date_created'          => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                            'date_activated'        => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                        ];

                        try {
                            log_message('debug', '[API Controller] License data to be inserted: ' . json_encode($licenseData, JSON_PRETTY_PRINT));

                            $return = $this->LicensesModel->insert($licenseData);
                        
                            if ($return) {
                                // Log the activity
                                licenseManagerLogger($licenseKey, 'create: License created from existing Envato purchase record ' . $licenseKey, 'yes');

                                // Add notification for new license from Envato purchase to User
                                $notificationMessage = 'License created from existing Envato purchase record.';
                                $notificationType = 'license_created';
                                $url = base_url('license-manager/list-all?s=' . $licenseKey);
                                $recipientUserId = $this->userID;
                                add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

                                // Record in envato_purchases table
                                $purchaseRecord = [
                                    'owner_id' => $this->userID,
                                    'purchase_code' => $licenseKey,
                                    'item_id' => $itemId,
                                    'item_name' => $purchaseData['item']['name'],
                                    'buyer_username' => $purchaseData['buyer'] ?? null,
                                    'buyer_email' => null,
                                    'purchase_date' => isset($purchaseData['sold_at']) ? Time::parse($purchaseData['sold_at'], $appTimezone) : Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                                    'license_type' => $purchaseData['license'] ?? 'Regular License',
                                    'support_until' => isset($purchaseData['supported_until']) ? Time::parse($purchaseData['supported_until'], $appTimezone)->format('Y-m-d H:i:s') : null,
                                    'processed' => 1,
                                    'license_created' => 1
                                ];

                                $envatoPurchasesModel->insert($purchaseRecord);

                                // Log the activity
                                licenseManagerLogger($licenseKey, 'create: License created from Envato purchase ' . $licenseKey, 'yes');
                        
                                // Call this method again to register the domain/device with the newly created license
                                return $this->registerDomainAndDevice($type, $name, $SecretKey, $licenseKey);
                            } else {
                                log_message('error', '[API Controller] Encountered error in attempting to save new license (1).');
                                
                                // Prepare error response for failed insertion
                                $response = [
                                    'result'     => 'error',
                                    'message'    => 'License key not found.',
                                    'error_code' => LICENSE_INVALID
                                ];
                                return $this->respondCreated($response);
                            }
                        } catch (\Throwable $e) {
                            log_message('error', '[API Controller] Encountered error in attempting to save new license: ' . $e->getMessage());
                            
                            // Handle exceptions
                            $response = [
                                    'result'     => 'error',
                                    'message'    => 'License key not found.',
                                    'error_code' => LICENSE_INVALID
                                ];
                            return $this->respondCreated($response);
                        }
                    } 
                    else if ($existingPurchase && $existingPurchase['license_created']) {
                        // Purchase record exists and license was supposedly created
                        $associatedLicense = $this->LicensesModel->where('owner_id', $this->userID)
                                                                ->where('license_key', $licenseKey)
                                                                ->first();
                        
                        if ($associatedLicense) {
                            // Continue with normal flow below - the license key is already the purchase code
                        } else {
                            // Purchase record exists but license is missing - create new license
                            log_message('info', '[API Controller] Purchase code exists but license not found. Creating new license for: ' . $licenseKey);

                            // Create a new license using the purchase code as the license key
                            $licenseData = [
                                'owner_id'              => $this->userID,
                                'license_key'           => $licenseKey,
                                'license_status'        => $this->myConfig['default_license_status'] ?? 'active',
                                'license_type'          => 'lifetime',
                                'first_name'            => 'Envato',
                                'last_name'             => 'Customer',
                                'email'                 => $this->myConfig['adminEmail'],
                                'subscr_id'             => $licenseKey,
                                'company_name'          => '',
                                'max_allowed_domains'   => (int)($type === 'domain' ? ($this->myConfig['defaultAllowedDomains'] ?? 1) : 1),
                                'max_allowed_devices'   => (int)($type === 'device' ? ($this->myConfig['defaultAllowedDevices'] ?? 1) : 1),
                                'billing_length'        => '',
                                'billing_interval'      => 'onetime',
                                'date_expiry'           => NULL,
                                'product_ref'           => $productName,
                                'txn_id'                => $licenseKey,
                                'purchase_id_'          => $licenseKey,
                                'until'                 => isset($purchaseData['supported_until']) ? Time::parse($purchaseData['supported_until'], $appTimezone)->format('Y-m-d H:i:s') : '',
                                'current_ver'           => $products[$productName]['version'] ?? '',
                                'item_reference'        => 'envato',
                                'manual_reset_count'    => '',
                                'date_created'          => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                                'date_activated'        => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                            ];

                            try {
                                $return = $this->LicensesModel->insert($licenseData);
                            
                                if ($return) {
                                    // Log the activity
                                    licenseManagerLogger($licenseKey, 'create: License created from existing Envato purchase record ' . $licenseKey, 'yes');

                                    // Add notification for new license from Envato purchase to User
                                    $notificationMessage = 'License created from existing Envato purchase record.';
                                    $notificationType = 'license_created';
                                    $url = base_url('license-manager/list-all?s=' . $licenseKey);
                                    $recipientUserId = $this->userID;
                                    add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
                            
                                    // Call this method again to register the domain/device with the newly created license
                                    return $this->registerDomainAndDevice($type, $name, $SecretKey, $licenseKey);
                                } else {
                                    log_message('error', '[API Controller] Encountered error in attempting to save new license (2).');
                                    
                                    // Prepare error response for failed insertion
                                    $response = [
                                        'result'     => 'error',
                                        'message'    => 'License key not found.',
                                        'error_code' => LICENSE_INVALID
                                    ];
                                    return $this->respondCreated($response);
                                }
                            } catch (\Throwable $e) {
                                log_message('error', '[API Controller] Encountered error in attempting to save new license: ' . $e->getMessage());
                                
                                // Handle exceptions
                                $response = [
                                        'result'     => 'error',
                                        'message'    => 'License key not found.',
                                        'error_code' => LICENSE_INVALID
                                    ];
                                return $this->respondCreated($response);
                            }
                        }
                    }
                    else {
                        // Purchase record exists but license was not created
                        log_message('info', '[API Controller] Purchase record exists but license not created. Creating license for: ' . $licenseKey);
                        
                        // Create a new license using the purchase code as the license key
                        $licenseData = [
                            'owner_id'              => $this->userID,
                            'license_key'           => $licenseKey,
                            'license_status'        => $this->myConfig['default_license_status'] ?? 'active',
                            'license_type'          => 'lifetime',
                            'first_name'            => 'Envato',
                            'last_name'             => 'Customer',
                            'email'                 => $this->myConfig['adminEmail'],
                            'subscr_id'             => $licenseKey,
                            'company_name'          => '',
                            'max_allowed_domains'   => (int)($type === 'domain' ? ($this->myConfig['defaultAllowedDomains'] ?? 1) : 1),
                            'max_allowed_devices'   => (int)($type === 'device' ? ($this->myConfig['defaultAllowedDevices'] ?? 1) : 1),
                            'billing_length'        => '',
                            'billing_interval'      => 'onetime',
                            'date_expiry'           => NULL,
                            'product_ref'           => $productName,
                            'txn_id'                => $licenseKey,
                            'purchase_id_'          => $licenseKey,
                            'until'                 => isset($purchaseData['supported_until']) ? Time::parse($purchaseData['supported_until'], $appTimezone)->format('Y-m-d H:i:s') : '',
                            'current_ver'           => $products[$productName]['version'] ?? '',
                            'item_reference'        => 'envato',
                            'manual_reset_count'    => '',
                            'date_created'          => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                            'date_activated'        => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                        ];

                        try {
                            $return = $this->LicensesModel->insert($licenseData);
                        
                            if ($return) {
                                // Log the activity
                                licenseManagerLogger($licenseKey, 'create: License created from existing Envato purchase record ' . $licenseKey, 'yes');

                                // Add notification for new license from Envato purchase to User
                                $notificationMessage = 'License created from existing Envato purchase record.';
                                $notificationType = 'license_created';
                                $url = base_url('license-manager/list-all?s=' . $licenseKey);
                                $recipientUserId = $this->userID;
                                add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

                                // Update purchase record to mark as processed
                                $envatoPurchasesModel->update($existingPurchase['id'], [
                                    'license_created' => 1
                                ]);
                                
                                // Log the activity
                                licenseManagerLogger($licenseKey, 'create: License created from existing Envato purchase record ' . $licenseKey, 'yes');
                        
                                // Call this method again to register the domain/device with the newly created license
                                return $this->registerDomainAndDevice($type, $name, $SecretKey, $licenseKey);
                            } else {
                                log_message('error', '[API Controller] Encountered error in attempting to save new license (3).');
                                
                                // Prepare error response for failed insertion
                                $response = [
                                    'result'     => 'error',
                                    'message'    => 'License key not found.',
                                    'error_code' => LICENSE_INVALID
                                ];
                                return $this->respondCreated($response);
                            }
                        } catch (\Throwable $e) {
                            log_message('error', '[API Controller] Encountered error in attempting to save new license: ' . $e->getMessage());
                            
                            // Handle exceptions
                            $response = [
                                    'result'     => 'error',
                                    'message'    => 'License key not found.',
                                    'error_code' => LICENSE_INVALID
                                ];
                            return $this->respondCreated($response);
                        }
                    }
                } else {
                    log_message('info', '[API Controller] Invalid purchase code');
                    
                    $response = [
                        'result'     => 'error',
                        'message'    => 'License key not found.',
                        'error_code' => LICENSE_INVALID
                    ];
                    return $this->respondCreated($response);
                }
            } else {
                // Not a valid license key or Envato purchase code
                // Log the activity
                licenseManagerLogger($licenseKey, 'verify: License key not found', 'no', $this->userID);

                // Add notification for validation error of license
                $notificationMessage = 'License not found error received';
                $notificationType = 'license_validation';
                $url = base_url('license-manager/activity-logs?s=' . $licenseKey);
                $recipientUserId = $this->userID;	
                add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

                $response = [
                    'result'     => 'error',
                    'message'    => 'License key not found.',
                    'error_code' => LICENSE_INVALID
                ];

                return $this->respondCreated($response);
            }
        }

        /***
         * END: Envato Purchase Verification
         */
        else {
            // Validate if license status is active
            if($licenseData['status'] !== 'active') {
                // Log the activity
                licenseManagerLogger($licenseKey, 'registration: Unable to process ' . $type . ' activation as the license status is not active', 'no');

                $response = [
                    'result'     => 'error',
                    'message'    => 'Unable to process request as the license status is not active',
                    'error_code' => LICENSE_INVALID
                ];

                return $this->respondCreated($response);
            }            

            $countAllowed = $licenseData[$allowedCount]; // Get the set allowed domain or device for this license

            // Get the number of domains or devices registered for this license key
            $registeredCount = $model->where('owner_id', $this->userID)->where('license_key', $licenseKey)->countAllResults();
            
            // First, validate if the name already exists in the registered domain or device list
            if (searchArrayRecursive($licenseData[$queryType], $name)) {
                // Log the activity
                licenseManagerLogger($licenseKey, 'registration: ' . ucwords($type) . ' (' . $name . ') is already registered under the license', 'yes');
            
                $response = [
                    'result'     => 'success',
                    'message'    => ucwords($type) . ' (' . $name . ') is already registered under the license',
                    'error_code' => ''
                ];
            
                return $this->respondCreated($response);
            }
            
            // Logic to register
            if ($registeredCount >= $countAllowed) {
                // Log the activity
                licenseManagerLogger($licenseKey, 'registration: Reached maximum allowable ' . $type, 'no');
            
                $response = [
                    'result'     => 'error',
                    'message'    => 'The allowed limit for registered ' . $type . ' has been reached',
                    'error_code' => $errorCode
                ];
                return $this->respondCreated($response);
            }
            else {
                /**
                 * Check the domain or device name if it has registered previously using a different trial license key 
                 * to prevent abuse of multiple trial license key for the same domain/device
                 * */
                if ($licenseData['license_type'] === 'trial') {
                    // Check if the same device/domain already registered previously with the same product name
                    $backgroundCheck = $model->where($columnName, $name)
                                            ->where('item_reference', $licenseData['item_reference'])
                                            ->findAll();

                    if (!empty($backgroundCheck)) {
                        foreach ($backgroundCheck as $prevReg) {
                            $prevLicenseData = getLicenseData($prevReg['license_key']);

                            if (
                                $prevLicenseData['license_type'] === 'trial' &&
                                $prevLicenseData['product_ref'] === $licenseData['product_ref']
                            ) {
                                // Check the gap between previous and current license creation dates
                                $prevDate = Time::parse($prevLicenseData['date_created']);
                                $currentDate = Time::parse($licenseData['date_created']);
                                $daysDifference = abs($prevDate->difference($currentDate)->getDays());

                                log_message('debug', '[API Controller] Previous license date: ' . json_encode($prevDate, JSON_PRETTY_PRINT));
                                log_message('debug', '[API Controller] Current license date: ' . json_encode($prevDate, JSON_PRETTY_PRINT));
                                log_message('debug', '[API Controller] Days Difference: ' . $daysDifference);

                                if ($daysDifference < 7) {
                                    // Add notification for validation error of license
                                    $notificationMessage = 'Multiple trial license abuse suspected.';
                                    $notificationType = 'license_validation';
                                    $url = base_url('license-manager/list-all?s=' . $licenseKey);
                                    $recipientUserId = $licenseData['owner_id'];
                                    add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

                                    // Log the result in license manager logger
                                    licenseManagerLogger($licenseKey, 'registration: The same ' . $type . ' has been registered previously with a trial license key.', 'no');

                                    $response = [
                                        'result'     => 'error',
                                        'message'    => 'The same ' . $type . ' has been registered previously with a trial license key.',
                                        'error_code' => LICENSE_INVALID
                                    ];
                                    return $this->respondCreated($response);
                                }
                            }
                        }
                    }
                }
                                
                // Add new $data to insert
                $data = [
                    'owner_id'       => $this->userID,
                    'license_key_id' => getLicenseData($licenseKey, 'id'),
                    'license_key'    => $licenseKey,
                    $columnName      => $name,
                    'item_reference' => $licenseData['item_reference'],
                ];
            
                try {
                    $model->insert($data);
            
                    // Log the activity
                    licenseManagerLogger($licenseKey, 'registration: Registration of the ' . $type . ' (' . $name . ') was successful', 'yes');
            
                    // Initiate email notification to customer
                    if ($this->myConfig['sendNewDomainDeviceRegistration']) {
                        $bccAdmin = $this->myConfig['sendBCCtoLicenseClientNotifications'] ? true : false;

                        $emailService = new \App\Libraries\EmailService();
                        $licenseNotificationResult = $emailService->sendLicenseNotification([
                            'license_key' => $licenseData['license_key'],
                            'recipient_email' => $licenseData['email'],
                            'date_activity' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                            'template' => 'license_activation_notification',
                            'email_format' => 'html',
                            'with_bcc' => $bccAdmin
                        ]);   
                    }
            
                    $response = [
                        'result'     => 'success',
                        'message'    => 'Registration of the ' . $type . ' (' . $name . ') was successful',
                        'error_code' => ''
                    ];
                    return $this->respondCreated($response);
            
                } catch (\Throwable $e) {
                    // Handle exceptions
                    $response = [
                        'result'     => 'error',
                        'message'    => 'License registration error: ' . $e->getMessage(),
                        'error_code' => QUERY_ERROR
                    ];
            
                    return $this->respondCreated($response);
                }
            }            

            // Log the activity
            licenseManagerLogger($licenseKey, 'verify: License key not found', 'no', $this->userID);

            // Add notification for validation error of license
            $notificationMessage = 'License not found error received';
            $notificationType = 'license_validation';
            $url = base_url('license-manager/activity-logs?s=' . $licenseKey);
            $recipientUserId = $this->userID;	
            add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

            $response = [
                'result'     => 'error',
                'message'    => 'License key not found.',
                'error_code' => LICENSE_INVALID
            ];

            return $this->respondCreated($response);
        }
    }

    /**
     * Action: license logs
     * URI: /api/license/logs/{options}/{secret_key}
     * Method: GET
     * Description: Retrieve all license activity logs
     * Parameter:
     * {options} (required)     : all or a specific license
     * {secret_key} (required)  : A secret key for authorization.
     * @return mixed The response containing the list of licenses or an error message.
    */
    public function license_acitivty_logs($licenseKey, $secretKey)
    {
        $authResult = $this->authorizeSecretKey('general', $secretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }

        $licenseKey = $this->stripValue($licenseKey);

        // Get data if by specific license or all
        if($licenseKey !== 'all') {
			// Define the where clause for filtering licenses
			$where = ['license_key' => $licenseKey];
	
			// Get all records with the same license key
			$data = $this->LicenseLogsModel->where('owner_id', $this->userID)
                                            ->where($where)->orderBy('id', 'DESC')
                                            ->findAll();
        }
        else if($licenseKey === 'all') {
            // Get all records
			$data = $this->LicenseLogsModel->where('owner_id', $this->userID)
                                            ->orderBy('id', 'DESC')
                                            ->findAll();
        }
        else {
            // Throw error as request is required
        }

        return $this->respondCreated($data);
    }      

    /**
     * Action: subscribers
     * URI: /api/license/subscribers/{secret_key}
     * Method: GET
     * Description: Retrieve a list of all subscribers.
     * Parameters:
     * {secret_key} (required): A secret key for authorization.
     * @return mixed The response containing the list of licenses or an error message.
    */
    public function subscribers($secretKey)
    {
        $authResult = $this->authorizeSecretKey('general', $secretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }
    
        $data = $this->LicenseEmailListModel->where('owner_id', $this->userID)
                                            ->orderBy('id', 'DESC')
                                            ->findAll();
        return $this->respondCreated($data);
    }    

    /**
     * Action: generate
     * URI: /api/license/generate
     * Method: GET
     * Description: Generate license for SLM integration
     * @return mixed The response containing the generate new license key
    */
    public function generateLicenseKeySLM()
    {
        $this->userID = $this->getUserID();

        if(!$this->userID) {
            $response = [
                'result'     => 'error',
                'message'    => 'Unauthorized access',
                'error_code' => FORBIDDEN_ERROR
            ];
    
            return $this->respondCreated($response);
        }

        return $this->respondCreated(generateLicenseKey($this->userID));
    }    

    /**
     * Action: product/all
     * URI: /api/product/all
     * Method: GET
     * Description: List all products (base name only)
     * @return mixed The response containing the list of all products (base name only)
    */
    public function listProducts()
    {
        $this->userID = $this->getUserID();

        if(!$this->userID) {
            $response = [
                'result'     => 'error',
                'message'    => 'Unauthorized access',
                'error_code' => FORBIDDEN_ERROR
            ];
    
            return $this->respondCreated($response);
        }

        $data = [];
		$data = productList($this->userID);

        if(count($data) !== 0) {
            return $this->respondCreated($data);
        }
        else {
            $response = [
                'result'     => 'error',
                'message'    => 'Requested data is empty',
                'error_code' => RETURNED_EMPTY
            ];

            return $this->respondCreated($response);
        }
    }

    /**
     * Action: product/with-variations
     * URI: /api/product/with-variations
     * Method: GET
     * Description: List all products with variations
     * @return mixed The response containing the list of all products with variations
    */
    public function listProductsWithVariations()
    {
        $this->userID = $this->getUserID();

        if(!$this->userID) {
            $response = [
                'result'     => 'error',
                'message'    => 'Unauthorized access',
                'error_code' => FORBIDDEN_ERROR
            ];
    
            return $this->respondCreated($response);
        }

        $data = [];
		$data = productListWithVariation($this->userID);

        if(count($data) !== 0) {
            return $this->respondCreated($data);
        }
        else {
            $response = [
                'result'     => 'error',
                'message'    => 'Requested data is empty',
                'error_code' => RETURNED_EMPTY
            ];

            return $this->respondCreated($response);
        }
    }

    /**
     * Action: product/versions
     * URI: /api/product/current-versions
     * Method: GET
     * Description: List all product versions with variations
     * @return mixed The response containing the list of all product's versions with variations
    */
    public function listProductCurrentVersions()
    {
        $this->userID = $this->getUserID();

        if(!$this->userID) {
            $response = [
                'result'     => 'error',
                'message'    => 'Unauthorized access',
                'error_code' => FORBIDDEN_ERROR
            ];
    
            return $this->respondCreated($response);
        }

        $data = [];
		$data = allProductCurrentVersions($this->userID);

        if(count($data) !== 0) {
            return $this->respondCreated($data);
        }
        else {
            $response = [
                'result'     => 'error',
                'message'    => 'Requested data is empty',
                'error_code' => RETURNED_EMPTY
            ];

            return $this->respondCreated($response);
        }
    }    

    /**
     * Action: variation
     * URI: /api/variation/all
     * Method: GET
     * Description: List all variations
     * @return mixed The response containing the list of all configured variations
    */
    public function listVariations()
    {
        $this->userID = $this->getUserID();

        if(!$this->userID) {
            $response = [
                'result'     => 'error',
                'message'    => 'Unauthorized access',
                'error_code' => FORBIDDEN_ERROR
            ];
    
            return $this->respondCreated($response);
        }

        $data = [];
		$data = getProductVariations($this->userID);

        if(count($data) !== 0) {
            return $this->respondCreated($data);
        }
        else {
            $response = [
                'result'     => 'error',
                'message'    => 'Requested data is empty',
                'error_code' => RETURNED_EMPTY
            ];

            return $this->respondCreated($response);
        }
    }

    /**
     * Action: delete database entries
     * URI: /api/license/delete/{option}/{secret_key}/{license_key}
     * Method: POST
     * Description: Delete in the database of posted ID(s)
     * Parameters:
     * {option} (required)      : 'key' for license deleteion, 'logs' for log entry, 'all-logs' for all log entires, 'subscriber' for email subscriber
     * {secret_key} (required)  : A secret key for authorization.
     * {license_key} (optional) : If deletion is for specific license key only in the activity log
     * 
     * Posted data example:
     * name="selectedLicense[]" value="143"
     * name="selectedLicense[]" value="142"
     * @return mixed The response containing the list of all configured variations
    */
	public function delete_license_action($option,$secretKey, $licenseKey=NULL)
	{
        $authResult = $this->authorizeSecretKey('manage', $secretKey);

        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}        
    
        // license key deletion
        if($option === 'key') {
            // Get selected licenses to delete
            $selectedLicenses = $this->request->getPost('selectedLicense');
    
            if (!empty($selectedLicenses)) {
                $deletedLicenses = [];
                $failedLicenses = [];
    
                if (is_array($selectedLicenses)) {
                    // Process multiple selected licenses
                    foreach ($selectedLicenses as $selectedLicense) {
                        if ($this->deleteLicense($selectedLicense, 'key')) {
                            $deletedLicenses[] = $selectedLicense;
                        } else {
                            $failedLicenses[] = $selectedLicense;
                        }
                    }
                } else {
                    // Process single selected license
                    if ($this->deleteLicense($selectedLicenses, 'key')) {
                        $deletedLicenses[] = $selectedLicenses;
                    } else {
                        $failedLicenses[] = $selectedLicenses;
                    }
                }
    
                // Prepare response
                $response = [
                    'result'     => !empty($deletedLicenses) ? 'success' : 'error',
                    'message'    => !empty($deletedLicenses) ? lang('Notifications.success_deleted_license') : lang('Notifications.error_deleting_license'),
                    'error_code' => '',
                    'deleted_licenses' => $deletedLicenses,
                    'failed_licenses' => $failedLicenses,
                ];

            } else {
                // No licenses selected for deletion
                $response = [
                    'result'     => 'error',
                    'message'    => lang('Notifications.error_no_license_selected_for_deletion'),
                    'error_code' => QUERY_NOT_FOUND,
                    'deleted_licenses' => [],
                    'failed_licenses' => [],
                ];                    
            }

        }

        // license log deletion
        else if($option === 'logs') {

            // Request is for individual license key only
            $licenseKey = $this->stripValue($licenseKey);
            if($licenseKey) {
                // Retrieve the record based on the license key
                $licenseRecord = $this->LicensesModel->where('owner_id', $this->userID)
                                                        ->where('license_key', $licenseKey)
                                                        ->first();        
            
                if ($licenseRecord) {
                    // Define the where clause for filtering licenses
                    $where = ['license_key' => $licenseKey];
            
                    // Delete all records with the same license key
                    $deleted = $this->LicenseLogsModel->where('owner_id', $this->userID)
                                                        ->where($where)
                                                        ->delete();
            
                    if ($deleted) {
                        // Log the activity
                        licenseManagerLogger($licenseRecord['license_key'], 'log: License activity log deletion initiated', 'yes');
        
                        // Deletion successful
                        $response = [
                            'result'     => 'success',
                            'message'    => lang('Notifications.success_activity_log_cleared'),
                            'error_code' => '',                      
                        ];
                    } else {
                        // No records found to delete
                        $response = [
                            'result'     => 'error',
                            'message'    => lang('Notifications.error_clearing_activity_log_no_record'),
                            'error_code' => '',                      
                        ];
                    }           
                } else {
                    // No license record found with the provided license key                        
                    $response = [
                        'result'     => 'error',
                        'message'    => lang('Notifications.error_clearing_activity_log_no_license_record'),
                        'error_code' => '',                      
                    ];
                }

                return $this->respondCreated($response);
            }

            // Get selected licenses to delete
            $selectedLicenses = $this->request->getPost('selectedLicense');
    
            if (!empty($selectedLicenses)) {
                $deletedLicenses = [];
                $failedLicenses = [];
    
                if (is_array($selectedLicenses)) {
                    // Process multiple selected licenses
                    foreach ($selectedLicenses as $selectedLicense) {
                        if ($this->deleteLicense($selectedLicense, 'logs')) {
                            $deletedLicenses[] = $selectedLicense;
                        } else {
                            $failedLicenses[] = $selectedLicense;
                        }
                    }
                } else {
                    // Process single selected license
                    if ($this->deleteLicense($selectedLicenses, 'logs')) {
                        $deletedLicenses[] = $selectedLicenses;
                    } else {
                        $failedLicenses[] = $selectedLicenses;
                    }
                }
    
                // Prepare response
                $response = [
                    'result'     => 'success',
                    'message'    => !empty($deletedLicenses) ? lang('Notifications.success_deleted_license_logs') : lang('Notifications.error_deleting_license'),
                    'error_code' => '',
                    'deleted_licenses' => $deletedLicenses,
                    'failed_licenses' => $failedLicenses,                        
                ];

            } else {
                // No licenses selected for deletion
                $response = [
                    'result'     => 'error',
                    'message'    => lang('Notifications.error_no_log_selected_for_deletion'),
                    'error_code' => QUERY_NOT_FOUND,
                    'deleted_licenses' => [],
                    'failed_licenses' => [],
                ];                    
            }
        }

        // license subscriber deletion
        else if($option === 'subscriber') {
            // Get selected licenses to delete
            $selectedLicenses = $this->request->getPost('selectedLicense');
    
            if (!empty($selectedLicenses)) {
                $deletedLicenses = [];
                $failedLicenses = [];
    
                if (is_array($selectedLicenses)) {
                    // Process multiple selected licenses
                    foreach ($selectedLicenses as $selectedLicense) {
                        if ($this->deleteLicense($selectedLicense, 'subscriber')) {
                            $deletedLicenses[] = $selectedLicense;
                        } else {
                            $failedLicenses[] = $selectedLicense;
                        }
                    }
                } else {
                    // Process single selected license
                    if ($this->deleteLicense($selectedLicenses, 'subscriber')) {
                        $deletedLicenses[] = $selectedLicenses;
                    } else {
                        $failedLicenses[] = $selectedLicenses;
                    }
                }
    
                // Prepare response
                $response = [
                    'result'     => 'success',
                    'message'    => !empty($deletedLicenses) ? lang('Notifications.success_deleted_subscribers') : lang('Notifications.error_deleting_license'),
                    'error_code' => '',
                    'deleted_licenses' => $deletedLicenses,
                    'failed_licenses' => $failedLicenses,                        
                ];

            } else {
                // No licenses selected for deletion
                $response = [
                    'result'     => 'error',
                    'message'    => lang('Notifications.error_no_subscriber_selected_for_deletion'),
                    'error_code' => QUERY_NOT_FOUND,
                    'deleted_licenses' => [],
                    'failed_licenses' => [],
                ];                    
            }
        }
        
        // license log deletion
        else if($option === 'all-logs') {
            try {
                // Delete all records
                $deleted = $this->LicenseLogsModel->where('owner_id', $this->userID)
                                                    ->truncate();
                
                if ($deleted) {
                    // Log the activity
                    // licenseManagerLogger('All license activity logs cleared.', 'log: License activity log deletion initiated', 'yes');
        
                    // Deletion successful
                    $response = [
                        'result'     => 'success',
                        'message'    => lang('Notifications.success_all_activity_log_cleared'),
                        'error_code' => '',                       
                    ];

                } else {
                    // No records found to delete
                    $response = [
                        'result'     => 'error',
                        'message'    => lang('Notifications.error_clearing_all_activity_log_no_record'),
                        'error_code' => '',                       
                    ];                        
                }
            } catch (\Exception $e) {
                // Error occurred during deletion
                log_message('error', '[API] Error occured during deletion of all activity: ' . $e->getMessage());
                $response = [
                    'success' => false,
                    'status' => 0,
                    'msg' => $e->getMessage() // Return the error message
                ];
            }
        }

        return $this->respondCreated($response);        
	}

	// Helper function to delete a license
	private function deleteLicense($selectedID, $option)
	{
        // license key deletion
        if($option === 'key') {
            // Retrieve the ID of the record based on the license key
            $licenseRecord = $this->LicensesModel->where('id', $selectedID)->first();

            if ($licenseRecord) {
                // Log the activity
                licenseManagerLogger($licenseRecord['license_key'], 'delete: License deletion initiated', 'yes');

                // Delete the record using its ID
                $deleted =$this->LicensesModel->delete($selectedID);
                return $deleted;
            } else {
                // Record not found, cannot delete
                return false;
            }
        }

        // license log deletion
        else if($option === 'logs') {   
            // Retrieve the ID of the record based on the license key
            $licenseRecord = $this->LicenseLogsModel->where('id', $selectedID)->first();

            if ($licenseRecord) {
                // Log the activity
                licenseManagerLogger($licenseRecord['license_key'], 'log: License activity log deletion initiated', 'yes');

                // Delete the record using its ID
                $deleted = $this->LicenseLogsModel->delete($selectedID);
                return $deleted;
            } else {
                // Record not found, cannot delete
                return false;
            }
        }

        // license subscriber deletion
        else if($option === 'subscriber') {   
            // Retrieve the ID of the record based on the license key
            $licenseRecord = $this->LicenseEmailListModel->where('id', $selectedID)->first();

            if ($licenseRecord) {
                // Log the activity
                licenseManagerLogger($licenseRecord['license_key'], 'subscriber: Subscriber deletion initiated', 'yes');

                // Delete the record using its ID
                $deleted = $this->LicenseEmailListModel->delete($selectedID);
                return $deleted;
            } else {
                // Record not found, cannot delete
                return false;
            }
        }
	}

    /**
     * Action: get product files/packages
     * URI: /api/product/packages/{product_name}/{secret_key}
     * Method: GET
     * Description: Get the list of uploaded files for a specific product
     * Parameters:
     * {product_name} (required)     : 'all' to get all the files for each product or 'product_name' for a specific product only
     * {secret_key} (required)  : A secret key for authorization.
     * 
     * @return mixed The response containing the list of all configured variations
    */
	public function fetchProductFiles($requestedProduct, $secretKey)
	{
        $authResult = $this->authorizeSecretKey('general', $secretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }

        $fetchList = getProductFiles('', $this->userID);    
   
        if($requestedProduct !== 'all') {
            $requestedProduct = productBasename($requestedProduct, $this->userID);

            // check if product exists
            if (!in_array($requestedProduct, productList($this->userID))) {
                $response = [
                    'result'     => 'error',
                    'message'    => 'Requested product is not existing.',
                    'error_code' => QUERY_NOT_FOUND
                ];

                return $this->respondCreated($response);
            }
            else {                
                return $this->respondCreated($fetchList[$requestedProduct]);       
            }            
        }
        else {
            return $this->respondCreated($fetchList);
        }
    }

    /**
     * Action: get product changelog/data
     * URI: /api/product/changelog/{product_name}/{secret_key}
     * Method: GET
     * Description: Get the product's changelog
     * Parameters:
     * {product_name} (required)     : 'all' to get all the changelog for each product or 'product_name' for a specific product only
     * {secret_key} (required)  : A secret key for authorization.
     * 
     * @return mixed The response containing the list of all configured variations
    */
	public function fetchProductChangelog($requestedProduct, $secretKey)
	{
        $authResult = $this->authorizeSecretKey('general', $secretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }   
   
        if($requestedProduct !== 'all') {
            $requestedProduct = productBasename($requestedProduct, $this->userID);

            // check if product exists
            if (!in_array($requestedProduct, productList($this->userID))) {
                $response = [
                    'result'     => 'error',
                    'message'    => 'Requested product is not existing.',
                    'error_code' => QUERY_NOT_FOUND
                ];

                return $this->respondCreated($response);
            }
            else {       
                return $this->respondCreated(productDetails($requestedProduct, $this->userID));
            }            
        }
        else {
            return $this->respondCreated(productDetails('', $this->userID));
        }
    }

    /**
     * Action: routine validation of domain/device
     * URI: /validate?t={product_name}&s={license_key}&d={name}
     * Method: GET
     * Description: Quick validation to check if the query domain/device name is registered in provided license key and at the same time check the license's validity as well
     * Parameters:
     * t    : {product_name} - Product's name
     * s    : {license_key} - License key
     * d    : {name} - The doamin or device name for query
     * 
     * @return numeric '0' if returned invalid or '1' if valid
    */
	public function routineValidation()
	{
		// Setting the response header to JSON
		// header('Content-Type: application/json');

		$getData = $this->request->getGet();

		$product = isset($getData['t']) ? $getData['t'] : NULL;
		$licenseKey = isset($getData['s']) ? $getData['s'] : NULL;
		$domainDevice = isset($getData['d']) ? $getData['d'] : NULL;

        if($licenseKey) {
            $this->userID = getOwnerByLicenseKey($licenseKey);
        }

        if(!$this->userID) {
            $response = [
                'result'     => 'error',
                'message'    => 'Unauthorized access',
                'error_code' => FORBIDDEN_ERROR
            ];
    
            return $this->respondCreated($response);
        }
				
		// Checking if there are any GET parameters
		if ($getData) { 
			// Checking if required parameters are set
			if ($this->userID && $product && $licenseKey && $domainDevice) {
				// Calling the validateLicense method with provided parameters
				return $this->validateLicense($this->userID, $product, $licenseKey, $domainDevice);
			} else {
                return $this->respondCreated(0);
			}
		}

        return $this->respondCreated(0);
	}
    
	protected function validateLicense($userID, $product, $licenseKey, $domain_or_device)
	{
		$is_license_valid = false; // Set default license value to false

		// Extract product base name
		$product = productBasename($product, $userID);
				
		$licenseStatus = getLicenseData($licenseKey); // Get the license status from the license server

		if (isset($licenseStatus['error'])) {
			// return "Error fetching license details: " . $licenseDetails['error'];
			$is_license_valid = true;
		}
		else {
            $this->userID = $licenseStatus['owner_id'];

            $this->myConfig = getMyConfig('', $this->userID);
            $this->userDataPath = $this->userID ? USER_DATA_PATH . $this->userID . '/' : NULL;

			$now_date =  Time::now()->format('Y-m-d'); // Set the date format		
			$pathTo_Valid_Queries = $this->userDataPath . $this->myConfig['userLogsPath'] . $this->myConfig['License_Valid_Log_FileName']; // Path to valid license log
			$pathTo_Invalid_Queries = $this->userDataPath . $this->myConfig['userLogsPath'] . $this->myConfig['License_Invalid_Log_FileName']; // Path to invalid license log

			$is_license_valid = false; // Set default license value to false

			// Criterias
			$inProductList = false;
			$licenseSubscription = false;
			$matchesLicenseProduct_and_Query = false;
			$licenseRegisteredDomainDevice = false;
			$licenseIsExcempted = false;

			/***
			 * First
			 * Check if the query product is in the list
			 *  */ 
			$productList = productList($this->userID);
			if(in_array($product, $productList)) {
				$inProductList = true;
			}
			
			/***
			 * Second
			 * Check subscription if valid or if lifetime
			 *  */ 
			if(isset($licenseStatus['license_type'])) {
                if ($inProductList && ($licenseStatus['license_type'] == 'subscription') && (strtotime($licenseStatus['date_expiry']) > strtotime($now_date)) && ($licenseStatus['status'] == 'active')) {
                    $licenseSubscription = true;
                } 
                else if ($inProductList && ($licenseStatus['license_type'] == 'trial') && (strtotime($licenseStatus['date_expiry']) > strtotime($now_date)) && ($licenseStatus['status'] == 'active')) {
                    $licenseSubscription = true;
                }
                else if ($inProductList && ($licenseStatus['license_type'] == 'lifetime') && ($licenseStatus['status'] == 'active')) {
                    $licenseSubscription = true;
                }
                else {
                    $licenseSubscription = false;
                }
            }
            else {
                $licenseSubscription = false;
            }
			
			/***
			 * Third
			 * Check if matches with the product registered
			 *  */ 
			$license_product_for = isset($licenseStatus['product_ref']) ? $licenseStatus['product_ref'] : '';

			// Extract product base name
			$license_product_for = productBasename($license_product_for, $userID);

			if ($inProductList && $licenseSubscription && ($product === $license_product_for) ) {
				$matchesLicenseProduct_and_Query = true;
			}
			
			/***
			 * Fourth
			 * Check if registered domain or device
			 *  */ 

			// Check the registered domain
            $licenseRegisteredDomain = false;

            if($licenseStatus['max_allowed_domains'] !== '0') {
                $licenseRegisteredDomain = false;
                if ($inProductList && $licenseSubscription && $matchesLicenseProduct_and_Query && isset($licenseStatus['lic_type']) && $licenseStatus['registered_domains']) {
                    $licenseRegisteredDomain = $this->check_registered_domains($domain_or_device, $licenseStatus['registered_domains']);
                }

                // Log the activity
                if(!$licenseRegisteredDomain) {
                    // Invalid result
                    licenseManagerLogger($licenseKey, 'verify: Invalid license validation with registered domain ('. $domain_or_device . ')', 'no');
                }
                else {
                    // Valid result
                    licenseManagerLogger($licenseKey, 'verify: Valid license validation with registered domain ('. $domain_or_device . ')', 'yes');
                }
            }          
		
			// Check the registered device
            $licenseRegisteredDevice = false;

            if(!$licenseRegisteredDomain && $licenseStatus['max_allowed_devices'] !== '0') {
                $licenseRegisteredDevice = false;

                if ($inProductList && $licenseSubscription && $matchesLicenseProduct_and_Query && isset($licenseStatus['lic_type']) && $licenseStatus['registered_devices']) {
                    $licenseRegisteredDevice = $this->check_registered_devices($domain_or_device, $licenseStatus['registered_devices']);
                }

                // Log the activity
                if(!$licenseRegisteredDevice) {
                    // Invalid result
                    licenseManagerLogger($licenseKey, 'verify: Invalid license validation with registered device ('. $domain_or_device . ')', 'no');
                }
                else {
                    // Valid result
                    licenseManagerLogger($licenseKey, 'verify: Valid license validation with registered device ('. $domain_or_device . ')', 'yes');
                }           
            }

			if( $licenseRegisteredDomain || (!$licenseRegisteredDomain && $licenseRegisteredDevice) ) {
				$is_license_valid = true;
			}
			else {
				$is_license_valid = false;
			}           
		
			// Write the invalid checks here
			if (!$is_license_valid) {       
				$message_item = array(
					'time'              => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
					'product'           => $product,
					'domain_or_device'  => $domain_or_device,
					'license'           => $licenseKey,
					'is_license_valid'  => lang('Pages.Invalid'),
					'checking_result'   => json_encode($licenseStatus, JSON_FORCE_OBJECT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
				);
				
				// CSV FILE
				if (!file_exists($pathTo_Invalid_Queries)) {
					
					$header = lang('Pages.TIME') . ", " . lang('Pages.PRODUCT') . ", " . lang('Pages.DOMAIN_DEVICE') . ", " . lang('Pages.LICENSE') . ", " . lang('Pages.RESULT') . ", " . lang('Pages.API_RESULT');
					$file = fopen($pathTo_Invalid_Queries, 'wb');
					fputcsv($file, explode(', ', $header));
					fclose($file);       
				}
                
				$file = fopen($pathTo_Invalid_Queries, 'a+');
				fputcsv($file, $message_item);
				fclose($file);                       
				
				if($this->myConfig['sendEmailInvalidChecks']) {
                    // Add notification for validation error of license
                    $notificationMessage = 'License validation error received';
                    $notificationType = 'license_validation';
                    $url = base_url('license-manager/list-all?s=' . $licenseKey);
                    $recipientUserId = $this->userID;
                    add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

					// Email settings
					$toEmail        = $this->myConfig['adminEmail'];
					$subject        = lang('Pages.License_Error_Received');
                    $message_item['checking_result'] = $licenseStatus;

                    $emailService = new \App\Libraries\EmailService();

                    try {
                        $licenseNotificationResult = $emailService->sendGeneralEmail([
                                'license_key' => $licenseKey,
                            	'template' => 'general_email',
                            	'userID' => $this->userID,
                            	'email_format' => 'html',
                            	'recipient_email' => $toEmail,
                            	'subject' => $subject,
                            	'message' => $this->routine_license_check_result_for_email(json_encode($message_item, true))
                        ]);
                    } catch (\Throwable $e) {
                        // Handle exceptions
                        log_message(
                            'error',
                            '[License Manager] Error sending license notification: ' . $e->getMessage()
                        );
                    }
				}
		
			} else {
				$message_item = array(
					'time'              => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
					'product'           => $product,
					'domain_or_device'  => $domain_or_device,
					'license'           => $licenseKey,
					'is_license_valid'  => lang('Pages.Valid'),
					'checking_result'   => json_encode($licenseStatus, JSON_FORCE_OBJECT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
				);  
		
				// CSV FILE
				if (!file_exists($pathTo_Valid_Queries)) {
					$header = lang('Pages.TIME') . ", " . lang('Pages.PRODUCT') . ", " . lang('Pages.DOMAIN_DEVICE') . ", " . lang('Pages.LICENSE') . ", " . lang('Pages.RESULT') . ", " . lang('Pages.API_RESULT');
					$file = fopen($pathTo_Valid_Queries, 'wb');
					fputcsv($file, explode(', ', $header));
					fclose($file);       
				}       
				$file = fopen($pathTo_Valid_Queries, 'a+');
				fputcsv($file, $message_item);
				fclose($file);      
			}
		}

		if($is_license_valid) {
			$is_license_valid = '1';
		}
		else {
			$is_license_valid = '0';
		}

        return $this->respondCreated($is_license_valid);
	}

    protected function routine_license_check_result_for_email($jsonData)
    {
        try {
            // First remove any extra quotes if the JSON is double encoded
            $jsonData = trim($jsonData, '"');
            // Fix escaped quotes if any
            $jsonData = str_replace('\"', '"', $jsonData);
            
            // Decode outer JSON
            $decoded_message = json_decode($jsonData, true);
            if (!$decoded_message) {
                return "Error: Invalid message format";
            }
    
            // Get checking_result
            $checking_result = $decoded_message['checking_result'];
    
            // Start building HTML content
            $html = '<div class="license-check">';
            
            // Header section
            $html .= '<table>';
            $html .= '    <tr>';
            $html .= '        <td>Date & Time</td>';
            $html .= '        <td>' .  htmlspecialchars(formatDate($decoded_message['time'], $this->myConfig)) . '</td>';
            $html .= '    </tr>';
            $html .= '    <tr>';
            $html .= '        <td>Product</td>';
            $html .= '        <td>' .  htmlspecialchars($decoded_message['product']) . '</td>';
            $html .= '    </tr>';
            $html .= '	<tr>';
            $html .= '		<td>Domain/Device Name</td>';
            $html .= '		<td>' .  htmlspecialchars($decoded_message['domain_or_device']) . '</td>';
            $html .= '	</tr>';
            $html .= '	<tr>';
            $html .= '		<td>License</td>';
            $html .= '		<td>' .  htmlspecialchars($decoded_message['license']) . '</td>';
            $html .= '	</tr>';
            $html .= '	<tr>';
            $html .= '		<td>License Status</td>';
            $html .= '		<td>' .  htmlspecialchars($decoded_message['is_license_valid']) . '</td>';
            $html .= '	</tr>';
            $html .= '</table>';
            
            // License Details
            $html .= '<h4>License Details:</h4>';
            $html .= '<ul>';
            $html .= '<li>First Name: ' . htmlspecialchars($checking_result['first_name']) . '</li>';
            $html .= '<li>Last Name: ' . htmlspecialchars($checking_result['last_name']) . '</li>';
            $html .= '<li>Company: ' . htmlspecialchars($checking_result['company_name']) . '</li>';
            $html .= '<li>Email: ' . htmlspecialchars($checking_result['email']) . '</li>';
            $html .= '<li>License Type: ' . htmlspecialchars($checking_result['license_type']) . '</li>';
            $html .= '<li>Max Domains: ' . htmlspecialchars($checking_result['max_allowed_domains']) . '</li>';
            $html .= '<li>Max Devices: ' . htmlspecialchars($checking_result['max_allowed_devices']) . '</li>';

            if(($checking_result['license_type'] === 'trial') || ($checking_result['license_type'] === 'subscription') ) {
                $html .= '<li>Expiry Date: ' . htmlspecialchars(formatDate($checking_result['date_expiry'] ?? '', $this->myConfig)) . '</li>';    
            }
            
            $html .= '</ul>';
            
            // Registered Domains
            $html .= '<h4>Registered Domains:</h4>';
            if (!empty($checking_result['registered_domains'])) {
                $html .= '<ul>';
                foreach ($checking_result['registered_domains'] as $domain) {
                    $html .= '<li>';
                    $html .= 'Domain: ' . htmlspecialchars($domain['domain_name']);
                    $html .= '<ul>';
                    $html .= '<li>ID: ' . htmlspecialchars($domain['id']) . '</li>';
                    $html .= '<li>License Key: ' . htmlspecialchars($domain['license_key']) . '</li>';
                    if (!empty($domain['item_reference'])) {
                        $html .= '<li>Item Reference: ' . htmlspecialchars($domain['item_reference']) . '</li>';
                    }
                    $html .= '</ul>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
            } else {
                $html .= '<p>No domains registered</p>';
            }
            
            // Registered Devices
            $html .= '<h4>Registered Devices:</h4>';
            if (!empty($checking_result['registered_devices'])) {
                $html .= '<ul>';
                foreach ($checking_result['registered_devices'] as $device) {
                    $html .= '<li>';
                    $html .= 'Device: ' . htmlspecialchars($device['device_name']);
                    $html .= '<ul>';
                    $html .= '<li>ID: ' . htmlspecialchars($device['id']) . '</li>';
                    $html .= '<li>License Key: ' . htmlspecialchars($device['license_key']) . '</li>';
                    if (!empty($device['item_reference'])) {
                        $html .= '<li>Item Reference: ' . htmlspecialchars($device['item_reference']) . '</li>';
                    }
                    $html .= '</ul>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
            } else {
                $html .= '<p>No devices registered</p>';
            }
            
            $html .= '</div>';
            
            return $html;
    
        } catch (Exception $e) {
            log_message('error', '[API] License check email formatting error: ' . $e->getMessage());
            return '<div class="error">Error processing license check results: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
    
	protected function check_registered_domains($domain, $registered_domains, $strict = false)
	{
		foreach ($registered_domains as $item) {
			if (($strict ? $item === $domain : $item == $domain) || (is_array($item) && $this->check_registered_domains($domain, $item, $strict))) {
				return true;
			}
		}    
		return false;
	}
	
	protected function check_registered_devices($device, $registered_devices, $strict = false)
	{
		foreach ($registered_devices as $item) {
			if (($strict ? $item === $device : $item == $device) || (is_array($item) && $this->check_registered_devices($device, $item, $strict))) {
				return true;
			}
		}    
		return false;
	}

    /**
     * Action: edit license
     * URI: /api/license/edit/{secret_key}
     * Method: POST
     * Description: Create a new license
     * Parameters:
     * license_key                  : Target license key to edit details
     * license_status               : 'pending', 'active', 'blocked', 'expired'
     * license_type                 : Type of license. 'trial', 'subscription', 'lifetime
     * first_name                   : License user's first name
     * last_name                    : License user's last name
     * email                        : Client email address
     * subscr_id (optional)         : The Subscriber ID (if any). Can be useful if you are using the license key with a recurring payment plan.
     * company_name (optional)      : License user's company name (if any)
     * max_allowed_domains          : Number of domains/installs in which this license can be used
     * max_allowed_devices          : Number of devices/installs in which this license can be used
     * billing_length (required: if license_type = 'subscription')      : Amount in days or months or years
     * billing_interval (required: if license_type = 'subscription')    : Frequency period: in days, months, years or onetime   
     * date_expiry (required: if license_type = 'subscription') : The license license_status will automatically become expired if the date reached
     * product_ref                  : The product that this license gives access to
     * txn_id                       : The unique transaction ID associated with this license key
     * purchase_id_                 : This is associated with the purchase ID for third party payment app or platform. 
     * until (optional)             : Until what version this product is supported (if applicable)
     * current_ver (optional)       : What is the current version of this product
     * item_reference (optional)    : By the default, it will be the same as product_ref
     * manual_reset_count (optional): The number of times this license has been manually reset by the admin (use it if you want to keep track of it). It can be helpful for the admin to keep track of manual reset counts
     * @return mixed The response containing the modified license details or an error message.
    */
    public function editLicense($secretKey)
    {

        $authResult = $this->authorizeSecretKey('manage', $secretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}        

        // Get all posted data
        $postedData = $this->request->getPost();

        // Check if license key provided
        if(!isset($postedData['license_key'])) {
            $response = [
                'result'     => 'error',
                'message'    => 'License detail update failed. The license key is required to proceed with the request.',
                'error_code' => KEY_UPDATE_FAILED
            ];
            
            return $this->respondCreated($response);
        }

        // Check if the target license exists
        $targetLicense = $this->LicensesModel->where('owner_id', $this->userID)
                                                ->where('license_key', $postedData['license_key'])
                                                ->first();

        try {
            if($targetLicense) {
                
                // reformat the date_expiry if present
                if ( ($postedData['date_expiry'] !== null) && ($postedData['date_expiry'] !== '') && $postedData['license_type'] !== 'lifetime') {
                    // User's timezone from configuration

                    // First check session for detected timezone
                    $session = session();
					$userTimezone = $session->get('detected_timezone') ?? 
									$this->myConfig['defaultTimezone'] ?? 
									'UTC';
                    
                    // App's default timezone
                    $appTimezone = app_timezone();

                    // Parse the date with the user's timezone
                    $dateCreated = getLicenseData($postedData['license_key'], 'date_created');
                    $dateCreated = Time::parse($dateCreated, $appTimezone);

                    // Remove AM/PM and convert to 24-hour format
                    $dateExpiry = $postedData['date_expiry'];
                    $is_pm = stripos($dateExpiry, 'PM') !== false;
                    $dateExpiry = trim(str_replace(['AM', 'PM'], '', $dateExpiry));

                    // Parse the expiration date in the user's timezone
                    $expirationDate = Time::parse($dateExpiry, $userTimezone);

                    // Convert to app's default timezone
                    $expirationDate = $expirationDate->setTimezone($appTimezone);

                    // Convert to 24-hour format if needed
                    if ($is_pm) {
                        $hour = $expirationDate->getHour();
                        if ($hour !== 12) {
                            $expirationDate = $expirationDate->setTime($hour + 12, $expirationDate->getMinute(), $expirationDate->getSecond());
                        }
                    } else {
                        // Handle midnight (12 AM)
                        $hour = $expirationDate->getHour();
                        if ($hour === 12) {
                            $expirationDate = $expirationDate->setTime(0, $expirationDate->getMinute(), $expirationDate->getSecond());
                        }
                    }

                    // Check if the time is '00:00:00'
                    if ($expirationDate->getHour() === 0 && $expirationDate->getMinute() === 0) {
                        // Set the time to the same as the creation date
                        $postedData['date_expiry'] = $expirationDate
                            ->setTime(
                                $dateCreated->getHour(), 
                                $dateCreated->getMinute(), 
                                $dateCreated->getSecond()
                            )
                            ->format('Y-m-d H:i:s');
                    } else {
                        // Convert to standard database format
                        $postedData['date_expiry'] = $expirationDate->format('Y-m-d H:i:s');
                    }
                }
                else {
                    $response = [
                        'result'     => 'error',
                        'message'    => lang('Notifications.incorrectDateFormatEditLicense'),
                        'error_code' => KEY_UPDATE_FAILED
                    ];
                    
                    return $this->respondCreated($response);
                }

                // Automatically set billing length and interval if lifetime
                if($postedData['license_type'] === 'lifetime') {
                    $postedData['billing_length'] = '';
                    $postedData['billing_interval'] = 'onetime';
                    $postedData['date_expiry']  = NULL;
                    if($postedData['item_reference'] === '' || $postedData['item_reference'] === NULL) {
                        $postedData['item_reference'] = $postedData['product_ref'];
                    }                         
                }

                // Do not allow to modify the date_created & date_activated data
                if (isset($postedData['date_created'])) {
                    unset($postedData['date_created']);
                }
                if (isset($postedData['date_activated'])) {
                    unset($postedData['date_activated']);
                }                    

                // Individual validation
                $individualLicenseParamValidations = individualLicenseParamValidations($postedData, $this->userID);

                if($individualLicenseParamValidations !== true) {
                    // returned error upon validation

                    $errorDetails = json_decode($individualLicenseParamValidations, true);
                    
                    if($errorDetails['msg'] === lang('Notifications.error_not_in_product_list')) {
                        $response = [
                            'result'     => 'error',
                            'message'    => 'License detail update failed. Value of \'product_ref\' is not in the product list.',
                            'error_code' => KEY_UPDATE_FAILED
                        ];
                    }
                    else if($errorDetails['msg'] === lang('Notifications.error_no_billing_length')) {
                        $response = [
                            'result'     => 'error',
                            'message'    => 'License detail update failed. Specify license \'billing_length\'.',
                            'error_code' => KEY_UPDATE_FAILED
                        ];
                    }
                    else if($errorDetails['msg'] === lang('Notifications.error_no_billing_interval')) {
                        $response = [
                            'result'     => 'error',
                            'message'    => 'License detail update failed. Specify license \'billing_interval\'.',
                            'error_code' => KEY_UPDATE_FAILED
                        ];
                    }
                    else if($errorDetails['msg'] === lang('Notifications.exp_date_required_subscription_type')) {
                        $response = [
                            'result'     => 'error',
                            'message'    => 'License detail update failed. Specify license \'date_expiry\'.',
                            'error_code' => KEY_UPDATE_FAILED
                        ];
                    }
                    else if($errorDetails['msg'] === lang('Notifications.exp_date_required_trial_type')) {
                        $response = [
                            'result'     => 'error',
                            'message'    => 'License detail update failed. Specify license length \'date_expiry\'.',
                            'error_code' => KEY_UPDATE_FAILED
                        ];
                    }
                    else if($errorDetails['msg'] === lang('Notifications.required_fields_missing')) {
                        $response = [
                            'result'     => 'error',
                            'message'    => 'License detail update failed. Please complete the required parameters',
                            'error_code' => KEY_UPDATE_FAILED
                        ];
                    }
                    else {
                        $response = [
                            'result'     => 'error',
                            'message'    => 'License detail update failed due to some unknown error on validation.',
                            'error_code' => KEY_UPDATE_FAILED
                        ];
                    }

                    return $this->respondCreated($response);
                }
        
                // Validation rules & messages
                $validationRules = [
                    'license_key' => 'required',
                    'max_allowed_domains' => 'required|numeric',
                    'max_allowed_devices' => 'required|numeric',
                    'license_status' => 'required|in_list[pending,active,blocked,expired]',
                    'license_type' => 'required|in_list[trial,subscription,lifetime]',
                    'first_name' => 'required|regex_match[/^[\p{L}\p{M}\s.-]+$/u]',
                    'last_name' => 'required|regex_match[/^[\p{L}\p{M}\s.-]+$/u]',
                    'email' => 'required|valid_email',
                    'purchase_id_' => 'required|alpha_numeric_punct',
                    'txn_id' => 'required|alpha_numeric_punct',
                    'product_ref' => 'required|alpha_numeric_punct',
                ];

                $validationMessages = [
                    'license_key' => [
                        'required'              => 'The license key field is required.',
                    ],
                    'max_allowed_domains' => [
                        'required'              => 'The max allowed domains field is required.',
                        'numeric'                   => 'Please enter a numeric value in the max allowed domains field.',
                    ],
                    'max_allowed_devices' => [
                        'required'              => 'The max allowed devices field is required.',
                        'numeric'                   => 'Please enter a numeric value in the max allowed devices field.',
                    ],
                    'license_status' => [
                        'required'              => 'The license status field selection is required.',
                    ],
                    'license_type' => [
                        'required'              => 'The license type field selection is required.',
                    ],
                    'first_name' => [
                        'required'              => 'The first name field is required.',
                        'regex_match'           => 'The first name field may only contain letters, spaces, periods, and hyphens.'
                    ],
                    'last_name' => [
                        'required'              => 'The last name field is required.',
                        'regex_match'           => 'The last name field may only contain letters, spaces, periods, and hyphens.'
                    ],
                    'email' => [
                        'required'              => 'The email field is required.',
                        'valid_email'           => 'Please provide a valid email address.',
                    ],
                    'purchase_id_' => [
                        'required'              => 'The purchase ID field is required.',
                        'alpha_numeric_punct'   => 'The purchase ID field should only contains alphanumeric characters.',
                    ],
                    'txn_id' => [
                        'required'              => 'The transaction ID field is required.',
                        'alpha_numeric_punct'   => 'The transaction ID field should only contains alphanumeric characters.',
                    ],
                    'product_ref' => [
                        'required'              => 'The product reference field is required.',
                        'alpha_numeric_punct'   => 'The product reference field should only contains alphanumeric characters.',
                    ],
                ];      

                // Run validation
                if (!$this->validate($validationRules, $validationMessages)) {
                    $errors = $this->validator->getErrors();
                    $response = [
                        'result'     => 'error',
                        'message'    => $errors,
                        'error_code' => KEY_UPDATE_FAILED
                    ];

                    return $this->respondCreated($response);
                } else {
                    try {
                        $return = $this->LicensesModel->where('owner_id', $this->userID)
                                                        ->update($targetLicense['id'], $postedData);
                        
                        if ($return) {
                            // Check if needed to update WooCommerce
                            checkToUpdateWooCommerce($postedData['license_key']);
                            
                            // Check if the email in the record has changed
                            $previousEmail = $this->LicenseEmailListModel->where('owner_id', $this->userID)
                                                                        ->where('license_key', $postedData['license_key'])
                                                                        ->first();
                            $postedEmail = $postedData['email'];

                            $emailListData = [
                                'owner_id' => $this->userID,
                                'license_key' => $postedData['license_key'],
                                'sent_to' => $postedData['email'],
                                'status' => 'success',
                                'sent' => 'yes',
                                'date_sent' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                            ];

                            if (empty($previousEmail) || $previousEmail['sent_to'] !== $postedEmail) {

                                $updateEmailList = $this->LicenseEmailListModel->insertOrUpdate($emailListData);

                                try {
                                    if ($updateEmailList) {
                                        // Log the activity
                                        licenseManagerLogger($postedData['license_key'], 'update: License details updated', 'yes');

                                        // Prepare success response
                                        $response = [
                                            'result' => 'success',
                                            'message' => 'License details successfully updated.',
                                            'key' => $postedData['license_key'],
                                            'code' => KEY_UPDATE_SUCCESS
                                        ];                                            
                                    }
                                } catch (\Exception $e) {
                                    // Log the activity
                                    licenseManagerLogger($postedData['license_key'], 'update: License details updated but encountered error in updating the email list: ' . $e->getMessage(), 'no');
                    
                                    // Prepare success response with partial error
                                    $response = [
                                        'result' => 'success',
                                        'message' => 'License details updated but encountered error in updating the email list.',
                                        'key' => $postedData['license_key'],
                                        'code' => KEY_UPDATE_SUCCESS
                                    ];                                            
                                }
                                
                            }
                            else {
                                // Log the activity
                                licenseManagerLogger($postedData['license_key'], 'update: License details updated', 'yes');
                        
                                // Prepare success response
                                $response = [
                                    'result' => 'success',
                                    'message' => 'License details successfully updated.',
                                    'key' => $postedData['license_key'],
                                    'code' => KEY_UPDATE_SUCCESS
                                ];
                            }

                        } else {
                            // Log the activity
                            licenseManagerLogger($postedData['license_key'], 'update: License details update failed due to server error', 'no');

                            // Prepare error response for failed insertion
                            $response = [
                                'result' => 'error',
                                'message' => 'License details update failed due to server error.',
                                'error_code' => KEY_UPDATE_FAILED
                            ];
                        }
                    }
                    catch (\Throwable $e) {
                        // Handle errors
                        log_message('error', '[API] License detail update failed: ' . $e->getMessage());
                    
                        // Log the activity
                        licenseManagerLogger($postedData['license_key'], 'update: License details update failed: ' . $e->getMessage(), 'no');

                        // Handle exceptions
                        $response = [
                            'result' => 'error',
                            'message' => 'License details update failed: ' . $e->getMessage(),
                            'error_code' => KEY_UPDATE_FAILED
                        ];
                    }
                    
                    return $this->respondCreated($response);
                }
            }
            else {
                // Lincense not found
                $response = [
                    'result'     => 'error',
                    'message'    => 'License key not found',
                    'error_code' => KEY_UPDATE_FAILED
                ];
                
                return $this->respondCreated($response);
            }
        } catch (\Exception $e) {
            // Error upon searching the license in the DB
            $response = [
                'result'     => 'error',
                'message'    => 'Encountered a problem upon updating the license details. Result: ' . $e->getMessage(),
                'error_code' => KEY_UPDATE_FAILED
            ];
            
            return $this->respondCreated($response);            
        }
    }

            /**************************************************************
            * ***************** API for Super Admin ONLY ******************
            * ************************************************************/

    /**
     * Action: all
     * URI: /api/user/all/{secret_key}
     * Method: GET
     * Description: Retrieve the list of all users.
     * Requirements: Admin authority only
     * {secret_key} (required): A secret key for authorization.
     * @return mixed The response containing the list of users or an error message.
    */
    public function listUsers($secretKey)
    {
        $authResult = $this->authorizeSecretKey('manage', $secretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }

		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		// Prepare the User data
		$userListData = [];
		// $userListBaseData = $this->UserModel->orderBy('id', 'DESC')->findAll();
        $userListBaseData = $this->UserModel->withDeleted()->orderBy('id', 'DESC')->findAll();
		$userLoginModel = model('LoginModel');

		foreach($userListBaseData as $user) {
			$lastLoginDetails = $userLoginModel->previousLogin($user);
            $latestSubscription = $this->SubscriptionModel->where('user_id', $user->id)
													->orderBy('created_at', 'DESC')
													->first();

			$package = $latestSubscription ? $this->PackageModel->find($latestSubscription['package_id']) : null;

			$userListData[$user->id] = [
				'avatar' => $user->avatar,
				'username' => $user->username,    
				'email' => $user->email,
				'registered' => $user->created_at,
				'package' => $package ? $package['package_name'] : 'No Package',
				'status' => $latestSubscription ? $latestSubscription['subscription_status'] : 'Inactive',
				'package_expiry' => $latestSubscription ? $latestSubscription['end_date'] : 'N/A',
				'last_login' => $lastLoginDetails ? $lastLoginDetails->date : 'Never',
				'last_ip' => $lastLoginDetails ? $lastLoginDetails->ip_address : 'N/A',
                'deleted_at' => $user->deleted_at,
			];
		}

        return $this->respondCreated($userListData);
    }

    /**
     * Action: all
     * URI: /api/package/all/{secret_key}
     * Method: GET
     * Description: Retrieve all packages.
     * Requirements: Admin authority only
     * {secret_key} (required): A secret key for authorization.
     * @return mixed The response containing the list of users or an error message.
    */
    public function listPackages($secretKey)
    {
        $authResult = $this->authorizeSecretKey('manage', $secretKey);
        if ($authResult !== true) {
            return $authResult; // Return the unauthorized response
        }

		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		// Prepare the User data
		$packageListData = $this->PackageModel->orderBy('sort_order', 'ASC')->findAll();

        return $this->respondCreated($packageListData);
    }     
}