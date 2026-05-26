<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KatalogBukuModel;

class KatalogBukuController extends BaseController
{
    protected KatalogBukuModel $katalogBukuModel;

    public function __construct()
    {
        $this->katalogBukuModel = new KatalogBukuModel();
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
     * Daftar semua katalog buku.
     */
    public function index()
    {
        $katalogBuku = $this->katalogBukuModel->orderBy('urutan', 'ASC')->orderBy('id', 'DESC')->findAll();

        return view('admin/katalog-buku/index', [
            'katalogBuku' => $katalogBuku,
            'menus'       => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah katalog buku.
     */
    public function create()
    {
        return view('admin/katalog-buku/form', [
            'buku'   => null,
            'action' => base_url('admin/katalog-buku/store'),
            'menus'  => $this->getMenus(),
        ]);
    }

    /**
     * Simpan katalog buku baru.
     */
    public function store()
    {
        $rules = [
            'kode'  => 'required|max_length[50]',
            'judul' => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->katalogBukuModel->insert([
            'kode'          => $this->request->getPost('kode'),
            'judul'         => $this->request->getPost('judul'),
            'isbn'          => $this->request->getPost('isbn') ?: null,
            'pengarang'     => $this->request->getPost('pengarang') ?: null,
            'penerbit'      => $this->request->getPost('penerbit') ?: null,
            'harga'         => $this->request->getPost('harga') ?: null,
            'url_thumbnail' => $this->request->getPost('url_thumbnail') ?: null,
            'url_shopee'    => $this->request->getPost('url_shopee') ?: null,
            'is_active'     => $this->request->getPost('is_active') ? 1 : 0,
            'is_highlight'  => $this->request->getPost('is_highlight') ? 1 : 0,
            'urutan'        => (int) ($this->request->getPost('urutan') ?: 0),
        ]);

        return redirect()->to(base_url('admin/katalog-buku'))
            ->with('success', 'Buku berhasil ditambahkan.');
    }

    /**
     * Form edit katalog buku.
     */
    public function edit(int $id)
    {
        $buku = $this->katalogBukuModel->find($id);

        if (! $buku) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Buku tidak ditemukan.');
        }

        return view('admin/katalog-buku/form', [
            'buku'   => $buku,
            'action' => base_url("admin/katalog-buku/{$id}/update"),
            'menus'  => $this->getMenus(),
        ]);
    }

    /**
     * Update katalog buku.
     */
    public function update(int $id)
    {
        $buku = $this->katalogBukuModel->find($id);

        if (! $buku) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Buku tidak ditemukan.');
        }

        $rules = [
            'kode'  => 'required|max_length[50]',
            'judul' => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->katalogBukuModel->update($id, [
            'kode'          => $this->request->getPost('kode'),
            'judul'         => $this->request->getPost('judul'),
            'isbn'          => $this->request->getPost('isbn') ?: null,
            'pengarang'     => $this->request->getPost('pengarang') ?: null,
            'penerbit'      => $this->request->getPost('penerbit') ?: null,
            'harga'         => $this->request->getPost('harga') ?: null,
            'url_thumbnail' => $this->request->getPost('url_thumbnail') ?: null,
            'url_shopee'    => $this->request->getPost('url_shopee') ?: null,
            'is_active'     => $this->request->getPost('is_active') ? 1 : 0,
            'is_highlight'  => $this->request->getPost('is_highlight') ? 1 : 0,
            'urutan'        => (int) ($this->request->getPost('urutan') ?: 0),
        ]);

        return redirect()->to(base_url('admin/katalog-buku'))
            ->with('success', 'Buku berhasil diperbarui.');
    }

    /**
     * Hapus katalog buku.
     */
    public function delete(int $id)
    {
        $buku = $this->katalogBukuModel->find($id);

        if (! $buku) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Buku tidak ditemukan.');
        }

        $this->katalogBukuModel->delete($id);

        return redirect()->to(base_url('admin/katalog-buku'))
            ->with('success', 'Buku berhasil dihapus.');
    }

    /**
     * AJAX: Toggle field is_active atau is_highlight.
     */
    public function toggle()
    {
        $id    = (int) $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = (int) $this->request->getPost('value');

        // Validasi field yang diizinkan
        if (! in_array($field, ['is_active', 'is_highlight'], true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Field tidak valid.']);
        }

        $buku = $this->katalogBukuModel->find($id);
        if (! $buku) {
            return $this->response->setJSON(['status' => false, 'message' => 'Buku tidak ditemukan.']);
        }

        $this->katalogBukuModel->update($id, [$field => $value ? 1 : 0]);

        return $this->response->setJSON(['status' => true]);
    }

    /**
     * Form import CSV.
     */
    public function import()
    {
        return view('admin/katalog-buku/import', [
            'menus' => $this->getMenus(),
        ]);
    }

    /**
     * Proses import CSV.
     * Format kolom (sesuai template):
     *   A(0) = No
     *   B(1) = Kode *
     *   C(2) = Judul Buku *
     *   D(3) = ISBN
     *   E(4) = Pengarang
     *   F(5) = Penerbit
     *   G(6) = Harga
     *   H(7) = URL Thumbnail
     *   I(8) = URL Shopee
     */
    public function importProcess()
    {
        $file = $this->request->getFile('file_import');

        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'Tidak ada file yang diunggah.');
        }

        $ext = strtolower($file->getClientExtension());
        if ($ext !== 'csv') {
            return redirect()->back()->with('error', 'Format file harus .csv');
        }

        $tmpDir  = WRITEPATH . 'uploads/import/';
        if (! is_dir($tmpDir)) mkdir($tmpDir, 0755, true);
        $tmpName = $file->getRandomName();
        $file->move($tmpDir, $tmpName);
        $filePath = $tmpDir . $tmpName;

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            @unlink($filePath);
            return redirect()->back()->with('error', 'Gagal membuka file.');
        }

        // BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $imported = 0;
        $errors   = [];
        $rowNum   = 0;

        while (($cols = fgetcsv($handle)) !== false) {
            $rowNum++;

            // Skip baris kosong
            if (implode('', $cols) === '') continue;

            // Skip header (baris pertama jika kolom 0 = "No" atau kolom 1 = "KODE")
            $col0 = strtolower(trim($cols[0] ?? ''));
            $col1 = strtolower(trim($cols[1] ?? ''));
            if ($rowNum === 1 && ($col0 === 'no' || $col1 === 'kode')) {
                continue;
            }

            // Format: No(0), Kode(1), Judul(2), ISBN(3), Pengarang(4), Penerbit(5), Harga(6), URL Thumbnail(7), URL Shopee(8)
            $no         = trim($cols[0] ?? '');
            $kode       = trim($cols[1] ?? '');
            $judul      = trim($cols[2] ?? '');
            $isbn       = trim($cols[3] ?? '');
            $pengarang  = trim($cols[4] ?? '');
            $penerbit   = trim($cols[5] ?? '');
            $harga      = trim($cols[6] ?? '');
            $urlThumb   = trim($cols[7] ?? '');
            $urlShopee  = trim($cols[8] ?? '');

            // Validasi wajib: kode dan judul
            if ($kode === '') {
                $errors[] = "Baris {$rowNum}: Kode kosong.";
                continue;
            }
            if ($judul === '') {
                $errors[] = "Baris {$rowNum}: Judul kosong.";
                continue;
            }

            // Bersihkan harga (hapus koma, titik ribuan)
            $hargaClean = preg_replace('/[^0-9.]/', '', str_replace(',', '', $harga));

            $this->katalogBukuModel->insert([
                'kode'          => $kode,
                'judul'         => $judul,
                'isbn'          => $isbn ?: null,
                'pengarang'     => $pengarang ?: null,
                'penerbit'      => $penerbit ?: null,
                'harga'         => $hargaClean !== '' ? (float) $hargaClean : null,
                'url_thumbnail' => $urlThumb ?: null,
                'url_shopee'    => $urlShopee ?: null,
                'is_active'     => 0,
                'is_highlight'  => 0,
                'urutan'        => $imported + 1,
            ]);
            $imported++;
        }

        fclose($handle);
        @unlink($filePath);

        if (! empty($errors)) {
            return redirect()->back()
                ->with('import_errors', $errors)
                ->with('total_imported', $imported);
        }

        return redirect()->to(base_url('admin/katalog-buku'))
            ->with('success', "{$imported} buku berhasil diimport.");
    }
}
