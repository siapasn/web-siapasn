<?php

namespace App\Controllers\Admin\Master;

use App\Controllers\BaseController;
use App\Models\KategoriModel;
use App\Models\PassingGradeModel;

class PassingGradeController extends BaseController
{
    protected PassingGradeModel $passingGradeModel;
    protected KategoriModel     $kategoriModel;

    public function __construct()
    {
        $this->passingGradeModel = new PassingGradeModel();
        $this->kategoriModel     = new KategoriModel();
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
     * Daftar semua passing grade beserta nama tryout dan kategori.
     */
    public function index()
    {
        $passingGrades = $this->passingGradeModel->getWithRelations();

        return view('admin/master/passing-grade/index', [
            'passingGrades' => $passingGrades,
            'menus'         => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah passing grade.
     */
    public function create()
    {
        return view('admin/master/passing-grade/form', [
            'passingGrade' => null,
            'action'       => base_url('admin/master/passing-grade/store'),
            'kategoris'    => $this->kategoriModel->getAll(),
            'subKategoris' => [],
            'menus'        => $this->getMenus(),
        ]);
    }

    /**
     * GET AJAX: Cek apakah kombinasi kategori+sub sudah ada.
     * Digunakan oleh form sebelum submit untuk konfirmasi.
     */
    public function checkDuplicate()
    {
        $kategoriId    = $this->request->getGet('kategori_id');
        $subKategoriId = $this->request->getGet('sub_kategori_id');
        $excludeId     = $this->request->getGet('exclude_id');

        if (empty($kategoriId) || ! is_numeric($kategoriId)) {
            return $this->response->setJSON(['exists' => false]);
        }

        $subId = ($subKategoriId !== '' && $subKategoriId !== null && is_numeric($subKategoriId))
                 ? (int) $subKategoriId : null;
        $excId = ($excludeId !== '' && $excludeId !== null && is_numeric($excludeId))
                 ? (int) $excludeId : null;

        $existing = $this->passingGradeModel->findByKategoriAndSub((int) $kategoriId, $subId, $excId);

        if ($existing) {
            $db     = \Config\Database::connect();
            $katRow = $db->table('kategori')->select('nama')->where('id', (int) $kategoriId)->get()->getRowArray();
            $subRow = $subId ? $db->table('kategori')->select('nama')->where('id', $subId)->get()->getRowArray() : null;

            return $this->response->setJSON([
                'exists'        => true,
                'nilai_minimum' => $existing['nilai_minimum'],
                'nama_kategori' => $katRow['nama'] ?? '',
                'nama_sub'      => $subRow['nama'] ?? 'Semua Sub Kategori',
            ]);
        }

        return $this->response->setJSON(['exists' => false]);
    }

    /**
     * Simpan passing grade baru, atau update jika kombinasi kategori+sub sudah ada.
     */
    public function store()
    {
        $rules = [
            'kategori_id'   => 'required|is_natural_no_zero',
            'nilai_minimum' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $kategoriId    = (int) $this->request->getPost('kategori_id');
        $subKategoriId = $this->request->getPost('sub_kategori_id');
        $subKategoriId = ($subKategoriId !== '' && $subKategoriId !== null) ? (int) $subKategoriId : null;
        $nilaiMinimumRaw = $this->request->getPost('nilai_minimum');
        $nilaiMinimum  = ($nilaiMinimumRaw !== '' && $nilaiMinimumRaw !== null) ? (float) $nilaiMinimumRaw : null;

        // Upsert: update jika kombinasi sudah ada, insert jika belum
        $existing = $this->passingGradeModel->findByKategoriAndSub($kategoriId, $subKategoriId);

        if ($existing) {
            $this->passingGradeModel->update($existing['id'], [
                'nilai_minimum' => $nilaiMinimum,
            ]);
            return redirect()->to(base_url('admin/master/passing-grade'))
                ->with('success', 'Passing grade berhasil diperbarui.');
        }

        $this->passingGradeModel->insert([
            'tryout_id'       => null,
            'kategori_id'     => $kategoriId,
            'sub_kategori_id' => $subKategoriId,
            'nilai_minimum'   => $nilaiMinimum,
        ]);

        return redirect()->to(base_url('admin/master/passing-grade'))
            ->with('success', 'Passing grade berhasil ditambahkan.');
    }

    /**
     * Form edit passing grade.
     */
    public function edit(int $id)
    {
        $passingGrade = $this->passingGradeModel->find($id);

        if (! $passingGrade) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Passing grade tidak ditemukan.');
        }

        // Load sub-kategori jika kategori sudah dipilih
        $subKategoris = ! empty($passingGrade['kategori_id'])
            ? $this->kategoriModel->getChildren((int) $passingGrade['kategori_id'])
            : [];

        return view('admin/master/passing-grade/form', [
            'passingGrade' => $passingGrade,
            'action'       => base_url("admin/master/passing-grade/{$id}/update"),
            'kategoris'    => $this->kategoriModel->getAll(),
            'subKategoris' => $subKategoris,
            'menus'        => $this->getMenus(),
        ]);
    }

    /**
     * Update passing grade.
     */
    public function update(int $id)
    {
        $passingGrade = $this->passingGradeModel->find($id);

        if (! $passingGrade) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Passing grade tidak ditemukan.');
        }

        $rules = [
            'kategori_id'   => 'required|is_natural_no_zero',
            'nilai_minimum' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $kategoriId      = (int) $this->request->getPost('kategori_id');
        $subKategoriId   = $this->request->getPost('sub_kategori_id');
        $nilaiMinimumRaw = $this->request->getPost('nilai_minimum');

        $this->passingGradeModel->update($id, [
            'tryout_id'       => null,
            'kategori_id'     => $kategoriId,
            'sub_kategori_id' => ($subKategoriId !== '' && $subKategoriId !== null) ? (int) $subKategoriId : null,
            'nilai_minimum'   => ($nilaiMinimumRaw !== '' && $nilaiMinimumRaw !== null) ? (float) $nilaiMinimumRaw : null,
        ]);

        return redirect()->to(base_url('admin/master/passing-grade'))->with('success', 'Passing grade berhasil diperbarui.');
    }

    /**
     * Hapus passing grade.
     */
    public function delete(int $id)
    {
        $passingGrade = $this->passingGradeModel->find($id);

        if (! $passingGrade) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Passing grade tidak ditemukan.');
        }

        $this->passingGradeModel->delete($id);

        return redirect()->to(base_url('admin/master/passing-grade'))->with('success', 'Passing grade berhasil dihapus.');
    }
}
