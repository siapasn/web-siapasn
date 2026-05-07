<?php
/**
 * Script untuk generate template Excel import soal CPNS
 * Jalankan: php generate_template_soal.php
 * Output: template_import_soal_cpns.xlsx
 */

require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

$spreadsheet = new Spreadsheet();

// ============================================================
// Sheet 1: Template Import Soal
// ============================================================
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Import Soal');

// --- Warna ---
$colorHeader    = '1a3a5c'; // biru tua SiapASN
$colorSubHeader = 'f5a623'; // kuning emas SiapASN
$colorRequired  = 'fff3cd'; // kuning muda (kolom wajib)
$colorOptional  = 'f8f9fa'; // abu muda (kolom opsional)
$colorExample   = 'e8f5e9'; // hijau muda (baris contoh)
$colorWhite     = 'ffffff';

// --- Judul ---
$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A1', 'TEMPLATE IMPORT SOAL — SiapASN Simulation Center');
$sheet->getStyle('A1')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $colorWhite]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorHeader]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);
$sheet->getRowDimension(1)->setRowHeight(30);

// --- Sub-judul ---
$sheet->mergeCells('A2:I2');
$sheet->setCellValue('A2', 'Baris 1 = judul (jangan dihapus). Baris 2 = sub-judul (jangan dihapus). Data dimulai dari BARIS 3.');
$sheet->getStyle('A2')->applyFromArray([
    'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '555555']],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'fff8e1']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// --- Header kolom (baris 3) ---
$headers = [
    'A' => ['label' => 'kategori_id',   'wajib' => true,  'width' => 14],
    'B' => ['label' => 'pertanyaan',    'wajib' => true,  'width' => 55],
    'C' => ['label' => 'pilihan_a',     'wajib' => true,  'width' => 35],
    'D' => ['label' => 'pilihan_b',     'wajib' => true,  'width' => 35],
    'E' => ['label' => 'pilihan_c',     'wajib' => true,  'width' => 35],
    'F' => ['label' => 'pilihan_d',     'wajib' => true,  'width' => 35],
    'G' => ['label' => 'pilihan_e',     'wajib' => false, 'width' => 35],
    'H' => ['label' => 'kunci_jawaban', 'wajib' => true,  'width' => 16],
    'I' => ['label' => 'pembahasan',    'wajib' => false, 'width' => 50],
];

foreach ($headers as $col => $info) {
    $cell = $col . '3';
    $sheet->setCellValue($cell, $info['label'] . ($info['wajib'] ? ' *' : ''));
    $bgColor = $info['wajib'] ? $colorRequired : $colorOptional;
    $sheet->getStyle($cell)->applyFromArray([
        'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => $colorHeader]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
    ]);
    $sheet->getColumnDimension($col)->setWidth($info['width']);
}
$sheet->getRowDimension(3)->setRowHeight(22);

// --- Baris keterangan (baris 4) ---
$keterangan = [
    'A' => 'ID kategori dari tabel kategori (lihat sheet "Daftar Kategori")',
    'B' => 'Teks pertanyaan lengkap',
    'C' => 'Teks pilihan A',
    'D' => 'Teks pilihan B',
    'E' => 'Teks pilihan C',
    'F' => 'Teks pilihan D',
    'G' => 'Teks pilihan E (boleh kosong)',
    'H' => 'Huruf kunci: a / b / c / d / e',
    'I' => 'Penjelasan jawaban (boleh kosong)',
];

foreach ($keterangan as $col => $text) {
    $cell = $col . '4';
    $sheet->setCellValue($cell, $text);
    $sheet->getStyle($cell)->applyFromArray([
        'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '666666']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f5f5f5']],
        'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'dddddd']]],
    ]);
}
$sheet->getRowDimension(4)->setRowHeight(30);

// --- Contoh data soal CPNS (baris 5-14) ---
$contohSoal = [
    // TWK - Pancasila (kategori_id = 4, sesuaikan dengan ID di DB)
    [4, 'Pancasila sebagai dasar negara Indonesia pertama kali dirumuskan oleh...', 'Ir. Soekarno', 'Moh. Hatta', 'Moh. Yamin', 'Soepomo', 'Ahmad Subarjo', 'c', 'Moh. Yamin adalah orang pertama yang mengusulkan dasar negara pada sidang BPUPKI tanggal 29 Mei 1945.'],
    [4, 'Sila ke-3 Pancasila berbunyi...', 'Ketuhanan Yang Maha Esa', 'Kemanusiaan yang Adil dan Beradab', 'Persatuan Indonesia', 'Kerakyatan yang Dipimpin oleh Hikmat Kebijaksanaan', 'Keadilan Sosial bagi Seluruh Rakyat Indonesia', 'c', 'Sila ke-3 Pancasila adalah "Persatuan Indonesia".'],
    // TWK - UUD 1945 (kategori_id = 5)
    [5, 'Pasal berapa dalam UUD 1945 yang mengatur tentang hak atas pekerjaan dan penghidupan yang layak?', 'Pasal 27 ayat (1)', 'Pasal 27 ayat (2)', 'Pasal 28', 'Pasal 29', 'Pasal 30', 'b', 'Pasal 27 ayat (2) UUD 1945: "Tiap-tiap warga negara berhak atas pekerjaan dan penghidupan yang layak bagi kemanusiaan."'],
    // TIU - Verbal (kategori_id = 8)
    [8, 'Antonim dari kata "KONKRET" adalah...', 'Nyata', 'Abstrak', 'Jelas', 'Pasti', 'Riil', 'b', 'Antonim (lawan kata) dari KONKRET adalah ABSTRAK. Konkret = nyata/berwujud, Abstrak = tidak berwujud/tidak nyata.'],
    [8, 'Sinonim dari kata "AMBIGU" adalah...', 'Jelas', 'Tegas', 'Mendua', 'Pasti', 'Nyata', 'c', 'Sinonim (persamaan kata) dari AMBIGU adalah MENDUA, yang berarti memiliki dua makna atau tidak jelas.'],
    // TIU - Numerik (kategori_id = 9)
    [9, 'Jika 3x + 7 = 22, maka nilai x adalah...', '3', '4', '5', '6', '7', 'c', '3x + 7 = 22 → 3x = 22 - 7 = 15 → x = 15/3 = 5'],
    [9, 'Deret berikut: 2, 4, 8, 16, 32, ... Bilangan selanjutnya adalah...', '48', '56', '64', '72', '80', 'c', 'Pola deret: setiap bilangan dikali 2. Jadi 32 × 2 = 64.'],
    // TKP - Pelayanan Publik (kategori_id = 11)
    [11, 'Seorang pegawai negeri menerima keluhan dari masyarakat tentang pelayanan yang lambat. Sikap yang paling tepat adalah...', 'Mengabaikan keluhan tersebut karena sudah sesuai prosedur', 'Meminta masyarakat untuk bersabar dan menunggu', 'Mendengarkan keluhan, meminta maaf, dan berupaya memperbaiki pelayanan', 'Menyalahkan rekan kerja atas keterlambatan tersebut', 'Melaporkan masyarakat yang mengeluh kepada atasan', 'c', 'Sikap terbaik ASN adalah mendengarkan keluhan dengan empati, meminta maaf atas ketidaknyamanan, dan berkomitmen untuk memperbaiki pelayanan.'],
    // TKP - Profesionalisme (kategori_id = 14)
    [14, 'Anda mendapat tugas mendadak dari atasan saat sedang menyelesaikan pekerjaan lain yang juga penting. Apa yang Anda lakukan?', 'Menolak tugas baru karena sedang sibuk', 'Mengerjakan tugas baru dan mengabaikan pekerjaan lama', 'Meminta klarifikasi prioritas kepada atasan dan menyusun rencana penyelesaian keduanya', 'Mendelegasikan semua pekerjaan kepada rekan', 'Menunda keduanya sampai situasi lebih tenang', 'c', 'Profesionalisme ASN mengharuskan komunikasi yang baik dengan atasan untuk menentukan prioritas dan menyusun rencana kerja yang efektif.'],
    // TWK - NKRI (kategori_id = 6)
    [6, 'Wawasan Nusantara adalah cara pandang bangsa Indonesia tentang diri dan lingkungannya berdasarkan...', 'Pancasila dan UUD 1945', 'Bhinneka Tunggal Ika', 'Sumpah Pemuda', 'Proklamasi Kemerdekaan', 'Tap MPR', 'a', 'Wawasan Nusantara didasarkan pada Pancasila sebagai falsafah bangsa dan UUD 1945 sebagai konstitusi negara.'],
];

$rowNum = 5;
foreach ($contohSoal as $soal) {
    $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
    foreach ($cols as $i => $col) {
        $sheet->setCellValue($col . $rowNum, $soal[$i]);
        $sheet->getStyle($col . $rowNum)->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorExample]],
            'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
        ]);
    }
    $sheet->getRowDimension($rowNum)->setRowHeight(40);
    $rowNum++;
}

// --- Baris kosong untuk input (baris 15-50) ---
for ($r = $rowNum; $r <= 50; $r++) {
    foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'] as $col) {
        $sheet->getStyle($col . $r)->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorWhite]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e0e0e0']]],
            'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
        ]);
    }
    $sheet->getRowDimension($r)->setRowHeight(35);
}

// Freeze panes di baris 5 (header tetap terlihat)
$sheet->freezePane('A5');

// ============================================================
// Sheet 2: Daftar Kategori
// ============================================================
$sheetKat = $spreadsheet->createSheet();
$sheetKat->setTitle('Daftar Kategori');

// Header
$sheetKat->mergeCells('A1:D1');
$sheetKat->setCellValue('A1', 'DAFTAR ID KATEGORI — Gunakan ID ini di kolom A sheet "Import Soal"');
$sheetKat->getStyle('A1')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => $colorWhite]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorHeader]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);
$sheetKat->getRowDimension(1)->setRowHeight(28);

// Sub-header
$katHeaders = ['A' => 'ID', 'B' => 'Nama Kategori', 'C' => 'Parent', 'D' => 'Keterangan'];
foreach ($katHeaders as $col => $label) {
    $sheetKat->setCellValue($col . '2', $label);
    $sheetKat->getStyle($col . '2')->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => $colorWhite]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorSubHeader]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
    ]);
}

// Data kategori (sesuai urutan seeder)
$kategoriData = [
    // ID, Nama, Parent, Keterangan
    [1,  'TWK (Tes Wawasan Kebangsaan)', '-',                              'Kategori induk TWK'],
    [2,  'TIU (Tes Intelegensia Umum)',  '-',                              'Kategori induk TIU'],
    [3,  'TKP (Tes Karakteristik Pribadi)', '-',                           'Kategori induk TKP'],
    [4,  'Pancasila',                    'TWK (id=1)',                     'Sub-kategori TWK'],
    [5,  'UUD 1945',                     'TWK (id=1)',                     'Sub-kategori TWK'],
    [6,  'NKRI',                         'TWK (id=1)',                     'Sub-kategori TWK'],
    [7,  'Bhineka Tunggal Ika',          'TWK (id=1)',                     'Sub-kategori TWK'],
    [8,  'Verbal',                       'TIU (id=2)',                     'Sub-kategori TIU'],
    [9,  'Numerik',                      'TIU (id=2)',                     'Sub-kategori TIU'],
    [10, 'Figural',                      'TIU (id=2)',                     'Sub-kategori TIU'],
    [11, 'Pelayanan Publik',             'TKP (id=3)',                     'Sub-kategori TKP'],
    [12, 'Sosial Budaya',                'TKP (id=3)',                     'Sub-kategori TKP'],
    [13, 'Teknologi Informasi',          'TKP (id=3)',                     'Sub-kategori TKP'],
    [14, 'Profesionalisme',              'TKP (id=3)',                     'Sub-kategori TKP'],
];

$rowK = 3;
foreach ($kategoriData as $kat) {
    $isParent = $kat[2] === '-';
    $bgColor  = $isParent ? 'e3f2fd' : $colorWhite;

    $sheetKat->setCellValue('A' . $rowK, $kat[0]);
    $sheetKat->setCellValue('B' . $rowK, $kat[1]);
    $sheetKat->setCellValue('C' . $rowK, $kat[2]);
    $sheetKat->setCellValue('D' . $rowK, $kat[3]);

    $sheetKat->getStyle('A' . $rowK . ':D' . $rowK)->applyFromArray([
        'font'      => ['bold' => $isParent],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheetKat->getStyle('A' . $rowK)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $rowK++;
}

$sheetKat->getColumnDimension('A')->setWidth(8);
$sheetKat->getColumnDimension('B')->setWidth(35);
$sheetKat->getColumnDimension('C')->setWidth(20);
$sheetKat->getColumnDimension('D')->setWidth(25);

// ============================================================
// Sheet 3: Panduan
// ============================================================
$sheetGuide = $spreadsheet->createSheet();
$sheetGuide->setTitle('Panduan');

$panduan = [
    ['PANDUAN IMPORT SOAL — SiapASN Simulation Center', '', true, $colorHeader, $colorWhite],
    ['', '', false, $colorWhite, '333333'],
    ['LANGKAH-LANGKAH:', '', true, 'e3f2fd', $colorHeader],
    ['1.', 'Lihat sheet "Daftar Kategori" untuk mengetahui ID kategori yang tersedia.', false, $colorWhite, '333333'],
    ['2.', 'Isi data soal di sheet "Import Soal" mulai dari baris ke-5 (baris 3-4 adalah header, jangan dihapus).', false, $colorWhite, '333333'],
    ['3.', 'Kolom bertanda (*) adalah WAJIB diisi. Kolom tanpa (*) boleh dikosongkan.', false, $colorWhite, '333333'],
    ['4.', 'Kolom kunci_jawaban hanya boleh berisi huruf: a, b, c, d, atau e (huruf kecil).', false, $colorWhite, '333333'],
    ['5.', 'Simpan file dalam format .xlsx sebelum diupload.', false, $colorWhite, '333333'],
    ['6.', 'Upload file di menu: Admin → Master Data → Soal → Import Excel/CSV.', false, $colorWhite, '333333'],
    ['', '', false, $colorWhite, '333333'],
    ['ATURAN VALIDASI:', '', true, 'fff3e0', 'd4891a'],
    ['✓', 'kategori_id harus berupa angka dan ID-nya harus ada di tabel kategori.', false, $colorWhite, '333333'],
    ['✓', 'pertanyaan tidak boleh kosong.', false, $colorWhite, '333333'],
    ['✓', 'pilihan_a, pilihan_b, pilihan_c, pilihan_d wajib diisi.', false, $colorWhite, '333333'],
    ['✓', 'pilihan_e boleh dikosongkan (soal dengan 4 pilihan).', false, $colorWhite, '333333'],
    ['✓', 'kunci_jawaban harus salah satu dari: a, b, c, d, e.', false, $colorWhite, '333333'],
    ['✓', 'Jika ada 1 baris yang tidak valid, SELURUH import akan dibatalkan.', false, 'fff3cd', '856404'],
    ['', '', false, $colorWhite, '333333'],
    ['CATATAN ID KATEGORI:', '', true, 'e8f5e9', '2e7d32'],
    ['⚠', 'ID kategori di file ini mengasumsikan seeder dijalankan sekali dari awal.', false, $colorWhite, '333333'],
    ['⚠', 'Jika ID berbeda, cek di phpMyAdmin: SELECT id, nama FROM kategori ORDER BY id', false, $colorWhite, '333333'],
    ['⚠', 'Atau lihat sheet "Daftar Kategori" dan sesuaikan ID dengan kondisi database Anda.', false, $colorWhite, '333333'],
];

$rowG = 1;
foreach ($panduan as $baris) {
    $sheetGuide->mergeCells('A' . $rowG . ':C' . $rowG);
    if ($baris[0] !== '') {
        $sheetGuide->setCellValue('A' . $rowG, $baris[0] . ($baris[1] ? '  ' . $baris[1] : ''));
    }
    $sheetGuide->getStyle('A' . $rowG)->applyFromArray([
        'font'      => ['bold' => $baris[2], 'color' => ['rgb' => $baris[4]]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $baris[3]]],
        'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheetGuide->getRowDimension($rowG)->setRowHeight(22);
    $rowG++;
}

$sheetGuide->getColumnDimension('A')->setWidth(5);
$sheetGuide->getColumnDimension('B')->setWidth(80);
$sheetGuide->getColumnDimension('C')->setWidth(20);

// ============================================================
// Set active sheet ke sheet pertama
// ============================================================
$spreadsheet->setActiveSheetIndex(0);

// ============================================================
// Simpan file
// ============================================================
$outputPath = __DIR__ . '/public/assets/template_import_soal_cpns.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($outputPath);

echo "✅ File berhasil dibuat: public/assets/template_import_soal_cpns.xlsx\n";
echo "   URL akses: http://localhost:8081/assets/template_import_soal_cpns.xlsx\n";
