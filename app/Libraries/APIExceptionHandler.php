<?php

namespace App\Libraries;

use CodeIgniter\Debug\ExceptionHandlerInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

class APIExceptionHandler implements ExceptionHandlerInterface
{
    public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode
    ): void {
        // Check if this is an API request by checking the URL path
        $isAPI = strpos($request->getPath(), 'api/') === 0;

        if ($isAPI && $exception instanceof PageNotFoundException) {
            $response->setJSON([
                'result' => 'error',
                'messages' => 'The requested resource was not found',
                'error_code' => QUERY_NOT_FOUND
            ])->setStatusCode(QUERY_NOT_FOUND);
            
            // Send the response
            $response->send();
            exit($exitCode);
        }

        // For non-API requests or other exceptions, use the default handler
        (new \CodeIgniter\Debug\ExceptionHandler(config('Exceptions')))
            ->handle($exception, $request, $response, $statusCode, $exitCode);
    }
}