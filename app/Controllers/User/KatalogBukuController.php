<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\KatalogBukuModel;

class KatalogBukuController extends BaseController
{
    protected KatalogBukuModel $katalogBukuModel;

    public function __construct()
    {
        $this->katalogBukuModel = new KatalogBukuModel();
    }

    /**
     * Tampilkan katalog buku aktif (tanpa kategori, flat list).
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $buku = $this->katalogBukuModel->getAktif();

        $menus = $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();

        return view('user/katalog-buku/index', [
            'buku'  => $buku,
            'menus' => $menus,
        ]);
    }
}
