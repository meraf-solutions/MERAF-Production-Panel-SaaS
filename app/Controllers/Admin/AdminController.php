<?php

namespace App\Controllers\Admin;

use CodeIgniter\I18n\Time;
use App\Controllers\Home;
use App\Models\IpBlockModel;
use App\Models\ModuleCategoryModel;
use App\Models\PackageModulesModel;

class AdminController extends Home
{

	protected $IpBlockModel;

    public function __construct()
    {
        parent::__construct();

		// Initialize models
		$this->IpBlockModel = new IpBlockModel();
		$this->ModuleCategoryModel = new ModuleCategoryModel();
    	$this->PackageModulesModel = new PackageModulesModel();
    }

	protected function checkAdminAuthorization()
	{
		// Check if user is logged in and in admin group
		if (!$this->userAcctDetails || !$this->userAcctDetails->inGroup('admin')) {
			return redirect()->to('forbidden')->with('error', lang('Pages.forbidden_error_msg'));
		}
		return true;
	}

    public function global_settings_page()
    {
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		$data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Setup') . ' | ' . lang('Pages.global_settings');
		$data['section'] = 'Setup';
		$data['subsection'] = 'global_settings';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;

        return view('dashboard/admin/global_settings', $data);
    }

	public function global_settings_action()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}		
	
		$responseArray = [];
		// Set the response messages
		$msgResponse_fileSavingError = lang('Notifications.error_saving_uploaded_files');

		// Validate form data
		$validationRules = [
			'appName' 				=> 'required|alpha_numeric_punct',
			'companyName' 			=> 'required',
			'companyAddress' 		=> 'required',
			'packageCurrency'		=> 'required',
		];

		// Set custom error messages
		$validationMessages = [
			'appName' => [
				'required' => lang('Notifications.required_input_empty'),
			],
			'companyName' => [
				'required' => lang('Notifications.required_input_empty'),
			],
			'companyAddress' => [
				'required' => lang('Notifications.required_input_empty'),
			],
			'packageCurrency' => [
				'required' => lang('Notifications.error_select_currency'),
			],
		];

		// Validate PWA requirements
		$pwaEnabled = $this->request->getPost('PWA_App_enabled');

		// If PWA is enabled, add PWA field validations
		if (!empty($pwaEnabled)) {
			$fcmFields = [
				'PWA_App_shortname',
			];
			
			foreach ($fcmFields as $field) {
				$validationRules[$field] = 'required|alpha_numeric';
				$validationMessages[$field] = [
					'required'      => lang('Notifications.pwaAppShortnameMissing'),
					'alpha_numeric' => lang('Notifications.pwaAppShortnameInvalid'), // Add this language key
				];
			}
		}

		// Validate FCM credentials if needed
		$pushEnabled = $this->request->getPost('push_notification_feature_enabled');

		// If push notifications are enabled, add FCM field validations
		if (!empty($pushEnabled)) {
			$fcmFields = [
				'fcm_apiKey',
				'fcm_authDomain',
				'fcm_projectId',
				'fcm_storageBucket',
				'fcm_messagingSenderId',
				'fcm_appId',
				'fcm_measurementId',
				'fcm_vapidKey'
			];
			
			foreach ($fcmFields as $field) {
				$validationRules[$field] = 'required';
				$validationMessages[$field]['required'] = lang('Pages.fcm_field_required_when_enabled');
			}

			if(!$this->myConfig['fcm_private_key_file']) {
			    $responseArray['inputs'] = [
    				'success' => false,
    				'status' => 0,
    				'msg' => [ 
						'fcm_private_key_file' => lang('Notifications.private_key_file_missing')
            		]
    			];				
    			return $this->response->setJSON($responseArray);
			}
		}

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

			$data = [
				// Super Admin
				'appName'         					=> trim($this->request->getPost('appName')),
				'companyName'         				=> trim($this->request->getPost('companyName')),
				'companyAddress'         			=> trim($this->request->getPost('companyAddress')),
				'preloadEnabled'         			=> $this->request->getPost('preloadEnabled'),
				'reCAPTCHA_enabled'    	  			=> $this->request->getPost('reCAPTCHA_enabled'),
				'reCAPTCHA_Site_Key'    	  		=> trim($this->request->getPost('reCAPTCHA_Site_Key')),
				'reCAPTCHA_Secret_Key'    	  		=> trim($this->request->getPost('reCAPTCHA_Secret_Key')),				
				'defaultTimezone'         			=> trim($this->request->getPost('defaultTimezone')),
				'defaultLocale'         			=> trim($this->request->getPost('defaultLocale')),
				'defaultTheme'         				=> trim($this->request->getPost('defaultTheme')),
				'packageCurrency'         			=> trim($this->request->getPost('packageCurrency')),
				'PWA_App_enabled'    	  			=> $this->request->getPost('PWA_App_enabled'),
				'PWA_App_name'    	  				=> trim($this->request->getPost('PWA_App_name')),
				'PWA_App_shortname'    	  			=> trim($this->request->getPost('PWA_App_shortname')),
				'push_notification_feature_enabled' => $this->request->getPost('push_notification_feature_enabled'),
				'fcm_apiKey'						=> trim($this->request->getPost('fcm_apiKey')),
				'fcm_authDomain'					=> trim($this->request->getPost('fcm_authDomain')),
				'fcm_projectId'						=> trim($this->request->getPost('fcm_projectId')),
				'fcm_storageBucket'					=> trim($this->request->getPost('fcm_storageBucket')),
				'fcm_messagingSenderId'				=> trim($this->request->getPost('fcm_messagingSenderId')),
				'fcm_appId'							=> trim($this->request->getPost('fcm_appId')),
				'fcm_measurementId'					=> trim($this->request->getPost('fcm_measurementId')),
				'fcm_vapidKey'						=> trim($this->request->getPost('fcm_vapidKey')),
			];	

			/**
			 * Handle each input and save in the database
			 */
			foreach($data as $key => $value) {
				$this->UserSettingsModel->setUserSetting($key, $value, 0);
			}

			/**
			 * Start: Handle the Cache settings
			 */
			$data['cacheHandler'] = $this->request->getPost('cacheHandler');

    			if($data['cacheHandler'] !== $this->myConfig['cacheHandler']) {
    			    
    			    if ($data['cacheHandler'] === 'memcached') {
    				$memcachedSettings = [
    					'host' => $this->request->getPost('memcached_host'),
    					'port' => (int)$this->request->getPost('memcached_port'),
    					'weight' => (int)$this->request->getPost('memcached_weight'),
    					'raw' => (bool)$this->request->getPost('memcached_raw'),
    				];
    				
    				$this->UserSettingsModel->setUserSetting('memcached_host', $memcachedSettings['host'], 0);
    				$this->UserSettingsModel->setUserSetting('memcached_port', $memcachedSettings['port'], 0);
    				$this->UserSettingsModel->setUserSetting('memcached_weight', $memcachedSettings['weight'], 0);
    				$this->UserSettingsModel->setUserSetting('memcached_raw', $memcachedSettings['raw'], 0);
    				
    				// Update the Cache.php file with memcached settings
    				$this->updateMemcachedConfig($memcachedSettings);
    			} else if ($data['cacheHandler'] === 'redis') {
    				$redisSettings = [
    					'host' => $this->request->getPost('redis_host'),
    					'port' => (int)$this->request->getPost('redis_port'),
    					'password' => $this->request->getPost('redis_password'),
    					'timeout' => (int)$this->request->getPost('redis_timeout'),
    					'database' => (int)$this->request->getPost('redis_database'),
    				];
    				
    				$this->UserSettingsModel->setUserSetting('redis_host', $redisSettings['host'], 0);
    				$this->UserSettingsModel->setUserSetting('redis_port', $redisSettings['port'], 0);
    				$this->UserSettingsModel->setUserSetting('redis_password', $redisSettings['password'], 0);
    				$this->UserSettingsModel->setUserSetting('redis_timeout', $redisSettings['timeout'], 0);
    				$this->UserSettingsModel->setUserSetting('redis_database', $redisSettings['database'], 0);
    				
    				// Update the Cache.php file with redis settings
    				$this->updateRedisConfig($redisSettings);
    			}
			    
				// Check dependencies for the selected cache handler
				$dependencyError = $this->checkCacheDependencies($data['cacheHandler']);

				if ($dependencyError) {
					$responseArray['cacheHandler'] = [
						'success' => false,
						'status' => 0,
						'msg' => $dependencyError . "\n" . lang('Notifications.reverting_back_to_previous_cache_handler', ['previousCacheHandler' => ucfirst($this->myConfig['cacheHandler'])]),
						'errorElement' => 'cacheHandler',
					];

					// Error occured after check for dependency. Save the default cache handler 'file'
					$this->UserSettingsModel->setUserSetting('cacheHandler', $this->myConfig['cacheHandler'], 0);

					// return $this->response->setJSON($responseArray);
				}
				else {
					// Save the cache handler setting after checking if everything is okay and no error
					$this->UserSettingsModel->setUserSetting('cacheHandler',  $data['cacheHandler'], 0);
				}			

				// Update the Cache.php file
				$this->updateCacheConfig($data['cacheHandler']);
			}
			/**
			 * End: Handle the Cache settings
			 */
			
			$responseArray['inputs'] = [
				'success' => true,
				'status' => 1,
				'msg' => lang('Notifications.success_global_settings_saved'),
			];
		}

		/***
		 * Handle file uploads
		 *  */ 
		$uploadedFiles = [];

		// Validate and handle appLogo_light upload
		$appLogo_light = $this->request->getFile('appLogo_light');
		if ($appLogo_light->isValid()) {

			$validationRulesImage = [
				'appLogo_light' => 'uploaded[appLogo_light]|mime_in[appLogo_light,image/jpg,image/jpeg,image/png]',
			];

			$validationMessagesImages = [
				'appLogo_light' => [
					'mime_in' => lang('Notifications.choose_correct_logo_format'),
				],
			];

			if (!$this->validate($validationRulesImage, $validationMessagesImages)) {
				$errors = $this->validator->getErrors();
				$responseArray['appLogo_light'] = [
					'success' => false,
					'status' => 0,
					'msg' => $errors,
					'errorElement' => 'appLogo_light',
				];
				return $this->response->setJSON($responseArray);
			}

			// Get the dimensions of the uploaded image
			$imageSize = getimagesize($appLogo_light->getPathname());
			$imageWidth = $imageSize[0];
			$imageHeight = $imageSize[1];	
			
			// Check if the image dimensions are within 400x400
			if ($imageHeight > 60) {
				$responseArray['appLogo_light'] = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.choose_correct_logo_pixel'),
					'errorElement' => 'appLogo_light',
				];

				return $this->response->setJSON($responseArray);
			}				

			// Handle appLogo_light upload
			$uploadedFiles[] = $this->handleFileUpload($appLogo_light, 'appLogo_light');
		}

		// Validate and handle appLogo_dark upload
		$appLogo_dark = $this->request->getFile('appLogo_dark');
		if ($appLogo_dark->isValid()) {

			$validationRulesImage = [
				'appLogo_dark' => 'uploaded[appLogo_dark]|mime_in[appLogo_dark,image/jpg,image/jpeg,image/png]',
			];

			$validationMessagesImages = [
				'appLogo_dark' => [
					'mime_in' => lang('Notifications.choose_correct_logo_format'),
				],
			];

			if (!$this->validate($validationRulesImage, $validationMessagesImages)) {
				$errors = $this->validator->getErrors();
				$responseArray['appLogo_dark'] = [
					'success' => false,
					'status' => 0,
					'msg' => $errors,
					'errorElement' => 'appLogo_dark',
				];
				return $this->response->setJSON($responseArray);
			}

			// Get the dimensions of the uploaded image
			$imageSize = getimagesize($appLogo_dark->getPathname());
			$imageWidth = $imageSize[0];
			$imageHeight = $imageSize[1];	
			
			// Check if the image dimensions are within 400x400
			if ($imageHeight > 60) {
				$responseArray['appLogo_dark'] = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.choose_correct_logo_pixel'),
					'errorElement' => 'appLogo_dark',
				];

				return $this->response->setJSON($responseArray);
			}				

			// Handle appLogo_dark upload
			$uploadedFiles[] = $this->handleFileUpload($appLogo_dark, 'appLogo_dark');
		}			

		// Validate and handle appIcon upload			
		$appIcon = $this->request->getFile('appIcon');
		if ($appIcon->isValid()) {					

			$validationRulesImage = [
				'appIcon' => 'uploaded[appIcon]|mime_in[appIcon,image/jpg,image/jpeg,image/png]',
			];

			$validationMessagesImages = [
				'appIcon' => [
					'mime_in' => lang('Notifications.choose_correct_icon_format'),
				],
			];

			if (!$this->validate($validationRulesImage, $validationMessagesImages)) {
				$errors = $this->validator->getErrors();
				$responseArray['appIcon'] = [
					'success' => false,
					'status' => 0,
					'msg' => $errors,
					'errorElement' => 'appIcon',
				];
				return $this->response->setJSON($responseArray);
			}

			// Get the dimensions of the uploaded image
			$imageSize = getimagesize($appIcon->getPathname());
			$imageWidth = $imageSize[0];
			$imageHeight = $imageSize[1];	
			
			// Check if the image dimensions are within 400x400
			if ($imageWidth > 256 || $imageHeight > 256) {
				$responseArray['appIcon'] = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.choose_correct_icon_pixel'),
					'errorElement' => 'appIcon',
				];

				return $this->response->setJSON($responseArray);
			}				

			// Handle appIcon upload
			$uploadedFiles[] = $this->handleFileUpload($appIcon, 'appIcon');
		}

		// Validate and handle PWA_App_icon_192x192 upload
		$PWA_App_icon_192x192 = $this->request->getFile('PWA_App_icon_192x192');
		if ($PWA_App_icon_192x192->isValid()) {

			$validationRulesImage = [
				'PWA_App_icon_192x192' => [
				'label' => 'PWA App icon 192x192',
				'rules' => 'uploaded[PWA_App_icon_192x192]'
					. '|is_image[PWA_App_icon_192x192]'
					. '|mime_in[PWA_App_icon_192x192,image/jpg,image/jpeg,image/png,image/svg,image/webp]'
					. '|max_size[PWA_App_icon_192x192,192]'
					. '|max_dims[PWA_App_icon_192x192,192,192]'
					. '|min_dims[PWA_App_icon_192x192,192,192]',
				]
			];

			$validationMessagesImages = [
				'PWA_App_icon_192x192' => [
					'is_image'  => lang('Notifications.field_valid_image_file'),
					'mime_in'   => lang('Notifications.valid_pwa_icon_format'),
					'max_size'  => lang('Notifications.pwa_icon_max_size'),
					'max_dims'  => lang('Notifications.pwa_icon_192x192_max_dims'),
					'min_dims'  => lang('Notifications.pwa_icon_192x192_max_dims'),
				],
			];

			if (!$this->validate($validationRulesImage, $validationMessagesImages)) {
				$errors = $this->validator->getErrors();
				$responseArray['PWA_App_icon_192x192'] = [
					'success' => false,
					'status' => 0,
					'msg' => $errors,
					'errorElement' => 'PWA_App_icon_192x192',
				];
				return $this->response->setJSON($responseArray);
			}

			// Get the dimensions of the uploaded image
			$imageSize = getimagesize($PWA_App_icon_192x192->getPathname());
			$imageWidth = $imageSize[0];
			$imageHeight = $imageSize[1];	
			
			// Check if the image dimensions are within 400x400
			if ($imageWidth > 192 || $imageHeight > 192) {
				$responseArray['PWA_App_icon_192x192'] = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.choose_correct_icon_pixel'),
					'errorElement' => 'PWA_App_icon_192x192',
				];

				return $this->response->setJSON($responseArray);
			}
			
			if($this->deleteCustomAppAsset('PWA_App_icon_192x192')) {
				// Handle PWA_App_icon_192x192 upload
				$uploadedFiles[] = $this->handleFileUpload($PWA_App_icon_192x192, 'PWA_App_icon_192x192');
			}
		}

		// Validate and handle PWA_App_icon_512x512 upload			
		$PWA_App_icon_512x512 = $this->request->getFile('PWA_App_icon_512x512');
		if ($PWA_App_icon_512x512->isValid()) {

			$validationRulesImage = [
				'PWA_App_icon_512x512' => [
				'label' => 'PWA App icon 512x512',
				'rules' => 'uploaded[PWA_App_icon_512x512]'
					. '|is_image[PWA_App_icon_512x512]'
					. '|mime_in[PWA_App_icon_512x512,image/jpg,image/jpeg,image/png,image/svg,image/webp]'
					. '|max_size[PWA_App_icon_512x512,192]'
					. '|max_dims[PWA_App_icon_512x512,512,512]'
					. '|min_dims[PWA_App_icon_512x512,512,512]'
				]
			];

			$validationMessagesImages = [
				'PWA_App_icon_512x512' => [
					'is_image'  => lang('Notifications.field_valid_image_file'),
					'mime_in'   => lang('Notifications.valid_pwa_icon_format'),
					'max_size'  => lang('Notifications.pwa_icon_max_size'),
					'max_dims'  => lang('Notifications.pwa_icon_512x512_max_dims'),
					'min_dims'  => lang('Notifications.pwa_icon_512x512_max_dims'),
				],
			];

			if (!$this->validate($validationRulesImage, $validationMessagesImages)) {
				$errors = $this->validator->getErrors();
				$responseArray['PWA_App_icon_512x512'] = [
					'success' => false,
					'status' => 0,
					'msg' => $errors,
					'errorElement' => 'PWA_App_icon_512x512',
				];
				return $this->response->setJSON($responseArray);
			}

			// Get the dimensions of the uploaded image
			$imageSize = getimagesize($PWA_App_icon_512x512->getPathname());
			$imageWidth = $imageSize[0];
			$imageHeight = $imageSize[1];	
			
			// Check if the image dimensions are within 400x400
			if ($imageWidth > 512 || $imageHeight > 512) {
				$responseArray['PWA_App_icon_512x512'] = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.choose_correct_icon_pixel'),
					'errorElement' => 'PWA_App_icon_512x512',
				];

				return $this->response->setJSON($responseArray);
			}
			
			if($this->deleteCustomAppAsset('PWA_App_icon_512x512')) {
				// Handle PWA_App_icon_512x512 upload
				$uploadedFiles[] = $this->handleFileUpload($PWA_App_icon_512x512, 'PWA_App_icon_512x512');
			}
		}

		// Update settings if files uploaded successfully
		if(count($uploadedFiles) !== 0) {
			
			$updateElements = [];

			foreach ($uploadedFiles as $file) {
				if ($file['success']) {
					if($this->deleteCustomAppAsset($file['type'])) {
						try {
							$this->UserSettingsModel->setUserSetting($file['type'], $file['fileName'], 0);
							$updateElements[] = $file;

						} catch (\Exception $e) {
							log_message('error', '[Admin/AdminController] ' . lang('Notifications.failed_to_save_in_db', ['errorMessage' => $e->getMessage()]));

							$responseArray[$file['type']] = [
								'success' => false,
								'status' => 0,
								'msg' => lang('Notifications.error_saving_uploaded_files'),
								'errorElement' => $file['type'],
							];

							return $this->response->setJSON($responseArray);
						}
					}
				}
			}

			foreach( $updateElements as $updateElement) {
				$responseArray[$updateElement['type']] = [
					'success' => true,
					'status' => 1,
					'msg' => lang('Notifications.success_app_logo_icon_updated'),
					'updateElement' => $updateElement['type'],
					'fileName' => $updateElement['fileName'],						
				];	
			}
		
		}

		// Clear the cache
		clearCache();
		
		// Reset meraf_app_info cookie
		setAppInfoCookie();

		// Get fresh settings directly from the settings service instead of using cached myConfig
		$currentSettings = getMyConfig('', $this->userID);

		// Update the manifest.json
		if(!$this->updateManifest($currentSettings)) {
			$this->UserSettingsModel->setUserSetting('PWA_App_enabled', null, 0);

			$responseArray['PWA_App_enabled'] = [
				'success' => false,
				'status' => 0,
				'msg' => $errors,
				'errorElement' => 'PWA_App_enabled',
			];
		}

		// Update Firebase files
		if(!$this->updateFirebaseFiles($currentSettings)) {
			$this->UserSettingsModel->setUserSetting('push_notification_feature_enabled', null, 0);

			$responseArray['push_notification_feature_enabled'] = [
				'success' => false,
				'status' => 0,
				'msg' => $errors,
				'errorElement' => 'push_notification_feature_enabled',
			];
		}

		// Update the Service Worker script as needed
		if(!$this->generateCombinedServiceWorkerContent($currentSettings)) {
			$swError = lang('Notifications.failed_generate_service_worker');

			if($currentSettings['PWA_App_enabled']) {
				$this->UserSettingsModel->setUserSetting('PWA_App_enabled', null, 0);

				$responseArray['PWA_App_enabled'] = [
					'success' => false,
					'status' => 0,
					'msg' => $swError,
					'errorElement' => 'PWA_App_enabled',
				];
			}

			if($currentSettings['push_notification_feature_enabled']) {
				$this->UserSettingsModel->setUserSetting('push_notification_feature_enabled', null, 0);
				
				$responseArray['push_notification_feature_enabled'] = [
					'success' => false,
					'status' => 0,
					'msg' => $swError,
					'errorElement' => 'push_notification_feature_enabled',
				];
			}
		}

		return $this->response->setJSON($responseArray);
	}
	
	// Helper function to handle file upload and return information
	private function handleFileUpload($file, $type)
	{
		$uploadedFilePath = WRITEPATH . 'uploads/app-custom-assets/';
		// $fileName = $type . '-custom.' . $file->getExtension();
		$fileName = sha1($type . Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s')) . '.' . $file->getExtension();
	
		// Make sure the directory exists
		if (!file_exists($uploadedFilePath)) {
			mkdir($uploadedFilePath, 0777, true);
		}
	
		// Move the uploaded file to the destination directory
		if ($file->move($uploadedFilePath, $fileName, true)) { // Overwrite existing file if exists
			return ['success' => true, 'fileName' => $fileName, 'type' => $type];
		} else {
			return ['success' => false, 'fileName' => '', 'type' => ''];
		}	
	}

	private function deleteCustomAppAsset($setting)
	{
		// Example of custom asset URL: https://sampledomain.com/writable/uploads/app-custom-assets/{media_file}
		// Corresponding server path: WRITEPATH . 'uploads/app-custom-assets/' . $media_file
	
		try {
			// Ensure the setting exists in the configuration
			if (!isset($this->myConfig[$setting])) {
				throw new \Exception("Setting '$setting' not found in configuration.");
			}
	
			// If the setting is empty, there's nothing to delete
			if (empty($this->myConfig[$setting])) {
				return true;
			}
	
			// Extract the filename from the URL and build the full path to the file
			$extractCurrentMedia = str_replace(base_url('writable/uploads/app-custom-assets/'), '', $this->myConfig[$setting]);
			$existingMedia = WRITEPATH . 'uploads/app-custom-assets/' . $extractCurrentMedia;

			// If the path doesn't indicate a custom asset, clear the setting without deletion
			if (strpos($existingMedia, 'writable/uploads/app-custom-assets/') === false) {
				$this->UserSettingsModel->setUserSetting($setting, '', 0);
				setAppInfoCookie();
				return true;
			}

			// log_message('debug', 'extractCurrentMedia: ' . $extractCurrentMedia);
			// log_message('debug', 'existingMedia: ' . $existingMedia);
			// log_message('debug', 'Does file exists? ' . (file_exists($existingMedia) ? 'Yes' : 'No'));
	
			// If the file exists, attempt to delete it
			if ( file_exists($existingMedia) ) {
				if (!unlink($existingMedia)) {
					throw new \Exception("Failed to delete asset file: $existingMedia");
				}
				log_message('info', 'File deleted: ' . $extractCurrentMedia);

				// Clear the setting after successful deletion
				$this->UserSettingsModel->setUserSetting($setting, '', 0);
	
				// If the deleted asset is part of the PWA manifest, update it
				$groupPwaMediaSettings = [
					'PWA_App_icon_192x192',
					'PWA_App_icon_512x512',
				];

				$groupAppMediaSettings = [
					'appIcon',
					'appLogo_light',
					'appLogo_dark',
				];
	
				if (in_array($setting, $groupPwaMediaSettings)) {
					$currentSettings = getMyConfig('', $this->userID);
					$this->updateManifest($currentSettings);
					$this->generateCombinedServiceWorkerContent($currentSettings);
				}
				else if (in_array($setting, $groupAppMediaSettings)) {
					$currentSettings = getMyConfig('', $this->userID);
					$this->generateCombinedServiceWorkerContent($currentSettings);
				}

				setAppInfoCookie();
	
				return true;
			} else {
				// File does not exist; still reset the setting
				$this->UserSettingsModel->setUserSetting($setting, '', 0);
				return true;
			}
		} catch (\Exception $e) {
			log_message('error', $e->getMessage());
			return false;
		}
	}
	
	public function global_settings_reset_action($setting)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}

		// log_message('debug', 'setting:' . $setting);
		// log_message('debug', 'Setting value in db: ' . ($this->myConfig[$setting]  ?? 'None'));
		
        // Check if setting has value, if none, return
        if (empty($this->myConfig[$setting]) || ($this->myConfig[$setting] === NULL))
        {
            return $this->response->setJSON([
                'success' => true,
                'status' => 2,
                'msg' => lang('Notifications.no_app_asset_set'),
                $setting => $this->myConfig[$setting]
            ]);
        }
        
        $currentValue = $this->myConfig[$setting];
        
        // Check if this is a default asset (contains a URL without the custom assets path)
        if (strpos($currentValue, 'http') === 0 && strpos($currentValue, 'writable/uploads/app-custom-assets/') === false)
        {
            return $this->response->setJSON([
                'success' => true,
                'status' => 2,
                'msg' => lang('Notifications.no_app_asset_set'),
                $setting => $currentValue
            ]);
        }

		// Extract the filename from the URL and build the full path to the file
		$extractCurrentMedia = str_replace(base_url('writable/uploads/app-custom-assets/'), '', $this->adminSettings[$setting]);
		$existingMedia = WRITEPATH . 'uploads/app-custom-assets/' . $extractCurrentMedia;

		if(file_exists($existingMedia)) {

			//log_message('debug', 'Requesting to reset: ' . $setting);
			//log_message('debug', "Custom {$setting} exists? " . (file_exists($existingMedia) ? 'Yes' : 'No'));
			// log_message('debug', 'Filename: ' . $existingMedia);
			
		    if($this->deleteCustomAppAsset($setting)) {
				
    			$response = [
    				'success' => true,
    				'status' => 1,
    				'msg' => lang('Notifications.success_app_asset_reset'),
					$setting => base_url('assets/images/meraf-'.$setting.'.png')
    			];


		    }
		    else {
                $response = [
    				'success' => false,
    				'status' => 0,
    				'msg' => lang('Notifications.error_reset_app_assets'),
    				$setting => $this->adminSettings[$setting]
    			];					        
		    }

			// Clear the cache
			clearCache();

			// Reset meraf_app_info cookie
			setAppInfoCookie();

			// Update the manifest.json
			$this->updateManifest(getMyConfig('', $this->userID));
		}
		else {
			$response = [
				'success' => false,
				'status' => 3,
				'msg' => lang('Notifications.custom_asset_media_file_not_found'),
				$setting => $this->myConfig[$setting]
			];								
		}		

		return $this->response->setJSON($response);
	}

    public function email_settings_page()
    {
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		$data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Setup') . ' | ' . lang('Pages.email_settings');
		$data['section'] = 'Setup';
		$data['subsection'] = 'email_settings';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;

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

        return view('dashboard/admin/email_settings', $data);
    }

	public function email_settings_action()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}        

		$responseArray = [];
		$msgResponse_fileSavingError = lang('Notifications.error_saving_uploaded_files');

		// Validate form data
		$validationRules = [
			'fromName'          => 'required|alpha_numeric_space',
			'fromEmail'         => 'required|valid_email',
			'supportName'       => 'required|alpha_numeric_space',
			'supportEmail'      => 'required|valid_email',
			'htmlEmailFooter'   => 'required',
			'textEmailFooter'   => 'required',
		];

		// Set custom error messages
		$validationMessages = [
			'fromName' => [
				'required' => lang('Notifications.required_input_empty'),
				'alpha_numeric' => lang('Notifications.required_valid_name_from'),
			],
			'fromEmail' => [
				'required' => lang('Notifications.required_input_empty'),
				'valid_email' => lang('Notifications.required_valid_email_from'),
			],
			'supportName' => [
				'required' => lang('Notifications.required_input_empty'),
				'alpha_numeric' => lang('Notifications.required_valid_name_from'),
			],
			'supportEmail' => [
				'required' => lang('Notifications.required_input_empty'),
				'valid_email' => lang('Notifications.required_valid_email_from'),
			],
			'salesName' => [
				'required' => lang('Notifications.required_input_empty'),
				'alpha_numeric' => lang('Notifications.required_valid_name_from'),
			],
			'salesEmail' => [
				'required' => lang('Notifications.required_input_empty'),
				'valid_email' => lang('Notifications.required_valid_email_from'),
			],
			'htmlEmailFooter' => [
				'required' => lang('Notifications.required_html_email_footer'),
			],
			'textEmailFooter' => [
				'required' => lang('Notifications.required_text_email_footer'),
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

			$data = [
				'fromName' => trim($this->request->getPost('fromName')),
				'fromEmail' => trim($this->request->getPost('fromEmail')),
				'supportName' => trim($this->request->getPost('supportName')),
				'supportEmail' => trim($this->request->getPost('supportEmail')),
				'salesName' => trim($this->request->getPost('salesName')),
				'salesEmail' => trim($this->request->getPost('salesEmail')),
				'htmlEmailFooter' => trim($this->request->getPost('htmlEmailFooter')),
				'textEmailFooter' => trim($this->request->getPost('textEmailFooter')),
			];

			// Handle each input and save in the database
			foreach($data as $key => $value) {
				$this->UserSettingsModel->setUserSetting($key, $value, 0);
			}
			
			$responseArray['inputs'] = [
				'success' => true,
				'status' => 1,
				'msg' => lang('Notifications.success_email_settings_saved'),
			];
		}

		// Clear the cache
		clearCache();
					
		return $this->response->setJSON($responseArray);
	}

	public function updateEmailServiceSettings()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}	

		// Get the input fields with class 'variation-input'
		$inputFields = $this->request->getPost();

		// Iterate through each input field
		// foreach ($inputFields as $inputName => $inputValue) {
		// 	// Define the validation rule for each input dynamically
		// 	$validationRules[$inputName] = 'required'; // Adjust the validation rule as needed
		// }

		// Individual validation
		$errorMessage = NULL;

		if(empty($inputFields['protocol']))
		{
			$errorMessage = lang('Pages.protocolError');
		}
		else if($inputFields['protocol'] === 'mail' && empty($inputFields['sendmailPath'])) {
			$errorMessage = lang('Pages.sendmailPathError');
		}
		else if($inputFields['protocol'] === 'sendmail' && empty($inputFields['sendmailPath'])) {
			$errorMessage = lang('Pages.sendmailPathError');
		}
		else if($inputFields['protocol'] === 'smtp' && empty($inputFields['smtpHostname'])) {
			$errorMessage = lang('Pages.smtpHostnameError');
		}
		else if($inputFields['protocol'] === 'smtp' && empty($inputFields['smtpUsername'])) {
			$errorMessage = lang('Pages.smtpUsernameError');
		}
		else if($inputFields['protocol'] === 'smtp' && empty($inputFields['smtpPassword'])) {
			$errorMessage = lang('Pages.smtpPasswordError');
		}
		else if($inputFields['protocol'] === 'smtp' && empty($inputFields['smtpPort'])) {
			$errorMessage = lang('Pages.smtpPortError');
		}
		else if($inputFields['protocol'] === 'smtp' && empty($inputFields['smtpEncryption'])) {
			$errorMessage = lang('Pages.smtpEncryptionError');
		}
		
		if($errorMessage) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => $errorMessage,
			];

			return $this->response->setJSON($response);
		}
		else {
			// No errors found
			$configEmailFile = APPPATH . 'Config/Email.php';
			$configEmailContent = file_get_contents($configEmailFile);

			if( $inputFields['smtpPort'] === '' || $inputFields['smtpPort'] === NULL || !isset($inputFields['smtpPort']) ) {
				$inputFields['smtpPort'] = 587;
			}
	
			$replaceEmailPatterns = [
				'/\$protocol\s*=\s*\''.preg_quote($inputFields['prev_protocol'], '/').'\'\;/' => "\$protocol = '".$inputFields['protocol']."';",
				'/\$mailPath\s*=\s*\''.preg_quote($inputFields['prev_sendmailPath'], '/').'\'\;/' => "\$mailPath = '".$inputFields['sendmailPath']."';",
				'/\$SMTPHost\s*=\s*\''.preg_quote($inputFields['prev_smtpHostname'], '/').'\'\;/' => "\$SMTPHost = '".$inputFields['smtpHostname']."';",
				'/\$SMTPUser\s*=\s*\''.preg_quote($inputFields['prev_smtpUsername'], '/').'\'\;/' => "\$SMTPUser = '".$inputFields['smtpUsername']."';",
				'/\$SMTPPass\s*=\s*\''.preg_quote($inputFields['prev_smtpPassword'], '/').'\'\;/' => "\$SMTPPass = '".$inputFields['smtpPassword']."';",
				'/\$SMTPPort\s*=\s*'.$inputFields['prev_smtpPort'].'\;/' => "\$SMTPPort = ".$inputFields['smtpPort'].";",
				'/\$SMTPCrypto\s*=\s*\''.preg_quote($inputFields['prev_smtpEncryption'], '/').'\'\;/' => "\$SMTPCrypto = '".$inputFields['smtpEncryption']."';",
			];
			$configEmailContent = preg_replace(array_keys($replaceEmailPatterns) , array_values($replaceEmailPatterns) , $configEmailContent);				

			if(file_put_contents($configEmailFile, $configEmailContent)) {

				// Clear the cache
				clearCache();					

				$response = [
					'success' => true,
					'status' => 1,
					'msg' => lang('Notifications.success_updating_email_service'),
				];	
			}
			else {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.error_updating_email_service'),
				];
			}

			return $this->response->setJSON($response);
		}
	}

    public function cron_job_logs_page()
    {
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		$data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Setup') . ' | ' . lang('Pages.cronjob_logs');
		$data['section'] = 'Setup';
		$data['subsection'] = 'cronjob_logs';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		
		// Get Cronjob logs
		$cronjobLogData = [];
		$getCronjobLogData = getMyConfig('Config\Tasks', 0);
		foreach($getCronjobLogData as $key => $logData) {
		    if(!empty($logData) && ($key !== 'enabled')) {
		       if (isset($logData['error'])) {
    				$logData['error'] = $this->maybeUnserialize($logData['error']);
		       }
		       else {
		           $logData = unserialize($logData);
		       }
		       $cronjobLogData[$key] = $logData;
		       
		    }
		}
		$data['cronjobLogData'] = $cronjobLogData;
		
        return view('dashboard/admin/cronjob_logs', $data);
    }
	
	private function maybeUnserialize($data)
	{
		// Check if the data is serialized
		if (is_string($data) && @unserialize($data) !== false) {
			return unserialize($data);
		}
		return $data;
	}

	public function clear_server_cache()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		if(clearCache()) {
			$response = [
				'success' => true,
				'status' => 1,
				'msg' => lang('Notifications.success_clear_server_cache'),
			];
		}
		else {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_clear_server_cache'),
			];
		}

		return $this->response->setJSON($response);
	}

	public function debug()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		// $userId = 2; // Change this to a valid user ID
		// $result = add_notification(
		// 	"This is a test push notification",
		// 	"system_test",
		// 	base_url('success-logs'),
		// 	$userId
		// );
		
		// echo "Notification sent: " . ($result ? "Success" : "Failed");
	}
	
	public function backup_project_files()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
	
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
	
		try {
			clearCache();
	
			$backupPath = dirname(ROOTPATH) . '/private/backup';
			$publicHtmlPath = ROOTPATH;
			
			// Store last backup time
			$lastBackupFile = $backupPath . '/last_backup_time.txt';
			$lastBackupTime = file_exists($lastBackupFile) ? file_get_contents($lastBackupFile) : 0;
	
			// Create backup directory if it doesn't exist
			if (!file_exists($backupPath)) {
				if (!mkdir($backupPath, 0755, true)) {
					throw new Exception('Failed to create backup directory');
				}
			}
	
			$totalFiles = 0;
			$copiedFiles = 0;
			$copiedFileList = []; // Initialize as array
	
			// Recursive function to copy only modified files
			$copyModifiedFiles = function($source, $dest, $lastBackupTime) use (&$copyModifiedFiles, &$totalFiles, &$copiedFiles, &$copiedFileList) {
				$dir = opendir($source);
				
				while (($file = readdir($dir)) !== false) {
					if ($file === '.' || $file === '..' || 
						in_array($file, ['_BACKUP', 'Build-Release', 'debugbar', 'logs', 'single-user-prodpanel'])) {
						continue;
					}
	
					$sourcePath = $source . '/' . $file;
					$destPath = $dest . '/' . $file;
					
					if (is_dir($sourcePath)) {
						if (!file_exists($destPath)) {
							mkdir($destPath, 0755, true);
						}
						$copyModifiedFiles($sourcePath, $destPath, $lastBackupTime);
					} else {
						$totalFiles++;
						
						// Copy only if file is new or modified since last backup
						if (!file_exists($destPath) || filemtime($sourcePath) > $lastBackupTime) {
							if (copy($sourcePath, $destPath)) {
								// Store relative path instead of full path for better readability
								$relativePath = str_replace(ROOTPATH, '', $sourcePath);
								$copiedFileList[] = $relativePath; // Add to array properly
								$copiedFiles++;
								
								// Log progress every 100 files
								if ($copiedFiles % 100 === 0) {
									log_message('info', "[Admin/AdminController] Copied $copiedFiles files out of $totalFiles checked");
								}
							} else {
								log_message('error', "[Admin/AdminController] Failed to copy file: $sourcePath");
							}
						}
					}
				}
				closedir($dir);
			};
	
			// Start the incremental backup
			$copyModifiedFiles($publicHtmlPath, $backupPath, $lastBackupTime);
	
			// Log the list of copied files (only if files were actually copied)
			if (!empty($copiedFileList)) {
				// Sort the list for better readability
				sort($copiedFileList);
				// Limit the number of files logged to prevent excessive log size
				$logLimit = 1000;
				$loggedFiles = array_slice($copiedFileList, 0, $logLimit);
				if (count($copiedFileList) > $logLimit) {
					$loggedFiles[] = "... and " . (count($copiedFileList) - $logLimit) . " more files";
				}
				log_message('info', "Files backed up:\n" . implode("\n", $loggedFiles));
			}
	
			// Update last backup time
			file_put_contents($lastBackupFile, time());
	
			log_message('info', "[Admin/AdminController] Backup completed. Copied $copiedFiles out of $totalFiles files");
	
			$data = array_merge($data, [
				'pageTitle' => 'Backup Successful',
				'alert' => [
					'type' => 'success',
					'message' => "Backup completed successfully ($copiedFiles files updated). Would you like to <a href='" . 
								base_url('build-release') . "' class='alert-link'>build release</a>?",
					'copiedFileList' => $copiedFileList
				]
			]);
	
			return view('layouts/single_page', $data);
	
		} catch (Exception $e) {
			log_message('error', '[Admin/AdminController] Backup failed: ' . $e->getMessage());
	
			$data = array_merge($data, [
				'pageTitle' => 'Backup Failed',
				'alert' => [
					'type' => 'danger',
					'message' => 'Failed to backup project files: ' . $e->getMessage()
				]
			]);
	
			return view('layouts/single_page', $data);
		}
	}

	public function build_release_package()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
		
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;

		// Clear the cache
		clearCache();		

		// Store the original installer folder name
		$defaultInstallerFolderName = '';
		$publicPath = ROOTPATH . 'public/';
		$installFolders = glob($publicPath . 'install-*', GLOB_ONLYDIR);
		foreach ($installFolders as $folder) {
			$defaultInstallerFolderName = $folder;
			rename($folder, $publicPath . 'install');
			break;
		}
	
		// Exclude files and folders
		$excludedItems = [			
			APPPATH . 'Config/App.php',
			APPPATH . 'Config/Database.php',
			APPPATH . 'Config/Email.php',
			APPPATH . 'Config/Encryption.php',
			WRITEPATH . 'debugbar',
			WRITEPATH . 'logs',	
			WRITEPATH . 'cache',
			WRITEPATH . 'session',			
			WRITEPATH . 'uploads/app-custom-assets',
			WRITEPATH . 'uploads/user-avatar/5caff23bb067c57adaf50d3950b2167b2b8dc4b1.jpeg',
			WRITEPATH . 'Build-Release',
			WRITEPATH . '.installed',
			ROOTPATH . '.env',
			ROOTPATH . '.git',
			ROOTPATH . 'CLAUDE.md',
			ROOTPATH . '.gitignore',
			ROOTPATH . 'docs',
			ROOTPATH . '.claude',
			ROOTPATH . 'commit_message.txt',
			ROOTPATH . 'last_backup_time.txt',
			ROOTPATH . '_BACKUP',
			ROOTPATH . 'user-data',
			ROOTPATH . 'tests',
			ROOTPATH . 'composer.json.bak',
			ROOTPATH . 'env',
			ROOTPATH . 'LICENSE',
			ROOTPATH . 'phpunit.xml.dist',
			ROOTPATH . 'README.md',
			ROOTPATH . 'public/install/config/database.php',
			ROOTPATH . 'public/manifest.json',
			ROOTPATH . 'public/service-worker.js',
			ROOTPATH . 'public/assets/js/firebase-init.js',
			ROOTPATH . 'vendor/bin',
			ROOTPATH . 'vendor/clue',
			ROOTPATH . 'vendor/codeigniter',
			ROOTPATH . 'vendor/evenement',
			ROOTPATH . 'vendor/fakerphp',
			ROOTPATH . 'vendor/fidry',
			ROOTPATH . 'vendor/friendsofphp',
			ROOTPATH . 'vendor/kint-php',
			ROOTPATH . 'vendor/mikey179',
			ROOTPATH . 'vendor/myclabs',
			ROOTPATH . 'vendor/nexusphp',
			ROOTPATH . 'vendor/nikic',
			ROOTPATH . 'vendor/phar-io',
			ROOTPATH . 'vendor/phpunit',
			ROOTPATH . 'vendor/predis',
			ROOTPATH . 'vendor/react',
			ROOTPATH . 'vendor/sebastian',
			ROOTPATH . 'vendor/symfony',
			ROOTPATH . 'vendor/theseer',
			APPPATH . 'Views/includes/dashboard/developer.php'
		];
	
		// Make sure there's a products folder
		if (!file_exists(WRITEPATH . 'Build-Release')) {
			mkdir(WRITEPATH . 'Build-Release', 0777, true);	
		}
	
		// Create a new zip archive
		$zip = new \ZipArchive();
		$zipFileName = WRITEPATH . 'Build-Release/' . $this->myConfig['appName'] . '_v-.-.-_' . Time::now()->format('dmY_Hi') . '.zip'; // Save in ROOTPATH
	
		if ($zip->open($zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
			$this->zipDirectoryRecursive(ROOTPATH, '', $zip, $excludedItems); // Start recursive zipping from the root directory
			$zip->close();
			$zipFileName = str_replace(ROOTPATH , '', $zipFileName);

			// Rename back the installer to its original name
			if (!empty($defaultInstallerFolderName) && file_exists($publicPath . 'install')) {
				rename($publicPath . 'install', $defaultInstallerFolderName);
			}			

			$data = array_merge($data, [
				'pageTitle' => 'Project Build Successful',
				'alert' => [
					'type' => 'success',
					'message' => lang('Notifications.success_files_zipped', ['zipFileName' => $zipFileName])
				]
			]);			
	
			return view('layouts/single_page', $data);
		} else {
			$data = array_merge($data, [
				'pageTitle' => 'Project Build Error',
				'alert' => [
					'type' => 'success',
					'message' => lang('Notifications.error_zipping_files')
				]
			]);		
	
			return view('layouts/single_page', $data);
		}
	}

    public function user_manager_page()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		$data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Setup') . ' | ' . lang('Pages.Manage_Users');
		$data['section'] = 'Setup';
		$data['subsection'] = 'user_manager';
		$data['productNames'] = productList($this->userID);
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;

		$data['packages'] = $this->PackageModel->findAll();

		return view('dashboard/admin/user_manager', $data);
	}

    /**
     * Update user subscription manually
     */
	public function update_user_subscription()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setStatusCode(403)->setJSON([
				'success' => false,
				'msg' => lang('Pages.forbidden_error_msg')
			]);
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setJSON([
				'success' => false,
				'msg' => lang('Notifications.Method_Not_Allowed')
			]);
		}

		$userId = $this->request->getPost('user_id');
		$packageId = $this->request->getPost('package_id');
		$expiryDate = $this->request->getPost('expiry_date');
		
		// Create Time instances

		// First check session for detected timezone
		$session = session();
		$userTimezone = $session->get('detected_timezone') ?? 
						$this->myConfig['defaultTimezone'] ?? 
						'UTC';
						
        $startDate = Time::now($userTimezone);
        $endDate = Time::parse($expiryDate, $userTimezone);
        
        // Convert both to UTC
        $startDateUTC = $startDate->setTimezone('UTC');
        $endDateUTC = $endDate->setTimezone('UTC');

		if ($startDateUTC->getTimestamp() >= $endDateUTC->getTimestamp()) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.please_check_the_subscription_expiry_date')
			]);
		}

		// Validate input
		if (!$userId || !$packageId || !$expiryDate) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.invalid_input')
			]);
		}

		$package = $this->PackageModel->find($packageId);
		if (!$package) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.invalid_package_selected')
			]);
		}

		$newSubscriptionReference = 'O-' . strtoupper(uniqid());
		$offlineService = new \App\Modules\Offline\Libraries\OfflineService();
		$newTransactionId = $offlineService->generateTransactionId();

		// Create or update subscription
		$subscriptionData = [
			'user_id' => (int)$userId,
			'package_id' => (int)$packageId,
			'subscription_status' => 'active',
			'payment_status' => 'completed',
			'payment_method' => 'Offline',
			'transaction_id' => $newTransactionId,
			'subscription_reference' => $newSubscriptionReference,
			'currency' => $this->myConfig['packageCurrency'],
			'amount_paid' => (float)"0.00",
			'billing_cycle' => $package['validity_duration'],
			'billing_period' => (int)$package['validity'],
			'start_date' => $startDateUTC,
			'end_date' => $endDateUTC,
			'last_payment_date' => $startDateUTC,
		];

		log_message('debug', '[Admin/AdminController] Subscription raw data to be saved: ' . json_encode($subscriptionData));

		// Check if the user has existing and active subscription
		try {
			// Check if the user has existing and active subscription
			$existingSubscription = $this->SubscriptionModel->where('user_id', $userId)
														   ->where('subscription_status', 'active')
														   ->orderBy('created_at', 'DESC')
														   ->first();
		
			// If found, cancel the subscriptions before creating a new one
			if ($existingSubscription) {
				// Cancel the subscription according to the payment method used
				$subscriptionController = new \App\Controllers\Admin\SubscriptionController();
				
				$initiateCancellation = $subscriptionController->subscription_cancel($existingSubscription['id']);
				
				// Decode json return
				$cancellationResult = json_decode($initiateCancellation, true);
				
				if (!$cancellationResult || !isset($cancellationResult['success']) || !$cancellationResult['success']) {
					return $this->response->setJSON([
						'success' => false,
						'msg' => $cancellationResult['message'] ?? lang('Notifications.failed_to_cancel_subscription_for_change_package_admin')
					]);
				}
			}
		} catch (Exception $e) {
			log_message('error', '[SubscriptionCancellation] Error: ' . $e->getMessage());
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.failed_to_cancel_subscription_for_change_package_admin')
			]);
		}
		
		// Now create a new subscription id for the new package
		$subscriptionId = $this->SubscriptionModel->insert($subscriptionData);
		$subscriptionReference = $subscriptionData['subscription_reference'];
	
		if (!$subscriptionId) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.error_updating_subscription')
			]);
		}

		// Create subscription payment record
		$paymentData = [
			'subscription_id' => $subscriptionReference,
			'transaction_id' => $newTransactionId,
			'amount' => (float)"0.00",
			'currency' => $this->myConfig['packageCurrency'],
			'payment_status' => 'completed',
			'payment_date' => $startDateUTC
		];

		log_message('debug', '[Admin/AdminController] Payment raw data to be saved: ' . json_encode($paymentData));

		$paymentResult = $this->PaymentModel->insert($paymentData);

		if (!$paymentResult) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.error_creating_payment_record')
			]);
		}

		return $this->response->setJSON([
			'success' => true,
			'msg' => lang('Notifications.user_subscription_updated_successfully')
		]);
	}

    private function generateRandomString($length = 12) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function list_packages_page()
    {
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		$data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.package_manager') . ' | ' . lang('Pages.List_packages');
		$data['section'] = 'package_manager';
		$data['subsection'] = 'List_packages';
		$data['productNames'] = productList($this->userID);
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		$data['defaultPackage'] = $this->PackageModel->getDefaultPackage();

        $moduleCategories = $this->ModuleCategoryModel->where('status', 'active')
                                                      ->orderBy('sort_order', 'ASC')
                                                      ->findAll();

		$packageModules = $this->PackageModulesModel->findAll();

		$featureLanguageMap = [];
		foreach($moduleCategories as $moduleCategory) {
			foreach($packageModules as $packageModule) {
				if($moduleCategory['id'] ===  $packageModule['module_category_id']) {
					// Get the lang
					$packageModule['measurement_unit'] = json_decode($packageModule['measurement_unit'], true);
					$packageModule['measurement_unit'] = $packageModule['measurement_unit']['unit'];

					$featureLanguageMap[$moduleCategory['category_name']][$moduleCategory['category_name']] = lang('Pages.' .$moduleCategory['category_name']);
					$featureLanguageMap[$moduleCategory['category_name']][$packageModule['module_name']] = lang('Pages.' .$packageModule['module_name']);
					$featureLanguageMap[$moduleCategory['category_name']][$packageModule['module_description']] = lang('Pages.' .$packageModule['module_description']);
					$featureLanguageMap[$moduleCategory['category_name']][$packageModule['module_name'].'_unit'] = lang('Pages.' .$packageModule['measurement_unit']);
				}
			}
		}

		$data['featureLanguageMap'] = $featureLanguageMap;

        return view('dashboard/admin/package-manager/list_packages', $data);
    }

	public function save_package()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
        }
        
        // Get form data
        $packageId = $this->request->getPost('packageId');
        $moduleData = $this->request->getPost('moduleData');

		// Get the input fields with class 'variation-input'
		$debugValues = [];
		$inputFields = $this->request->getPost();

		// Iterate through each input field
		foreach ($inputFields as $inputName => $inputValue) {
			// Define the validation rule for each input dynamically
			$debugValues[$inputName] = $inputValue;
		}

        $packageData = [
            'owner_id' => $this->userID,
            'package_name' => $this->request->getPost('packageName'),
            'price' => $this->request->getPost('packagePrice'),
            'validity' => strtolower($this->request->getPost('packageDuration')) === 'lifetime' ? 1 : $this->request->getPost('packageValidity'),
            'validity_duration' => strtolower($this->request->getPost('packageDuration')),
            'visible' => $this->request->getPost('packageActive') === 'true' ? 'on' : 'off',
            'highlight' => $this->request->getPost('packageHighlighted') === 'true' ? 'on' : 'off',
			'is_default' => $this->request->getPost('defaultPackage') === 'true' ? 'on' : 'off',
            'status' => 'active',
			'sort_order' => $this->request->getPost('sortOrder'),
			'package_modules' => json_encode($moduleData, JSON_PRETTY_PRINT)
        ];
        
        log_message('debug', '[Admin/AdminController] Submitted package data: '.json_encode($packageData, JSON_PRETTY_PRINT));

        $allPackageData = $this->PackageModel->where('owner_id', $this->userID)->findAll();
        
        // Unset the current package id if edit mode
        if($packageId) {
            $allPackageData = array_filter($allPackageData, function($pkg) use ($packageId) {
                return $pkg['id'] != $packageId;
            });
        }
        
		// Check if the same package name is unique
		$packageNameExists = in_array($packageData['package_name'], array_column($allPackageData, 'package_name'));
		if($packageNameExists) {
			return $this->response->setJSON([
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_duplicated_package_name', ['packageName' => $packageData['package_name']]),
				'element' => 'packageName'
			]);
		}

		// Check if other package already a default
		if ($packageData['is_default'] === 'on') {
			$packageHasDefault = array_filter($allPackageData, function($pkg) {
				return $pkg['is_default'] === 'on';
			});

			if (!empty($packageHasDefault)) {
				return $this->response->setJSON([
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.error_exisiting_default_package'),
					'element' => 'defaultPackage'
				]);
			}			
		}
		
        try {
            if ($packageId) {
                // Update existing package
                $this->PackageModel->update($packageId, $packageData);
                $message = lang('Notifications.package_updated_successfully');
            } else {
                // Create new package
                $packageId = $this->PackageModel->insert($packageData);

				if($packageId) {
					$message = lang('Notifications.package_created_successfully');
				}
				else {
					log_message9('debug', json_encode($packageId));
					$message = lang('Notifications.error_saving_package');
				}
                
            }

            return $this->response->setJSON([
                'success' => true,
                'status' => 1,
                'msg' => $message
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Admin/AdminController] Package Save Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'status' => 0,
                'msg' => lang('Notifications.error_saving_package')
            ]);
        }
    }

	public function manage_individual_package($page, $id=NULL)
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $auth;
        }

        $data['productNames'] = productList($this->userID);
		$data['sideBarMenu'] = $this->sideBarMenu;
        $data['userData'] = $this->userAcctDetails;
        $data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
        $data['myConfig'] = $this->myConfig;        

        // Load module categories
        $data['moduleCategories'] = $this->ModuleCategoryModel->where('status', 'active')
                                                      ->orderBy('sort_order', 'ASC')
                                                      ->findAll();

        // Load package modules with default measurement units
		$packageModules = $this->PackageModulesModel->findAll();
        $data['packageModules'] = $packageModules;

		$packageModules = $this->PackageModulesModel->getPackageModules();
		foreach($packageModules as $packageModule) {
			if($packageModule['is_enabled'] === 'yes') {
				$baseModules = $packageModule;
			}
		}

        if($page == 'new') {
            $data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.package_manager') . ' | ' . lang('Pages.New_package');
            $data['section'] = 'package_manager';
			$data['subsection'] = 'New_package';
            $data['packageID'] = NULL;
        }
		else if($page === 'select_package') {
            $data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.package_manager') . ' | ' . lang('Pages.Edit_package');
			$data['section'] = 'package_manager';
            $data['subsection'] = 'Edit_package';
            $data['packageID'] = intval($id);

            // Load package data for editing
            $filter = 'active';
            $allPackageData = $this->PackageModel->where('owner_id', $this->userID)->findAll();
            $allPackageData = array_filter($allPackageData, function($pkg) use ($filter) {
                return $pkg['status'] == $filter;
            });
			usort($allPackageData, function($a, $b) {
				return $a['sort_order'] - $b['sort_order'];
			});
            
            $data['allPackageData'] = $allPackageData;
        }
        else if($page === 'edit' && $id) {
            $data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.package_manager') . ' | ' . lang('Pages.Edit_package');
			$data['section'] = 'package_manager';
			$data['subsection'] = 'Edit_package';
            $data['packageID'] = intval($id);

            // Load package data for editing
            $filter = 'active';
            $allPackageData = $this->PackageModel->where('owner_id', $this->userID)->findAll();
            $allPackageData = array_filter($allPackageData, function($pkg) use ($filter) {
                return $pkg['status'] == $filter;
            });
			$data['allPackageData'] = $allPackageData;

            $data['packageData'] = $this->PackageModel->where('owner_id', $this->userID)->find($id);
            if (!$data['packageData']) {
                return redirect()->to('forbidden')->with('error', lang('Pages.package_not_found'));
            }
        }
        else {
            return redirect()->to('forbidden')->with('error', lang('Pages.forbidden_error_msg'));
        }

        return view('dashboard/admin/package-manager/manage_individual_package', $data);
    }

    public function listPackages()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }
        
        try {
            // Get all packages
			$packageListData = $this->PackageModel->orderBy('sort_order', 'ASC')->findAll();

			$response = [
				'success' => true,
				'status' => 1,
				'data' => $packageListData
			];			

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            log_message('error', '[Admin/AdminController] Package List Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
				'status' => 0,
                'msg' => lang('Notifications.error_loading_packages')
            ]);
        }
    }

    public function deletePackage($id)
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }

		$packageData = $this->PackageModel->where('owner_id', $this->userID)->find($id);
		$packageData = [
			'status' => 'deleted',
			'visible' => 'off',
			'highlight' => 'off',
			'is_default' => 'off',
			'sort_order' => 99,
		];

        try {
            $this->PackageModel->update($id, $packageData);

            return $this->response->setJSON([
                'success' => true,
				'status' => 1,
                'msg' => lang('Notifications.package_deleted_successfully')
            ]);
        } catch (\Exception $e) {
            log_message('error', '[Admin/AdminController] Package Delete Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
				'status' => 0,
                'msg' => lang('Notifications.error_deleting_package')
            ]);
        }
    }

	public function packageModules()
	{
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }

		$data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.package_manager') . ' | ' . lang('Pages.Modules');
		$data['section'] = 'package_manager';
		$data['subsection'] = 'Modules';
		$data['productNames'] = productList($this->userID);
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;     

        // Load module categories
        $data['moduleCategories'] = $this->ModuleCategoryModel->where('status', 'active')
                                                      ->orderBy('sort_order', 'ASC')
                                                      ->findAll();

        // Load package modules with default measurement units
		$packageModules = $this->PackageModulesModel->findAll();
        $data['packageModules'] = $packageModules;

		// $packageModules = $this->PackageModulesModel->getPackageModules();
		// foreach($packageModules as $packageModule) {
		// 	if($packageModule['is_enabled'] === 'yes') {
		// 		$baseModules = $packageModule;
		// 	}
		// }

		return view('dashboard/admin/package-manager/package_modules', $data);
	}

	/**
	 * Save a new category
	 */
	public function saveCategory()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Pages.forbidden_error_msg')
			]);
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setJSON([
				'success' => false,
				'msg' => lang('Notifications.Method_Not_Allowed')
			]);
		}

		$rules = [
			'category_name' => 'required|min_length[3]|max_length[100]|is_unique[module_category.category_name]',
			'sort_order' => 'permit_empty|integer|greater_than_equal_to[0]',
			'status' => 'required|in_list[active,inactive]'
		];

		if (!$this->validate($rules)) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => $this->validator->getErrors()
			]);
		}

		$data = [
			'category_name' => $this->request->getPost('category_name'),
			'description' => $this->request->getPost('description'),
			'sort_order' => $this->request->getPost('sort_order') ?: 1,
			'status' => $this->request->getPost('status')
		];

		try {
			$this->ModuleCategoryModel->insert($data);
			
			return $this->response->setJSON([
				'success' => true,
				'msg' => lang('Notifications.category_created_successfully')
			]);
		} catch (\Exception $e) {
			log_message('error', '[Admin/AdminController] Category Save Error: ' . $e->getMessage());
			
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.error_saving_category')
			]);
		}
	}

	/**
	 * Update an existing category
	 */
	public function updateCategory()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Pages.forbidden_error_msg')
			]);
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setJSON([
				'success' => false,
				'msg' => lang('Notifications.Method_Not_Allowed')
			]);
		}

		$categoryId = $this->request->getPost('category_id');
		
		$rules = [
			'category_id' => 'required|integer|is_not_unique[module_category.id]',
			'category_name' => 'required|min_length[3]|max_length[100]|is_unique[module_category.category_name,id,'.$categoryId.']',
			'sort_order' => 'permit_empty|integer|greater_than_equal_to[0]',
			'status' => 'required|in_list[active,inactive]'
		];

		if (!$this->validate($rules)) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => $this->validator->getErrors()
			]);
		}

		$data = [
			'category_name' => $this->request->getPost('category_name'),
			'description' => $this->request->getPost('description'),
			'sort_order' => $this->request->getPost('sort_order') ?: 1,
			'status' => $this->request->getPost('status')
		];

		try {
			$this->ModuleCategoryModel->update($categoryId, $data);
			
			return $this->response->setJSON([
				'success' => true,
				'msg' => lang('Notifications.category_updated_successfully')
			]);
		} catch (\Exception $e) {
			log_message('error', '[Admin/AdminController] Category Update Error: ' . $e->getMessage());
			
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.error_updating_category')
			]);
		}
	}

	/**
	 * Save a new module
	 */
	public function saveModule()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Pages.forbidden_error_msg')
			]);
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setJSON([
				'success' => false,
				'msg' => lang('Notifications.Method_Not_Allowed')
			]);
		}

		$rules = [
			'module_name' => 'required|max_length[100]',
			'module_category_id' => 'required|integer|is_not_unique[module_category.id]',
			'is_enabled' => 'required|in_list[yes,no]',
			'measurement_unit' => 'required|valid_json'
		];

		if (!$this->validate($rules)) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => $this->validator->getErrors()
			]);
		}

        // Get the module category ID from the request
        $moduleCategoryId = $this->request->getPost('module_category_id');
        
        // Find the highest existing package_id that starts with the module category ID
        $highestPackage = $this->PackageModulesModel
            ->where('package_id >=', $moduleCategoryId * 100)
            ->where('package_id <', ($moduleCategoryId + 1) * 100)
            ->orderBy('package_id', 'DESC')
            ->first();
        
        // Set the new package_id
        if ($highestPackage) {
            // Increment the highest existing package_id
            $packageId = $highestPackage['package_id'] + 1;
        } else {
            // If no existing packages with this prefix, start at X00 + 1
            $packageId = ($moduleCategoryId * 100) + 1;
        }
		
        $data = [
            'package_id' => $packageId,
            'module_category_id' => $moduleCategoryId,
            'module_name' => $this->request->getPost('module_name'),
            'module_description' => $this->request->getPost('module_description'),
            'is_enabled' => $this->request->getPost('is_enabled'),
            'measurement_unit' => $this->request->getPost('measurement_unit')
        ];
		
		log_message('debug', '[Admin/AdminController] Module data to be saved: ' . json_encode($data, JSON_PRETTY_PRINT));

		try {
			$moduleId = $this->PackageModulesModel->insert($data);
    
			if ($moduleId) {
				// Update all packages with the new module
				$this->updatePackagesWithModuleChanges($data);
			}
			
			return $this->response->setJSON([
				'success' => true,
				'msg' => lang('Notifications.module_created_successfully')
			]);
		} catch (\Exception $e) {
			log_message('error', '[Admin/AdminController] Module Save Error: ' . $e->getMessage());
			
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.error_saving_module')
			]);
		}
	}

	/**
	 * Update an existing module
	 */
	public function updateModule()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Pages.forbidden_error_msg')
			]);
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setJSON([
				'success' => false,
				'msg' => lang('Notifications.Method_Not_Allowed')
			]);
		}

		$moduleId = $this->request->getPost('module_id');
		
		$rules = [
			'module_id' => 'required|integer|is_not_unique[package_modules.id]',
			'module_name' => 'required|max_length[100]',
			'module_category_id' => 'required|integer|is_not_unique[module_category.id]',
			'is_enabled' => 'required|in_list[yes,no]',
			'measurement_unit' => 'required|valid_json'
		];

		if (!$this->validate($rules)) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => $this->validator->getErrors()
			]);
		}

		$data = [
			'module_category_id' => $this->request->getPost('module_category_id'),
			'module_name' => $this->request->getPost('module_name'),
			'module_description' => $this->request->getPost('module_description'),
			'is_enabled' => $this->request->getPost('is_enabled'),
			'measurement_unit' => $this->request->getPost('measurement_unit')
		];

		try {
			// Get the old module data before updating
			$oldModule = $this->PackageModulesModel->find($moduleId);
			$oldModuleName = $oldModule['module_name'];
			
			// Update the module
			$this->PackageModulesModel->update($moduleId, $data);
			
			// Update all packages with the module changes
			$this->updatePackagesWithModuleChanges($data, $oldModuleName);
			
			return $this->response->setJSON([
				'success' => true,
				'msg' => lang('Notifications.module_updated_successfully')
			]);
		} catch (\Exception $e) {
			log_message('error', '[Admin/AdminController] Module Update Error: ' . $e->getMessage());
			
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.error_updating_module')
			]);
		}
	}

	/**
	 * Delete a module
	 * 
	 * @param int $id Module ID to delete
	 * @return \CodeIgniter\HTTP\Response
	 */
	public function deleteModule($id)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Pages.forbidden_error_msg')
			]);
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setJSON([
				'success' => false,
				'msg' => lang('Notifications.Method_Not_Allowed')
			]);
		}

		try {
			// Get the module data before deleting
			$module = $this->PackageModulesModel->find($id);
			
			if (!$module) {
				return $this->response->setJSON([
					'success' => false,
					'msg' => lang('Notifications.module_not_found')
				]);
			}
			
			// Remove the module from all packages
			$this->removeModuleFromPackages($module);
			
			// Delete the module
			$this->PackageModulesModel->delete($id);
			
			return $this->response->setJSON([
				'success' => true,
				'msg' => lang('Notifications.module_deleted_successfully')
			]);
		} catch (\Exception $e) {
			log_message('error', '[Admin/AdminController] Module Delete Error: ' . $e->getMessage());
			
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.error_deleting_module')
			]);
		}
	}

	/**
	 * Remove a module from all packages
	 * 
	 * @param array $moduleData The module data
	 * @return void
	 */
	private function removeModuleFromPackages($moduleData)
	{
		// Get all packages
		$packages = $this->PackageModel->where('owner_id', $this->userID)->findAll();
		
		if (empty($packages)) {
			return; // No packages to update
		}
		
		// Get the module category
		$moduleCategory = $this->ModuleCategoryModel->find($moduleData['module_category_id']);
		if (!$moduleCategory) {
			log_message('error', '[Admin/AdminController] Module category not found: ' . $moduleData['module_category_id']);
			return;
		}
		
		$categoryName = $moduleCategory['category_name'];
		$moduleName = $moduleData['module_name'];
		
		// Update each package
		foreach ($packages as $package) {
			$packageModules = json_decode($package['package_modules'], true) ?: [];
			$updated = false;
			
			// Check if the category exists in the package modules
			if (isset($packageModules[$categoryName])) {
				// Check if the module exists in the category
				if (isset($packageModules[$categoryName][$moduleName])) {
					// Remove the module
					unset($packageModules[$categoryName][$moduleName]);
					$updated = true;
					
					// If the category is now empty, remove it
					if (empty($packageModules[$categoryName])) {
						unset($packageModules[$categoryName]);
					}
				}
			}
			
			// Only update if changes were made
			if ($updated) {
				// Update the package
				$this->PackageModel->update($package['id'], [
					'package_modules' => json_encode($packageModules, JSON_PRETTY_PRINT)
				]);
				
				log_message('debug', '[Admin/AdminController] Removed module ' . $moduleName . ' from package ' . $package['id']);
			}
		}
	}

	/**
	 * Update all packages with module changes
	 * 
	 * @param array $moduleData The module data
	 * @param string $oldModuleName The previous module name (for updates)
	 * @return void
	 */
	private function updatePackagesWithModuleChanges($moduleData, $oldModuleName = null)
	{
		// Get all packages
		$packages = $this->PackageModel->where('owner_id', $this->userID)->findAll();
		
		if (empty($packages)) {
			return; // No packages to update
		}
		
		// Get the module category
		$moduleCategory = $this->ModuleCategoryModel->find($moduleData['module_category_id']);
		if (!$moduleCategory) {
			log_message('error', '[Admin/AdminController] Module category not found: ' . $moduleData['module_category_id']);
			return;
		}
		
		$categoryName = $moduleCategory['category_name'];
		$moduleName = $moduleData['module_name'];
		
		// Update each package
		foreach ($packages as $package) {
			$packageModules = json_decode($package['package_modules'], true) ?: [];
			$updated = false;
			
			// Ensure the category exists in the package modules
			if (!isset($packageModules[$categoryName])) {
				$packageModules[$categoryName] = [];
			}
			
			// If this is an update (rename) operation
			if ($oldModuleName && $oldModuleName !== $moduleName) {
				// Look for the old module name in all categories (in case category changed too)
				foreach ($packageModules as $catName => $modules) {
					if (isset($modules[$oldModuleName])) {
						// Save the old settings
						$oldSettings = $modules[$oldModuleName];
						
						// Remove the old module
						unset($packageModules[$catName][$oldModuleName]);
						
						// If the category is now empty, remove it
						if (empty($packageModules[$catName])) {
							unset($packageModules[$catName]);
						}
						
						// Add the module with new name but keep the old settings
						$packageModules[$categoryName][$moduleName] = $oldSettings;
						$updated = true;
						break;
					}
				}
			}
			
			// If the module doesn't exist yet or wasn't found for update
			if (!isset($packageModules[$categoryName][$moduleName])) {
				// Add the new module with enabled=false
				$packageModules[$categoryName][$moduleName] = [
					'value' => $this->getDefaultValueForModule($moduleData),
					'enabled' => 'false'
				];
				$updated = true;
			}
			
			// Only update if changes were made
			if ($updated) {
				// Update the package
				$this->PackageModel->update($package['id'], [
					'package_modules' => json_encode($packageModules, JSON_PRETTY_PRINT)
				]);
				
				log_message('debug', '[Admin/AdminController] Updated package ' . $package['id'] . ' with module: ' . $moduleName);
			}
		}
	}

	/**
	 * Get default value for a module based on its measurement unit
	 * 
	 * @param array $moduleData The module data
	 * @return string The default value
	 */
	private function getDefaultValueForModule($moduleData)
	{
		$measurementUnit = json_decode($moduleData['measurement_unit'], true);
		
		if ($measurementUnit['type'] === 'checkbox') {
			return 'false';
		} else if ($measurementUnit['type'] === 'number') {
			return isset($measurementUnit['default']) ? (string)$measurementUnit['default'] : '0';
		} else if ($measurementUnit['type'] === 'text') {
			return isset($measurementUnit['default']) ? $measurementUnit['default'] : '';
		}
		
		return '';
	}

	public function blocked_ip_log()
	{
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }

		$data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Setup') . ' | ' . lang('Pages.Blocked_ip_logs');
		$data['section'] = 'Setup';
		$data['subsection'] = 'Blocked_ip_logs';
		$data['productNames'] = productList($this->userID);
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;

		return view('dashboard/admin/blocked_ips', $data);
	}

	public function blocked_ip_log_data()
	{
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }
        
        $data = $this->IpBlockModel->findAll();

		foreach($data as $key => $entry) {
			$owner = $this->UserModel->where('id', $entry['owner_id'])->first();
			$data[$key]['owner_username'] = $owner->username;
		}

        return $this->response->setJSON($data);
    }
    
	public function blocked_ip_action()
	{
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }
        
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON(['result' => 'error', 'message' => lang('Notifications.Method_Not_Allowed')]);
        }

		$selectedIPs = $this->request->getPost('selectedLicense');
		
		if (!$selectedIPs) {
			return $this->response->setJSON(['result' => 'error', 'message' => lang('Notifications.no_ips_selected')]);
		}

		$deletedCount = $this->IpBlockModel->delete($selectedIPs);

		if ($deletedCount > 0) {
			return $this->response->setJSON(['result' => 'success', 'message' => lang('Notifications.ips_deleted_successfully', ['count' => $deletedCount])]);
		} else {
			return $this->response->setJSON(['result' => 'error', 'message' => lang('Notifications.failed_to_delete_ips')]);
		}
    }

	/**
     * Get user details
     */
	public function get_user_details()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setStatusCode(403)->setJSON([
				'success' => false,
				'msg' => lang('Pages.forbidden_error_msg')
			]);
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setJSON([
				'success' => false,
				'msg' => lang('Notifications.Method_Not_Allowed')
			]);
		}

		$userId = $this->request->getPost('user_id');
		$user = $this->UserModel->find($userId);

		if ($user) {
			return $this->response->setJSON([
				'success' => true,
				'first_name' => $user->first_name,
				'last_name' => $user->last_name
			]);
		} else {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.user_not_found')
			]);
		}
	}

	/**
     * Update user details
     */
    public function update_user_details()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => lang('Notifications.Method_Not_Allowed')
            ]);
        }

        $userId = $this->request->getPost('user_id');
        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name')
        ];
        
        try {
            if ($this->UserModel->updateUserDetails($userId, $userData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 1,
                    'msg' => lang('Notifications.user_details_updated_successfully')
                ]);
            } else {
                $errors = $this->UserModel->getLastErrors();
                $errorMsg = isset($errors['email']) ? lang('Notifications.email_already_in_use') : 
                            (isset($errors['username']) ? lang('Notifications.username_already_in_use') : 
                            lang('Notifications.error_updating_user_details'));
                return $this->response->setJSON([
                    'success' => false,
                    'status' => 0,
                    'msg' => $errorMsg,
                    'errors' => $errors
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', '[Admin/AdminController] User Update Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'status' => 0,
                'msg' => lang('Notifications.error_updating_user_details'),
                'errors' => ['general' => $e->getMessage()]
            ]);
        }
    }

    /**
     * Change user password
     */
    public function change_user_password()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => lang('Notifications.Method_Not_Allowed')
            ]);
        }

        $userId = $this->request->getPost('user_id');
        $newPassword = $this->request->getPost('new_password');
        
        try {
            if ($this->UserModel->adminChangePassword($userId, $newPassword)) {
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 1,
                    'msg' => lang('Notifications.user_password_changed_successfully')
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'status' => 0,
                    'msg' => lang('Notifications.error_changing_user_password'),
                    'errors' => $this->UserModel->getLastErrors()
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', '[Admin/AdminController] Password Change Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'status' => 0,
                'msg' => lang('Notifications.error_changing_user_password'),
                'errors' => ['general' => $e->getMessage()]
            ]);
        }
    }
	
	/**
     * Get user's current group
     */
    public function get_user_group()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => lang('Notifications.Method_Not_Allowed')
            ]);
        }

        $userId = $this->request->getPost('user_id');

        $group = $this->UserModel->getUserGroup($userId);
        
        return $this->response->setJSON([
            'success' => true,
            'group' => $group
        ]);
    }

    /**
     * Set user group/role
     */
    public function set_user_group()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => lang('Notifications.Method_Not_Allowed')
            ]);
        }

        $userId = $this->request->getPost('user_id');
        $group = $this->request->getPost('group');
        
        try {
            if ($this->UserModel->setUserGroup($userId, $group)) {
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 1,
                    'msg' => lang('Notifications.user_group_updated_successfully')
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'status' => 0,
                    'msg' => lang('Notifications.error_updating_user_group'),
                    'errors' => $this->UserModel->getLastErrors()
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', '[Admin/AdminController] User Group Update Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'status' => 0,
                'msg' => lang('Notifications.error_updating_user_group'),
                'errors' => ['general' => $e->getMessage()]
            ]);
        }
    }

    /**
     * Generate API key for user
     */
    public function generate_user_api_key()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => lang('Notifications.Method_Not_Allowed')
            ]);
        }

        $userId = $this->request->getPost('user_id');
        
        try {
            $apiKey = $this->UserModel->generateUserApiKey($userId);
            
            if ($apiKey) {
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 1,
                    'msg' => lang('Notifications.user_api_key_generated_successfully'),
                    'api_key' => $apiKey
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'status' => 0,
                    'msg' => lang('Notifications.error_generating_user_api_key'),
                    'errors' => $this->UserModel->getLastErrors()
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', '[Admin/AdminController] API Key Generation Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'status' => 0,
                'msg' => lang('Notifications.error_generating_user_api_key'),
                'errors' => ['general' => $e->getMessage()]
            ]);
        }
    }

    /**
     * Revoke API key for user
     */
    public function revoke_user_api_key()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => lang('Notifications.Method_Not_Allowed')
            ]);
        }

        $userId = $this->request->getPost('user_id');
        
        try {
            if ($this->UserModel->revokeUserApiKey($userId)) {
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 1,
                    'msg' => lang('Notifications.user_api_key_revoked_successfully')
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'status' => 0,
                    'msg' => lang('Notifications.error_revoking_user_api_key'),
                    'errors' => $this->UserModel->getLastErrors()
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', '[Admin/AdminController] API Key Revocation Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'status' => 0,
                'msg' => lang('Notifications.error_revoking_user_api_key'),
                'errors' => ['general' => $e->getMessage()]
            ]);
        }
    }

    /**
     * Get user's current API key status
     */
    public function get_user_api_key()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => lang('Notifications.Method_Not_Allowed')
            ]);
        }

        $userId = $this->request->getPost('user_id');

        $apiKey = $this->UserModel->getUserApiKey($userId);
        
        return $this->response->setJSON([
            'success' => true,
            'api_key' => $apiKey ? 'Generated' : null
        ]);
    }

    /**
     * Delete user
     */
    public function delete_user()
    {
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => lang('Notifications.Method_Not_Allowed')
            ]);
        }

        $userId = $this->request->getPost('user_id');
        
        try {
            if ($this->UserModel->softDeleteUser($userId)) {
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 1,
                    'msg' => lang('Notifications.user_deleted_successfully')
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
            log_message('error', '[Admin/AdminController] User Deletion Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'status' => 0,
                'msg' => lang('Notifications.error_deleting_user'),
                'errors' => ['general' => $e->getMessage()]
            ]);
        }
    }

	/***
	 * Email Logs
	 */
	public function email_logs_page()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
	
		$data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Email_Logs');
		$data['section'] = 'Admin';
		$data['subsection'] = 'Email_Logs';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		$data['pageUrl'] = base_url('admin-options/email-logs/');
	
		return view('dashboard/admin/email_logs', $data);
	}
	
	public function email_logs_data()
	{
		$emailLogModel = new \App\Models\EmailLogModel();
	
		$status = $this->request->getGet('status');
		$startDate = $this->request->getGet('start_date');
		$endDate = $this->request->getGet('end_date');
	
		log_message('debug', '[Admin/AdminController] Status: ' . $status);
		log_message('debug', '[Admin/AdminController] Start Date: ' . $startDate);
		log_message('debug', '[Admin/AdminController] End Date: ' . $endDate);
	
		if ($status) {
			$emailLogModel->where('status', $status);
		}
		if ($startDate) {
			$emailLogModel->where('created_at >=', $startDate . ' 00:00:00');
		}
		if ($endDate) {
			$emailLogModel->where('created_at <=', $endDate . ' 23:59:59');
		}
	
		$logs = $emailLogModel->orderBy('id', 'DESC')->findAll();
	
		// log_message('debug', '[Admin/AdminController] Email Log Query: ' . $emailLogModel->getLastQuery()->getQuery());
		// log_message('debug', '[Admin/AdminController] Email Log Filtered results: ' . json_encode($logs, JSON_PRETTY_PRINT));
	
		return $this->response->setJSON($logs);
	}
	
	public function view_email_log($id)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
	
		$emailLogModel = new \App\Models\EmailLogModel();
		$data['log'] = $emailLogModel->find($id);
	
		if (empty($data['log'])) {
			return redirect()->to('/admin-options/email-logs')->with('error', lang('Notifications.Email_log_not_found'));
		}
	
		$data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.View_Email_Log');
		$data['section'] = 'Admin';
		$data['subsection'] = 'Email_Logs';
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;
		$data['pageUrl'] = base_url('admin-options/email-logs/');
	
		return view('dashboard/admin/email_log_view', $data);
	}
	
	public function view_email_body($id)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
	
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

		log_message('debug', '[Admin/AdminController] Attaching image logo on data: ' . $email_logo);

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

	private function updateManifest($currentSettings)
	{
		log_message('info', '[Admin/AdminController] Updating manifest.json');
		
		// Use the fresh settings for the PWA icons
		$PWA_App_icon_192x192 = !empty($currentSettings['PWA_App_icon_192x192']) ? 
			'/writable/uploads/app-custom-assets/' . $currentSettings['PWA_App_icon_192x192'] : 
			'/assets/images/meraf-PWA_App_icon_192x192.png';
		
		$PWA_App_icon_512x512 = !empty($currentSettings['PWA_App_icon_512x512']) ? 
			'/writable/uploads/app-custom-assets/' . $currentSettings['PWA_App_icon_512x512'] : 
			'/assets/images/meraf-PWA_App_icon_512x512.png';
	
		// Extract file extension and corresponding mime types
		$extension192 = pathinfo($PWA_App_icon_192x192, PATHINFO_EXTENSION);
		$extension512 = pathinfo($PWA_App_icon_512x512, PATHINFO_EXTENSION);
		
		// Map common extensions to MIME types
		$mimeTypes = [
			'png' => 'image/png',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif' => 'image/gif',
			'webp' => 'image/webp',
			'svg' => 'image/svg+xml'
		];
		
		$mimeType192 = $mimeTypes[strtolower($extension192)] ?? 'image/png'; // Default to png if unknown
		$mimeType512 = $mimeTypes[strtolower($extension512)] ?? 'image/png'; // Default to png if unknown
	
		// Update the manifest.json using fresh settings
		$manifest = [
			"name" => $currentSettings['PWA_App_name'] ?? $currentSettings['appName'],
			"short_name" => $currentSettings['PWA_App_shortname'] ?? 'ProdPanel',
			"msPwa" => [
				"publisher" => "MERAF Digital Solutions"
			],
			"start_url" => "/",
			"display" => "standalone",
			"background_color" => "#1c2836",
			"theme_color" => "#000000",
			"icons" => [
				[
					"src" => $PWA_App_icon_192x192,
					"sizes" => "192x192",
					"type" => $mimeType192
				],
				[
					"src" => $PWA_App_icon_512x512,
					"sizes" => "512x512",
					"type" => $mimeType512
				]
			],
			"related_applications" => [
				[
					"platform" => "webapp",
					"url" => base_url('manifest.json')
					// "id" => "optional-app-id" // optional, only needed for Play Store, etc.
				]
			],
			"prefer_related_applications" => false
		];
	
		// Save the new manifest in ROOTPATH . 'public/manifest.json',
		$manifestPath = ROOTPATH . 'public/manifest.json';
		
		// Convert the PHP array to JSON with pretty printing (JSON_PRETTY_PRINT)
		$jsonContent = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		
		// Write the JSON content to the file
		if (file_put_contents($manifestPath, $jsonContent)) {
			log_message('info', 'PWA manifest.json updated successfully');
			return true;
		} else {
			log_message('error', 'Failed to update PWA manifest.json');
			return false;
		}
	}

	public function languageEditor()
	{
        $auth = $this->checkAdminAuthorization();
        if ($auth !== true) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => lang('Pages.forbidden_error_msg')
            ]);
        }

		$data['pageTitle'] = lang('Pages.Admin') . ' | ' . lang('Pages.Language_Editor');
		$data['section'] = 'Admin';
		$data['subsection'] = 'Language_Editor';
		$data['productNames'] = productList($this->userID);
		$data['sideBarMenu'] = $this->sideBarMenu;
		$data['userData'] = $this->userAcctDetails;
		$data['lastLoginHistory'] = json_encode($this->lastLoginHistory());
		$data['myConfig'] = $this->myConfig;    

		return view('dashboard/admin/language_editor', $data);
	}

	/**
	 * Get available languages
	 */
	public function getLanguages()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Pages.forbidden_error_msg')
			]);
		}

		$languages = [];
		$languagePath = APPPATH . 'Language/';
		
		if (is_dir($languagePath)) {
			$dirs = scandir($languagePath);
			foreach ($dirs as $dir) {
				if ($dir != '.' && $dir != '..' && is_dir($languagePath . $dir) && $dir != '.gitkeep') {
					$languages[] = $dir;
				}
			}
		}
		
		return $this->response->setJSON([
			'success' => true,
			'languages' => $languages
		]);
	}

	/**
	 * Get language files
	 */
	public function getFiles()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Pages.forbidden_error_msg')
			]);
		}

		$language = $this->request->getGet('language');
		
		if (!$language) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.missing_required_parameters', ['param' => 'language'])
			]);
		}
		
		$files = [];
		$languagePath = APPPATH . 'Language/' . $language . '/';
		
		if (is_dir($languagePath)) {
			$dirFiles = scandir($languagePath);
			foreach ($dirFiles as $file) {
				if ($file != '.' && $file != '..' && is_file($languagePath . $file) && pathinfo($file, PATHINFO_EXTENSION) == 'php') {
					$files[] = pathinfo($file, PATHINFO_FILENAME);
				}
			}
		} else {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.error_folder_doesnt_exists', ['folderPath' => $languagePath])
			]);
		}
		
		return $this->response->setJSON([
			'success' => true,
			'files' => $files
		]);
	}

	/**
	 * Get language keys
	 */
	public function getKeys()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Pages.forbidden_error_msg')
			]);
		}

		$language = $this->request->getGet('language');
		$file = $this->request->getGet('file');
		
		if (!$language || !$file) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.missing_required_parameters')
			]);
		}
		
		$filePath = APPPATH . 'Language/' . $language . '/' . $file . '.php';
		
		if (!file_exists($filePath)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.error_file_not_found', ['fileName' => $filePath])
			]);
		}
		
		// Load the language file
		$keys = require $filePath;
		
		if (!is_array($keys)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.error_invalid_json_format')
			]);
		}
		
		return $this->response->setJSON([
			'success' => true,
			'keys' => $keys
		]);
	}

	/**
	 * Add new language
	 */
	public function addLanguage()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Pages.forbidden_error_msg')
			]);
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.Method_Not_Allowed')
			]);
		}
		
		$languageCode = $this->request->getPost('language_code');
		$baseLanguage = $this->request->getPost('base_language');
		
		if (!$languageCode || !$baseLanguage) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.missing_required_parameters')
			]);
		}
		
		// Validate language code (ISO 639-1 format: 2 letters)
		if (!preg_match('/^[a-z]{2}$/', $languageCode)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Invalid language code. Please use ISO 639-1 format (2 letters).'
			]);
		}
		
		$newLanguagePath = APPPATH . 'Language/' . $languageCode;
		$baseLanguagePath = APPPATH . 'Language/' . $baseLanguage;
		
		// Check if language already exists
		if (is_dir($newLanguagePath)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Language already exists.'
			]);
		}
		
		// Check if base language exists
		if (!is_dir($baseLanguagePath)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.error_folder_doesnt_exists', ['folderPath' => $baseLanguagePath])
			]);
		}
		
		// Create new language directory
		if (!mkdir($newLanguagePath, 0755, true)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.error_creating_directory')
			]);
		}
		
		// Copy all files from base language
		$baseFiles = scandir($baseLanguagePath);
		$copiedFiles = 0;
		
		foreach ($baseFiles as $file) {
			if ($file != '.' && $file != '..' && is_file($baseLanguagePath . '/' . $file)) {
				// Load the base language file
				$baseKeys = require $baseLanguagePath . '/' . $file;
				
				// Create a new array with the same keys but empty values
				$newKeys = [];
				foreach ($baseKeys as $key => $value) {
					$newKeys[$key] = ''; // Empty value for new language
				}
				
				// Create the new language file
				$newFilePath = $newLanguagePath . '/' . $file;
				$content = "<?php\n// <?= lang('" . pathinfo($file, PATHINFO_FILENAME) . ".language_key')\nreturn " . var_export($newKeys, true) . ";\n";
				
				if (file_put_contents($newFilePath, $content)) {
					$copiedFiles++;
				}
			}
		}
		
		if ($copiedFiles > 0) {
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Language created successfully with ' . $copiedFiles . ' files.'
			]);
		} else {
			// Remove the directory if no files were copied
			rmdir($newLanguagePath);
			
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Failed to copy language files.'
			]);
		}
	}

	/**
	 * Add new language file
	 */
	public function addFile()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Pages.forbidden_error_msg')
			]);
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.Method_Not_Allowed')
			]);
		}
		
		$fileName = $this->request->getPost('file_name');
		
		if (!$fileName) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.missing_required_parameters')
			]);
		}
		
		// Validate file name (alphanumeric and underscores only)
		if (!preg_match('/^[a-zA-Z0-9_]+$/', $fileName)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Invalid file name. Use only letters, numbers, and underscores.'
			]);
		}
		
		// Get all language directories
		$languagePath = APPPATH . 'Language/';
		$languages = [];
		
		if (is_dir($languagePath)) {
			$dirs = scandir($languagePath);
			foreach ($dirs as $dir) {
				if ($dir != '.' && $dir != '..' && is_dir($languagePath . $dir) && $dir != '.gitkeep') {
					$languages[] = $dir;
				}
			}
		}
		
		if (empty($languages)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'No languages found.'
			]);
		}
		
		// Create the file in each language directory
		$createdFiles = 0;
		
		foreach ($languages as $language) {
			$filePath = $languagePath . $language . '/' . $fileName . '.php';
			
			// Check if file already exists
			if (file_exists($filePath)) {
				continue;
			}
			
			// Create empty language file
			$content = "<?php\n// <?= lang('" . $fileName . ".language_key')\nreturn [\n    // Add your language keys here\n];\n";
			
			if (file_put_contents($filePath, $content)) {
				$createdFiles++;
			}
		}
		
		if ($createdFiles > 0) {
			return $this->response->setJSON([
				'success' => true,
				'message' => 'File created successfully in ' . $createdFiles . ' languages.'
			]);
		} else {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Failed to create file or file already exists in all languages.'
			]);
		}
	}

	/**
	 * Add new language key
	 */
	public function addKey()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Pages.forbidden_error_msg')
			]);
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.Method_Not_Allowed')
			]);
		}
		
		$language = $this->request->getPost('language');
		$file = $this->request->getPost('file');
		$key = $this->request->getPost('key');
		$value = $this->request->getPost('value');
		
		if (!$language || !$file || !$key) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.missing_required_parameters')
			]);
		}
		
		// Validate key name (alphanumeric, underscores, and hyphens only)
		if (!preg_match('/^[a-zA-Z0-9_-]+$/', $key)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Invalid key name. Use only letters, numbers, underscores, and hyphens.'
			]);
		}
		
		// Get all language directories
		$languagePath = APPPATH . 'Language/';
		$languages = [];
		
		if (is_dir($languagePath)) {
			$dirs = scandir($languagePath);
			foreach ($dirs as $dir) {
				if ($dir != '.' && $dir != '..' && is_dir($languagePath . $dir) && $dir != '.gitkeep') {
					$languages[] = $dir;
				}
			}
		}
		
		if (empty($languages)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'No languages found.'
			]);
		}
		
		// Add the key to the specified language file
		$filePath = $languagePath . $language . '/' . $file . '.php';
		
		if (!file_exists($filePath)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.error_file_not_found', ['fileName' => $filePath])
			]);
		}
		
		// Load the language file
		$keys = require $filePath;
		
		if (!is_array($keys)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.error_invalid_json_format')
			]);
		}
		
		// Check if key already exists
		if (isset($keys[$key])) {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Key already exists in this language file.'
			]);
		}
		
		// Add the key to the array
		$keys[$key] = $value;
		
		// Save the updated file
		$content = "<?php\n// <?= lang('" . $file . ".language_key')\nreturn " . var_export($keys, true) . ";\n";
		
		if (!file_put_contents($filePath, $content)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.error_saving_file')
			]);
		}
		
		// Add the key to all other language files
		$addedToOtherLanguages = 0;
		
		foreach ($languages as $lang) {
			if ($lang == $language) {
				continue; // Skip the current language
			}
			
			$otherFilePath = $languagePath . $lang . '/' . $file . '.php';
			
			if (!file_exists($otherFilePath)) {
				continue; // Skip if file doesn't exist in this language
			}
			
			// Load the language file
			$otherKeys = require $otherFilePath;
			
			if (!is_array($otherKeys)) {
				continue; // Skip if file is not valid
			}
			
			// Add the key with an empty value
			$otherKeys[$key] = '';
			
			// Save the updated file
			$otherContent = "<?php\n// <?= lang('" . $file . ".language_key')\nreturn " . var_export($otherKeys, true) . ";\n";
			
			if (file_put_contents($otherFilePath, $otherContent)) {
				$addedToOtherLanguages++;
			}
		}
		
		return $this->response->setJSON([
			'success' => true,
			'message' => 'Key added successfully to ' . ($addedToOtherLanguages + 1) . ' languages.'
		]);
	}

	/**
	 * Update language key
	 */
	public function updateKey()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Pages.forbidden_error_msg')
			]);
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.Method_Not_Allowed')
			]);
		}
		
		$language = $this->request->getPost('language');
		$file = $this->request->getPost('file');
		$key = $this->request->getPost('key');
		$value = $this->request->getPost('value');
		
		if (!$language || !$file || !$key) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.missing_required_parameters')
			]);
		}
		
		$filePath = APPPATH . 'Language/' . $language . '/' . $file . '.php';
		
		if (!file_exists($filePath)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.error_file_not_found', ['fileName' => $filePath])
			]);
		}
		
		// Load the language file
		$keys = require $filePath;
		
		if (!is_array($keys)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.error_invalid_json_format')
			]);
		}
		
		// Check if key exists
		if (!isset($keys[$key])) {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Key does not exist in this language file.'
			]);
		}
		
		// Update the key
		$keys[$key] = $value;
		
		// Save the updated file
		$content = "<?php\n// <?= lang('" . $file . ".language_key')\nreturn " . var_export($keys, true) . ";\n";
		
		if (file_put_contents($filePath, $content)) {
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Key updated successfully.'
			]);
		} else {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.error_saving_file')
			]);
		}
	}

	/**
	 * Delete language key
	 */
	public function deleteKey()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Pages.forbidden_error_msg')
			]);
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.Method_Not_Allowed')
			]);
		}
		
		$language = $this->request->getPost('language');
		$file = $this->request->getPost('file');
		$key = $this->request->getPost('key');
		
		if (!$language || !$file || !$key) {
			return $this->response->setJSON([
				'success' => false,
				'message' => lang('Notifications.missing_required_parameters')
			]);
		}
		
		// Get all language directories
		$languagePath = APPPATH . 'Language/';
		$languages = [];
		
		if (is_dir($languagePath)) {
			$dirs = scandir($languagePath);
			foreach ($dirs as $dir) {
				if ($dir != '.' && $dir != '..' && is_dir($languagePath . $dir) && $dir != '.gitkeep') {
					$languages[] = $dir;
				}
			}
		}
		
		if (empty($languages)) {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'No languages found.'
			]);
		}
		
		$deletedCount = 0;
		
		// Delete the key from all language files
		foreach ($languages as $lang) {
			$filePath = $languagePath . $lang . '/' . $file . '.php';
			
			if (!file_exists($filePath)) {
				continue; // Skip if file doesn't exist
			}
			
			// Load the language file
			$keys = require $filePath;
			
			if (!is_array($keys)) {
				continue; // Skip if file is not valid
			}
			
			// Check if key exists
			if (!isset($keys[$key])) {
				continue; // Skip if key doesn't exist
			}
			
			// Remove the key
			unset($keys[$key]);
			
			// Save the updated file
			$content = "<?php\n// <?= lang('" . $file . ".language_key')\nreturn " . var_export($keys, true) . ";\n";
			
			if (file_put_contents($filePath, $content)) {
				$deletedCount++;
			}
		}
		
		if ($deletedCount > 0) {
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Key deleted successfully from ' . $deletedCount . ' languages.'
			]);
		} else {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Failed to delete key from any language.'
			]);
		}
	}

	public function upload_notification_badge_action()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}
	
		$file = $this->request->getFile('push_notification_badge');
		
		if (!$file || !$file->isValid() || $file->hasMoved()) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.invalid_file_upload')
			]);
		}
	
		$validationRule = [
			'push_notification_badge' => [
				'label' => 'Notification Badge',
				'rules' => 'uploaded[push_notification_badge]'
					. '|is_image[push_notification_badge]'
					. '|mime_in[push_notification_badge,image/jpg,image/jpeg,image/png,image/gif,image/svg+xml,image/webp]'
					. '|max_size[push_notification_badge,96]'
					. '|max_dims[push_notification_badge,96,96]',
			],
		];
	
		if (!$this->validate($validationRule)) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => $this->validator->getErrors()['push_notification_badge']
			]);
		}
	
		$ext = strtolower($file->getExtension());
		$newFilename = md5(uniqid((string)time(), true)) . '.' . $ext;
	
		$sourcePath = WRITEPATH . 'temp/' . $newFilename;
		$destinationPath = WRITEPATH . 'uploads/app-custom-assets/' . $newFilename;
	
		if ($file->move(dirname($sourcePath), basename($sourcePath), true)) {
	
			$success = $this->removeAlphaChannelAndSave($sourcePath, $destinationPath);
	
			if ($success) {
                unlink($sourcePath); // delete the temporary file
                
				if($this->deleteCustomAppAsset('push_notification_badge')) {

					$notificationBadgeUrl = base_url('writable/uploads/app-custom-assets/' . $newFilename);

					$this->UserSettingsModel->setUserSetting('push_notification_badge', $newFilename, 0);
	
					return $this->response->setJSON([
						'success' => true,
						'msg' => lang('Notifications.email_logo_uploaded_successfully'),
						'newNotificationBadge' => $notificationBadgeUrl,
					]);
				}
				else {
				    unlink($sourcePath); // delete the temporary file
					unlink($destinationPath); // delete the processed file

					return $this->response->setJSON([
						'success' => false,
						'msg' => lang('Notifications.error_deleting_previous_notification_badge')
					]);
				}
			}
	
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.error_processing_notification_badge')
			]);
		}
	
		return $this->response->setJSON([
			'success' => false,
			'msg' => lang('Notifications.error_uploading_notification_badge')
		]);
	}
	
	public function upload_private_key_file_action()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}
	
		$file = $this->request->getFile('fcm_private_key_file');
	
		if (!$file || !$file->isValid() || $file->hasMoved()) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.invalid_file_upload')
			]);
		}
	
		$validationRule = [
			'fcm_private_key_file' => [
				'label' => 'Private Key File',
				'rules' => 'uploaded[fcm_private_key_file]|mime_in[fcm_private_key_file,application/json,text/plain]',
			],
		];
	
		if (!$this->validate($validationRule)) {
			return $this->response->setJSON([
				'success' => false,
				'msg' => $this->validator->getError('fcm_private_key_file')
			]);
		}
	
		$ext = strtolower($file->getExtension());
		$newFilename = md5(uniqid((string)time(), true)) . '.' . $ext;
		$destinationPath = USER_DATA_PATH . $newFilename;
	
		if ($file->move(dirname($destinationPath), basename($destinationPath), true)) {
			$success = true;
	
			if ($this->myConfig['fcm_private_key_file']) {
				$success = @unlink(USER_DATA_PATH . $this->myConfig['fcm_private_key_file']);
			}
	
			if ($success) {
				$this->UserSettingsModel->setUserSetting('fcm_private_key_file', $newFilename, 0);
				return $this->response->setJSON([
					'success' => true,
					'msg' => lang('Notifications.private_key_uploaded_successfully')
				]);
			} else {
				@unlink($destinationPath);
				return $this->response->setJSON([
					'success' => false,
					'msg' => lang('Notifications.error_deleting_previous_private_key')
				]);
			}
		}
	
		// Only reached if file->move() fails
		return $this->response->setJSON([
			'success' => false,
			'msg' => lang('Notifications.error_uploading_private_key')
		]);
	}	

	public function delete_private_key_file_action()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}

		$response = [
			'success' => false,
			'msg'     => lang('Notifications.unable_to_delete_file'),
		];
	
		if(!$this->myConfig['fcm_private_key_file']) {
			$response = [
				'success' => false,
				'msg'     => lang('Notifications.no_private_key_file'),
			];

			return $this->response->setJSON($response);
		}

		$privateKeyFile = USER_DATA_PATH . $this->myConfig['fcm_private_key_file'];
		
		if (file_exists($privateKeyFile)) {
			if (unlink($privateKeyFile)) {
				// Update the fcm settings
				$this->UserSettingsModel->setUserSetting('fcm_private_key_file', NULL, 0);
				$this->UserSettingsModel->setUserSetting('push_notification_feature_enabled', NULL, 0);

				$response = [
					'success' => true,
					'msg'     => lang('Notifications.deleted_file_success'),
				];
			} else {
				$response = [
					'success' => false,
					'msg'     => lang('Notifications.failed_delete_file'),
				];
			}
		} else {
			$response = [
				'success' => false,
				'msg'     => lang('Notifications.files_does_not_exist'),
			];
		}
	
		return $this->response->setJSON($response);
	}

	public function testPushNotification()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}
		
		// Get current settings
		$currentSettings = getMyConfig();
		
		// Check if push notifications are enabled
		if ($currentSettings['push_notification_feature_enabled'] !== 'on') {
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.test_notification_not_enabled')
			]);
		}
		
		// Get user ID (either current user or specific user ID)
		$userId = $this->request->getGet('user_id') ?? auth()->id();
		
		// Create notification data
		$title = 'Test Notification';
		$body = 'This is a test notification sent at ' . Time::now();
		$type = 'test_notification';
		$link = base_url('admin-options/global-settings');
		
		try {
			// Use the notification helper to send the notification
			$result = add_notification(
				$body,
				$type,
				$link,
				$userId,
				$title
			);
			
			if ($result) {
				return $this->response->setJSON([
					'success' => true,
					'msg' => lang('Notifications.test_notification_success'),
					'details' => [
						'user_id' => $userId,
						'title' => $title,
						'body' => $body,
						'type' => $type,
						'link' => $link
					]
				]);
			} else {
				return $this->response->setJSON([
					'success' => false,
					'msg' => lang('Notifications.test_notification_failed')
				]);
			}
		} catch (\Exception $e) {
			log_message('error', lang('Notifications.test_notification_error') .': ' . $e->getMessage());
			
			return $this->response->setJSON([
				'success' => false,
				'msg' => lang('Notifications.test_notification_error') .': ' . $e->getMessage()
			]);
		}
	}

	private function removeAlphaChannelAndSave(string $sourcePath, string $destinationPath): bool
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
		
		// Check if Imagick is installed
		if (!class_exists('Imagick')) {
			return false;
		}
	
		try {
			$imagick = new \Imagick();
			$imagick->setBackgroundColor(new \ImagickPixel('transparent'));
	
			// Read only the first frame (for multi-frame files)
			$imagick->readImage($sourcePath . '[0]');
	
			// Ensure alpha channel exists
			$imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);
	
			// Check current dimensions
			$width = $imagick->getImageWidth();
			$height = $imagick->getImageHeight();
	
			// Only resize if width > 96 OR height > 96
			if ($width > 96 || $height > 96) {
				// Resize keeping aspect ratio, max 96x96
				$imagick->thumbnailImage(96, 96, true, true);
			}
	
			// Set format to PNG
			$imagick->setImageFormat('png');
	
			// Flatten against transparent background
			$flattened = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
	
			// Save the processed image
			$flattened->writeImage($destinationPath);
	
			// Free up memory
			$flattened->clear();
			$flattened->destroy();
			$imagick->clear();
			$imagick->destroy();
	
			return true;
		} catch (\Exception $e) {
			// Optional: log_message('error', $e->getMessage());
			return false;
		}
	}

	private function updateFirebaseFiles($currentSettings)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
		
		log_message('info', '[Admin/AdminController] Updating Firebase Init files');
		
		// Paths to the FCM init file
		$initJsPath = ROOTPATH . 'public/assets/js/firebase-init.js';
		
		// Check if features are enabled
		$fcmEnabled = $currentSettings['push_notification_feature_enabled'] === 'on';
		
		// If FCM is enabled, update the init.js file
		if ($fcmEnabled) {
			// Update the Firebase init.js to point to the combined service worker
			$initJsContent = $this->generateFirebaseInitJsContent($currentSettings);
			
			// Write init.js file
			if (file_exists($initJsPath) && is_writable($initJsPath)) {
				if (file_put_contents($initJsPath, $initJsContent)) {
					log_message('info', 'Firebase Init JS updated successfully.');
				} else {
					log_message('error', 'Failed to update Firebase Init JS.');
					return false;
				}
			} else if (!file_exists($initJsPath)) {
				// Create new init.js file
				if (file_put_contents($initJsPath, $initJsContent)) {
					log_message('info', 'Firebase Init JS created successfully.');
				} else {
					log_message('error', 'Failed to create Firebase Init JS.');
					return false;
				}
			} else {
				log_message('error', 'Firebase Init JS file is not writable.');
				return false;
			}
		}
		
		return true;
	}

	private function generatePwaServiceWorkerContent($currentSettings)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
		
		$shortName = $currentSettings['PWA_App_shortname'];
		$pwaVersion = '1.0.0';
    	$serviceWorkerPath = ROOTPATH . 'public/service-worker.js';

		// Assets
		$appIcon = $currentSettings['appIcon'] ? str_replace(base_url(), '/', $currentSettings['appIcon']) : '/assets/images/meraf-appIcon.png';
		$appLogo_light = $currentSettings['appLogo_light'] ? str_replace(base_url(), '/', $currentSettings['appLogo_light']) : '/assets/images/meraf-appLogo_light.png';
		$appLogo_dark = $currentSettings['appLogo_dark'] ? str_replace(base_url(), '/', $currentSettings['appLogo_dark']) : '/assets/images/meraf-appLogo_dark.png';
		$PWA_App_icon_192x192 = $currentSettings['PWA_App_icon_192x192'] ? '/writable/uploads/app-custom-assets/' . $currentSettings['PWA_App_icon_192x192'] : '/assets/images/meraf-PWA_App_icon_192x192.png';
		$PWA_App_icon_512x512 = $currentSettings['PWA_App_icon_512x512'] ? '/writable/uploads/app-custom-assets/' . $currentSettings['PWA_App_icon_512x512'] : '/assets/images/meraf-PWA_App_icon_512x512.png';
    
    	// If SW exists, get the current pwa version
    	if (file_exists($serviceWorkerPath)) {
    		$content = file_get_contents($serviceWorkerPath);
    
    		// Match the version pattern in CACHE_NAME (e.g., v1.0.0)
    		if (preg_match('/CACHE_NAME\s*=\s*[\'"]' . preg_quote($shortName) . '-PWA\s*-v(\d+)\.(\d+)\.(\d+)[\'"]/', $content, $matches)) {
    			$major = (int) $matches[1];
    			$minor = (int) $matches[2];
    			$patch = (int) $matches[3];
    
    			// Increment logic: 1.0.9 → 1.1.0, 1.9.9 → 2.0.0
    			if ($patch >= 9) {
    				$patch = 0;
    				$minor++;
    				if ($minor > 9) {
    					$minor = 0;
    					$major++;
    				}
    			} else {
    				$patch++;
    			}
    
    			$pwaVersion = "{$major}.{$minor}.{$patch}";
    		}
    	}

		return <<<EOT
		//--------------------//
		// PWA Service Worker //
		//--------------------//

		const CACHE_NAME = '{$shortName}-PWA-v{$pwaVersion}';
		const STATIC_ASSETS = [
			'/assets/css/style.min.css',
			'/assets/css/style-rtl.min.css',
			'/assets/css/style-dark.min.css',
			'/assets/css/style-dark-rtl.min.css',
			'/assets/css/icons.min.css',
			'/assets/css/icons-rtl.min.css',
			'/assets/css/bootstrap.min.css',
			'/assets/css/bootstrap-rtl.min.css',
			'/assets/css/bootstrap-dark.min.css',
			'/assets/css/bootstrap-dark-rtl.min.css',
			'/assets/libs/simplebar/simplebar.min.css',
			'/assets/libs/@mdi/font/css/materialdesignicons.min.css',
			'/assets/libs/@iconscout/unicons/css/line.css',
			'/assets/js/app.js',
			'/assets/js/fullcalendar.init.js',
			'/assets/js/notifications.js',
			'/assets/js/plugins.init.js',
			'/assets/libs/bootstrap/js/bootstrap.bundle.min.js',
			'/assets/libs/feather-icons/feather.min.js',
			'/assets/libs/simplebar/simplebar.min.js',
			'/assets/libs/jquery-3.7.1.min.js',
			'/assets/fonts/ajax-loader.gif',
			'/assets/images/403.svg',
			'/assets/images/404.svg',
			'/assets/images/503.svg',
			'/assets/images/bg.png',
			'{$appIcon}',
			'{$appLogo_dark}',
			'{$appLogo_light}',
			'/assets/images/meraf-appLogo.png',
			'{$PWA_App_icon_192x192}',
			'{$PWA_App_icon_512x512}',
			'/assets/images/shape01.png',
			'/offline.html'
		];

		// Install event: Cache static assets
		self.addEventListener('install', (event) => {
		event.waitUntil(
			caches.open(CACHE_NAME).then((cache) => {
			return cache.addAll(STATIC_ASSETS);
			})
		);
		});

		// Activate event: Clean up old caches
		self.addEventListener('activate', (event) => {
		event.waitUntil(
			caches.keys().then((cacheNames) => {
			return Promise.all(
				cacheNames
				.filter((cacheName) => cacheName !== CACHE_NAME)
				.map((cacheName) => caches.delete(cacheName))
			);
			})
		);
		});

		// Fetch event: Handle requests
		self.addEventListener('fetch', (event) => {
		const { request } = event;

		// 1. If it's a navigation request (HTML page)
		if (request.mode === 'navigate') {
			event.respondWith(
			fetch(request)
				.catch(() => caches.match('/offline.html')) // fallback if offline
			);
			return;
		}

		// 2. If it's a static asset (CSS, JS, fonts, images)
		if (request.destination === 'style' || 
			request.destination === 'script' ||
			request.destination === 'font' ||
			request.destination === 'image') {
			
			event.respondWith(
			caches.match(request)
				.then((cachedResponse) => {
				if (cachedResponse) {
					return cachedResponse;
				}
				// Otherwise fetch from network and add it to cache
				return fetch(request).then((networkResponse) => {
					return caches.open(CACHE_NAME).then((cache) => {
					cache.put(request, networkResponse.clone());
					return networkResponse;
					});
				});
				})
			);
			return;
		}

		// 3. For all other requests, just fetch normally
		event.respondWith(fetch(request));
		});\n\n
		EOT;
	}
	
	private function generateFirebaseServiceWorkerContent($currentSettings)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
		
		// Get the badge URL
		$badgeUrl = !empty($currentSettings['push_notification_badge']) ? 
			$currentSettings['push_notification_badge'] : 
			'/assets/images/meraf-push_notification_badge.png';
		
		// Create the service worker content with the settings
		return <<<EOT
		//-----------------------------------------//
		// Firebase Cloud Messaging Service Worker //
		//-----------------------------------------//
		
		// Import and configure the Firebase SDK
		importScripts('https://www.gstatic.com/firebasejs/9.6.0/firebase-app-compat.js');
		importScripts('https://www.gstatic.com/firebasejs/9.6.0/firebase-messaging-compat.js');
		
		firebase.initializeApp({
			apiKey: "{$currentSettings['fcm_apiKey']}",
			authDomain: "{$currentSettings['fcm_authDomain']}",
			projectId: "{$currentSettings['fcm_projectId']}",
			storageBucket: "{$currentSettings['fcm_storageBucket']}",
			messagingSenderId: "{$currentSettings['fcm_messagingSenderId']}",
			appId: "{$currentSettings['fcm_appId']}",
			measurementId: "{$currentSettings['fcm_measurementId']}"
		});
		
		const messaging = firebase.messaging();
		
		// Handle background messages
		messaging.onBackgroundMessage((payload) => {
			console.log('[service-worker.js] Received background message:', payload);
			
			// For data-only messages, notification content is in the data payload
			const data = payload.data || {};
			
			// Check if the app is visible before showing notification
			return clients.matchAll({
				type: 'window',
				includeUncontrolled: true
			}).then((windowClients) => {
				// Check if there is a focused window
				const clientIsFocused = windowClients.some((windowClient) => {
					return windowClient.focused;
				});
				
				// If a window is focused and foreground is true, don't show notification
				if (clientIsFocused || data.foreground === 'true') {
					console.log('[service-worker.js] App is focused, skipping notification');
					return;
				}
				
				// Otherwise, show notification using data from payload.data
				const notificationTitle = data.title;
				const notificationOptions = {
					body: data.body,
					icon: data.icon || '/assets/images/meraf-appIcon.png',
					badge: data.badge || '{$badgeUrl}',
					data: data,
					// Add a tag to replace existing notifications with the same tag
					// This prevents stacking multiple notifications
					tag: 'notification-' + (data.id || Date.now())
				};
				
				return self.registration.showNotification(notificationTitle, notificationOptions);
			}).catch(error => {
				console.error('[service-worker.js] Error checking client focus:', error);
				// Fall back to showing notification anyway
				const notificationTitle = data.title;
				const notificationOptions = {
					body: data.body,
					icon: data.icon || '/assets/images/meraf-appIcon.png',
					badge: data.badge || '{$badgeUrl}',
					data: data,
					tag: 'notification-' + (data.id || Date.now())
				};
				return self.registration.showNotification(notificationTitle, notificationOptions);
			});
		});
		
		// Handle notification click
		self.addEventListener('notificationclick', (event) => {
			console.log('[service-worker.js] Notification clicked:', event);
			
			event.notification.close();
			
			// This looks to see if the current is already open and focuses if it is
			event.waitUntil(
				clients.matchAll({
					type: "window"
				})
				.then((clientList) => {
					const payload = event.notification.data;
					const url = payload && payload.link ? payload.link : '/dashboard';
					
					for (const client of clientList) {
						if (client.url === url && 'focus' in client) {
							return client.focus();
						}
					}
					
					if (clients.openWindow) {
						return clients.openWindow(url);
					}
				})
			);
		});
		EOT;
	}
	
	private function generateFirebaseInitJsContent($settings)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
		
		return <<<EOT
		// Firebase initialization and token management

		document.addEventListener('DOMContentLoaded', function() {
			if (!firebase.messaging.isSupported()) {
				return;
			}

			const firebaseConfig = {
				apiKey: "{$settings['fcm_apiKey']}",
				authDomain: "{$settings['fcm_authDomain']}",
				projectId: "{$settings['fcm_projectId']}",
				storageBucket: "{$settings['fcm_storageBucket']}",
				messagingSenderId: "{$settings['fcm_messagingSenderId']}",
				appId: "{$settings['fcm_appId']}",
				measurementId: "{$settings['fcm_measurementId']}"
			};

			firebase.initializeApp(firebaseConfig);

			let messaging;
			let swRegistration;

			const enableNotificationsBtn = document.getElementById('enable-notifications');
			const allowWebpushPrompt = document.getElementById('webpush-allow');
			const disallowWebpushPrompt = document.getElementById('webpush-deny');
			const webpushWindowPrompt = document.getElementById('webpush-prompt');
			const registeredNotificationBtn = document.getElementById('registered-device');
			const defaultText = enableNotificationsBtn ? enableNotificationsBtn.innerHTML : '';
			const webpushPromptDefaultText = allowWebpushPrompt ? allowWebpushPrompt.innerHTML : '';

			const deviceId = getDeviceId();
			const viewMode = isStandaloneMode() ? 'standalone' : 'browser';

			fetch('/notification/current-device', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-Requested-With': 'XMLHttpRequest'
				},
				body: JSON.stringify({ deviceId })
			});

			function generateDeviceId() {
				const fingerprint = `\${navigator.userAgent}|\${screen.width}x\${screen.height}|\${screen.colorDepth}|\${Intl.DateTimeFormat().resolvedOptions().timeZone}|\${navigator.language}`;
				let hash = 0;
				for (let i = 0; i < fingerprint.length; i++) {
					hash = ((hash << 5) - hash) + fingerprint.charCodeAt(i);
					hash = hash & 0xFFFFFFFF;
				}
				hash = hash & 0x7FFFFFFF;
				return hash.toString(16);
			}

			function getDeviceId() {
				let deviceId = getCookie('device_id');
				if (!deviceId) {
					deviceId = generateDeviceId();
					console.log('Current device ID: ' + deviceId);
					setCookie('device_id', deviceId, 365);
				}
				return deviceId;
			}

			function setCookie(name, value, days) {
				const date = new Date();
				date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
				document.cookie = `\${name}=\${value}; expires=\${date.toUTCString()}; path=/; SameSite=Lax`;
			}

			function getCookie(name) {
				const nameEQ = name + '=';
				const ca = document.cookie.split(';');
				for (let c of ca) {
					while (c.charAt(0) === ' ') c = c.substring(1);
					if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length);
				}
				return null;
			}

			function requestNotificationPermission() {
				enableNotificationsBtn.innerHTML = `<i class="ti ti-bell"></i> \${lang_CheckingPermisisions}`;
				enableNotificationsBtn.disabled = true;

				allowWebpushPrompt.innerHTML = lang_CheckingPermisisions;
				allowWebpushPrompt.disabled = true;


				Notification.requestPermission().then(permission => {
					if (permission === 'granted') {
						if ('serviceWorker' in navigator) {
							navigator.serviceWorker.register('/service-worker.js', { scope: '/' })
								.then(function(registration) {
									console.log('✅ FCM Service Worker registered with scope:', registration.scope);
									navigator.serviceWorker.ready.then((readyRegistration) => {
										console.log('✅ FCM Service Worker is active and ready.');
										getTokenWithRetry(readyRegistration);
									});
								}).catch(function(err) {
									console.error('❌ FCM Service Worker registration failed:', err);
									showToast('danger', lang_Failed_to_register_SW);
									resetNotificationButton();
								});
						}
					} else {
						showToast('danger', lang_Permission_denied);
						resetNotificationButton();
					}
				});
			}

			function getTokenWithRetry(registration, retries = 5, delay = 2000) {
				messaging.getToken({
					vapidKey: '{$settings['fcm_vapidKey']}',
					serviceWorkerRegistration: registration
				})
				.then(currentToken => {
					if (currentToken) {
						saveTokenToServer(currentToken).then(success => {
							if (success) {
								enableNotificationsBtn.style.display = 'none';
								webpushWindowPrompt.style.display = 'none';
								registeredNotificationBtn.style.display = 'inline-block';
								showToast('success', lang_Success_enabled_push_notification);
							} else {
								showToast('info', lang_Unable_to_verify_permission);
								resetNotificationButton();
							}
						});
					} else if (retries > 0) {
						console.log(`Token not ready. Retrying in \${delay}ms...`);
						setTimeout(() => getTokenWithRetry(registration, retries - 1, delay), delay);
					} else {
						console.error('Token still not available after retries.');
						showToast('danger', lang_Failed_confirming_permission);
						resetNotificationButton();
					}
				})
				.catch(err => {
					console.error('Error retrieving token:', err);
					showToast('danger', lang_Error_retrieving_device_token);
					resetNotificationButton();
				});
			}

			function resetNotificationButton() {
				enableNotificationsBtn.innerHTML = defaultText;
				enableNotificationsBtn.disabled = false;

				showWebpushPrompt(true);
				allowWebpushPrompt.innerHTML = webpushPromptDefaultText;
				allowWebpushPrompt.disabled = false;
			}

			function saveTokenToServer(token) {
				const deviceInfo = {
					userAgent: navigator.userAgent,
					deviceId,
					platform: navigator.platform,
					screenSize: `\${screen.width}x\${screen.height}`,
					mode: viewMode
				};

				enableNotificationsBtn.innerHTML = `<i class="ti ti-bell"></i> \${lang_Registering_device}`;
				enableNotificationsBtn.disabled = true;

				allowWebpushPrompt.innerHTML = lang_Registering_device;
				allowWebpushPrompt.disabled = true;

				return fetch('/notification/registerToken', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-Device-ID': deviceId
					},
					body: JSON.stringify({ token, device: JSON.stringify(deviceInfo) })
				})
					.then(response => response.json())
					.then(data => data.success)
					.catch(error => {
						console.error('Error saving token to server:', error);
						return false;
					});
				
			}

			// Check notification subscription and token
			function checkNotificationStatus() {
				if (!messaging || !swRegistration) return;

				if (Notification.permission === 'granted') {
					messaging.getToken({
						vapidKey: '{$settings['fcm_vapidKey']}',
						serviceWorkerRegistration: swRegistration
					}).then(currentToken => {
						if (currentToken) {
							checkDeviceRegistration(deviceId, currentToken,).then(isRegistered => {
								if (isRegistered) {
									enableNotificationsBtn.style.display = 'none';
									registeredNotificationBtn.style.display = 'inline-block';
									webpushWindowPrompt.style.display = 'none';
								} else {
									enableNotificationsBtn.style.display = 'inline-block';
									registeredNotificationBtn.style.display = 'none';
									showWebpushPrompt(true);
								}
							});
						} else {
							console.warn('⚠️ No registration token available.');
							enableNotificationsBtn.style.display = 'inline-block';
							registeredNotificationBtn.style.display = 'none';
							showWebpushPrompt(true);
						}
					}).catch(err => {
						console.error('❌ Error checking token:', err);
						enableNotificationsBtn.style.display = 'inline-block';
						registeredNotificationBtn.style.display = 'none';
						showWebpushPrompt(true);
					});
				} else {
					enableNotificationsBtn.style.display = 'inline-block';
					registeredNotificationBtn.style.display = 'none';
					showWebpushPrompt(true);
				}
			}
			
			let lastRefresh = 0;

			// Handle token refresh
			function refreshToken() {
				if (!messaging || !swRegistration) return;

				if (Date.now() - lastRefresh < 60000) {
					// Don't refresh again if within 1 minute
					return;
				}
				lastRefresh = Date.now();

				if (Notification.permission === 'granted') {
					messaging.getToken({
						vapidKey: '{$settings['fcm_vapidKey']}',
						serviceWorkerRegistration: swRegistration
					})
					.then(currentToken => {
						if (currentToken) {
							saveTokenToServer(currentToken); // Update last_used timestamp, etc.
						} else {
							console.warn('⚠️ No token to refresh');
							resetNotificationButton();
							enableNotificationsBtn.style.display = 'inline-block';
							registeredNotificationBtn.style.display = 'none';
							showWebpushPrompt(true);
						}
					})
					.catch(err => {
						console.error('❌ Error refreshing token:', err);
					});
				}
			}
			
			// Call refreshToken on page load
			window.addEventListener('load', function() {
				if (firebase.messaging.isSupported()) {
					setTimeout(refreshToken, 3000); // Delay to ensure everything is loaded
				}
			});

			function checkDeviceRegistration(deviceId, token) {
				return fetch('/notification/checkDeviceRegistration', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-Requested-With': 'XMLHttpRequest'
					},
					body: JSON.stringify({ deviceId, token })
				})
				.then(response => response.json())
				.then(data => data.isRegistered)
				.catch(error => {
					console.error('Error checking device registration:', error);
					return false;
				});
			}

			function registerOnMessageHandler() {
				if (messaging && typeof messaging.onMessage === 'function') {
					messaging.onMessage(payload => {
						// For data-only messages, all notification content is in the data payload
						const data = payload.data || {};
						
						// Mark as foreground message
						data.foreground = 'true';
						
						// Refresh notifications list if function exists
						if (typeof loadNotifications === 'function') loadNotifications();
						
						// Show toast notification using data from payload.data
						if (typeof showToast === 'function' && data.title && data.body) {
							showToast('info', `\${data.title}: \${data.body} <a href="\${data.link}" class="alert-link">[link]</a>`);
							loadNotifications();
						}
					});
				} else {
					console.warn('📭 messaging is not ready yet, retrying...');
					setTimeout(registerOnMessageHandler, 500); // retry after short delay
				}
			}	

			// Register Service Worker
			if ('serviceWorker' in navigator && firebase.messaging.isSupported()) {
				navigator.serviceWorker.register('/service-worker.js', { scope: '/' })
					.then(function (registration) {
						console.log('✅ FCM Service Worker registered with scope:', registration.scope);
						swRegistration = registration;

						navigator.serviceWorker.ready.then(function (readyRegistration) {
							console.log('✅ FCM Service Worker is active and ready.');
							messaging = firebase.messaging();
							registerOnMessageHandler();
							checkNotificationStatus();

							if (isStandaloneMode()) {
								console.log('📱 App running in standalone (PWA) mode');
								refreshToken();
							} else {
								console.log('🧭 App running in browser/tab mode');
							}
						});
					})
					.catch(function (err) {
						console.error('❌ FCM Service Worker registration failed:', err);
						showToast('danger', 'Failed to register service worker.');
						resetNotificationButton();
					});
			}

			function disallowWebpushPromptCookie() {
				// Set expiration date to 1 year from now
				const expiryDate = new Date();
				expiryDate.setFullYear(expiryDate.getFullYear() + 1);
				const expires = "expires=" + expiryDate.toUTCString();
			
				let cookieValue = "webprompt_disallowed=true; " +
								  expires + "; " +
								  "path=/; " +
								  "secure; " +
								  "samesite=Strict";
			
				// Set the cookie
				document.cookie = cookieValue;

				// Hide the webprompt
				webpushWindowPrompt.style.display = 'none';
			}

			function getCookie(name) {
				const value = `; \${document.cookie}`;
				const parts = value.split(`; \${name}=`);
				if (parts.length === 2) {
					return parts.pop().split(';').shift();
				}
				return null;
			}
			
			function showWebpushPrompt(enabled) {
				const cookieValue = getCookie('webprompt_disallowed');
			
				// Hide the prompt if:
				// - The function parameter `enabled` is false
				// - OR the cookie exists and its value is 'true'
				if (enabled === false || cookieValue === 'true') {
					webpushWindowPrompt.style.display = 'none';
				} else {
					webpushWindowPrompt.style.display = 'inline-block'
				}
			}

			function isStandaloneMode() {
				return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
			}


			if (enableNotificationsBtn || allowWebpushPrompt) {
				enableNotificationsBtn.addEventListener('click', requestNotificationPermission);
				allowWebpushPrompt.addEventListener('click', requestNotificationPermission);
			} else {
				console.error('Enable notifications button not found');
			}

			if (disallowWebpushPrompt) {
				disallowWebpushPrompt.addEventListener('click', disallowWebpushPromptCookie);
			}
		});
		EOT;
	}

	private function generateCombinedServiceWorkerContent($currentSettings)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
		
		$serviceWorkerPath = ROOTPATH . 'public/service-worker.js';
		$pwaEnabled = $currentSettings['PWA_App_enabled'];
		$fcmEnabled = $currentSettings['push_notification_feature_enabled'];

		// Start building the service worker content
		$content = "/******************\n";
		$content .= "* Service Worker *\n";
		$content .= "*****************/\n\n";
		
		// Define cache name and assets to cache for PWA functionality
		if ($pwaEnabled) {
			$content .= $this->generatePwaServiceWorkerContent($currentSettings);
		}
		
		// Add Firebase imports and initialization if FCM is enabled
		if ($fcmEnabled) {
			$content .= $this->generateFirebaseServiceWorkerContent($currentSettings);
		}

		// Write service-worker.js file
		if (file_exists($serviceWorkerPath) && is_writable($serviceWorkerPath)) {
			if (file_put_contents($serviceWorkerPath, $content)) {
				log_message('info', 'Service worker JS updated successfully.');
				return true;
			} else {
				log_message('error', 'Failed to update Service worker JS.');
				return false;
			}
		} else if (!file_exists($serviceWorkerPath)) {
			// Create new init.js file
			if (file_put_contents($serviceWorkerPath, $content)) {
				log_message('info', 'Service worker JS created successfully.');
				return true;
			} else {
				log_message('error', 'Failed to create Service worker JS.');
				return false;
			}
		} else {
			log_message('error', 'Service worker JS is not writable.');
			return false;
		}
		
		// Delete service if either is enabled
		if($pwaEnabled && !$fcmEnabled) {
		    if (file_exists($serviceWorkerPath) && is_writable($serviceWorkerPath)) {
		        if(unlink($$serviceWorkerPath)) {
		            return true;
		        }
		        else {
		            return false;
		        }
		    }
		}
		
		return false;
	}

	/**
	 * Check if the required dependencies for the selected cache handler are installed
	 */
	private function checkCacheDependencies($handler)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
		
		switch ($handler) {
			case 'memcached':
				if (!extension_loaded('memcached')) {
					return lang('Notifications.memcached_extension_not_installed');
				}
				break;
			case 'redis':
				if (!extension_loaded('redis')) {
					return lang('Notifications.redis_extension_not_installed');
				}
				break;
			case 'predis':
				// Predis is a PHP library, so we don't need to check for an extension
				break;
			case 'wincache':
				if (!extension_loaded('wincache')) {
					return lang('Notifications.wincache_extension_not_installed');
				}
				break;
		}
		
		return false; // No dependency issues
	}

	/**
	 * Update the Cache.php configuration file with the selected handler
	 */
	private function updateCacheConfig($handler)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
		
		$cacheConfigPath = APPPATH . 'Config/Cache.php';
		$content = file_get_contents($cacheConfigPath);
		
		// Set the backup handler to 'file' if the selected handler is not 'dummy' or 'file'
		$backupHandler = ($handler !== 'dummy' && $handler !== 'file') ? 'file' : 'dummy';
		
		// Update the handler value
		$content = preg_replace(
			"/public string \\\$handler = ENVIRONMENT === 'development' \? '.*?' : '.*?';/",
			"public string \\\$handler = ENVIRONMENT === 'development' ? 'dummy' : '$handler';",
			$content
		);
		
		// Update the backup handler value
		$content = preg_replace(
			"/public string \\\$backupHandler = '.*?';/",
			"public string \\\$backupHandler = '$backupHandler';",
			$content
		);
		
		// Write the updated content back to the file
		return file_put_contents($cacheConfigPath, $content);
	}

	/**
	 * Update the Cache.php configuration file with memcached settings
	 */
	private function updateMemcachedConfig($settings)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}
		
		$cacheConfigPath = APPPATH . 'Config/Cache.php';
		$content = file_get_contents($cacheConfigPath);
		
		// Update the memcached settings
		$memcachedConfig = "public array \$memcached = [\n";
		$memcachedConfig .= "        'host'   => '{$settings['host']}',\n";
		$memcachedConfig .= "        'port'   => {$settings['port']},\n";
		$memcachedConfig .= "        'weight' => {$settings['weight']},\n";
		$memcachedConfig .= "        'raw'    => " . ($settings['raw'] ? 'true' : 'false') . ",\n";
		$memcachedConfig .= "    ];";
		
		// Replace the memcached configuration in the file
		$content = preg_replace(
			"/public array \\\$memcached = \[.*?\];/s",
			$memcachedConfig,
			$content
		);
		
		// Write the updated content back to the file
		return file_put_contents($cacheConfigPath, $content);
	}

	/**
	 * Update the Cache.php configuration file with redis settings
	 */
	private function updateRedisConfig($settings)
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		$cacheConfigPath = APPPATH . 'Config/Cache.php';
		$content = file_get_contents($cacheConfigPath);
		
		// Format the password value correctly
		$passwordValue = empty($settings['password']) ? 'null' : "'{$settings['password']}'";
		
		// Update the redis settings
		$redisConfig = "public array \$redis = [\n";
		$redisConfig .= "        'host'     => '{$settings['host']}',\n";
		$redisConfig .= "        'password' => {$passwordValue},\n";
		$redisConfig .= "        'port'     => {$settings['port']},\n";
		$redisConfig .= "        'timeout'  => {$settings['timeout']},\n";
		$redisConfig .= "        'database' => {$settings['database']},\n";
		$redisConfig .= "    ];";
		
		// Replace the redis configuration in the file
		$content = preg_replace(
			"/public array \\\$redis = \[.*?\];/s",
			$redisConfig,
			$content
		);
		
		// Write the updated content back to the file
		return file_put_contents($cacheConfigPath, $content);
	}

	/**
	 * Test the selected Cache Handler
	 */
	public function testCacheConnection()
	{
		$auth = $this->checkAdminAuthorization();
		if ($auth !== true) {
			return $auth;
		}

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}
		
		// Get the cache handler from the request or from settings
		$handler = $this->request->getPost('handler') ?? $this->myConfig['cacheHandler'];
		
		// Initialize response array
		$response = [
			'success' => false,
			'msg' => '',
			'details' => []
		];
		
		try {
			switch ($handler) {
				case 'memcached':
					if (!extension_loaded('memcached')) {
						throw new \Exception(lang('Notifications.memcached_extension_not_installed'));
					}
					
					$host = $this->myConfig['memcached_host'] ?? '127.0.0.1';
					$port = $this->myConfig['memcached_port'] ?? 11211;
					
					$memcached = new \Memcached();
					$memcached->addServer($host, $port);
					
					// Test connection by setting and getting a value
					$testKey = 'connection_test_' . time();
					$testValue = 'Connection successful at ' . date('Y-m-d H:i:s');
					$memcached->set($testKey, $testValue, 60);
					$retrievedValue = $memcached->get($testKey);
					
					if ($retrievedValue === $testValue) {
						$response['success'] = true;
						$response['msg'] = lang('Notifications.memcached_connection_successful');
						
						// Get server stats
						$stats = $memcached->getStats();
						if (!empty($stats)) {
							foreach ($stats as $server => $serverStats) {
								$response['details'][] = [
									'server' => $server,
									'version' => $serverStats['version'] ?? 'Unknown',
									'uptime' => $serverStats['uptime'] ?? 'Unknown',
									'curr_connections' => $serverStats['curr_connections'] ?? 'Unknown',
									'bytes' => $serverStats['bytes'] ?? 'Unknown',
									'cmd_get' => $serverStats['cmd_get'] ?? 'Unknown',
									'cmd_set' => $serverStats['cmd_set'] ?? 'Unknown'
								];
							}
						}
					} else {
						throw new \Exception(lang('Notifications.memcached_connection_failed'));
					}
					break;
					
				case 'redis':
				case 'predis':
					if ($handler === 'redis' && !extension_loaded('redis')) {
						throw new \Exception(lang('Notifications.redis_extension_not_installed'));
					}
					
					$host = $this->myConfig['redis_host'] ?? '127.0.0.1';
					$port = $this->myConfig['redis_port'] ?? 6379;
					$password = $this->myConfig['redis_password'] ?? null;
					$database = $this->myConfig['redis_database'] ?? 0;
					$timeout = $this->myConfig['redis_timeout'] ?? 0;
					
					if ($handler === 'redis') {
						// Use phpredis extension
						$redis = new \Redis();
						$connected = $redis->connect($host, $port, $timeout);
						
						if (!$connected) {
							throw new \Exception(lang('Notifications.redis_connection_failed'));
						}
						
						if (!empty($password)) {
							$authenticated = $redis->auth($password);
							if (!$authenticated) {
								throw new \Exception(lang('Notifications.redis_authentication_failed'));
							}
						}
						
						if ($database !== 0) {
							$redis->select($database);
						}
						
						// Test connection by setting and getting a value
						$testKey = 'connection_test_' . time();
						$testValue = 'Connection successful at ' . date('Y-m-d H:i:s');
						$redis->set($testKey, $testValue);
						$retrievedValue = $redis->get($testKey);
						
						if ($retrievedValue === $testValue) {
							$response['success'] = true;
							$response['msg'] = lang('Notifications.redis_connection_successful');
							
							// Get server info
							$info = $redis->info();
							$response['details'] = [
								'redis_version' => $info['redis_version'] ?? 'Unknown',
								'uptime_in_seconds' => $info['uptime_in_seconds'] ?? 'Unknown',
								'connected_clients' => $info['connected_clients'] ?? 'Unknown',
								'used_memory_human' => $info['used_memory_human'] ?? 'Unknown',
								'total_connections_received' => $info['total_connections_received'] ?? 'Unknown',
								'total_commands_processed' => $info['total_commands_processed'] ?? 'Unknown'
							];
						} else {
							throw new \Exception(lang('Notifications.redis_connection_failed'));
						}
					} else {
						// Use Predis library
						$config = [
							'scheme' => 'tcp',
							'host' => $host,
							'port' => $port,
							'database' => $database
						];
						
						if (!empty($password)) {
							$config['password'] = $password;
						}
						
						if ($timeout > 0) {
							$config['timeout'] = $timeout;
						}
						
						try {
							$predis = new \Predis\Client($config);
							$predis->connect();
							
							// Test connection by setting and getting a value
							$testKey = 'connection_test_' . time();
							$testValue = 'Connection successful at ' . date('Y-m-d H:i:s');
							$predis->set($testKey, $testValue);
							$retrievedValue = $predis->get($testKey);
							
							if ($retrievedValue === $testValue) {
								$response['success'] = true;
								$response['msg'] = lang('Notifications.predis_connection_successful');
								
								// Get server info
								$info = $predis->info();
								$response['details'] = [
									'redis_version' => $info['Server']['redis_version'] ?? 'Unknown',
									'uptime_in_seconds' => $info['Server']['uptime_in_seconds'] ?? 'Unknown',
									'connected_clients' => $info['Clients']['connected_clients'] ?? 'Unknown',
									'used_memory_human' => $info['Memory']['used_memory_human'] ?? 'Unknown'
								];
							} else {
								throw new \Exception(lang('Notifications.predis_connection_failed'));
							}
						} catch (\Predis\Connection\ConnectionException $e) {
							throw new \Exception(lang('Notifications.predis_connection_failed') . ': ' . $e->getMessage());
						}
					}
					break;
					
				default:
					throw new \Exception(lang('Notifications.cache_handler_not_testable', ['handler' => $handler]));
			}
		} catch (\Exception $e) {
			$response['success'] = false;
			$response['msg'] = $e->getMessage();
		}
		
		return $this->response->setJSON($response);
	}
}
