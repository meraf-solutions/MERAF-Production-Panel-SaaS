<?php

if (!function_exists('loadModuleLibrary')) {
    function loadModuleMenu($libraryName = null)
    {
        $modulesPath = APPPATH . 'Modules';
        $modules = array_diff(scandir($modulesPath), ['.', '..']); // Scan the Modules directory

        $results = []; // Array to collect results

        foreach ($modules as $module) {
            // Use the provided library name or default to {ModuleName}Service
            $currentLibraryName = $libraryName ?? $module . 'Service';
            $libraryPath = "{$modulesPath}/{$module}/Libraries/{$currentLibraryName}.php";

            if (is_file($libraryPath)) {
                $className = "App\\Modules\\{$module}\\Libraries\\{$currentLibraryName}";

                if (class_exists($className, true)) {
                    $instance = new $className();

                    // Check if the method exists
                    if (method_exists($instance, 'moduleDetails')) {
                        $results[$module] = $instance->moduleDetails(); // Collect the result
                    } else {
                        $results[$module] = $instance; // Store the instance if no method
                    }
                }
            }
        }

        // If no results were collected, throw an exception
        // if (empty($results)) {
        //     throw new \Exception("Library '{$libraryName}' not found in any module.");
        // }

        return $results; // Return all collected results
    }
}

if (!function_exists('load_module_languages')) {
    /**
     * Register module language paths with the Language service
     */
    function load_module_languages()
    {
        $modulesPath = APPPATH . 'Modules';
        
        if (!is_dir($modulesPath)) {
            return;
        }

        $language = \Config\Services::language();
        
        // Get immediate subdirectories in the Modules folder
        $modules = array_diff(scandir($modulesPath), ['.', '..']);
        
        foreach ($modules as $module) {
            $languagePath = $modulesPath . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'Language';
            
            if (is_dir($languagePath)) {
                // Get all language folders (en, es, fr, etc.)
                $langDirs = array_diff(scandir($languagePath), ['.', '..']);
                
                foreach ($langDirs as $lang) {
                    $fullLangPath = $languagePath . DIRECTORY_SEPARATOR . $lang;
                    if (is_dir($fullLangPath)) {
                        // Add the language directory to the Language service
                        $language->addDirectory($lang, $fullLangPath);
                    }
                }
            }
        }
    }
}
