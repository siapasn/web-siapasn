<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Libraries\RateLimiter;
use App\Models\UserModel;
use App\Services\EmailService;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    protected UserModel $userModel;
    protected EmailService $emailService;
    protected RateLimiter $rateLimiter;

    public function __construct()
    {
        $this->userModel    = new UserModel();
        $this->emailService = new EmailService();
        $this->rateLimiter  = new RateLimiter();
    }

    // -------------------------------------------------------------------------
    // resendVerification — POST: kirim ulang email verifikasi
    // -------------------------------------------------------------------------
    public function resendVerification(): RedirectResponse
    {
        $email = $this->request->getPost('email');

        if (empty($email)) {
            return redirect()->to('/login')->with('error', 'Email tidak valid.');
        }

        $user = $this->userModel->findByEmail($email);

        if (! $user) {
            // Jangan beri info apakah email terdaftar atau tidak (security)
            return redirect()->to('/login')
                ->with('success', 'Jika email terdaftar, link verifikasi telah dikirim ulang.');
        }

        // Jika sudah aktif, tidak perlu kirim ulang
        if ($user['is_active'] && ! empty($user['email_verified_at'])) {
            return redirect()->to('/login')
                ->with('success', 'Akun Anda sudah terverifikasi. Silakan login.');
        }

        // Hapus token lama, buat token baru
        $db    = \Config\Database::connect();
        $db->table('password_resets')->where('email', $email)->delete();

        $token = bin2hex(random_bytes(32));
        $db->table('password_resets')->insert([
            'email'      => $email,
            'token'      => $token,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $verifyUrl = base_url('verify-email/' . $token);

        try {
            $this->emailService->sendVerification($email, $user['nama'], $verifyUrl);
        } catch (\Throwable $e) {
            log_message('error', 'Resend verification gagal: ' . $e->getMessage());
        }

        return redirect()->to('/login')
            ->with('success', 'Link verifikasi telah dikirim ulang. Silakan cek email Anda.');
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
        $data = [
            'captcha' => $this->generateCaptcha(),
        ];

        return view('auth/register', $data);
    }

    // -------------------------------------------------------------------------
    // registerProcess — POST: proses registrasi
    // -------------------------------------------------------------------------
    public function registerProcess(): RedirectResponse
    {
        // Rate limiting: max 3 register per menit per IP
        $ip      = $this->request->getIPAddress();
        $rateKey = 'register_' . md5($ip);

        if (! $this->rateLimiter->check($rateKey, 3, 1)) {
            $retryAfter = $this->rateLimiter->getRetryAfter($rateKey);
            $seconds    = $retryAfter > 0 ? $retryAfter : 60;
            $this->generateCaptcha();
            return redirect()->back()->withInput()
                ->with('error', "Terlalu banyak percobaan registrasi. Silakan coba lagi dalam {$seconds} detik.");
        }

        // Validasi captcha
        $captchaError = $this->validateCaptcha();
        if ($captchaError !== null) {
            return redirect()->back()->withInput()->with('errors', ['captcha' => $captchaError]);
        }

        // Validasi nama (custom)
        $nama = $this->request->getPost('nama');
        $namaError = $this->validateNama($nama);
        if ($namaError !== null) {
            return redirect()->back()->withInput()->with('errors', ['nama' => $namaError]);
        }

        // Validasi nomor telepon (custom)
        $telepon = $this->request->getPost('telepon');
        $teleponError = $this->validateTelepon($telepon);
        if ($teleponError !== null) {
            return redirect()->back()->withInput()->with('errors', ['telepon' => $teleponError]);
        }

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

        // Increment rate limiter setelah validasi lolos
        $this->rateLimiter->increment($rateKey, 1);

        $password = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);

        $userId = $this->userModel->insert([
            'nama'      => $this->request->getPost('nama'),
            'email'     => $this->request->getPost('email'),
            'telepon'   => $this->request->getPost('telepon'),
            'password'  => $password,
            'role'      => 'user',
            'is_active' => 0, // nonaktif sampai verifikasi email
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

        $data = [
            'captcha'      => $this->generateCaptcha(),
            'redirect_url' => $this->request->getGet('redirect_url') ?? '',
        ];

        return view('auth/login', $data);
    }

    // -------------------------------------------------------------------------
    // loginProcess — POST: proses login
    // -------------------------------------------------------------------------
    public function loginProcess(): RedirectResponse
    {
        // Rate limiting: max 10 failed login per 15 menit per IP
        $ip      = $this->request->getIPAddress();
        $rateKey = 'login_' . md5($ip);

        if (! $this->rateLimiter->check($rateKey, 10, 15)) {
            $retryAfter = $this->rateLimiter->getRetryAfter($rateKey);
            $minutes    = ceil($retryAfter / 60);
            $this->generateCaptcha();
            return redirect()->back()->withInput()
                ->with('error', "Terlalu banyak percobaan login. Silakan coba lagi dalam {$minutes} menit.");
        }

        // Validasi captcha
        $captchaError = $this->validateCaptcha();
        if ($captchaError !== null) {
            return redirect()->back()->withInput()->with('error', $captchaError);
        }

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
            $this->rateLimiter->increment($rateKey, 15);
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
            $this->rateLimiter->increment($rateKey, 15);

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

        // Login berhasil — cek status akun
        // Cek apakah akun nonaktif (belum verifikasi email atau dinonaktifkan admin)
        if (! $user['is_active']) {
            // Tentukan pesan berdasarkan apakah sudah pernah dapat token verifikasi
            $db = \Config\Database::connect();
            $hasToken = $db->table('password_resets')
                ->where('email', $user['email'])
                ->countAllResults() > 0;

            if ($hasToken || empty($user['email_verified_at'])) {
                return redirect()->back()->withInput()
                    ->with('error', 'Akun Anda belum aktif. Silakan verifikasi email terlebih dahulu. Cek inbox atau folder spam Anda.')
                    ->with('show_resend_verification', $user['email']);
            }

            return redirect()->back()->withInput()
                ->with('error', 'Akun Anda dinonaktifkan. Silakan hubungi admin.');
        }

        // Reset rate limiter
        $this->rateLimiter->reset($rateKey);
        $this->userModel->resetLoginAttempts($user['id']);

        // Generate session token baru — invalidate sesi lain yang sedang aktif
        $sessionToken = bin2hex(random_bytes(32));
        $this->userModel->update($user['id'], ['session_token' => $sessionToken]);

        session()->set([
            'user_id'       => $user['id'],
            'nama'          => $user['nama'],
            'email'         => $user['email'],
            'role'          => $user['role'],
            'session_token' => $sessionToken,
            'last_activity' => time(),
        ]);

        // Redirect ke URL yang diminta sebelum login (jika ada)
        $redirectUrl = $this->request->getPost('redirect_url');
        if (! empty($redirectUrl) && $user['role'] === 'user') {
            // Sanitasi: pastikan hanya path internal
            $redirectUrl = ltrim($redirectUrl, '/');
            if (! str_starts_with($redirectUrl, 'http')) {
                return redirect()->to(base_url($redirectUrl));
            }
        }

        return $this->redirectToDashboard($user['role']);
    }

    // -------------------------------------------------------------------------
    // logout — GET: hapus sesi dan redirect ke login
    // -------------------------------------------------------------------------
    public function logout(): RedirectResponse
    {
        // Hapus session_token di DB agar tidak bisa dipakai lagi
        $userId = session()->get('user_id');
        if ($userId) {
            $this->userModel->update($userId, ['session_token' => null]);
        }

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

        // Notifikasi ke admin: user baru terdaftar
        \App\Models\NotifikasiModel::kirimKeRole('admin', 'user', 'User Baru Terdaftar', $user['nama'] . ' (' . $user['email'] . ') telah verifikasi email.', 'admin/master/user');
        \App\Models\NotifikasiModel::kirimKeRole('super_admin', 'user', 'User Baru Terdaftar', $user['nama'] . ' (' . $user['email'] . ') telah verifikasi email.', 'admin/master/user');

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

    // =========================================================================
    // CAPTCHA METHODS
    // =========================================================================

    /**
     * Generate math captcha dan simpan jawaban di session
     */
    protected function generateCaptcha(): string
    {
        $num1 = random_int(1, 20);
        $num2 = random_int(1, 20);

        $operators = ['+', '-'];
        $operator  = $operators[array_rand($operators)];

        if ($operator === '-') {
            if ($num1 < $num2) {
                [$num1, $num2] = [$num2, $num1];
            }
            $answer = $num1 - $num2;
        } else {
            $answer = $num1 + $num2;
        }

        $question = "{$num1} {$operator} {$num2} = ?";
        session()->set('captcha_answer', $answer);

        return $question;
    }

    /**
     * Validasi jawaban captcha dari form
     */
    protected function validateCaptcha(): ?string
    {
        $userAnswer    = $this->request->getPost('captcha');
        $correctAnswer = session()->get('captcha_answer');

        // Selalu regenerate captcha setelah validasi
        $this->generateCaptcha();

        if ($userAnswer === null || $userAnswer === '') {
            return 'Jawaban captcha wajib diisi.';
        }

        if ((int) $userAnswer !== (int) $correctAnswer) {
            return 'Jawaban captcha salah. Silakan coba lagi.';
        }

        return null;
    }

    // =========================================================================
    // CUSTOM VALIDATION METHODS
    // =========================================================================

    /**
     * Validasi nama: minimal 3 karakter, harus ada huruf, tidak boleh random
     */
    protected function validateNama(?string $nama): ?string
    {
        if (empty($nama)) {
            return null;
        }

        $nama = trim($nama);

        if (mb_strlen($nama) < 3) {
            return 'Nama minimal 3 karakter.';
        }

        if (! preg_match('/[a-zA-Z]/', $nama)) {
            return 'Nama harus mengandung minimal satu huruf.';
        }

        // Deteksi pola random: 4+ konsonan berturut-turut tanpa vokal
        $namaLower = strtolower(preg_replace('/[^a-zA-Z]/', '', $nama));

        if (strlen($namaLower) > 0 && preg_match('/[^aeiou]{4,}/', $namaLower)) {
            return 'Nama tidak valid. Mohon masukkan nama asli Anda.';
        }

        // Cek rasio konsonan:vokal untuk nama > 4 karakter
        if (strlen($namaLower) > 4) {
            $vowels     = preg_match_all('/[aeiou]/', $namaLower);
            $consonants = strlen($namaLower) - $vowels;

            if ($vowels > 0 && ($consonants / $vowels) > 4) {
                return 'Nama tidak valid. Mohon masukkan nama asli Anda.';
            }

            if ($vowels === 0) {
                return 'Nama tidak valid. Mohon masukkan nama asli Anda.';
            }
        }

        return null;
    }

    /**
     * Validasi nomor telepon Indonesia
     * Format: 08xx, +62xx, atau 62xx (10-15 digit total)
     */
    protected function validateTelepon(?string $telepon): ?string
    {
        if (empty($telepon)) {
            return null;
        }

        $telepon = trim($telepon);

        if (! preg_match('/^(\+62|62|08)[0-9]{8,13}$/', $telepon)) {
            return 'Format nomor HP tidak valid. Gunakan format 08xx, +62xx, atau 62xx (10-15 digit).';
        }

        return null;
    }
}
