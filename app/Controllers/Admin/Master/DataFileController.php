<?php

namespace App\Controllers\Admin\Master;

use App\Controllers\BaseController;
use App\Models\MasterDataFileModel;

class DataFileController extends BaseController
{
    protected MasterDataFileModel $fileModel;

    public function __construct()
    {
        $this->fileModel = new MasterDataFileModel();
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
     * Daftar semua file.
     */
    public function index()
    {
        $files = $this->fileModel->orderBy('id', 'DESC')->findAll();

        return view('admin/master/datafile/index', [
            'files' => $files,
            'menus' => $this->getMenus(),
        ]);
    }

    /**
     * Upload file baru.
     * Mendukung AJAX (return JSON) maupun form biasa (redirect).
     */
    public function upload()
    {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $maxSizeMB    = 5;

        $file = $this->request->getFile('file');

        if (! $file || ! $file->isValid()) {
            $error = $file ? $file->getErrorString() : 'Tidak ada file yang diunggah.';

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'message' => $error]);
            }
            return redirect()->back()->with('error', $error);
        }

        // Validasi ekstensi
        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, $allowedTypes, true)) {
            $msg = 'Tipe file tidak diizinkan. Diizinkan: ' . implode(', ', $allowedTypes);
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'message' => $msg]);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Validasi ukuran (bytes)
        if ($file->getSize() > $maxSizeMB * 1024 * 1024) {
            $msg = "Ukuran file melebihi batas maksimum {$maxSizeMB} MB.";
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'message' => $msg]);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Pindahkan file ke direktori upload
        $uploadDir  = WRITEPATH . 'uploads/datafile/';
        $newName    = $file->getRandomName();

        if (! $file->move($uploadDir, $newName)) {
            $msg = 'Gagal menyimpan file ke server.';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'message' => $msg]);
            }
            return redirect()->back()->with('error', $msg);
        }

        $relativePath = 'uploads/datafile/' . $newName;

        $id = $this->fileModel->insertFile([
            'nama'   => $file->getClientName(),
            'path'   => $relativePath,
            'tipe'   => $ext,
            'ukuran' => $file->getSize(),
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => true,
                'data'   => [
                    'id'   => $id,
                    'nama' => $file->getClientName(),
                    'path' => $relativePath,
                ],
            ]);
        }

        return redirect()->to(base_url('admin/master/datafile'))->with('success', 'File berhasil diunggah.');
    }

    /**
     * Hapus file (fisik + record DB).
     */
    public function delete(int $id)
    {
        $record = $this->fileModel->find($id);

        if (! $record) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        // Hapus file fisik
        $physicalPath = WRITEPATH . $record['path'];
        if (is_file($physicalPath)) {
            @unlink($physicalPath);
        }

        $this->fileModel->delete($id);

        return redirect()->to(base_url('admin/master/datafile'))->with('success', 'File berhasil dihapus.');
    }

    /**
     * Serve file dari WRITEPATH agar bisa diakses via URL.
     * Digunakan untuk preview dan sebagai URL yang disalin ke materi pelajaran.
     */
    public function serve(int $id)
    {
        $record = $this->fileModel->find($id);

        if (! $record) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        $physicalPath = WRITEPATH . $record['path'];

        if (! is_file($physicalPath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File fisik tidak ditemukan.');
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
