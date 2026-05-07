<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    private function getMenus(): array
    {
        $db = \Config\Database::connect();
        return $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();
    }

    public function index()
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // Total user terdaftar (role = 'user')
        $totalUser = $db->table('users')
            ->where('role', 'user')
            ->countAllResults();

        // Total transaksi hari ini
        $totalTransaksiHariIni = $db->table('transaksi')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();

        // Total pendapatan bulan ini (status = 'success')
        $pendapatanBulanIni = (float) ($db->table('transaksi')
            ->selectSum('harga_bayar')
            ->where('status', 'success')
            ->where('YEAR(created_at)', date('Y'))
            ->where('MONTH(created_at)', date('n'))
            ->get()->getRow()->harga_bayar ?? 0);

        // Jumlah sesi tryout yang sedang berlangsung
        $sesiSedangBerlangsung = $db->table('sesi_tryout')
            ->where('status', 'berlangsung')
            ->countAllResults();

        // Tren transaksi 30 hari terakhir (untuk Chart.js)
        $trenTransaksi = $db->table('transaksi')
            ->select("DATE(created_at) as tanggal, COUNT(*) as jumlah, SUM(CASE WHEN status='success' THEN harga_bayar ELSE 0 END) as pendapatan")
            ->where('created_at >=', date('Y-m-d', strtotime('-29 days')))
            ->groupBy('DATE(created_at)')
            ->orderBy('tanggal', 'ASC')
            ->get()->getResultArray();

        // 10 transaksi terbaru
        $transaksiTerbaru = $db->table('transaksi t')
            ->select('t.*, u.nama as user_nama, p.nama as produk_nama')
            ->join('users u', 'u.id = t.user_id')
            ->join('produk p', 'p.id = t.produk_id')
            ->orderBy('t.created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return view('admin/dashboard', [
            'totalUser'             => $totalUser,
            'totalTransaksiHariIni' => $totalTransaksiHariIni,
            'pendapatanBulanIni'    => $pendapatanBulanIni,
            'sesiSedangBerlangsung' => $sesiSedangBerlangsung,
            'trenTransaksi'         => $trenTransaksi,
            'transaksiTerbaru'      => $transaksiTerbaru,
            'menus'                 => $this->getMenus(),
        ]);
    }
}
