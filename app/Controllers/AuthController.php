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
        $auth = service('auth'); // Get the auth service

        // Make sure reCAPTCHA Secret Key is set
        if(!$this->myConfig['reCAPTCHA_Secret_Key']) {
            return redirect()->back()->withInput()->with('errors', lang('Notifications.reCAPTCHA_secret_not_set')); 
        }

        // Validate Google reCAPTCHA
        $recaptchaResponse = $request->getPost('g-recaptcha-response');
        $secretKey = $this->myConfig['reCAPTCHA_Secret_Key']; // Replace with your actual reCAPTCHA secret key

        $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptchaResponse}");
        $responseData = json_decode($verifyResponse); 

        if (!$responseData->success) {
            return redirect()->back()->withInput()->with('error', lang('Notifications.reCAPTCHA_verification_failed'));
        }

        // Validate user input
        $credentials = $this->request->getPost(['email', 'password']);

        $result = $auth->attempt($credentials, $this->request->getPost('remember'));

        if (! $result->isOK()) {
            return redirect()->back()->withInput()->with('errors', $result->reason());
        }

        // Redirect to the intended page, or dashboard after login
        return redirect()->to('/');
    }

    public function register()
    {
        // Set the locale dynamically based on user preference
        setMyLocale();
        
        $request = service('request');
        $users = auth()->getProvider();

        // Make sure reCAPTCHA Secret Key is set
        if(!$this->myConfig['reCAPTCHA_Secret_Key']) {
            return redirect()->back()->withInput()->with('errors', lang('Notifications.reCAPTCHA_secret_not_set')); 
        }

        // Validate Google reCAPTCHA
        $recaptchaResponse = $request->getPost('g-recaptcha-response');
        $secretKey = $this->myConfig['reCAPTCHA_Secret_Key'];

        $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptchaResponse}");
        $responseData = json_decode($verifyResponse); 

        if (!$responseData->success) {
            return redirect()->back()->withInput()->with('error', lang('Notifications.reCAPTCHA_verification_failed'));
        }

        // Validate basic user input
        $rules = [
            'username' => 'required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|strong_password',
            'password_confirm' => 'required|matches[password]',
            'first_name' => 'required|alpha_numeric_space|min_length[2]|max_length[50]',
            'last_name' => 'required|alpha_numeric_space|min_length[2]|max_length[50]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Create the user
        $user = new User([
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
        ]);

        // Save the user
        $users->save($user);

        // To get the complete user object with ID, we need to get from the database
        $user = $users->findById($users->getInsertID());

        // Add to default group
        $users->addToDefaultGroup($user);

        // Success!
        return redirect()->to(url_to('login'))->with('message', lang('Auth.registrationSuccess'));
    }    
}
