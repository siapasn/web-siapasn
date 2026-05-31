<?php

namespace App\Controllers\Admin\Master;

use App\Controllers\BaseController;
use App\Models\KategoriModel;
use App\Models\SoalModel;

class SoalController extends BaseController
{
    protected SoalModel     $soalModel;
    protected KategoriModel $kategoriModel;

    public function __construct()
    {
        $this->soalModel     = new SoalModel();
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
     * Daftar soal dengan DataTables, filter by kategori_id.
     * Join dengan kategori untuk nama.
     */
    public function index()
    {
        $kategoriId = $this->request->getGet('kategori_id') ?? '';

        $db      = \Config\Database::connect();
        $builder = $db->table('soal s')
            ->select('s.*, k.nama AS nama_kategori, k.tipe_soal AS tipe_soal')
            ->join('kategori k', 'k.id = s.kategori_id', 'left')
            ->orderBy('s.id', 'DESC');

        if ($kategoriId !== '') {
            $builder->where('s.kategori_id', (int) $kategoriId);
        }

        $soals     = $builder->get()->getResultArray();
        $kategoris = $this->kategoriModel->getAll();

        return view('admin/master/soal/index', [
            'soals'       => $soals,
            'kategoris'   => $kategoris,
            'kategori_id' => $kategoriId,
            'menus'       => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah soal.
     */
    public function create()
    {
        $tryouts = \Config\Database::connect()
            ->table('tryout')
            ->select('id, nama')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();

        return view('admin/master/soal/form', [
            'soal'      => null,
            'tipeSoal'  => null,
            'action'    => base_url('admin/master/soal/store'),
            'kategoris' => $this->kategoriModel->getAll(),
            'tryouts'   => $tryouts,
            'menus'     => $this->getMenus(),
        ]);
    }

    /**
     * Simpan soal baru.
     */
    public function store()
    {
        $rules = [
            'pertanyaan'    => 'required',
            'pilihan_a'     => 'required',
            'pilihan_b'     => 'required',
            'pilihan_c'     => 'required',
            'pilihan_d'     => 'required',
            'kategori_id'   => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $kategoriId = (int) $this->request->getPost('kategori_id');

        // Tentukan tipe soal dari kategori yang dipilih
        $kat      = $this->kategoriModel->find($kategoriId);
        $tipeSoal = $kat['tipe_soal'] ?? null;

        // Validasi berdasarkan tipe soal
        if ($tipeSoal === 'SCORE') {
            // SCORE: kunci jawaban wajib (pilihan ganda A/B/C/D/E)
            if (empty($this->request->getPost('kunci_jawaban'))) {
                return redirect()->back()->withInput()
                    ->with('errors', ['kunci_jawaban' => 'Kunci jawaban wajib dipilih.']);
            }
        } else if ($tipeSoal === 'POINT') {
            // POINT: wajib isi nilai A-E, angka 1-5, tidak boleh sama
            $nilaiFields = ['nilai_a', 'nilai_b', 'nilai_c', 'nilai_d', 'nilai_e'];
            $nilaiValues = [];
            foreach ($nilaiFields as $field) {
                $val = $this->request->getPost($field);
                if ($val === '' || $val === null || ! in_array((int)$val, [1,2,3,4,5])) {
                    return redirect()->back()->withInput()
                        ->with('errors', [$field => 'Nilai ' . strtoupper(str_replace('nilai_', '', $field)) . ' wajib diisi dengan angka 1-5.']);
                }
                if (in_array((int)$val, $nilaiValues)) {
                    return redirect()->back()->withInput()
                        ->with('errors', [$field => 'Nilai setiap pilihan tidak boleh sama.']);
                }
                $nilaiValues[] = (int)$val;
            }
        } else {
            // Tipe tidak diset: wajib kunci jawaban sebagai default
            if (empty($this->request->getPost('kunci_jawaban'))) {
                return redirect()->back()->withInput()
                    ->with('errors', ['kunci_jawaban' => 'Kunci jawaban wajib dipilih.']);
            }
        }

        $this->soalModel->insert([
            'kategori_id'     => $kategoriId,
            'pertanyaan'      => $this->request->getPost('pertanyaan'),
            'pilihan_a'       => $this->request->getPost('pilihan_a'),
            'pilihan_b'       => $this->request->getPost('pilihan_b'),
            'pilihan_c'       => $this->request->getPost('pilihan_c'),
            'pilihan_d'       => $this->request->getPost('pilihan_d'),
            'pilihan_e'       => $this->request->getPost('pilihan_e') ?? null,
            'nilai_a'         => $tipeSoal === 'POINT' ? (int) $this->request->getPost('nilai_a') : null,
            'nilai_b'         => $tipeSoal === 'POINT' ? (int) $this->request->getPost('nilai_b') : null,
            'nilai_c'         => $tipeSoal === 'POINT' ? (int) $this->request->getPost('nilai_c') : null,
            'nilai_d'         => $tipeSoal === 'POINT' ? (int) $this->request->getPost('nilai_d') : null,
            'nilai_e'         => $tipeSoal === 'POINT' ? (int) $this->request->getPost('nilai_e') : null,
            'kunci_jawaban'   => $tipeSoal !== 'POINT' ? $this->request->getPost('kunci_jawaban') : null,
            'pembahasan'      => $this->request->getPost('pembahasan') ?? null,
        ]);

        $soalId = $this->soalModel->getInsertID();

        // Jika tryout dipilih, langsung mapping soal ke tryout
        $tryoutId = $this->request->getPost('tryout_id');
        if (! empty($tryoutId) && is_numeric($tryoutId)) {
            $tryoutId = (int) $tryoutId;
            $db = \Config\Database::connect();
            // Cek duplikasi sebelum insert
            $isDup = $db->table('mapping_soal')
                ->where('tryout_id', $tryoutId)
                ->where('soal_id', $soalId)
                ->countAllResults() > 0;

            if (! $isDup) {
                $maxRow = $db->table('mapping_soal')
                    ->selectMax('urutan')
                    ->where('tryout_id', $tryoutId)
                    ->get()->getRowArray();
                $nextUrutan = ($maxRow && $maxRow['urutan'] !== null) ? (int) $maxRow['urutan'] + 1 : 1;

                $db->table('mapping_soal')->insert([
                    'tryout_id'  => $tryoutId,
                    'soal_id'    => $soalId,
                    'urutan'     => $nextUrutan,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()->to(base_url('admin/master/soal'))->with('success', 'Soal berhasil ditambahkan.');
    }

    /**
     * Form edit soal.
     */
    public function edit(int $id)
    {
        $soal = $this->soalModel->find($id);

        if (! $soal) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Soal tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        $tryouts = $db->table('tryout')
            ->select('id, nama')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();

        // Ambil tryout yang sudah di-mapping ke soal ini (untuk pre-select)
        $mappedTryoutIds = array_column(
            $db->table('mapping_soal')->select('tryout_id')->where('soal_id', $id)->get()->getResultArray(),
            'tryout_id'
        );

        return view('admin/master/soal/form', [
            'soal'           => $soal,
            'tipeSoal'       => $this->getTipeSoalFromSoal($soal),
            'action'         => base_url("admin/master/soal/{$id}/update"),
            'kategoris'      => $this->kategoriModel->getAll(),
            'tryouts'        => $tryouts,
            'mappedTryoutIds'=> $mappedTryoutIds,
            'menus'          => $this->getMenus(),
        ]);
    }

    /**
     * Update soal.
     */
    public function update(int $id)
    {
        $soal = $this->soalModel->find($id);

        if (! $soal) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Soal tidak ditemukan.');
        }

        $rules = [
            'pertanyaan'    => 'required',
            'pilihan_a'     => 'required',
            'pilihan_b'     => 'required',
            'pilihan_c'     => 'required',
            'pilihan_d'     => 'required',
            'kategori_id'   => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $kategoriId = (int) $this->request->getPost('kategori_id');

        // Tentukan tipe soal dari kategori yang dipilih
        $kat      = $this->kategoriModel->find($kategoriId);
        $tipeSoal = $kat['tipe_soal'] ?? null;

        // Validasi berdasarkan tipe soal
        if ($tipeSoal === 'SCORE') {
            // SCORE: kunci jawaban wajib (pilihan ganda A/B/C/D/E)
            if (empty($this->request->getPost('kunci_jawaban'))) {
                return redirect()->back()->withInput()
                    ->with('errors', ['kunci_jawaban' => 'Kunci jawaban wajib dipilih.']);
            }
        } else if ($tipeSoal === 'POINT') {
            // POINT: wajib isi nilai A-E, angka 1-5, tidak boleh sama
            $nilaiFields = ['nilai_a', 'nilai_b', 'nilai_c', 'nilai_d', 'nilai_e'];
            $nilaiValues = [];
            foreach ($nilaiFields as $field) {
                $val = $this->request->getPost($field);
                if ($val === '' || $val === null || ! in_array((int)$val, [1,2,3,4,5])) {
                    return redirect()->back()->withInput()
                        ->with('errors', [$field => 'Nilai ' . strtoupper(str_replace('nilai_', '', $field)) . ' wajib diisi dengan angka 1-5.']);
                }
                if (in_array((int)$val, $nilaiValues)) {
                    return redirect()->back()->withInput()
                        ->with('errors', [$field => 'Nilai setiap pilihan tidak boleh sama.']);
                }
                $nilaiValues[] = (int)$val;
            }
        } else {
            // Tipe tidak diset: wajib kunci jawaban sebagai default
            if (empty($this->request->getPost('kunci_jawaban'))) {
                return redirect()->back()->withInput()
                    ->with('errors', ['kunci_jawaban' => 'Kunci jawaban wajib dipilih.']);
            }
        }

        $this->soalModel->update($id, [
            'kategori_id'     => $kategoriId,
            'pertanyaan'      => $this->request->getPost('pertanyaan'),
            'pilihan_a'       => $this->request->getPost('pilihan_a'),
            'pilihan_b'       => $this->request->getPost('pilihan_b'),
            'pilihan_c'       => $this->request->getPost('pilihan_c'),
            'pilihan_d'       => $this->request->getPost('pilihan_d'),
            'pilihan_e'       => $this->request->getPost('pilihan_e') ?? null,
            'nilai_a'         => $tipeSoal === 'POINT' ? (int) $this->request->getPost('nilai_a') : null,
            'nilai_b'         => $tipeSoal === 'POINT' ? (int) $this->request->getPost('nilai_b') : null,
            'nilai_c'         => $tipeSoal === 'POINT' ? (int) $this->request->getPost('nilai_c') : null,
            'nilai_d'         => $tipeSoal === 'POINT' ? (int) $this->request->getPost('nilai_d') : null,
            'nilai_e'         => $tipeSoal === 'POINT' ? (int) $this->request->getPost('nilai_e') : null,
            'kunci_jawaban'   => $tipeSoal !== 'POINT' ? $this->request->getPost('kunci_jawaban') : null,
            'pembahasan'      => $this->request->getPost('pembahasan') ?? null,
        ]);

        // Jika tryout dipilih, tambahkan mapping jika belum ada
        $tryoutId = $this->request->getPost('tryout_id');
        if (! empty($tryoutId) && is_numeric($tryoutId)) {
            $tryoutId = (int) $tryoutId;
            $db = \Config\Database::connect();
            $isDup = $db->table('mapping_soal')
                ->where('tryout_id', $tryoutId)
                ->where('soal_id', $id)
                ->countAllResults() > 0;

            if (! $isDup) {
                $maxRow = $db->table('mapping_soal')
                    ->selectMax('urutan')
                    ->where('tryout_id', $tryoutId)
                    ->get()->getRowArray();
                $nextUrutan = ($maxRow && $maxRow['urutan'] !== null) ? (int) $maxRow['urutan'] + 1 : 1;

                $db->table('mapping_soal')->insert([
                    'tryout_id'  => $tryoutId,
                    'soal_id'    => $id,
                    'urutan'     => $nextUrutan,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()->to(base_url('admin/master/soal'))->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * Hapus soal.
     * Dicegah jika soal masih digunakan di mapping_soal.
     */
    public function delete(int $id)
    {
        $soal = $this->soalModel->find($id);

        if (! $soal) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Soal tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        // Cek apakah soal masih digunakan di mapping_soal
        $isUsed = $db->table('mapping_soal')
            ->where('soal_id', $id)
            ->countAllResults() > 0;

        if ($isUsed) {
            return redirect()->to(base_url('admin/master/soal'))
                ->with('error', 'Soal tidak dapat dihapus karena masih digunakan dalam mapping tryout.');
        }

        $this->soalModel->delete($id);

        return redirect()->to(base_url('admin/master/soal'))->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * GET AJAX: Ambil tipe_soal dari kategori_id.
     * URL: admin/master/soal/tipe-soal/{id}
     */
    public function getTipeSoal(int $kategoriId)
    {
        $kategori = $this->kategoriModel->find($kategoriId);
        return $this->response
            ->setHeader('Content-Type', 'application/json')
            ->setJSON([
                'status'    => (bool) $kategori,
                'tipe_soal' => $kategori['tipe_soal'] ?? null,
            ]);
    }

    /**
     * GET AJAX: Ambil sub-kategori berdasarkan parent kategori_id.
     * URL: admin/master/soal/sub-kategori/{id}
     */
    public function getSubKategori(int $kategoriId)
    {
        $subKategoris = $this->kategoriModel->getChildren($kategoriId);
        return $this->response
            ->setHeader('Content-Type', 'application/json')
            ->setJSON([
                'status' => true,
                'data'   => $subKategoris,
            ]);
    }

    /**
     * Helper: ambil tipe_soal dari kategori_id soal.
     */
    private function getTipeSoalFromSoal(?array $soal): ?string
    {
        if (! $soal || empty($soal['kategori_id'])) {
            return null;
        }
        $kat = $this->kategoriModel->find((int) $soal['kategori_id']);
        return $kat['tipe_soal'] ?? null;
    }

    /**
     * Form impor soal dari Excel/CSV.
     */
    public function import()
    {
        return view('admin/master/soal/import', [
            'menus' => $this->getMenus(),
        ]);
    }

    /**
     * Form salin soal antar tryout.
     */
    public function salinSoal()
    {
        $db      = \Config\Database::connect();
        $tryouts = $db->table('tryout')
            ->select('id, nama')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();

        return view('admin/master/soal/salin', [
            'tryouts' => $tryouts,
            'menus'   => $this->getMenus(),
        ]);
    }

    /**
     * Proses salin soal dari tryout sumber ke tryout tujuan.
     * Menyalin semua mapping_soal dari tryout sumber ke tryout tujuan.
     * Soal yang sudah ada di tryout tujuan dilewati (tidak duplikat).
     */
    public function salinSoalProcess()
    {
        $tryoutSumberId  = (int) $this->request->getPost('tryout_sumber_id');
        $tryoutTujuanId  = (int) $this->request->getPost('tryout_tujuan_id');

        if ($tryoutSumberId <= 0 || $tryoutTujuanId <= 0) {
            return redirect()->back()->withInput()
                ->with('error', 'Tryout sumber dan tujuan wajib dipilih.');
        }

        if ($tryoutSumberId === $tryoutTujuanId) {
            return redirect()->back()->withInput()
                ->with('error', 'Tryout sumber dan tujuan tidak boleh sama.');
        }

        $db = \Config\Database::connect();

        // Ambil semua soal dari tryout sumber
        $soalSumber = $db->table('mapping_soal')
            ->select('soal_id')
            ->where('tryout_id', $tryoutSumberId)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();

        if (empty($soalSumber)) {
            return redirect()->back()->withInput()
                ->with('error', 'Tryout sumber tidak memiliki soal yang bisa disalin.');
        }

        // Ambil soal yang sudah ada di tryout tujuan (untuk skip duplikat)
        $soalTujuanIds = array_column(
            $db->table('mapping_soal')
                ->select('soal_id')
                ->where('tryout_id', $tryoutTujuanId)
                ->get()->getResultArray(),
            'soal_id'
        );

        // Ambil urutan terakhir di tryout tujuan
        $maxRow     = $db->table('mapping_soal')
            ->selectMax('urutan')
            ->where('tryout_id', $tryoutTujuanId)
            ->get()->getRowArray();
        $nextUrutan = ($maxRow && $maxRow['urutan'] !== null) ? (int) $maxRow['urutan'] + 1 : 1;

        $copied  = 0;
        $skipped = 0;

        $db->transStart();
        foreach ($soalSumber as $row) {
            $soalId = (int) $row['soal_id'];
            if (in_array($soalId, $soalTujuanIds)) {
                $skipped++;
                continue;
            }
            $db->table('mapping_soal')->insert([
                'tryout_id'  => $tryoutTujuanId,
                'soal_id'    => $soalId,
                'urutan'     => $nextUrutan++,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $copied++;
        }
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyalin soal. Silakan coba lagi.');
        }

        $msg = "{$copied} soal berhasil disalin ke tryout tujuan.";
        if ($skipped > 0) {
            $msg .= " {$skipped} soal dilewati karena sudah ada di tryout tujuan.";
        }

        return redirect()->to(base_url('admin/master/soal/salin'))
            ->with('success', $msg);
    }

    /**
     * Proses impor soal dari file CSV.
     * Format kolom template (A=index 0):
     *   A(0)  = No             (nomor urut, diabaikan)
     *   B(1)  = kategori_id *  (ID integer)
     *   C(2)  = pertanyaan *
     *   D(3)  = pilihan_a *
     *   E(4)  = pilihan_b *
     *   F(5)  = pilihan_c *
     *   G(6)  = pilihan_d *
     *   H(7)  = pilihan_e      (opsional)
     *   I(8)  = Kunci          (wajib jika tipe SCORE: a/b/c/d/e)
     *   J(9)  = nilai_a        (wajib jika tipe POINT: 1-5)
     *   K(10) = nilai_b
     *   L(11) = nilai_c
     *   M(12) = nilai_d
     *   N(13) = nilai_e
     *   O(14) = pembahasan     (opsional)
     *   P(15) = tryout_id      (opsional)
     */
    public function importProcess()
    {
        $file = $this->request->getFile('file_import');

        if (! $file || ! $file->isValid()) {
            $error = $file ? $file->getErrorString() : 'Tidak ada file yang diunggah.';
            // Debug: cek apakah ada file di $_FILES
            if (empty($_FILES)) {
                $error .= ' (Kemungkinan: post_max_size atau upload_max_filesize PHP terlalu kecil, atau form tidak memiliki enctype multipart/form-data)';
            } elseif (isset($_FILES['file_import'])) {
                $error .= ' (File error code: ' . $_FILES['file_import']['error'] . ')';
            }
            return redirect()->back()->with('error', $error);
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['csv'], true)) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan .csv (download template terbaru).');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return redirect()->back()->with('error', 'Ukuran file melebihi batas maksimum 5 MB.');
        }

        $tmpDir  = WRITEPATH . 'uploads/import/';
        $tmpName = $file->getRandomName();
        $file->move($tmpDir, $tmpName);
        $filePath = $tmpDir . $tmpName;

        // Buat lookup kategori: id => tipe_soal
        $db      = \Config\Database::connect();
        $katRows = $db->table('kategori')->select('id, tipe_soal')->get()->getResultArray();

        $kategoriTipeMap = []; // id => tipe_soal
        foreach ($katRows as $row) {
            $kategoriTipeMap[(int) $row['id']] = $row['tipe_soal'];
        }

        // Buat lookup tryout: id => true (untuk validasi)
        $tryoutIds = array_column(
            $db->table('tryout')->select('id')->get()->getResultArray(),
            'id'
        );
        $validTryoutIds = array_flip(array_map('intval', $tryoutIds));

        $errors       = [];
        $rowsToInsert = [];
        $validKunci   = ['a', 'b', 'c', 'd', 'e'];

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            @unlink($filePath);
            return redirect()->back()->with('error', 'Gagal membuka file CSV.');
        }

        // Baca BOM jika ada
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $rowNum = 0;
        while (($cols = fgetcsv($handle)) !== false) {
            $rowNum++;

            // Skip baris kosong
            if (implode('', $cols) === '') continue;

            // Kolom A (index 0) = No (nomor urut), kolom B (index 1) = kategori_id
            $colNo      = trim((string) ($cols[0] ?? ''));
            $colKatId   = trim((string) ($cols[1] ?? ''));

            // Skip baris header (kolom A bukan angka, misal "No")
            if (! is_numeric($colNo)) continue;

            // Skip jika kategori_id tidak valid
            if (! is_numeric($colKatId) || (int) $colKatId <= 0) continue;

            $kategoriId = (int) $colKatId;
            if (! isset($kategoriTipeMap[$kategoriId])) continue;

            $rowErrors = $this->validateImportRow($cols, $rowNum, $validKunci, $kategoriTipeMap, $validTryoutIds);
            if (! empty($rowErrors)) {
                $errors = array_merge($errors, $rowErrors);
            } else {
                $rowsToInsert[] = $this->buildImportRow($cols, $kategoriTipeMap);
            }
        }
        fclose($handle);
        @unlink($filePath);

        if (! empty($errors)) {
            return redirect()->back()
                ->with('import_errors', $errors)
                ->with('total_imported', 0);
        }

        $totalImported = 0;
        $db->transStart();
        foreach ($rowsToInsert as $row) {
            $tryoutId = $row['_tryout_id'] ?? null;
            unset($row['_tryout_id']);

            $this->soalModel->insert($row);
            $soalId = $this->soalModel->getInsertID();
            $totalImported++;

            // Mapping ke tryout jika tryout_id diisi
            if ($tryoutId && $soalId) {
                $isDup = $db->table('mapping_soal')
                    ->where('tryout_id', $tryoutId)
                    ->where('soal_id', $soalId)
                    ->countAllResults() > 0;

                if (! $isDup) {
                    $maxRow = $db->table('mapping_soal')
                        ->selectMax('urutan')
                        ->where('tryout_id', $tryoutId)
                        ->get()->getRowArray();
                    $nextUrutan = ($maxRow && $maxRow['urutan'] !== null) ? (int) $maxRow['urutan'] + 1 : 1;

                    $db->table('mapping_soal')->insert([
                        'tryout_id'  => $tryoutId,
                        'soal_id'    => $soalId,
                        'urutan'     => $nextUrutan,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()
                ->with('import_errors', ['Terjadi kesalahan saat menyimpan data ke database. Silakan coba lagi.'])
                ->with('total_imported', 0);
        }

        return redirect()->back()
            ->with('total_imported', $totalImported)
            ->with('import_errors', []);
    }

    /**
     * Validasi satu baris data impor.
     * Format template baru (dengan kolom No di index 0):
     *   0=No, 1=kategori_id, 2=pertanyaan, 3=pilihan_a, 4=pilihan_b,
     *   5=pilihan_c, 6=pilihan_d, 7=pilihan_e,
     *   8=Kunci (SCORE: a/b/c/d/e),
     *   9=nilai_a, 10=nilai_b, 11=nilai_c, 12=nilai_d, 13=nilai_e (POINT: 1-5),
     *   14=pembahasan, 15=tryout_id
     */
    public static function validateImportRow(
        array  $cols,
        int    $rowNum,
        array  $validKunci,
        array  $kategoriTipeMap = [],
        array  $validTryoutIds = []
    ): array {
        $errors = [];
        $prefix = "Baris {$rowNum}";

        $kategoriId = (int) trim((string) ($cols[1] ?? ''));
        $pertanyaan = trim((string) ($cols[2] ?? ''));
        $pilihanA   = trim((string) ($cols[3] ?? ''));
        $pilihanB   = trim((string) ($cols[4] ?? ''));
        $pilihanC   = trim((string) ($cols[5] ?? ''));
        $pilihanD   = trim((string) ($cols[6] ?? ''));

        if ($pertanyaan === '') $errors[] = "{$prefix}: Pertanyaan tidak boleh kosong.";
        if ($pilihanA   === '') $errors[] = "{$prefix}: Pilihan A tidak boleh kosong.";
        if ($pilihanB   === '') $errors[] = "{$prefix}: Pilihan B tidak boleh kosong.";
        if ($pilihanC   === '') $errors[] = "{$prefix}: Pilihan C tidak boleh kosong.";
        if ($pilihanD   === '') $errors[] = "{$prefix}: Pilihan D tidak boleh kosong.";

        if ($kategoriId <= 0 || ! isset($kategoriTipeMap[$kategoriId])) {
            $errors[] = "{$prefix}: kategori_id '{$kategoriId}' tidak valid.";
            return $errors;
        }

        $tipe = $kategoriTipeMap[$kategoriId] ?? '';

        // Fallback deteksi tipe dari kolom I (index 8) — Kunci
        if (! $tipe) {
            $colKunci = trim((string) ($cols[8] ?? ''));
            if (in_array(strtolower($colKunci), ['a','b','c','d','e'], true)) {
                $tipe = 'SCORE'; // ada kunci jawaban → SCORE (pilihan ganda)
            } elseif (is_numeric($cols[9] ?? '') && in_array((int)($cols[9] ?? ''), [1,2,3,4,5])) {
                $tipe = 'POINT'; // ada nilai 1-5 → POINT (TKP)
            } else {
                $tipe = 'SCORE'; // default fallback ke SCORE
            }
        }

        if ($tipe === 'SCORE') {
            // SCORE: kolom I (index 8) = kunci_jawaban (a/b/c/d/e)
            $kunci = strtolower(trim((string) ($cols[8] ?? '')));
            if (! in_array($kunci, $validKunci, true)) {
                $errors[] = "{$prefix}: Kunci jawaban harus a/b/c/d/e (ditemukan: '{$kunci}').";
            }
        } else {
            // POINT: kolom J–N (index 9–13) = nilai_a–e, angka 1-5, tidak boleh sama
            $nilaiValues = [];
            $nilaiMap    = [9 => 'nilai_a', 10 => 'nilai_b', 11 => 'nilai_c', 12 => 'nilai_d', 13 => 'nilai_e'];
            foreach ($nilaiMap as $idx => $label) {
                $val = trim((string) ($cols[$idx] ?? ''));
                if ($val === '' || ! is_numeric($val) || ! in_array((int)$val, [1,2,3,4,5])) {
                    $errors[] = "{$prefix}: {$label} wajib diisi angka 1–5.";
                } elseif (in_array((int)$val, $nilaiValues)) {
                    $errors[] = "{$prefix}: Nilai setiap pilihan tidak boleh sama (duplikat pada {$label}).";
                } else {
                    $nilaiValues[] = (int)$val;
                }
            }
        }

        // Validasi tryout_id jika diisi (index 15)
        $tryoutId = trim((string) ($cols[15] ?? ''));
        if ($tryoutId !== '' && is_numeric($tryoutId) && ! isset($validTryoutIds[(int)$tryoutId])) {
            $errors[] = "{$prefix}: tryout_id '{$tryoutId}' tidak ditemukan.";
        }

        return $errors;
    }

    /**
     * Bangun array data soal dari kolom baris impor (template baru dengan kolom No).
     * Menyertakan _tryout_id sebagai metadata (dihapus sebelum insert ke soal).
     */
    private function buildImportRow(array $cols, array $kategoriTipeMap): array
    {
        $kategoriId = (int) trim((string) ($cols[1] ?? ''));
        $pilihanE   = trim((string) ($cols[7] ?? ''));
        $tipe       = $kategoriTipeMap[$kategoriId] ?? '';

        // Fallback deteksi tipe
        if (! $tipe) {
            $colKunci = trim((string) ($cols[8] ?? ''));
            if (in_array(strtolower($colKunci), ['a','b','c','d','e'], true)) {
                $tipe = 'SCORE'; // ada kunci jawaban → SCORE
            } elseif (is_numeric($cols[9] ?? '') && in_array((int)($cols[9] ?? ''), [1,2,3,4,5])) {
                $tipe = 'POINT'; // ada nilai 1-5 → POINT
            } else {
                $tipe = 'SCORE'; // default fallback
            }
        }

        $tryoutRaw = trim((string) ($cols[15] ?? ''));
        $tryoutId  = ($tryoutRaw !== '' && is_numeric($tryoutRaw) && (int)$tryoutRaw > 0)
                     ? (int) $tryoutRaw : null;

        $base = [
            'kategori_id'     => $kategoriId,
            'pertanyaan'      => trim((string) ($cols[2] ?? '')),
            'pilihan_a'       => trim((string) ($cols[3] ?? '')),
            'pilihan_b'       => trim((string) ($cols[4] ?? '')),
            'pilihan_c'       => trim((string) ($cols[5] ?? '')),
            'pilihan_d'       => trim((string) ($cols[6] ?? '')),
            'pilihan_e'       => $pilihanE !== '' ? $pilihanE : null,
            '_tryout_id'      => $tryoutId,
        ];

        if ($tipe === 'POINT') {
            // POINT: kolom J–N (index 9–13) = nilai_a–e, kunci_jawaban = null
            return array_merge($base, [
                'nilai_a'       => (int) trim((string) ($cols[9]  ?? '')),
                'nilai_b'       => (int) trim((string) ($cols[10] ?? '')),
                'nilai_c'       => (int) trim((string) ($cols[11] ?? '')),
                'nilai_d'       => (int) trim((string) ($cols[12] ?? '')),
                'nilai_e'       => (int) trim((string) ($cols[13] ?? '')),
                'kunci_jawaban' => null,
                'pembahasan'    => trim((string) ($cols[14] ?? '')) ?: null,
            ]);
        }

        // SCORE: kolom I (index 8) = kunci_jawaban, nilai_a–e = null
        return array_merge($base, [
            'nilai_a'       => null,
            'nilai_b'       => null,
            'nilai_c'       => null,
            'nilai_d'       => null,
            'nilai_e'       => null,
            'kunci_jawaban' => strtolower(trim((string) ($cols[8] ?? ''))),
            'pembahasan'    => trim((string) ($cols[14] ?? '')) ?: null,
        ]);
    }
}