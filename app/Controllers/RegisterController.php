<?php

namespace App\Controllers;

use CodeIgniter\Shield\Controllers\RegisterController as ShieldRegisterController;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Entities\User;

class RegisterController extends ShieldRegisterController
{
    public function register()
    {
        // Check if registration is allowed
        if (! setting('Auth.allowRegistration')) {
            return redirect()->back()->withInput()->with('error', lang('Auth.registerDisabled'));
        }

        return $this->view(setting('Auth.views')['register']);
    }

    public function registerAction(): RedirectResponse
    {
        // Check if registration is allowed
        if (! setting('Auth.allowRegistration')) {
            return redirect()->back()->withInput()->with('error', lang('Auth.registerDisabled'));
        }

        $users = model(UserModel::class);

        // Validate basics first since some password rules rely on these fields
        $rules = [
            'username' => 'required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[auth_identities.secret]',
            'first_name' => 'required|alpha_numeric_space|min_length[2]|max_length[50]',
            'last_name' => 'required|alpha_numeric_space|min_length[2]|max_length[50]',
            'password'     => 'required|strong_password',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Save the user
        $allowedPostFields = [
            'username',
            'email',
            'password',
            'first_name',
            'last_name'
        ];
        $user = $this->getUserEntity();
        $user->fill($this->request->getPost($allowedPostFields));

        // Ensure default group gets assigned if set
        if (! empty(setting('Auth.defaultUserGroup'))) {
            $users = $users->withGroup(setting('Auth.defaultUserGroup'));
        }

        if (! $users->save($user)) {
            return redirect()->back()->withInput()->with('errors', $users->errors());
        }

        // Success!
        return redirect()->route('login')->with('message', lang('Auth.registerSuccess'));
    }

    protected function getUserEntity(): User
    {
        $user = new User();
        $user->first_name = $this->request->getPost('first_name');
        $user->last_name = $this->request->getPost('last_name');
        return $user;
    }
}
