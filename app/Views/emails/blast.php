<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($subject) ?></title>
    <style>
        body { margin:0; padding:0; background:#f4f7fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .wrapper { max-width:600px; margin:0 auto; padding:20px; }
        .card { background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.06); }
        .header { background:linear-gradient(135deg, #1a3a5c, #2d6a9f); padding:24px 30px; text-align:center; }
        .header h1 { color:#fff; font-size:18px; margin:0; font-weight:600; }
        .content { padding:30px; line-height:1.7; color:#333; font-size:15px; }
        .content img { max-width:100%; height:auto; }
        .footer { background:#f8fafc; padding:16px 30px; text-align:center; font-size:12px; color:#94a3b8; border-top:1px solid #e2e8f0; }
        .greeting { font-size:16px; font-weight:600; color:#1a3a5c; margin-bottom:16px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1><?= esc($subject) ?></h1>
            </div>
            <div class="content">
                <div class="greeting">Halo, <?= esc($nama) ?>!</div>
                <?= $body ?>
            </div>
            <div class="footer">
                &copy; <?= date('Y') ?> <?= esc(env('app.name', 'SiapASN Simulation Center')) ?>. Semua hak dilindungi.
            </div>
        </div>
    </div>
</body>
</html>
