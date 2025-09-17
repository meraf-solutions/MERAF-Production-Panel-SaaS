<?php

if (!function_exists('getEmailTemplateDetails')) {
    function getEmailTemplateDetails($userID, $template='') {
		$myConfig = getMyConfig('', $userID);

		// Define the email templates directory path
		$userDataPath = USER_DATA_PATH . $userID . DIRECTORY_SEPARATOR; 

		$templatesDirectory = $userDataPath . $myConfig['userEmailTemplatesPath'];
	
		// Check if the directory exists
		if (!is_dir($templatesDirectory)) {
			// Handle error if the directory doesn't exist
			echo lang('Notifications.error_no_email_template_folder');
			return;
		}
	
		// Initialize an array to store template details
		$emailTemplates = array();
	
		// If a specific template is provided, get its details
		if ($template !== '') {
			// Get the path to the template file
			$templateFilePath = $templatesDirectory . '/' . $template . '/' . $template . '.php';
	
			// Check if the template file exists
			if (!file_exists($templateFilePath)) {
				echo lang('Notifications.template_file_not_found');
				return;
			}
	
			// Read the content of the PHP file
			$fileContent = file_get_contents($templateFilePath);
	
			// Use regular expression to match the comment block
			preg_match('/\/\*\*(.*?)\*\//s', $fileContent, $matches);
	
			// Initialize variables to store template details
			$templateName = '';
			$description = '';
			$version = '';
			$author = '';
			$authorURI = ''; 
	
			// Check if the match is found
			if (!empty($matches[1])) {
				// Split the comment block into lines
				$lines = explode("\n", $matches[1]);
	
				// Loop through each line
				foreach ($lines as $line) {
					// Use regular expressions to extract key-value pairs
					if (preg_match('/\* (.*?): (.*)/', $line, $matches)) {
						$key = trim($matches[1]);
						$value = trim($matches[2]);
	
						// Assign values to respective variables based on keys
						switch ($key) {
							case 'Email Template Name':
								$templateName = $value;
								break;
							case 'Description':
								$description = $value;
								break;
							case 'Version':
								$version = $value;
								break;
							case 'Author':
								$author = $value;
								break;
							case 'Author URI':
								$authorURI = $value;
								break;
						}
					}
				}
	
				// Store template details in the array
				$emailTemplates[$template] = array(
					'templateName' => $templateName,
					'description' => $description,
					'version' => $version,
					'author' => $author,
					'authorURI' => $authorURI
				);
			}
		} else {
			// If no specific template is provided, get details for all templates
			$subdirectories = glob($templatesDirectory . '/*', GLOB_ONLYDIR);
	
			// Loop through each subdirectory
			foreach ($subdirectories as $subdirectory) {
				// Extract folder name from the path
				$folderName = basename($subdirectory);
	
				// Get list of PHP files in the subdirectory
				$files = glob($subdirectory . '/*.php');
	
				// Loop through each PHP file
				foreach ($files as $file) {
					// Read the content of the PHP file
					$fileContent = file_get_contents($file);
	
					// Use regular expression to match the comment block
					preg_match('/\/\*\*(.*?)\*\//s', $fileContent, $matches);
	
					// Initialize variables to store template details
					$templateName = '';
					$description = '';
					$version = '';
					$author = '';
					$authorURI = ''; 
	
					// Check if the match is found
					if (!empty($matches[1])) {
						// Split the comment block into lines
						$lines = explode("\n", $matches[1]);
	
						// Loop through each line
						foreach ($lines as $line) {
							// Use regular expressions to extract key-value pairs
							if (preg_match('/\* (.*?): (.*)/', $line, $matches)) {
								$key = trim($matches[1]);
								$value = trim($matches[2]);
	
								// Assign values to respective variables based on keys
								switch ($key) {
									case 'Email Template Name':
										$templateName = $value;
										break;
									case 'Description':
										$description = $value;
										break;
									case 'Version':
										$version = $value;
										break;
									case 'Author':
										$author = $value;
										break;
									case 'Author URI':
										$authorURI = $value;
										break;
								}
							}
						}
	
						// Store template details in the array
						$emailTemplates[$folderName] = array(
							'templateName' => $templateName,
							'description' => $description,
							'version' => $version,
							'author' => $author,
							'authorURI' => $authorURI
						);
					}
				}
			}
		}
	
		// Print or return the array of email templates
		// echo '<pre>'; print_r($emailTemplates); echo '</pre>';
		return $emailTemplates;
	}
}

if (!function_exists('updateEmailTemplateDetails')) {
	function updateEmailTemplateDetails($userID) {

		$myConfig = getMyConfig('', $userID);

		// Define the email templates directory path
		$userDataPath = USER_DATA_PATH . $userID . DIRECTORY_SEPARATOR; 

		$templatesDirectory = $userDataPath . $myConfig['userEmailTemplatesPath'];		

		// Path to product-email-templates.json
		$emailTemplateConfigFilePath = $userDataPath . $myConfig['userAppSettings'] . 'product-email-templates.json';
		
		// Retrieve saved email template config from JSON file
		$emailTemplateConfigContent = [];
		if (file_exists($emailTemplateConfigFilePath)) {
			$emailTemplateConfigContent = json_decode(file_get_contents($emailTemplateConfigFilePath), true);
		}
		
		// Get current email template details
		$existingSetup = getEmailTemplateDetails($userID);

		// Loop through existing setup and compare with config file content
		foreach ($existingSetup as $key => $value) {
			// If key does not exist in the config file, add it with an empty array
			if (!array_key_exists($key, $emailTemplateConfigContent)) {
				$emailTemplateConfigContent[$key] = "";
			}
		}

		// Save the updated JSON data back to the file
		if (file_put_contents($emailTemplateConfigFilePath, json_encode($emailTemplateConfigContent, JSON_PRETTY_PRINT))) {
			// Data saved successfully
			return true;
		} else {
			// Error occurred while saving
			return false;
		}
	}
}