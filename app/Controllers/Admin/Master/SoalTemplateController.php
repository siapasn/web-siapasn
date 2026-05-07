<?php

namespace App\Controllers\Admin\Master;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class SoalTemplateController extends BaseController
{
    /**
     * Download template Excel import soal.
     * Sheet 1: POINT (TWK/TIU) — kunci jawaban
     * Sheet 2: SCORE (TKP)     — nilai A-E
     * Sheet 3: Referensi kategori
     */
    public function download()
    {
        // Ambil data kategori dari DB
        $db         = \Config\Database::connect();
        $kategoris  = $db->table('kategori k')
            ->select('k.id, k.nama, k.parent_id, k.tipe_soal, p.nama AS parent_nama')
            ->join('kategori p', 'p.id = k.parent_id', 'left')
            ->orderBy('k.parent_id', 'ASC')
            ->orderBy('k.id', 'ASC')
            ->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('SiapASN Simulation Center')
            ->setTitle('Template Import Soal CPNS')
            ->setDescription('Template import soal untuk SiapASN Simulation Center');

        // ── Sheet 1: POINT (TWK / TIU) ──────────────────────────────────────
        $sheetPoint = $spreadsheet->getActiveSheet();
        $sheetPoint->setTitle('POINT (TWK-TIU)');
        $this->buildSheetPoint($sheetPoint, $kategoris);

        // ── Sheet 2: SCORE (TKP) ────────────────────────────────────────────
        $sheetScore = $spreadsheet->createSheet();
        $sheetScore->setTitle('SCORE (TKP)');
        $this->buildSheetScore($sheetScore, $kategoris);

        // ── Sheet 3: Referensi Kategori ──────────────────────────────────────
        $sheetRef = $spreadsheet->createSheet();
        $sheetRef->setTitle('Referensi Kategori');
        $this->buildSheetRef($sheetRef, $kategoris);

        // Set sheet aktif ke sheet pertama
        $spreadsheet->setActiveSheetIndex(0);

        // Output sebagai file download
        $filename = 'template_import_soal_cpns_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sheet 1: POINT — kunci jawaban (TWK, TIU)
    // Kolom: A=nama_kategori, B=nama_sub_kategori, C=pertanyaan, D=pilihan_a,
    //        E=pilihan_b, F=pilihan_c, G=pilihan_d, H=pilihan_e,
    //        I=kunci_jawaban, J=pembahasan
    // ─────────────────────────────────────────────────────────────────────────
    private function buildSheetPoint($sheet, array $kategoris): void
    {
        // ── Judul ──
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT SOAL — TIPE POINT (TWK / TIU)');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Keterangan tipe ──
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'Gunakan sheet ini untuk soal TWK dan TIU. Isi kolom Kunci Jawaban dengan huruf: a / b / c / d / e. Lihat sheet "Referensi Kategori" untuk nama yang valid.');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'color' => ['rgb' => '1F4E79']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D6E4F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
        ]);

        // ── Header kolom ──
        $headers = [
            'A' => 'nama_kategori *',
            'B' => 'nama_sub_kategori',
            'C' => 'pertanyaan *',
            'D' => 'pilihan_a *',
            'E' => 'pilihan_b *',
            'F' => 'pilihan_c *',
            'G' => 'pilihan_d *',
            'H' => 'pilihan_e',
            'I' => 'kunci_jawaban *',
            'J' => 'pembahasan',
        ];
        $this->writeHeaders($sheet, 3, $headers, '1F4E79');

        // ── Contoh soal TWK ──
        $sheet->fromArray([
            'CPNS', 'TWK',
            'Pancasila sebagai dasar negara Indonesia pertama kali dirumuskan dalam sidang BPUPKI. Siapakah yang mengusulkan nama "Pancasila"?',
            'Ir. Soekarno',
            'Drs. Mohammad Hatta',
            'Mr. Soepomo',
            'Mr. Muhammad Yamin',
            '',
            'a',
            'Ir. Soekarno mengusulkan nama Pancasila pada sidang BPUPKI tanggal 1 Juni 1945.',
        ], null, 'A4');

        // ── Contoh soal TIU ──
        $sheet->fromArray([
            'CPNS', 'TIU',
            'Jika 3x + 7 = 22, maka nilai x adalah...',
            '3',
            '4',
            '5',
            '6',
            '',
            'c',
            '3x = 22 - 7 = 15, maka x = 15/3 = 5.',
        ], null, 'A5');

        // ── Style baris contoh ──
        $this->styleDataRows($sheet, 4, 5, 'A', 'J');

        // ── Lebar kolom ──
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(55);
        foreach (['D','E','F','G','H'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(28);
        }
        $sheet->getColumnDimension('I')->setWidth(16);
        $sheet->getColumnDimension('J')->setWidth(40);

        // Wrap text kolom pertanyaan & pilihan
        foreach (['C','D','E','F','G','H','J'] as $col) {
            $sheet->getStyle($col . '4:' . $col . '1000')
                ->getAlignment()->setWrapText(true);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sheet 2: SCORE — nilai A-E (TKP)
    // Kolom: A=nama_kategori, B=nama_sub_kategori, C=pertanyaan, D=pilihan_a,
    //        E=pilihan_b, F=pilihan_c, G=pilihan_d, H=pilihan_e,
    //        I=nilai_a, J=nilai_b, K=nilai_c, L=nilai_d, M=nilai_e,
    //        N=pembahasan
    // ─────────────────────────────────────────────────────────────────────────
    private function buildSheetScore($sheet, array $kategoris): void
    {
        // ── Judul ──
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT SOAL — TIPE SCORE (TKP)');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7B3F00']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Keterangan tipe ──
        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A2', 'Gunakan sheet ini untuk soal TKP. Isi nilai_a s/d nilai_e dengan angka 1–5. Setiap nilai HARUS BERBEDA. Lihat sheet "Referensi Kategori" untuk nama yang valid.');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'color' => ['rgb' => '7B3F00']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3CD']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
        ]);

        // ── Header kolom ──
        $headers = [
            'A' => 'nama_kategori *',
            'B' => 'nama_sub_kategori',
            'C' => 'pertanyaan *',
            'D' => 'pilihan_a *',
            'E' => 'pilihan_b *',
            'F' => 'pilihan_c *',
            'G' => 'pilihan_d *',
            'H' => 'pilihan_e',
            'I' => 'nilai_a *',
            'J' => 'nilai_b *',
            'K' => 'nilai_c *',
            'L' => 'nilai_d *',
            'M' => 'nilai_e *',
            'N' => 'pembahasan',
        ];
        $this->writeHeaders($sheet, 3, $headers, '7B3F00');

        // ── Contoh soal TKP ──
        $sheet->fromArray([
            'CPNS', 'TKP',
            'Rekan kerja Anda meminta bantuan menyelesaikan tugasnya karena ia sedang sakit. Padahal Anda sendiri juga memiliki pekerjaan yang harus diselesaikan hari ini. Apa yang Anda lakukan?',
            'Menolak karena pekerjaan saya sendiri belum selesai',
            'Membantu sebagian sambil tetap menyelesaikan pekerjaan saya',
            'Membantu sepenuhnya dan menunda pekerjaan saya',
            'Melaporkan kepada atasan agar rekan kerja mendapat bantuan lain',
            'Mengabaikan permintaan tersebut',
            2, 5, 4, 3, 1,
            'Pilihan B mencerminkan keseimbangan antara empati dan tanggung jawab pribadi.',
        ], null, 'A4');

        // ── Style baris contoh ──
        $this->styleDataRows($sheet, 4, 4, 'A', 'N');

        // ── Highlight kolom nilai ──
        $sheet->getStyle('I3:M3')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC000']],
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
        ]);
        $sheet->getStyle('I4:M4')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3CD']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Lebar kolom ──
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(55);
        foreach (['D','E','F','G','H'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(28);
        }
        foreach (['I','J','K','L','M'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(10);
        }
        $sheet->getColumnDimension('N')->setWidth(40);

        // Wrap text
        foreach (['C','D','E','F','G','H','N'] as $col) {
            $sheet->getStyle($col . '4:' . $col . '1000')
                ->getAlignment()->setWrapText(true);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sheet 3: Referensi Kategori
    // ─────────────────────────────────────────────────────────────────────────
    private function buildSheetRef($sheet, array $kategoris): void
    {
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'REFERENSI KATEGORI — Gunakan kolom "Nama" di kolom nama_kategori dan nama_sub_kategori');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '375623']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Header
        $headers = ['A' => 'Nama', 'B' => 'Tipe Soal', 'C' => 'Jenis', 'D' => 'Parent (Kategori Induk)'];
        $this->writeHeaders($sheet, 2, $headers, '375623');

        $row = 3;
        foreach ($kategoris as $k) {
            $jenis = $k['parent_id'] === null ? 'Kategori Induk' : 'Sub Kategori';
            $tipe  = $k['tipe_soal'] ?? '—';
            $sheet->fromArray([
                $k['nama'],
                $tipe,
                $jenis,
                $k['parent_nama'] ?? '—',
            ], null, 'A' . $row);

            // Warna baris berdasarkan tipe
            $bgColor = '';
            if ($k['tipe_soal'] === 'SCORE') {
                $bgColor = 'FFF3CD';
            } elseif ($k['tipe_soal'] === 'POINT') {
                $bgColor = 'D6E4F0';
            } elseif ($k['parent_id'] === null) {
                $bgColor = 'F2F2F2';
            }

            if ($bgColor) {
                $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                ]);
            }

            $this->addBorder($sheet, 'A' . $row . ':D' . $row);
            $row++;
        }

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(22);

        // Legenda
        $sheet->setCellValue('A' . ($row + 1), 'Keterangan warna:');
        $sheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);

        $sheet->setCellValue('A' . ($row + 2), 'Kuning');
        $sheet->getStyle('A' . ($row + 2))->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3CD']],
        ]);
        $sheet->setCellValue('B' . ($row + 2), '= SCORE (TKP) — isi nilai_a s/d nilai_e');

        $sheet->setCellValue('A' . ($row + 3), 'Biru');
        $sheet->getStyle('A' . ($row + 3))->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D6E4F0']],
        ]);
        $sheet->setCellValue('B' . ($row + 3), '= POINT (TWK/TIU) — isi kunci_jawaban');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: tulis baris header dengan style
    // ─────────────────────────────────────────────────────────────────────────
    private function writeHeaders($sheet, int $row, array $headers, string $bgHex): void
    {
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . $row, $label);
        }
        $cols   = array_keys($headers);
        $range  = $cols[0] . $row . ':' . end($cols) . $row;
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgHex]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: style baris data contoh
    // ─────────────────────────────────────────────────────────────────────────
    private function styleDataRows($sheet, int $from, int $to, string $colStart, string $colEnd): void
    {
        $range = $colStart . $from . ':' . $colEnd . $to;
        $sheet->getStyle($range)->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9F9F9']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP],
        ]);
        for ($r = $from; $r <= $to; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(60);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: tambah border tipis
    // ─────────────────────────────────────────────────────────────────────────
    private function addBorder($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);
    }
}
