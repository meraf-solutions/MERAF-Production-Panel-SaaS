<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Trim implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Recursive trim function
        $trimRecursive = function ($data) use (&$trimRecursive) {
            if (is_array($data)) {
                return array_map($trimRecursive, $data);
            }
            return is_string($data) ? trim($data) : $data;
        };

        // Trim POST
        $post = $request->getPost();
        if (!empty($post)) {
            $request->setGlobal('post', $trimRecursive($post));
        }

        // Trim GET
        $get = $request->getGet();
        if (!empty($get)) {
            $request->setGlobal('get', $trimRecursive($get));
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do here
    }
}
