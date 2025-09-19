<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;
use App\Filters\Auth;
use App\Filters\GroupFilter;
use App\Filters\GuestFilter;
use App\Filters\IPBlockFilter;
use App\Filters\APIThrottle;
use App\Filters\FirstLoginFilter;
use App\Filters\Trim;
use App\Filters\NotificationReadFilter;
use App\Filters\SecurityHeaders;
use App\Services\ModuleScanner;

class Filters extends BaseFilters
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array<string, class-string|list<class-string>>
     *
     * [filter_name => classname]
     * or [filter_name => [classname1, classname2, ...]]
     */
    public array $aliases = [
        // Core Filters
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        
        // Application Filters
        'api_throttle'  => APIThrottle::class,
        'auth'          => Auth::class,
        'firstLogin'    => FirstLoginFilter::class,
        'group'         => GroupFilter::class,
        'guest'         => GuestFilter::class,
        'ipblock'       => IPBlockFilter::class,
        'trim'          => Trim::class,
        'notificationRead' => NotificationReadFilter::class,
        'security_headers' => SecurityHeaders::class,
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     */
    public array $globals = [
        'before' => [
            'firstLogin' => ['except' => ['login*', 'register*', 'logout*', 'subscription/my-subscription*']],
            'notificationRead',
            'trim',
            'ipblock',
            'session' => [
                'except' => [
                    'login*',
                    'register',
                    'auth/a/*',
                    'logout',
                    'send-email-new-license/*',
                    'download/*',
                    'products/*',
                    'projects/*',
                    'reset-own-license',
                    'reset-license/*',
                    'validate/?*',
                    'api/*',
                    'cron/*'
                ]
            ],
        ],
        'after' => [
            'toolbar',
            'security_headers',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'POST' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don't expect could bypass the filter.
     *
     * @var array<string, list<string>>
     */
    public array $methods = [
        'POST' => ['api_throttle'],
    ];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [
        'api_throttle' => ['before' => ['api/*']],
    ];

    /**
     * Constructor to load module filters
     */
    public function __construct()
    {
        parent::__construct();

        // Initialize ModuleScanner and load module filters
        try {
            $scanner = new ModuleScanner();
            $scanner->loadFilters($this);
            
            // Debug log the current filter configuration
            // log_message('debug', '[Filters] Current filter aliases: ' . print_r($this->aliases, true));
            // log_message('debug', '[Filters] Current filter rules: ' . print_r($this->filters, true));
            // log_message('debug', '[Filters] Current global filters: ' . print_r($this->globals, true));
            
        } catch (\Exception $e) {
            log_message('error', '[Filters] Failed to load module filters: ' . $e->getMessage());
            log_message('error', '[Filters] Stack trace: ' . $e->getTraceAsString());
        }
    }
}
