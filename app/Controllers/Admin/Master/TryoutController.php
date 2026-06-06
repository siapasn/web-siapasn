<?php

namespace App\Controllers\Admin\Master;

use App\Controllers\BaseController;
use App\Models\TryoutModel;

class TryoutController extends BaseController
{
    protected TryoutModel $tryoutModel;

    public function __construct()
    {
        $this->tryoutModel = new TryoutModel();
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
     * Daftar semua tryout — jumlah soal dihitung dari mapping_soal.
     */
    public function index()
    {
        $db      = \Config\Database::connect();
        $tryouts = $this->tryoutModel->orderBy('id', 'DESC')->findAll();

        // Hitung jumlah soal aktual dari mapping_soal per tryout
        foreach ($tryouts as &$t) {
            $t['jumlah_soal_mapped'] = $db->table('mapping_soal')
                ->where('tryout_id', $t['id'])
                ->countAllResults();
        }
        unset($t);

        return view('admin/master/tryout/index', [
            'tryouts' => $tryouts,
            'menus'   => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah tryout.
     */
    public function create()
    {
        return view('admin/master/tryout/form', [
            'tryout' => null,
            'action' => base_url('admin/master/tryout/store'),
            'menus'  => $this->getMenus(),
        ]);
    }

    /**
     * Simpan tryout baru.
     */
    public function store()
    {
        $rules = [
            'nama'   => 'required',
            'durasi' => 'required|integer|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        helper('slug');
        $slug = make_unique_slug('tryout', $this->request->getPost('nama'));

        $this->tryoutModel->insert([
            'nama'      => $this->request->getPost('nama'),
            'slug'      => $slug,
            'durasi'    => (int) $this->request->getPost('durasi'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to(base_url('admin/master/tryout'))->with('success', 'Tryout berhasil ditambahkan.');
    }

    /**
     * Form edit tryout.
     */
    public function edit(int $id)
    {
        $tryout = $this->tryoutModel->find($id);

        if (! $tryout) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tryout tidak ditemukan.');
        }

        return view('admin/master/tryout/form', [
            'tryout' => $tryout,
            'action' => base_url("admin/master/tryout/{$id}/update"),
            'menus'  => $this->getMenus(),
        ]);
    }

    /**
     * Update tryout.
     */
    public function update(int $id)
    {
        $tryout = $this->tryoutModel->find($id);

        if (! $tryout) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tryout tidak ditemukan.');
        }

        $rules = [
            'nama'   => 'required',
            'durasi' => 'required|integer|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        helper('slug');
        $slug = make_unique_slug('tryout', $this->request->getPost('nama'), $id);

        $this->tryoutModel->update($id, [
            'nama'      => $this->request->getPost('nama'),
            'slug'      => $slug,
            'durasi'    => (int) $this->request->getPost('durasi'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to(base_url('admin/master/tryout'))->with('success', 'Tryout berhasil diperbarui.');
    }

    /**
     * Hapus tryout.
     * Dicegah jika tryout masih digunakan di mapping_tryout atau sesi_tryout.
     */
    public function delete(int $id)
    {
        $tryout = $this->tryoutModel->find($id);

        if (! $tryout) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tryout tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        // Cek apakah tryout masih digunakan di mapping_tryout
        $inMapping = $db->table('mapping_tryout')
            ->where('tryout_id', $id)
            ->countAllResults() > 0;

        if ($inMapping) {
            return redirect()->to(base_url('admin/master/tryout'))
                ->with('error', 'Tryout tidak dapat dihapus karena masih digunakan dalam mapping produk.');
        }

        // Cek apakah tryout masih memiliki sesi_tryout
        $inSesi = $db->table('sesi_tryout')
            ->where('tryout_id', $id)
            ->countAllResults() > 0;

        if ($inSesi) {
            return redirect()->to(base_url('admin/master/tryout'))
                ->with('error', 'Tryout tidak dapat dihapus karena sudah memiliki sesi tryout yang dikerjakan.');
        }

        $this->tryoutModel->delete($id);

        return redirect()->to(base_url('admin/master/tryout'))->with('success', 'Tryout berhasil dihapus.');
    }

    /**
     * Preview soal beserta kunci jawaban dan pembahasan.
     * Dibuka di tab baru dari halaman index tryout.
     */
    public function previewSoal(int $id)
    {
        $tryout = $this->tryoutModel->find($id);

        if (! $tryout) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tryout tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        $soalList = $db->table('mapping_soal ms')
            ->select('ms.urutan, s.id as soal_id, s.pertanyaan,
                      s.pilihan_a, s.pilihan_b, s.pilihan_c, s.pilihan_d, s.pilihan_e,
                      s.kunci_jawaban, s.pembahasan,
                      s.nilai_a, s.nilai_b, s.nilai_c, s.nilai_d, s.nilai_e,
                      s.kategori_id, k.nama AS kategori_nama, k.tipe_soal')
            ->join('soal s', 's.id = ms.soal_id')
            ->join('kategori k', 'k.id = s.kategori_id', 'left')
            ->where('ms.tryout_id', $id)
            ->orderBy('ms.urutan', 'ASC')
            ->get()->getResultArray();

        return view('admin/master/tryout/preview-soal', [
            'tryout'   => $tryout,
            'soalList' => $soalList,
        ]);
    }
}
