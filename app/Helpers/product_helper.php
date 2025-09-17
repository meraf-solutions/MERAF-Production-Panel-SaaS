<?php

if (!function_exists('productBasename')) {
    /**
     * Get the base product name by removing the variation from the query.
     *
     * @param string $queryName The name of the product with the variation.
     * @param int $userID The ID of the user.
     * @return string The base product name without the variation.
     */
    function productBasename($queryName, $userID)
    {
        // Decode the URL-encoded query name
        $queryName = urldecode($queryName);
        
        // Retrieve user-specific configuration settings
        $myConfig = getMyConfig('', $userID);

        // Define the path to the user's data folder
        $userDataPath = USER_DATA_PATH . $userID . DIRECTORY_SEPARATOR;

        // Path to the JSON file that contains product variations
        $variationFilePath = $userDataPath . $myConfig['userAppSettings'] . 'product-variations.json';

        // Initialize an empty array to hold product variations
        $product_variations = [];

        // If the variations file exists, read and decode it into an array
        if (file_exists($variationFilePath)) {
            $product_variations = json_decode(file_get_contents($variationFilePath), true);
        }

        // Search through the variations to find a match for the query name
        foreach ($product_variations as $variation => $products) {
            $product_list = explode(',', $products);
            
            foreach ($product_list as $product) {
                $product = trim($product);

                if (!empty($product) && strpos($queryName, $product) !== false) {

                    if (substr($queryName, -strlen($variation)) === $variation) {
                        return trim($product);
                    }
                }
            }
        }

        // If no match is found, return the submitted product name
        return $queryName;
    }
}

if (!function_exists('get_product_directories')) {
    /**
     * Get the directory names inside the specified folder.
     *
     * @param string $folder The folder path.
     * @return array Array of directory names.
     */
    function get_product_directories($folder)
    {
        $directories = [];

        if (is_dir($folder)) {
            $iterator = new DirectoryIterator($folder);

            foreach ($iterator as $item) {
                if ($item->isDir() && !$item->isDot()) {
                    $directories[] = $item->getFilename();
                }
            }

            sort($directories, SORT_STRING);
        }

        return $directories;
    }
}

if (!function_exists('productList')) {
    function productList($userID = NULL)
    {
        // Get the logged in user's settings
        $userID = $userID === NULL ? auth()->id() : $userID;

        $myConfig = getMyConfig('', $userID);

        $userDataPath = USER_DATA_PATH . $userID . DIRECTORY_SEPARATOR;
        
		$products = [];
		
		$productDirectories = get_product_directories($userDataPath . $myConfig['userProductPath'] );
	
		foreach ($productDirectories as $productName) {
			// Trim spaces from the beginning and end of each product name
			$trimmedProductName = trim($productName);
	
			// Add the trimmed product name to the array
			$products[] = $trimmedProductName;
		}
	
		return $products;
    }
}

if (!function_exists('productListWithVariation')) {
    function productListWithVariation($userID = NULL)
    {
        // Get the logged in user's settings
        $userID = $userID === NULL ? auth()->id() : $userID;
        
        $myConfig = getMyConfig('', $userID);
        
		$productNames = productList($userID);

        $productVariations = getProductVariations($userID);

        $productFullList = [];
		
        foreach ($productNames as $productName) {
            $found = false;
            foreach ($productVariations as $variation => $productsIncluded) {
                $productsIncluded = explode(",", $productsIncluded);
                if (in_array($productName, $productsIncluded)) {
                    $productFullList[] = $productName . ' ' . $variation;
                    $found = true;
                }
            }
            if (!$found) {
                $productFullList[] = $productName;
            }
        }
	
		return $productFullList;
    }
}

if (!function_exists('productDetails')) {
	function productDetails($specificProduct=NULL, $userID = NULL)
	{
        // Get the logged in user's settings
        $userID = $userID === NULL ? auth()->id() : $userID;
        
		$products = [];
	
		$productNames = productList($userID);

        $myConfig = getMyConfig('', $userID);

        $userDataPath = USER_DATA_PATH . $userID . DIRECTORY_SEPARATOR;
	
		$key = 0;
		foreach ($productNames as $productName) {
            // Generate product code
            $encodedProductName = sha1($productName, false);

			// Get product details from json file.
			$jsonPath = $userDataPath . $myConfig['userProductPath'] . $encodedProductName . '.json';
	
			if (file_exists($jsonPath)) {
				$readJson = file_get_contents($jsonPath);
				$productDetails = json_decode($readJson, true);
	
				if ($productDetails !== null) {
					// Use the product name as the key
					$products[$productName]['version'] 		= $productDetails[$productName]['version'];
					$products[$productName]['url'] 			= $productDetails[$productName]['url'];
					$products[$productName]['changelog'] 	= $productDetails[$productName]['changelog'];

                    // Get the envato item code if envato is enabled                    
                    if ($myConfig['userEnvatoSyncEnabled'])  {
                        $envatoItemCodes = [];
                        $envatoItemCodes = json_decode($myConfig['userEnvatoItemCodes'], true) ?: [];

                        if (array_key_exists( $encodedProductName, $envatoItemCodes)) {
                            $products[$productName]['envato_item_code'] = $envatoItemCodes[$encodedProductName];
                        }
                        else {
                            $products[$productName]['envato_item_code'] = '';
                        }
                    }
				} else {
					// Handle invalid JSON format if needed.
					$products[$productName] = [lang('Notifications.error_invalid_json_format')];
				}
			} else {
				$products[$productName] = [lang('Notifications.no_data_found')];
			}

			$key++;
		}
	
        if ($specificProduct) {
            $specificProduct = productBasename($specificProduct, $userID);
            return $products[$specificProduct] ?? null;
        }
        else {
            return $products;
        }
	}
}

if (!function_exists('getProductVariations')) {
    function getProductVariations($userID = NULL)
    {
        // Get the logged in user's settings
        $userID = $userID === NULL ? auth()->id() : $userID;
        
        $myConfig = getMyConfig('', $userID);

        $userDataPath = USER_DATA_PATH . $userID . DIRECTORY_SEPARATOR;

        // Construct the file path for product variations JSON file
        $variationFilePath = $userDataPath . $myConfig['userAppSettings'] . 'product-variations.json';
        $productVariations = [];

        // Check if the JSON file exists, then read and decode it
        if (file_exists($variationFilePath)) {
            $productVariations = json_decode(file_get_contents($variationFilePath), true);
        }
        else {
            $productVariations = lang('Notifications.no_set_product_variations');
        }

        return $productVariations;
    }
}

if (!function_exists('allProductCurrentVersions')) {
    function allProductCurrentVersions($userID = NULL)
    {
        // Get the logged in user's settings
        $userID = $userID === NULL ? auth()->id() : $userID;
        
        $myConfig = getMyConfig('', $userID);
        
        $productVersionList = [];
        $productList = [];
        $productVariations = getProductVariations($userID);

        // Iterate through the list of products
        foreach (productList($userID) as $productName) {
            $found = false;
            foreach ($productVariations as $variation => $productsIncluded) {
                $productsIncluded = explode(",", $productsIncluded);
                // Check if the current product is included in any variation
                if (in_array($productName, $productsIncluded)) {
                    // If found in variation, add it with variation to product list
                    $productList[] = $productName . ' ' . $variation;
                    $found = true;
                }
            }
            // If not found in any variation, add the product without variation
            if (!$found) {
                $productList[] = $productName;
            }
        }

        // Fetch product details
        $productDetails = productDetails(NULL, $userID);
        
        // Iterate through the product list to gather version details
        foreach ($productList as $productName) {
            $productBasename = productBasename($productName, $userID);
            // Check if $productBasename exists in $productDetails and has version key before accessing ['version']
            if (isset($productDetails[$productBasename]) && isset($productDetails[$productBasename]['version'])) {
                $productVersionList[$productName] = $productDetails[$productBasename]['version'];
            } else {
                // Handle the case where $productBasename doesn't exist in $productDetails or version key is missing
                // For example, set a default version or log the error
                $productVersionList[$productName] = 'Unknown'; // Set a default version
            }
        }

        return $productVersionList;
    }
}

if (!function_exists('getProductFiles')) {
	function getProductFiles($product='', $userID = NULL) 
	{
        // Get the logged in user's settings
        $userID = $userID === NULL ? auth()->id() : $userID;
        
        $myConfig = getMyConfig('', $userID);

        $userDataPath = USER_DATA_PATH . $userID . DIRECTORY_SEPARATOR;

		$productFiles = [];
	
		$productList = productList($userID);
	
		foreach ($productList as $productIndividual) {
			$productIndividualPath = $userDataPath . $myConfig['userProductPath'] . $productIndividual;
            
			// Check if the directory exists
			if (is_dir($productIndividualPath)) {
				// Get all files in the directory
				$files = scandir($productIndividualPath);
	
				// Filter out the files you want to exclude (e.g., 'index.php', '.', '..')
				$filteredFiles = array_filter($files, function ($file) {
					return $file !== 'index.php' && $file !== '.' && $file !== '..';
				});
	
				// Store the list of files for each product in the array
				$productFiles[$productIndividual] = $filteredFiles;
			}
		}
		
		if($product !== '') {
			return $productFiles[$product];
		}
		else {
			return $productFiles;
		}
	}
}

if (!function_exists('getProductGuide')) {
    function getProductGuide($product, $userID) 
    {        
        // Validate input parameters
        if (empty($product) || empty($userID)) {
            return false;
        }

        $myConfig = getMyConfig('', $userID);

        // Use the correct variable name ($product instead of $productName)
        $userDataPath = USER_DATA_PATH . $userID . DIRECTORY_SEPARATOR;
        $productGuideFile = $userDataPath . $myConfig['userProductPath'] . DIRECTORY_SEPARATOR . sha1($product, false) . '.txt';
        
        // Check if file exists
        if (!file_exists($productGuideFile)) {
            return false;
        }
        
        // Add error handling for file_get_contents
        try {
            $content = file_get_contents($productGuideFile);
            return ($content !== false) ? $content : false;
        } catch (\Exception $e) {
            return false;
        }
    }
}