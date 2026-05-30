<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProdukExpiredDaysConfig extends Migration
{
    public function up(): void
    {
        $exists = $this->db->table('master_aplikasi')
            ->where('config_key', 'produk_expired_days')
            ->countAllResults();

        if ($exists === 0) {
            $this->db->table('master_aplikasi')->insert([
                'config_key'   => 'produk_expired_days',
                'config_value' => '365',
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down(): void
    {
        $this->db->table('master_aplikasi')
            ->where('config_key', 'produk_expired_days')
            ->delete();
    }
}
