<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KategoriFormasiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama'      => 'Teknologi Informasi',
                'deskripsi' => 'Formasi terkait bidang IT, komputer, dan sistem informasi',
                'icon'      => 'bi-laptop',
                'urutan'    => 1,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Pemerintahan & Kebijakan',
                'deskripsi' => 'Formasi terkait administrasi pemerintahan dan kebijakan publik',
                'icon'      => 'bi-building',
                'urutan'    => 2,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Hukum',
                'deskripsi' => 'Formasi terkait bidang hukum, perundang-undangan, dan peradilan',
                'icon'      => 'bi-journal-bookmark',
                'urutan'    => 3,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Keuangan & Audit',
                'deskripsi' => 'Formasi terkait keuangan negara, akuntansi, dan audit',
                'icon'      => 'bi-cash-stack',
                'urutan'    => 4,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Kesehatan',
                'deskripsi' => 'Formasi terkait bidang kesehatan dan medis',
                'icon'      => 'bi-heart-pulse',
                'urutan'    => 5,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Pendidikan & Penelitian',
                'deskripsi' => 'Formasi terkait bidang pendidikan, pengajaran, dan penelitian',
                'icon'      => 'bi-mortarboard',
                'urutan'    => 6,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Pertanian',
                'deskripsi' => 'Formasi terkait bidang pertanian, perkebunan, dan peternakan',
                'icon'      => 'bi-tree',
                'urutan'    => 7,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Kelautan & Perikanan',
                'deskripsi' => 'Formasi terkait bidang kelautan, perikanan, dan maritim',
                'icon'      => 'bi-water',
                'urutan'    => 8,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Kehutanan & Lingkungan',
                'deskripsi' => 'Formasi terkait bidang kehutanan, lingkungan hidup, dan konservasi',
                'icon'      => 'bi-flower1',
                'urutan'    => 9,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Teknik & Infrastruktur',
                'deskripsi' => 'Formasi terkait bidang teknik sipil, arsitektur, dan infrastruktur',
                'icon'      => 'bi-gear',
                'urutan'    => 10,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Perdagangan & Industri',
                'deskripsi' => 'Formasi terkait bidang perdagangan, industri, dan UMKM',
                'icon'      => 'bi-shop',
                'urutan'    => 11,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Kebencanaan & Keamanan',
                'deskripsi' => 'Formasi terkait bidang penanggulangan bencana dan keamanan',
                'icon'      => 'bi-shield-check',
                'urutan'    => 12,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Komunikasi & Media',
                'deskripsi' => 'Formasi terkait bidang komunikasi, media, dan informasi publik',
                'icon'      => 'bi-megaphone',
                'urutan'    => 13,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Imigrasi & Pemasyarakatan',
                'deskripsi' => 'Formasi terkait bidang imigrasi, pemasyarakatan, dan keimigrasian',
                'icon'      => 'bi-passport',
                'urutan'    => 14,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Pengawasan & Pengujian',
                'deskripsi' => 'Formasi terkait bidang pengawasan, inspeksi, dan pengujian mutu',
                'icon'      => 'bi-clipboard-check',
                'urutan'    => 15,
                'is_active' => 1,
            ],
        ];

        $now = date('Y-m-d H:i:s');

        foreach ($data as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        $this->db->table('kategori_formasi')->insertBatch($data);
    }
}
