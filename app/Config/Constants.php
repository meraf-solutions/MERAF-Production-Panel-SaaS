<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2_592_000);
defined('YEAR')   || define('YEAR', 31_536_000);
defined('DECADE') || define('DECADE', 315_360_000);

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0);        // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1);          // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3);         // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4);   // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5);  // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7);     // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8);       // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9);      // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125);    // highest automatically-assigned error code

/*
 | --------------------------------------------------------------------------
 | Custom API Return Codes
 | --------------------------------------------------------------------------
 |
 */
define('CREATE_FAILED', 10);
define('REACHED_MAX_DOMAINS', 50);
define('LICENSE_INVALID', 60);
define('REACHED_MAX_DEVICES', 120);
define('LICENSE_EXIST', 200);
define('KEY_DEACTIVATE_SUCCESS', 340);
define('LICENSE_CREATED', 400);
define('FORBIDDEN_ERROR', 403);
define('QUERY_DOMAINorDEVICE_NOT_EXISTING', 404);
define('METHOD_NOT_ALLOWED', 405);
define('QUERY_NOT_FOUND', 500);
define('QUERY_ERROR', 503);
define('RETURNED_EMPTY', 204);
define('KEY_UPDATE_FAILED', 220);
define('KEY_UPDATE_SUCCESS', 240);
define('TOO_MANY_REQUESTS', 429);
define('CREATE_KEY_INVALID', 100); // not used
define('DOMAIN_ALREADY_INACTIVE', 80); // not used
define('DOMAIN_MISSING', 70); // not used
define('KEY_CANCELLED', 130); // not used
define('KEY_CANCELLED_FAILED', 140); // not used
define('KEY_DEACTIVATE_DOMAIN_SUCCESS', 360); // not used
define('KEY_DELETE_FAILED', 300); // not used
define('KEY_DELETE_SUCCESS', 320); // not used
define('KEY_DELETED', 130); // not used
define('LICENSE_ACTIVATED', 380); // not used
define('LICENSE_BLOCKED', 20); // not used
define('LICENSE_EXPIRED', 30); // not used
define('LICENSE_IN_USE', 40); // not used
define('LICENSE_VALID', 65); // not used
define('MISSING_KEY_DELETE_FAILED', 280); // not used
define('MISSING_KEY_UPDATE_FAILED', 260); // not used
define('VERIFY_KEY_INVALID', 90); // not used

/*
 | --------------------------------------------------------------------------
 | Other Custom Constants
 | --------------------------------------------------------------------------
 |
 */
defined('USER_DATA_PATH') || define('USER_DATA_PATH', ROOTPATH . 'user-data' . DIRECTORY_SEPARATOR);
defined('MODULESPATH') || define('MODULESPATH', APPPATH . 'Modules' . DIRECTORY_SEPARATOR);