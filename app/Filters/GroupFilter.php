<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class GroupFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        // Get the authenticated user
        $user = auth()->user();

        // Check if user belongs to at least one of the required groups
        foreach ($arguments as $group) {
            if ($user->inGroup($group)) {
                return;
            }
        }

        // If we're still here, user doesn't belong to any required group
        return redirect()->to('forbidden')->with('error', lang('Pages.forbidden_error_msg'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
