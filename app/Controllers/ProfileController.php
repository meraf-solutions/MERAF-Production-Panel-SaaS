<?php

namespace App\Controllers; 

use CodeIgniter\I18n\Time;
use CodeIgniter\Controller;
  
class ProfileController extends BaseController
{
    protected $userID;
    protected $myConfig;

    public function __construct()
	{
        // Get the current user's ID
        $this->userID = auth()->id() ?? NULL;

        $this->myConfig = getMyConfig('', $this->userID);

		// Set the locale dynamically based on user preference
		setMyLocale();     
    }

    protected function authenticationCheck(string $password, ?string &$error = null): bool
    {
        $result = auth()->check([
            'email'    => auth()->user()->email,
            'password' => $password,
        ]);
    
        if( !$result->isOK() ) {
            // Send back the error message
            $error = lang('Notifications.authentication_failed');
    
            return false;
        }
    
        return true;
    }
    
    public function change_password_action()
    {
        if (NULL === auth()->id()) {
            return redirect()->to('login');
        }

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}

        // Second, make sure the initial and confirmation new password are the same
        $userID = auth()->user()->id;
        $oldPassword = trim($this->request->getPost('oldPassword'));
        $initialPassword = trim($this->request->getPost('initial_newPassword'));
        $confirmedPassword = trim($this->request->getPost('newPassword'));
        
        // First, check if the submitted old password is correct
        if (!$this->authenticationCheck($oldPassword)) {
            // Send back the error message
            $response = [
                'success' => false,
                'status' => 0,
                'msg' => lang('Notifications.invalid_old_password'),
            ];

            return $this->response->setJSON($response);
        }
        
        if ($oldPassword === $initialPassword) {
            // Send back the error message
            $response = [
                'success' => false,
                'status' => 0,
                'msg' => lang('Notifications.same_new_old_password'),
            ];
            
            return $this->response->setJSON($response);
        }

        // Set the validation rules and messages
        // Validate form data
        $validationRules = [
            'oldPassword'           => 'required',
            'initial_newPassword'   => 'required|max_byte[72]|strong_password[]',
            'newPassword'           => 'required|matches[initial_newPassword]',
        ];

        // Custom error messages for validation rules
        $validationMessages = [
            'oldPassword' => [
                'required' => lang('Notifications.old_pass_required'),
            ],
            'initial_newPassword' => [
                'required' => lang('Notifications.new_pass_required'),
                'max_byte' => lang('Notifications.too_long_new_pass')
            ],
            'newPassword' => [
                'required' => lang('Notifications.pass_confirmation_required'),
                'matches' => lang('Notifications.pass_doesnt_match')
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            // Get validation errors
            $errors = $this->validator->getErrors();

            $response = [
                'success' => false,
                'status' => 0,
                'msg' => $errors, // Send validation errors
            ];

            return $this->response->setJSON($response);
        } else {

            if ($initialPassword === $confirmedPassword) {

                // Initiate changing of password
                $users = auth()->getProvider();

                $user = $users->findById($userID);

                $user->fill([
                    'password' => $initialPassword
                ]);

                try {
                    $users->save($user);

                    $response = [
                        'success' => true,
                        'status' => 1,
                        'msg' => lang('Notifications.pass_change_success'), // Utilize language file for success message
                    ];

                } catch (\Exception $e) {
                    $logger = \Config\Services::logger();                        
                    $logger->error(lang('Notifications.pass_change_failed', ['errorMessage' => $e->getMessage()]));

                    $response = [
                        'success' => false,
                        'status'  => 0,
                        'msg'     => lang('Notifications.unable_save_new_pass'),
                    ];
                }

                return $this->response->setJSON($response);
            }
        }
    }
    
    public function upload_new_avatar_action()
    {
        if (null === auth()->id()) {
            return redirect()->to('login');
        }

		if ($this->request->getMethod() !== 'POST') {
			return $this->response->setStatusCode(405)->setBody(lang('Notifications.Method_Not_Allowed'));
		}        

        // Set the response messages
        $msgResponse_validationError = lang('Notifications.error_submitted_details');
        $msgResponse_uploadError = lang('Notifications.error_uploading_avatar');
        $msgResponse_dataError = lang('Notifications.error_updating_user_data');
        $msgResponse_invalidUser = lang('Notifications.error_wrong_pass');
        $msgResponse_success = lang('Notifications.success_avatar_upload');

        // First, check if the current submitter exists in the database
        $enteredPassword = trim($this->request->getPost('profilePassword'));

        if (!$this->authenticationCheck($enteredPassword)) {
            // Send back the error message
            $response = [
                'success' => false,
                'status' => 0,
                'msg' => $msgResponse_invalidUser,
            ];

            return $this->response->setJSON($response);
        }

        // Validate form data
        $validationRules = [
            'tmpAvatar' => 'uploaded[tmpAvatar]|ext_in[tmpAvatar,jpg,jpeg,png]', // Only *.jpg or *.jpeg file
        ];

        // Set custom error messages
        $validationMessages = [
            'tmpAvatar' => [
                'uploaded' => lang('Notifications.choose_avatar'),
                'ext_in' => lang('Notifications.choose_correct_avatar_format'),
            ],
        ];

        // Run validation
        if (!$this->validate($validationRules, $validationMessages)) {
            $response = [
                'success' => false,
                'status' => 0,
                'msg' => $msgResponse_validationError,
            ];

            return $this->response->setJSON($response);
        } else {
            // Set the path where to save the uploaded file
            $uploadedFilePath = WRITEPATH . 'uploads/user-avatar/';

            // Form data is valid, proceed with further processing
            $tmpAvatar = $this->request->getFile('tmpAvatar');

            // Get the dimensions of the uploaded image
            $imageSize = getimagesize($tmpAvatar->getPathname());
            $imageWidth = $imageSize[0];
            $imageHeight = $imageSize[1];

            // Check if the image dimensions are within 400x400
            if ($imageWidth > 400 || $imageHeight > 400) {
                $response = [
                    'success' => false,
                    'status' => 0,
                    'msg' => lang('Notifications.choose_correct_avatar_pixel'),
                ];

                return $this->response->setJSON($response);
            }

            // Check if there's an existing value of the avatar from the database
            if (auth()->user()->avatar) {
                $existingAvatar = $uploadedFilePath . auth()->user()->avatar;

                if (file_exists($existingAvatar)) {
                    unlink($existingAvatar);
                }
            }

            // Generate avatar file name of $tmpAvatar
            $fileName = sha1(auth()->user()->getEmail() . Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s')) . '.' . $tmpAvatar->getExtension();

            if ($tmpAvatar->move($uploadedFilePath, $fileName, true)) { // Overwrite existing file if exists
                // Initiate changing of password
                $users = auth()->getProvider();

                $user = $users->findById(auth()->user()->id);

                $user->fill([
                    'avatar' => $fileName
                ]);

                try {
                    $users->save($user);

                    $response = [
                        'success' => true,
                        'status' => 1,
                        'msg' => $msgResponse_success,
                        'updatedAvatar' => $fileName
                    ];

                } catch (\Exception $e) {
                    $logger = \Config\Services::logger();                        
                    $logger->error(lang('Notifications.failed_to_save_in_db', ['errorMessage' => $e->getMessage()]));

                    $response = [
                        'success' => false,
                        'status' => 0,
                        'msg' => $msgResponse_dataError,
                    ];
                }

            } else {

                $response = [
                    'success' => false,
                    'status' => 0,
                    'msg' => $msgResponse_uploadError,
                ];

            }

            return $this->response->setJSON($response);
        }
    }    
}