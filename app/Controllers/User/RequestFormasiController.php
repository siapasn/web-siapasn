<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class RequestFormasiController extends BaseController
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
     * Submit request formasi tryout.
     */
    public function store()
    {
        $userId    = (int) session()->get('user_id');
        $formasiId = (int) $this->request->getPost('formasi_id');
        $pesan     = $this->request->getPost('pesan');

        if ($formasiId <= 0) {
            return redirect()->back()->with('error', 'Formasi tidak valid.');
        }

        $db = \Config\Database::connect();

        // Cek formasi ada
        $formasi = $db->table('formasi')->where('id', $formasiId)->get()->getRowArray();
        if (! $formasi) {
            return redirect()->back()->with('error', 'Formasi tidak ditemukan.');
        }

        // Cek apakah sudah pernah request formasi yang sama dan masih pending
        $existing = $db->table('request_formasi')
            ->where('user_id', $userId)
            ->where('formasi_id', $formasiId)
            ->where('status', 'pending')
            ->countAllResults();

        if ($existing > 0) {
            return redirect()->back()->with('error', 'Anda sudah pernah request formasi ini. Mohon tunggu respon dari admin.');
        }

        $db->table('request_formasi')->insert([
            'user_id'    => $userId,
            'formasi_id' => $formasiId,
            'pesan'      => $pesan ?: null,
            'status'     => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Request berhasil dikirim! Admin akan meninjau permintaan Anda.');
    }
}
