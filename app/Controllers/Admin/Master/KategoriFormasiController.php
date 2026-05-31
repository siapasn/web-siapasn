<?php

namespace App\Controllers\Admin\Master;

use App\Controllers\BaseController;
use App\Models\KategoriFormasiModel;

class KategoriFormasiController extends BaseController
{
    protected KategoriFormasiModel $kategoriFormasiModel;

    public function __construct()
    {
        $this->kategoriFormasiModel = new KategoriFormasiModel();
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
     * Daftar semua kategori formasi.
     */
    public function index()
    {
        $kategoris = $this->kategoriFormasiModel->getAllWithCount();

        return view('admin/master/kategori-formasi/index', [
            'kategoris' => $kategoris,
            'menus'     => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah kategori formasi.
     */
    public function create()
    {
        return view('admin/master/kategori-formasi/form', [
            'kategori' => null,
            'action'   => base_url('admin/master/kategori-formasi/store'),
            'menus'    => $this->getMenus(),
        ]);
    }

    /**
     * Simpan kategori formasi baru.
     */
    public function store()
    {
        $rules = [
            'nama'      => 'required|min_length[2]|max_length[150]',
            'deskripsi' => 'permit_empty|max_length[500]',
            'icon'      => 'permit_empty|max_length[100]',
            'urutan'    => 'permit_empty|is_natural',
            'is_active' => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->kategoriFormasiModel->insert([
            'nama'      => $this->request->getPost('nama'),
            'deskripsi' => $this->request->getPost('deskripsi') ?: null,
            'icon'      => $this->request->getPost('icon') ?: null,
            'urutan'    => $this->request->getPost('urutan') ?: 0,
            'is_active' => $this->request->getPost('is_active') ?? 1,
        ]);

        return redirect()->to(base_url('admin/master/kategori-formasi'))->with('success', 'Kategori formasi berhasil ditambahkan.');
    }

    /**
     * Form edit kategori formasi.
     */
    public function edit(int $id)
    {
        $kategori = $this->kategoriFormasiModel->find($id);

        if (! $kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori formasi tidak ditemukan.');
        }

        return view('admin/master/kategori-formasi/form', [
            'kategori' => $kategori,
            'action'   => base_url("admin/master/kategori-formasi/{$id}/update"),
            'menus'    => $this->getMenus(),
        ]);
    }

    /**
     * Update kategori formasi.
     */
    public function update(int $id)
    {
        $kategori = $this->kategoriFormasiModel->find($id);

        if (! $kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori formasi tidak ditemukan.');
        }

        $rules = [
            'nama'      => 'required|min_length[2]|max_length[150]',
            'deskripsi' => 'permit_empty|max_length[500]',
            'icon'      => 'permit_empty|max_length[100]',
            'urutan'    => 'permit_empty|is_natural',
            'is_active' => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->kategoriFormasiModel->update($id, [
            'nama'      => $this->request->getPost('nama'),
            'deskripsi' => $this->request->getPost('deskripsi') ?: null,
            'icon'      => $this->request->getPost('icon') ?: null,
            'urutan'    => $this->request->getPost('urutan') ?: 0,
            'is_active' => $this->request->getPost('is_active') ?? 1,
        ]);

        return redirect()->to(base_url('admin/master/kategori-formasi'))->with('success', 'Kategori formasi berhasil diperbarui.');
    }

    /**
     * Hapus kategori formasi.
     * Dicegah jika masih memiliki formasi di dalamnya.
     */
    public function delete(int $id)
    {
        $kategori = $this->kategoriFormasiModel->find($id);

        if (! $kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori formasi tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        // Cek apakah masih ada formasi di kategori ini
        $hasFormasi = $db->table('formasi')
            ->where('kategori_formasi_id', $id)
            ->countAllResults() > 0;

        if ($hasFormasi) {
            return redirect()->to(base_url('admin/master/kategori-formasi'))
                ->with('error', 'Kategori formasi tidak dapat dihapus karena masih memiliki formasi di dalamnya.');
        }

        $this->kategoriFormasiModel->delete($id);

        return redirect()->to(base_url('admin/master/kategori-formasi'))->with('success', 'Kategori formasi berhasil dihapus.');
    }

    /**
     * Detail kategori formasi — menampilkan daftar formasi di dalamnya.
     */
    public function detail(int $id)
    {
        $kategori = $this->kategoriFormasiModel->find($id);

        if (! $kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori formasi tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $formasiList = $db->table('formasi')
            ->where('kategori_formasi_id', $id)
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResultArray();

        return view('admin/master/kategori-formasi/detail', [
            'kategori'    => $kategori,
            'formasiList' => $formasiList,
            'menus'       => $this->getMenus(),
        ]);
    }

    /**
     * Simpan formasi baru ke dalam kategori.
     */
    public function storeFormasi(int $kategoriId)
    {
        $kategori = $this->kategoriFormasiModel->find($kategoriId);

        if (! $kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori formasi tidak ditemukan.');
        }

        $rules = [
            'nama'      => 'required|min_length[2]|max_length[255]',
            'deskripsi' => 'permit_empty|max_length[500]',
            'referensi' => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->table('formasi')->insert([
            'kategori_formasi_id' => $kategoriId,
            'nama'                => $this->request->getPost('nama'),
            'deskripsi'           => $this->request->getPost('deskripsi') ?: null,
            'referensi'           => $this->request->getPost('referensi') ?: null,
            'is_active'           => 1,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url("admin/master/kategori-formasi/{$kategoriId}/detail"))
            ->with('success', 'Formasi berhasil ditambahkan.');
    }

    /**
     * Hapus formasi.
     */
    public function deleteFormasi(int $kategoriId, int $formasiId)
    {
        $db = \Config\Database::connect();

        $formasi = $db->table('formasi')
            ->where('id', $formasiId)
            ->where('kategori_formasi_id', $kategoriId)
            ->get()
            ->getRowArray();

        if (! $formasi) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Formasi tidak ditemukan.');
        }

        $db->table('formasi')->where('id', $formasiId)->delete();

        return redirect()->to(base_url("admin/master/kategori-formasi/{$kategoriId}/detail"))
            ->with('success', 'Formasi berhasil dihapus.');
    }

    /**
     * Update formasi.
     */
    public function updateFormasi(int $kategoriId, int $formasiId)
    {
        $db = \Config\Database::connect();

        $formasi = $db->table('formasi')
            ->where('id', $formasiId)
            ->where('kategori_formasi_id', $kategoriId)
            ->get()
            ->getRowArray();

        if (! $formasi) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Formasi tidak ditemukan.');
        }

        $rules = [
            'nama'      => 'required|min_length[2]|max_length[255]',
            'deskripsi' => 'permit_empty|max_length[500]',
            'referensi' => 'permit_empty|is_natural',
            'is_active' => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db->table('formasi')->where('id', $formasiId)->update([
            'nama'       => $this->request->getPost('nama'),
            'deskripsi'  => $this->request->getPost('deskripsi') ?: null,
            'referensi'  => $this->request->getPost('referensi') ?: null,
            'is_active'  => $this->request->getPost('is_active') ?? 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url("admin/master/kategori-formasi/{$kategoriId}/detail"))
            ->with('success', 'Formasi berhasil diperbarui.');
    }
}
