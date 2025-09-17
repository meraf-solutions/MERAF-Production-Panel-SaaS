<?php

namespace App\Filters;

use CodeIgniter\I18n\Time;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class FirstLoginFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Get the current URI path using the proper method
        $currentPath = $request->getUri()->getPath();

        // Skip if not logged in
        if (!auth()->loggedIn()) {
            return;
        }
        
        $user = auth()->user();
        
        if ($user->id === 1) {
            // Skip if already on the subscription page
            if (str_contains($currentPath, 'app-settings/registration')) {
                return;
            }
        }
        else {
            if (str_contains($currentPath, 'subscription/my-subscription')) {
                return;
            }
        }
        
        // Check if this is the first login (last_active is null)
        if ($user->last_active === null) {
            log_message('info', '[FirstLoginFilter] First login detected for user ID: ' . $user->id);
            
            try {
                $initializeNewUser = new \App\Libraries\InitializeNewUser();
                
                // Check if the user ID is being set properly
                log_message('debug', '[FirstLoginFilter] User ID in library: ' . ($initializeNewUser->getUserID() ?? 'NULL'));
                
                importDefaultUserSettings($user->id);
                log_message('info', '[FirstLoginFilter] User default settings import initialized');
                
                $initializeNewUser->initializeUserDirectories();
                log_message('info', '[FirstLoginFilter] User directories initialized');
                
                $initializeNewUser->initializeSecretKeys();
                log_message('info', '[FirstLoginFilter] Secret keys initialized');
                
                $initializeNewUser->initializeDefaultEmailTemplate();
                log_message('info', '[FirstLoginFilter] Default email template initialized');

                $user->fill([
                    'last_active' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s')
                ]);
                
                if($user->id === 1) {
                    return redirect()->to(base_url('app-settings/registration'));
                }
                else {
                    return redirect()->to(base_url('subscription/my-subscription'));
                }
            } catch (\Exception $e) {
                log_message('error', '[FirstLoginFilter] Error initializing user: ' . $e->getMessage());
                return redirect()->to(base_url('unavailable'));
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing after
    }
}