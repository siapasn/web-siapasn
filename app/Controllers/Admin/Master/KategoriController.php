<?php

namespace App\Controllers\Admin\Master;

use App\Controllers\BaseController;
use App\Models\KategoriModel;

class KategoriController extends BaseController
{
    protected KategoriModel $kategoriModel;

    public function __construct()
    {
        $this->kategoriModel = new KategoriModel();
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
     * Daftar semua kategori beserta nama parent.
     */
    public function index()
    {
        $kategoris = $this->kategoriModel->getWithParent();

        // Hitung jumlah sub-kategori per kategori
        $db = \Config\Database::connect();
        $childCounts = [];
        $rows = $db->table('kategori')
            ->select('parent_id, COUNT(*) as jumlah')
            ->where('parent_id IS NOT NULL', null, false)
            ->groupBy('parent_id')
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $childCounts[(int) $row['parent_id']] = (int) $row['jumlah'];
        }

        return view('admin/master/kategori/index', [
            'kategoris'   => $kategoris,
            'childCounts' => $childCounts,
            'menus'       => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah kategori.
     */
    public function create()
    {
        return view('admin/master/kategori/form', [
            'kategori' => null,
            'parents'  => $this->kategoriModel->getParents(),
            'action'   => base_url('admin/master/kategori/store'),
            'menus'    => $this->getMenus(),
        ]);
    }

    /**
     * Simpan kategori baru.
     */
    public function store()
    {
        $rules = [
            'nama'      => 'required|min_length[2]|max_length[100]',
            'parent_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $parentId  = $this->request->getPost('parent_id');
        $tipeSoal  = $this->request->getPost('tipe_soal');

        // Cek kedalaman: jika parent_id dipilih, pastikan parent tersebut tidak punya parent (max 1 level)
        if ($parentId !== '' && $parentId !== null) {
            $parentKategori = $this->kategoriModel->find((int) $parentId);
            if ($parentKategori && $parentKategori['parent_id'] !== null) {
                return redirect()->back()->withInput()
                    ->with('errors', ['parent_id' => 'Sub-kategori hanya diperbolehkan 1 tingkat. Kategori yang dipilih sudah merupakan sub-kategori.']);
            }
            // Tipe soal wajib jika sub-kategori
            if (empty($tipeSoal) || ! in_array($tipeSoal, ['SCORE', 'POINT'])) {
                return redirect()->back()->withInput()
                    ->with('errors', ['tipe_soal' => 'Tipe Soal wajib dipilih (SCORE atau POINT) untuk sub-kategori.']);
            }
        }

        $this->kategoriModel->insert([
            'nama'      => $this->request->getPost('nama'),
            'parent_id' => ($parentId !== '' && $parentId !== null) ? (int) $parentId : null,
            'tipe_soal' => ($parentId !== '' && $parentId !== null) ? $tipeSoal : null,
        ]);

        return redirect()->to(base_url('admin/master/kategori'))->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Form edit kategori.
     */
    public function edit(int $id)
    {
        $kategori = $this->kategoriModel->find($id);

        if (! $kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori tidak ditemukan.');
        }

        return view('admin/master/kategori/form', [
            'kategori' => $kategori,
            'parents'  => $this->kategoriModel->getParents(),
            'action'   => base_url("admin/master/kategori/{$id}/update"),
            'menus'    => $this->getMenus(),
        ]);
    }

    /**
     * Update kategori.
     */
    public function update(int $id)
    {
        $kategori = $this->kategoriModel->find($id);

        if (! $kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori tidak ditemukan.');
        }

        $rules = [
            'nama'      => 'required|min_length[2]|max_length[100]',
            'parent_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $parentId = $this->request->getPost('parent_id');
        $tipeSoal = $this->request->getPost('tipe_soal');

        // Cek kedalaman: jika parent_id dipilih, pastikan parent tersebut tidak punya parent (max 1 level)
        if ($parentId !== '' && $parentId !== null) {
            $parentKategori = $this->kategoriModel->find((int) $parentId);
            if ($parentKategori && $parentKategori['parent_id'] !== null) {
                return redirect()->back()->withInput()
                    ->with('errors', ['parent_id' => 'Sub-kategori hanya diperbolehkan 1 tingkat. Kategori yang dipilih sudah merupakan sub-kategori.']);
            }
            // Tipe soal wajib jika sub-kategori
            if (empty($tipeSoal) || ! in_array($tipeSoal, ['SCORE', 'POINT'])) {
                return redirect()->back()->withInput()
                    ->with('errors', ['tipe_soal' => 'Tipe Soal wajib dipilih (SCORE atau POINT) untuk sub-kategori.']);
            }
        }

        $this->kategoriModel->update($id, [
            'nama'      => $this->request->getPost('nama'),
            'parent_id' => ($parentId !== '' && $parentId !== null) ? (int) $parentId : null,
            'tipe_soal' => ($parentId !== '' && $parentId !== null) ? $tipeSoal : null,
        ]);

        return redirect()->to(base_url('admin/master/kategori'))->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori.
     * Dicegah jika masih memiliki sub-kategori atau soal yang menggunakannya.
     */
    public function delete(int $id)
    {
        $kategori = $this->kategoriModel->find($id);

        if (! $kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        // Cek apakah masih ada sub-kategori
        $hasChildren = $db->table('kategori')
            ->where('parent_id', $id)
            ->countAllResults() > 0;

        if ($hasChildren) {
            return redirect()->to(base_url('admin/master/kategori'))
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki sub-kategori.');
        }

        // Cek apakah masih ada soal yang menggunakan kategori ini
        $hasSoal = $db->table('soal')
            ->where('kategori_id', $id)
            ->countAllResults() > 0;

        if ($hasSoal) {
            return redirect()->to(base_url('admin/master/kategori'))
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh soal.');
        }

        $this->kategoriModel->delete($id);

        return redirect()->to(base_url('admin/master/kategori'))->with('success', 'Kategori berhasil dihapus.');
    }
}
