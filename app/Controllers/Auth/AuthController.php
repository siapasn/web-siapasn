<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Services\EmailService;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    protected UserModel $userModel;
    protected EmailService $emailService;

    public function __construct()
    {
        $this->userModel    = new UserModel();
        $this->emailService = new EmailService();
    }

    // -------------------------------------------------------------------------
    // index — redirect ke /login
    // -------------------------------------------------------------------------
    public function index(): RedirectResponse
    {
        return redirect()->to('/login');
    }

    // -------------------------------------------------------------------------
    // register — GET: tampilkan form registrasi
    // -------------------------------------------------------------------------
    public function register()
    {
        return view('auth/register');
    }

    // -------------------------------------------------------------------------
    // registerProcess — POST: proses registrasi
    // -------------------------------------------------------------------------
    public function registerProcess(): RedirectResponse
    {
        $rules = [
            'nama'             => 'required|min_length[3]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'telepon'          => 'permit_empty|max_length[20]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'nama'             => ['required' => 'Nama wajib diisi.', 'min_length' => 'Nama minimal 3 karakter.'],
            'email'            => ['required' => 'Email wajib diisi.', 'valid_email' => 'Format email tidak valid.', 'is_unique' => 'Email sudah terdaftar.'],
            'password'         => ['required' => 'Password wajib diisi.', 'min_length' => 'Password minimal 8 karakter.'],
            'password_confirm' => ['required' => 'Konfirmasi password wajib diisi.', 'matches' => 'Konfirmasi password tidak cocok.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $password = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);

        $userId = $this->userModel->insert([
            'nama'     => $this->request->getPost('nama'),
            'email'    => $this->request->getPost('email'),
            'telepon'  => $this->request->getPost('telepon'),
            'password' => $password,
            'role'     => 'user',
            'is_active' => 1,
        ]);

        // Generate email verification token
        $token = bin2hex(random_bytes(32));
        $db    = \Config\Database::connect();
        $db->table('password_resets')->insert([
            'email'      => $this->request->getPost('email'),
            'token'      => $token,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $verifyUrl = base_url('verify-email/' . $token);

        // Kirim email verifikasi — jika gagal, tetap lanjut (akun sudah dibuat)
        // User bisa minta kirim ulang atau admin bisa verifikasi manual
        try {
            $this->emailService->sendVerification(
                $this->request->getPost('email'),
                $this->request->getPost('nama'),
                $verifyUrl
            );
            $successMsg = 'Registrasi berhasil! Silakan cek email untuk verifikasi.';
        } catch (\Throwable $e) {
            log_message('error', 'Email verifikasi gagal dikirim: ' . $e->getMessage());
            $successMsg = 'Registrasi berhasil! Namun email verifikasi gagal dikirim. Silakan hubungi admin.';
        }

        return redirect()->to('/login')->with('success', $successMsg);
    }

    // -------------------------------------------------------------------------
    // login — GET: tampilkan form login
    // -------------------------------------------------------------------------
    public function login()
    {
        // Jika sudah login, redirect ke dashboard sesuai role
        if (session()->get('user_id')) {
            return $this->redirectToDashboard(session()->get('role'));
        }

        return view('auth/login');
    }

    // -------------------------------------------------------------------------
    // loginProcess — POST: proses login
    // -------------------------------------------------------------------------
    public function loginProcess(): RedirectResponse
    {
        $rules = [
            'email'    => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $user  = $this->userModel->findByEmail($email);

        if (! $user) {
            return redirect()->back()->withInput()->with('error', 'Email atau password salah.');
        }

        // Cek apakah akun terkunci
        if ($this->userModel->isLocked($user)) {
            $lockedUntil = date('d M Y H:i', strtotime($user['locked_until']));
            return redirect()->back()->withInput()
                ->with('error', "Akun Anda terkunci hingga {$lockedUntil}.");
        }

        // Verifikasi password
        if (! password_verify($this->request->getPost('password'), $user['password'])) {
            $this->userModel->incrementLoginAttempts($user['id']);

            // Ambil ulang data user untuk mendapatkan login_attempts terbaru
            $updatedUser = $this->userModel->find($user['id']);
            $attempts    = (int) ($updatedUser['login_attempts'] ?? 0);

            if ($attempts >= 5) {
                $this->userModel->lockAccount($user['id'], 15);
                return redirect()->back()->withInput()
                    ->with('error', 'Akun dikunci 15 menit karena 5x gagal login.');
            }

            return redirect()->back()->withInput()->with('error', 'Email atau password salah.');
        }

        // Login berhasil
        $this->userModel->resetLoginAttempts($user['id']);

        session()->set([
            'user_id'       => $user['id'],
            'nama'          => $user['nama'],
            'email'         => $user['email'],
            'role'          => $user['role'],
            'last_activity' => time(),
        ]);

        return $this->redirectToDashboard($user['role']);
    }

    // -------------------------------------------------------------------------
    // logout — GET: hapus sesi dan redirect ke login
    // -------------------------------------------------------------------------
    public function logout(): RedirectResponse
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda telah logout.');
    }

    // -------------------------------------------------------------------------
    // resetPassword — GET: tampilkan form permintaan reset password
    // -------------------------------------------------------------------------
    public function resetPassword()
    {
        return view('auth/reset_password');
    }

    // -------------------------------------------------------------------------
    // resetPasswordProcess — POST: kirim link reset password
    // -------------------------------------------------------------------------
    public function resetPasswordProcess(): RedirectResponse
    {
        $rules = ['email' => 'required|valid_email'];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $user  = $this->userModel->findByEmail($email);

        if (! $user) {
            return redirect()->back()->withInput()
                ->with('error', 'Email tidak ditemukan dalam sistem kami.');
        }

        $token = bin2hex(random_bytes(32));
        $db    = \Config\Database::connect();

        // Hapus token lama untuk email ini
        $db->table('password_resets')->where('email', $email)->delete();

        // Simpan token baru
        $db->table('password_resets')->insert([
            'email'      => $email,
            'token'      => $token,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $resetUrl = base_url('reset-password/' . $token);
        $this->emailService->sendPasswordReset($email, $user['nama'], $resetUrl);

        return redirect()->to('/login')
            ->with('success', 'Link reset password telah dikirim ke email Anda.');
    }

    // -------------------------------------------------------------------------
    // resetPasswordForm — GET: tampilkan form reset password baru
    // -------------------------------------------------------------------------
    public function resetPasswordForm(string $token)
    {
        $db    = \Config\Database::connect();
        $reset = $db->table('password_resets')->where('token', $token)->get()->getRowArray();

        if (! $reset) {
            return redirect()->to('/reset-password')
                ->with('error', 'Token tidak valid atau sudah kedaluwarsa.');
        }

        // Cek apakah token masih berlaku (< 60 menit)
        $createdAt = strtotime($reset['created_at']);
        if ((time() - $createdAt) > 3600) {
            return redirect()->to('/reset-password')
                ->with('error', 'Token tidak valid atau sudah kedaluwarsa.');
        }

        return view('auth/reset_password_form', ['token' => $token]);
    }

    // -------------------------------------------------------------------------
    // resetPasswordUpdate — POST: simpan password baru
    // -------------------------------------------------------------------------
    public function resetPasswordUpdate(): RedirectResponse
    {
        $rules = [
            'token'            => 'required',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'password'         => ['required' => 'Password wajib diisi.', 'min_length' => 'Password minimal 8 karakter.'],
            'password_confirm' => ['required' => 'Konfirmasi password wajib diisi.', 'matches' => 'Konfirmasi password tidak cocok.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $token = $this->request->getPost('token');
        $db    = \Config\Database::connect();
        $reset = $db->table('password_resets')->where('token', $token)->get()->getRowArray();

        if (! $reset) {
            return redirect()->to('/reset-password')
                ->with('error', 'Token tidak valid atau sudah kedaluwarsa.');
        }

        // Cek apakah token masih berlaku (< 60 menit)
        $createdAt = strtotime($reset['created_at']);
        if ((time() - $createdAt) > 3600) {
            return redirect()->to('/reset-password')
                ->with('error', 'Token tidak valid atau sudah kedaluwarsa.');
        }

        $user = $this->userModel->findByEmail($reset['email']);
        if (! $user) {
            return redirect()->to('/reset-password')
                ->with('error', 'Akun tidak ditemukan.');
        }

        // Update password
        $this->userModel->update($user['id'], [
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
        ]);

        // Hapus token
        $db->table('password_resets')->where('token', $token)->delete();

        return redirect()->to('/login')
            ->with('success', 'Password berhasil diubah. Silakan login dengan password baru Anda.');
    }

    // -------------------------------------------------------------------------
    // verifyEmail — GET: verifikasi email via token
    // -------------------------------------------------------------------------
    public function verifyEmail(string $token): RedirectResponse
    {
        $db    = \Config\Database::connect();
        $reset = $db->table('password_resets')->where('token', $token)->get()->getRowArray();

        if (! $reset) {
            return redirect()->to('/login')
                ->with('error', 'Token verifikasi tidak valid.');
        }

        $user = $this->userModel->findByEmail($reset['email']);
        if (! $user) {
            return redirect()->to('/login')
                ->with('error', 'Akun tidak ditemukan.');
        }

        $this->userModel->verifyEmail($user['id']);

        // Hapus token setelah digunakan
        $db->table('password_resets')->where('token', $token)->delete();

        return redirect()->to('/login')
            ->with('success', 'Email berhasil diverifikasi. Silakan login.');
    }

    // -------------------------------------------------------------------------
    // Helper: redirect ke dashboard berdasarkan role
    // -------------------------------------------------------------------------
    protected function redirectToDashboard(string $role): RedirectResponse
    {
        return match ($role) {
            'admin', 'super_admin' => redirect()->to('/admin/dashboard'),
            default                => redirect()->to('/user/dashboard'),
        };
    }
}
