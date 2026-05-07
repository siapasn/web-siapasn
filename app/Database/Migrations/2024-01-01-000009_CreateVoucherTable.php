<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVoucherTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'jenis_diskon' => [
                'type'       => 'ENUM',
                'constraint' => ['persentase', 'nominal'],
                'null'       => false,
            ],
            'nilai_diskon' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
            ],
            'batas_penggunaan' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => null,
            ],
            'jumlah_digunakan' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'expired_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');

        $this->forge->createTable('voucher');
    }

    public function down(): void
    {
        $this->forge->dropTable('voucher', true);
    }
}
