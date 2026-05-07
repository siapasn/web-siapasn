<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembelian</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; }
        .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #198754, #146c43); padding: 32px 24px; text-align: center; color: #ffffff; }
        .header h2 { margin: 0; font-size: 22px; }
        .body { padding: 32px 24px; color: #333333; line-height: 1.6; }
        .success-badge { background-color: #d1e7dd; color: #0f5132; border-radius: 6px; padding: 10px 16px; text-align: center; font-weight: bold; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        table th, table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #e9ecef; font-size: 14px; }
        table th { background-color: #f8f9fa; color: #495057; font-weight: 600; }
        .total-row td { font-weight: bold; font-size: 15px; border-top: 2px solid #dee2e6; }
        .footer { padding: 16px 24px; background-color: #f8f9fa; text-align: center; font-size: 12px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>&#127891; SiapASN Simulation Center</h2>
        </div>
        <div class="body">
            <p>Halo, <strong><?= esc($nama) ?></strong>!</p>
            <div class="success-badge">&#10003; Pembayaran Berhasil!</div>
            <p>Terima kasih atas pembelian Anda. Berikut adalah detail transaksi Anda:</p>
            <table>
                <tr>
                    <th>Kode Transaksi</th>
                    <td><?= esc($transaksi['kode_transaksi'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Produk</th>
                    <td><?= esc($transaksi['nama_produk'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td><?= esc($transaksi['tanggal'] ?? date('d M Y H:i')) ?></td>
                </tr>
                <tr>
                    <th>Harga Asli</th>
                    <td>Rp <?= number_format($transaksi['harga_asli'] ?? 0, 0, ',', '.') ?></td>
                </tr>
                <?php if (!empty($transaksi['diskon']) && $transaksi['diskon'] > 0): ?>
                <tr>
                    <th>Diskon</th>
                    <td>- Rp <?= number_format($transaksi['diskon'], 0, ',', '.') ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td>Total Dibayar</td>
                    <td>Rp <?= number_format($transaksi['harga_bayar'] ?? 0, 0, ',', '.') ?></td>
                </tr>
            </table>
            <p>Akses ke paket tryout Anda sudah aktif. Silakan login dan mulai belajar!</p>
        </div>
        <div class="footer">
            &copy; <?= date('Y') ?> SiapASN Simulation Center. Hak cipta dilindungi.
        </div>
    </div>
</body>
</html>
