<?php

namespace App\Controllers\Admin\Master;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    private function getMenus(): array
    {
        $db = \Config\Database::connect();
        return $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Daftar semua user dengan search dan filter role.
     * Pagination ditangani oleh DataTables client-side.
     */
    public function index()
    {
        $search = $this->request->getGet('search') ?? '';
        $role   = $this->request->getGet('role') ?? '';

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

        $builder->orderBy('created_at', 'DESC');

        $users = $builder->get()->getResultArray();

        return view('admin/master/user/index', [
            'users'  => $users,
            'search' => $search,
            'role'   => $role,
            'menus'  => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah user baru.
     */
    public function create()
    {
        return view('admin/master/user/form', [
            'user'   => null,
            'action' => base_url('admin/master/user/store'),
            'menus'  => $this->getMenus(),
        ]);
    }

    /**
     * Simpan user baru.
     */
    public function store()
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

        $this->userModel->insert([
            'nama'      => $this->request->getPost('nama'),
            'email'     => $this->request->getPost('email'),
            'telepon'   => $this->request->getPost('telepon') ?? '',
            'password'  => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role'      => $this->request->getPost('role'),
            'is_active' => (int) ($this->request->getPost('is_active') ?? 1),
        ]);

        return redirect()->to(base_url('admin/master/user'))->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Form edit user.
     */
    public function edit(int $id)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('User tidak ditemukan.');
        }

        return view('admin/master/user/form', [
            'user'   => $user,
            'action' => base_url("admin/master/user/{$id}/update"),
            'menus'  => $this->getMenus(),
        ]);
    }

    /**
     * Update data user.
     */
    public function update(int $id)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('User tidak ditemukan.');
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
            'is_active' => $user['is_active'], // pertahankan status lama, diubah via toggle di list
        ];

        if ($password !== '' && $password !== null) {
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $this->userModel->update($id, $data);

        return redirect()->to(base_url('admin/master/user'))->with('success', 'User berhasil diperbarui.');
    }

    /**
     * AJAX: Toggle is_active user.
     * POST admin/master/user/toggle-status
     */
    public function toggleStatus()
    {
        $id    = (int) $this->request->getPost('id');
        $value = (int) $this->request->getPost('value'); // 1 atau 0

        if ((int) session()->get('user_id') === $id) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak dapat mengubah status akun sendiri.']);
        }

        $user = $this->userModel->find($id);
        if (! $user) {
            return $this->response->setJSON(['status' => false, 'message' => 'User tidak ditemukan.']);
        }

        $this->userModel->update($id, ['is_active' => $value ? 1 : 0]);

        return $this->response->setJSON([
            'status'  => true,
            'message' => $value ? 'User diaktifkan.' : 'User dinonaktifkan.',
            'value'   => $value ? 1 : 0,
        ]);
    }

    /**
     * Soft-delete user (set is_active = 0).
     * Mencegah penghapusan akun sendiri.
     */
    public function delete(int $id)
    {
        if ((int) session()->get('user_id') === $id) {
            return redirect()->to(base_url('admin/master/user'))->with('error', 'Tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user = $this->userModel->find($id);

        if (! $user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('User tidak ditemukan.');
        }

        $this->userModel->update($id, ['is_active' => 0]);

        return redirect()->to(base_url('admin/master/user'))->with('success', 'User berhasil dinonaktifkan.');
    }
}
