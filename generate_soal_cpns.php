<?php
/**
 * Script untuk generate soal CPNS ke Excel
 * Jalankan: php generate_soal_cpns.php
 * Output: public/assets/template_soal_cpns.xlsx
 */

require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Soal CPNS');

// --- Warna ---
$colorHeader  = '1a3a5c'; // biru tua
$colorAccent  = 'f5a623'; // emas
$colorWhite   = 'FFFFFF';
$colorRow1    = 'EAF2FB'; // biru muda (baris ganjil)
$colorRow2    = 'FFFFFF'; // putih (baris genap)

// ============================================================
// Row 1: Judul (merged A1:I1)
// ============================================================
$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A1', 'TEMPLATE IMPORT SOAL CPNS — SiapASN Simulation Center');
$sheet->getStyle('A1')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $colorWhite]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorHeader]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);
$sheet->getRowDimension(1)->setRowHeight(32);

// ============================================================
// Row 2: Subtitle / catatan (merged A2:I2)
// ============================================================
$sheet->mergeCells('A2:I2');
$sheet->setCellValue('A2', 'Data dimulai dari baris ke-3. Jangan hapus baris 1 dan 2.');
$sheet->getStyle('A2')->applyFromArray([
    'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '555555']],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF8DC']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);
$sheet->getRowDimension(2)->setRowHeight(22);

// ============================================================
// Lebar kolom
// ============================================================
$sheet->getColumnDimension('A')->setWidth(14);
$sheet->getColumnDimension('B')->setWidth(55);
$sheet->getColumnDimension('C')->setWidth(35);
$sheet->getColumnDimension('D')->setWidth(35);
$sheet->getColumnDimension('E')->setWidth(35);
$sheet->getColumnDimension('F')->setWidth(35);
$sheet->getColumnDimension('G')->setWidth(35);
$sheet->getColumnDimension('H')->setWidth(16);
$sheet->getColumnDimension('I')->setWidth(55);

// ============================================================
// Freeze rows 1 & 2
// ============================================================
$sheet->freezePane('A3');

// ============================================================
// Data soal (50 soal, kategori_id = 1)
// Format: [kategori_id, pertanyaan, a, b, c, d, e, kunci, pembahasan]
// ============================================================
$soal = [];
