<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?> — <?= config('App')->appName ?? 'SiapASN Simulation Center' ?></title>

    <link rel="icon" type="image/x-icon" href="<?= base_url('siapasn_favicon.ico') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --sa-primary:    #1a3a5c;
            --sa-primary-dk: #0f2744;
            --sa-accent:     #f5a623;
            --sa-body-bg:    #f0f4f8;
        }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--sa-body-bg);
        }
        .navbar-home {
            background-color: var(--sa-primary-dk);
            padding: .75rem 0;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
        }
        .navbar-home .navbar-brand {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .navbar-home .navbar-brand span { color: var(--sa-accent); }
        .navbar-home .nav-link { color: rgba(255,255,255,.8) !important; font-size:.9rem; }
        .navbar-home .nav-link:hover { color: var(--sa-accent) !important; }
        .btn-login {
            background-color: var(--sa-accent);
            color: var(--sa-primary-dk) !important;
            font-weight: 600;
            border-radius: 6px;
            padding: .4rem 1.2rem;
        }
        .page-header {
            background: linear-gradient(135deg, var(--sa-primary-dk), var(--sa-primary));
            color: #fff;
            padding: 50px 0 40px;
        }
        .page-header .ph-icon {
            width: 56px; height: 56px;
            background: rgba(245,166,35,.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            color: var(--sa-accent);
            flex-shrink: 0;
        }
        .page-header h1 { font-size: 1.8rem; font-weight: 700; margin: 0; }
        .content-card {
            background: #fff;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 2px 12px rgba(26,58,92,.08);
            margin-top: -20px;
            position: relative;
            z-index: 1;
        }
        .content-card .prose { line-height: 1.8; color: #374151; }
        .content-card .prose h2,
        .content-card .prose h3 { color: var(--sa-primary); font-weight: 700; margin-top: 1.5rem; }
        .content-card .prose p { margin-bottom: 1rem; }
        .content-card .prose ul, .content-card .prose ol { padding-left: 1.5rem; margin-bottom: 1rem; }
        .content-card .prose li { margin-bottom: .4rem; }
        footer {
            background-color: var(--sa-primary-dk);
            color: rgba(255,255,255,.6);
            padding: 24px 0;
            font-size: .85rem;
            text-align: center;
            margin-top: 60px;
        }
        footer a { color: rgba(255,255,255,.6); text-decoration: none; }
        footer a:hover { color: var(--sa-accent); }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-home navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('/') ?>">
            <i class="bi bi-mortarboard-fill me-1" style="color:var(--sa-accent)"></i>
            SiapASN <span>Simulation</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPage">
            <i class="bi bi-list text-white fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="navPage">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/') ?>">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('hubungi-kami') ?>">Hubungi Kami</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="nav-link btn-login" href="<?= base_url('login') ?>">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <div class="ph-icon"><i class="bi <?= esc($icon ?? 'bi-file-earmark-text') ?>"></i></div>
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size:.8rem; opacity:.7;">
                        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-white text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page"><?= esc($judul) ?></li>
                    </ol>
                </nav>
                <h1><?= esc($judul) ?></h1>
            </div>
        </div>
    </div>
</div>

<!-- Content -->
<div class="container" style="max-width:860px; padding-bottom:60px;">
    <div class="content-card">
        <?php if (!empty($konten)): ?>
            <div class="prose"><?= $konten /* HTML dari editor, sudah disimpan admin */ ?></div>
        <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-file-earmark-x fs-1 d-block mb-2 opacity-50"></i>
                Konten belum tersedia. Silakan hubungi administrator.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="mb-2">
            <a href="<?= base_url('syarat-ketentuan') ?>">Syarat dan Ketentuan</a>
            &nbsp;·&nbsp;
            <a href="<?= base_url('kebijakan-privasi') ?>">Kebijakan Privasi</a>
            &nbsp;·&nbsp;
            <a href="<?= base_url('hubungi-kami') ?>">Hubungi Kami</a>
        </div>
        &copy; <?= date('Y') ?> SiapASN Simulation Center. Hak cipta dilindungi.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
