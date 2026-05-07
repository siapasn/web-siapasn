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
        $kategoriId    = $this->request->getGet('kategori_id') ?? '';
        $subKategoriId = $this->request->getGet('sub_kategori_id') ?? '';

        $db      = \Config\Database::connect();
        $builder = $db->table('soal s')
            ->select('s.*, k.nama AS nama_kategori, sk.nama AS nama_sub_kategori, sk.tipe_soal AS tipe_soal')
            ->join('kategori k', 'k.id = s.kategori_id', 'left')
            ->join('kategori sk', 'sk.id = s.sub_kategori_id', 'left')
            ->orderBy('s.id', 'DESC');

        if ($kategoriId !== '') {
            $builder->where('s.kategori_id', (int) $kategoriId);
        }

        if ($subKategoriId !== '') {
            $builder->where('s.sub_kategori_id', (int) $subKategoriId);
        }

        $soals      = $builder->get()->getResultArray();
        $kategoris  = $this->kategoriModel->getParents();

        // Ambil sub-kategori untuk dropdown (jika kategori sudah dipilih)
        $subKategoris = $kategoriId !== ''
            ? $this->kategoriModel->getChildren((int) $kategoriId)
            : [];

        return view('admin/master/soal/index', [
            'soals'          => $soals,
            'kategoris'      => $kategoris,
            'subKategoris'   => $subKategoris,
            'kategori_id'    => $kategoriId,
            'sub_kategori_id' => $subKategoriId,
            'menus'          => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah soal.
     */
    public function create()
    {
        return view('admin/master/soal/form', [
            'soal'      => null,
            'tipeSoal'  => null,
            'action'    => base_url('admin/master/soal/store'),
            'kategoris' => $this->kategoriModel->getParents(),
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

        // Validasi sub_kategori wajib jika kategori memiliki sub-kategori
        $kategoriId    = (int) $this->request->getPost('kategori_id');
        $subKategoriId = $this->request->getPost('sub_kategori_id');
        $hasSubKategori = $this->kategoriModel->getChildren($kategoriId);

        if (! empty($hasSubKategori) && (empty($subKategoriId) || $subKategoriId === '')) {
            return redirect()->back()->withInput()
                ->with('errors', ['sub_kategori_id' => 'Sub Kategori wajib dipilih karena kategori ini memiliki sub-kategori.']);
        }

        // Tentukan tipe soal dari sub-kategori
        $tipeSoal = null;
        if (! empty($subKategoriId)) {
            $subKat   = $this->kategoriModel->find((int) $subKategoriId);
            $tipeSoal = $subKat['tipe_soal'] ?? null;
        }

        // Validasi berdasarkan tipe soal
        if ($tipeSoal === 'SCORE') {
            // SCORE (TKP): wajib isi nilai A-E, angka 1-5, tidak boleh sama
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
            // POINT (TWK, TIU) atau kosong: kunci jawaban wajib
            if (empty($this->request->getPost('kunci_jawaban'))) {
                return redirect()->back()->withInput()
                    ->with('errors', ['kunci_jawaban' => 'Kunci jawaban wajib dipilih.']);
            }
        }

        $this->soalModel->insert([
            'kategori_id'     => $kategoriId,
            'sub_kategori_id' => ! empty($subKategoriId) ? (int) $subKategoriId : null,
            'pertanyaan'    => $this->request->getPost('pertanyaan'),
            'pilihan_a'     => $this->request->getPost('pilihan_a'),
            'pilihan_b'     => $this->request->getPost('pilihan_b'),
            'pilihan_c'     => $this->request->getPost('pilihan_c'),
            'pilihan_d'     => $this->request->getPost('pilihan_d'),
            'pilihan_e'     => $this->request->getPost('pilihan_e') ?? null,
            'nilai_a'       => $tipeSoal === 'SCORE' ? (int) $this->request->getPost('nilai_a') : null,
            'nilai_b'       => $tipeSoal === 'SCORE' ? (int) $this->request->getPost('nilai_b') : null,
            'nilai_c'       => $tipeSoal === 'SCORE' ? (int) $this->request->getPost('nilai_c') : null,
            'nilai_d'       => $tipeSoal === 'SCORE' ? (int) $this->request->getPost('nilai_d') : null,
            'nilai_e'       => $tipeSoal === 'SCORE' ? (int) $this->request->getPost('nilai_e') : null,
            'kunci_jawaban' => $tipeSoal !== 'SCORE' ? $this->request->getPost('kunci_jawaban') : null,
            'pembahasan'    => $this->request->getPost('pembahasan') ?? null,
        ]);

        return redirect()->to(base_url('admin/master/soal'))->with('success', 'Soal berhasil ditambahkan.');
    }

    /**
     * Form edit soal.
     * Hanya kirim kategori parent (parent_id IS NULL) ke view.
     */
    public function edit(int $id)
    {
        $soal = $this->soalModel->find($id);

        if (! $soal) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Soal tidak ditemukan.');
        }

        return view('admin/master/soal/form', [
            'soal'      => $soal,
            'tipeSoal'  => $this->getTipeSoalFromSoal($soal),
            'action'    => base_url("admin/master/soal/{$id}/update"),
            'kategoris' => $this->kategoriModel->getParents(),
            'menus'     => $this->getMenus(),
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

        // Validasi sub_kategori wajib jika kategori memiliki sub-kategori
        $kategoriId    = (int) $this->request->getPost('kategori_id');
        $subKategoriId = $this->request->getPost('sub_kategori_id');
        $hasSubKategori = $this->kategoriModel->getChildren($kategoriId);

        if (! empty($hasSubKategori) && (empty($subKategoriId) || $subKategoriId === '')) {
            return redirect()->back()->withInput()
                ->with('errors', ['sub_kategori_id' => 'Sub Kategori wajib dipilih karena kategori ini memiliki sub-kategori.']);
        }

        // Tentukan tipe soal dari sub-kategori
        $tipeSoal = null;
        if (! empty($subKategoriId)) {
            $subKat   = $this->kategoriModel->find((int) $subKategoriId);
            $tipeSoal = $subKat['tipe_soal'] ?? null;
        }

        // Validasi berdasarkan tipe soal
        if ($tipeSoal === 'SCORE') {
            // SCORE (TKP): wajib isi nilai A-E, angka 1-5, tidak boleh sama
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
            // POINT (TWK, TIU) atau kosong: kunci jawaban wajib
            if (empty($this->request->getPost('kunci_jawaban'))) {
                return redirect()->back()->withInput()
                    ->with('errors', ['kunci_jawaban' => 'Kunci jawaban wajib dipilih.']);
            }
        }

        $this->soalModel->update($id, [
            'kategori_id'     => $kategoriId,
            'sub_kategori_id' => ! empty($subKategoriId) ? (int) $subKategoriId : null,
            'pertanyaan'    => $this->request->getPost('pertanyaan'),
            'pilihan_a'     => $this->request->getPost('pilihan_a'),
            'pilihan_b'     => $this->request->getPost('pilihan_b'),
            'pilihan_c'     => $this->request->getPost('pilihan_c'),
            'pilihan_d'     => $this->request->getPost('pilihan_d'),
            'pilihan_e'     => $this->request->getPost('pilihan_e') ?? null,
            'nilai_a'       => $tipeSoal === 'SCORE' ? (int) $this->request->getPost('nilai_a') : null,
            'nilai_b'       => $tipeSoal === 'SCORE' ? (int) $this->request->getPost('nilai_b') : null,
            'nilai_c'       => $tipeSoal === 'SCORE' ? (int) $this->request->getPost('nilai_c') : null,
            'nilai_d'       => $tipeSoal === 'SCORE' ? (int) $this->request->getPost('nilai_d') : null,
            'nilai_e'       => $tipeSoal === 'SCORE' ? (int) $this->request->getPost('nilai_e') : null,
            'kunci_jawaban' => $tipeSoal !== 'SCORE' ? $this->request->getPost('kunci_jawaban') : null,
            'pembahasan'    => $this->request->getPost('pembahasan') ?? null,
        ]);

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
                'data'   => $subKategoris, // includes tipe_soal field
            ]);
    }

    /**
     * Helper: ambil tipe_soal dari sub_kategori_id soal.
     */
    private function getTipeSoalFromSoal(?array $soal): ?string
    {
        if (! $soal || empty($soal['sub_kategori_id'])) {
            return null;
        }
        $subKat = $this->kategoriModel->find((int) $soal['sub_kategori_id']);
        return $subKat['tipe_soal'] ?? null;
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
     * Proses impor soal dari file Excel/CSV.
     * Format baru (14 kolom):
     *   POINT sheet: A=kategori_id, B=sub_kategori_id, C=pertanyaan,
     *                D=pilihan_a, E=pilihan_b, F=pilihan_c, G=pilihan_d, H=pilihan_e,
     *                I=kunci_jawaban, J=pembahasan
     *   SCORE sheet: A=kategori_id, B=sub_kategori_id, C=pertanyaan,
     *                D=pilihan_a, E=pilihan_b, F=pilihan_c, G=pilihan_d, H=pilihan_e,
     *                I=nilai_a, J=nilai_b, K=nilai_c, L=nilai_d, M=nilai_e, N=pembahasan
     *
     * Sistem mendeteksi tipe otomatis dari sub_kategori_id (kolom B).
     * Jika kolom B kosong, fallback ke deteksi dari kolom I:
     *   - huruf a/b/c/d/e → POINT, angka 1-5 → SCORE
     */
    public function importProcess()
    {
        $file = $this->request->getFile('file_import');

        if (! $file || ! $file->isValid()) {
            $error = $file ? $file->getErrorString() : 'Tidak ada file yang diunggah.';
            return redirect()->back()->with('error', $error);
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'csv'], true)) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan .xlsx atau .csv.');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return redirect()->back()->with('error', 'Ukuran file melebihi batas maksimum 5 MB.');
        }

        $tmpDir  = WRITEPATH . 'uploads/import/';
        $tmpName = $file->getRandomName();
        $file->move($tmpDir, $tmpName);
        $filePath = $tmpDir . $tmpName;

        // Ambil semua kategori beserta tipe_soal — buat lookup nama → id
        $db      = \Config\Database::connect();
        $katRows = $db->table('kategori')->select('id, nama, parent_id, tipe_soal')->get()->getResultArray();

        $validKategoriIds = [];
        $kategoriTipeMap  = []; // id => tipe_soal
        $namaToIdMap      = []; // strtolower(nama) => id  (untuk semua kategori)
        $namaParentToId   = []; // strtolower(nama) => id  (hanya parent/induk)
        $namaSubToId      = []; // strtolower(nama) => id  (hanya sub-kategori)

        foreach ($katRows as $row) {
            $id   = (int) $row['id'];
            $key  = strtolower(trim($row['nama']));

            $validKategoriIds[]    = $id;
            $kategoriTipeMap[$id]  = $row['tipe_soal'];
            $namaToIdMap[$key]     = $id;

            if ($row['parent_id'] === null) {
                $namaParentToId[$key] = $id;
            } else {
                $namaSubToId[$key] = $id;
            }
        }

        $errors       = [];
        $rowsToInsert = [];
        $validKunci   = ['a', 'b', 'c', 'd', 'e'];

        try {
            if ($ext === 'csv') {
                $handle = fopen($filePath, 'r');
                if ($handle === false) {
                    throw new \RuntimeException('Gagal membuka file CSV.');
                }
                $rowNum = 0;
                while (($cols = fgetcsv($handle)) !== false) {
                    $rowNum++;
                    
                    // Skip baris kosong
                    if (implode('', $cols) === '') continue;
                    
                    // Skip baris yang kolom A-nya bukan nama valid (judul, keterangan, header)
                    $colA = trim((string) ($cols[0] ?? ''));
                    if ($colA === '' || isset($namaParentToId[strtolower($colA)]) === false) continue;

                    $rowErrors = $this->validateImportRow($cols, $rowNum, $validKategoriIds, $validKunci, $kategoriTipeMap, '', $namaParentToId, $namaSubToId);
                    if (! empty($rowErrors)) {
                        $errors = array_merge($errors, $rowErrors);
                    } else {
                        $rowsToInsert[] = $this->buildImportRow($cols, $kategoriTipeMap, $namaParentToId, $namaSubToId);
                    }
                }
                fclose($handle);
            } else {
                if (! class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                    @unlink($filePath);
                    return redirect()->back()->with('error', 'Library PhpSpreadsheet belum terinstall. Jalankan: composer require phpoffice/phpspreadsheet');
                }

                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);

                // Proses semua sheet (kecuali sheet Referensi)
                foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                    $sheetTitle = $sheet->getTitle();
                    if (stripos($sheetTitle, 'referensi') !== false) {
                        continue; // Lewati sheet referensi
                    }

                    $highestRow = $sheet->getHighestRow();
                    $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                        $sheet->getHighestColumn()
                    );
                    // Minimal 10 kolom (POINT), maksimal 14 (SCORE)
                    $maxCol = max($highestCol, 14);

                    for ($rowNum = 2; $rowNum <= $highestRow; $rowNum++) {
                        $cols = [];
                        for ($col = 1; $col <= $maxCol; $col++) {
                            $colLetter  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                            $cellValue  = $sheet->getCell($colLetter . $rowNum)->getValue();
                            $cols[$col - 1] = $cellValue !== null ? trim((string) $cellValue) : '';
                        }

                        if (implode('', $cols) === '') continue;

                        // Skip baris yang kolom A-nya bukan nama kategori valid (judul, keterangan, header)
                        $colA = strtolower(trim($cols[0] ?? ''));
                        if ($colA === '' || ! isset($namaParentToId[$colA])) continue;

                        $rowErrors = $this->validateImportRow($cols, $rowNum, $validKategoriIds, $validKunci, $kategoriTipeMap, $sheetTitle, $namaParentToId, $namaSubToId);
                        if (! empty($rowErrors)) {
                            $errors = array_merge($errors, $rowErrors);
                        } else {
                            $rowsToInsert[] = $this->buildImportRow($cols, $kategoriTipeMap, $namaParentToId, $namaSubToId);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            @unlink($filePath);
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        @unlink($filePath);

        if (! empty($errors)) {
            return view('admin/master/soal/import', [
                'errors'         => $errors,
                'total_imported' => 0,
                'menus'          => $this->getMenus(),
            ]);
        }

        $totalImported = 0;
        $db->transStart();
        foreach ($rowsToInsert as $row) {
            $this->soalModel->insert($row);
            $totalImported++;
        }
        $db->transComplete();

        if ($db->transStatus() === false) {
            return view('admin/master/soal/import', [
                'errors'         => ['Terjadi kesalahan saat menyimpan data ke database. Silakan coba lagi.'],
                'total_imported' => 0,
                'menus'          => $this->getMenus(),
            ]);
        }

        return view('admin/master/soal/import', [
            'errors'         => [],
            'total_imported' => $totalImported,
            'menus'          => $this->getMenus(),
        ]);
    }

    /**
     * Deteksi tipe soal dari kolom data.
     * Prioritas: sub_kategori_id (kolom B) → fallback kolom I (kunci/nilai).
     */
    private function detectTipeSoal(array $cols, array $kategoriTipeMap): string
    {
        $subKatId = trim((string) ($cols[1] ?? ''));
        if ($subKatId !== '' && is_numeric($subKatId)) {
            return $kategoriTipeMap[(int)$subKatId] ?? '';
        }
        // Fallback: cek kolom I (index 8)
        $colI = trim((string) ($cols[8] ?? ''));
        if (in_array(strtolower($colI), ['a','b','c','d','e'], true)) {
            return 'POINT';
        }
        if (is_numeric($colI) && in_array((int)$colI, [1,2,3,4,5])) {
            return 'SCORE';
        }
        return '';
    }

    /**
     * Validasi satu baris data impor (format nama kategori).
     */
    public static function validateImportRow(
        array  $cols,
        int    $rowNum,
        array  $validKategoriIds,
        array  $validKunci,
        array  $kategoriTipeMap = [],
        string $sheetName = '',
        array  $namaParentToId = [],
        array  $namaSubToId = []
    ): array {
        $errors = [];
        $prefix = $sheetName ? "[Sheet: {$sheetName}] Baris {$rowNum}" : "Baris {$rowNum}";

        $namaKategori    = trim((string) ($cols[0] ?? ''));
        $namaSubKategori = trim((string) ($cols[1] ?? ''));
        $pertanyaan      = trim((string) ($cols[2] ?? ''));
        $pilihanA        = trim((string) ($cols[3] ?? ''));
        $pilihanB        = trim((string) ($cols[4] ?? ''));
        $pilihanC        = trim((string) ($cols[5] ?? ''));
        $pilihanD        = trim((string) ($cols[6] ?? ''));

        // Wajib
        if ($pertanyaan === '') $errors[] = "{$prefix}: Pertanyaan tidak boleh kosong.";
        if ($pilihanA   === '') $errors[] = "{$prefix}: Pilihan A tidak boleh kosong.";
        if ($pilihanB   === '') $errors[] = "{$prefix}: Pilihan B tidak boleh kosong.";
        if ($pilihanC   === '') $errors[] = "{$prefix}: Pilihan C tidak boleh kosong.";
        if ($pilihanD   === '') $errors[] = "{$prefix}: Pilihan D tidak boleh kosong.";

        // Validasi nama_kategori
        $kategoriKey = strtolower($namaKategori);
        if ($namaKategori === '') {
            $errors[] = "{$prefix}: nama_kategori tidak boleh kosong.";
            return $errors; // tidak bisa lanjut tanpa kategori
        }
        if (! isset($namaParentToId[$kategoriKey])) {
            $errors[] = "{$prefix}: nama_kategori '{$namaKategori}' tidak ditemukan di database.";
            return $errors;
        }
        $kategoriId = $namaParentToId[$kategoriKey];

        // Validasi nama_sub_kategori (opsional)
        $subKategoriId = null;
        if ($namaSubKategori !== '') {
            $subKey = strtolower($namaSubKategori);
            if (! isset($namaSubToId[$subKey])) {
                $errors[] = "{$prefix}: nama_sub_kategori '{$namaSubKategori}' tidak ditemukan di database.";
            } else {
                $subKategoriId = $namaSubToId[$subKey];
            }
        }

        // Deteksi tipe dari sub kategori atau fallback kolom I
        $tipe = '';
        if ($subKategoriId !== null) {
            $tipe = $kategoriTipeMap[$subKategoriId] ?? '';
        }
        if ($tipe === '') {
            $colI = trim((string) ($cols[8] ?? ''));
            if (in_array(strtolower($colI), ['a','b','c','d','e'], true)) {
                $tipe = 'POINT';
            } elseif (is_numeric($colI) && in_array((int)$colI, [1,2,3,4,5])) {
                $tipe = 'SCORE';
            }
        }

        if ($tipe === 'SCORE') {
            $nilaiValues = [];
            $nilaiLabels = ['nilai_a' => 8, 'nilai_b' => 9, 'nilai_c' => 10, 'nilai_d' => 11, 'nilai_e' => 12];
            foreach ($nilaiLabels as $label => $idx) {
                $val = trim((string) ($cols[$idx] ?? ''));
                if ($val === '' || ! is_numeric($val) || ! in_array((int)$val, [1,2,3,4,5])) {
                    $errors[] = "{$prefix}: {$label} wajib diisi dengan angka 1–5.";
                } elseif (in_array((int)$val, $nilaiValues)) {
                    $errors[] = "{$prefix}: Nilai setiap pilihan tidak boleh sama (duplikat pada {$label}).";
                } else {
                    $nilaiValues[] = (int)$val;
                }
            }
        } else {
            $kunci = strtolower(trim((string) ($cols[8] ?? '')));
            if (! in_array($kunci, $validKunci, true)) {
                $errors[] = "{$prefix}: kunci_jawaban harus salah satu dari a, b, c, d, e (ditemukan: '{$kunci}').";
            }
        }

        return $errors;
    }

    /**
     * Bangun array data soal dari kolom baris impor (format nama kategori).
     */
    private function buildImportRow(array $cols, array $kategoriTipeMap, array $namaParentToId = [], array $namaSubToId = []): array
    {
        $namaKategori    = trim((string) ($cols[0] ?? ''));
        $namaSubKategori = trim((string) ($cols[1] ?? ''));
        $pilihanE        = trim((string) ($cols[7] ?? ''));

        // Resolve nama → ID
        $kategoriId    = $namaParentToId[strtolower($namaKategori)] ?? 0;
        $subKategoriId = ($namaSubKategori !== '' && isset($namaSubToId[strtolower($namaSubKategori)]))
                         ? $namaSubToId[strtolower($namaSubKategori)] : null;

        // Deteksi tipe
        $tipe = '';
        if ($subKategoriId !== null) {
            $tipe = $kategoriTipeMap[$subKategoriId] ?? '';
        }
        if ($tipe === '') {
            $colI = trim((string) ($cols[8] ?? ''));
            if (in_array(strtolower($colI), ['a','b','c','d','e'], true)) {
                $tipe = 'POINT';
            } elseif (is_numeric($colI) && in_array((int)$colI, [1,2,3,4,5])) {
                $tipe = 'SCORE';
            }
        }

        if ($tipe === 'SCORE') {
            $pembahasan = trim((string) ($cols[13] ?? ''));
            return [
                'kategori_id'     => $kategoriId,
                'sub_kategori_id' => $subKategoriId,
                'pertanyaan'      => trim((string) ($cols[2] ?? '')),
                'pilihan_a'       => trim((string) ($cols[3] ?? '')),
                'pilihan_b'       => trim((string) ($cols[4] ?? '')),
                'pilihan_c'       => trim((string) ($cols[5] ?? '')),
                'pilihan_d'       => trim((string) ($cols[6] ?? '')),
                'pilihan_e'       => $pilihanE !== '' ? $pilihanE : null,
                'nilai_a'         => (int) trim((string) ($cols[8]  ?? '')),
                'nilai_b'         => (int) trim((string) ($cols[9]  ?? '')),
                'nilai_c'         => (int) trim((string) ($cols[10] ?? '')),
                'nilai_d'         => (int) trim((string) ($cols[11] ?? '')),
                'nilai_e'         => (int) trim((string) ($cols[12] ?? '')),
                'kunci_jawaban'   => null,
                'pembahasan'      => $pembahasan !== '' ? $pembahasan : null,
            ];
        } else {
            // POINT
            $pembahasan = trim((string) ($cols[9] ?? ''));
            return [
                'kategori_id'     => $kategoriId,
                'sub_kategori_id' => $subKategoriId,
                'pertanyaan'      => trim((string) ($cols[2] ?? '')),
                'pilihan_a'       => trim((string) ($cols[3] ?? '')),
                'pilihan_b'       => trim((string) ($cols[4] ?? '')),
                'pilihan_c'       => trim((string) ($cols[5] ?? '')),
                'pilihan_d'       => trim((string) ($cols[6] ?? '')),
                'pilihan_e'       => $pilihanE !== '' ? $pilihanE : null,
                'nilai_a'         => null,
                'nilai_b'         => null,
                'nilai_c'         => null,
                'nilai_d'         => null,
                'nilai_e'         => null,
                'kunci_jawaban'   => strtolower(trim((string) ($cols[8] ?? ''))),
                'pembahasan'      => $pembahasan !== '' ? $pembahasan : null,
            ];
        }
    }
}
