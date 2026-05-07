<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use App\Models\TryoutModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

class LaporanController extends BaseController
{
    protected ProdukModel  $produkModel;
    protected TryoutModel  $tryoutModel;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
        $this->tryoutModel = new TryoutModel();
    }

    // -------------------------------------------------------------------------
    // Helper: ambil menu sidebar
    // -------------------------------------------------------------------------
    private function getMenus(): array
    {
        $db = \Config\Database::connect();
        return $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();
    }

    // -------------------------------------------------------------------------
    // Helper: ambil filter dari request
    // -------------------------------------------------------------------------
    private function getFilters(): array
    {
        $tanggalDari    = $this->request->getGet('tanggal_dari')
                            ?? date('Y-m-01');          // hari pertama bulan ini
        $tanggalSampai  = $this->request->getGet('tanggal_sampai')
                            ?? date('Y-m-d');            // hari ini
        $status         = $this->request->getGet('status') ?? 'all';
        $produkId       = $this->request->getGet('produk_id');

        return compact('tanggalDari', 'tanggalSampai', 'status', 'produkId');
    }

    // -------------------------------------------------------------------------
    // Helper: query transaksi dengan filter
    // -------------------------------------------------------------------------
    private function queryTransaksi(array $filters): array
    {
        $db = \Config\Database::connect();

        $builder = $db->table('transaksi')
            ->select('transaksi.*, users.nama AS user_nama, produk.nama AS produk_nama')
            ->join('users',  'users.id  = transaksi.user_id')
            ->join('produk', 'produk.id = transaksi.produk_id')
            ->where('DATE(transaksi.created_at) >=', $filters['tanggalDari'])
            ->where('DATE(transaksi.created_at) <=', $filters['tanggalSampai'])
            ->orderBy('transaksi.id', 'DESC');

        if ($filters['status'] !== 'all' && $filters['status'] !== '') {
            $builder->where('transaksi.status', $filters['status']);
        }

        if (! empty($filters['produkId'])) {
            $builder->where('transaksi.produk_id', (int) $filters['produkId']);
        }

        return $builder->get()->getResultArray();
    }

    // =========================================================================
    // Laporan Transaksi
    // =========================================================================

    /**
     * GET admin/laporan/transaksi
     * Tampilkan laporan transaksi dengan filter.
     */
    public function transaksi()
    {
        $filters      = $this->getFilters();
        $transaksis   = $this->queryTransaksi($filters);

        // Hitung total pendapatan (hanya status success)
        $totalPendapatan = array_reduce($transaksis, function ($carry, $item) {
            if ($item['status'] === 'success') {
                $carry += (float) $item['harga_bayar'];
            }
            return $carry;
        }, 0.0);

        $produks = $this->produkModel->findAll();

        return view('admin/laporan/transaksi', [
            'transaksis'      => $transaksis,
            'totalPendapatan' => $totalPendapatan,
            'filters'         => $filters,
            'produks'         => $produks,
            'menus'           => $this->getMenus(),
        ]);
    }

    // =========================================================================
    // Laporan Tryout
    // =========================================================================

    /**
     * GET admin/laporan/tryout
     * Tampilkan statistik tryout.
     */
    public function tryout()
    {
        $tryoutId = $this->request->getGet('tryout_id');

        $db = \Config\Database::connect();

        $builder = $db->table('hasil_tryout')
            ->select('
                tryout.id        AS tryout_id,
                tryout.nama      AS tryout_nama,
                COUNT(hasil_tryout.id)          AS total_sesi,
                AVG(hasil_tryout.skor_total)    AS rata_rata_skor,
                MAX(hasil_tryout.skor_total)    AS skor_tertinggi,
                MIN(hasil_tryout.skor_total)    AS skor_terendah
            ')
            ->join('tryout', 'tryout.id = hasil_tryout.tryout_id')
            ->groupBy('tryout.id, tryout.nama')
            ->orderBy('tryout.id', 'ASC');

        if (! empty($tryoutId)) {
            $builder->where('hasil_tryout.tryout_id', (int) $tryoutId);
        }

        $statistik = $builder->get()->getResultArray();
        $tryouts   = $this->tryoutModel->findAll();

        return view('admin/laporan/tryout', [
            'statistik' => $statistik,
            'tryouts'   => $tryouts,
            'tryoutId'  => $tryoutId,
            'menus'     => $this->getMenus(),
        ]);
    }

    // =========================================================================
    // Export Excel
    // =========================================================================

    /**
     * GET admin/laporan/transaksi/export-excel
     * Export laporan transaksi ke file Excel.
     */
    public function exportTransaksiExcel()
    {
        $filters    = $this->getFilters();
        $transaksis = $this->queryTransaksi($filters);

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Transaksi');

        // Header baris
        $headers = [
            'A1' => 'No',
            'B1' => 'Kode Transaksi',
            'C1' => 'Nama User',
            'D1' => 'Produk',
            'E1' => 'Tanggal',
            'F1' => 'Harga Asli',
            'G1' => 'Diskon',
            'H1' => 'Harga Bayar',
            'I1' => 'Status',
        ];

        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Isi data
        $row = 2;
        foreach ($transaksis as $i => $t) {
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $t['kode_transaksi']);
            $sheet->setCellValue("C{$row}", $t['user_nama']);
            $sheet->setCellValue("D{$row}", $t['produk_nama']);
            $sheet->setCellValue("E{$row}", $t['created_at']);
            $sheet->setCellValue("F{$row}", (float) $t['harga_asli']);
            $sheet->setCellValue("G{$row}", (float) $t['diskon']);
            $sheet->setCellValue("H{$row}", (float) $t['harga_bayar']);
            $sheet->setCellValue("I{$row}", strtoupper($t['status']));
            $row++;
        }

        // Auto-size kolom
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Nama file
        $filename = 'laporan_transaksi_' . $filters['tanggalDari'] . '_sd_' . $filters['tanggalSampai'] . '.xlsx';

        // Response headers untuk download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // =========================================================================
    // Export PDF
    // =========================================================================

    /**
     * GET admin/laporan/transaksi/export-pdf
     * Export laporan transaksi ke file PDF.
     */
    public function exportTransaksiPdf()
    {
        $filters    = $this->getFilters();
        $transaksis = $this->queryTransaksi($filters);

        // Hitung total pendapatan
        $totalPendapatan = array_reduce($transaksis, function ($carry, $item) {
            if ($item['status'] === 'success') {
                $carry += (float) $item['harga_bayar'];
            }
            return $carry;
        }, 0.0);

        // Render HTML view untuk PDF
        $html = view('admin/laporan/pdf_transaksi', [
            'transaksis'      => $transaksis,
            'totalPendapatan' => $totalPendapatan,
            'filters'         => $filters,
        ]);

        // Konfigurasi DomPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'laporan_transaksi_' . $filters['tanggalDari'] . '_sd_' . $filters['tanggalSampai'] . '.pdf';

        // Response headers untuk download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo $dompdf->output();
        exit;
    }
}
