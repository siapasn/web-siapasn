<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FormasiLengkapSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // Mapping kategori_formasi_id berdasarkan urutan di tabel
        // 1=Teknologi Informasi, 2=Pemerintahan & Kebijakan, 3=Hukum,
        // 4=Keuangan & Audit, 5=Kesehatan, 6=Pendidikan & Penelitian,
        // 7=Pertanian, 8=Kelautan & Perikanan, 9=Kehutanan & Lingkungan,
        // 10=Teknik & Infrastruktur, 11=Perdagangan & Industri,
        // 12=Kebencanaan & Keamanan, 13=Komunikasi & Media,
        // 14=Imigrasi & Pemasyarakatan, 15=Pengawasan & Pengujian

        $data = [
            // ═══════════════════════════════════════════════════════════════
            // 1. TEKNOLOGI INFORMASI
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 1, 'nama' => 'Pranata Komputer Ahli Pertama'],
            ['kategori_formasi_id' => 1, 'nama' => 'Pranata Komputer Ahli Muda'],
            ['kategori_formasi_id' => 1, 'nama' => 'Pranata Komputer Terampil'],
            ['kategori_formasi_id' => 1, 'nama' => 'Sandiman Ahli Pertama'],
            ['kategori_formasi_id' => 1, 'nama' => 'Sandiman Ahli Muda'],
            ['kategori_formasi_id' => 1, 'nama' => 'Sandiman Terampil'],
            ['kategori_formasi_id' => 1, 'nama' => 'Statistisi Ahli Pertama'],
            ['kategori_formasi_id' => 1, 'nama' => 'Statistisi Ahli Muda'],
            ['kategori_formasi_id' => 1, 'nama' => 'Manggala Informatika Ahli Pertama'],
            ['kategori_formasi_id' => 1, 'nama' => 'Manggala Informatika Ahli Muda'],
            ['kategori_formasi_id' => 1, 'nama' => 'Administrator Database Kependudukan Ahli Pertama'],
            ['kategori_formasi_id' => 1, 'nama' => 'Analis Data Ilmiah Ahli Pertama'],
            ['kategori_formasi_id' => 1, 'nama' => 'Analis Sistem Informasi Ahli Pertama'],
            ['kategori_formasi_id' => 1, 'nama' => 'Pengelola Teknologi Informasi Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 2. PEMERINTAHAN & KEBIJAKAN
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 2, 'nama' => 'Analis Kebijakan Ahli Pertama'],
            ['kategori_formasi_id' => 2, 'nama' => 'Analis Kebijakan Ahli Muda'],
            ['kategori_formasi_id' => 2, 'nama' => 'Perencana Ahli Pertama'],
            ['kategori_formasi_id' => 2, 'nama' => 'Perencana Ahli Muda'],
            ['kategori_formasi_id' => 2, 'nama' => 'Analis Pemerintahan Ahli Pertama'],
            ['kategori_formasi_id' => 2, 'nama' => 'Analis Pemerintahan Ahli Muda'],
            ['kategori_formasi_id' => 2, 'nama' => 'Diplomat Ahli Pertama'],
            ['kategori_formasi_id' => 2, 'nama' => 'Diplomat Ahli Muda'],
            ['kategori_formasi_id' => 2, 'nama' => 'Analis Kepegawaian Ahli Pertama'],
            ['kategori_formasi_id' => 2, 'nama' => 'Analis Kepegawaian Ahli Muda'],
            ['kategori_formasi_id' => 2, 'nama' => 'Analis Pertahanan Negara Ahli Pertama'],
            ['kategori_formasi_id' => 2, 'nama' => 'Penata Kelola Pemerintahan Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 3. HUKUM
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 3, 'nama' => 'Analis Hukum Ahli Pertama'],
            ['kategori_formasi_id' => 3, 'nama' => 'Analis Hukum Ahli Muda'],
            ['kategori_formasi_id' => 3, 'nama' => 'Perancang Peraturan Perundang-undangan Ahli Pertama'],
            ['kategori_formasi_id' => 3, 'nama' => 'Perancang Peraturan Perundang-undangan Ahli Muda'],
            ['kategori_formasi_id' => 3, 'nama' => 'Jaksa Ahli Pertama'],
            ['kategori_formasi_id' => 3, 'nama' => 'Penyidik PNS Ahli Pertama'],
            ['kategori_formasi_id' => 3, 'nama' => 'Mediator Hubungan Industrial Ahli Pertama'],
            ['kategori_formasi_id' => 3, 'nama' => 'Penilai Hak Asasi Manusia Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 4. KEUANGAN & AUDIT
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 4, 'nama' => 'Auditor Ahli Pertama'],
            ['kategori_formasi_id' => 4, 'nama' => 'Auditor Ahli Muda'],
            ['kategori_formasi_id' => 4, 'nama' => 'Analis Keuangan Pusat dan Daerah Ahli Pertama'],
            ['kategori_formasi_id' => 4, 'nama' => 'Analis Keuangan Pusat dan Daerah Ahli Muda'],
            ['kategori_formasi_id' => 4, 'nama' => 'Penilai Pemerintah Ahli Pertama'],
            ['kategori_formasi_id' => 4, 'nama' => 'Penilai Pemerintah Ahli Muda'],
            ['kategori_formasi_id' => 4, 'nama' => 'Pemeriksa Pajak Ahli Pertama'],
            ['kategori_formasi_id' => 4, 'nama' => 'Pemeriksa Pajak Ahli Muda'],
            ['kategori_formasi_id' => 4, 'nama' => 'Analis Anggaran Ahli Pertama'],
            ['kategori_formasi_id' => 4, 'nama' => 'Analis Anggaran Ahli Muda'],
            ['kategori_formasi_id' => 4, 'nama' => 'Pemeriksa Bea dan Cukai Ahli Pertama'],
            ['kategori_formasi_id' => 4, 'nama' => 'Pengelola Perbendaharaan Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 5. KESEHATAN
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 5, 'nama' => 'Dokter Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Dokter Gigi Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Apoteker Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Apoteker Ahli Muda'],
            ['kategori_formasi_id' => 5, 'nama' => 'Perawat Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Perawat Ahli Muda'],
            ['kategori_formasi_id' => 5, 'nama' => 'Perawat Terampil'],
            ['kategori_formasi_id' => 5, 'nama' => 'Bidan Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Bidan Terampil'],
            ['kategori_formasi_id' => 5, 'nama' => 'Nutrisionis Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Sanitarian Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Epidemiolog Kesehatan Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Pranata Laboratorium Kesehatan Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Fisioterapis Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Radiografer Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Teknisi Gigi Terampil'],
            ['kategori_formasi_id' => 5, 'nama' => 'Refraksionis Optisien Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Terapis Wicara Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Okupasi Terapis Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Administrator Kesehatan Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Penyuluh Kesehatan Masyarakat Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Asisten Apoteker Terampil'],
            ['kategori_formasi_id' => 5, 'nama' => 'Rekam Medis Ahli Pertama'],
            ['kategori_formasi_id' => 5, 'nama' => 'Psikolog Klinis Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 6. PENDIDIKAN & PENELITIAN
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 6, 'nama' => 'Guru Ahli Pertama'],
            ['kategori_formasi_id' => 6, 'nama' => 'Guru Ahli Muda'],
            ['kategori_formasi_id' => 6, 'nama' => 'Dosen Ahli Pertama'],
            ['kategori_formasi_id' => 6, 'nama' => 'Dosen Ahli Muda'],
            ['kategori_formasi_id' => 6, 'nama' => 'Peneliti Ahli Pertama'],
            ['kategori_formasi_id' => 6, 'nama' => 'Peneliti Ahli Muda'],
            ['kategori_formasi_id' => 6, 'nama' => 'Perekayasa Ahli Pertama'],
            ['kategori_formasi_id' => 6, 'nama' => 'Perekayasa Ahli Muda'],
            ['kategori_formasi_id' => 6, 'nama' => 'Widyaiswara Ahli Pertama'],
            ['kategori_formasi_id' => 6, 'nama' => 'Widyaiswara Ahli Muda'],
            ['kategori_formasi_id' => 6, 'nama' => 'Pustakawan Ahli Pertama'],
            ['kategori_formasi_id' => 6, 'nama' => 'Pustakawan Ahli Muda'],
            ['kategori_formasi_id' => 6, 'nama' => 'Arsiparis Ahli Pertama'],
            ['kategori_formasi_id' => 6, 'nama' => 'Arsiparis Ahli Muda'],
            ['kategori_formasi_id' => 6, 'nama' => 'Pamong Budaya Ahli Pertama'],
            ['kategori_formasi_id' => 6, 'nama' => 'Pamong Belajar Ahli Pertama'],
            ['kategori_formasi_id' => 6, 'nama' => 'Pengembang Teknologi Pembelajaran Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 7. PERTANIAN
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 7, 'nama' => 'Penyuluh Pertanian Ahli Pertama'],
            ['kategori_formasi_id' => 7, 'nama' => 'Penyuluh Pertanian Ahli Muda'],
            ['kategori_formasi_id' => 7, 'nama' => 'Pengendali Organisme Pengganggu Tumbuhan Ahli Pertama'],
            ['kategori_formasi_id' => 7, 'nama' => 'Medik Veteriner Ahli Pertama'],
            ['kategori_formasi_id' => 7, 'nama' => 'Medik Veteriner Ahli Muda'],
            ['kategori_formasi_id' => 7, 'nama' => 'Paramedik Veteriner Terampil'],
            ['kategori_formasi_id' => 7, 'nama' => 'Pengawas Mutu Hasil Pertanian Ahli Pertama'],
            ['kategori_formasi_id' => 7, 'nama' => 'Analis Ketahanan Pangan Ahli Pertama'],
            ['kategori_formasi_id' => 7, 'nama' => 'Pengawas Benih Tanaman Ahli Pertama'],
            ['kategori_formasi_id' => 7, 'nama' => 'Pemeriksa Karantina Pertanian Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 8. KELAUTAN & PERIKANAN
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 8, 'nama' => 'Penyuluh Perikanan Ahli Pertama'],
            ['kategori_formasi_id' => 8, 'nama' => 'Penyuluh Perikanan Ahli Muda'],
            ['kategori_formasi_id' => 8, 'nama' => 'Pengawas Perikanan Ahli Pertama'],
            ['kategori_formasi_id' => 8, 'nama' => 'Pengawas Perikanan Ahli Muda'],
            ['kategori_formasi_id' => 8, 'nama' => 'Analis Kelautan Ahli Pertama'],
            ['kategori_formasi_id' => 8, 'nama' => 'Pengelola Ekosistem Laut dan Pesisir Ahli Pertama'],
            ['kategori_formasi_id' => 8, 'nama' => 'Analis Akuakultur Ahli Pertama'],
            ['kategori_formasi_id' => 8, 'nama' => 'Pemeriksa Karantina Ikan Ahli Pertama'],
            ['kategori_formasi_id' => 8, 'nama' => 'Pengelola Pelabuhan Perikanan Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 9. KEHUTANAN & LINGKUNGAN
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 9, 'nama' => 'Polisi Kehutanan Ahli Pertama'],
            ['kategori_formasi_id' => 9, 'nama' => 'Polisi Kehutanan Terampil'],
            ['kategori_formasi_id' => 9, 'nama' => 'Penyuluh Kehutanan Ahli Pertama'],
            ['kategori_formasi_id' => 9, 'nama' => 'Penyuluh Kehutanan Ahli Muda'],
            ['kategori_formasi_id' => 9, 'nama' => 'Pengendali Ekosistem Hutan Ahli Pertama'],
            ['kategori_formasi_id' => 9, 'nama' => 'Pengendali Ekosistem Hutan Ahli Muda'],
            ['kategori_formasi_id' => 9, 'nama' => 'Pengawas Lingkungan Hidup Ahli Pertama'],
            ['kategori_formasi_id' => 9, 'nama' => 'Pengawas Lingkungan Hidup Ahli Muda'],
            ['kategori_formasi_id' => 9, 'nama' => 'Analis Pengelolaan Sumber Daya Alam Ahli Pertama'],
            ['kategori_formasi_id' => 9, 'nama' => 'Pengelola Keanekaragaman Hayati Ahli Pertama'],
            ['kategori_formasi_id' => 9, 'nama' => 'Pengendali Dampak Lingkungan Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 10. TEKNIK & INFRASTRUKTUR
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 10, 'nama' => 'Teknik Jalan dan Jembatan Ahli Pertama'],
            ['kategori_formasi_id' => 10, 'nama' => 'Teknik Jalan dan Jembatan Ahli Muda'],
            ['kategori_formasi_id' => 10, 'nama' => 'Teknik Tata Bangunan dan Perumahan Ahli Pertama'],
            ['kategori_formasi_id' => 10, 'nama' => 'Teknik Tata Bangunan dan Perumahan Ahli Muda'],
            ['kategori_formasi_id' => 10, 'nama' => 'Teknik Pengairan Ahli Pertama'],
            ['kategori_formasi_id' => 10, 'nama' => 'Teknik Pengairan Ahli Muda'],
            ['kategori_formasi_id' => 10, 'nama' => 'Surveyor Pemetaan Ahli Pertama'],
            ['kategori_formasi_id' => 10, 'nama' => 'Surveyor Pemetaan Ahli Muda'],
            ['kategori_formasi_id' => 10, 'nama' => 'Penata Ruang Ahli Pertama'],
            ['kategori_formasi_id' => 10, 'nama' => 'Penata Ruang Ahli Muda'],
            ['kategori_formasi_id' => 10, 'nama' => 'Analis Transportasi Ahli Pertama'],
            ['kategori_formasi_id' => 10, 'nama' => 'Penguji Keselamatan Pelayaran Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 11. PERDAGANGAN & INDUSTRI
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 11, 'nama' => 'Penera Ahli Pertama'],
            ['kategori_formasi_id' => 11, 'nama' => 'Penera Ahli Muda'],
            ['kategori_formasi_id' => 11, 'nama' => 'Analis Perdagangan Ahli Pertama'],
            ['kategori_formasi_id' => 11, 'nama' => 'Analis Perdagangan Ahli Muda'],
            ['kategori_formasi_id' => 11, 'nama' => 'Analis Industri Ahli Pertama'],
            ['kategori_formasi_id' => 11, 'nama' => 'Pengawas Koperasi Ahli Pertama'],
            ['kategori_formasi_id' => 11, 'nama' => 'Pengelola Kekayaan Intelektual Ahli Pertama'],
            ['kategori_formasi_id' => 11, 'nama' => 'Adyatama Kepariwisataan dan Ekonomi Kreatif Ahli Pertama'],
            ['kategori_formasi_id' => 11, 'nama' => 'Analis Investigasi Dan Pengamanan Perdagangan'],

            // ═══════════════════════════════════════════════════════════════
            // 12. KEBENCANAAN & KEAMANAN
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 12, 'nama' => 'Analis Kebencanaan Ahli Pertama'],
            ['kategori_formasi_id' => 12, 'nama' => 'Analis Kebencanaan Ahli Muda'],
            ['kategori_formasi_id' => 12, 'nama' => 'Rescuer Ahli Pertama'],
            ['kategori_formasi_id' => 12, 'nama' => 'Rescuer Terampil'],
            ['kategori_formasi_id' => 12, 'nama' => 'Analis Kebakaran Ahli Pertama'],
            ['kategori_formasi_id' => 12, 'nama' => 'Pemadam Kebakaran Terampil'],
            ['kategori_formasi_id' => 12, 'nama' => 'Analis Intelijen Ahli Pertama'],
            ['kategori_formasi_id' => 12, 'nama' => 'Analis Intelijen Ahli Muda'],
            ['kategori_formasi_id' => 12, 'nama' => 'Pengelola Penanggulangan Bencana Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 13. KOMUNIKASI & MEDIA
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 13, 'nama' => 'Pranata Humas Ahli Pertama'],
            ['kategori_formasi_id' => 13, 'nama' => 'Pranata Humas Ahli Muda'],
            ['kategori_formasi_id' => 13, 'nama' => 'Penerjemah Ahli Pertama'],
            ['kategori_formasi_id' => 13, 'nama' => 'Penerjemah Ahli Muda'],
            ['kategori_formasi_id' => 13, 'nama' => 'Analis Komunikasi Publik Ahli Pertama'],
            ['kategori_formasi_id' => 13, 'nama' => 'Pengelola Informasi dan Dokumentasi Ahli Pertama'],
            ['kategori_formasi_id' => 13, 'nama' => 'Penyiar Ahli Pertama'],
            ['kategori_formasi_id' => 13, 'nama' => 'Fotografer Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 14. IMIGRASI & PEMASYARAKATAN
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 14, 'nama' => 'Pemeriksa Keimigrasian Ahli Pertama'],
            ['kategori_formasi_id' => 14, 'nama' => 'Pemeriksa Keimigrasian Ahli Muda'],
            ['kategori_formasi_id' => 14, 'nama' => 'Analis Keimigrasian Ahli Pertama'],
            ['kategori_formasi_id' => 14, 'nama' => 'Analis Keimigrasian Ahli Muda'],
            ['kategori_formasi_id' => 14, 'nama' => 'Pembimbing Kemasyarakatan Ahli Pertama'],
            ['kategori_formasi_id' => 14, 'nama' => 'Pembimbing Kemasyarakatan Ahli Muda'],
            ['kategori_formasi_id' => 14, 'nama' => 'Penjaga Tahanan Terampil'],
            ['kategori_formasi_id' => 14, 'nama' => 'Assessor SDM Aparatur Ahli Pertama'],
            ['kategori_formasi_id' => 14, 'nama' => 'Assessor SDM Aparatur Ahli Muda'],
            ['kategori_formasi_id' => 14, 'nama' => 'Analis Pemasyarakatan Ahli Pertama'],

            // ═══════════════════════════════════════════════════════════════
            // 15. PENGAWASAN & PENGUJIAN
            // ═══════════════════════════════════════════════════════════════
            ['kategori_formasi_id' => 15, 'nama' => 'Pengawas Farmasi dan Makanan Ahli Pertama'],
            ['kategori_formasi_id' => 15, 'nama' => 'Pengawas Farmasi dan Makanan Ahli Muda'],
            ['kategori_formasi_id' => 15, 'nama' => 'Penguji Kendaraan Bermotor Ahli Pertama'],
            ['kategori_formasi_id' => 15, 'nama' => 'Penguji Kendaraan Bermotor Terampil'],
            ['kategori_formasi_id' => 15, 'nama' => 'Pengawas Ketenagakerjaan Ahli Pertama'],
            ['kategori_formasi_id' => 15, 'nama' => 'Pengawas Ketenagakerjaan Ahli Muda'],
            ['kategori_formasi_id' => 15, 'nama' => 'Pengawas Radiasi Ahli Pertama'],
            ['kategori_formasi_id' => 15, 'nama' => 'Penguji Mutu Barang Ahli Pertama'],
            ['kategori_formasi_id' => 15, 'nama' => 'Inspektur Tambang Ahli Pertama'],
            ['kategori_formasi_id' => 15, 'nama' => 'Inspektur Tambang Ahli Muda'],
            ['kategori_formasi_id' => 15, 'nama' => 'Pengawas Penyelenggaraan Urusan Pemerintahan Daerah Ahli Pertama'],
            ['kategori_formasi_id' => 15, 'nama' => 'Penguji Keselamatan dan Kesehatan Kerja Ahli Pertama'],
        ];

        // Hapus data lama (opsional — uncomment jika ingin replace)
        // $this->db->table('formasi')->truncate();

        $batch = [];
        foreach ($data as $row) {
            $batch[] = [
                'kategori_formasi_id' => $row['kategori_formasi_id'],
                'nama'                => $row['nama'],
                'deskripsi'           => null,
                'is_active'           => 1,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];
        }

        // Insert batch — skip yang sudah ada (berdasarkan nama)
        $existingNames = array_column(
            $this->db->table('formasi')->select('nama')->get()->getResultArray(),
            'nama'
        );

        $toInsert = array_filter($batch, fn($row) => ! in_array($row['nama'], $existingNames));

        if (! empty($toInsert)) {
            $this->db->table('formasi')->insertBatch(array_values($toInsert));
        }

        echo "Inserted " . count($toInsert) . " formasi baru (skipped " . (count($batch) - count($toInsert)) . " yang sudah ada).\n";
    }
}
