<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\MasterDataFileModel;
use App\Models\ProdukMateriModel;
use App\Models\UserProdukModel;

class MateriFileController extends BaseController
{
    /**
     * Serve file materi pelajaran.
     *
     * Validasi:
     * 1. User harus login (dijamin oleh filter 'auth' di Routes.php)
     * 2. File harus terdaftar di produk_materi
     * 3. User harus memiliki akses aktif ke produk yang memuat materi tersebut
     *
     * @param int $fileId  ID dari tabel master_data_file
     */
    public function serve(int $fileId)
    {
        $userId      = (int) session()->get('user_id');
        $fileModel   = new MasterDataFileModel();
        $materiModel = new ProdukMateriModel();
        $userProduk  = new UserProdukModel();

        // 1. Cek file ada di master_data_file
        $record = $fileModel->find($fileId);
        if (! $record) {
            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan.');
        }

        // 2. Bangun URL serve admin untuk dicocokkan dengan url_file di produk_materi
        $serveUrl = base_url("admin/master/datafile/{$fileId}/serve");

        // 3. Cari materi yang menggunakan URL ini
        $materi = $materiModel->where('url_file', $serveUrl)->first();
        if (! $materi) {
            return $this->response->setStatusCode(403)
                ->setBody('File ini tidak terdaftar sebagai materi pelajaran.');
        }

        // 4. Cek apakah user memiliki akses ke produk yang memuat materi ini
        if (! $userProduk->hasAccess($userId, (int) $materi['produk_id'])) {
            return $this->response->setStatusCode(403)
                ->setBody('Anda tidak memiliki akses ke materi ini. Silakan beli produk terlebih dahulu.');
        }

        // 5. Serve file
        $physicalPath = WRITEPATH . $record['path'];
        if (! is_file($physicalPath)) {
            return $this->response->setStatusCode(404)->setBody('File fisik tidak ditemukan di server.');
        }

        $mimeMap = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        $ext      = strtolower($record['tipe']);
        $mimeType = $mimeMap[$ext] ?? 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . $record['nama'] . '"')
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($physicalPath));
    }
}
