<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VoucherModel;

class VoucherController extends BaseController
{
    protected VoucherModel $voucherModel;

    public function __construct()
    {
        $this->voucherModel = new VoucherModel();
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
     * Daftar semua voucher.
     */
    public function index()
    {
        // Auto-deactivate expired vouchers before listing
        $this->voucherModel->deactivateExpired();

        $vouchers = $this->voucherModel->orderBy('id', 'DESC')->findAll();

        return view('admin/voucher/index', [
            'vouchers' => $vouchers,
            'menus'    => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah voucher.
     */
    public function create()
    {
        return view('admin/voucher/form', [
            'voucher' => null,
            'action'  => base_url('admin/voucher/store'),
            'menus'   => $this->getMenus(),
        ]);
    }

    /**
     * Simpan voucher baru.
     */
    public function store()
    {
        $rules = [
            'kode'             => 'required',
            'jenis_diskon'     => 'required|in_list[persentase,nominal]',
            'nilai_diskon'     => 'required|decimal|greater_than[0]',
            'batas_penggunaan' => 'permit_empty|integer|greater_than[0]',
            'expired_at'       => 'permit_empty|valid_date[Y-m-d\TH:i]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Check kode uniqueness
        $kode = trim($this->request->getPost('kode'));
        $existing = $this->voucherModel->where('kode', $kode)->first();
        if ($existing) {
            return redirect()->back()->withInput()
                ->with('errors', ['kode' => 'Kode voucher sudah digunakan.']);
        }

        $expiredAt = $this->request->getPost('expired_at');

        $this->voucherModel->insert([
            'kode'             => $kode,
            'jenis_diskon'     => $this->request->getPost('jenis_diskon'),
            'nilai_diskon'     => (float) $this->request->getPost('nilai_diskon'),
            'batas_penggunaan' => $this->request->getPost('batas_penggunaan')
                                    ? (int) $this->request->getPost('batas_penggunaan')
                                    : null,
            'jumlah_digunakan' => 0,
            'expired_at'       => $expiredAt
                                    ? date('Y-m-d H:i:s', strtotime($expiredAt))
                                    : null,
            'is_active'        => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to(base_url('admin/voucher'))->with('success', 'Voucher berhasil ditambahkan.');
    }

    /**
     * Form edit voucher.
     */
    public function edit(int $id)
    {
        $voucher = $this->voucherModel->find($id);

        if (! $voucher) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Voucher tidak ditemukan.');
        }

        return view('admin/voucher/form', [
            'voucher' => $voucher,
            'action'  => base_url("admin/voucher/{$id}/update"),
            'menus'   => $this->getMenus(),
        ]);
    }

    /**
     * Update voucher.
     */
    public function update(int $id)
    {
        $voucher = $this->voucherModel->find($id);

        if (! $voucher) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Voucher tidak ditemukan.');
        }

        $rules = [
            'kode'             => 'required',
            'jenis_diskon'     => 'required|in_list[persentase,nominal]',
            'nilai_diskon'     => 'required|decimal|greater_than[0]',
            'batas_penggunaan' => 'permit_empty|integer|greater_than[0]',
            'expired_at'       => 'permit_empty|valid_date[Y-m-d\TH:i]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Check kode uniqueness (exclude self)
        $kode = trim($this->request->getPost('kode'));
        $existing = $this->voucherModel->where('kode', $kode)->where('id !=', $id)->first();
        if ($existing) {
            return redirect()->back()->withInput()
                ->with('errors', ['kode' => 'Kode voucher sudah digunakan.']);
        }

        $expiredAt = $this->request->getPost('expired_at');

        $this->voucherModel->update($id, [
            'kode'             => $kode,
            'jenis_diskon'     => $this->request->getPost('jenis_diskon'),
            'nilai_diskon'     => (float) $this->request->getPost('nilai_diskon'),
            'batas_penggunaan' => $this->request->getPost('batas_penggunaan')
                                    ? (int) $this->request->getPost('batas_penggunaan')
                                    : null,
            'expired_at'       => $expiredAt
                                    ? date('Y-m-d H:i:s', strtotime($expiredAt))
                                    : null,
            'is_active'        => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to(base_url('admin/voucher'))->with('success', 'Voucher berhasil diperbarui.');
    }

    /**
     * Hapus voucher.
     */
    public function delete(int $id)
    {
        $voucher = $this->voucherModel->find($id);

        if (! $voucher) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Voucher tidak ditemukan.');
        }

        $this->voucherModel->delete($id);

        return redirect()->to(base_url('admin/voucher'))->with('success', 'Voucher berhasil dihapus.');
    }
}
