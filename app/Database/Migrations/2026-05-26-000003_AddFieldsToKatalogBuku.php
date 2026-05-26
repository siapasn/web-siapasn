<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToKatalogBuku extends Migration
{
    public function up(): void
    {
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('katalog_buku');

        $newFields = [];

        if (! in_array('kode', $fields)) {
            $newFields['kode'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'id',
            ];
        }

        if (! in_array('isbn', $fields)) {
            $newFields['isbn'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'judul',
            ];
        }

        if (! in_array('pengarang', $fields)) {
            $newFields['pengarang'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'isbn',
            ];
        }

        if (! in_array('penerbit', $fields)) {
            $newFields['penerbit'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'pengarang',
            ];
        }

        if (! in_array('harga', $fields)) {
            $newFields['harga'] = [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'penerbit',
            ];
        }

        if (! empty($newFields)) {
            $this->forge->addColumn('katalog_buku', $newFields);
        }
    }

    public function down(): void
    {
        $cols = ['kode', 'isbn', 'pengarang', 'penerbit', 'harga'];
        foreach ($cols as $col) {
            $this->forge->dropColumn('katalog_buku', $col);
        }
    }
}
