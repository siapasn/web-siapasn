<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWebContentTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Unique key: syarat-ketentuan, kebijakan-privasi, hubungi-kami, hero_title, dll',
            ],
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
            ],
            'konten' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['halaman', 'teks', 'angka'],
                'default'    => 'halaman',
                'comment'    => 'halaman=rich HTML, teks=plain text, angka=numeric',
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('web_content');

        // Seed data awal
        $now = date('Y-m-d H:i:s');
        $this->db->table('web_content')->insertBatch([
            [
                'slug'       => 'syarat-ketentuan',
                'judul'      => 'Syarat dan Ketentuan',
                'konten'     => '<h5>1. Penerimaan Syarat</h5><p>Dengan mengakses dan menggunakan layanan SiapASN Simulation Center, Anda menyetujui untuk terikat oleh syarat dan ketentuan ini.</p><h5>2. Layanan</h5><p>SiapASN menyediakan platform simulasi tryout CPNS berbasis web untuk membantu persiapan ujian seleksi ASN.</p><h5>3. Akun Pengguna</h5><p>Anda bertanggung jawab untuk menjaga kerahasiaan akun dan password Anda. Segala aktivitas yang terjadi di bawah akun Anda menjadi tanggung jawab Anda.</p><h5>4. Pembayaran</h5><p>Semua transaksi pembelian paket tryout bersifat final dan tidak dapat dikembalikan kecuali terdapat kesalahan teknis dari pihak kami.</p><h5>5. Hak Kekayaan Intelektual</h5><p>Seluruh konten, soal, dan materi yang tersedia di platform ini dilindungi hak cipta dan merupakan milik SiapASN Simulation Center.</p><h5>6. Perubahan Layanan</h5><p>Kami berhak mengubah, menangguhkan, atau menghentikan layanan kapan saja tanpa pemberitahuan sebelumnya.</p>',
                'tipe'       => 'halaman',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'kebijakan-privasi',
                'judul'      => 'Kebijakan Privasi',
                'konten'     => '<h5>1. Informasi yang Kami Kumpulkan</h5><p>Kami mengumpulkan informasi yang Anda berikan saat mendaftar, termasuk nama, alamat email, dan nomor telepon.</p><h5>2. Penggunaan Informasi</h5><p>Informasi Anda digunakan untuk menyediakan layanan, memproses transaksi, dan mengirimkan notifikasi terkait akun Anda.</p><h5>3. Keamanan Data</h5><p>Kami menerapkan langkah-langkah keamanan teknis dan organisasi yang wajar untuk melindungi data pribadi Anda dari akses tidak sah.</p><h5>4. Berbagi Data</h5><p>Kami tidak menjual, memperdagangkan, atau mentransfer informasi pribadi Anda kepada pihak ketiga tanpa persetujuan Anda, kecuali diwajibkan oleh hukum.</p><h5>5. Cookie</h5><p>Platform kami menggunakan cookie untuk meningkatkan pengalaman pengguna. Anda dapat mengatur browser untuk menolak cookie, namun beberapa fitur mungkin tidak berfungsi optimal.</p><h5>6. Perubahan Kebijakan</h5><p>Kami dapat memperbarui kebijakan privasi ini sewaktu-waktu. Perubahan akan diberitahukan melalui email atau notifikasi di platform.</p>',
                'tipe'       => 'halaman',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'hubungi-kami',
                'judul'      => 'Hubungi Kami',
                'konten'     => '<p>Kami siap membantu Anda. Silakan hubungi kami melalui salah satu saluran berikut:</p>',
                'tipe'       => 'halaman',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'kontak_email',
                'judul'      => 'Email Kontak',
                'konten'     => 'info@siapasn.id',
                'tipe'       => 'teks',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'kontak_whatsapp',
                'judul'      => 'WhatsApp',
                'konten'     => '6281234567890',
                'tipe'       => 'teks',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'kontak_alamat',
                'judul'      => 'Alamat Kantor',
                'konten'     => 'Jakarta, Indonesia',
                'tipe'       => 'teks',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'hero_tagline',
                'judul'      => 'Hero Tagline',
                'konten'     => 'Raih Impian ASN-mu Bersama Kami',
                'tipe'       => 'teks',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'hero_deskripsi',
                'judul'      => 'Hero Deskripsi',
                'konten'     => 'Platform simulasi tryout CPNS terlengkap dengan ribuan soal, pembahasan mendalam, dan analisis nilai real-time untuk mempersiapkan Anda menghadapi seleksi ASN.',
                'tipe'       => 'teks',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'stat_pengguna',
                'judul'      => 'Statistik Pengguna',
                'konten'     => '10.000+',
                'tipe'       => 'teks',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'stat_soal',
                'judul'      => 'Statistik Soal',
                'konten'     => '5.000+',
                'tipe'       => 'teks',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'stat_paket',
                'judul'      => 'Statistik Paket',
                'konten'     => '50+',
                'tipe'       => 'teks',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('web_content', true);
    }
}
