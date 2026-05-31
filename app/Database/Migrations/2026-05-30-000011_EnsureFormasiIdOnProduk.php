<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureFormasiIdOnProduk extends Migration
{
    public function up(): void
    {
        $fields = $this->db->getFieldNames('produk');

        if (! in_array('formasi_id', $fields)) {
            $this->forge->addColumn('produk', [
                'formasi_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'kategori_id',
                ],
            ]);

            $this->db->query('ALTER TABLE produk ADD CONSTRAINT fk_produk_formasi FOREIGN KEY (formasi_id) REFERENCES formasi(id) ON DELETE SET NULL ON UPDATE CASCADE');
        }
    }

    public function down(): void
    {
        $fields = $this->db->getFieldNames('produk');

        if (in_array('formasi_id', $fields)) {
            try {
                $this->db->query('ALTER TABLE produk DROP FOREIGN KEY fk_produk_formasi');
            } catch (\Throwable $e) {
                // FK mungkin tidak ada
            }
            $this->forge->dropColumn('produk', 'formasi_id');
        }
    }
}
