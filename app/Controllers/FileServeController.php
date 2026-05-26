<?php

namespace App\Controllers;

use App\Models\MasterDataFileModel;

/**
 * FileServeController
 *
 * Serve file dari WRITEPATH secara publik (tanpa auth).
 * Digunakan untuk thumbnail katalog buku dan aset publik lainnya.
 * URL: /file/{id}
 */
class FileServeController extends BaseController
{
    public function index(int $id)
    {
        $fileModel = new MasterDataFileModel();
        $record    = $fileModel->find($id);

        if (! $record) {
            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan.');
        }

        $physicalPath = WRITEPATH . $record['path'];

        if (! is_file($physicalPath)) {
            return $this->response->setStatusCode(404)->setBody('File fisik tidak ditemukan.');
        }

        $mimeMap = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
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
            ->setHeader('Cache-Control', 'public, max-age=86400')
            ->setBody(file_get_contents($physicalPath));
    }
}
