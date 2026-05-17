<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\UserModel;

class ProfilController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // -------------------------------------------------------------------------
    // index() — tampilkan halaman profil user
    // -------------------------------------------------------------------------

    public function index()
    {
        $userId = (int) session()->get('user_id');
        $user   = $this->userModel->find($userId);

        if (! $user) {
            return redirect()->to(base_url('user/dashboard'))
                ->with('error', 'Data pengguna tidak ditemukan.');
        }

        $db    = \Config\Database::connect();
        $menus = $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();

        return view('user/profil/index', [
            'user'  => $user,
            'menus' => $menus,
        ]);
    }

    // -------------------------------------------------------------------------
    // updateProfil() — POST: update nama dan no hp
    // -------------------------------------------------------------------------

    public function updateProfil()
    {
        $userId = (int) session()->get('user_id');

        $rules = [
            'nama'    => 'required|min_length[2]|max_length[100]',
            'telepon' => 'permit_empty|max_length[20]|regex_match[/^[0-9+\-\s()]{6,20}$/]',
        ];

        $messages = [
            'nama' => [
                'required'   => 'Nama tidak boleh kosong.',
                'min_length' => 'Nama minimal 2 karakter.',
                'max_length' => 'Nama maksimal 100 karakter.',
            ],
            'telepon' => [
                'max_length'   => 'No HP maksimal 20 karakter.',
                'regex_match'  => 'Format no HP tidak valid.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('error_profil', $this->validator->getErrors());
        }

        $nama    = trim($this->request->getPost('nama'));
        $telepon = trim($this->request->getPost('telepon') ?? '');

        $this->userModel->update($userId, [
            'nama'    => $nama,
            'telepon' => $telepon ?: null,
        ]);

        // Perbarui nama di session
        session()->set('nama', $nama);

        return redirect()->to(base_url('user/profil'))
            ->with('success', 'Profil berhasil diperbarui.');
    }

    // -------------------------------------------------------------------------
    // updatePassword() — POST: ganti password
    // -------------------------------------------------------------------------

    public function updatePassword()
    {
        $userId = (int) session()->get('user_id');
        $user   = $this->userModel->find($userId);

        if (! $user) {
            return redirect()->back()->with('error_password', ['user' => 'Data pengguna tidak ditemukan.']);
        }

        $rules = [
            'password_lama'  => 'required',
            'password_baru'  => 'required|min_length[8]|max_length[72]',
            'konfirmasi'     => 'required|matches[password_baru]',
        ];

        $messages = [
            'password_lama' => [
                'required' => 'Password lama tidak boleh kosong.',
            ],
            'password_baru' => [
                'required'   => 'Password baru tidak boleh kosong.',
                'min_length' => 'Password baru minimal 8 karakter.',
                'max_length' => 'Password baru maksimal 72 karakter.',
            ],
            'konfirmasi' => [
                'required' => 'Konfirmasi password tidak boleh kosong.',
                'matches'  => 'Konfirmasi password tidak cocok.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()
                ->with('error_password', $this->validator->getErrors())
                ->with('tab_aktif', 'password');
        }

        // Verifikasi password lama
        if (! password_verify($this->request->getPost('password_lama'), $user['password'])) {
            return redirect()->back()
                ->with('error_password', ['password_lama' => 'Password lama tidak sesuai.'])
                ->with('tab_aktif', 'password');
        }

        // Pastikan password baru berbeda dari password lama
        if (password_verify($this->request->getPost('password_baru'), $user['password'])) {
            return redirect()->back()
                ->with('error_password', ['password_baru' => 'Password baru tidak boleh sama dengan password lama.'])
                ->with('tab_aktif', 'password');
        }

        $this->userModel->update($userId, [
            'password' => password_hash($this->request->getPost('password_baru'), PASSWORD_BCRYPT),
        ]);

        return redirect()->to(base_url('user/profil'))
            ->with('success', 'Password berhasil diperbarui. Silakan login kembali jika diperlukan.')
            ->with('tab_aktif', 'password');
    }
}
