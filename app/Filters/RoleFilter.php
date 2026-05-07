<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class RoleFilter implements FilterInterface
{
    /**
     * Memeriksa apakah role pengguna sesuai dengan role yang diizinkan.
     * Jika tidak, kembalikan respons 403 Forbidden.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $userRole = session()->get('role');

        // Jika tidak ada argumen role yang ditentukan, izinkan akses
        if (empty($arguments)) {
            return null;
        }

        // Cek apakah role user ada dalam daftar role yang diizinkan
        if (! in_array($userRole, $arguments, true)) {
            return Services::response()
                ->setStatusCode(403)
                ->setBody(view('errors/html/error_403'));
        }

        return null;
    }

    /**
     * No-op: tidak ada tindakan setelah response.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
