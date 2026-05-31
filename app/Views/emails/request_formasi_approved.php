<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tryout Formasi Tersedia</title>
    <style>
        body { margin:0; padding:0; background:#f4f7fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .wrapper { max-width:600px; margin:0 auto; padding:20px; }
        .card { background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.06); }
        .header { background:linear-gradient(135deg, #1a3a5c, #2d6a9f); padding:24px 30px; text-align:center; }
        .header h1 { color:#fff; font-size:18px; margin:0; font-weight:600; }
        .content { padding:30px; line-height:1.7; color:#333; font-size:15px; }
        .highlight { background:#e8f5e9; border-left:4px solid #4caf50; padding:12px 16px; border-radius:4px; margin:16px 0; }
        .btn-cta { display:inline-block; background:#1a3a5c; color:#fff; padding:12px 24px; border-radius:6px; text-decoration:none; font-weight:600; margin-top:16px; }
        .footer { background:#f8fafc; padding:16px 30px; text-align:center; font-size:12px; color:#94a3b8; border-top:1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>🎉 Tryout Formasi Sudah Tersedia!</h1>
            </div>
            <div class="content">
                <p>Halo, <strong><?= esc($nama) ?></strong>!</p>

                <p>Kabar baik! Tryout untuk formasi yang Anda request sudah tersedia:</p>

                <div class="highlight">
                    <strong>📋 Formasi:</strong> <?= esc($formasi_nama) ?>
                </div>

                <?php if (! empty($admin_note)): ?>
                <p><strong>Catatan dari Admin:</strong><br><?= esc($admin_note) ?></p>
                <?php endif; ?>

                <p>Anda sekarang bisa langsung membeli paket tryout untuk formasi ini dan mulai berlatih.</p>

                <a href="<?= base_url('user/produk') ?>" class="btn-cta">Lihat Paket Tryout</a>

                <p style="margin-top:24px;color:#666;font-size:13px">Terima kasih telah menggunakan platform kami. Semoga sukses dalam persiapan CPNS!</p>
            </div>
            <div class="footer">
                &copy; <?= date('Y') ?> <?= esc(env('app.name', 'SiapASN Simulation Center')) ?>. Semua hak dilindungi.
            </div>
        </div>
    </div>
</body>
</html>
