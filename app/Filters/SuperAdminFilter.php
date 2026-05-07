<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * SuperAdminFilter — hanya mengizinkan role 'super_admin'.
 * Digunakan pada route group /superadmin/*.
 */
class SuperAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userRole = session()->get('role');

        if ($userRole !== 'super_admin') {
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
