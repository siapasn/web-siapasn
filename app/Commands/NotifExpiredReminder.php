<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\NotifikasiModel;

/**
 * Kirim notifikasi reminder ke user yang produknya akan expired dalam 7 hari.
 * Jalankan via cron harian: php spark app:notif-expired-reminder
 */
class NotifExpiredReminder extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:notif-expired-reminder';
    protected $description = 'Kirim notifikasi reminder produk yang akan expired dalam 7 hari.';

    public function run(array $params)
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // Cari user_produk yang expired dalam 7 hari ke depan
        // dan belum pernah dikirimi reminder (cek notifikasi dengan tipe 'expired_reminder' untuk user+produk)
        $sevenDaysLater = date('Y-m-d H:i:s', strtotime('+7 days'));

        $expiring = $db->table('user_produk up')
            ->select('up.user_id, up.produk_id, up.expired_at, p.nama AS produk_nama')
            ->join('produk p', 'p.id = up.produk_id')
            ->where('up.expired_at IS NOT NULL', null, false)
            ->where('up.expired_at >', $now)
            ->where('up.expired_at <=', $sevenDaysLater)
            ->get()->getResultArray();

        $sent = 0;

        foreach ($expiring as $row) {
            // Cek apakah sudah pernah kirim reminder untuk produk ini
            $alreadySent = $db->table('notifikasi')
                ->where('user_id', $row['user_id'])
                ->where('tipe', 'expired_reminder')
                ->like('pesan', $row['produk_nama'])
                ->where('created_at >', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->countAllResults();

            if ($alreadySent > 0) continue;

            $sisaHari = (int) ceil((strtotime($row['expired_at']) - time()) / 86400);

            NotifikasiModel::kirim(
                (int) $row['user_id'],
                'expired_reminder',
                'Akses Akan Berakhir',
                'Akses ' . $row['produk_nama'] . ' akan berakhir dalam ' . $sisaHari . ' hari. Perpanjang sekarang!',
                'user/tryout'
            );

            $sent++;
        }

        CLI::write("Sent {$sent} expired reminder notifications.", 'green');
    }
}
