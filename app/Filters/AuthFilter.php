<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Memeriksa apakah pengguna sudah terautentikasi.
     * Jika tidak, redirect ke halaman login.
     * Jika sesi tidak aktif selama 60 menit, hancurkan sesi dan redirect ke login.
     * Jika akun dinonaktifkan (is_active = 0), hancurkan sesi dan redirect ke login.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Cek apakah user sudah login
        if (! $session->get('user_id')) {
            return redirect()->to('/login');
        }

        // Cek timeout sesi (60 menit = 3600 detik)
        $lastActivity = $session->get('last_activity');
        if ($lastActivity !== null && (time() - $lastActivity) > 3600) {
            $session->destroy();
            return redirect()->to('/login')->with('error', 'Sesi Anda telah berakhir');
        }

        // Cek apakah akun masih aktif di database
        $user = (new UserModel())->find($session->get('user_id'));
        if (! $user || (int) $user['is_active'] === 0) {
            $session->destroy();
            return redirect()->to('/login')->with('error', 'Akun Anda telah dinonaktifkan');
        }

        // Cek single session — jika session_token tidak cocok, akun dipakai di device lain
        $sessionTokenInSession = $session->get('session_token');
        $sessionTokenInDb      = $user['session_token'] ?? null;

        if ($sessionTokenInSession && $sessionTokenInDb && $sessionTokenInSession !== $sessionTokenInDb) {
            $session->destroy();
            return redirect()->to('/login')
                ->with('error', 'Sesi Anda berakhir karena akun ini login di perangkat lain.');
        }

        // Perbarui last_activity pada setiap request
        $session->set('last_activity', time());

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
