<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ShareController extends BaseController
{
    /**
     * Halaman publik share hasil tryout (tanpa login).
     */
    public function hasil(string $token)
    {
        $db = \Config\Database::connect();

        $hasil = $db->table('hasil_tryout')
            ->where('share_token', $token)
            ->get()->getRowArray();

        if (! $hasil) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Hasil tidak ditemukan.');
        }

        $user = $db->table('users')->select('nama')->where('id', $hasil['user_id'])->get()->getRowArray();
        $tryout = $db->table('tryout')->select('nama, durasi')->where('id', $hasil['tryout_id'])->get()->getRowArray();

        // Decode detail kategori
        $detailKategori = [];
        if (! empty($hasil['detail_kategori'])) {
            $decoded = json_decode($hasil['detail_kategori'], true);
            if (is_array($decoded)) $detailKategori = $decoded;
        }

        // Decode detail passing grade
        $detailPg = [];
        if (! empty($hasil['detail_passing_grade'])) {
            $decoded = json_decode($hasil['detail_passing_grade'], true);
            if (is_array($decoded)) $detailPg = $decoded;
        }

        // Tentukan tipe tryout (SKD atau SKB)
        $tipeTryout = 'SKD';
        if (! empty($detailKategori)) {
            $firstKat = $detailKategori[0] ?? [];
            // Jika hanya 1 kategori dan bukan TWK/TIU/TKP → kemungkinan SKB
            if (count($detailKategori) === 1 || (! empty($firstKat['kategori_nama']) && ! in_array($firstKat['kategori_nama'], ['Tes Wawasan Kebangsaan', 'Tes Intelegensia Umum', 'Tes Karakteristik Pribadi']))) {
                $tipeTryout = 'SKB';
            }
        }

        return view('share/hasil', [
            'hasil'           => $hasil,
            'user'            => $user,
            'tryout'          => $tryout,
            'detailKategori'  => $detailKategori,
            'detailPg'        => $detailPg,
            'tipeTryout'      => $tipeTryout,
            'token'           => $token,
        ]);
    }

    /**
     * AJAX: Generate share token untuk hasil tryout.
     */
    public function generateToken(int $sesiId)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();

        $hasil = $db->table('hasil_tryout')
            ->where('sesi_tryout_id', $sesiId)
            ->where('user_id', $userId)
            ->get()->getRowArray();

        if (! $hasil) {
            return $this->response->setJSON(['status' => false, 'message' => 'Hasil tidak ditemukan.']);
        }

        // Jika sudah punya token, kembalikan yang ada
        if (! empty($hasil['share_token'])) {
            return $this->response->setJSON([
                'status' => true,
                'token'  => $hasil['share_token'],
                'url'    => base_url('share/hasil/' . $hasil['share_token']),
            ]);
        }

        // Generate token baru
        $token = bin2hex(random_bytes(16));

        $db->table('hasil_tryout')
            ->where('id', $hasil['id'])
            ->update(['share_token' => $token]);

        return $this->response->setJSON([
            'status' => true,
            'token'  => $token,
            'url'    => base_url('share/hasil/' . $token),
        ]);
    }
}
