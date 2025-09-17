<?php

namespace App\Services;

use CodeIgniter\Config\BaseConfig;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use CodeIgniter\Tasks\Scheduler;

class ModuleScanner
{
    protected $modulesPath;
    protected $cachedLibraries = [];

    public function __construct()
    {
        $this->modulesPath = APPPATH . 'Modules';
    }

    /**
     * Manually load a specific library by module and name
     * 
     * @param string $moduleName Module name
     * @param string $libraryName Library name (without Library/Service suffix)
     * @return object|null Instantiated library or null if not found
     * 
     * example:
     * Load a specific library manually
     * $paypalService = $moduleScanner->loadLibrary('PayPal', 'PayPal');
     * 
     * Example usage in Controller:
     * Load PayPal library only when required
     * $paypalService = $this->moduleScanner->loadLibrary('PayPal', 'PayPal');
     * $paypalService->performAction();
     */
    public function loadLibrary(string $moduleName, string $libraryName)
    {
        $cacheKey = "{$moduleName}_{$libraryName}";
        if (isset($this->cachedLibraries[$cacheKey])) {
            return $this->cachedLibraries[$cacheKey];
        }

        // If global library (e.g., TrialService from App\Libraries)
        if (strtolower($moduleName) === 'trial') {
            $globalClass = "App\\Libraries\\{$libraryName}";
            if (!class_exists($globalClass)) {
                // Try fallback names
                $alternatives = [
                    "App\\Libraries\\{$libraryName}Library",
                    "App\\Libraries\\{$libraryName}Service"
                ];
                foreach ($alternatives as $altClass) {
                    if (class_exists($altClass)) {
                        $globalClass = $altClass;
                        break;
                    }
                }
            }

            if (!class_exists($globalClass)) {
                log_message('debug', "[ModuleScanner] Global library not found: {$globalClass}");
                return null;
            }

            $instance = new $globalClass();
            $this->cachedLibraries[$cacheKey] = $instance;
            return $instance;
        }

        // Otherwise, module-based library
        $libraryClassName = "App\\Modules\\{$moduleName}\\Libraries\\{$libraryName}";
        if (!class_exists($libraryClassName)) {
            $alternatives = [
                "App\\Modules\\{$moduleName}\\Libraries\\{$libraryName}Library",
                "App\\Modules\\{$moduleName}\\Libraries\\{$libraryName}Service"
            ];
            foreach ($alternatives as $altClass) {
                if (class_exists($altClass)) {
                    $libraryClassName = $altClass;
                    break;
                }
            }
        }

        if (!class_exists($libraryClassName)) {
            log_message('debug', "[ModuleScanner] Module library not found: {$libraryClassName}");
            return null;
        }

        $library = new $libraryClassName();
        $this->cachedLibraries[$cacheKey] = $library;

        return $library;
    }

    /**
     * Load libraries for a specific module
     * 
     * @param string $moduleName Module name
     * @return array Instantiated libraries
     * 
     * example:
     * Load all libraries for a specific module
     * $moduleLibraries = $moduleScanner->loadModuleLibraries('PayPal');
     */
    public function loadModuleLibraries(string $moduleName): array
    {
        $libraries = [];
        $modulePath = $this->modulesPath . DIRECTORY_SEPARATOR . $moduleName;
        $librariesPath = $modulePath . DIRECTORY_SEPARATOR . 'Libraries';

        if (!is_dir($librariesPath)) {
            log_message('debug', "[ModuleScanner] No libraries found for module: {$moduleName}");
            return $libraries;
        }

        $libraryFiles = scandir($librariesPath);
        foreach ($libraryFiles as $libraryFile) {
            if ($libraryFile === '.' || $libraryFile === '..') {
                continue;
            }

            // Only process PHP files that end with 'Library.php' or 'Service.php'
            if (preg_match('/^(.+)(Library|Service)\.php$/', $libraryFile, $matches)) {
                $libraryName = $matches[1];
                $library = $this->loadLibrary($moduleName, $libraryName);
                
                if ($library) {
                    $libraries[$libraryName . $matches[2]] = $library;
                }
            }
        }

        return $libraries;
    }

    /**
     * Scan and load module tasks
     */
    public function loadModuleTasks(Scheduler $schedule): void
    {
        $modules = $this->scanModules();
        
        foreach ($modules as $moduleName => $moduleInfo) {
            $tasksPath = $moduleInfo['path'] . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Tasks.php';
            
            if (file_exists($tasksPath)) {
                // Get the module's tasks configuration class name
                $moduleTasksClass = $moduleInfo['namespace'] . '\\Config\\Tasks';
                
                if (class_exists($moduleTasksClass)) {
                    $moduleTasks = new $moduleTasksClass();
                    
                    // Initialize module tasks
                    if (method_exists($moduleTasks, 'init')) {
                        $moduleTasks->init($schedule);
                    }
                }
            }
        }
    }

    /**
     * Scan for module language files
     */
    public function scanLanguages(): array
    {
        $languages = [];

        if (!is_dir($this->modulesPath)) {
            log_message('debug', '[ModuleScanner] Modules directory not found: ' . $this->modulesPath);
            return $languages;
        }

        // Get immediate subdirectories in the Modules folder
        $items = scandir($this->modulesPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $modulePath = $this->modulesPath . DIRECTORY_SEPARATOR . $item;
            $languagePath = $modulePath . DIRECTORY_SEPARATOR . 'Language';
            
            if (is_dir($languagePath)) {
                // Get all language folders (en, es, fr, etc.)
                $langDirs = scandir($languagePath);
                foreach ($langDirs as $lang) {
                    if ($lang === '.' || $lang === '..') {
                        continue;
                    }

                    $fullLangPath = $languagePath . DIRECTORY_SEPARATOR . $lang;
                    if (is_dir($fullLangPath)) {
                        if (!isset($languages[$lang])) {
                            $languages[$lang] = [];
                        }
                        $languages[$lang][] = [
                            'module' => $item,
                            'path' => $fullLangPath
                        ];
                    }
                }
            }
        }

        return $languages;
    }

    /**
     * Scan for all modules and their configurations
     */
    public function scanModules(): array
    {
        $modules = [];

        if (!is_dir($this->modulesPath)) {
            log_message('debug', '[ModuleScanner] Modules directory not found: ' . $this->modulesPath);
            return $modules;
        }

        // Get immediate subdirectories in the Modules folder
        $items = scandir($this->modulesPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $modulePath = $this->modulesPath . DIRECTORY_SEPARATOR . $item;
            if (is_dir($modulePath)) {
                $modules[$item] = [
                    'path' => $modulePath,
                    'namespace' => 'App\\Modules\\' . $item,
                    'hasAutoload' => file_exists($modulePath . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Autoload.php'),
                    'hasLanguage' => is_dir($modulePath . DIRECTORY_SEPARATOR . 'Language'),
                    'hasTasks' => file_exists($modulePath . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Tasks.php')
                ];
            }
        }

        return $modules;
    }

    /**
     * Load module autoload configurations
     */
    public function loadModuleAutoload(array &$config): void
    {
        $modules = $this->scanModules();
        
        foreach ($modules as $moduleName => $moduleInfo) {
            // Add module namespace to PSR4
            $config->psr4[$moduleInfo['namespace']] = $moduleInfo['path'];
            
            // Load module's autoload configuration if it exists
            if ($moduleInfo['hasAutoload']) {
                $autoloadPath = $moduleInfo['path'] . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Autoload.php';
                require_once $autoloadPath;
                
                // Get the module's autoload configuration class name
                $moduleAutoloadClass = $moduleInfo['namespace'] . '\\Config\\Autoload';
                if (class_exists($moduleAutoloadClass)) {
                    $moduleAutoload = new $moduleAutoloadClass();
                    
                    // Merge configurations
                    if (isset($moduleAutoload->psr4)) {
                        $config->psr4 = array_merge($config->psr4, $moduleAutoload->psr4);
                    }
                    if (isset($moduleAutoload->classmap)) {
                        $config->classmap = array_merge($config->classmap, $moduleAutoload->classmap);
                    }
                    if (isset($moduleAutoload->files)) {
                        $config->files = array_merge($config->files, $moduleAutoload->files);
                    }
                    if (isset($moduleAutoload->helpers)) {
                        $config->helpers = array_merge($config->helpers, $moduleAutoload->helpers);
                    }
                }
            }
        }
    }

    /**
     * Scan for all filter classes in modules
     */
    public function scanFilters(): array
    {
        $filters = [];
        $filterConfigs = [];

        if (!is_dir($this->modulesPath)) {
            log_message('debug', 'Modules directory not found: ' . $this->modulesPath);
            return [$filters, $filterConfigs];
        }

        $directory = new RecursiveDirectoryIterator($this->modulesPath);
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+Filter\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($regex as $file) {
            $filePath = $file[0];
            // log_message('debug', '[ModuleScanner] Found filter file: ' . $filePath);
            
            // Get relative path from app directory
            $relativePath = str_replace(APPPATH, '', $filePath);
            
            // Convert file path to namespace format
            $class = str_replace('/', '\\', 'App\\' . substr($relativePath, 0, -4));
            
            if (class_exists($class)) {
                // log_message('debug', '[ModuleScanner] Loading filter class: ' . $class);
                
                // Get module name from path
                preg_match('/Modules\/([^\/]+)/', $relativePath, $matches);
                $moduleName = $matches[1] ?? '';
                
                if ($moduleName) {
                    $moduleName = strtolower($moduleName);
                    
                    // Get filter name from class name
                    $filterName = str_replace('Filter', '', basename($relativePath, '.php'));
                    // Convert to camelCase and ensure consistent casing
                    $filterName = lcfirst($filterName);
                    
                    // If filter has config method, get its configuration
                    if (method_exists($class, 'getConfig')) {
                        $config = $class::getConfig();
                        if (!empty($config)) {
                            // Use the filter name from config if provided
                            if (!empty($config['aliases'])) {
                                $configAliases = array_keys($config['aliases']);
                                if (!empty($configAliases)) {
                                    $filterName = $configAliases[0];
                                }
                            }
                            $filterConfigs[$filterName] = $config;
                            // log_message('debug', "[ModuleScanner] Loaded config for filter {$filterName}: " . print_r($config, true));
                        }
                    }
                    
                    // Add to filters array using consistent name
                    $filters[$filterName] = $class;
                    // log_message('debug', "[ModuleScanner] Registered filter: {$filterName} => {$class}");
                }
            } else {
                log_message('error', '[ModuleScanner] Filter class not found: ' . $class);
            }
        }

        return [$filters, $filterConfigs];
    }

    /**
     * Scan for all route files in modules
     */
    public function scanRoutes(): array
    {
        $routeFiles = [];

        if (!is_dir($this->modulesPath)) {
            return $routeFiles;
        }

        $directory = new RecursiveDirectoryIterator($this->modulesPath);
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+Routes\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($regex as $file) {
            $routeFiles[] = $file[0];
        }

        return $routeFiles;
    }

    /**
     * Load module routes
     */
    public function loadRoutes($routes): void
    {
        $routeFiles = $this->scanRoutes();
        
        foreach ($routeFiles as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }

    /**
     * Load module filters
     */
    public function loadFilters(BaseConfig $config): void
    {
        try {
            [$filters, $filterConfigs] = $this->scanFilters();
            
            // Add filter configurations
            foreach ($filterConfigs as $name => $filterConfig) {
                // Add to aliases if specified
                if (!empty($filterConfig['aliases'])) {
                    foreach ($filterConfig['aliases'] as $alias => $class) {
                        $config->aliases[$alias] = $class;
                        // log_message('debug', "[ModuleScanner] Added filter alias from config: {$alias} => {$class}");
                    }
                }
                
                // Add to globals if specified
                if (!empty($filterConfig['globals'])) {
                    foreach ($filterConfig['globals'] as $timing => $filters) {
                        if (!isset($config->globals[$timing])) {
                            $config->globals[$timing] = [];
                        }
                        foreach ($filters as $filter => $settings) {
                            if (!isset($config->globals[$timing][$filter])) {
                                $config->globals[$timing][$filter] = $settings;
                            } else if (isset($settings['except']) && isset($config->globals[$timing][$filter]['except'])) {
                                // Merge except arrays if both exist
                                $config->globals[$timing][$filter]['except'] = array_merge(
                                    $config->globals[$timing][$filter]['except'],
                                    $settings['except']
                                );
                                // Remove duplicates
                                $config->globals[$timing][$filter]['except'] = array_unique(
                                    $config->globals[$timing][$filter]['except']
                                );
                            }
                        }
                        // log_message('debug', "[ModuleScanner] Added global filters for timing {$timing}: " . print_r($filters, true));
                    }
                }
                
                // Add to methods if specified
                if (!empty($filterConfig['methods'])) {
                    foreach ($filterConfig['methods'] as $method => $filters) {
                        if (!isset($config->methods[$method])) {
                            $config->methods[$method] = [];
                        }
                        $config->methods[$method] = array_merge($config->methods[$method], $filters);
                        // log_message('debug', "[ModuleScanner] Added method filters for {$method}: " . print_r($filters, true));
                    }
                }
                
                // Add to filters if specified
                if (!empty($filterConfig['filters'])) {
                    foreach ($filterConfig['filters'] as $filter => $rules) {
                        $config->filters[$filter] = $rules;
                        // log_message('debug', "[ModuleScanner] Added filter rules for {$filter}: " . print_r($rules, true));
                    }
                }
            }

            // log_message('info', '[ModuleScanner] Module filters loaded successfully');
            // log_message('debug', '[ModuleScanner] Final filter aliases: ' . print_r($config->aliases, true));
            // log_message('debug', '[ModuleScanner] Final filter rules: ' . print_r($config->filters, true));
            // log_message('debug', '[ModuleScanner] Final global filters: ' . print_r($config->globals, true));
            
        } catch (\Exception $e) {
            log_message('error', '[ModuleScanner] Error loading module filters: ' . $e->getMessage());
            log_message('error', '[ModuleScanner] Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}
