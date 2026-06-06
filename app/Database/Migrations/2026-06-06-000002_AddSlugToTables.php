<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSlugToTables extends Migration
{
    public function up(): void
    {
        // Tambah kolom slug ke tabel tryout_event (hanya jika belum ada)
        $fields = $this->db->getFieldNames('tryout_event');
        if (! in_array('slug', $fields)) {
            $this->forge->addColumn('tryout_event', [
                'slug' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'nama',
                    'unique'     => true,
                ],
            ]);
        }

        // Tambah kolom slug ke tabel tryout (hanya jika belum ada)
        $fields = $this->db->getFieldNames('tryout');
        if (! in_array('slug', $fields)) {
            $this->forge->addColumn('tryout', [
                'slug' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'nama',
                    'unique'     => true,
                ],
            ]);
        }

        // Tambah kolom slug ke tabel produk (hanya jika belum ada)
        $fields = $this->db->getFieldNames('produk');
        if (! in_array('slug', $fields)) {
            $this->forge->addColumn('produk', [
                'slug' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'nama',
                    'unique'     => true,
                ],
            ]);
        }

        // Isi slug untuk data yang sudah ada (hanya yang belum punya slug)
        $this->fillExistingSlugs();
    }

    public function down(): void
    {
        $this->forge->dropColumn('tryout_event', 'slug');
        $this->forge->dropColumn('tryout', 'slug');
        $this->forge->dropColumn('produk', 'slug');
    }

    /**
     * Generate dan isi slug untuk data yang sudah ada.
     */
    private function fillExistingSlugs(): void
    {
        // tryout_event
        $events = $this->db->table('tryout_event')->get()->getResultArray();
        foreach ($events as $e) {
            $slug = $this->makeUniqueSlug('tryout_event', $e['nama'], $e['id']);
            $this->db->table('tryout_event')->where('id', $e['id'])->update(['slug' => $slug]);
        }

        // tryout
        $tryouts = $this->db->table('tryout')->get()->getResultArray();
        foreach ($tryouts as $t) {
            $slug = $this->makeUniqueSlug('tryout', $t['nama'], $t['id']);
            $this->db->table('tryout')->where('id', $t['id'])->update(['slug' => $slug]);
        }

        // produk
        $produks = $this->db->table('produk')->get()->getResultArray();
        foreach ($produks as $p) {
            $slug = $this->makeUniqueSlug('produk', $p['nama'], $p['id']);
            $this->db->table('produk')->where('id', $p['id'])->update(['slug' => $slug]);
        }
    }

    /**
     * Buat slug unik dengan suffix angka jika sudah ada yang sama.
     */
    private function makeUniqueSlug(string $table, string $nama, int $excludeId): string
    {
        $base = url_title(strtolower($nama), '-', true);
        // Hapus karakter yang tidak aman di URL
        $base = preg_replace('/[^a-z0-9\-]/', '', $base);
        $base = trim($base, '-');
        if ($base === '') {
            $base = 'item';
        }

        $slug  = $base;
        $count = 1;

        while (true) {
            $exists = $this->db->table($table)
                ->where('slug', $slug)
                ->where('id !=', $excludeId)
                ->countAllResults();

            if ($exists === 0) {
                break;
            }

            $slug = $base . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
