<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KategoriBukuModel;
use App\Models\KatalogBukuModel;

class KategoriBukuController extends BaseController
{
    protected KategoriBukuModel $kategoriBukuModel;
    protected KatalogBukuModel $katalogBukuModel;

    public function __construct()
    {
        $this->kategoriBukuModel = new KategoriBukuModel();
        $this->katalogBukuModel  = new KatalogBukuModel();
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
     * Daftar semua kategori buku.
     */
    public function index()
    {
        $kategoris = $this->kategoriBukuModel
            ->orderBy('urutan', 'ASC')
            ->findAll();

        return view('admin/kategori-buku/index', [
            'kategoris' => $kategoris,
            'menus'     => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah kategori buku.
     */
    public function create()
    {
        return view('admin/kategori-buku/form', [
            'kategori' => null,
            'action'   => base_url('admin/kategori-buku/store'),
            'menus'    => $this->getMenus(),
        ]);
    }

    /**
     * Simpan kategori buku baru.
     */
    public function store()
    {
        $rules = [
            'nama' => 'required|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->kategoriBukuModel->insert([
            'nama'      => $this->request->getPost('nama'),
            'urutan'    => (int) $this->request->getPost('urutan'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to(base_url('admin/kategori-buku'))
            ->with('success', 'Kategori buku berhasil ditambahkan.');
    }

    /**
     * Form edit kategori buku.
     */
    public function edit(int $id)
    {
        $kategori = $this->kategoriBukuModel->find($id);

        if (! $kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori buku tidak ditemukan.');
        }

        return view('admin/kategori-buku/form', [
            'kategori' => $kategori,
            'action'   => base_url("admin/kategori-buku/{$id}/update"),
            'menus'    => $this->getMenus(),
        ]);
    }

    /**
     * Update kategori buku.
     */
    public function update(int $id)
    {
        $kategori = $this->kategoriBukuModel->find($id);

        if (! $kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori buku tidak ditemukan.');
        }

        $rules = [
            'nama' => 'required|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->kategoriBukuModel->update($id, [
            'nama'      => $this->request->getPost('nama'),
            'urutan'    => (int) $this->request->getPost('urutan'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to(base_url('admin/kategori-buku'))
            ->with('success', 'Kategori buku berhasil diperbarui.');
    }

    /**
     * Hapus kategori buku — cegah jika masih ada katalog buku terkait.
     */
    public function delete(int $id)
    {
        $kategori = $this->kategoriBukuModel->find($id);

        if (! $kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori buku tidak ditemukan.');
        }

        // Cek apakah masih ada katalog buku yang menggunakan kategori ini
        $jumlahBuku = $this->katalogBukuModel
            ->where('kategori_buku_id', $id)
            ->countAllResults();

        if ($jumlahBuku > 0) {
            return redirect()->to(base_url('admin/kategori-buku'))
                ->with('error', "Kategori tidak dapat dihapus karena masih digunakan oleh {$jumlahBuku} buku.");
        }

        $this->kategoriBukuModel->delete($id);

        return redirect()->to(base_url('admin/kategori-buku'))
            ->with('success', 'Kategori buku berhasil dihapus.');
    }
}
