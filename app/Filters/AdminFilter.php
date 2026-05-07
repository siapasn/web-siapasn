<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * AdminFilter — hanya mengizinkan role 'admin' dan 'super_admin'.
 * Digunakan pada route group /admin/*.
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userRole = session()->get('role');

        if (! in_array($userRole, ['admin', 'super_admin'], true)) {
            return Services::response()
                ->setStatusCode(403)
                ->setBody(view('errors/html/error_403'));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
