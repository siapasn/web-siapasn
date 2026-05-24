<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PromosiModel;
use App\Models\ProdukModel;

class PromosiController extends BaseController
{
    protected PromosiModel $promosiModel;
    protected ProdukModel  $produkModel;

    public function __construct()
    {
        $this->promosiModel = new PromosiModel();
        $this->produkModel  = new ProdukModel();
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
     * Daftar semua promosi beserta nama produk.
     */
    public function index()
    {
        // Auto-deactivate expired promosi before listing
        $this->promosiModel->deactivateExpired();

        $promosis = $this->promosiModel
            ->select('promosi.*, produk.nama AS nama_produk')
            ->join('produk', 'produk.id = promosi.produk_id', 'left')
            ->orderBy('promosi.id', 'DESC')
            ->findAll();

        return view('admin/promosi/index', [
            'promosis' => $promosis,
            'menus'    => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah promosi.
     */
    public function create()
    {
        $produks = $this->produkModel->getAktif();

        return view('admin/promosi/form', [
            'promosi'          => null,
            'produks'          => $produks,
            'selectedProdukIds'=> [],
            'action'           => base_url('admin/promosi/store'),
            'menus'            => $this->getMenus(),
        ]);
    }

    /**
     * Simpan promosi baru.
     * Jika multiple produk dipilih, buat satu record promosi per produk.
     */
    public function store()
    {
        $rules = [
            'nama'        => 'required',
            'produk_ids'  => 'required',
            'jenis_diskon'=> 'required|in_list[persentase,nominal]',
            'nilai_diskon'=> 'required|decimal|greater_than[0]',
            'mulai_at'    => 'required|valid_date[Y-m-d\TH:i]',
            'berakhir_at' => 'required|valid_date[Y-m-d\TH:i]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $mulaiAt    = $this->request->getPost('mulai_at');
        $berakhirAt = $this->request->getPost('berakhir_at');

        if (strtotime($berakhirAt) <= strtotime($mulaiAt)) {
            return redirect()->back()->withInput()
                ->with('errors', ['berakhir_at' => 'Tanggal berakhir harus setelah tanggal mulai.']);
        }

        $produkIds = $this->request->getPost('produk_ids'); // array
        if (! is_array($produkIds)) {
            $produkIds = [$produkIds];
        }

        $base = [
            'nama'        => $this->request->getPost('nama'),
            'deskripsi'   => $this->request->getPost('deskripsi') ?? null,
            'jenis_diskon'=> $this->request->getPost('jenis_diskon'),
            'nilai_diskon'=> (float) $this->request->getPost('nilai_diskon'),
            'mulai_at'    => date('Y-m-d H:i:s', strtotime($mulaiAt)),
            'berakhir_at' => date('Y-m-d H:i:s', strtotime($berakhirAt)),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ];

        foreach ($produkIds as $produkId) {
            $this->promosiModel->insert(array_merge($base, ['produk_id' => (int) $produkId]));
        }

        $jumlah = count($produkIds);
        return redirect()->to(base_url('admin/promosi'))
            ->with('success', "Promosi berhasil ditambahkan untuk {$jumlah} produk.");
    }

    /**
     * Form edit promosi.
     */
    public function edit(int $id)
    {
        $promosi = $this->promosiModel->find($id);

        if (! $promosi) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Promosi tidak ditemukan.');
        }

        $produks = $this->produkModel->getAktif();

        return view('admin/promosi/form', [
            'promosi'          => $promosi,
            'produks'          => $produks,
            'selectedProdukIds'=> [$promosi['produk_id']], // pre-select produk saat ini
            'action'           => base_url("admin/promosi/{$id}/update"),
            'menus'            => $this->getMenus(),
        ]);
    }

    /**
     * Update promosi.
     * Jika multiple produk dipilih, update record ini + buat record baru untuk produk tambahan.
     */
    public function update(int $id)
    {
        $promosi = $this->promosiModel->find($id);

        if (! $promosi) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Promosi tidak ditemukan.');
        }

        $rules = [
            'nama'        => 'required',
            'produk_ids'  => 'required',
            'jenis_diskon'=> 'required|in_list[persentase,nominal]',
            'nilai_diskon'=> 'required|decimal|greater_than[0]',
            'mulai_at'    => 'required|valid_date[Y-m-d\TH:i]',
            'berakhir_at' => 'required|valid_date[Y-m-d\TH:i]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $mulaiAt    = $this->request->getPost('mulai_at');
        $berakhirAt = $this->request->getPost('berakhir_at');

        if (strtotime($berakhirAt) <= strtotime($mulaiAt)) {
            return redirect()->back()->withInput()
                ->with('errors', ['berakhir_at' => 'Tanggal berakhir harus setelah tanggal mulai.']);
        }

        $produkIds = $this->request->getPost('produk_ids');
        if (! is_array($produkIds)) {
            $produkIds = [$produkIds];
        }

        $base = [
            'nama'        => $this->request->getPost('nama'),
            'deskripsi'   => $this->request->getPost('deskripsi') ?? null,
            'jenis_diskon'=> $this->request->getPost('jenis_diskon'),
            'nilai_diskon'=> (float) $this->request->getPost('nilai_diskon'),
            'mulai_at'    => date('Y-m-d H:i:s', strtotime($mulaiAt)),
            'berakhir_at' => date('Y-m-d H:i:s', strtotime($berakhirAt)),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Update record pertama (yang sedang diedit)
        $this->promosiModel->update($id, array_merge($base, [
            'produk_id' => (int) $produkIds[0],
        ]));

        // Buat record baru untuk produk tambahan (index 1+)
        for ($i = 1; $i < count($produkIds); $i++) {
            $this->promosiModel->insert(array_merge($base, [
                'produk_id' => (int) $produkIds[$i],
            ]));
        }

        return redirect()->to(base_url('admin/promosi'))->with('success', 'Promosi berhasil diperbarui.');
    }

    /**
     * Hapus promosi.
     */
    public function delete(int $id)
    {
        $promosi = $this->promosiModel->find($id);

        if (! $promosi) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Promosi tidak ditemukan.');
        }

        $this->promosiModel->delete($id);

        return redirect()->to(base_url('admin/promosi'))->with('success', 'Promosi berhasil dihapus.');
    }
}
