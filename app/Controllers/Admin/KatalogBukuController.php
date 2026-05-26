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
            'judul'         => 'required|max_length[255]',
            'url_thumbnail' => 'required',
            'url_shopee'    => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->katalogBukuModel->insert([
            'judul'         => $this->request->getPost('judul'),
            'url_thumbnail' => $this->request->getPost('url_thumbnail'),
            'url_shopee'    => $this->request->getPost('url_shopee'),
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
            'judul'         => 'required|max_length[255]',
            'url_thumbnail' => 'required',
            'url_shopee'    => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->katalogBukuModel->update($id, [
            'judul'         => $this->request->getPost('judul'),
            'url_thumbnail' => $this->request->getPost('url_thumbnail'),
            'url_shopee'    => $this->request->getPost('url_shopee'),
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
     * Format: judul, url_thumbnail, url_shopee
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

            // Skip header (baris pertama jika kolom 0 bukan URL dan bukan judul valid)
            $col0 = trim($cols[0] ?? '');
            if ($rowNum === 1 && (stripos($col0, 'judul') !== false || stripos($col0, 'no') !== false)) {
                continue;
            }

            // Deteksi format: bisa "judul, url_thumbnail, url_shopee" atau "no, judul, url_thumbnail, url_shopee"
            if (count($cols) >= 4 && is_numeric(trim($cols[0]))) {
                // Format: No, Judul, URL Thumbnail, URL Shopee
                $judul    = trim($cols[1] ?? '');
                $urlThumb = trim($cols[2] ?? '');
                $urlShopee = trim($cols[3] ?? '');
            } elseif (count($cols) >= 3) {
                // Format: Judul, URL Thumbnail, URL Shopee
                $judul    = trim($cols[0] ?? '');
                $urlThumb = trim($cols[1] ?? '');
                $urlShopee = trim($cols[2] ?? '');
            } else {
                $errors[] = "Baris {$rowNum}: Kolom tidak lengkap (minimal 3 kolom).";
                continue;
            }

            if ($judul === '') {
                $errors[] = "Baris {$rowNum}: Judul kosong.";
                continue;
            }
            if ($urlThumb === '') {
                $errors[] = "Baris {$rowNum}: URL Thumbnail kosong.";
                continue;
            }
            if ($urlShopee === '') {
                $errors[] = "Baris {$rowNum}: URL Shopee kosong.";
                continue;
            }

            $this->katalogBukuModel->insert([
                'judul'         => $judul,
                'url_thumbnail' => $urlThumb,
                'url_shopee'    => $urlShopee,
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
