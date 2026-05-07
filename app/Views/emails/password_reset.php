<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; }
        .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #0d6efd, #0a58ca); padding: 32px 24px; text-align: center; color: #ffffff; }
        .header h2 { margin: 0; font-size: 22px; }
        .body { padding: 32px 24px; color: #333333; line-height: 1.6; }
        .btn { display: inline-block; padding: 12px 28px; background-color: #dc3545; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
        .warning { background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 12px 16px; margin: 16px 0; font-size: 14px; color: #856404; }
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
            <p>Kami menerima permintaan untuk mereset password akun Anda. Klik tombol di bawah ini untuk membuat password baru.</p>
            <div style="text-align: center;">
                <a href="<?= esc($resetUrl) ?>" class="btn">Reset Password Saya</a>
            </div>
            <div class="warning">
                &#9888; Tautan ini hanya berlaku selama <strong>60 menit</strong>. Setelah itu, Anda perlu meminta tautan reset baru.
            </div>
            <p>Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke browser Anda:</p>
            <p class="url-fallback"><?= esc($resetUrl) ?></p>
            <p>Jika Anda tidak meminta reset password, abaikan email ini. Password Anda tidak akan berubah.</p>
        </div>
        <div class="footer">
            &copy; <?= date('Y') ?> SiapASN Simulation Center. Hak cipta dilindungi.
        </div>
    </div>
</body>
</html>
