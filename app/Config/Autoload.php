<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

/**
 * -------------------------------------------------------------------
 * AUTOLOADER CONFIGURATION
 * -------------------------------------------------------------------
 *
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 *
 * NOTE: If you use an identical key in $psr4 or $classmap, then
 *       the values in this file will overwrite the framework's values.
 *
 * NOTE: This class is required prior to Autoloader instantiation,
 *       and does not extend BaseConfig.
 */
class Autoload extends AutoloadConfig
{
    /**
     * -------------------------------------------------------------------
     * Namespaces
     * -------------------------------------------------------------------
     * This maps the locations of any namespaces in your application to
     * their location on the file system. These are used by the autoloader
     * to locate files the first time they have been instantiated.
     *
     * The 'Config' (APPPATH . 'Config') and 'CodeIgniter' (SYSTEMPATH) are
     * already mapped for you.
     *
     * You may change the name of the 'App' namespace if you wish,
     * but this should be done prior to creating any namespaced classes,
     * else you will need to modify all of those classes for this to work.
     *
     * @var array<string, list<string>|string>
     */
    public $psr4 = [
        APP_NAMESPACE => APPPATH, // For custom app namespace
        'Config'      => APPPATH . 'Config',
        'App\Helpers' => APPPATH . 'Helpers',
        'CodeIgniter\Shield' => ROOTPATH . 'vendor/codeigniter4/shield/src'
    ];

    /**
     * -------------------------------------------------------------------
     * Class Map
     * -------------------------------------------------------------------
     * The class map provides a map of class names and their exact
     * location on the drive. Classes loaded in this manner will have
     * slightly faster performance because they will not have to be
     * searched for within one or more directories as they would if they
     * were being autoloaded through a namespace.
     *
     * Prototype:
     *   $classmap = [
     *       'MyClass'   => '/path/to/class/file.php'
     *   ];
     *
     * @var array<string, string>
     */
    public $classmap = [];

    /**
     * -------------------------------------------------------------------
     * Files
     * -------------------------------------------------------------------
     * The files array provides a list of paths to __non-class__ files
     * that will be autoloaded. This can be useful for bootstrap operations
     * or for loading functions.
     *
     * Prototype:
     *   $files = [
     *       '/path/to/my/file.php',
     *   ];
     *
     * @var list<string>
     */
    public $files = [];

    /**
     * -------------------------------------------------------------------
     * Helpers
     * -------------------------------------------------------------------
     * Prototype:
     *   $helpers = [
     *       'form',
     *   ];
     *
     * @var list<string>
     */
    public $helpers = [
        'myconfig',
        'auth',
        'setting',
        'date',
        'locale',
        'product',
        'license',
        'subscription',
        'emailsubscription',
        'emailtemplate',
        'notification',
        'ModuleLoader'
    ];

    /**
     * Module language paths to be registered with the Language class
     * This will be processed after the system is fully bootstrapped
     * 
     * @var array
     */
    public $moduleLanguagePaths = [];
                
    public function __construct()
    {
        parent::__construct();

        // Scan and load modules
        $this->loadModules();

        /**
         * -------------------------------------------------------------------
         * Autoload Packages
         * -------------------------------------------------------------------
         * Prototype:
         *
         *   $psr4 = [
         *       'CodeIgniter' => SYSTEMPATH,
         *       'App'         => APPPATH
         *   ];
         *
         * @var array<string, string>
         */
        $this->psr4 = array_merge($this->psr4, [
            // Add any additional namespaces for packages here
        ]);

        /**
         * -------------------------------------------------------------------
         * Class Map
         * -------------------------------------------------------------------
         * The class map provides a map of class names and their exact
         * location on the drive. Classes loaded in this manner will have
         * slightly faster performance because they will not have to be
         * searched for within one or more directories as they would if they
         * were being autoloaded through a namespace.
         *
         * Prototype:
         *
         *   $classmap = [
         *       'MyClass'   => '/path/to/class/file.php'
         *   ];
         *
         * @var array<string, string>
         */
        $this->classmap = array_merge($this->classmap, [
            // Add any additional class mappings here
        ]);

        /**
         * -------------------------------------------------------------------
         * Files
         * -------------------------------------------------------------------
         * The files array provides a list of paths to __non-class__ files
         * that will be autoloaded. This can be useful for bootstrap operations
         * or for loading functions.
         *
         * Prototype:
         *
         *   $files = [
         *       '/path/to/my/file.php',
         *   ];
         *
         * @var array<int, string>
         */
        $this->files = array_merge($this->files, [
            // Add any additional files to autoload here
        ]);

        /**
         * -------------------------------------------------------------------
         * Helpers
         * -------------------------------------------------------------------
         * Prototype:
         *
         *   $helpers = [
         *       'form',
         *   ];
         *
         * @var array<int, string>
         */
        $this->helpers = array_merge($this->helpers, [
            // Add any additional helpers to autoload here
        ]);
    }

    /**
     * Scan and load modules
     */
    private function loadModules(): void
    {
        $modulesPath = APPPATH . 'Modules';

        if (!is_dir($modulesPath)) {
            return;
        }

        // Get immediate subdirectories in the Modules folder
        $items = scandir($modulesPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $modulePath = $modulesPath . DIRECTORY_SEPARATOR . $item;
            if (is_dir($modulePath)) {
                // Add module namespace to PSR4
                $namespace = 'App\\Modules\\' . $item;
                $this->psr4[$namespace] = $modulePath;

                // Store module language paths for later registration
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
                            if (!isset($this->moduleLanguagePaths[$lang])) {
                                $this->moduleLanguagePaths[$lang] = [];
                            }
                            $this->moduleLanguagePaths[$lang][] = $fullLangPath;
                        }
                    }
                }

                // Check for module's autoload configuration
                $autoloadPath = $modulePath . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Autoload.php';
                if (file_exists($autoloadPath)) {
                    require_once $autoloadPath;
                    
                    // Get the module's autoload configuration class name
                    $moduleAutoloadClass = $namespace . '\\Config\\Autoload';
                    if (class_exists($moduleAutoloadClass)) {
                        $moduleAutoload = new $moduleAutoloadClass();
                        
                        // Merge configurations
                        if (isset($moduleAutoload->psr4)) {
                            $this->psr4 = array_merge($this->psr4, $moduleAutoload->psr4);
                        }
                        if (isset($moduleAutoload->classmap)) {
                            $this->classmap = array_merge($this->classmap, $moduleAutoload->classmap);
                        }
                        if (isset($moduleAutoload->files)) {
                            $this->files = array_merge($this->files, $moduleAutoload->files);
                        }
                        if (isset($moduleAutoload->helpers)) {
                            $this->helpers = array_merge($this->helpers, $moduleAutoload->helpers);
                        }
                    }
                }
            }
        }
    }
}
