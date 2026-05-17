<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * AkunController
 *
 * Manajemen semua akun pengguna (semua role) untuk Super Admin.
 * Mendukung CRUD lengkap: tambah, edit, nonaktifkan, aktifkan, hapus.
 */
class AkunController extends BaseController
{
    protected UserModel     $userModel;
    protected AuditLogModel $auditLogModel;

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->auditLogModel = new AuditLogModel();
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function getMenus(): array
    {
        $db = \Config\Database::connect();
        return $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();
    }

    private function currentUserId(): int
    {
        return (int) session()->get('user_id');
    }

    // -------------------------------------------------------------------------
    // index — daftar semua user
    // -------------------------------------------------------------------------

    public function index(): string
    {
        $search = $this->request->getGet('search') ?? '';
        $role   = $this->request->getGet('role')   ?? '';
        $status = $this->request->getGet('status') ?? '';

        $builder = $this->userModel->builder();

        if ($search !== '') {
            $builder->groupStart()
                ->like('nama', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }

        if ($role !== '') {
            $builder->where('role', $role);
        }

        if ($status !== '') {
            $builder->where('is_active', (int) $status);
        }

        $builder->orderBy('created_at', 'DESC');

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        $total   = $builder->countAllResults(false);
        $users   = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return view('superadmin/akun/index', [
            'users'   => $users,
            'search'  => $search,
            'role'    => $role,
            'status'  => $status,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'menus'   => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // create — form tambah akun
    // -------------------------------------------------------------------------

    public function create(): string
    {
        return view('superadmin/akun/form', [
            'user'   => null,
            'action' => base_url('superadmin/akun/store'),
            'menus'  => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // store — simpan akun baru
    // -------------------------------------------------------------------------

    public function store(): RedirectResponse
    {
        $rules = [
            'nama'     => 'required|min_length[2]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role'     => 'required|in_list[user,admin,super_admin]',
            'telepon'  => 'permit_empty|max_length[20]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama'      => $this->request->getPost('nama'),
            'email'     => $this->request->getPost('email'),
            'telepon'   => $this->request->getPost('telepon') ?? '',
            'password'  => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role'      => $this->request->getPost('role'),
            'is_active' => (int) ($this->request->getPost('is_active') ?? 1),
        ];

        $this->userModel->insert($data);

        $this->auditLogModel->catat(
            $this->currentUserId(),
            'Tambah Akun',
            "Menambahkan akun baru: {$data['email']} (role: {$data['role']})",
            $this->request->getIPAddress()
        );

        return redirect()->to(base_url('superadmin/akun'))->with('success', 'Akun berhasil ditambahkan.');
    }

    // -------------------------------------------------------------------------
    // edit — form edit akun
    // -------------------------------------------------------------------------

    public function edit(int $id): string
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Akun tidak ditemukan.');
        }

        return view('superadmin/akun/form', [
            'user'   => $user,
            'action' => base_url("superadmin/akun/{$id}/update"),
            'menus'  => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // update — simpan perubahan akun
    // -------------------------------------------------------------------------

    public function update(int $id): RedirectResponse
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Akun tidak ditemukan.');
        }

        $rules = [
            'nama'    => 'required|min_length[2]|max_length[100]',
            'email'   => [
                'rules' => "required|valid_email|is_unique[users.email,id,{$id}]",
                'label' => 'Email',
            ],
            'role'    => 'required|in_list[user,admin,super_admin]',
            'telepon' => 'permit_empty|max_length[20]',
        ];

        $password = $this->request->getPost('password');
        if ($password !== '' && $password !== null) {
            $rules['password'] = 'min_length[8]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama'      => $this->request->getPost('nama'),
            'email'     => $this->request->getPost('email'),
            'telepon'   => $this->request->getPost('telepon') ?? '',
            'role'      => $this->request->getPost('role'),
            'is_active' => (int) $this->request->getPost('is_active'),
        ];

        if ($password !== '' && $password !== null) {
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $this->userModel->update($id, $data);

        $this->auditLogModel->catat(
            $this->currentUserId(),
            'Edit Akun',
            "Memperbarui akun: {$data['email']} (role: {$data['role']})",
            $this->request->getIPAddress()
        );

        return redirect()->to(base_url('superadmin/akun'))->with('success', 'Akun berhasil diperbarui.');
    }

    // -------------------------------------------------------------------------
    // nonaktifkan — set is_active = 0
    // -------------------------------------------------------------------------

    public function nonaktifkan(int $id): RedirectResponse
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Akun tidak ditemukan.');
        }

        $this->userModel->update($id, ['is_active' => 0]);

        $this->auditLogModel->catat(
            $this->currentUserId(),
            'Nonaktifkan Akun',
            "Menonaktifkan akun: {$user['email']} (role: {$user['role']})",
            $this->request->getIPAddress()
        );

        return redirect()->to(base_url('superadmin/akun'))->with('success', 'Akun berhasil dinonaktifkan.');
    }

    // -------------------------------------------------------------------------
    // aktifkan — set is_active = 1
    // -------------------------------------------------------------------------

    public function aktifkan(int $id): RedirectResponse
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Akun tidak ditemukan.');
        }

        $this->userModel->update($id, ['is_active' => 1]);

        $this->auditLogModel->catat(
            $this->currentUserId(),
            'Aktifkan Akun',
            "Mengaktifkan kembali akun: {$user['email']} (role: {$user['role']})",
            $this->request->getIPAddress()
        );

        return redirect()->to(base_url('superadmin/akun'))->with('success', 'Akun berhasil diaktifkan.');
    }

    // -------------------------------------------------------------------------
    // delete — hard delete akun
    // -------------------------------------------------------------------------

    public function delete(int $id): RedirectResponse
    {
        if ($this->currentUserId() === $id) {
            return redirect()->to(base_url('superadmin/akun'))
                ->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        $user = $this->userModel->find($id);

        if (! $user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Akun tidak ditemukan.');
        }

        $this->userModel->delete($id);

        $this->auditLogModel->catat(
            $this->currentUserId(),
            'Hapus Akun',
            "Menghapus akun: {$user['email']} (role: {$user['role']})",
            $this->request->getIPAddress()
        );

        return redirect()->to(base_url('superadmin/akun'))->with('success', 'Akun berhasil dihapus.');
    }
}
