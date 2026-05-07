<?php

namespace App\Controllers\Admin\Master;

use App\Controllers\BaseController;
use App\Models\ProdukModel;

class ProdukController extends BaseController
{
    protected ProdukModel $produkModel;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
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
     * Daftar semua produk.
     */
    public function index()
    {
        $produks = $this->produkModel->orderBy('id', 'DESC')->findAll();

        return view('admin/master/produk/index', [
            'produks' => $produks,
            'menus'   => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah produk.
     */
    public function create()
    {
        return view('admin/master/produk/form', [
            'produk' => null,
            'action' => base_url('admin/master/produk/store'),
            'menus'  => $this->getMenus(),
        ]);
    }

    /**
     * Simpan produk baru.
     */
    public function store()
    {
        $rules = [
            'nama'      => 'required',
            'harga'     => 'required|decimal|greater_than_equal_to[0]',
            'thumbnail' => 'if_exist|is_image[thumbnail]|max_size[thumbnail,2048]|ext_in[thumbnail,jpg,jpeg,png,webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $thumbnailName = null;
        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $thumbnailName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/produk', $thumbnailName);
        }

        $this->produkModel->insert([
            'nama'      => $this->request->getPost('nama'),
            'deskripsi' => $this->request->getPost('deskripsi') ?? null,
            'thumbnail' => $thumbnailName,
            'harga'     => (float) $this->request->getPost('harga'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to(base_url('admin/master/produk'))->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Form edit produk.
     */
    public function edit(int $id)
    {
        $produk = $this->produkModel->find($id);

        if (! $produk) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        return view('admin/master/produk/form', [
            'produk' => $produk,
            'action' => base_url("admin/master/produk/{$id}/update"),
            'menus'  => $this->getMenus(),
        ]);
    }

    /**
     * Update produk.
     */
    public function update(int $id)
    {
        $produk = $this->produkModel->find($id);

        if (! $produk) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        $rules = [
            'nama'      => 'required',
            'harga'     => 'required|decimal|greater_than_equal_to[0]',
            'thumbnail' => 'if_exist|is_image[thumbnail]|max_size[thumbnail,2048]|ext_in[thumbnail,jpg,jpeg,png,webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $thumbnailName = $produk['thumbnail']; // pertahankan thumbnail lama
        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            // Hapus thumbnail lama jika ada
            if ($thumbnailName && file_exists(FCPATH . 'uploads/produk/' . $thumbnailName)) {
                @unlink(FCPATH . 'uploads/produk/' . $thumbnailName);
            }
            $thumbnailName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/produk', $thumbnailName);
        }

        // Jika admin centang "hapus thumbnail"
        if ($this->request->getPost('hapus_thumbnail') && $thumbnailName) {
            if (file_exists(FCPATH . 'uploads/produk/' . $thumbnailName)) {
                @unlink(FCPATH . 'uploads/produk/' . $thumbnailName);
            }
            $thumbnailName = null;
        }

        $this->produkModel->update($id, [
            'nama'      => $this->request->getPost('nama'),
            'deskripsi' => $this->request->getPost('deskripsi') ?? null,
            'thumbnail' => $thumbnailName,
            'harga'     => (float) $this->request->getPost('harga'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to(base_url('admin/master/produk'))->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Hapus produk.
     * Dicegah jika produk masih memiliki transaksi aktif (success atau pending).
     */
    public function delete(int $id)
    {
        $produk = $this->produkModel->find($id);

        if (! $produk) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        if ($this->produkModel->hasActiveTransaksi($id)) {
            return redirect()->to(base_url('admin/master/produk'))
                ->with('error', 'Produk tidak dapat dihapus karena memiliki transaksi aktif.');
        }

        $this->produkModel->delete($id);

        return redirect()->to(base_url('admin/master/produk'))->with('success', 'Produk berhasil dihapus.');
    }
}
