<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use CodeIgniter\Controller;
use App\Models\LicensesModel;
use App\Models\LicenseLogsModel;
use App\Models\LicenseEmailListModel;
use App\Models\UserModel;

class LicenseManager extends BaseController
{
    protected $userID;
	protected $myConfig;
	protected $userDataPath;

    protected $LicensesModel;
    protected $LicenseRegisteredDomainsModel;
    protected $LicenseRegisteredDevicesModel;
    protected $LicenseLogsModel;
    protected $LicenseEmailListModel;
	protected $UserModel;

    public function __construct()
	{
		// Get the current user's ID
        $this->userID = auth()->id() ?? NULL;

		// Use the updated getMyConfig function with the user's ID
        $this->myConfig = getMyConfig('', $this->userID);

		// Define the path to the user's data folder
		$this->userDataPath = USER_DATA_PATH . $this->userID . '/';

		// Set the locale dynamically based on user preference
		setMyLocale();
		
        // Initialize Models
        $this->LicensesModel = new LicensesModel();
        $this->LicenseLogsModel = new LicenseLogsModel();
        $this->LicenseEmailListModel = new LicenseEmailListModel();
		$this->UserModel = new UserModel();
    }

	protected function checkIfLoggedIn()
	{
		if(NULL === auth()->id()) {
			return redirect()->to('login');
		}
	}

	public function debug()
	{
        
	}

	public function new_license_action()
	{ 
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}

		// Set the response messages
		$msgResponse_validationError 		= lang('Notifications.error_submitted_details');
		$msgResponse_jsonError 				= lang('Notifications.error_decoding_json_license_creation');

		// Validate form data
		$validationRules = [
			'license_key' => 'required',
			'product_ref' => 'required',
			'license_type' => 'required',
			'purchase_id_' => 'required',
			'first_name' => 'required|regex_match[/^[\p{L}\p{M}\s.\'-]+$/u]',
            'last_name'  => 'required|regex_match[/^[\p{L}\p{M}\s.\'-]+$/u]',
			'email' => 'required|valid_email',
			'txn_id' => 'required',
			'max_allowed_domains'	=> 'required|is_natural',
			'max_allowed_devices'   => 'required|is_natural',
		];

		if (!$this->validate($validationRules)) {

			$response = [
				'success' => false,
				'status' => 0,
				'msg' => $msgResponse_validationError,
			];

			return $this->response->setJSON($response);
		} else {
			// Get all posted data keys
			$postData = $this->request->getPost();

			// Individual validation
			$individualLicenseParamValidations = individualLicenseParamValidations($postData, $this->userID);

			if($individualLicenseParamValidations !== true) {
				return $this->response->setJSON($individualLicenseParamValidations);
			}

			// Form data is valid, proceed with further processing				
			$response = $this->createLicense($postData);

			// Check if the response is a JSON string
			if (!$this->json_validator($response)) {
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

	public function edit_license_action()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}

		// Set the response messages
		$msgResponse_validationError = lang('Notifications.error_submitted_details');
		
		// Validate form data
		$validationRules = [
			'id' => 'required',
			'license_key' => 'required',
			'product_ref' => 'required',
			'license_type' => 'required',
			'purchase_id_' => 'required',
			'first_name' => 'required|regex_match[/^[\p{L}\p{M}\s.-]+$/u]',
			'last_name' => 'required|regex_match[/^[\p{L}\p{M}\s.-]+$/u]',
			'email' => 'required|valid_email',
			'txn_id' => 'required',
			'max_allowed_domains' => 'required|is_natural',
			'max_allowed_devices' => 'required|is_natural',
		];

		if (!$this->validate($validationRules)) {

			$response = [
				'success' => false,
				'status' => 0,
				'msg' => $msgResponse_validationError,
			];

			return $this->response->setJSON($response);
		} else {

			// Get all posted data keys
			$postedData = $this->request->getPost();

			// reformat the date_expiry if present
			if($postedData['license_type'] !== 'lifetime') {
				if ( ($postedData['date_expiry'] !== null) && ($postedData['date_expiry'] !== '') ) {
					// TIMEZONE HANDLING LOGIC
					// Check if this is an API call from WooCommerce or similar integration
					// WooCommerce plugin sets item_reference = 'woocommerce' and sends dates in UTC
					$isWooCommerceCall = isset($postedData['item_reference']) &&
					                   $postedData['item_reference'] === 'woocommerce';

					// App's default timezone (UTC)
					$appTimezone = app_timezone();

					// Remove AM/PM and convert to 24-hour format
					$dateExpiry = $postedData['date_expiry'];
					$is_pm = stripos($dateExpiry, 'PM') !== false;
					$dateExpiry = trim(str_replace(['AM', 'PM'], '', $dateExpiry));

					if ($isWooCommerceCall) {
						// WooCommerce sends dates already in UTC format
						// No timezone conversion needed - parse as UTC directly
						log_message('info', '[TIMEZONE] WooCommerce API call detected (edit_license_action) - treating date_expiry as UTC');
						log_message('info', '[TIMEZONE] Received date_expiry: ' . $dateExpiry);

						$expirationDate = Time::parse($dateExpiry, 'UTC');

						// Handle AM/PM for 24-hour conversion
						if ($is_pm) {
							$hour = $expirationDate->getHour();
							if ($hour !== 12) {
								$expirationDate = $expirationDate->setTime($hour + 12, $expirationDate->getMinute(), $expirationDate->getSecond());
							}
						} else {
							$hour = $expirationDate->getHour();
							if ($hour === 12) {
								$expirationDate = $expirationDate->setTime(0, $expirationDate->getMinute(), $expirationDate->getSecond());
							}
						}

						$postedData['date_expiry'] = $expirationDate->format('Y-m-d H:i:s');
						log_message('info', '[TIMEZONE] Final date_expiry for database: ' . $postedData['date_expiry']);
					} else {
						// Manual web UI - convert from user timezone to UTC
						log_message('info', '[TIMEZONE] Manual web UI call detected (edit_license_action) - converting from user timezone');

						// First check session for detected timezone
						$session = session();
						$userTimezone = $session->get('detected_timezone') ??
						                $this->myConfig['defaultTimezone'] ??
						                'UTC';

						log_message('info', '[TIMEZONE] User timezone: ' . $userTimezone);
						log_message('info', '[TIMEZONE] Received date_expiry: ' . $dateExpiry);

						// Parse the date with the user's timezone
						$dateCreated = getLicenseData($postedData['license_key'], 'date_created');
						$dateCreated = Time::parse($dateCreated, $appTimezone);

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

						log_message('info', '[TIMEZONE] Converted to UTC: ' . $postedData['date_expiry']);
						log_message('info', '[TIMEZONE] Final date_expiry for database: ' . $postedData['date_expiry']);
					}
				}
				else {
					$response = [
						'success' => false,
						'status' => 0,
						'msg' => lang('Notifications.incorrectDateFormatEditLicense'),
					];
		
					return $this->response->setJSON($response);
				}
			}
			
			// Automatically set billing length and interval if lifetime
			if($postedData['license_type'] === 'lifetime') {
				$postedData['billing_length'] = '';
				$postedData['billing_interval'] = 'onetime';
				$postedData['date_renewed']  = NULL;
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

			// Set the date_activated if license just got activated
			if( ($postedData['license_status'] === 'active') && (!getLicenseData($postedData['license_key'], 'date_activated')) ) {
				$postedData['date_activated'] = Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s');
			}

			// Individual validation 
			$individualLicenseParamValidations = individualLicenseParamValidations($postedData, $this->userID);

			if($individualLicenseParamValidations !== true) {
				return $this->response->setJSON($individualLicenseParamValidations);
			}				

			// Form data is valid, proceed with further processing
			$postedKeys = array_keys($postedData);
			$dataToUpdate = [];
			
			// Iterate the data to be updated in the license table
			foreach($postedData as $key => $indData) {
				$dataToUpdate[$key] = $indData;
			}

			$dataToUpdate['owner_id'] = $this->userID;

			$return = $this->LicensesModel->update($dataToUpdate['id'], $dataToUpdate);

			try {
				if ($return) {
					// Check if needed to update WooCommerce
					checkToUpdateWooCommerce($dataToUpdate['license_key']);            
			
					// Check if the email in the record has changed
					$previousEmail = $this->LicenseEmailListModel->where('owner_id', $this->userID)
																->where('license_key', $dataToUpdate['license_key'])
																->first();

					$postedEmail = $dataToUpdate['email'];
			
					$emailListData = [
						'owner_id' => $this->userID,
						'license_key' => $dataToUpdate['license_key'],
						'sent_to' => $dataToUpdate['email'],
						'status' => 'success',
						'sent' => 'yes',
						'date_sent' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
					];
			
					if (empty($previousEmail) || $previousEmail['sent_to'] !== $postedEmail) {
			
						$updateEmailList = $this->LicenseEmailListModel->insertOrUpdate($emailListData);
			
						try {
							if ($updateEmailList) {
								$successMessage = 'update: License details updated';
								$success = true;
							}
						} catch (\Exception $e) {
							$successMessage = 'update: License details updated but encountered error in updating the email list!';
							$success = true;
							$additionalMessage = 'Result: ' . $e->getMessage();
						}
					} else {
						$successMessage = 'update: License details updated';
						$success = true;
					}
			
					// Log the activity
					licenseManagerLogger($dataToUpdate['license_key'], $successMessage, $success ? 'yes' : 'no');
			
					$response = [
						'success' => true,
						'status' => 1,
						'msg' => lang('Notifications.success_edit_license') . ($additionalMessage ?? ''),
						'data' => json_encode(getLicenseData($dataToUpdate['license_key'], false, true)),
					];
			
				} else {
					// Log the activity
					licenseManagerLogger($dataToUpdate['license_key'], 'update: License details update failed due to server error', 'no');
			
					$response = [
						'success' => false,
						'status' => 0,
						'msg' => lang('Notifications.error_edit_license'),
						'data' => '',
					];
				}
			}
			catch (\Exception $e) {
				// Handle errors
				log_message('error', '[LicenseManager] License detail update failed: ' . $e->getMessage());
			
				// Log the activity
				licenseManagerLogger($dataToUpdate['license_key'], 'update: License details update failed: ' . $e->getMessage(), 'no');
			
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.server_error_edit_license', ['errorMessage' => $e->getMessage()]),
					'data' => '',
				];
			}
			
			return $this->response->setJSON($response);
			
		}
	}

	protected function json_validator($data)
	{ 
        if (!empty($data)) { 
            return is_string($data) &&  
              is_array(json_decode($data, true)) ? true : false; 
        } 
        return false; 
    } 

	public function resend_license_details_action()
	{
		$this->checkIfLoggedIn();
	
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)
				->setBody(lang('Notifications.Method_Not_Allowed'));
		}
	
		// Validate form data
		$validationRules = [
			'licenseInput'      => 'required',
			'recipientTextarea' => 'required',
		];
	
		if (!$this->validate($validationRules)) {
			return $this->response->setJSON([
				'success' => false,
				'status'  => 0,
				'msg'     => lang('Notifications.error_submitted_details'),
			]);
		}
	
		// Form data is valid, proceed with processing
		$licenseKey = trim($this->request->getPost('licenseInput'));
		$recipientList = trim($this->request->getPost('recipientTextarea'));
	
		// Retrieve the license details
		$licenseDetails = $this->getLicenseDetails($licenseKey);
	
		if ($licenseDetails['result'] !== 'success') {
			return $this->response->setJSON([
				'success' => false,
				'status'  => 0,
				'msg'     => lang('Notifications.unable_to_query_license_details'),
			]);
		}
	
		// Check if the license is active
		if ($licenseDetails['status'] !== 'active') {
			return $this->response->setJSON([
				'success' => true,
				'status'  => 0,
				'msg'     => lang('Notifications.error_unable_to_process_request_for_license', 
					['licenseDetailsStatus' => $licenseDetails['status']]),
			]);
		}
	
		// Process email sending
		$emailAddresses = array_filter(array_map('trim', explode("\n", $recipientList)));
		if (empty($emailAddresses)) {
			return $this->response->setJSON([
				'success' => false,
				'status'  => 0,
				'msg'     => lang('Notifications.error_sending_email_failed'),
			]);
		}
	
		$bccAdmin = $this->myConfig['sendBCCtoResendLicense'] ?? false;
		$emailService = new \App\Libraries\EmailService();
		$successCount = 0;
		$failedEmails = [];
	
		foreach ($emailAddresses as $email) {
			try {
				$result = $emailService->sendLicenseDetails([
					'license_key' => $licenseKey,
					'recipient_email' => $email,
					'email_format' => 'html',
					'with_bcc' => $bccAdmin
				]);
	
				if ($result) {
					$successCount++;
				} else {
					$failedEmails[] = $email;
				}
			} catch (\Throwable $e) {
				log_message(
					'error',
					'[License Manager] Error sending license details: ' . $e->getMessage()
				);
				$failedEmails[] = $email;
			}
		}
	
		// Prepare response based on results
		if ($successCount === count($emailAddresses)) {
			return $this->response->setJSON([
				'success' => true,
				'status'  => 1,
				'msg'     => lang('Notifications.license_details_sent_to_client'),
			]);
		}
	
		if ($successCount > 0) {
			return $this->response->setJSON([
				'success' => true,
				'status'  => 1,
				'msg'     => sprintf(
					'Sent to %d recipient(s). Failed for: %s',
					$successCount,
					implode(', ', $failedEmails)
				),
			]);
		}
	
		return $this->response->setJSON([
			'success' => false,
			'status'  => 0,
			'msg'     => lang('Notifications.error_sending_email_failed'),
		]);
	}
	
	protected function getLicenseDetails($licenseKey)
	{
		return getLicenseData($licenseKey);
	}

	// requires an array parameter for the data to be posted
	protected function createLicense($postData)
	{

		if(!is_array($postData)) {
			$response = [
				'success' => false,
				'status'  => 0,
				'msg'     => lang('Notifications.error_createlicense_not_array_param'),
			];

			return json_encode($response);
		}

        // Iterate over the posted data to get keys and values
		// License Key and Status
		$newLicenseKey = '';
		$licenseStatus = '';
		$licenseType = '';

		// User Info
		$firstName = '';
		$lastName = '';
		$clientEmail = '';
		$subscriberID = '';
		$companyName = '';

		// Domains and Devices
		$allowed_domains = '';
		$allowed_devices = '';

		// Subscription and Renewal
		$manualResetCount = '';
		$billing_length = '';
		$billing_interval = '';
		$expirationDate = '';

		// Product
		$productName = '';
		$transactionID = '';
		$purchaseID = '';
		$supportedUntil = '';
		$currentVersion = '';
		$itemReference = '';

        foreach ($postData as $key => $value) {
			// License Key and Status
			if($key === 'license_key') {
				$newLicenseKey = $value;
			}
			if($key === 'license_status') {
				$licenseStatus = $value;
			}
			if($key === 'license_type') {
				$licenseType = $value;
			}

			// User Unfo
			if($key === 'first_name') {
				$firstName = $value;
			}
			if($key === 'last_name') {
				$lastName = $value;
			}
			if($key === 'email') {
				$clientEmail = $value;
			}
			if($key === 'subscr_id') {
				$subscriberID = $value;
			}
			if($key === 'company_name') {
				$companyName = $value;
			}

			// Domains & Devices
			if($key === 'max_allowed_domains') {
				$allowed_domains = $value;
			}
			if($key === 'max_allowed_devices') {
				$allowed_devices = $value;
			}

			// Subscription and Renewal
			if($key === 'manual_reset_count') {
				$manualResetCount = $value;
			}
			if($key === 'billing_length') {
				$billing_length = $value;
			}
			if($key === 'billing_interval') {
				$billing_interval = $value;
			}
			if($key === 'date_expiry') {
				$expirationDate = $value;
			}

			// Product		
			if($key === 'product_ref') {
				$productName = $value;
			}
			if($key === 'txn_id') {
				$transactionID = $value;
			}
			if($key === 'purchase_id_') {
				$purchaseID = $value;
			}
			if($key === 'until') {
				$supportedUntil = $value;
			}
			if($key === 'current_ver') {
				$currentVersion = $value;
			}
			if($key === 'item_reference') {
				$itemReference = $value;
			}
        }

		// Automatically set billing length and interval if lifetime
		if($licenseType === 'lifetime') {
            $billing_length = '';
            $billing_interval = 'onetime';
        }

		// Individual validation
		$individualLicenseParamValidations = individualLicenseParamValidations($postData, $this->userID);

		if($individualLicenseParamValidations !== true) {
			return $this->response->setJSON($individualLicenseParamValidations);
		}

		// Get the list of products
		$productList = productList($this->userID);	
	
		// Extract product base name
		$productNameBasic = productBasename($productName, $this->userID);

		// Check if the selected product has a configured email template
		$emailTemplateConfigFilePath = $this->userDataPath . $this->myConfig['userAppSettings'] . 'product-email-templates.json';

		// Read existing email template configuration from JSON file
		$existingEmailTemplateConfig = [];
		if (file_exists($emailTemplateConfigFilePath)) {
			$existingEmailTemplateConfig = json_decode(file_get_contents($emailTemplateConfigFilePath), true);
		}

		// Set the email template
		$emailTemplateSet = false;

		// Check if product has a configured email template
		foreach ($existingEmailTemplateConfig as $emailTemplate => $values) {
			if (strpos($values, $productNameBasic) !== false) {
				$emailTemplateSet = true;
				break;
			}
		}

		if (!$emailTemplateSet) {
			$response = [
				'success' => false,
				'status'  => 0,
				'msg'     => lang('Notifications.error_no_configured_email_template_for_product', ['productNameBasic' => $productNameBasic]),
			];

			return json_encode($response);
		}

		if (in_array($productNameBasic, $productList)) {
			// Initiate license manager task
			$responseArray = [];
			
			/************************************
			 * SLM Wordpress Plugin integration *
			 ************************************/
			if ($this->myConfig['licenseManagerOnUse'] === 'slm') {
				// Set SLM action
				$apiAction = 'slm_create_new';

				// SLM WP Plugin doesn't have trial license type
				if ($licenseType === 'trial') {
					$licenseType = 'subscription';
				}

				// Construct the API call URL
				$apiUrl = sprintf(
					"%s?secret_key=%s&slm_action=%s&license_key=%s&lic_status=%s&first_name=%s&last_name=%s&purchase_id_=%s&email=%s&txn_id=%s&date_expiry=%s&product_ref=%s&until=%s&subscr_id=%s&current_ver=%s&lic_type=%s&max_allowed_domains=%s&max_allowed_devices=%s&company_name=%s&slm_billing_length=%s&slm_billing_interval=%s",
					$this->myConfig['licenseServerDomain'],
					$this->myConfig['licenseServer_Create_SecretKey'],
					$apiAction,
					$newLicenseKey,
					$licenseStatus,
					urlencode($firstName),
					urlencode($lastName),
					$purchaseID,
					$clientEmail,
					$transactionID,
					$expirationDate,
					urlencode($productName),
					urlencode($supportedUntil),
					$subscriberID,
					urlencode($currentVersion),
					$licenseType,
					$allowed_domains,
					$allowed_devices,
					urlencode($companyName),
					$billing_length,
					$billing_interval
				);

				try {
					$apiCall = makeApiCall($apiUrl);
					$apiResponse = json_decode($apiCall->getBody(), true);

					if ($apiResponse === null && json_last_error() !== JSON_ERROR_NONE) {
						log_message('error', '[LicenseManager] License creation failed: ' . $e->getMessage());
						$response = [
							'success' => false,
							'status'  => 0,
							'msg'     => lang('Notifications.error_license_creation', ['APIresponse' => $apiCall->getBody()]),
						];

						return json_encode($response);
					}					

					// Process the response data
					log_message('info', '[LicenseManager] License creation success: ' . json_encode($apiResponse));

					$responseArray['creation'] = [
						'success' => $apiResponse['result'] === 'success',
						'status'  => 1,
						'msg'     => lang('Notifications.success_license_creation', ['APIresponse' => $apiResponse['message']]),
						'nextLicenseKey' => generateLicenseKey($this->userID),
					];

					if ($apiResponse['result'] === 'success') {
						// Initiate email license details to client
						if ($this->myConfig['sendEmailNewLicense']) {
							$clientFullName = $firstName . ' ' . $lastName;
							$bccAdmin = $this->myConfig['sendBCCtoLicenseClientNotifications'] ? true : false;
							
							$emailService = new \App\Libraries\EmailService();

							try {
								$response = $emailService->sendLicenseDetails([
										'license_key' => $newLicenseKey,
										'recipient_email' => $clientEmail,
										'email_format' => 'html',
										'with_bcc' => $bccAdmin
									]);
							} catch (\Throwable $e) {
								// Handle exceptions
								log_message(
									'error',
									'[License Manager] Error sending license details: ' . $e->getMessage()
								);
							}

							// Handle email sending response
							$responseArray['notification'] = [
								'success' => $response !== false,
								'status'  => $response !== false ? 1 : 0,
								'msg'     => $response !== false ? lang('Notifications.success_license_details_email_sent') : lang('Notifications.success_license_creation_ERROR_email_sent', ['APIresponse' => $apiResponse['message']]),
							];
						}
					}

					return json_encode($responseArray);

				} catch (\Exception $e) {
					// Handle errors
					log_message('error', '[LicenseManager] License creation failed: ' . $e->getMessage());
					$response = [
						'success' => false,
						'status'  => 0,
						'msg'     => lang('Notifications.error_license_creation', ['APIresponse' => $e->getMessage()]),
					];

					return json_encode($response);
				}
			}
			
			/****************************
			 * Built-in License Manager *
			 ***************************/ 
			else {
				$licenseData = [];

				// Iterate over the posted data to get keys and values
				foreach ($postData as $key => $value) {
					$licenseData[$key] = $value;
				}

				// Specify the owner
				$licenseData['owner_id'] = $this->userID;

				// Automatically set billing length and interval if lifetime
				if($licenseData['license_type'] === 'lifetime') {
					$licenseData['billing_length'] = '';
					$licenseData['billing_interval'] = 'onetime';
					$licenseData['date_renewed']  = NULL;
					$licenseData['date_expiry']  = NULL;
					if($licenseData['item_reference'] === '' || $licenseData['item_reference'] === NULL) {
						$licenseData['item_reference'] = $licenseData['product_ref'];
					} 
				}
				else {
					// reformat the date_expiry if present
					if ( ($licenseData['date_expiry'] !== null) && ($licenseData['date_expiry'] !== '') ) {
						// TIMEZONE HANDLING LOGIC
						// Check if this is an API call from WooCommerce or similar integration
						// WooCommerce plugin sets item_reference = 'woocommerce' and sends dates in UTC
						$isWooCommerceCall = isset($licenseData['item_reference']) &&
						                   $licenseData['item_reference'] === 'woocommerce';

						// App's default timezone (UTC)
						$appTimezone = app_timezone();

						// Remove AM/PM and convert to 24-hour format
						$dateExpiry = $licenseData['date_expiry'];
						$is_pm = stripos($dateExpiry, 'PM') !== false;
						$dateExpiry = trim(str_replace(['AM', 'PM'], '', $dateExpiry));

						if ($isWooCommerceCall) {
							// WooCommerce sends dates already in UTC format
							// No timezone conversion needed - parse as UTC directly
							log_message('info', '[TIMEZONE] WooCommerce API call detected (createLicense) - treating date_expiry as UTC');
							log_message('info', '[TIMEZONE] Received date_expiry: ' . $dateExpiry);

							$expirationDate = Time::parse($dateExpiry, 'UTC');

							// Handle AM/PM for 24-hour conversion
							if ($is_pm) {
								$hour = $expirationDate->getHour();
								if ($hour !== 12) {
									$expirationDate = $expirationDate->setTime($hour + 12, $expirationDate->getMinute(), $expirationDate->getSecond());
								}
							} else {
								$hour = $expirationDate->getHour();
								if ($hour === 12) {
									$expirationDate = $expirationDate->setTime(0, $expirationDate->getMinute(), $expirationDate->getSecond());
								}
							}

							$licenseData['date_expiry'] = $expirationDate->format('Y-m-d H:i:s');
							log_message('info', '[TIMEZONE] Final date_expiry for database: ' . $licenseData['date_expiry']);
						} else {
							// Manual web UI or other sources - convert from user timezone to UTC
							log_message('info', '[TIMEZONE] Non-WooCommerce call (createLicense) - converting from user timezone');

							// First check session for detected timezone
							$session = session();
							$userTimezone = $session->get('detected_timezone') ??
							                $this->myConfig['defaultTimezone'] ??
							                'UTC';

							log_message('info', '[TIMEZONE] User timezone: ' . $userTimezone);
							log_message('info', '[TIMEZONE] Received date_expiry: ' . $dateExpiry);

							// Get creation date (as Time object, not string!)
							$dateCreated = Time::now()->setTimezone('UTC');

							// Parse the expiration date in the user's timezone
							$expirationDate = Time::parse($dateExpiry, $userTimezone);

							// Convert to app's default timezone (UTC)
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
								$licenseData['date_expiry'] = $expirationDate
									->setTime(
										$dateCreated->getHour(),
										$dateCreated->getMinute(),
										$dateCreated->getSecond()
									)
									->format('Y-m-d H:i:s');
							} else {
								// Convert to standard database format
								$licenseData['date_expiry'] = $expirationDate->format('Y-m-d H:i:s');
							}

							log_message('info', '[TIMEZONE] Converted to UTC: ' . $licenseData['date_expiry']);
							log_message('info', '[TIMEZONE] Final date_expiry for database: ' . $licenseData['date_expiry']);
						}
					}
					else {
						$response = [
							'success' => false,
							'status' => 0,
							'msg' => lang('Notifications.incorrectDateFormatEditLicense'),
						];
			
						return $this->response->setJSON($response);
					}					
				}

				// Automatically set billing length and interval if lifetime
				if($licenseData['license_status'] === 'active') {
					$licenseData['date_activated'] = Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s');
				}
				else {
					$licenseData['date_activated'] = NULL;
				}
				
				try {
					// Insert data into the database
					log_message('info', '[LicenseManager] License creation success: ' . json_encode($licenseData));

					$insertNewLicenseDetails = $this->LicensesModel->insert($licenseData);
					// $insertNewLicenseDetails = true; // debug
				
					// Check if insertion was successful
					if ($insertNewLicenseDetails) {

						// Log the activity
						licenseManagerLogger($newLicenseKey, 'create: License creation initiated', 'yes');

						$responseArray['creation'] = [
							'success' => true,
							'status'  => 1,
							'msg'     => lang('Notifications.success_license_creation_builtin'),
							'nextLicenseKey' => generateLicenseKey($this->userID),
						];
				
						// Initiate email license details to client
						if ($this->myConfig['sendEmailNewLicense']) {
							
							$clientFullName = $firstName . ' ' . $lastName;						

							$bccAdmin = $this->myConfig['sendBCCtoLicenseClientNotifications'] ? true : false;

							$emailService = new \App\Libraries\EmailService();
							$licenseNotificationResult = $emailService->sendLicenseDetails([
									'license_key' => $newLicenseKey,
									'recipient_email' => $clientEmail,
									'email_format' => 'html',
									'with_bcc' => $bccAdmin
								]);

							$emailListData = [
								'owner_id'    => $this->userID,
								'license_key' => $newLicenseKey,
								'sent_to'     => $clientEmail,
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
	
							try {
								// Insert email sending status into the database
								$this->LicenseEmailListModel->insertOrUpdate($emailListData);
							} catch (\Throwable $e) {
								// Handle exceptions
								log_message(
									'error',
									'[LicenseManager] Error occurred while inserting new email list data: ' . $e->getMessage() .
									'. Data: ' . json_encode($emailListData)
								);
							}

							$responseArray['notification'] = $licenseNotificationResult['message'];
							
						}
				
						return json_encode($responseArray);
					} else {
						log_message('error', '[License Manager] Failed to insert new license. Last error: ' . print_r($this->LicensesModel->errors(), true));
						
						$response = [
							'success' => false,
							'status'  => 0,
							'msg'     => lang('Notifications.error_saving_license_details'),
						];
						return json_encode($response);
					}
				} catch (\Exception $e) {
					// Error in license creation
					$response = [
						'success' => false,
						'status'  => 0,
						'msg'     => lang('Notifications.error_license_creation', ['APIresponse' => $e->getMessage()]),
					];
					return json_encode($response);
				}				
			}
	
		} else {
			$response = [
				'success' => false,
				'status'  => 0,
				'msg'     => lang('Notifications.error_product_not_existing', ['productName' => $productName]),
			];
	
			return json_encode($response);
		}
	}

	public function sendEmailtoClient_new_license($licenseKey, $licenseType, $clientFullName, $clientEmail, $whatProduct)
	{
		// Check if email sending is enabled
		if($this->myConfig['sendEmailNewLicense']) {
			$clientFullName = urldecode($clientFullName);
			$clientEmail = urldecode($clientEmail);     
			
			// Extract product base name
			// $whatProduct = productBasename(urldecode($whatProduct, $this->userID));

			$bccAdmin = $this->myConfig['sendBCCtoLicenseClientNotifications'] ? true : false;
		
			$emailService = new \App\Libraries\EmailService();
			$licenseNotificationResult = $emailService->sendLicenseDetails([
					'license_key' => $licenseKey,
					'recipient_email' => $clientEmail,
					'email_format' => 'html',
					'with_bcc' => $bccAdmin
				]);
		
			if ($licenseNotificationResult['success']) {
				$response = [
					'success' => true,
					'status'  => 1,
					'msg'     => lang('Notifications.success_email_sending', ['emailResponse' => $emailResponse['msg']]),
				];
			} else {
				$response = [
					'success' => false,
					'status'  => 0,
					'msg'     => lang('Notifications.error_email_sending', ['emailResponse' => $emailResponse['msg']]),
				];
			}

			$info = [
				'licenseKey'        => $licenseKey,
				'licenseType' 		=> $licenseType,
				'clientFullName' 	=> $clientFullName,
				'clientEmail' 		=> $clientEmail,
				'whatProduct' 		=> $whatProduct,
			];

			log_message('alert', '[LicenseManager] External request received to send license email to client.
			Details:
			License Key: {licenseKey}
			License Type: {licenseType}
			Full Name: {clientFullName}
			Email: {clientEmail}
			Product: {whatProduct}', $info);

			return json_encode($response);
		}

		// Return error if email sending is not enabled
		return json_encode([
			'success' => false,
			'status'  => 0,
			'msg'     => lang('Notifications.error_new_license_not_set_to_send_email'),
		]);
	}	

	public function reset_license_search_action()
	{
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}

		// Set the response messages
		$msgResponse_validationError 		= lang('Notifications.error_submitted_details');
		$msgResponse_jsonError 				= lang('Notifications.error_decoding_json_email_status_response');
		$msgResponse_captchaError 				= lang('Notifications.error_wrong_captcha');
		
		// Check if captcha os correct
		if(trim($this->request->getPost('hash')) !== sha1(trim($this->request->getPost('captcha')))) {
			$response = [
				'success' => false,
				'status'  => 0,
				'msg'     => $msgResponse_captchaError,
			];

			return $this->response->setJSON($response);
		}

		// Validate form data
		$validationRules = [
			'licenseInput'      => 'required',
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
				'licenseKey'     => trim($this->request->getPost('licenseInput')),
			];

			// Retrieve the license details
			$licenseDetails = $this->getLicenseDetails($data['licenseKey']);

			if($licenseDetails['result'] === 'success') {
			    
				// Check if the license status is active
				if($licenseDetails['status'] !== 'active') {
					$response = [
						'success' => true,
						'status'  => 0,
						'msg'     => lang('Notifications.error_license_unable_to_process', ['licenseDetailsStatus' => $licenseDetails['status']]),
					];
	
					return $this->response->setJSON($response);
				}

				$response = [
					'success' => true,
					'status'  => 1,
					'msg'     => $licenseDetails['message'],
					'data'	  => $licenseDetails
				];

				return $this->response->setJSON($response);
			}	
			else {
				// response
				$response = [
					'success' => false,
					'status'  => 0,
					'msg'     => $licenseDetails['message'],
				];

				return $this->response->setJSON($response);
			}
		}
	}

	public function reset_delete_selected_action()
	{
		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}

		// Set response messages
		$validationErrorMessage = lang('Notifications.error_submitted_details');
		$jsonErrorMessage = lang('Notifications.error_decoding_json_email_status_response');

		// Get posted data
		$postedData = $this->request->getPost();
		$postedKeys = array_keys($postedData);

		// Retrieve the license details
		$licenseDetails = $this->getLicenseDetails($postedData['verified-license']);

		if ($licenseDetails['result'] === 'success') {
			// Check if the license status is active
			if ($licenseDetails['status'] !== 'active') {
				$response = [
					'success' => true,
					'status' => 0,
					'msg' => lang('Notifications.error_license_unable_to_process', ['licenseDetailsStatus' => $licenseDetails['status']]),
				];

				return $this->response->setJSON($response);
			}

			$this->userID = $licenseDetails['owner_id'];
			$this->myConfig = getMyConfig('', $this->userID);
            
			$apiAction = 'slm_deactivate';
			$secret_key = $this->myConfig['licenseServer_Validate_SecretKey'];
			$licenseKey = $licenseDetails['license_key'];

			// Handle selected domains
			if (!empty($postedData['selected-domain'])) {
				$domains = explode(",", $postedData['selected-domain']);
				foreach ($domains as $domain) {
					$data['whatToDelete'] = 'registered_domains';
					$data['registered_data'] = trim($domain);
					try {
						$this->updateLicense($this->userID, $apiAction, $secret_key, $licenseKey, $data);
					} catch (\Exception $e) {
						$logger = \Config\Services::logger();
						$logger->error(lang('Notifications.logger_api_request_failed', ['message' => $e->getMessage()]));

						$response = [
							'success' => false,
							'status' => 0,
							'msg' => lang('Notifications.error_encounted_problem_license_request'),
						];

						return json_encode($response);
					}
				}
			}

			// Handle selected devices
			if (!empty($postedData['selected-device'])) {
				$devices = explode(",", $postedData['selected-device']);
				foreach ($devices as $device) {
					$data['whatToDelete'] = 'registered_devices';
					$data['registered_data'] = trim($device);
					try {
						$this->updateLicense($this->userID, $apiAction, $secret_key, $licenseKey, $data);
					} catch (\Exception $e) {
						$logger = \Config\Services::logger();
						$logger->error(lang('Notifications.logger_api_request_failed', ['message' => $e->getMessage()]));

						$response = [
							'success' => false,
							'status' => 0,
							'msg' => lang('Notifications.error_encounted_problem_license_request'),
						];

						return json_encode($response);
					}
				}
			}			

			$response = [
				'success' => true,
				'status' => 1,
				'msg' => lang('Notifications.success_license_updated'),
			];

			return $this->response->setJSON($response);
		} else {
			// Error response
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => $licenseDetails['message'],
			];

			return $this->response->setJSON($response);
		}
	}
	
	private function updateLicense($userID, $apiAction, $secret_key, $licenseKey, $data)
	{		
		/************************************
		 * SLM Wordpress Plugin integration *
		 ************************************/		
		if ($this->myConfig['licenseManagerOnUse'] === 'slm') {
			$apiUrl = $this->myConfig['licenseServerDomain'] . '?secret_key=' . $secret_key 
					  . '&slm_action=' . $apiAction
					  . '&license_key=' . $licenseKey
					  . '&' . $data['whatToDelete'] . '=' . $data['registered_data'];

			$customHeaders = null;
		}

		/****************************
		 * Built-in License Manager *
		 ***************************/ 		
		else {
			if (strpos($data['whatToDelete'], 'domain') !== false) {
				// Task is for domain
				$type = 'domain';
				$name = $data['registered_data'];
			} else {
				// Task for device
				$type = 'device';
				$name = $data['registered_data'];
			}

			$apiUrl = base_url('api/license/unregister/' . $type . '/' . $name . '/' . $this->myConfig['License_DomainDevice_Registration_SecretKey'] . '/' . $licenseKey);

			log_message('info', '[LicenseManager Controller] api url to be call: ' . $apiUrl);

			$apiKey = $this->UserModel->getUserApiKey($userID);

			// If the API key is not present, return null
			if (empty($apiKey)) {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.Unauthorized_access'),
				];
			
				return $response;
			}
			
			$customHeaders = [
				'Accept' => 'application/json',
				'User-API-Key' => $apiKey,
			];				
		}

		try {
			$apiCall = makeApiCall($apiUrl, $customHeaders);
			$apiResponse = json_decode($apiCall->getBody(), true);

			// Check if the response is a JSON string
			if ($apiResponse === null && json_last_error() !== JSON_ERROR_NONE) {
				// Handle JSON decoding error
				$response = [
					'success' => false,
					'status'  => 0,
					'msg' => lang('Notifications.error_encountered_problem_license_request'),
				];

				return $response;
			}

			return $apiResponse;
	
		} catch (\Exception $e) {
			// Handle errors
			$response = [
				'success' => false,
				'status'  => 0,
				'msg'     => lang('Notifications.error_encountered_problem_license_request'),
			];
	
			return $response;
		}
	}
	
	public function clear_license_activity_log_action($licenseKey)
	{
		$this->checkIfLoggedIn(); // Check if user is logged in before proceeding
	
		// Retrieve the record based on the license key
		$licenseRecord = $this->LicensesModel->where('license_key', $licenseKey)->first();        
	
		if ($licenseRecord) {
			// Define the where clause for filtering licenses
			$where = ['license_key' => $licenseKey];
	
			// Delete all records with the same license key
			$deleted = $this->LicenseLogsModel->where($where)->delete();
	
			if ($deleted) {
				// Log the activity
				licenseManagerLogger($licenseRecord['license_key'], 'log: License activity log deletion initiated', 'yes');

				// Deletion successful
				$response = [
					'success' => true,
					'status' => 1,
					'msg' => lang('Notifications.success_activity_log_cleared')
				];
			} else {
				// No records found to delete
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.error_clearing_activity_log_no_record')
				];
			}           
		} else {
			// No license record found with the provided license key
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.error_clearing_activity_log_no_license_record')
			];                                      
		}       
	
		return $this->response->setJSON($response);
	}
}