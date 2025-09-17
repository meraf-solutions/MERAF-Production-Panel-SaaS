<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use CodeIgniter\Controller;
use App\Models\UserSettingsModel;

class AppUpdater extends BaseController
{
	
	protected $versionFilePath = USER_DATA_PATH . 'version.json';

	protected $myConfig;
	protected $UserSettingsModel;
	
	public function __construct()
	{
        $this->myConfig = getMyConfig('', 1);

		// Set the locale dynamically based on user preference
		setMyLocale();

		// Initialize models
		$this->UserSettingsModel = new UserSettingsModel();		
	}

	protected function checkIfLoggedIn()
	{
		if(NULL === auth()->id()) {
			return redirect()->to('login');
		}
	}
	
    public function index($reinstall = '')
    {
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed		
		
        // Read the version information from version.json
        $versionData = json_decode(file_get_contents($this->versionFilePath), true);

        // Get the update URL from the version data
        $updateURL = !empty($versionData['url']) ? $versionData['url'] : '';

        if (empty($updateURL)) {
            return redirect()->to(base_url('error_update'));
        }

		$data['userData'] = auth()->user();
		$data['updateURL'] = $updateURL;
		$data['myConfig'] = $this->myConfig;

        if($reinstall){
            $data['pageTitle'] = $this->myConfig['appName'];
            $data['headingText'] = lang('Pages.Reinstall_App');
            $data['submitButton'] = lang('Pages.Proceed_To_Reinstall');
			$data['actionURL'] = base_url('reinstall-production-panel/force-update');
        }
		else {
            $data['pageTitle'] = $this->myConfig['appName'] . ' Updater';
            $data['headingText'] = lang('Pages.Update_Version');
            $data['submitButton'] = lang('Pages.Proceed_With_The_Update');
			$data['actionURL'] = base_url('process-app-updater');
        }

        // Pass the update URL to the view
        return view('update_page', $data);
    }

	public function update_codeigniter()
	{
		$this->checkIfLoggedIn(); // Check if user is logged in before proceeding
	
		// GitHub API endpoint for CodeIgniter repository releases
		$apiUrl = 'https://api.github.com/repos/codeigniter4/framework/releases/latest';

		$customHeaders = [
			'Accept' => 'application/json',
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
		];
	
		try {
			$response = makeApiCall($apiUrl, $customHeaders);
			$data = json_decode($response->getBody(), true);
	
			// Extract latest version
			if (isset($data['message']) || !isset($data['tag_name'])) {
				$data['CIlatestVersion'] = \CodeIgniter\CodeIgniter::CI_VERSION;
			} else {
				$data['CIlatestVersion'] = preg_replace('/[^0-9.]/', '', $data['tag_name']);
			}
		} catch (\Exception $e) {
			// If there's an error, use the current version as the latest version
			$data['CIlatestVersion'] = \CodeIgniter\CodeIgniter::CI_VERSION;
			// You might want to log the error here
			log_message('error', '[AppUpdater] Failed to fetch latest CodeIgniter version: ' . $e->getMessage());
		}
	
		$data['CIinstalledVersion'] = \CodeIgniter\CodeIgniter::CI_VERSION;    
		$data['pageTitle'] = lang('Pages.CodeIgniter_Updater');
		$data['headingText'] = lang('Pages.Update_CodeIgniter');
		$data['submitButton'] = lang('Pages.Proceed_With_The_Update');
		$data['actionURL'] = base_url('process-codeigniter-updater');
		$data['userData'] = auth()->user();
		$data['myConfig'] = $this->myConfig;
	
		// Pass the update URL to the view
		return view('update_page', $data);
	}

	public function update_process($forceUpdate = '')
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		// Read the version information from version.json
		if (!file_exists($this->versionFilePath)) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.The_app_JSON_file_cannot_be_found'),
			];

			return $this->response->setJSON($response);
		}

		// Force update the version.json
		if($forceUpdate) {
			$this->productJSONupdate();
		}

		$versionData = json_decode(file_get_contents($this->versionFilePath), true);

		// Get the update URL from the version data
		$updateURL = !empty($versionData['url']) ? $versionData['url'] : '';

		// Fetch the URL from the external source
		if (!$updateURL) {
			$updateURL = fetchVersionDetails()['MERAF Production Panel SaaS']['url'];
		}

		if (empty($updateURL)) {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.No_update_URL_found'),
			];

			return $this->response->setJSON($response);
		}

		// Define the path to extract the update
		$updateFilePath = WRITEPATH . 'update.zip';

		// Download the update file
		$context = stream_context_create([
			'http' => [
				'ignore_errors' => true, // Ignore HTTP errors
			],
		]);

		$updateData = file_get_contents($updateURL, false, $context);

		// Check for HTTP response code (404)
		$http_response_header = $http_response_header ?? [];
		$response_code = isset($http_response_header[0]) ? explode(' ', $http_response_header[0])[1] : null;

		if ($response_code === '404') {
			// Handle 404 error
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.Update_file_not_found_404_error'),
			];

			return $this->response->setJSON($response);
		}

		if ($updateData === false) {
			// Handle other errors
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.Failed_to_download_the_update_file'),
			];

			return $this->response->setJSON($response);
		}

		// delete update.zip if exists
		if(file_exists($updateFilePath)) {
			unlink($updateFilePath);
		}

		// Save the update file locally
		file_put_contents($updateFilePath, $updateData);

		// Extract the contents of the update file to ROOTPATH
		$zip = new \ZipArchive();
		if ($zip->open($updateFilePath) === TRUE) {
			// Create a temporary directory for extraction
			$tempDir = WRITEPATH . 'temp_update_' . time();
			if (!mkdir($tempDir, 0755, true)) {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.Failed_to_create_temporary_directory'),
				];
				return $this->response->setJSON($response);
			}

			// Extract to the temporary directory
			if (!$zip->extractTo($tempDir)) {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.Failed_to_extract_update_file') . ': ' . $zip->getStatusString(),
				];
				$zip->close();
				$this->removeDirectory($tempDir);
				return $this->response->setJSON($response);
			}
			$zip->close();

			// Move files from temp directory to ROOTPATH
			if (!$this->moveFiles($tempDir, ROOTPATH)) {
				$response = [
					'success' => false,
					'status' => 0,
					'msg' => lang('Notifications.Failed_to_move_updated_files'),
				];
				$this->removeDirectory($tempDir);
				return $this->response->setJSON($response);
			}

			// Clean up
			$this->removeDirectory($tempDir);
		} else {
			// Handle error if unable to open the zip file
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.Unable_to_extract_the_update_file'),
			];

			return $this->response->setJSON($response);
		}

		// Delete the temporary update file
		unlink($updateFilePath);

		// delete the installer folder
		$installerPath = ROOTPATH . 'public/install';
		if(file_exists($installerPath)) {
			$this->removeDirectory($installerPath);
		}

		// Force update the version.json
		if(!$forceUpdate) {
			$this->productJSONupdate();
		}		

		// Clear the CI cache
		clearCache();

		$response = [
			'success' => true,
			'status' => 1,
			'msg' => lang('Notifications.Update_successfully_applied'),
		];

		// check if returnUrl is present
		$request = service('request');
		if((null !== $request->getGet('returnUrl'))) {
			return redirect()->to($request->getGet('returnUrl'));
		}
		
		// Display success message or redirect to home page
		return $this->response->setJSON($response);
	}

	private function moveFiles($source, $dest)
	{
		$dir = opendir($source);
		if (!$dir) {
			return false;
		}

		// List of files that should not be overwritten if they already exist
		$preserveFiles = [
			'app/Config/Cache.php'
		];

		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				$srcFile = $source . '/' . $file;
				$destFile = $dest . '/' . $file;
				
				if (is_dir($srcFile)) {
					if (!file_exists($destFile)) {
						mkdir($destFile);
					}
					if (!$this->moveFiles($srcFile, $destFile)) {
						return false;
					}
				} else {
					// Check if this is a file that should be preserved
					$relativePath = str_replace(ROOTPATH, '', $destFile);
					$relativePath = str_replace('\\', '/', $relativePath); // Normalize path separators
					
					if (in_array($relativePath, $preserveFiles) && file_exists($destFile)) {
						// Skip this file - don't overwrite it
						log_message('info', 'Preserved existing file during update: ' . $relativePath);
						continue;
					}
					
					if (!rename($srcFile, $destFile)) {
						return false;
					}
				}
			}
		}
		closedir($dir);
		return true;
	}

	private function removeDirectory($dir) {
		if (!file_exists($dir)) {
			return true;
		}
		if (!is_dir($dir)) {
			return unlink($dir);
		}
		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') {
				continue;
			}
			if (!$this->removeDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
				return false;
			}
		}
		return rmdir($dir);
	}

	public function update_codeigniter_process()
	{
		$this->checkIfLoggedIn(); // Check if user is logged before to proceed

		// Change directory to your CodeIgniter project
		chdir(ROOTPATH);

		// Execute Composer update command
		// exec('composer update', $output, $returnCode);
		exec('composer update 2>&1', $output, $returnCode);

		// Check if the update was successful
		if ($returnCode === 0) {
			$response = [
				'success' => true,
				'status' => 1,
				'msg' => lang('Notifications.CodeIgniter_updated_successfully'),
				'composerLog' => json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			];      
		} else {
			$response = [
				'success' => false,
				'status' => 0,
				'msg' => lang('Notifications.Failed_to_update_CodeIgniter_Please_check_the_error_output'),
				'composerLog' => json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			];
		}

		// Clear the CI cache
		clearCache();

		// Display success message or redirect to home page
		return $this->response->setJSON($response);
	}

    protected function productJSONupdate()
    {
        $productDetails = fetchVersionDetails();

		if ($productDetails !== false) {

			// update the appVersion in settings
			$this->UserSettingsModel->setUserSetting('appVersion', $productDetails['MERAF Production Panel SaaS']['version'], 0);

			$response = [
				'newVersion' => false,
				'status' => 0,
				'url' => $productDetails['MERAF Production Panel SaaS']['url'],
				'changelog' => $productDetails['MERAF Production Panel SaaS']['changelog'],
				'timestamp' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s')
			];

            $jsonData = json_encode($response, JSON_PRETTY_PRINT);
            file_put_contents($this->versionFilePath, $jsonData);            
		}
    }
}