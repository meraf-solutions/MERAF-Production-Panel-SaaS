<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use App\Models\UserModel;

class AuthController extends Controller
{
    protected $myConfig;
    protected $userID;
    protected $userDataPath;
    protected $UserModel;

    public function __construct()
    {
        // Load security helper for enhanced security functions
        helper('security');

        $this->userID = auth()->id() ?? NULL;
        $this->myConfig = getMyConfig('', $this->userID);
        $this->userDataPath = USER_DATA_PATH . $this->userID . '/';
        $this->UserModel = new UserModel();
    }

    public function profile()
    {
        $user = auth()->user();
        
        $apiKey = $this->UserModel->getUserApiKey($this->userID);

        $data = [
            'user' => $user,
            'userConfig' => $this->myConfig,
            'apiKey' => $apiKey,
        ];

        return view('auth/profile', $data);
    }  

    public function generateUserApiKey()
    {
        $apiKey = $this->UserModel->generateUserApiKey($this->userID);

        if ($apiKey) {
            return redirect()->to('/profile')->with('message', 'API key generated successfully.');
        } else {
            return redirect()->to('/profile')->with('error', 'Failed to generate API key.');
        }
    }

    public function revokeUserApiKey()
    {
        $result = $this->UserModel->revokeUserApiKey($this->userID);

        if ($result) {
            return redirect()->to('/profile')->with('message', 'API key revoked successfully.');
        } else {
            return redirect()->to('/profile')->with('error', 'Failed to revoke API key.');
        }
    }

    public function regenerateUserApiKey()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
        }
    
        $this->UserModel->revokeUserApiKey($this->userID);
        $apiKey = $this->UserModel->generateUserApiKey($this->userID);
    
        if ($apiKey) {
            $response = [
                'success' => true,
                'status' => 1,
                'msg' => lang('Notifications.success_regen_user_api_key'),
                'user_api_key' => $apiKey
            ];
        } else {
            $response = [
                'success' => false,
                'status' => 0,
                'msg' => lang('Notifications.failed_regen_user_api_key'),
                'user_api_key' => ''
            ];
        }
    
        return $this->response->setJSON($response); // Return JSON response
    }    

    public function login()
    {
        // Set the locale dynamically based on user preference
        setMyLocale();

        $request = service('request');
        $auth = service('auth');

        // Check if user is already logged in
        if ($auth->loggedIn()) {
            return redirect()->to('/');
        }

        // Enhanced security logging
        $ipAddress = $request->getIPAddress();
        $userAgent = $request->getUserAgent()->getAgentString();

        // IMPORTANT: Only validate reCAPTCHA if it's explicitly enabled
        // Check both the enabled flag AND that the keys are configured
        $reCAPTCHA_enabled = !empty($this->myConfig['reCAPTCHA_enabled']) &&
                            !empty($this->myConfig['reCAPTCHA_Site_Key']) &&
                            !empty($this->myConfig['reCAPTCHA_Secret_Key']);

        if ($reCAPTCHA_enabled) {
            // Validate Google reCAPTCHA with secure decryption
            $recaptchaResponse = $request->getPost('g-recaptcha-response');

            if (empty($recaptchaResponse)) {
                log_message('warning', "reCAPTCHA response missing from IP: {$ipAddress}");
                return redirect()->back()->withInput()->with('error', lang('Notifications.reCAPTCHA_verification_failed'));
            }

            // Decrypt reCAPTCHA secret key if needed
            $secretKey = decrypt_secret_key($this->myConfig['reCAPTCHA_Secret_Key'], $this->userID);

            // Validate reCAPTCHA with enhanced error handling
            $verifyResponse = $this->validateRecaptcha($recaptchaResponse, $secretKey);

            if (!$verifyResponse['success']) {
                log_message('warning', "reCAPTCHA verification failed from IP: {$ipAddress}, Reason: {$verifyResponse['error']}");
                return redirect()->back()->withInput()->with('error', lang('Notifications.reCAPTCHA_verification_failed'));
            }
        }

        // Enhanced input validation and sanitization
        $email = sanitize_input($request->getPost('email'));
        $password = $request->getPost('password');

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            log_message('warning', "Invalid email format in login attempt from IP: {$ipAddress}");
            return redirect()->back()->withInput()->with('error', 'Invalid email format.');
        }

        // Validate password strength
        if (strlen($password) < 8) {
            log_message('warning', "Weak password in login attempt from IP: {$ipAddress}, Email: {$email}");
            return redirect()->back()->withInput()->with('error', 'Password must be at least 8 characters.');
        }

        $credentials = ['email' => $email, 'password' => $password];

        // Attempt authentication with enhanced logging
        $result = $auth->attempt($credentials, $this->request->getPost('remember'));

        if (!$result->isOK()) {
            log_message('warning', "Failed login attempt from IP: {$ipAddress}, Email: {$email}, Reason: {$result->reason()}");
            return redirect()->back()->withInput()->with('errors', $result->reason());
        }

        // Log successful login
        $user = auth()->user();
        log_message('info', "Successful login for user ID: {$user->id}, Email: {$email}, IP: {$ipAddress}");

        // Redirect to the intended page, or dashboard after login
        return redirect()->to('/');
    }

    /**
     * Validate reCAPTCHA response with enhanced security
     *
     * @param string $recaptchaResponse The reCAPTCHA response token
     * @param string $secretKey The reCAPTCHA secret key
     * @return array Validation result
     */
    private function validateRecaptcha(string $recaptchaResponse, string $secretKey): array
    {
        try {
            $postData = http_build_query([
                'secret' => $secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => $this->request->getIPAddress()
            ]);

            $contextOptions = [
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => $postData,
                    'timeout' => 10
                ]
            ];

            $context = stream_context_create($contextOptions);
            $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);

            if ($verifyResponse === false) {
                return ['success' => false, 'error' => 'Failed to contact reCAPTCHA service'];
            }

            $responseData = json_decode($verifyResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['success' => false, 'error' => 'Invalid reCAPTCHA response format'];
            }

            return [
                'success' => $responseData['success'] ?? false,
                'score' => $responseData['score'] ?? 0,
                'error' => $responseData['error-codes'][0] ?? 'Unknown error'
            ];

        } catch (Exception $e) {
            log_message('error', 'reCAPTCHA validation error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'reCAPTCHA service error'];
        }
    }

    public function register()
    {
        // Set the locale dynamically based on user preference
        setMyLocale();

        $request = service('request');
        $users = auth()->getProvider();

        // Enhanced security logging
        $ipAddress = $request->getIPAddress();
        $userAgent = $request->getUserAgent()->getAgentString();

        // Check if registration is enabled
        if (!setting('Auth.allowRegistration')) {
            log_message('warning', "Registration attempt while disabled from IP: {$ipAddress}");
            return redirect()->back()->withInput()->with('error', lang('Auth.registerDisabled'));
        }

        // IMPORTANT: Only validate reCAPTCHA if it's explicitly enabled
        // Check both the enabled flag AND that the keys are configured
        $reCAPTCHA_enabled = !empty($this->myConfig['reCAPTCHA_enabled']) &&
                            !empty($this->myConfig['reCAPTCHA_Site_Key']) &&
                            !empty($this->myConfig['reCAPTCHA_Secret_Key']);

        if ($reCAPTCHA_enabled) {
            // Validate Google reCAPTCHA
            $recaptchaResponse = $request->getPost('g-recaptcha-response');

            if (empty($recaptchaResponse)) {
                log_message('warning', "reCAPTCHA response missing in registration from IP: {$ipAddress}");
                return redirect()->back()->withInput()->with('error', lang('Notifications.reCAPTCHA_verification_failed'));
            }

            // Decrypt reCAPTCHA secret key if needed
            $secretKey = decrypt_secret_key($this->myConfig['reCAPTCHA_Secret_Key'], $this->userID);

            // Validate reCAPTCHA with enhanced error handling
            $verifyResponse = $this->validateRecaptcha($recaptchaResponse, $secretKey);

            if (!$verifyResponse['success']) {
                log_message('warning', "reCAPTCHA verification failed in registration from IP: {$ipAddress}, Reason: {$verifyResponse['error']}");
                return redirect()->back()->withInput()->with('error', lang('Notifications.reCAPTCHA_verification_failed'));
            }
        }

        // Enhanced input validation with sanitization
        $formData = [
            'username' => sanitize_input($request->getPost('username')),
            'email' => sanitize_input($request->getPost('email')),
            'first_name' => sanitize_input($request->getPost('first_name')),
            'last_name' => sanitize_input($request->getPost('last_name')),
            'password' => $request->getPost('password'),
            'password_confirm' => $request->getPost('password_confirm')
        ];

        // Comprehensive validation rules
        $rules = [
            'username' => 'required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|strong_password',
            'password_confirm' => 'required|matches[password]',
            'first_name' => 'required|alpha_numeric_space|min_length[2]|max_length[50]',
            'last_name' => 'required|alpha_numeric_space|min_length[2]|max_length[50]',
        ];

        // Additional security validations
        if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            log_message('warning', "Invalid email format in registration from IP: {$ipAddress}");
            return redirect()->back()->withInput()->with('error', 'Invalid email format.');
        }

        if (strlen($formData['password']) < 8) {
            log_message('warning', "Weak password in registration from IP: {$ipAddress}");
            return redirect()->back()->withInput()->with('error', 'Password must be at least 8 characters.');
        }

        // Validate with CodeIgniter rules
        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            log_message('warning', "Registration validation failed from IP: {$ipAddress}, Errors: " . json_encode($errors));
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        try {
            // Create the user with sanitized data
            $user = new User([
                'username' => $formData['username'],
                'email' => $formData['email'],
                'password' => $formData['password'],
                'first_name' => $formData['first_name'],
                'last_name' => $formData['last_name'],
            ]);

            // Save the user
            if (!$users->save($user)) {
                log_message('error', "Failed to save user during registration from IP: {$ipAddress}, Email: {$formData['email']}");
                return redirect()->back()->withInput()->with('errors', $users->errors());
            }

            // Get the complete user object with ID
            $user = $users->findById($users->getInsertID());

            // Add to default group
            $users->addToDefaultGroup($user);

            // Log successful registration
            log_message('info', "Successful registration for user ID: {$user->id}, Email: {$formData['email']}, IP: {$ipAddress}");

            // Success!
            return redirect()->to(url_to('login'))->with('message', lang('Auth.registrationSuccess'));

        } catch (Exception $e) {
            log_message('error', "Registration error from IP: {$ipAddress}, Error: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Registration failed. Please try again.');
        }
    }    
}
