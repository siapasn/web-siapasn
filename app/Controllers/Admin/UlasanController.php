<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UlasanModel;

class UlasanController extends BaseController
{
    protected UlasanModel $ulasanModel;

    public function __construct()
    {
        $this->ulasanModel = new UlasanModel();
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
     * Daftar semua ulasan.
     */
    public function index()
    {
        $ulasans = $this->ulasanModel->getAllWithDetails();

        return view('admin/ulasan/index', [
            'ulasans' => $ulasans,
            'menus'   => $this->getMenus(),
        ]);
    }

    /**
     * Toggle visibility ulasan.
     */
    public function toggle(int $id)
    {
        $ulasan = $this->ulasanModel->find($id);
        if (! $ulasan) {
            return redirect()->back()->with('error', 'Ulasan tidak ditemukan.');
        }

        $newStatus = (int) $ulasan['is_visible'] === 1 ? 0 : 1;
        $this->ulasanModel->update($id, ['is_visible' => $newStatus]);

        $msg = $newStatus ? 'Ulasan ditampilkan.' : 'Ulasan disembunyikan.';
        return redirect()->back()->with('success', $msg);
    }

    /**
     * Hapus ulasan.
     */
    public function delete(int $id)
    {
        $ulasan = $this->ulasanModel->find($id);
        if (! $ulasan) {
            return redirect()->back()->with('error', 'Ulasan tidak ditemukan.');
        }

        $this->ulasanModel->delete($id);
        return redirect()->back()->with('success', 'Ulasan berhasil dihapus.');
    }
}
