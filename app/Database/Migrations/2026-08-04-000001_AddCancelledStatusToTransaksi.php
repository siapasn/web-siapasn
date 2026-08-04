<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCancelledStatusToTransaksi extends Migration
{
    public function up(): void
    {
        $db = \Config\Database::connect();
        $db->query("ALTER TABLE `transaksi` MODIFY COLUMN `status` ENUM('pending','success','failed','expired','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        $db = \Config\Database::connect();
        $db->query("ALTER TABLE `transaksi` MODIFY COLUMN `status` ENUM('pending','success','failed','expired') NOT NULL DEFAULT 'pending'");
    }
}
