<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; }
        .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #0d6efd, #0a58ca); padding: 32px 24px; text-align: center; color: #ffffff; }
        .header h2 { margin: 0; font-size: 22px; }
        .body { padding: 32px 24px; color: #333333; line-height: 1.6; }
        .btn { display: inline-block; padding: 12px 28px; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
        .footer { padding: 16px 24px; background-color: #f8f9fa; text-align: center; font-size: 12px; color: #6c757d; }
        .url-fallback { word-break: break-all; color: #0d6efd; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>&#127891; SiapASN Simulation Center</h2>
        </div>
        <div class="body">
            <p>Halo, <strong><?= esc($nama) ?></strong>!</p>
            <p>Terima kasih telah mendaftar di <strong>SiapASN Simulation Center</strong>. Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda.</p>
            <div style="text-align: center;">
                <a href="<?= esc($verifyUrl) ?>" class="btn">Verifikasi Email Saya</a>
            </div>
            <p>Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke browser Anda:</p>
            <p class="url-fallback"><?= esc($verifyUrl) ?></p>
            <p>Tautan ini tidak memiliki batas waktu kedaluwarsa. Jika Anda tidak mendaftar, abaikan email ini.</p>
        </div>
        <div class="footer">
            &copy; <?= date('Y') ?> SiapASN Simulation Center. Hak cipta dilindungi.
        </div>
    </div>
</body>
</html>
