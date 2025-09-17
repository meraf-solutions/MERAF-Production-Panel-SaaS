<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use CodeIgniter\Controller;
use App\Models\LicenseLogsModel;
use App\Models\ModuleCategoryModel;
use App\Models\PackageModel;
use App\Models\PackageModulesModel;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPaymentModel;
use App\Models\UserModel;
use App\Models\UserSettingsModel;
use App\Libraries\SubscriptionChecker;
use ZipArchive;

class Home extends BaseController
{
	protected $userAcctDetails;
	protected $userID;
	protected $userDataPath;
	protected $adminSettings;
	protected $myConfig;
	protected $sideBarMenu;
	protected $UserSettingsModel;
	protected $UserModel;
	protected $LicenseLogsModel;
	protected $ModuleCategoryModel;
	protected $PackageModulesModel;
	protected $PackageModel;
	protected $SubscriptionModel;
	protected $PaymentModel;
	protected $PaymentMethods;
	protected $db;
	protected $subscriptionChecker;

    public function __construct()
    {
		initLicenseManager();		

		// Get user account details
		$this->userAcctDetails = auth()->user();

        // Get the current user's ID
        $this->userID = $this->userAcctDetails->id ?? NULL;

		$this->userDataPath = $this->userID ? USER_DATA_PATH . $this->userID . DIRECTORY_SEPARATOR : NULL;

        // Use the updated getMyConfig function with the user's ID
        $this->myConfig = getMyConfig('', $this->userID);

		// Get Admin's settings
		$this->adminSettings = getMyConfig('', 0);

		// Get the installed payment methods
		$this->PaymentMethods = loadModuleMenu();

		// Get the dynamic sidebar menu
		$this->sideBarMenu = [
			'products' => $this->userID ? productList($this->userID) : [],
			'payment_methods' => $this->PaymentMethods
		];
        
        // Set the timezone
        setMyTimezone();

        // Set the locale dynamically based on user preference
        setMyLocale();

		// Initialize models
		$this->subscriptionChecker = new SubscriptionChecker();
		$this->db = \Config\Database::connect();
		$this->LicenseLogsModel = new LicenseLogsModel();
		$this->ModuleCategoryModel = new ModuleCategoryModel();
		$this->PackageModel = new PackageModel();
		$this->PackageModulesModel = new PackageModulesModel();
		$this->PaymentModel = new SubscriptionPaymentModel();
		$this->UserModel = new UserModel();
		$this->UserSettingsModel = new UserSettingsModel();
		$this->SubscriptionModel = new SubscriptionModel();
		
		if($this->myConfig['appVersion'] === NULL) {
			$this->checkAndUpdateVersionJson();
			header("Location:" . base_url('reinstall-production-panel/force-update?returnUrl='.current_url()));
			exit();
		}
    }

	public function setTimezone()
	{
		if ($this->request->isAJAX()) {
			$timezone = $this->request->getJSON()->timezone;
			
			// Validate timezone
			if (in_array($timezone, timezone_identifiers_list())) {
				$session = session();
				
				// Check if session already exists
				if (!$session->has('detected_timezone')) {
					$session->set('detected_timezone', $timezone);
				}
				
				return $this->response->setJSON(['status' => 'success']);
			}
		}
		
		return $this->response->setJSON(['status' => 'error']);
	}

    protected function checkIfLoggedIn()
    {
        $auth = auth();
    
        if (! $auth->loggedIn()) {
            return redirect()->to('login')->with('error', lang('Pages.Please_log_in'));
        }
    
        $user = $auth->user();
    
        // If somehow user info is missing, force logout and redirect
        if (! $user) {
            $auth->logout(); // <-- Important to clean session
            return redirect()->to('login')->with('error', lang('Pages.Must_login_to_continue'));
        }
    
        // Now safe: user is fully authenticated
    
        if (! $user->api_key) {
            $this->UserModel->generateUserApiKey($user->id); // Use $user->id, not $this->userID
            $this->UserModel->assignAdminRole();
        }
    }

	protected function lastLoginHistory()
	{
		$userDataModel = model('LoginModel');
		$data = $userDataModel->lastLogin(auth()->user()) ?? null;

		if($this->userID === 1) {
			if(empty($data)) {
				// app newly installed and first time to login, now disable registration as default
				// disableRegistration();

				// Clear the cache
				clearCache();				
			}

			$installerPath = ROOTPATH . 'public/install';
			if(file_exists($installerPath)) {
				rename($installerPath, strtolower(generateLicenseKey('install-', '', 5)));

				// Clear the cache
				clearCache();				
			}
		}

		return $data;
	}
	
    public function dashboard()
    {
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		$this->checkAndUpdateVersionJson(); // Routine check of latest app version

		$data['pageTitle'] = lang('Pages.index_title', ['appName' => $this->myConfig['appName']]);
		$data['section'] = 'home';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;

		$currentDate = Time::now();
		$startOfWeek = $currentDate->modify('Monday this week')->setTime(0, 0, 0);
		$endOfWeek = $currentDate->modify('Sunday this week')->setTime(23, 59, 59);
		$startOfWeek = $startOfWeek->format('Y-m-d H:i:s');
		$endOfWeek = $endOfWeek->format('Y-m-d H:i:s');
		
		$data['currentDate'] = $currentDate;
		$data['threeDaysLater'] = $currentDate->addDays(3);
		$data['startOfWeek'] = $startOfWeek;
		$data['endOfWeek'] = $endOfWeek;
		
		return view('dashboard', $data);
    }

    public function app_registration()
    {
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if(!$this->userAcctDetails->inGroup('admin')) {
			header("Location:" . base_url('unavailable'));
			exit();
		}

		$this->checkAndUpdateVersionJson(); // Routine check of latest app version

		$licenseFile = USER_DATA_PATH . 'license.txt';

		$registeredLicense = [];
		if(file_exists($licenseFile)) {
			$registeredLicense = file_get_contents($licenseFile);
			$registeredLicense = function_exists('readLicense') ? json_decode(readLicense($registeredLicense), true) : [];
		}

		$data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Setup') . ' | ' . lang('Pages.app_registration');
		$data['section'] = 'Setup';
		$data['subsection'] = 'app_registration';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		$data['registeredLicense'] = $registeredLicense;
		
		return view('dashboard/app-setup/app_registration', $data);
    }

	public function app_registration_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged in before proceeding
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}
	
		// Validate form data
		$validationRules = [
			'purchasedLicenseKey' => 'required',
		];
	
		$validationMessages = [
			'purchasedLicenseKey' => [
				'required' => lang('Pages.please_select_product_feedback')
			],
		];
	
		if (!$this->validate($validationRules, $validationMessages)) {
			$errors = $this->validator->getErrors();

			return $this->response->setJSON([
				'success' => false,
				'status'  => 0,
				'msg'     => $errors,
			]);
		}
	
		$purchasedLicenseKey = trim($this->request->getPost('purchasedLicenseKey'));
	
		$host = getHostFromCurrentUrl();
		$apiUrl = "https://prod.merafsolutions.com/api/license/register/domain/{$host}/jYXqBGUDHk4x5d1YISDu/{$purchasedLicenseKey}";
	
		try {
			$response = makeApiCall($apiUrl);
			$responseData = json_decode($response->getBody(), true);
	
			if ($responseData['result'] === 'error') {
				return $this->response->setJSON([
					'success' => false,
					'status'  => 0,
					'msg' => $responseData['message'],
				]);
			}
	
			$verificationUrl = "https://prod.merafsolutions.com/api/license/verify/Aly1XiEivaoYhQsbdE/{$purchasedLicenseKey}";
			$verificationResponse = makeApiCall($verificationUrl);
			$verificationData = json_decode($verificationResponse->getBody(), true);
			
			// Return if license is not active
			if($verificationData['status'] !== 'active' ) {
				log_message('error', '[Home] The entered license key is ' . $verificationData['status']);
			    return $this->response->setJSON([
					'success' => false,
					'status'  => 0,
					'msg' => 'The entered license key is ' . $verificationData['status'],
				]);
			}
			
			// Return if product is not matching
			if($verificationData['product_ref'] !== 'MERAF Production Panel SaaS' ) {
				log_message('error', '[Home] The entered license key is invalid for this app');
			    return $this->response->setJSON([
					'success' => false,
					'status'  => 0,
					'msg' => 'The entered license key is invalid for this app',
				]);
			}
			
			// License validated and write the result
			$dataToWrite = writeJSONResponse(json_encode($verificationData, true));
    	
    		if (file_put_contents(USER_DATA_PATH . 'license.txt', $dataToWrite)) {
    			return $this->response->setJSON([
    					'success' => true,
    					'status'  => 1,
    					'msg'     => $responseData['message'],
    				]);
    		} else {
    			throw new \Exception('Unable to apply the activation in the app!');
    			return $this->response->setJSON([
					'success' => false,
					'status'  => 0,
					'msg' => 'Unable to apply the activation in the app!',
				]);
    		}
		} catch (\Exception $e) {
			return $this->response->setJSON([
				'success' => false,
				'status'  => 0,
				'msg'     => $e->getMessage(),
			]);
		}
	}

	public function app_unregister_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged in before proceeding
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}
	
		// Validate form data
		$validationRules = [
			'purchasedLicenseKey' => 'required',
		];
	
		$validationMessages = [
			'purchasedLicenseKey' => [
				'required' => lang('Pages.please_select_product_feedback')
			],
		];
	
		if (!$this->validate($validationRules, $validationMessages)) {
			$errors = $this->validator->getErrors();
			return $this->response->setJSON([
				'success' => false,
				'status'  => 0,
				'msg'     => $errors,
			]);
		}
	
		$purchasedLicenseKey = trim($this->request->getPost('purchasedLicenseKey'));
	
		$host = getHostFromCurrentUrl();
		$apiUrl = "https://prod.merafsolutions.com/api/license/unregister/domain/{$host}/jYXqBGUDHk4x5d1YISDu/{$purchasedLicenseKey}";
	
		try {
			$response = makeApiCall($apiUrl);
			$responseData = json_decode($response->getBody(), true);
	
			if ($responseData['result'] === 'error') {
				return $this->response->setJSON([
					'success' => false,
					'status'  => 0,
					'msg' => $responseData['message'],
				]);
			}
	
			$licenseFilePath = USER_DATA_PATH . 'license.txt';

			if (file_exists($licenseFilePath)) {
				unlink($licenseFilePath);
			}
	
			return $this->response->setJSON([
				'success' => true,
				'status'  => 1,
				'msg'     => lang('Notifications.successful_deactivation_app'),
			]);
	
		} catch (\Exception $e) {
			return $this->response->setJSON([
				'success' => false,
				'status'  => 0,
				'msg'     => $e->getMessage(),
			]);
		}
	}

	private function maybeUnserialize($data) {
		// Check if the data is serialized
		if (is_string($data) && @unserialize($data) !== false) {
			return unserialize($data);
		}
		return $data;
	}

	public function product_changelog($product = '')
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
		
		$pageTitle = lang('Pages.Product_Changelog');
		$pageTitle = $product ? $pageTitle . ' | ' . $product : $pageTitle;

		$data['pageTitle'] = $pageTitle;
		$data['section'] = 'product_changelog';
		$data['subsection'] = $product;
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;	
		$data['productDetails'] = productDetails(NULL, $this->userID);
		$data['productFiles'] = getProductFiles('', $this->userID);
		$data['subscriptionChecker'] = $this->subscriptionChecker;
	
		return view('dashboard/products/product_changelog', $data);
	}

	public function product_guide($product = '')
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
		$toUpdate = $this->request->getGet('s') ?? '';

		$pageTitle = lang('Pages.Product_manager') . ($toUpdate ? ' | ' . $toUpdate : '');

		$data['pageTitle'] = $pageTitle;
		$data['section'] = 'Product_manager';
		$data['subsection'] = 'Getting_Started_Guide';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;	
		$data['productDetails'] = productDetails(NULL, $this->userID);
		$data['productFiles'] = getProductFiles('', $this->userID);
		$data['toUpdate'] = $toUpdate;
	
		return view('dashboard/products/product_guide', $data);
	}

	public function product_guide_update_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
		
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}        
	
		// Validate form data
		$validationRules = [
			'productName'                  => 'required',
		];
		
		$validationMessages = [
			'productName' => [
				'required' => lang('Pages.please_select_product_feedback')
			]
		];
		
		if (!$this->validate($validationRules)) {
			$errors = $this->validator->getErrors();
			$response = [
				'success' => false,
				'status'  => 0,
				'msg'     => $errors,
			];
			return $this->response->setJSON($response);
		} else {
			// Form data is valid, proceed with further processing
			
			// Submitted data
			$productName = trim($this->request->getPost('productName'));
			$productGuide = $this->request->getPost('productGuideTextarea');
			
			// Get the user's product list
			$productList = productList($this->userID);
			
			// Return if the submitted product is not in the list
			if (!in_array($productName, $productList)) {
				return $this->response->setJSON([
					'success' => false,
					'msg'     => lang('Notifications.error_not_existing_in_the_product_list', ['productName' => $productName]),
				]);
			}
			
			$productGuideFile = $this->userDataPath . $this->myConfig['userProductPath'] . sha1($productName, false) . '.txt';
			
			try {
				// Create directory if it doesn't exist
				$directory = dirname($productGuideFile);
				if (!is_dir($directory)) {
					if (!mkdir($directory, 0755, true)) {
						throw new \Exception(lang('Notifications.error_creating_directory'));
					}
				}
	
				// Sanitize the content
				$sanitizedGuide = htmlspecialchars($productGuide, ENT_QUOTES, 'UTF-8');
				
				// Write the file with proper error checking
				if (file_put_contents($productGuideFile, $sanitizedGuide) === false) {
					return $this->response->setJSON([
						'success' => false,
						'msg'     => lang('Notifications.error_saving_file'),
					]);
				}
	
				// Set proper file permissions
				chmod($productGuideFile, 0644);
	
				return $this->response->setJSON([
					'success' => true,
					'msg'     => lang('Notifications.success_product_updated', ['productName' => $productName]),
				]);
			} catch (\Exception $e) {
				return $this->response->setJSON([
					'success' => false,
					'msg'     => $e->getMessage(),
				]);
			}
		}
	}
	
	public function create_license($page='')
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		$data['pageTitle'] = lang('Pages.License_Manager') . ' | ' . lang('Pages.Create_license');
		$data['section'] = 'License_Manager';
		$data['subsection'] = 'Create_license';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		$data['productVariations'] = $this->productVariations();
		
		return view('dashboard/license/create_license', $data);
	}

	public function manage_licenses()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		$data['firstNumber'] = mt_rand(10, 99);
		$data['secondNumber'] = mt_rand(0, 9);
		$data['hash'] = sha1($data['firstNumber'] + $data['secondNumber']); 
		$data['captcha'] = $data['firstNumber'] + $data['secondNumber']; 
		
        $data = array_merge($data, [
			'pageTitle' => lang('Pages.License_Manager') . ' | ' . lang('Pages.Manage_licenses'),
			'section' => 'License_Manager',
			'subsection' => 'Manage_licenses',
			'sideBarMenu' => $this->sideBarMenu,
			'userData' => $this->userAcctDetails,
			'lastLoginHistory' => json_encode($this->lastLoginHistory()),
			'myConfig' => $this->myConfig,
        ]);	

		return view('dashboard/license/manage', $data);
	}
	
	public function license_acitivty_logs()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
		
        $data = [
			'pageTitle' => lang('Pages.License_Manager') . ' | ' . lang('Pages.Activity_Logs'),
			'section' => 'License_Manager',
			'subsection' => 'Activity_Logs',
			'sideBarMenu' => $this->sideBarMenu,
			'userData' => $this->userAcctDetails,
			'lastLoginHistory' => json_encode($this->lastLoginHistory()),
			'myConfig' => $this->myConfig,
        ];

		return view('dashboard/license/all_logs', $data);
	}
	
	public function zipDirectoryRecursive($dir, $relativePath, $zip, $excludedItems)
	{
		$files = scandir($dir);
		foreach ($files as $file) {
			if ($file === '.' || $file === '..') {
				continue; // Skip . and ..
			}
	
			$filePath = $dir . '/' . $file;
			$realPath = realpath($filePath); // Get the full path of the file/folder
	
			if ($realPath && in_array($realPath, $excludedItems)) {
				continue; // Skip excluded items based on full path
			}
	
			$relativeFilePath = $relativePath . '/' . $file;
			if (is_file($filePath)) {
				$zip->addFile($filePath, $relativeFilePath); // Add file to zip with relative path
			} elseif (is_dir($filePath)) {
				$this->zipDirectoryRecursive($filePath, $relativeFilePath, $zip, $excludedItems); // Recursive call for subdirectory
			}
		}
	}	

	public function subscribers()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
		
        $data = [
			'pageTitle' => lang('Pages.License_Manager') . ' | ' . lang('Pages.Subscribers'),
			'section' => 'License_Manager',
			'subsection' => 'Subscribers',
			'sideBarMenu' => $this->sideBarMenu,
			'userData' => $this->userAcctDetails,
			'lastLoginHistory' => json_encode($this->lastLoginHistory()),
			'myConfig' => $this->myConfig,
        ];

		return view('dashboard/license/subscribers', $data);
	}
	
	public function resend_license()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		$data['pageTitle'] = lang('Pages.License_Manager') . ' | ' . lang('Pages.resend_license_details');		
		$data['section'] = 'License_Manager';
		$data['subsection'] = 'resend_license';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;	
		
		return view('dashboard/license/resend_license', $data);
	}

	public function reset_license()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		$data['pageTitle'] = lang('Pages.License_Manager') . ' | ' . lang('Pages.Reset_license');	
		$data['section'] = 'License_Manager';
		$data['subsection'] = 'reset_license';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['firstNumber'] = mt_rand(10, 99);
		$data['secondNumber'] = mt_rand(0, 9);
		$data['hash'] = sha1($data['firstNumber'] + $data['secondNumber']); 
		
		return view('dashboard/license/reset_license', $data);
	}
	
	public function reset_license_public()
	{
		$data['pageTitle'] = lang('Pages.reset_license_title', ['appName' => $this->myConfig['appName']]);
		$data['firstNumber'] = mt_rand(10, 99);
		$data['secondNumber'] = mt_rand(0, 9);
		$data['hash'] = sha1($data['firstNumber'] + $data['secondNumber']); 
		
		return view('dashboard/license/reset_license_public', $data);
	}

	public function loadRows($logType,$offset, $limit, $licenseKey='')
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if($logType === 'error-logs') {
			$csvFilePath = $this->userDataPath .  $this->myConfig['userLogsPath'] . $this->myConfig['License_Invalid_Log_FileName'];
		}
		else if($logType === 'success-logs') {
			$csvFilePath = $this->userDataPath .  $this->myConfig['userLogsPath'] . $this->myConfig['License_Valid_Log_FileName'];
		}
		else if($logType === 'license-logs') {
			// Define the where clause for filtering licenses
			$where = "license_key = '".$licenseKey."'";

			// Fetch the list of licenses based on the where clause, order by id in descending order, with offset and limit
			$listLicenseLogs = $this->LicenseLogsModel->where($where)
												->orderBy('time', 'DESC')
												->findAll($limit, $offset);
			
			return json_encode($listLicenseLogs);
		}
		else {
			$csvFilePath = '';
		}

		if (!file_exists($csvFilePath)) {
			$data['pageTitle'] = lang('Pages.log_file_not_found_title', ['appName' => $this->myConfig['appName']]);
			$data['section'] = $logType.'_logs';
			$data['sideBarMenu'] = $this->sideBarMenu;
			$data['userData'] = $this->userAcctDetails;
			$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
			$data['myConfig'] = $this->myConfig;
			$data['userEmail'] = $this->userAcctDetails->getEmail();
			$data['sideBarMenu'] = $this->sideBarMenu;

			$data['error_message'] = lang('Notifications.log_file_not_found');
			return view('dashboard/logs/log_page', $data);
		}

		$csvFile = fopen($csvFilePath, 'r');

		// Skip the first row (header)
		fgetcsv($csvFile);

		$rows = [];

		// Read the CSV file and store rows in an array
		while (($rowData = fgetcsv($csvFile)) !== false) {
			$rows[] = $rowData;
		}

		// Sort by the first column (TIME) in descending order
		usort($rows, function ($a, $b) {
			return strtotime($b[0]) - strtotime($a[0]); // Assuming the first column is "TIME"
		});

		fclose($csvFile);

		// Slice the array to get only the specified range
		$rows = array_slice($rows, $offset, $limit, true);

		// Return the partial view as HTML
		return view('dashboard/logs/partial_log_page', ['rows' => $rows]);
	}

	public function log_page($logType)
	{
		$this->checkIfLoggedIn(); // Check if user is logged in before proceeding
	
		$data['pageTitle'] = lang('Pages.log_page_title', ['logName' => ucwords(str_replace('_', ' ', $logType))]);
		$data['section'] = $logType.'_logs';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		$data['sideBarMenu'] = $this->sideBarMenu;
	
		// Determine CSV file path based on log type
		$csvFilePath = NULL;
		if($logType === 'error') {
			$csvFilePath = $this->userDataPath .  $this->myConfig['userLogsPath'] . $this->myConfig['License_Invalid_Log_FileName'];
		} elseif($logType === 'success') {
			$csvFilePath = $this->userDataPath .  $this->myConfig['userLogsPath'] . $this->myConfig['License_Valid_Log_FileName'];
		}   
	
		// Check if CSV file exists
		if (!file_exists($csvFilePath)) {
			$data['error_message'] = lang('Notifications.log_file_not_found');
			$data['logContent'] = NULL;
		} else {
			$data['error_message'] = null;

			// Open CSV file for reading
			$csvFile = fopen($csvFilePath, 'r');
		
			// Skip the first row (header)
			fgetcsv($csvFile);
		
			// Read CSV file content
			$mainContent = [];
			$counter = 1;
			while (($rowData = fgetcsv($csvFile)) !== false) {
				$mainContent[$counter] = $rowData;
				$counter++;
			}
		
			// Sort by the first column (TIME) in descending order
			usort($mainContent, function ($a, $b) {
				return strtotime($b[0]) - strtotime($a[0]); // Assuming the first column is "TIME"
			});
            
            $data['logContent'] = $mainContent;
		
			// Close CSV file
			fclose($csvFile);			
		}
	
		return view('dashboard/logs/log_page', $data);
	}
	
	public function product_changelog_update_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}		
	
		// Set the response messages
		$msgResponse_validationError 		= lang('Notifications.error_submitted_details');
		$msgResponse_jsonError 				= lang('Notifications.error_decoding_json_response');

		// Validate form data
		$validationRules = [
			'productName'      			=> 'required',
			'productVersion'      		=> 'required',
			'productFile'      			=> 'required',
			'productChangelog'      	=> 'required',
		];

		if (!$this->validate($validationRules)) {
			$response = [
				'success' => false,
				'status'  => 0,
				'msg'     => $msgResponse_validationError,
			];

			return $this->response->setJSON($response);
		} else {
			// Form data is valid, proceed with further processing
			$data = [
				'productName'    	=> trim($this->request->getPost('productName')),
				'productVersion'    => trim($this->request->getPost('productVersion')),
				'productFile'  		=> trim($this->request->getPost('productFile')),
				'productChangelog'  => trim($this->request->getPost('productChangelog')),
			];
			
			$response = $this->updateChangelog(
				$data['productName'],
				$data['productVersion'],
				$data['productFile'],
				$data['productChangelog']
			);

			$isEnvatoSyncEnabled = $this->subscriptionChecker->isFeatureEnabled($this->userID, 'Envato_Sync');

			// Process the envato item code if enabled and entered value
			$envatoItemCodes = [];
			if ($isEnvatoSyncEnabled && $this->myConfig['userEnvatoSyncEnabled'])  {
				// Get the current settings - make sure we're using the exact same setting key
				$envatoItemCodes = json_decode($this->myConfig['userEnvatoItemCodes'], true) ?: [];

				// Encode the product name
				$encodedProductName = sha1($data['productName']);

				// Get the item code from the form, even if empty
				$envatoItemCode = trim($this->request->getPost('EnvatoItemCode') ?? '');

				// Update only this specific product's code, preserving all others
				$envatoItemCodes[$encodedProductName] = $envatoItemCode;
			}

			// Save back the settings with all keys preserved
			$this->UserSettingsModel->setUserSetting('userEnvatoItemCodes', json_encode($envatoItemCodes), $this->userID);

			// Check if the response is a JSON string
			if (json_decode($response) === null && json_last_error() !== JSON_ERROR_NONE) {
				// Handle JSON decoding error
				$response = [
					'success' => false,
					'status'  => 0,
					'msg'     => $msgResponse_jsonError,
				];
			} else {
				$response = json_decode($response, true);
			}

			return $this->response->setJSON($response);
		}
	}

	public function downloadLogs($download)
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($download == 'error-logs') {
			$filename = $this->myConfig['License_Invalid_Log_FileName'];
			$filepath = $this->userDataPath .  $this->myConfig['userLogsPath'] . $filename;
		} elseif ($download == 'success-logs') {
			$filename = $this->myConfig['License_Valid_Log_FileName'];
			$filepath = $this->userDataPath .  $this->myConfig['userLogsPath'] . $filename;
		} else {
			return $this->response->setStatusCode(404)->setJSON(['error' => lang('Notifications.invalid_download_type')]);
		}

		if (file_exists($filepath)) {
			$fileContent = file_get_contents($filepath);

			// Extract file extension from the filename
			$fileExtension = pathinfo($filename, PATHINFO_EXTENSION);

			// Create an associative array with filename and file format
			$response = [
				'fileName' => $download . '_' . Time::now()->format('Y-m-d_His'),
				'fileFormat' => $fileExtension,
				'fileContent' => base64_encode($fileContent) // Encode file content as base64
			];

			return $this->response->setJSON($response);
		} else {
			return $this->response->setStatusCode(404)->setJSON(['error' => lang('Notifications.file_not_found')]);
		}
	}

	public function deleteLogs($delete)
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
	
		$filePath = ''; // Corrected variable name from $filepath to $filePath
	
		if ($delete == 'error-logs') {
			$filename = $this->myConfig['License_Invalid_Log_FileName'];
			$filePath = $this->userDataPath .  $this->myConfig['userLogsPath'] . $filename;
		} elseif ($delete == 'success-logs') {
			$filename = $this->myConfig['License_Valid_Log_FileName'];
			$filePath = $this->userDataPath .  $this->myConfig['userLogsPath'] . $filename;
		} else {
			return $this->response->setStatusCode(404)->setJSON(['error' => lang('Notifications.invalid_log_type')]);
		}
	
		if (file_exists($filePath)) {
			// Attempt to delete the file
			if (unlink($filePath)) {
				$response = [
					'success' => true,
					'status'  => 1,
					'msg'     => lang('Notifications.success_log_deleted'),
				];		
			} else {
				log_message('error', "[Home] Failed to delete file: $filename"); // Log deletion failure
				$response = [
					'success' => false,
					'status'  => 0,
					'msg'     => lang('Notifications.error_file_deletion', ['fileName' => $filename]),
				];
			}
			
		} else {
			log_message('error', "[Home] File does not exist: $filename"); // Log file not found
			$response = [
				'success' => false,
				'status'  => 0,
				'msg'     => lang('Notifications.error_file_not_found', ['fileName' => $filename]),
			];
		}

		return json_encode($response);
	}	
	
	protected function compareVersions($installedVersion, $currentVersion, $productDetails)
	{
		$response = [
			'newVersion' => false,
			'status' => 0,
			'url' => '',
			'changelog' => '',
			'timestamp' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
		];
	
		$result = version_compare($installedVersion, $currentVersion);
	
		if ($result < 0) {
			// New version available
			$response = [
				'newVersion' => true,
				'status' => 1,
				'url' => $productDetails['MERAF Production Panel SaaS']['url'],
				'changelog' => $productDetails['MERAF Production Panel SaaS']['changelog'],
				'timestamp' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
			];
		} elseif ($result > 0) {
			// Installed version is higher than current version
			$response = [
				'newVersion' => false,
				'status' => 0,
				'url' => '',
				'changelog' => '',
				'timestamp' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
			];
		} else {
			// Installed version is same as current version
			$response = [
				'newVersion' => false,
				'status' => 0,
				'url' => $productDetails['MERAF Production Panel SaaS']['url'],
				'changelog' => $productDetails['MERAF Production Panel SaaS']['changelog'],
				'timestamp' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
			];
		}
	
		return $response;
	}
	
	protected function isUpdateRequired($versionData)
	{
		if (isset($versionData['timestamp'])) {
			$lastCheckedTimestamp = strtotime($versionData['timestamp']);
			return time() - $lastCheckedTimestamp >= 6 * 3600; // Check if more than 6 hours have passed
		}
		return true; // If timestamp is not set, update is required
	}
	
	protected function updateVersionFile($productDetails)
	{
		$versionFilePath = USER_DATA_PATH . 'version.json';
		$installedVersion = $this->myConfig['appVersion'] ?? '0.0.0';

		// Retrieve the details from the server and compare at the same time
		if ($productDetails !== false) {
			$currentVersion = $productDetails['MERAF Production Panel SaaS']['version'];
			$productChangelog = $productDetails['MERAF Production Panel SaaS']['changelog'];
			$response = $this->compareVersions($installedVersion, $currentVersion, $productDetails);
		} else {
			// The API call returned empty or failed. Fall back to local app details
			$appDetailsPath = USER_DATA_PATH . 'MERAF.json';
			
			if (file_exists($appDetailsPath)) {
				$productDetails = json_decode(file_get_contents($appDetailsPath), true);
	
				if (json_last_error() !== JSON_ERROR_NONE) {
					// Handle JSON decoding error
					log_message('error', '[Home] Failed to decode JSON from ' . $appDetailsPath . ': ' . json_last_error_msg());
					return;
				}
	
				$currentVersion = $productDetails['MERAF Production Panel SaaS']['version'];
				$productChangelog = $productDetails['MERAF Production Panel SaaS']['changelog'];
				$productURL = $productDetails['MERAF Production Panel SaaS']['url'];
	
				$response = $this->compareVersions($installedVersion, $currentVersion, $productDetails);
			} else {
				// Local app details file doesn't exist, create default response
				log_message('error', '[Home] App details not found, creating default values.');
				$response = [
					'newVersion' => false,
					'status' => 0,
					'url' => null,
					'changelog' => null,
					'timestamp' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
				];
			}
		}
	
		// Update version.json with the response
		$jsonData = json_encode($response, JSON_PRETTY_PRINT);
		if (json_last_error() !== JSON_ERROR_NONE) {
			log_message('error', '[Home] Failed to encode version data to JSON: ' . json_last_error_msg());
		} else {
			file_put_contents($versionFilePath, $jsonData);
		}
	}
	
	public function checkAndUpdateVersionJson()
	{
		$versionFilePath = USER_DATA_PATH . 'version.json';
	
		// Check if version.json exists
		if (!file_exists($versionFilePath)) {
			// Fetch new version details
			$productDetails = fetchVersionDetails();
			$this->updateVersionFile($productDetails);
		}
	
		// Read and decode version.json data
		$versionData = json_decode(file_get_contents($versionFilePath), true);
	
		if (json_last_error() !== JSON_ERROR_NONE) {
			log_message('error', '[Home] Failed to decode JSON from ' . $versionFilePath . ': ' . json_last_error_msg());
			return;
		}
	
		// Check if an update is required based on timestamp
		if (!$this->isUpdateRequired($versionData)) {
			return; // the version.json file is updated
		}
		else {
			log_message('debug', '[Home] The file version.json is more than 6 hours old, time to update it');
	
			// Fetch new version details
			$productDetails = fetchVersionDetails();
			$this->updateVersionFile($productDetails);
		}	
	}

	protected function updateChangelog($productName, $productVersion, $productFile, $productChangelog)
	{
		$productList = productList($this->userID);
	
		if (in_array($productName, $productList)) {
			// Set the location of the json file based on the product name
			$jsonPath = $this->userDataPath .  $this->myConfig['userProductPath'] . sha1($productName, false) . '.json';
	
			if (!file_exists($jsonPath)) {
				try {
					// Create the product json file with blank content
					if (file_put_contents($jsonPath, '') === false) {
						throw new Exception(lang('Notifications.error_unable_to_create_json_for_product', ['productName' => $productName]));
					}
				} catch (\Exception $e) {
					$response = [
						'success' => false,
						'status'  => 0,
						'msg'     => $e->getMessage(),
					];

					return $this->response->setJSON($response);
				}
			}
	
			// Replace some characters in the changelog
			$productChangelog = str_replace(array('<', '>'), array('&#60;', '&#62;'), $productChangelog);
	
			// Use the product name as the key
			$productNewDetails[$productName]['version']     = $productVersion;
			$productNewDetails[$productName]['url']         = $productFile;
			$productNewDetails[$productName]['changelog']   = $productChangelog;
	
			// Write the details in the product's json file
			try {
				$filePointer = fopen($jsonPath, 'w');
				fwrite($filePointer, json_encode($productNewDetails, JSON_PRETTY_PRINT));
				fclose($filePointer);
			} catch (\Exception $e) {

				$response = [
					'success' => false,
					'status'  => 0,
					'msg'     => lang('Notifications.error_unable_to_write_json_for_product', ['message' => $e->getMessage()]),
				];
				
				return json_encode($response, JSON_FORCE_OBJECT | JSON_HEX_QUOT | JSON_HEX_TAG);
			}
	
			$response = [
				'success' => true,
				'status'  => 1,
				'msg'     => lang('Notifications.success_product_updated', ['productName' => $productName]),
			];
	
			return json_encode($response, JSON_FORCE_OBJECT | JSON_HEX_QUOT | JSON_HEX_TAG);
	
		} else {
			$response = [
				'success' => false,
				'status'  => 0,
				'msg'     => lang('Notifications.error_not_existing_in_the_product_list', ['productName' => $productName]),
			];
			
			return json_encode($response, JSON_FORCE_OBJECT | JSON_HEX_QUOT | JSON_HEX_TAG);
		}
	}

	public function serve($productName, $fileName, $misc=null)
	{
		// Define the path to the directory where your files are stored
		if($productName === 'email-template') {
			$filePath = $this->userDataPath . $this->myConfig['userEmailTemplatesPath'];

			$version = null !== $this->request->getGet('v') ? '_v'.$this->request->getGet('v') : '';

			// Define the ZIP filename
			$zipFileName = $fileName . $version . '.zip';
			
			$temporary_folder = WRITEPATH . 'temp/';
			if (!file_exists($temporary_folder)) {
				mkdir($temporary_folder, 0777, true);
			}
			
			$zipFilePath = $temporary_folder . $zipFileName;
			
			// Initialize ZipArchive
			$zip = new \ZipArchive();
			
			if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
				return $this->response->setStatusCode(500)->setBody(lang('Notifications.error_create_zip_file'));
			}
			
			// Add files to the ZIP archive, prefixed with $fileName as the root folder
			$this->addFolderToZip($filePath.$fileName, $zip, $fileName);
			
			// Close the ZIP archive
			$zip->close();

			// Register shutdown function to delete the temporary file
			register_shutdown_function(function() use ($zipFilePath) {
				// Wait a short moment to ensure the file has been served
				if (file_exists($zipFilePath)) {
					if (!@unlink($zipFilePath)) {
						log_message('error', '[Home] Failed to delete temporary zip file: ' . $zipFilePath);
					}
				}
				
				// Also clean up any old temporary zip files
				$this->cleanupOldTemporaryFiles();
			});	
			
			// Serve the ZIP file as a download
			return $this->response->download($zipFilePath, null)->setFileName($zipFileName);
		}
		else {
			$filePath = $this->userDataPath .  $this->myConfig['userProductPath'] . $productName . '/';
		}
	
		// Check if the file name contains "SLM" and "latest"
		if (strpos($fileName, 'SLM') !== false) {
			// Get all files in the folder
			$files = array_diff(scandir($filePath), array('.', '..'));
	
			// Find the zip file with the file name containing "SLM" and "latest"
			$zipFile = null;
			foreach ($files as $file) {
				if (strpos($file, 'SLM') !== false && strpos($file, 'latest') !== false && pathinfo($file, PATHINFO_EXTENSION) == 'zip') {
					$zipFile = $file;
					break;
				}
			}
	
			// If zip file is found, serve it
			if ($zipFile) {
				$filePath = $filePath . $zipFile;
	
				// Set headers
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . $zipFile . '"');
				header('Content-Length: ' . filesize($filePath));
	
				// Read the file and output its content as a blob
				readfile($filePath);
				exit(); // Ensure no further processing is done
			} else {
				// If zip file not found, return error message
				return $this->response->setStatusCode(404)->setJSON(['error' => lang('Notifications.file_not_found')]);
			}
		}
		// WooCommerce Addon Plugin
		else if (strpos($fileName, 'WooCommerce') !== false) {
			// Get all files in the folder
			$files = array_diff(scandir($filePath), array('.', '..'));
	
			// Find the zip file with the file name containing "WooCommerce" and "latest"
			$zipFile = null;
			foreach ($files as $file) {
				if (strpos($file, 'WooCommerce') !== false && strpos($file, 'latest') !== false && pathinfo($file, PATHINFO_EXTENSION) == 'zip') {
					$zipFile = $file;
					break;
				}
			}
	
			// If zip file is found, serve it
			if ($zipFile) {
				$filePath = $filePath . $zipFile;
	
				// Set headers
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . $zipFile . '"');
				header('Content-Length: ' . filesize($filePath));
	
				// Read the file and output its content as a blob
				readfile($filePath);
				exit(); // Ensure no further processing is done
			} else {
				// If zip file not found, return error message
				return $this->response->setStatusCode(404)->setJSON(['error' => lang('Notifications.file_not_found')]);
			}
		}
	
		// Set the full path to the file
		$fullPath = $filePath . $fileName;
	
		// Check if the file exists
		if (file_exists($fullPath)) {
			// Set the MIME type
			$mimeType = mime_content_type($fullPath);
	
			// Set the file name for the download
			$downloadName = $fileName;
	
			// Send the file as a response
			$response = $this->response->download($fullPath, null)->setFileName($downloadName)->setContentType($mimeType);
	
			// Return the response
			return $response;
		} else {
			// If the file does not exist, show a 404 page or handle the error accordingly
			return $this->response->setStatusCode(404)->setJSON(['error' => lang('Notifications.file_not_found')]);
		}
	}	

	/**
	 * page = create_product, modify_product, version_files
	 */
	public function product_manager($page = '')
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
		
		$selectedProduct = null !== $this->request->getGet('product') ? $this->request->getGet('product') : NULL;

		$pageTitle = lang('Pages.Product_manager');
		$pageTitle = $page ? $pageTitle . ' | ' . lang('Pages.' . ucwords($page)) : $pageTitle;
		$pageTitle = $selectedProduct ? $pageTitle . ' | ' . $selectedProduct : $pageTitle;

		$data['pageTitle'] = $pageTitle;
		$data['section'] = 'product_manager';
		$data['subsection'] = $page;
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		$data['productVariations'] = $this->productVariations();
		$data['selectedProduct'] = $selectedProduct;
		$data['productFiles'] = getProductFiles('', $this->userID);
		
		return view('dashboard/products/product_manager', $data);		
	}

public function version_files_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}

		$msgResponse_validationError = lang('Notifications.error_submitted_details');
		$msgResponse_uploadError = lang('Notifications.error_uploading_release_package');
		$msgResponse_duplicateError = lang('Notifications.error_duplicated_filename');

		$fileExtensions = '';
		if ($this->myConfig['acceptedFileExtensions']) {
			$fileExtensionsArray = json_decode(str_replace('\r', '', $this->myConfig['acceptedFileExtensions']), true);
			$fileExtensions = implode(',', $fileExtensionsArray);
		}

		// Check if multiple files are uploaded
		$filesMulti = $this->request->getFiles();
		$hasMultiFiles = isset($filesMulti['releasePackageMulti']) && is_array($filesMulti['releasePackageMulti']) && count($filesMulti['releasePackageMulti']) > 0;

		// If multi-file upload is present, process multiple files
		if ($hasMultiFiles) {
			$productName = trim($this->request->getPost('productName'));
			if (empty($productName)) {
				return $this->response->setJSON([
					'success' => false,
					'status' => 0,
					'msg' => $msgResponse_validationError,
				]);
			}

			$uploadedFilePath = $this->userDataPath .  $this->myConfig['userProductPath'] . $productName . '/';

			$fileResults = [];
			$anySuccess = false;

			foreach ($filesMulti['releasePackageMulti'] as $file) {
				if (!$file->isValid()) {
					$fileResults[] = [
						'fileName' => $file->getClientName(),
						'success' => false,
						'message' => lang('Notifications.error_uploading_release_package'),
					];
					continue;
				}

				// Validate extension
				$ext = strtolower($file->getClientExtension());
				if (!in_array($ext, $fileExtensionsArray)) {
					$fileResults[] = [
						'fileName' => $file->getClientName(),
						'success' => false,
						'message' => lang('Pages.release_package_upload_feedback', ['acceptedFileExtensions' => $fileExtensions]),
					];
					continue;
				}

				// Check for duplicate
				if (is_file($uploadedFilePath . $file->getClientName())) {
					$fileResults[] = [
						'fileName' => $file->getClientName(),
						'success' => false,
						'message' => $msgResponse_duplicateError,
					];
					continue;
				}

				// Move file
				if ($file->move($uploadedFilePath)) {
					$fileResults[] = [
						'fileName' => $file->getClientName(),
						'success' => true,
						'message' => lang('Notifications.success_upload_release_package', ['productName' => $productName]),
					];
					$anySuccess = true;
				} else {
					$fileResults[] = [
						'fileName' => $file->getClientName(),
						'success' => false,
						'message' => $msgResponse_uploadError,
					];
				}
			}

			return $this->response->setJSON([
				'success' => $anySuccess,
				'status' => $anySuccess ? 1 : 0,
				'msg' => $anySuccess ? lang('Notifications.success_upload_release_package', ['productName' => $productName]) : $msgResponse_uploadError,
				'fileResults' => $fileResults,
				'current_files' => getProductFiles(),
			]);
		}

		// Fallback to single file upload
		$validationRules = [
			'upload-productName' => 'required',
			'release-Package' => 'uploaded[release-Package]|ext_in[release-Package,' . $fileExtensions . ']',
		];

		$validationMessages = [
			'upload-productName' => [
				'required' => lang('Pages.please_select_product_feedback')
			],
			'release-Package' => [
				'uploaded' => lang('Notifications.choose_zip_file'),
				'ext_in' => lang('Pages.release_package_upload_feedback', ['acceptedFileExtensions' => $fileExtensions]),
			],
		];

		if (!$this->validate($validationRules, $validationMessages)) {
			$errors = $this->validator->getErrors();
			return $this->response->setJSON([
				'success' => false,
				'status' => 0,
				'msg' => $errors,
			]);
		}

		$productName = trim($this->request->getPost('upload-productName'));
		$releasePackage = $this->request->getFile('release-Package');
		$uploadedFilePath = $this->userDataPath .  $this->myConfig['userProductPath'] . $productName . '/';

		if (is_file($uploadedFilePath . $releasePackage->getName())) {
			return $this->response->setJSON([
				'success' => false,
				'status' => 0,
				'msg' => $msgResponse_duplicateError,
			]);
		}

		if ($releasePackage->move($uploadedFilePath)) {
			return $this->response->setJSON([
				'success' => true,
				'status' => 1,
				'msg' => lang('Notifications.success_upload_release_package', ['productName' => $productName]),
				'current_files' => getProductFiles(),
			]);
		} else {
			return $this->response->setJSON([
				'success' => false,
				'status' => 0,
				'msg' => $msgResponse_uploadError,
				'current_files' => getProductFiles(),
			]);
		}
	}		
	
	public function list_product_files()
	{
		$this->checkIfLoggedIn(); // Ensure user is authenticated
	
		if ($this->request->getMethod() !== 'POST') {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_fetching_file_list'),
				'current_files' => [],
			];
			return $this->response->setJSON($response);
		}
	
		if (!empty($productFiles)) {
			$response = [
				'success' => true,
				'msg' => lang('Notifications.success_retrieved_the_file_list'),
				'current_files' => getProductFiles('', $this->userID),
			];
		} else {
			$response = [
				'success' => false,
				'msg' => lang('Notifications.error_no_product_found'),
				'current_files' => [],
			];
		}
	
		return $this->response->setJSON($response);
	}	

	public function delete_product_files_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}			

		// Get selected files to delete
		$selectedFiles = $this->request->getPost('selectedFiles');
		$product = trim($this->request->getPost('productFolder'));

		if (!empty($selectedFiles)) {
			$deletedFiles = [];
			$failedFiles = [];

			if (is_array($selectedFiles)) {
				// Process multiple selected files
				foreach ($selectedFiles as $fileName) {
					if ($this->deleteFile($fileName, $product)) {
						$deletedFiles[] = $fileName;
					} else {
						$failedFiles[] = $fileName;
					}
				}
			} else {
				// Process single selected file
				if ($this->deleteFile($selectedFiles, $product)) {
					$deletedFiles[] = $selectedFiles;
				} else {
					$failedFiles[] = $selectedFiles;
				}
			}

			if (!empty($deletedFiles)) {
				$response = [
					'success' => true,
					'status' => 1,
					'msg' => lang('Notifications.success_deleted_file'),
					'deleted_files' => $deletedFiles,
					'failed_files' => $failedFiles,
					'current_files' => getProductFiles('', $this->userID),
				];
			} else {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.error_deleting_file'),
					'deleted_files' => $deletedFiles,
					'failed_files' => $failedFiles,
					'current_files' => getProductFiles('', $this->userID),
				];
			}
		} else {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_no_file_selected_for_deletion'),
				'deleted_files' => $deletedFiles,
				'failed_files' => $failedFiles,
				'current_files' => getProductFiles('', $this->userID),
			];
		}

		return $this->response->setJSON($response);
	}
	
	// Helper function to delete a file
	private function deleteFile($fileName, $product)
	{
		// Assume $product is the product name (you need to get it from somewhere)
		$productPath = $this->userDataPath .  $this->myConfig['userProductPath'] . $product . '/';
		$filePath = $productPath . $fileName;

		// Check if the file exists before attempting to delete
		if (file_exists($filePath)) {
			// Attempt to delete the file
			if (unlink($filePath)) {
				return true; // File successfully deleted
			} else {
				log_message('error', '[Home] ' . lang('Notifications.error_file_deletion', ['fileName' => $fileName])); // Log deletion failure
				return false; // Failed to delete file
			}
		} else {
			log_message('error', '[Home] ' . lang('Notifications.error_file_not_found', ['fileName' => $fileName])); // Log file not found
			return false; // File does not exist
		}
	}

	public function new_product_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}			
	
		// Set the response messages
		$msgResponse_validationError = lang('Notifications.error_submitted_details');
		$msgResponse_duplicateError = lang('Notifications.duplicated_product_name');

		// Validate form data
		$validationRules = [
			'new-productName' => 'required|alpha_numeric_space',
		];

		// Run validation
		if (!$this->validate($validationRules)) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => $msgResponse_validationError,
			];

			return $this->response->setJSON($response);
		} else {
			// Form data is valid, proceed with further processing
			$productName = trim($this->request->getPost('new-productName'));

			// Set the path where to create new folder
			$newFolderPath = $this->userDataPath .  $this->myConfig['userProductPath'] . $productName;

			// Check if the product folder already exists
			if (file_exists($newFolderPath)) {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => $msgResponse_duplicateError,
				];

				return $this->response->setJSON($response);
			}

			// Create the product folder
			mkdir($newFolderPath, 0755, true);

			// Check if the folder was created successfully
			if (!file_exists($newFolderPath)) {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.error_unable_create_product_folder', ['productName' => $productName]),
				];

				return $this->response->setJSON($response);
			}

			// Set the file name for the new JSON file
			$newJSONfile = $this->userDataPath .  $this->myConfig['userProductPath'] . sha1($productName, false) . '.json';

			// Initiate creation of the new JSON file
			$emptyArray = [];

			$emptyArray[$productName]['version'] 		= lang('Notifications.version_not_yet_set');
			$emptyArray[$productName]['url'] 			= '';
			$emptyArray[$productName]['changelog'] 		= lang('Notifications.changelog_not_yet_set');

			$jsonEncoded = json_encode($emptyArray, JSON_PRETTY_PRINT);

			// Check for JSON encoding errors
			if (json_last_error() !== JSON_ERROR_NONE) {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.error_json_encoding_new_product', ['productName' => $productName]),
				];

				return $this->response->setJSON($response);
			}

			// Write JSON data to file
			$jsonFileCreated = file_put_contents($newJSONfile, $jsonEncoded);

			// Check if the JSON file was created successfully
			if (!$jsonFileCreated) {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.error_unable_create_new_json_for_new_product', ['productName' => $productName]),
				];

				return $this->response->setJSON($response);
			}

			// Add the new product in the default email template
			$emailTemplateConfigFilePath = $this->userDataPath . $this->myConfig['userAppSettings'] . 'product-email-templates.json';

			// Read existing email template configuration from JSON file
			$existingEmailTemplateConfig = [];
			if (file_exists($emailTemplateConfigFilePath)) {
				$existingEmailTemplateConfig = json_decode(file_get_contents($emailTemplateConfigFilePath), true);
				
				// If default_email_template exists, convert it to array, add new product, and join back to string
				if (isset($existingEmailTemplateConfig['default_email_template'])) {
					// Convert existing string to array
					$productsArray = explode(',', $existingEmailTemplateConfig['default_email_template']);
					
					// Add new product
					$productsArray[] = $productName;
					
					// Convert back to comma-separated string
					$existingEmailTemplateConfig['default_email_template'] = implode(',', $productsArray);
				} else {
					// If the key doesn't exist, create it with the product as the first item
					$existingEmailTemplateConfig['default_email_template'] = $productName;
				}
				
				// Save the updated configuration
				file_put_contents($emailTemplateConfigFilePath, json_encode($existingEmailTemplateConfig, JSON_PRETTY_PRINT));
			}

			// Both folder and JSON file created successfully
			$response = [
				'success' => true,
				'status' => 1,
				'msg' => lang('Notifications.success_new_product_creation', ['productName' => $productName]),
				'current_files' => getProductFiles('', $this->userID),
			];

			return $this->response->setJSON($response);
		}
	}

	public function delete_whole_product_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}			

		// Get selected product to delete
		$productName = trim($this->request->getPost('modify-productName'));

		// Set the product folder to be deleted recursively
		$productFolder = $this->userDataPath .  $this->myConfig['userProductPath'] . $productName;

		$productEncodedName = sha1($productName, false);

		// Set the product's JSON file
		$productJSON = $this->userDataPath .  $this->myConfig['userProductPath'] . $productEncodedName . '.json';

		// Set the product's guide file
		$productGuide = $this->userDataPath .  $this->myConfig['userProductPath'] . $productEncodedName . '.txt';

		// Check if the product folder exists
		if (!file_exists($productFolder)) {
			return $this->response->setJSON([
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_product_folder_not_found', ['productName' => $productName]),
			]);
		}

		// Attempt to delete the product files
		$result = deleteDirectory($productFolder);

		// Check if all files were deleted successfully
		if (!$result) {
			return $this->response->setJSON([
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_deleting_product_files', ['productName' => $productName]),
			]);
		}

		// Delete the product JSON file
		if (file_exists($productJSON)) {
			unlink($productJSON);
		}

		// Delete the product guide file
		if (file_exists($productGuide)) {
			unlink($productGuide);
		}

		// Delete the product anywhere in the email template settings
		$emailTemplateConfigFilePath = $this->userDataPath . $this->myConfig['userAppSettings'] . 'product-email-templates.json';				

		// Read existing email template configuration from JSON file
		$existingEmailTemplateConfig = [];
		if (file_exists($emailTemplateConfigFilePath)) {
			$existingEmailTemplateConfig = json_decode(file_get_contents($emailTemplateConfigFilePath), true);
			
			// Iterate through each key in the configuration
			foreach ($existingEmailTemplateConfig as $key => $value) {
				// Skip empty values
				if (empty($value)) {
					continue;
				}
				
				// Convert the string to array and trim each element
				$productsArray = array_map('trim', explode(',', $value));
				
				// Remove the product using array_diff
				$productsArray = array_diff($productsArray, [$productName]);
				
				// Convert back to comma-separated string
				$existingEmailTemplateConfig[$key] = implode(',', array_filter($productsArray));
			}
			
			// Save the updated configuration
			file_put_contents($emailTemplateConfigFilePath, json_encode($existingEmailTemplateConfig, JSON_PRETTY_PRINT));
		}

		// Delete the product anywhere in the variation list
		$variationFilePath = $this->userDataPath . $this->myConfig['userAppSettings'] . 'product-variations.json';

		// Read existing variations from JSON file
		$existingVariations = [];
		if (file_exists($variationFilePath)) {
			$existingVariations = json_decode(file_get_contents($variationFilePath), true);
			
			foreach ($existingVariations as $key => $value) {
				if (!empty($value)) {
					// Split into array and remove the product
					$productsArray = explode(',', $value);
					$productsArray = array_map('trim', $productsArray);
					
					// Create new array excluding the product to remove
					$newProductsArray = [];
					foreach ($productsArray as $product) {
						if (trim($product) !== trim($productName)) {
							$newProductsArray[] = $product;
						}
					}
					
					// Update the value with filtered products
					$existingVariations[$key] = implode(',', $newProductsArray);
				}
			}
			
			// Save the updated configuration
			file_put_contents($variationFilePath, json_encode($existingVariations, JSON_PRETTY_PRINT));
		}

		// Product successfully deleted
		return $this->response->setJSON([
			'success' => true,
			'status' => 1,
			'msg' => lang('Notifications.success_deleting_product', ['productName' => $productName]),
			'current_files' => getProductFiles('', $this->userID),
		]);
	}
	
	public function rename_product_action()
	{
		$this->checkIfLoggedIn();
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}   
	
		// Validate form data
		$validationRules = [
			'oldProductName' => 'required|alpha_numeric_space',
			'newProductName' => 'required|alpha_numeric_space',
		];
	
		if (!$this->validate($validationRules)) {
			return $this->response->setJSON([
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_submitted_details'),
			]);
		}
	
		// Get and sanitize input
		$oldProductName = trim($this->request->getPost('oldProductName'));
		$newProductName = trim($this->request->getPost('newProductName'));
	
		// Set up file paths
		$paths = $this->getProductPaths($oldProductName, $newProductName);
	
		try {
			// Check for duplicate product
			if (file_exists($paths['newFolder'])) {
				throw new Exception(lang('Notifications.duplicated_product_name'));
			}
	
			// Perform file operations
			$this->renameProductFiles($paths);
			
			// Update JSON contents
			$this->updateProductJSON($paths['newJSON'], $oldProductName, $newProductName);
			$this->updateVariationsJSON($oldProductName, $newProductName);
			$this->updateEmailTemplatesJSON($oldProductName, $newProductName);
	
			return $this->response->setJSON([
				'success' => true,
				'status' => 1,
				'msg' => lang('Notifications.success_renaming_product', [
					'oldProductName' => $oldProductName, 
					'newProductName' => $newProductName
				]),
				'current_files' => getProductFiles('', $this->userID),
			]);
	
		} catch (Exception $e) {
			// Attempt to rollback any changes if needed
			$this->rollbackRename($paths);
	
			return $this->response->setJSON([
				'success' => false,
				'status' => 0,
				'msg' => $e->getMessage(),
			]);
		}
	}
	
	private function getProductPaths($oldName, $newName): array
	{
		$encodedOld = sha1($oldName, false);
		$encodedNew = sha1($newName, false);
		$productPath = $this->userDataPath . $this->myConfig['userProductPath'];
	
		return [
			'oldFolder' => $productPath . $oldName,
			'newFolder' => $productPath . $newName,
			'oldJSON' => $productPath . $encodedOld . '.json',
			'newJSON' => $productPath . $encodedNew . '.json',
			'oldGuide' => $productPath . $encodedOld . '.txt',
			'newGuide' => $productPath . $encodedNew . '.txt',
		];
	}
	
	private function renameProductFiles(array $paths): void
	{
		// Rename folder
		if (file_exists($paths['oldFolder']) && !rename($paths['oldFolder'], $paths['newFolder'])) {
			throw new Exception(lang('Notifications.error_unable_to_rename_product'));
		}
	
		// Rename JSON file
		if (file_exists($paths['oldJSON']) && !rename($paths['oldJSON'], $paths['newJSON'])) {
			throw new Exception(lang('Notifications.error_unable_rename_product_json'));
		}
	
		// Rename guide file
		if (file_exists($paths['oldGuide']) && !rename($paths['oldGuide'], $paths['newGuide'])) {
			throw new Exception(lang('Notifications.error_unable_rename_product_guide'));
		}
	}
	
	private function updateProductJSON(string $jsonPath, string $oldName, string $newName): void
	{
		$details = json_decode(file_get_contents($jsonPath), true);
		$productDetails = [];
		
		foreach ($details as $name => $detail) {
			$key = ($name === $oldName) ? $newName : $name;
			$productDetails[$key] = $detail;
		}
	
		$this->writeJSON($jsonPath, $productDetails);
	}
	
	private function updateVariationsJSON(string $oldName, string $newName): void
	{
		$filePath = $this->userDataPath . $this->myConfig['userAppSettings'] . 'product-variations.json';
		if (!file_exists($filePath)) return;
	
		$variations = json_decode(file_get_contents($filePath), true);
		
		foreach ($variations as $variation => $values) {
			if (strpos($values, $oldName) !== false) {
				$variations[$variation] = str_replace($oldName, $newName, $values);
			}
		}
	
		$this->writeJSON($filePath, $variations);
	}
	
	private function updateEmailTemplatesJSON(string $oldName, string $newName): void
	{
		$filePath = $this->userDataPath . $this->myConfig['userAppSettings'] . 'product-email-templates.json';
		if (!file_exists($filePath)) return;
	
		$templates = json_decode(file_get_contents($filePath), true);
		
		foreach ($templates as $template => $values) {
			if (strpos($values, $oldName) !== false) {
				$templates[$template] = str_replace($oldName, $newName, $values);
			}
		}
	
		$this->writeJSON($filePath, $templates);
	}
	
	private function writeJSON(string $path, array $data): void
	{
		if (file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT)) === false) {
			throw new Exception(lang('Notifications.error_json_writing'));
		}
	}
	
	private function rollbackRename(array $paths): void
	{
		// Attempt to restore original folder name if new folder exists
		if (file_exists($paths['newFolder'])) {
			@rename($paths['newFolder'], $paths['oldFolder']);
		}
		
		// Attempt to restore original JSON file if new one exists
		if (file_exists($paths['newJSON'])) {
			@rename($paths['newJSON'], $paths['oldJSON']);
		}
		
		// Attempt to restore original guide file if new one exists
		if (file_exists($paths['newGuide'])) {
			@rename($paths['newGuide'], $paths['oldGuide']);
		}
	}

	public function provide_product_json_only($productName='')
	{
		if ( isset($_GET['s']) || $productName !== '') {
			$requestedProduct = '';

			if(isset($_GET['s'])) {
				$requestedProduct = $_GET['s'];
			}
			else if($productName !== '') {
				$requestedProduct = $productName;
			}

			$requestedProduct = urldecode($requestedProduct);

			// Extract product base name
			$productNameBasic = productBasename($requestedProduct, $this->userID);

			$requestedDetails = $this->userDataPath .  $this->myConfig['userProductPath'] . sha1($productNameBasic, false) . '.json';

			if((strpos($requestedProduct, 'json') !== false)) {
				$requestedDetails = $this->userDataPath .  $this->myConfig['userProductPath'] . $requestedProduct;
			}	
		
			// Check if the JSON file exists
			if (file_exists($requestedDetails)) {
				// Read the JSON data from the file
				$json = file_get_contents($requestedDetails);
		
				// Output the JSON data in the response
				return $this->response->setJSON($json);
			} else {
				throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
			}
		}
	}

	public function email_template_setup()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
		
		$data['pageTitle'] = lang('Pages.Email_Service') . ' | ' . lang('Pages.Template');
		$data['section'] = 'Email_Service';    
		$data['subsection'] = 'Template';    
		$data['userData'] = $this->userAcctDetails;
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		$data['userEmailTemplates'] = getEmailTemplateDetails($this->userID);
		
		// Search for the user's logo
		$logoPath = $this->myConfig['emailLogoFile'] ?? '';
		$logoPath = str_replace(base_url(), ROOTPATH, $logoPath);

		if (!empty($logoPath) && is_file($logoPath)) {
			$mimeType = mime_content_type($logoPath);
			$base64   = base64_encode(file_get_contents($logoPath));
			$data['emailLogoFile'] = 'data:' . $mimeType . ';base64,' . $base64;
		} else {
			$data['emailLogoFile'] = '';
		}
		
		return view('dashboard/email-service/templates', $data);        
	}

	public function email_notifications_setup()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
		
		$data['pageTitle'] = lang('Pages.Email_Service') . ' | ' . lang('Pages.Notifications');
		$data['section'] = 'Email_Service';    
		$data['subsection'] = 'Notifications';    
		$data['userData'] = $this->userAcctDetails;
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;

		// Prepare the email select options
		$emailTemplateSelections = [];

		// Get the email templates configured
		$emailTemplateConfigFilePath = $this->userDataPath .  $this->myConfig['userAppSettings'] . 'product-email-templates.json';
		$emailTemplateConfig = json_decode(file_get_contents($emailTemplateConfigFilePath), true);
		
		foreach ($emailTemplateConfig as $key => $value) {
			// if (empty($value)) {
				$data['emailTemplateSelections'][] = $key;
			// }
		}
		
		return view('dashboard/email-service/notifications', $data);        
	}

	public function email_service_settings()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
		
		$data['pageTitle'] = lang('Pages.Email_Service') . ' | ' . lang('Pages.Settings');
		$data['section'] = 'Email_Service';    
		$data['subsection'] = 'Settings';    
		$data['userData'] = $this->userAcctDetails;
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;

		return view('dashboard/email-service/settings', $data);        
	}
	
	public function email_logs_page()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
	
		$data['pageTitle'] = lang('Pages.Email_Service') . ' | ' . lang('Pages.Logs');
		$data['section'] = 'Email_Service';
		$data['subsection'] = 'Logs';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		$data['pageUrl'] = base_url('email-service/logs/');
	
		return view('dashboard/admin/email_logs', $data);
	}
	
	public function email_logs_data()
	{
		$emailLogModel = new \App\Models\EmailLogModel();
	
		$status = $this->request->getGet('status');
		$startDate = $this->request->getGet('start_date');
		$endDate = $this->request->getGet('end_date');
	
		log_message('debug', '[AdminController] Status: ' . $status);
		log_message('debug', '[AdminController] Start Date: ' . $startDate);
		log_message('debug', '[AdminController] End Date: ' . $endDate);
	
		if ($status) {
			$emailLogModel->where('status', $status);
		}
		if ($startDate) {
			$emailLogModel->where('created_at >=', $startDate . ' 00:00:00');
		}
		if ($endDate) {
			$emailLogModel->where('created_at <=', $endDate . ' 23:59:59');
		}
	
		$logs = $emailLogModel->where('owner_id', $this->userID)->orderBy('id', 'DESC')->findAll();
	
		log_message('debug', '[AdminController] Email Log Query: ' . $emailLogModel->getLastQuery()->getQuery());
		log_message('debug', '[AdminController] Email Log Filtered results: ' . json_encode($logs, JSON_PRETTY_PRINT));
	
		return $this->response->setJSON($logs);
	}
	
	public function view_email_log($id)
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
	
		$emailLogModel = new \App\Models\EmailLogModel();
		$data['log'] = $emailLogModel->find($id);
	
		if (empty($data['log'])) {
			return redirect()->to('/admin-options/email-logs')->with('error', lang('Notifications.Email_log_not_found'));
		}
	
		$data['pageTitle'] = lang('Pages.Email_Service') . ' | ' . lang('Pages.View_Email_Log');
		$data['section'] = 'Email_Service';
		$data['subsection'] = 'Logs';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		$data['pageUrl'] = base_url('email-service/logs/');
	
		return view('dashboard/admin/email_log_view', $data);
	}
	
	public function view_email_body($id)
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
	
		$emailLogModel = new \App\Models\EmailLogModel();
		$log = $emailLogModel->find($id);
	
		if (empty($log)) {
			return $this->response->setStatusCode(404)->setBody(lang('Notifications.Email_log_not_found'));
		}
		
		if($log['format'] === 'text') {
			include_once APPPATH . 'Views/includes/auth/head.php';
		}
	
		// Prepare the actual email message
		if($log['format'] === 'text') {
			$message = '<link href="' . $styleCSS . '" class="theme-opt" rel="stylesheet" type="text/css" /><p class="p-1">' . nl2br($log['body']) . '</p>';
		}
		else {
			$emailAttachments = json_decode($log['attachments'], true) ?? [];
			$message = $log['body'];
			
			// Only process logo replacement if attachments exist
			if (!empty($emailAttachments)) {
				$emailLogoFileName = $emailAttachments[0];
				$originalLogoSrc = USER_DATA_PATH . $log['owner_id'] . DIRECTORY_SEPARATOR . $emailLogoFileName;			
				
				if(!empty($emailLogoFileName)) {
					$fileExtension = pathinfo($emailLogoFileName, PATHINFO_EXTENSION);

					if(file_exists($originalLogoSrc)) {
						$encodedLogoSrc = base64_encode(file_get_contents($originalLogoSrc));

						$message = preg_replace(
							'/cid:' . preg_quote('logo.' . $fileExtension, '/') . '@[^"]+/',
							"data:image/{$fileExtension};base64,{$encodedLogoSrc}",
							$message
						);
					}
					
				}
			}
		}
	
		return $this->response->setHeader('Content-Type', 'text/html')
							  ->setBody($message);
	}
	
	public function resend_email($id)
	{
		$emailLogModel = new \App\Models\EmailLogModel();
		$log = $emailLogModel->find($id);
	
		if (!$log) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.Email_log_not_found')
			]);
		}

		$emailService = new \App\Libraries\EmailService();

		// Search for the user's logo
		$checkEmailLogo = $this->myConfig['emailLogoFile'] ?? null;
		$email_logo = ($checkEmailLogo && $log['format'] === 'html') ? 
			$emailService->getEmailLogo($log['owner_id'], $checkEmailLogo) : 
			null;

		log_message('debug', '[AdminController] Attaching image logo on data: ' . $email_logo);

		if($email_logo) {
			$emailLogoFileName = explode('@', $email_logo)[0];
			$emailLogoCID = explode('@', $email_logo)[1];

			$log['body'] = preg_replace(
				'/cid:' . preg_quote($emailLogoFileName, '/') . '@[^"]+/',
				'cid:' . $email_logo,
				$log['body']
			);
		}

        $emailParams = [
            'userID' => $log['owner_id'],
            'toEmail' => $log['to'],
            'toName' => '',
            'subject' => $log['subject'],
            'message' => $log['body'],
            'plain_text_message' => $log['plain_text_message'],
            'emailType' => $log['format']
        ];

        $result = $emailService->sendEmail($emailParams);
	
		if($result) {
			return $this->response->setJSON([
				'success' => true,
				'message' => lang('Notifications.Email_resent_successfully')
			]);
		}

		return $this->response->setJSON([
			'success' => false,
			'message' => lang('Notifications.Email_resent_failed')
		]);
		
	}

	public function email_notifications_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.Method_Not_Allowed')
			])->setStatusCode(405);
		}
	
		// Add validation rules
		$validationRules = [
			'selectedEmailTemplate' => 'required',
			'activationEmailSubject' => 'required|min_length[5]|max_length[200]',
			'activationEmailMessage' => 'required|min_length[10]',
			'reminderEmailSubject' => 'required|min_length[5]|max_length[200]',
			'reminderEmailMessage' => 'required|min_length[10]',
			'expiredLicenseEmailSubject' => 'required|min_length[5]|max_length[200]',
			'expiredLicenseEmailMessage' => 'required|min_length[10]',
			'numberOfHoursToRemind' => 'required|integer|greater_than[0]|less_than[169]', // One week (168 hours) max
			'newDomainDeviceEmailSubject' => 'required|min_length[5]|max_length[200]',
			'newDomainDeviceEmailMessage' => 'required|min_length[10]',
			'unregisteredDomainDeviceEmailSubject' => 'required|min_length[5]|max_length[200]',
			'unregisteredDomainDeviceEmailMessage' => 'required|min_length[10]'
		];
	
		// Add validation messages
		$validationMessages = [
			'selectedEmailTemplate' => [
				'required' => lang('Notifications.template_required'),
			],
			'numberOfHoursToRemind' => [
				'required' => lang('Notifications.hours_required'),
				'integer' => lang('Notifications.hours_must_be_number'),
				'greater_than' => lang('Notifications.hours_must_be_positive'),
				'less_than' => lang('Notifications.hours_max_limit')
			]
		];

		// Run validation
		if (!$this->validate($validationRules, $validationMessages)) {
			$errors = $this->validator->getErrors();
			$response = [
				'success' => false,
				'msg' => $errors,
			];

			return $this->response->setJSON($response);
		}

		$emailNotificationSettings = [
			'selectedEmailTemplate' => $this->request->getPost('selectedEmailTemplate'),
			'sendEmailInvalidChecks' => $this->request->getPost('sendEmailInvalidChecks'),
			'sendEmailNewLicense' => $this->request->getPost('sendEmailNewLicense'),
			'sendBCCtoResendLicense' => $this->request->getPost('sendBCCtoResendLicense'),
			'sendBCCtoLicenseClientNotifications' => $this->request->getPost('sendBCCtoLicenseClientNotifications'),
			'activationEmailSubject' => esc($this->request->getPost('activationEmailSubject')),
			'activationEmailMessage' => esc($this->request->getPost('activationEmailMessage')),
			'reminderEmailSubject' => esc($this->request->getPost('reminderEmailSubject')),
			'reminderEmailMessage' => esc($this->request->getPost('reminderEmailMessage')),
			'expiredLicenseEmailSubject' => esc($this->request->getPost('expiredLicenseEmailSubject')),
			'expiredLicenseEmailMessage' => esc($this->request->getPost('expiredLicenseEmailMessage')),
			'sendNewDomainDeviceRegistration' => $this->request->getPost('sendNewDomainDeviceRegistration'),
			'sendUnregisteredDomainDeviceRegistration' => $this->request->getPost('sendUnregisteredDomainDeviceRegistration'),
			'sendActivationNotification' => $this->request->getPost('sendActivationNotification'),
			'sendReminderNotification' => $this->request->getPost('sendReminderNotification'),
			'numberOfHoursToRemind' => $this->request->getPost('numberOfHoursToRemind'),
			'sendExpiredNotification' => $this->request->getPost('sendExpiredNotification'),
			'newDomainDeviceEmailSubject' => esc($this->request->getPost('newDomainDeviceEmailSubject')),
			'newDomainDeviceEmailMessage' => esc($this->request->getPost('newDomainDeviceEmailMessage')),
			'unregisteredDomainDeviceEmailSubject' => esc($this->request->getPost('unregisteredDomainDeviceEmailSubject')),
			'unregisteredDomainDeviceEmailMessage' => esc($this->request->getPost('unregisteredDomainDeviceEmailMessage')),
		];
	
		try {
			// Save each setting
			foreach ($emailNotificationSettings as $key => $value) {
				$result = $this->UserSettingsModel->setUserSetting($key, $value, $this->userID);
				if (!$result) {
					return $this->response->setJSON([
						'success' => false,
						'msg' => lang('Notifications.email_notifications_failed_saving')
					]);
				}
			}
	
			return $this->response->setJSON([
				'success' => true,
				'msg' => lang('Notifications.email_notifications_saved_successfully')
			]);
		} catch (\Exception $e) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => $e->getMessage()
			])->setStatusCode(500);
		}
	}

	public function email_settings_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.Method_Not_Allowed')
			])->setStatusCode(405);
		}
	
		// Add validation rules
		$validationRules = [
			'adminName' 			=> 'required|alpha_numeric_space',
			'adminEmail'    		=> 'required|valid_email',
			'replyToName' 			=> 'required|alpha_numeric_space',
			'replyToEmail'  		=> 'required|valid_email',
			'bccName' 				=> 'required|alpha_numeric_space',
			'bccEmail'    			=> 'required|valid_email',
		];
	
		// Add validation messages
		$validationMessages = [
			'adminName' => [
				'required' => lang('Notifications.required_input_empty'),
				'alpha_numeric' => lang('Notifications.required_valid_name_admin'),
			],
			'adminEmail' => [
				'required' => lang('Notifications.required_input_empty'),
				'valid_email' => lang('Notifications.required_valid_email_admin'),
			],
			'replyToName' => [
				'required' => lang('Notifications.required_input_empty'),
				'alpha_numeric' => lang('Notifications.required_valid_name_replyto'),
			],
			'replyToEmail' => [
				'required' => lang('Notifications.required_input_empty'),
				'valid_email' => lang('Notifications.required_valid_email_replyto'),
			],
			'bccName' => [
				'required' => lang('Notifications.required_input_empty'),
				'alpha_numeric' => lang('Notifications.required_valid_name_bcc'),
			],
			'bccEmail' => [
				'required' => lang('Notifications.required_input_empty'),
				'valid_email' => lang('Notifications.required_valid_email_bcc'),
			],
		];

		// Run validation
		if (!$this->validate($validationRules, $validationMessages)) {
			$errors = $this->validator->getErrors();
			$response = [
				'success' => false,
				'msg' => $errors,
			];

			return $this->response->setJSON($response);
		}

		$emailServiceSettings = [
				'adminName' => trim($this->request->getPost('adminName')),
				'adminEmail' => trim($this->request->getPost('adminEmail')),
				'replyToName' => trim($this->request->getPost('replyToName')),
				'replyToEmail' => trim($this->request->getPost('replyToEmail')),
				'bccName' => trim($this->request->getPost('bccName')),
				'bccEmail' => trim($this->request->getPost('bccEmail')),
		];
	
		try {
			// Save each setting
			foreach ($emailServiceSettings as $key => $value) {
				$result = $this->UserSettingsModel->setUserSetting($key, $value, $this->userID);
				if (!$result) {
					return $this->response->setJSON([
						'success' => false,
						'msg' => lang('Notifications.email_settings_failed_saving')
					]);
				}
			}
	
			return $this->response->setJSON([
				'success' => true,
				'msg' => lang('Notifications.email_settings_saved_successfully')
			]);
		} catch (\Exception $e) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => $e->getMessage()
			])->setStatusCode(500);
		}
	}

	public function app_settings()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		// Prepare the email select options
		$emailTemplateSelections = [];

		// Get the email templates configured
		$emailTemplateConfigFilePath = $this->userDataPath .  $this->myConfig['userAppSettings'] . 'product-email-templates.json';
		$emailTemplateConfig = json_decode(file_get_contents($emailTemplateConfigFilePath), true);
		
		foreach ($emailTemplateConfig as $key => $value) {
			// if (empty($value)) {
				$emailTemplateSelections[] = $key;
			// }
		}

		// Clear the cache
		clearCache();

		$email = \Config\Services::email();

		$emailService = [
			'protocol' => $email->protocol,
			'sendmailPath' => $email->mailPath,
			'smtpHostname' => $email->SMTPHost,
			'smtpUsername' => $email->SMTPUser,
			'smtpPassword' => $email->SMTPPass,
			'smtpPort' => $email->SMTPPort,
			'smtpEncryption' => $email->SMTPCrypto,
		];

		$data['emailService'] = $emailService;		
		$data['pageTitle'] = lang('Pages.app_settings_title');
		$data['section'] = 'app_settings';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		$data['emailTemplateSelections'] = $emailTemplateSelections;
		$data['subscriptionChecker'] = $this->subscriptionChecker;
		
		return view('dashboard/app-setup/app_settings', $data);		
	}	

	public function save_product_variation_list_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}			

		// Set the response messages
		$msgResponse_validationError = lang('Notifications.error_submitted_details');
		$msgResponse_success = lang('Notifications.success_variation_list_saved');
		$msgResponse_error = lang('Notifications.error_variation_list_not_saved');

		// Validate form data
		$validationRules = [
			'variationList' => 'permit_empty', // Allow empty or alphanumeric with spaces
		];

		// Run validation
		if (!$this->validate($validationRules)) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => $msgResponse_validationError,
			];

			return $this->response->setJSON($response);
		} else {
			// Form data is valid, proceed with further processing
			$productVariations = trim($this->request->getPost('variationList'));
			$productVariations = explode(',', $productVariations);

			// Save data as JSON file
			$variationFilePath = $this->userDataPath .  $this->myConfig['userAppSettings'] . 'product-variations.json';

			// Read existing variations from JSON file
			$existingVariations = [];
			if (file_exists($variationFilePath)) {
				$existingVariations = json_decode(file_get_contents($variationFilePath), true);
			}

			// Update existing variations with posted data
			foreach ($productVariations as $variation) {
				// Check if the variation already exists
				if (!array_key_exists($variation, $existingVariations)) {
					$existingVariations[$variation] = ''; // Add new variation with an empty value
				}
			}

			// Remove any keys that are not present in the current form data
			foreach (array_keys($existingVariations) as $existingVariation) {
				if (!in_array($existingVariation, $productVariations)) {
					unset($existingVariations[$existingVariation]);
				}
			}

			// Encode merged variations back to JSON
			$jsonData = json_encode($existingVariations, JSON_PRETTY_PRINT);

			// Save the updated JSON data to the file
			if (file_put_contents($variationFilePath, $jsonData)) {
				// Data saved successfully
				$response = [
					'success' => true,
					'status' => 1,
					'msg' => $msgResponse_success,
				];
			} else {
				// Error occurred while saving
				$response = [
					'success' => false,
					'status' => 2,
					'msg' => $msgResponse_error,
				];
			}
		}

		return $this->response->setJSON($response);
	}

	protected function replace_array_key($arr, $oldkey, $newkey) {
		if (array_key_exists($oldkey, $arr)) {
			$keys = array_keys($arr);
			$keys[array_search($oldkey, $keys)] = $newkey;
			return array_combine($keys, $arr);	
		}
		return $arr;    
	}
	
	public function rename_product_variation_list_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}			

		// Set the response messages
		$msgResponse_validationError = lang('Notifications.error_submitted_details');
		$msgResponse_success = lang('Notifications.success_variation_list_saved');
		$msgResponse_partial_success = lang('Notifications.success_variation_list_saved_partially');
		$msgResponse_error = lang('Notifications.error_saving_variation_list');

		// Validate form data
		// Initialize an empty array to store validation rules for each input
		$validationRules = [];

		// Get the input fields with class 'variation-input'
		$inputFields = $this->request->getPost();

		// Iterate through each input field
		foreach ($inputFields as $inputName => $inputValue) {
			// Define the validation rule for each input dynamically
			$validationRules[$inputName] = 'required'; // Adjust the validation rule as needed
		}

		// Run validation
		if (!$this->validate($validationRules)) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => $msgResponse_validationError,
			];

			return $this->response->setJSON($response);
		} else {

			// Read existing variations from JSON file
			$variationFilePath = $this->userDataPath .  $this->myConfig['userAppSettings'] . 'product-variations.json';
			$existingVariations = [];

			if (file_exists($variationFilePath)) {
				$existingVariations = json_decode(file_get_contents($variationFilePath), true);
			}

			// Update existing variations with posted data
			$log = [];
			foreach ($inputFields as $oldVariationName => $newVariationName) {
				$oldVariationName = str_replace('_', ' ', $oldVariationName);
				// Check if the old variation name exists in the existing variations
				if (array_key_exists($oldVariationName, $existingVariations)) {
					$existingVariations = $this->replace_array_key($existingVariations, $oldVariationName, $newVariationName);
				} else {
					$log[] = $oldVariationName;
				}
			}

			// Save the updated JSON data to the file
			$jsonData = json_encode($existingVariations, JSON_PRETTY_PRINT);

			// Save the updated JSON data to the file
			if (file_put_contents($variationFilePath, $jsonData)) {
				// Data saved successfully
				if (!empty($log)) {
					// make the $log a string
					$logString = implode(", ", $log);

					$response = [
						'success' => true,
						'status' => 2,
						'msg' => $msgResponse_partial_success . $logString,
					];						
				} else {
					$response = [
						'success' => true,
						'status' => 1,
						'msg' => $msgResponse_success,
					];
				}

			} else {
				// Error occurred while saving
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => $msgResponse_error,
				];
			}
		}

		return $this->response->setJSON($response);
	}
	
	protected function productVariations()
	{
		$variationFilePath = $this->userDataPath .  $this->myConfig['userAppSettings'] . 'product-variations.json';
		$variationList = json_decode(file_get_contents($variationFilePath), true);
		
		return $variationList;
	}

	public function public_variations_only()
	{
		$variations = [];
		$variationList = $this->productVariations();
	
		foreach ($variationList as $variation => $productsIncluded) {
			// Trim spaces from the beginning and end of each product name
			$trimmedVariationName = trim($variation);
	
			// Add the trimmed product name to the array
			$variations[] = $trimmedVariationName;
		}
	
		return $this->response->setJSON($variations);
	}

	public function set_product_variations_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}		

		// Set the response messages
		$msgResponse_validationError = lang('Notifications.error_submitted_details');
		$msgResponse_success = lang('Notifications.success_product_variations_saved');
		$msgResponse_error = lang('Notifications.error_variation_list_not_saved');

		$variationFilePath = $this->userDataPath .  $this->myConfig['userAppSettings'] . 'product-variations.json';

		// Read existing variations from JSON file
		$existingVariations = [];
		if (file_exists($variationFilePath)) {
			$existingVariations = json_decode(file_get_contents($variationFilePath), true);
		}

		// Retrieve posted data and replace underscores with spaces in the keys
		$postedData = [];
		foreach ($this->request->getPost() as $key => $value) {
			$key = str_replace('_', ' ', $key); // Replace underscores with spaces
			$postedData[$key] = $value;
		}

		// Update existing variations with posted data
		foreach ($postedData as $key => $value) {
			// Check if the variation exists in the existing variations (with spaces)
			if (array_key_exists($key, $existingVariations)) {
				$existingVariations[$key] = $value; // Update value with posted data
			} else {
				// If the variation doesn't exist, it's a new one, so add it to the existing variations
				$existingVariations[$key] = $value;
			}
		}

		// Encode variations back to JSON
		$jsonData = json_encode($existingVariations, JSON_PRETTY_PRINT);

		// Save the updated JSON data to the file
		if (file_put_contents($variationFilePath, $jsonData)) {
			// Data saved successfully
			$response = [
				'success' => true,
				'status' => 1,
				'msg' => $msgResponse_success,
			];
		} else {
			// Error occurred while saving
			$response = [
				'success' => false,
				'status' => 2,
				'msg' => $msgResponse_error,
			];
		}

		return $this->response->setJSON($response);
	}

	public function public_email_template_list()
	{
		$userID = $this->userID;
		$emailTemplates =[];
		$emailTemplates = getEmailTemplateDetails($userID); // Get all the list of email templates

		$selectedGeneralEmailTemplate = $this->myConfig['selectedEmailTemplate']; // Get the saved general purpose email template

		// Start filtering excluding the general purpose email template
		$filteredTemplates = [];
		foreach ($emailTemplates as $key => $template) {
			$filteredTemplates[$key] = $template;
		}

		if(count($filteredTemplates) !== 0) {
			$response = [
				'success' => true,
				'status' => 1,
				'msg' => $filteredTemplates,
			];
		}
		else {
			$response = [
				'success' => true,
				'status' => 2,
				// 'msg' => 'Server Error: Unable to retrieve the email template list!',
				'msg' => lang('Notifications.no_template_file_not_found'),
			];			
		}
		
		return $this->response->setJSON($response);

	}

	public function public_email_template_config()
	{
		$emailTemplateConfigFilePath = $this->userDataPath .  $this->myConfig['userAppSettings'] . 'product-email-templates.json';
		$emailTemplateConfig = json_decode(file_get_contents($emailTemplateConfigFilePath), true);

		if(count($emailTemplateConfig) !== 0) {
			$response = [
				'success' => true,
				'status' => 1,
				'msg' => json_encode($emailTemplateConfig, JSON_PRETTY_PRINT),
			];
		}
		else {
			$response = [
				'success' => true,
				'status' => 2,
				// 'msg' => 'Server Error: Unable to retrieve the email template list!',
				'msg' => lang('Notifications.email_config_empty'),
			];			
		}
		
		return $this->response->setJSON($response);
	}
	
	public function upload_email_logo_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}
	
		$logoFile = $this->request->getFile('email_logo');
	
		if (!$logoFile || !$logoFile->isValid() || $logoFile->hasMoved()) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.invalid_file_upload')
			]);
		}
	
		$validationRule = [
			'email_logo' => [
				'label' => 'Email logo',
				'rules' => 'uploaded[email_logo]'
					. '|is_image[email_logo]'
					. '|mime_in[email_logo,image/jpg,image/jpeg,image/gif,image/png]'
					. '|max_size[email_logo,2048]'
					. '|max_dims[email_logo,1000,1000]',
			],
		];
	
		if (!$this->validate($validationRule)) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => $this->validator->getErrors()['email_logo']
			]);
		}
	
		$logoPath = USER_DATA_PATH . $this->userID . '/';
		$newName = md5(uniqid(time())) . '.' . $logoFile->getExtension();
	
		if ($logoFile->move($logoPath, $newName, true)) {
			// Delete existing logo file if it exists
			$existingLogo = $this->myConfig['emailLogoFile'] ?? null;
			if($existingLogo) {
				$logoFile = pathinfo($existingLogo, PATHINFO_BASENAME);
				$logoFile = USER_DATA_PATH . $this->userID . '/' . $logoFile;

				if (is_file($logoFile)) {
					unlink($logoFile);
				}
			}

			// Update the user's configuration with the new logo path
			$logoUrl = base_url('user-data/' . $this->userID . '/' . $newName);
			$this->UserSettingsModel->setUserSetting('emailLogoFile', $logoUrl, $this->userID);

			$logoPath = str_replace(base_url(), ROOTPATH, $logoUrl);
			$logoString = '';

			if (!empty($logoPath) && is_file($logoPath)) {
				$mimeType = mime_content_type($logoPath);
				$base64   = base64_encode(file_get_contents($logoPath));
				$logoString = 'data:' . $mimeType . ';base64,' . $base64;
			}
	
			return $this->response->setJSON([
				'success' => true,
				'msg' => lang('Notifications.email_logo_uploaded_successfully'),
				'logoString' => $logoString
			]);
		} else {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.error_uploading_email_logo')
			]);
		}
	}

	public function delete_email_logo_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}

		// Delete existing logo file if it exists
		$logoPath = USER_DATA_PATH . $this->userID . '/';
		$existingLogo = $this->myConfig['emailLogoFile'] ?? '';
	
		if($existingLogo) {
			$logoFile = pathinfo($existingLogo, PATHINFO_BASENAME);
			$logoFile = USER_DATA_PATH . $this->userID . '/' . $logoFile;
			if (is_file($logoFile)) {
				unlink($logoFile);
			}
	
			// Update the user's configuration to remove the logo path
			$this->UserSettingsModel->setUserSetting('emailLogoFile', '', $this->userID);
	
			return $this->response->setJSON([
				'success' => true,
				'msg' => lang('Notifications.email_logo_deleted_successfully')
			]);
		} else {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.email_logo_not_found')
			]);
		}
	}

	public function upload_email_template_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}		

		// Set the response messages
		$msgResponse_validationError = lang('Notifications.error_submitted_details');
		$msgResponse_uploadError = lang('Notifications.error_uploading_email_template_file');
		$msgResponse_duplicateError = lang('Notifications.error_duplicated_email_template');
		$msgResponse_invalidTemplateError = lang('Notifications.error_template_invalid_name');
		$msgResponse_invalidTemplateFolderError = lang('Notifications.error_template_invalid_name2');

		// Validate form data
		$validationRules = [
			'templateFile' => 'uploaded[templateFile]|ext_in[templateFile,zip]', // Only ZIP files allowed
		];

		// Set custom error messages
		$validationMessages = [
			'templateFile' => [
				'uploaded' => lang('Notifications.choose_zip_file'),
				'ext_in' => lang('Notifications.choose_correct_file_format_zip'),
			],
		];

		// Run validation
		if (!$this->validate($validationRules, $validationMessages)) {
			$errors = $this->validator->getErrors();
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => $errors,
			];

			return $this->response->setJSON($response);
		} else {
			// Form data is valid, proceed with further processing
			$templateFile = $this->request->getFile('templateFile');

			// Set the path where to save the uploaded file
			$uploadedFilePath = $this->userDataPath .  $this->myConfig['userEmailTemplatesPath'];

			// Check if the file already exists in the destination folder
			if (is_file($uploadedFilePath . $templateFile->getName())) {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => $msgResponse_duplicateError,
				];

				return $this->response->setJSON($response);
			}

			// Move the uploaded file to the specified directory
			if ($templateFile->move($uploadedFilePath)) {
				// Extract the uploaded zip file
				$zip = new \ZipArchive;
				if ($zip->open($uploadedFilePath . $templateFile->getName()) === TRUE) {
					// Check if the archive content starts with a folder and contains only one parent folder inside
					$firstEntryName = $zip->getNameIndex(0); // Get the name of the first entry in the archive
					$parts = explode('/', $firstEntryName); // Split the first entry's path into segments
					$firstEntryFolder = $parts[0]; // Get the first segment, which represents the folder

					// Check if the first entry is a folder
					if (!empty($firstEntryFolder)) {
						// Initialize a counter for parent folders
						$parentFolders = [];

						// Loop through all entries in the archive
						for ($i = 0; $i < $zip->numFiles; $i++) {
							$entryName = $zip->getNameIndex($i); // Get the name of each entry
							$parts = explode('/', $entryName); // Split the entry's path into segments
							$parentFolder = $parts[0]; // Get the first segment, which represents the parent folder

							// If the parent folder is not already counted, add it to the counter
							if (!in_array($parentFolder, $parentFolders)) {
								// Check if the parent folder name contains only alphanumeric characters, dashes, and underscores
								if (!preg_match('/^[a-zA-Z0-9_-]+$/', $parentFolder)) {
									// Invalid parent folder name found
									$zip->close();
									unlink($uploadedFilePath . $templateFile->getName()); // Delete the uploaded ZIP file
									$response = [
										'success' => false,
										'status' => 0,
										'msg' => $msgResponse_invalidTemplateFolderError,
									];
									return $this->response->setJSON($response);
								}

								$parentFolders[] = $parentFolder;
							}
						}

						// Check if there is only one parent folder inside the archive
						if (count($parentFolders) == 1) {
							// No invalid file names found, proceed with extraction
							$zip->extractTo($uploadedFilePath);
							$zip->close();

							// Delete the uploaded zip file
							unlink($uploadedFilePath . $templateFile->getName());

							if(updateEmailTemplateDetails($this->userID)) {
								// Data saved successfully
								$response = [
									'success' => true,
									'status' => 1,
									'msg' => lang('Notifications.success_email_template_uploaded'),
								];
							}
							else {
								// Error occurred while saving
								$response = [
									'success' => false,
									'status' => 0,
									'msg' => $msgResponse_uploadError,
								];
							}
						} else {
							// The archive does not meet the specified conditions
							$zip->close();
							unlink($uploadedFilePath . $templateFile->getName()); // Delete the uploaded ZIP file
							$response = [
								'success' => false,
								'status' => 0,
								'msg' => lang('Notifications.error_email_template_duplicated_parent'),
							];
						}
					} else {
						// The archive does not start with a folder
						$zip->close();
						unlink($uploadedFilePath . $templateFile->getName()); // Delete the uploaded ZIP file
						$response = [
							'success' => false,
							'status' => 0,
							'msg' => lang('Notifications.error_must_start_folder'),
						];
					}
				} else {
					$response = [
						'success' => false,
						'status' => 0,
						'msg' => lang('Notifications.error_unzipping'),
					];
				}
			} else {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => $msgResponse_uploadError,
				];
			}

			return $this->response->setJSON($response);
		}
	}

    public function delete_email_templates_action()
    {
        $this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}		

		// Get selected files to delete
		$selectedTemplates = trim($this->request->getPost('selectedTemplates'));

		$emailTemplateRoot = $this->userDataPath .  $this->myConfig['userEmailTemplatesPath']; // Add missing semicolon

		if (!empty($selectedTemplates)) {
			$deletedFolders = [];
			$failedFolders = [];

			if(strpos($selectedTemplates, ',') !== false) {
				$selectedTemplates = explode(",", $selectedTemplates);
			}

			if (is_array($selectedTemplates)) {
				// Process multiple selected files
				foreach ($selectedTemplates as $folderName) {
					if ($this->deleteTemplateFolder($emailTemplateRoot . $folderName)) {
						$deletedFolders[] = $folderName;
					} else {
						$failedFolders[] = $folderName;
					}
					
					$this->unsetArrayKey($folderName, $this->userDataPath .  $this->myConfig['userAppSettings'] . 'product-email-templates.json');
				}
			} else {
				// Process single selected file
				if ($this->deleteTemplateFolder($emailTemplateRoot . $selectedTemplates)) {
					$deletedFolders[] = $selectedTemplates;
				} else {
					$failedFolders[] = $selectedTemplates;
				}
				
				$this->unsetArrayKey($selectedTemplates, $this->userDataPath .  $this->myConfig['userAppSettings'] . 'product-email-templates.json');
			}
			
			// Save the updated JSON data to the file
			if (!empty($deletedFolders) && updateEmailTemplateDetails($this->userID)) {
				// Data saved successfully
				$response = [
					'success' => true,
					'status' => 1,
					'msg' => lang('Notifications.success_deleted_email_templates'),
					'deleted_folders' => $deletedFolders,
					'failed_folders' => $failedFolders,
				];
			} else {
				// Error occurred while saving
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.error_deleting_email_templates'),
					'deleted_folders' => $deletedFolders,
					'failed_folders' => $failedFolders,
				];
			}
		} else {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_no_folder_found'),
				'deleted_folders' => [],
				'failed_folders' => [],
			];
		}

		return $this->response->setJSON($response);
    }

	private function unsetArrayKey($arrayKeyToUnset, $jsonPath)
	{
		$decodedJSON = []; // Initialize as an empty array
		if (file_exists($jsonPath)) {
			$decodedJSON = json_decode(file_get_contents($jsonPath), true);
		} else {
			// Handle the case where the file doesn't exist
			return;
		}
	
		foreach ($decodedJSON as $key => $value) {
			if ($arrayKeyToUnset === $key) {
				unset($decodedJSON[$key]);
			}
		}
	
		// Save the modified data back to the JSON file if needed
		file_put_contents($jsonPath, json_encode($decodedJSON));
	} 
    
    private function deleteTemplateFolder($folderPath)
    {
        // Check if the folder exists before attempting to delete
        if (is_dir($folderPath)) {
            // Attempt to delete the folder recursively
            if (deleteDirectory($folderPath)) {
                return true; // Folder and its contents successfully deleted
            } else {
                log_message('error', '[Home] ' . lang('Notifications.error_failed_to_delete_folder', ['folderPath' => $folderPath])); // Log deletion failure
                return false; // Failed to delete folder and its contents
            }
        } else {
            log_message('error', '[Home] ' . lang('Notifications.error_folder_doesnt_exists', ['folderPath' => $folderPath])); // Log folder not found
            return false; // Folder does not exist
        }
    }

	public function set_product_email_templates_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}
	
		// Set the response messages
		$msgResponse_validationError = lang('Notifications.error_submitted_details');
		$msgResponse_success = lang('Notifications.success_product_email_template_saved');
		$msgResponse_error = lang('Notifications.error_product_email_template_saving');

		$emailTemplateConfigFilePath = $this->userDataPath .  $this->myConfig['userAppSettings'] . 'product-email-templates.json';

		// Read existing email template config from JSON file
		$existingSetup = [];
		if (file_exists($emailTemplateConfigFilePath)) {
			$existingSetup = json_decode(file_get_contents($emailTemplateConfigFilePath), true);
		}

		// Retrieve posted data
		$postedData = $this->request->getPost();

		// Get the keys present in the posted data
		$postedKeys = array_keys($postedData);

		// Remove keys that are not present in the posted data
		foreach ($existingSetup as $key => $value) {
			if (!in_array($key, $postedKeys)) {
				unset($existingSetup[$key]);
			}
		}

		// Update existing email template config with posted data
		foreach ($postedData as $key => $value) {
			// Check if the variation exists in the existing email template config
			$existingSetup[$key] = $value;
		}

		// Encode email template config back to JSON
		$jsonData = json_encode($existingSetup, JSON_PRETTY_PRINT);

		// Save the updated JSON data to the file
		if (file_put_contents($emailTemplateConfigFilePath, $jsonData)) {
			// Data saved successfully
			$response = [
				'success' => true,
				'status' => 1,
				'msg' => $msgResponse_success,
			];
		} else {
			// Error occurred while saving
			$response = [
				'success' => false,
				'status' => 2,
				'msg' => $msgResponse_error,
			];
		}

		return $this->response->setJSON($response);
	}
	
	public function app_settings_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged in before proceeding

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}		
	
		$responseArray = [];
		// Set the response messages
		$msgResponse_fileSavingError = lang('Notifications.error_saving_uploaded_files');

		// Initialize variables that may be used in conditional blocks
		$dataLicenseCredentials = [];

		// Validate form data
		$validationRules = [
			'acceptedFileExtensions'=> 'required',
		];

		// Set custom error messages
		$validationMessages = [
			'acceptedFileExtensions' => [
				'required' => lang('Pages.file_extension_required'),
			],
		];

		// Run validation
		if (!$this->validate($validationRules, $validationMessages)) {
			$errors = $this->validator->getErrors();
			$responseArray['inputs'] = [
				'success' => false,
				'status' => 0,
				'msg' => $errors,
			];				
			return $this->response->setJSON($responseArray);
		} else {
			$isLicensePrefixEnabled = $this->subscriptionChecker->isFeatureEnabled($this->userID, 'License_Prefix');
        	$isLicenseSuffixEnabled = $this->subscriptionChecker->isFeatureEnabled($this->userID, 'License_Suffix');
			$isEnvatoSyncEnabled = $this->subscriptionChecker->isFeatureEnabled($this->userID, 'Envato_Sync');

			$data = [
				// Admin
				'userCompanyName'         			=> trim($this->request->getPost('userCompanyName')),
				'userCompanyAddress'         		=> trim($this->request->getPost('userCompanyAddress')),
				'defaultTimezone'         			=> trim($this->request->getPost('defaultTimezone')),
				'defaultLocale'         			=> trim($this->request->getPost('defaultLocale')),
				'defaultTheme'         				=> trim($this->request->getPost('defaultTheme')),

				// General
				'acceptedFileExtensions'         	=> trim($this->request->getPost('acceptedFileExtensions')),

				// WooCommerce Addon Option
				'woocommerceServerDomain' 			=> trim($this->request->getPost('woocommerceServerDomain')),

				// Envato Author
				'userEnvatoSyncEnabled'    	  		=> $isEnvatoSyncEnabled ? trim($this->request->getPost('userEnvatoSyncEnabled')) : NULL,
				'userEnvatoAPIKey'    	  			=> $isEnvatoSyncEnabled ? trim($this->request->getPost('userEnvatoAPIKey')) : NULL,
			];
			
			// Prepare the file extension list data to be saved
			$acceptedFileExtensions = explode("\n", $data['acceptedFileExtensions']);
			
			$data['acceptedFileExtensions'] = json_encode($acceptedFileExtensions);

			/**
			 * Handle each input and save in the database
			 */
			foreach($data as $key => $value) {
				$this->UserSettingsModel->setUserSetting($key, $value, $this->userID);
			}				

			// Validate and save data depending on the license manager set
			$SLMselection = $this->request->getPost('licenseManagerOnUse');

			if($SLMselection === 'slm') {
				
				$validationRulesSLM = [
					'licenseServerDomain'   => 'required|valid_url_strict[https]',
					'licenseServer_Validate_SecretKey'   => 'required',
					'licenseServer_Create_SecretKey'   => 'required',
				];
		
				// Set custom error messages
				$validationMessagesSLM = [
					'licenseServerDomain' => [
						'required' => lang('Notifications.required_input_empty'),
						'valid_url_strict' => lang('Notifications.required_slm_url_should_use_https'),
					],
					'licenseServer_Validate_SecretKey' => [
						'required' => lang('Notifications.required_input_empty'),
					],
					'licenseServer_Create_SecretKey' => [
						'required' => lang('Notifications.required_input_empty'),
					],
				];
	
				// Run validation
				if (!$this->validate($validationRulesSLM, $validationMessagesSLM)) {
					$errors = $this->validator->getErrors();
					$responseArray['inputs'] = [
						'success' => false,
						'status' => 0,
						'msg' => $errors,
					];				
					return $this->response->setJSON($responseArray);
				} else {
					$dataLicenseCredentials = [
						'licenseServerDomain'         		=> trim($this->request->getPost('licenseServerDomain')),
						'licenseServer_Validate_SecretKey'  => trim($this->request->getPost('licenseServer_Validate_SecretKey')),
						'licenseServer_Create_SecretKey'    => trim($this->request->getPost('licenseServer_Create_SecretKey')),
						// 'selectedEmailTemplate'    	  		=> '',
					];
				}
			}
			// Built-in License Manager Settings
			else {
				$validationRulesBuiltin = [
					'License_Validate_SecretKey'   => 'required',
					'License_Create_SecretKey'   	=> 'required',
					'License_DomainDevice_Registration_SecretKey'   	=> 'required',
					'Manage_License_SecretKey'   	=> 'required',
					'General_Info_SecretKey'   	=> 'required',
					// 'selectedEmailTemplate'			=> 'required'
				];
		
				// Set custom error messages
				$validationMessagesBuiltin = [
					'License_Validate_SecretKey' => [
						'required' => lang('Notifications.required_input_empty'),
					],
					'License_Create_SecretKey' => [
						'required' => lang('Notifications.required_input_empty'),
					],
					'License_DomainDevice_Registration_SecretKey' => [
						'required' => lang('Notifications.required_input_empty'),
					],
					'Manage_License_SecretKey' => [
						'required' => lang('Notifications.required_input_empty'),
					],
					'General_Info_SecretKey' => [
						'required' => lang('Notifications.required_input_empty'),
					],
					// 'selectedEmailTemplate' => [
					// 	'required' => lang('Notifications.error_license_email_template'),
					// ],
				];
	
				// Run validation
				if (!$this->validate($validationRulesBuiltin, $validationMessagesBuiltin)) {
					$errors = $this->validator->getErrors();
					$responseArray['inputs'] = [
						'success' => false,
						'status' => 0,
						'msg' => $errors,
					];				
					return $this->response->setJSON($responseArray);
				} else {
					$dataLicenseCredentials = [
						'License_Validate_SecretKey'  => trim($this->request->getPost('License_Validate_SecretKey')),
						'License_Create_SecretKey'    => trim($this->request->getPost('License_Create_SecretKey')),
						
						'License_DomainDevice_Registration_SecretKey'    => trim($this->request->getPost('License_DomainDevice_Registration_SecretKey')),
						
						'Manage_License_SecretKey'    => trim($this->request->getPost('Manage_License_SecretKey')),
						
						'General_Info_SecretKey'    => trim($this->request->getPost('General_Info_SecretKey')),
						'autoExpireLicenseKeys'    	  => $this->request->getPost('autoExpireLicenseKeys'),
						'defaultAllowedDomains'    	  => trim($this->request->getPost('defaultAllowedDomains')),
						'defaultAllowedDevices'    	  => trim($this->request->getPost('defaultAllowedDevices')),
						'default_license_status'	  => trim($this->request->getPost('default_license_status')),
						'defaultTrialDays'			  => trim($this->request->getPost('defaultTrialDays')),
					];
				}
			}

			/**
			 * Handle each input and save in the database
			 */
			$dataLicenseOptions = [
				'licenseManagerOnUse'         	=> trim($this->request->getPost('licenseManagerOnUse')),
				'licensePrefix'         		=> $isLicensePrefixEnabled ? trim($this->request->getPost('licensePrefix')) : NULL,
				'licenseSuffix'         		=> $isLicenseSuffixEnabled ? trim($this->request->getPost('licenseSuffix')) : NULL,
				'licenseKeyCharsCount'         	=> trim($this->request->getPost('licenseKeyCharsCount')),
			];

			// Merge arrays into $dataLicenseManagement
			$dataLicenseManagement = array_merge($dataLicenseCredentials, $dataLicenseOptions);

			foreach($dataLicenseManagement as $key => $value) {
				$this->UserSettingsModel->setUserSetting($key, $value, $this->userID);
			}

			$responseArray['inputs'] = [
				'success' => true,
				'status' => 1,
				'msg' => lang('Notifications.success_app_settings_saved'),
			];
		}

		// Clear the cache
		clearCache();
					
		return $this->response->setJSON($responseArray);
	}

	public function testEmailSending()
	{
		$this->checkIfLoggedIn(); // Check if user is logged in before proceeding
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}		
	
		// Validate form data
		$validationRules = [
			'testToEmail'       => 'required|valid_email',
			'testSubjectEmail'  => 'required',
			'testBodyEmail'     => 'required',
		];
	
		// Set custom error messages
		$validationMessages = [
			'testToEmail' => [
				'required' => lang('Pages.test_From_Email_Required'),
				'valid_email' => lang('Pages.test_From_Email_Required'),
			],                 
			'testSubjectEmail' => [
				'required' => lang('Pages.test_Subject_Email_Required'),
			],
			'testBodyEmail' => [
				'required' => lang('Pages.test_Body_Email_Required'),
			],
		];
	
		// Run validation
		if (!$this->validate($validationRules, $validationMessages)) {
			$errors = $this->validator->getErrors();
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => $errors,
			];
	
			return $this->response->setJSON($response);
		} else {
			// Form data is valid, proceed with further processing
			$postedData = $this->request->getPost();
			$fromEmail = getMyConfig('', 0)['fromEmail'];
			$toEmail = $postedData['testToEmail'];
			$emailSubject = $postedData['testSubjectEmail'];
			$emailBody = $postedData['testBodyEmail'];
			$emailFormat = isset($postedData['testEmailFormat']) ? 'html' : 'text';
			
			try {
				$emailService = new \App\Libraries\EmailService();
				$licenseNotificationResult = $emailService->sendGeneralEmail([
					'template' => 'test_email',
					'userID' => $this->userID,
					'email_format' => $emailFormat,
					'recipient_email' => $toEmail,
					'subject' => $emailSubject,
					'message' => $emailBody
				]);
			
				if ($licenseNotificationResult['success']) {
					log_message('info', '[Home] ' . $licenseNotificationResult['message']);
					$response = [
						'success' => true,
						'status' => 1,
						'msg' => $licenseNotificationResult['message'],
					];
				} else {
					log_message('error', '[Home] ' . $licenseNotificationResult['message']);
					$response = [
						'success' => $licenseNotificationResult['success'],
						'status' => 0,
						'msg' => $licenseNotificationResult['message'],
					];
				}
			} catch (\Exception $e) {
				log_message('error', '[Home] Exception occurred while sending test email: ' . $e->getMessage());
				log_message('debug', '[Home] Error details: ' . $e->getTraceAsString());
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.error_test_email_sent') . ': ' . $e->getMessage(),
				];
			}
	
			return $this->response->setJSON($response);
		}
	}

	public function app_settings_delete_cookies()
	{
		$this->checkIfLoggedIn(); // Check if user is logged in before proceeding
	
		// Delete color_scheme cookie if it exists
		if(isset($_COOKIE["color_scheme"])) {
			setcookie("color_scheme", "", time() - 3600, '/');
		}
		
		// Delete theme cookie if it exists
		if(isset($_COOKIE['theme'])) {
			setcookie('theme', '', time() - 3600, '/');
		}
	
		$response = [
			'success' => true,
			'status' => 1,
			'msg' => lang('Notifications.success_delete_cookies'),
		];			
	
		return $this->response->setJSON($response);
	}

	public function app_settings_generate_new_key($setting, $length = NULL)
	{
		$this->checkIfLoggedIn(); // Check if user is logged in before proceeding

		$newSecretKey = generateApiKey($length);

		if($newSecretKey) {
			$response = [
				'success' => true,
				'status' => 1,
				'msg' => $newSecretKey
			];
		}
		else {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_processing_request')
			];
		}

		return $this->response->setJSON($response);
	}

	private function addFolderToZip($folderPath, \ZipArchive $zip, $rootFolder)
	{
		$folderPath = rtrim($folderPath, '/') . '/';
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($folderPath),
			\RecursiveIteratorIterator::LEAVES_ONLY
		);
	
		foreach ($files as $file) {
			if (!$file->isDir()) {
				// Get the relative path for the zip
				$filePath = $file->getRealPath();
				$relativePath = $rootFolder . '/' . substr($filePath, strlen($folderPath));
				// Add the file to the zip archive
				$zip->addFile($filePath, $relativePath);
			}
		}
	}

	protected function cleanupOldTemporaryFiles()
	{
		$temporary_folder = WRITEPATH . 'temp/';
		if (!is_dir($temporary_folder) || !is_writable($temporary_folder)) {
			log_message('error', '[Home] Temporary folder is not accessible or writable: ' . $temporary_folder);
			return;
		}
	
		// Get all zip files in the temporary folder
		$files = glob($temporary_folder . '/*.zip');
		
		// Current timestamp
		$now = time();
		
		// Delete files older than 1 hour (3600 seconds)
		foreach ($files as $file) {
			if (is_file($file)) {
				if ($now - filemtime($file) > 3600) {
					@unlink($file);
				}
			}
		}
	}

    public function delete_user_account_action()
    {
        $this->checkIfLoggedIn(); // Check if user is logged in before proceeding
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}		
	
		// Validate form data
		$validationRules = [
			'userEmail'       => 'required|valid_email',
		];
	
		// Set custom error messages
		$validationMessages = [
			'userEmail' => [
				'required' => lang('Notifications.enter_email_to_delete_account'),
				'valid_email' => lang('Notifcations.enter_valid_email_to_delete'),
			],
		];
	
		// Run validation
		if (!$this->validate($validationRules, $validationMessages)) {
			$errors = $this->validator->getErrors();
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => $errors,
			];
	
			return $this->response->setJSON($response);
		} else {

			if($this->userAcctDetails->getEmail() !== trim($this->request->getPost('userEmail')) ) {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.email_doesnt_match'),
				];
		
				return $this->response->setJSON($response);
			}

			try {
				if ($this->UserModel->softDeleteUser($this->userID)) {
					// Add notification for new blocked IP
                    $notificationMessage = "{$this->userAcctDetails->username} (ID: {$this->userID}) has inititated account deletion";
                    $notificationType = 'user_deletion';
                    $url = base_url('admin-options/user-manager');
                    $recipientUserId = 1;
                    add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

					return $this->response->setJSON([
						'success' => true,
						'status' => 1,
						'msg' => lang('Notifications.your_account_successfuly_deleted')
					]);
				} else {
					return $this->response->setJSON([
						'success' => false,
						'status' => 0,
						'msg' => lang('Notifications.error_deleting_user'),
						'errors' => $this->UserModel->getLastErrors()
					]);
				}
			} catch (\Exception $e) {
				log_message('error', '[Home] User Deletion Error: ' . $e->getMessage());
				return $this->response->setJSON([
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.error_deleting_user'),
					'errors' => ['general' => $e->getMessage()]
				]);
			}
		}
    }
}