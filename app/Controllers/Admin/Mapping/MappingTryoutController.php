<?php

namespace App\Controllers\Admin\Mapping;

use App\Controllers\BaseController;
use App\Models\MappingTryoutModel;
use App\Models\ProdukModel;
use App\Models\TryoutModel;

class MappingTryoutController extends BaseController
{
    protected MappingTryoutModel $mappingModel;
    protected ProdukModel        $produkModel;
    protected TryoutModel        $tryoutModel;

    public function __construct()
    {
        $this->mappingModel = new MappingTryoutModel();
        $this->produkModel  = new ProdukModel();
        $this->tryoutModel  = new TryoutModel();
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
     * Tampilkan halaman mapping tryout ke produk.
     * Jika produkId = 0, tampilkan pemilihan produk.
     */
    public function index(int $produkId = 0)
    {
        $db      = \Config\Database::connect();
        $produks = $db->table('produk')
            ->select('id, nama')
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();

        $mappedTryouts    = [];
        $availableTryouts = [];
        $selectedProduk   = null;
        $totalTryout      = 0;

        if ($produkId > 0) {
            $selectedProduk = $this->produkModel->find($produkId);

            if (! $selectedProduk) {
                return redirect()->to(base_url('admin/mapping/tryout'))
                    ->with('error', 'Produk tidak ditemukan.');
            }

            $mappedTryouts   = $this->mappingModel->getByProduk($produkId);
            $totalTryout     = $this->mappingModel->getTotalByProduk($produkId);
            $mappedTryoutIds = array_column($mappedTryouts, 'tryout_id');

            // Hitung jumlah soal dari mapping_soal (bukan kolom jumlah_soal)
            $builder = $db->table('tryout t')
                ->select('t.id, t.nama, t.durasi, COUNT(ms.soal_id) AS jumlah_soal')
                ->join('mapping_soal ms', 'ms.tryout_id = t.id', 'left')
                ->groupBy('t.id')
                ->orderBy('t.nama', 'ASC');

            if (! empty($mappedTryoutIds)) {
                $builder->whereNotIn('t.id', $mappedTryoutIds);
            }

            $availableTryouts = $builder->get()->getResultArray();
        }

        return view('admin/mapping/tryout/index', [
            'produks'          => $produks,
            'selectedProduk'   => $selectedProduk,
            'produkId'         => $produkId,
            'mappedTryouts'    => $mappedTryouts,
            'availableTryouts' => $availableTryouts,
            'totalTryout'      => $totalTryout,
            'menus'            => $this->getMenus(),
        ]);
    }

    /**
     * POST AJAX: Tambah tryout ke produk.
     * Validasi produk_id dan tryout_id, cek duplikasi.
     */
    public function store()
    {
        $produkId  = (int) $this->request->getPost('produk_id');
        $tryoutId  = (int) $this->request->getPost('tryout_id');

        if ($produkId <= 0 || $tryoutId <= 0) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'produk_id dan tryout_id wajib diisi.',
            ]);
        }

        // Cek duplikasi
        if ($this->mappingModel->isDuplicate($produkId, $tryoutId)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tryout sudah terdapat dalam produk ini',
            ]);
        }

        // Tentukan urutan berikutnya (max urutan + 1)
        $db     = \Config\Database::connect();
        $maxRow = $db->table('mapping_tryout')
            ->selectMax('urutan')
            ->where('produk_id', $produkId)
            ->get()
            ->getRowArray();

        $nextUrutan = ($maxRow && $maxRow['urutan'] !== null) ? (int) $maxRow['urutan'] + 1 : 1;

        $this->mappingModel->insert([
            'produk_id'  => $produkId,
            'tryout_id'  => $tryoutId,
            'urutan'     => $nextUrutan,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => true]);
    }

    /**
     * POST: Hapus mapping tryout dari produk.
     */
    public function delete(int $id)
    {
        $mapping = $this->mappingModel->find($id);

        if (! $mapping) {
            return redirect()->back()->with('error', 'Mapping tidak ditemukan.');
        }

        $produkId = (int) $mapping['produk_id'];
        $this->mappingModel->delete($id);

        return redirect()->to(base_url("admin/mapping/tryout/{$produkId}"))
            ->with('success', 'Tryout berhasil dihapus dari produk.');
    }

    /**
     * POST AJAX: Perbarui urutan untuk beberapa mapping sekaligus.
     * Menerima array of {id, urutan}.
     */
    public function updateUrutan()
    {
        $items = $this->request->getPost('items');

        if (! is_array($items)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Data urutan tidak valid.',
            ]);
        }

        foreach ($items as $item) {
            $id     = (int) ($item['id'] ?? 0);
            $urutan = (int) ($item['urutan'] ?? 0);

            if ($id > 0) {
                $this->mappingModel->updateUrutan($id, $urutan);
            }
        }

        return $this->response->setJSON(['status' => true]);
    }
}
