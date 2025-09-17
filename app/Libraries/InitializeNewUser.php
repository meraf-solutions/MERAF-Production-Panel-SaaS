<?php

namespace App\Libraries;

use App\Models\UserSettingsModel;

class InitializeNewUser
{
    protected $userAcctDetails;
    protected $userID;
    protected $userDataPath;
    protected $myConfig;
    protected $UserSettingsModel;

    public function __construct()
    {
		$this->userAcctDetails = auth()->user();
        $this->userID = $this->userAcctDetails->id ?? NULL;
        $this->userDataPath = $this->userID ? USER_DATA_PATH . $this->userID . DIRECTORY_SEPARATOR : NULL;
        $this->myConfig = getMyConfig('', $this->userID);
        $this->UserSettingsModel = new UserSettingsModel();
    }

	public function initializeUserDirectories()
	{
	    log_message('debug', '[Libraries/InitializeNewUser] Starting directory initialization for user: ' . $this->userID);
        log_message('debug', '[Libraries/InitializeNewUser] User data path: ' . $this->userDataPath);
    
		$htaccessContent = "# Disable directory listing
	Options -Indexes
	
	# Deny access to all files by default
	<FilesMatch \".*\">
		Order Allow,Deny
		Deny from all
	</FilesMatch>
	
	# Prevent script execution
	<FilesMatch \"(?i)\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|htm|html|shtml|sh|cgi|json|txt)$\">
		Order Deny,Allow
		Deny from all
	</FilesMatch>
	
	# Enable mod_rewrite
	<IfModule mod_rewrite.c>
		RewriteEngine On
		RewriteRule ^(.*)$ - [F,L]
	</IfModule>
	
	# If someone tries to access a file directly, return 403 Forbidden
	<IfModule mod_rewrite.c>
		RewriteEngine On
		RewriteBase /user-data/
		RewriteCond %{REQUEST_FILENAME} -f
		RewriteRule . - [F,L]
	</IfModule>";
	
		$indexContent = "<?php\nhttp_response_code(403);\ndie('Access Denied');";
	
		// First secure the base USER_DATA_PATH directory
		if (!is_dir(USER_DATA_PATH)) {
			mkdir(USER_DATA_PATH, 0750, true);
		}
	
		// Check and create security files in base USER_DATA_PATH
		$baseHtaccess = USER_DATA_PATH . '.htaccess';
		$baseIndex = USER_DATA_PATH . 'index.php';
	
		if (!file_exists($baseHtaccess)) {
			file_put_contents($baseHtaccess, $htaccessContent);
			chmod($baseHtaccess, 0644);
		}
	
		if (!file_exists($baseIndex)) {
			file_put_contents($baseIndex, $indexContent);
			chmod($baseIndex, 0644);
		}
	
		// Ensure user-specific directory exists and is secured
		if ($this->userDataPath) {
			if (!is_dir($this->userDataPath)) {
				mkdir($this->userDataPath, 0750, true);
			}
	
			// Create or update user directory security files
			if (!file_exists($this->userDataPath . '.htaccess')) {
				file_put_contents($this->userDataPath . '.htaccess', $htaccessContent);
				chmod($this->userDataPath . '.htaccess', 0644);
			}
		
			if (!file_exists($this->userDataPath . 'index.php')) {
				file_put_contents($this->userDataPath . 'index.php', $indexContent);
				chmod($this->userDataPath . 'index.php', 0644);
			}
	
			$directories = [
				$this->userDataPath . $this->myConfig['userAppSettings'] . DIRECTORY_SEPARATOR,
				$this->userDataPath . $this->myConfig['userProductPath'] . DIRECTORY_SEPARATOR,
				$this->userDataPath . $this->myConfig['userLogsPath'] . DIRECTORY_SEPARATOR,
				$this->userDataPath . $this->myConfig['userEmailTemplatesPath'] . DIRECTORY_SEPARATOR,
			];
	
			// Create directories with proper permissions and security
			foreach ($directories as $dir) {
				if (!is_dir($dir)) {
					mkdir($dir, 0755, true);
				}
				
				// Add security files to subdirectories
				if(!file_exists($dir . '.htaccess')) {
					file_put_contents($dir . '.htaccess', $htaccessContent);
					chmod($dir . '.htaccess', 0644);
				}
	
				if(!file_exists($dir . 'index.php')) {
					file_put_contents($dir . 'index.php', $indexContent);
					chmod($dir . 'index.php', 0644);
				}
			}
	
			// Fix any existing directory permissions
			$this->secureExistingDirectories($this->userDataPath);
	
			$this->initializeJsonFiles();
		}
		
		// After directories creation, verify
        if ($this->userDataPath && is_dir($this->userDataPath)) {
            log_message('debug', '[Libraries/InitializeNewUser] Successfully created user directory at: ' . $this->userDataPath);
        } else {
            log_message('error', '[Libraries/InitializeNewUser] Failed to create user directory at: ' . $this->userDataPath);
        }
	}

	protected function secureExistingDirectories($basePath)
	{
		if (is_dir($basePath)) {
			// Fix base directory permissions
			chmod($basePath, 0750);
			
			// Get all subdirectories
			$items = new \DirectoryIterator($basePath);
			
			foreach ($items as $item) {
				if ($item->isDot()) continue;
				
				$path = $item->getPathname();
				
				if ($item->isDir()) {
					// Recursive call for directories
					$this->secureExistingDirectories($path);
				} else {
					// Set file permissions
					chmod($path, 0644);
				}
			}
		}
	}

    protected function initializeJsonFiles()
    {
        $jsonFiles = [
            $this->userDataPath . $this->myConfig['userAppSettings'] . 'product-variations.json', 
            $this->userDataPath . $this->myConfig['userAppSettings'] . 'product-email-templates.json',
        ];

        foreach ($jsonFiles as $file) {
            if (!file_exists($file)) {
                file_put_contents($file, json_encode([]));
            }
        }
    }

    public function initializeSecretKeys()
    {
        $keys = [
            'License_Validate_SecretKey',
            'License_Create_SecretKey',
            'License_DomainDevice_Registration_SecretKey',
            'Manage_License_SecretKey',
            'General_Info_SecretKey',
        ];

        foreach ($keys as $key) {
			$this->UserSettingsModel->setUserSetting($key, generateApiKey(8), $this->userID);
        }				
    }

	public function initializeDefaultEmailTemplate()
	{
		$emailTemplateConfigPath = $this->userDataPath . $this->myConfig['userAppSettings'];
		$emailTemplatesPath = $this->userDataPath . $this->myConfig['userEmailTemplatesPath'];
		$emailTemplateConfigFile = $emailTemplateConfigPath . 'product-email-templates.json';
		$defaultEmailTemplateContent = json_encode(['default_email_template' => '']);
		$defaultEmailTemplateFile = ROOTPATH . 'public/assets/default_email_template_v1.0.0.zip';
	
		if (!file_exists($defaultEmailTemplateFile)) {
			log_message('error', '[Libraries/InitializeNewUser] Default email template zip file not found: ' . $defaultEmailTemplateFile);
			return;
		}
	
		// Create directory if it doesn't exist
		if (!is_dir($emailTemplateConfigPath) || !is_dir($emailTemplatesPath)) {
			$this->initializeUserDirectories();
		}

		if (!file_exists($emailTemplateConfigFile)) {
			$this->initializeJsonFiles();
		}
	
		// Initialize or update the email template configuration
		if (file_exists($emailTemplateConfigFile)) {
			$existingEmailTemplateConfig = json_decode(file_get_contents($emailTemplateConfigFile), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				log_message('error', '[Libraries/InitializeNewUser] Invalid JSON in existing email template config: ' . json_last_error_msg());
				return;
			}
		}
	
		$saveDefaultSettings = file_put_contents($emailTemplateConfigFile, $defaultEmailTemplateContent);
		if (!$saveDefaultSettings) {
			log_message('error', '[Libraries/InitializeNewUser] Unable to save default email template configuration');
			return;
		}
	
		$zip = new \ZipArchive();
		$zipResult = $zip->open($defaultEmailTemplateFile);
	
		if ($zipResult === TRUE) {
			try {
				if (!$zip->extractTo($emailTemplatesPath)) {
					log_message('error', '[Libraries/InitializeNewUser] Unable to extract the Default Email Template: ' . $zip->getStatusString());
				}
			} finally {
				$zip->close();
			}
		} else {
			log_message('error', '[Libraries/InitializeNewUser] Unable to open default email template zip file. Error code: ' . $zipResult);
		}
	}
	
	// Add this to InitializeNewUser.php
    public function getUserID()
    {
        return $this->userID;
    }
}