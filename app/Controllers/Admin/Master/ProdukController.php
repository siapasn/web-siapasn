<?php

namespace App\Controllers\Admin\Master;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use App\Models\ProdukMateriModel;

class ProdukController extends BaseController
{
    protected ProdukModel       $produkModel;
    protected ProdukMateriModel $materiModel;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
        $this->materiModel = new ProdukMateriModel();
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

    private function getKategoris(): array
    {
        return \Config\Database::connect()
            ->table('kategori')
            ->where('parent_id IS NULL', null, false)
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();
    }

    private function getKategoriFormasi(): array
    {
        return \Config\Database::connect()
            ->table('kategori_formasi')
            ->where('is_active', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();
    }

    private function getFormasi(): array
    {
        return \Config\Database::connect()
            ->table('formasi')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Ambil ID kategori produk yang memerlukan pemilihan formasi.
     * Kategori yang mengandung kata "SKB" atau "PPPK" dianggap perlu formasi.
     */
    private function getKategoriIdsWithFormasi(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('kategori')
            ->select('id')
            ->where('parent_id IS NULL', null, false)
            ->groupStart()
                ->like('nama', 'SKB')
                ->orLike('nama', 'PPPK')
            ->groupEnd()
            ->get()->getResultArray();

        return array_column($rows, 'id');
    }

    /**
     * Daftar semua produk.
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $produks = $db->table('produk p')
            ->select('p.*, k.nama AS kategori_nama, f.nama AS formasi_nama')
            ->join('kategori k', 'k.id = p.kategori_id', 'left')
            ->join('formasi f', 'f.id = p.formasi_id', 'left')
            ->orderBy('p.id', 'DESC')
            ->get()->getResultArray();

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
            'produk'              => null,
            'materi'              => [],
            'kategoris'           => $this->getKategoris(),
            'kategoriFormasi'     => $this->getKategoriFormasi(),
            'formasiList'         => $this->getFormasi(),
            'kategoriWithFormasi' => $this->getKategoriIdsWithFormasi(),
            'action'              => base_url('admin/master/produk/store'),
            'menus'               => $this->getMenus(),
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

        helper('slug');
        $slug = make_unique_slug('produk', $this->request->getPost('nama'));

        $produkId = $this->produkModel->insert([
            'nama'        => $this->request->getPost('nama'),
            'slug'        => $slug,
            'kategori_id' => $this->request->getPost('kategori_id') ?: null,
            'formasi_id'  => $this->request->getPost('formasi_id') ?: null,
            'deskripsi'   => $this->request->getPost('deskripsi') ?? null,
            'thumbnail'   => $thumbnailName,
            'harga'       => (float) $this->request->getPost('harga'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        // Simpan materi pelajaran
        $this->simpanMateri((int) $produkId);

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
            'produk'              => $produk,
            'materi'              => $this->materiModel->getByProduk($id),
            'kategoris'           => $this->getKategoris(),
            'kategoriFormasi'     => $this->getKategoriFormasi(),
            'formasiList'         => $this->getFormasi(),
            'kategoriWithFormasi' => $this->getKategoriIdsWithFormasi(),
            'action'              => base_url("admin/master/produk/{$id}/update"),
            'menus'               => $this->getMenus(),
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

        helper('slug');
        $slug = make_unique_slug('produk', $this->request->getPost('nama'), $id);

        $this->produkModel->update($id, [
            'nama'        => $this->request->getPost('nama'),
            'slug'        => $slug,
            'kategori_id' => $this->request->getPost('kategori_id') ?: null,
            'formasi_id'  => $this->request->getPost('formasi_id') ?: null,
            'deskripsi'   => $this->request->getPost('deskripsi') ?? null,
            'thumbnail'   => $thumbnailName,
            'harga'       => (float) $this->request->getPost('harga'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        // Simpan ulang materi pelajaran (replace all)
        $this->materiModel->deleteByProduk($id);
        $this->simpanMateri($id);

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

    /**
     * AJAX: Toggle field is_active atau is_highlight.
     */
    public function toggle()
    {
        $id    = (int) $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = (int) $this->request->getPost('value');

        if (! in_array($field, ['is_active', 'is_highlight'], true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Field tidak valid.']);
        }

        $produk = $this->produkModel->find($id);
        if (! $produk) {
            return $this->response->setJSON(['status' => false, 'message' => 'Produk tidak ditemukan.']);
        }

        $this->produkModel->update($id, [$field => $value ? 1 : 0]);

        return $this->response->setJSON(['status' => true]);
    }

    /**
     * Simpan baris materi dari POST data ke tabel produk_materi.
     * Data dikirim sebagai array: materi[judul][], materi[tipe_file][], materi[url_file][], materi[urutan][]
     */
    private function simpanMateri(int $produkId): void
    {
        $judulArr    = $this->request->getPost('materi_judul')    ?? [];
        $tipeArr     = $this->request->getPost('materi_tipe')     ?? [];
        $urlArr      = $this->request->getPost('materi_url')      ?? [];

        $tipeValid = ['Gambar', 'Video', 'Dokumen'];

        foreach ($judulArr as $i => $judul) {
            $judul = trim($judul);
            $url   = trim($urlArr[$i] ?? '');
            $tipe  = $tipeArr[$i] ?? '';

            // Lewati baris yang tidak lengkap
            if ($judul === '' || $url === '' || ! in_array($tipe, $tipeValid, true)) {
                continue;
            }

            $this->materiModel->insert([
                'produk_id' => $produkId,
                'judul'     => $judul,
                'tipe_file' => $tipe,
                'url_file'  => $url,
                'urutan'    => (int) ($i + 1),
            ]);
        }
    }
}
