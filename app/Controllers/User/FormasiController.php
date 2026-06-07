<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class FormasiController extends BaseController
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

    /**
     * Daftar formasi SKB — tabel semua formasi dikelompokkan per kategori,
     * dengan status ketersediaan produk tryout.
     */
    public function index()
    {
        $db     = \Config\Database::connect();
        $userId = (int) session()->get('user_id');

        // Ambil semua formasi aktif yang punya referensi (referensi IS NOT NULL dan > 0),
        // diurutkan: kategori (urutan ASC) → formasi (nama ASC)
        $formasiRaw = $db->table('formasi f')
            ->select('f.id, f.nama, f.deskripsi, f.kategori_formasi_id,
                      kf.nama AS kategori_nama, kf.icon AS kategori_icon, kf.urutan AS kategori_urutan')
            ->join('kategori_formasi kf', 'kf.id = f.kategori_formasi_id', 'left')
            ->where('f.is_active', 1)
            ->where('f.referensi IS NOT NULL', null, false)
            ->where('f.referensi >', 0)
            ->orderBy('kf.urutan', 'ASC')
            ->orderBy('kf.nama', 'ASC')
            ->orderBy('f.nama', 'ASC')
            ->get()->getResultArray();

        // Ambil semua formasi_id yang sudah punya produk aktif dengan tryout ter-mapping
        $produkFormasiRows = $db->table('produk p')
            ->select('p.formasi_id, p.id AS produk_id, p.slug AS produk_slug, p.nama AS produk_nama')
            ->join('mapping_tryout mt', 'mt.produk_id = p.id', 'inner')
            ->where('p.is_active', 1)
            ->where('p.formasi_id IS NOT NULL', null, false)
            ->groupBy('p.formasi_id')
            ->having('COUNT(mt.id) >', 0)
            ->get()->getResultArray();

        // Build map: formasi_id → produk data
        $produkByFormasi = [];
        foreach ($produkFormasiRows as $row) {
            $produkByFormasi[(int) $row['formasi_id']] = [
                'produk_id'   => $row['produk_id'],
                'produk_slug' => $row['produk_slug'] ?: $row['produk_id'],
                'produk_nama' => $row['produk_nama'],
            ];
        }

        // Ambil formasi_id yang user sudah pernah request (status pending/approved)
        $requestedFormasiIds = [];
        if ($userId > 0) {
            $requestRows = $db->table('request_formasi')
                ->select('formasi_id')
                ->where('user_id', $userId)
                ->whereIn('status', ['pending', 'approved'])
                ->get()->getResultArray();
            $requestedFormasiIds = array_column($requestRows, 'formasi_id');
        }

        // Kelompokkan formasi per kategori
        $formasiByKategori = [];
        $nomor = 1;

        foreach ($formasiRaw as $f) {
            $katKey = $f['kategori_formasi_id'] ?? 0;
            $katLabel = $f['kategori_nama'] ?? 'Lainnya';
            $katIcon  = $f['kategori_icon']  ?? 'bi-briefcase';

            if (! isset($formasiByKategori[$katKey])) {
                $formasiByKategori[$katKey] = [
                    'kategori_id'   => $katKey,
                    'kategori_nama' => $katLabel,
                    'kategori_icon' => $katIcon,
                    'items'         => [],
                ];
            }

            $hasProduk   = isset($produkByFormasi[(int) $f['id']]);
            $hasRequested = in_array((string) $f['id'], array_map('strval', $requestedFormasiIds));

            $formasiByKategori[$katKey]['items'][] = [
                'no'            => $nomor++,
                'id'            => $f['id'],
                'nama'          => $f['nama'],
                'deskripsi'     => $f['deskripsi'],
                'has_produk'    => $hasProduk,
                'produk'        => $hasProduk ? $produkByFormasi[(int) $f['id']] : null,
                'has_requested' => $hasRequested,
            ];
        }

        // Reset to indexed array
        $formasiByKategori = array_values($formasiByKategori);

        // Statistik ringkas
        $totalFormasi   = count($formasiRaw);
        $totalTersedia  = count($produkFormasiRows);
        $totalBelumAda  = $totalFormasi - $totalTersedia;

        return view('user/formasi/index', [
            'formasiByKategori' => $formasiByKategori,
            'totalFormasi'      => $totalFormasi,
            'totalTersedia'     => $totalTersedia,
            'totalBelumAda'     => $totalBelumAda,
            'menus'             => $this->getMenus(),
        ]);
    }
}
