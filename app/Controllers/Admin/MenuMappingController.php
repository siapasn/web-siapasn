<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MenuMappingModel;

/**
 * MenuMappingController
 *
 * Mengelola konfigurasi visibilitas dan urutan menu per role.
 *
 * CATATAN: Menu dimuat dari tabel `menu_mapping` pada setiap request
 * (melalui method getMenus() di masing-masing controller). Oleh karena itu,
 * perubahan yang disimpan via save() langsung berlaku pada page load berikutnya
 * tanpa perlu restart server atau clear cache apapun.
 */
class MenuMappingController extends BaseController
{
    protected MenuMappingModel $menuMappingModel;

    public function __construct()
    {
        $this->menuMappingModel = new MenuMappingModel();
    }

    /**
     * Ambil menu sidebar untuk user yang sedang login (digunakan oleh layout admin).
     */
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
     * Halaman utama menu mapping — tampilkan selector role dan daftar menu.
     * Default role: 'user'.
     */
    public function index()
    {
        $role  = $this->request->getGet('role') ?? 'user';
        $roles = ['user', 'admin', 'super_admin'];

        // Pastikan role valid
        if (! in_array($role, $roles, true)) {
            $role = 'user';
        }

        $menus = $this->menuMappingModel->getByRole($role);

        return view('admin/menu-mapping/index', [
            'activeRole'   => $role,
            'roles'        => $roles,
            'menus'        => $this->getMenus(),
            'menuItems'    => $menus,
        ]);
    }

    /**
     * POST AJAX — Simpan perubahan visibilitas dan urutan menu untuk suatu role.
     *
     * Menerima JSON array berisi item menu dengan field:
     *   - id        : int
     *   - is_visible: 0|1
     *   - urutan    : int
     *
     * Mengembalikan JSON {status: true} jika berhasil.
     *
     * Perubahan langsung berlaku pada request berikutnya karena menu dimuat
     * dari database pada setiap request (tidak ada cache statis).
     */
    public function save()
    {
        $json  = $this->request->getJSON(true);
        $items = $json['items'] ?? [];

        if (empty($items) || ! is_array($items)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak valid.']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $this->menuMappingModel->update($id, [
                'is_visible' => (int) ($item['is_visible'] ?? 1),
                'urutan'     => (int) ($item['urutan'] ?? 0),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan perubahan.']);
        }

        return $this->response->setJSON(['status' => true, 'message' => 'Menu berhasil disimpan.']);
    }

    /**
     * GET AJAX — Kembalikan daftar menu untuk role tertentu (untuk preview sebelum simpan).
     *
     * Query param: ?role=user|admin|super_admin
     */
    public function preview()
    {
        $role  = $this->request->getGet('role') ?? 'user';
        $roles = ['user', 'admin', 'super_admin'];

        if (! in_array($role, $roles, true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Role tidak valid.']);
        }

        $menus = $this->menuMappingModel->getByRole($role);

        return $this->response->setJSON([
            'status' => true,
            'role'   => $role,
            'menus'  => $menus,
        ]);
    }
}
