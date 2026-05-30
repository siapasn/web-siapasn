<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\MasterAplikasiModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * MasterAplikasiController
 *
 * Mengelola konfigurasi aplikasi untuk Super Admin:
 * - Informasi Umum (nama, deskripsi, logo)
 * - Midtrans (server key, client key, environment)
 * - Email SMTP (host, port, username, password, encryption, from, from_name)
 * - Kebijakan Sesi (session_timeout)
 */
class MasterAplikasiController extends BaseController
{
    protected MasterAplikasiModel $masterAplikasiModel;
    protected AuditLogModel       $auditLogModel;

    public function __construct()
    {
        $this->masterAplikasiModel = new MasterAplikasiModel();
        $this->auditLogModel       = new AuditLogModel();
    }

    /**
     * GET superadmin/master-aplikasi
     * Tampilkan form konfigurasi aplikasi dengan nilai saat ini.
     */
    public function index(): string
    {
        $configs = $this->masterAplikasiModel->getAll();

        return view('superadmin/master-aplikasi/index', [
            'configs' => $configs,
            'menus'   => $this->getMenus(),
        ]);
    }

    /**
     * POST superadmin/master-aplikasi/save
     * Validasi dan simpan semua konfigurasi.
     */
    public function save(): RedirectResponse
    {
        $rules = [
            // Informasi Umum
            'app_name' => [
                'label' => 'Nama Aplikasi',
                'rules' => 'required|min_length[3]',
            ],
            'app_description' => [
                'label' => 'Deskripsi Aplikasi',
                'rules' => 'permit_empty',
            ],

            // Midtrans — opsional, bisa dikosongkan jika belum dikonfigurasi
            'midtrans_server_key' => [
                'label' => 'Midtrans Server Key',
                'rules' => 'permit_empty',
            ],
            'midtrans_client_key' => [
                'label' => 'Midtrans Client Key',
                'rules' => 'permit_empty',
            ],
            'midtrans_environment' => [
                'label' => 'Midtrans Environment',
                'rules' => 'required|in_list[sandbox,production]',
            ],
            'midtrans_url' => [
                'label' => 'Midtrans URL',
                'rules' => 'permit_empty|valid_url_strict',
                'errors' => [
                    'valid_url_strict' => 'Format URL Midtrans tidak valid.',
                ],
            ],
            'midtrans_merchant_id' => [
                'label' => 'Midtrans Merchant ID',
                'rules' => 'permit_empty',
            ],

            // Email SMTP — opsional, bisa dikosongkan jika belum dikonfigurasi
            'email_host' => [
                'label' => 'Email Host',
                'rules' => 'permit_empty',
            ],
            'email_port' => [
                'label' => 'Email Port',
                'rules' => 'required|integer|greater_than[0]|less_than[65536]',
                'errors' => [
                    'greater_than' => 'Port harus antara 1 dan 65535.',
                    'less_than'    => 'Port harus antara 1 dan 65535.',
                ],
            ],
            'email_username' => [
                'label' => 'Email Username',
                'rules' => 'permit_empty',
            ],
            'email_password' => [
                'label' => 'Email Password',
                'rules' => 'permit_empty',
            ],
            'email_encryption' => [
                'label' => 'Email Enkripsi',
                'rules' => 'required|in_list[tls,ssl,none]',
            ],
            'email_from' => [
                'label' => 'Email Pengirim',
                'rules' => 'permit_empty|if_exist|valid_email',
            ],
            'email_from_name' => [
                'label' => 'Nama Pengirim',
                'rules' => 'permit_empty',
            ],

            // Kebijakan Sesi
            'session_timeout' => [
                'label' => 'Session Timeout',
                'rules' => 'required|integer|greater_than_equal_to[5]|less_than_equal_to[1440]',
                'errors' => [
                    'greater_than_equal_to' => 'Session timeout minimal 5 menit.',
                    'less_than_equal_to'    => 'Session timeout maksimal 1440 menit.',
                ],
            ],
            'produk_expired_days' => [
                'label' => 'Masa Aktif Produk',
                'rules' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[3650]',
                'errors' => [
                    'greater_than_equal_to' => 'Masa aktif produk minimal 1 hari.',
                    'less_than_equal_to'    => 'Masa aktif produk maksimal 3650 hari (10 tahun).',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Kumpulkan semua field konfigurasi
        $configData = [
            // Informasi Umum
            'app_name'             => $this->request->getPost('app_name'),
            'app_description'      => $this->request->getPost('app_description') ?? '',

            // Midtrans
            'midtrans_server_key'  => $this->request->getPost('midtrans_server_key'),
            'midtrans_client_key'  => $this->request->getPost('midtrans_client_key'),
            'midtrans_environment' => $this->request->getPost('midtrans_environment'),
            'midtrans_url'         => $this->request->getPost('midtrans_url'),
            'midtrans_merchant_id' => $this->request->getPost('midtrans_merchant_id'),

            // Email SMTP
            'email_host'           => $this->request->getPost('email_host'),
            'email_port'           => $this->request->getPost('email_port'),
            'email_username'       => $this->request->getPost('email_username'),
            'email_encryption'     => $this->request->getPost('email_encryption'),
            'email_from'           => $this->request->getPost('email_from'),
            'email_from_name'      => $this->request->getPost('email_from_name'),

            // Kebijakan Sesi
            'session_timeout'      => $this->request->getPost('session_timeout'),

            // Masa Aktif Produk
            'produk_expired_days'  => $this->request->getPost('produk_expired_days'),

            // Redis
            'redis_socket'         => $this->request->getPost('redis_socket') ?? '',
            'redis_host'           => $this->request->getPost('redis_host') ?? '127.0.0.1',
            'redis_port'           => $this->request->getPost('redis_port') ?? '6379',
            'redis_password'       => $this->request->getPost('redis_password') ?? '',
            'redis_db'             => $this->request->getPost('redis_db') ?? '0',
        ];

        // Password email hanya diperbarui jika diisi (tidak kosong)
        $emailPassword = $this->request->getPost('email_password');
        if ($emailPassword !== null && $emailPassword !== '') {
            $configData['email_password'] = $emailPassword;
        }

        // Handle upload logo aplikasi
        $logoFile = $this->request->getFile('app_logo');
        if ($logoFile !== null && $logoFile->isValid() && ! $logoFile->hasMoved()) {
            $newName = $logoFile->getRandomName();
            $logoFile->move(FCPATH . 'uploads/logo', $newName);
            $configData['app_logo'] = 'uploads/logo/' . $newName;
        }

        $this->masterAplikasiModel->setMultiple($configData);

        $this->auditLogModel->catat(
            (int) session()->get('user_id'),
            'Simpan Konfigurasi Aplikasi',
            'Memperbarui konfigurasi master aplikasi',
            $this->request->getIPAddress()
        );

        return redirect()->to(base_url('superadmin/master-aplikasi'))
            ->with('success', 'Konfigurasi aplikasi berhasil disimpan.');
    }

    /**
     * Ambil menu sidebar untuk user yang sedang login.
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
}
