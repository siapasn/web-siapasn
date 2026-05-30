<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/x-icon" href="<?= base_url('siapasn_favicon.ico') ?>">

    <?php
    $seo_title       = 'Hubungi Kami';
    $seo_description = 'Hubungi tim SiapASN Simulation Center melalui email, WhatsApp, atau kunjungi kantor kami. Kami siap membantu persiapan tryout CPNS & PPPK Anda.';
    $seo_canonical   = base_url('hubungi-kami');
    $seo_keywords    = 'hubungi SiapASN, kontak tryout CPNS, customer service CPNS';
    ?>
    <?= $this->include('partials/_seo_head') ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type":"ListItem","position":1,"name":"Beranda","item":"<?= base_url('/') ?>"},
            {"@type":"ListItem","position":2,"name":"Hubungi Kami","item":"<?= base_url('hubungi-kami') ?>"}
        ]
    }
    </script>
    <?php if (!empty($email) || !empty($wa) || !empty($alamat)): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "EducationalOrganization",
        "name": "SiapASN Simulation Center",
        "url": "<?= base_url('/') ?>",
        "logo": "<?= base_url('assets/images/SiapASN.png') ?>",
        "description": "Platform simulasi tryout CPNS & PPPK terlengkap di Indonesia.",
        <?php if (!empty($email)): ?>
        "email": "<?= esc($email) ?>",
        <?php endif; ?>
        <?php if (!empty($wa)): ?>
        "telephone": "<?= esc($wa) ?>",
        <?php endif; ?>
        <?php if (!empty($alamat)): ?>
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "<?= esc($alamat) ?>",
            "addressCountry": "ID"
        },
        <?php endif; ?>
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer support",
            "availableLanguage": "Indonesian"
        }
    }
    </script>
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

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
        .navbar-home .navbar-brand { color: #fff; font-weight: 700; font-size: 1.1rem; }
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
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem;
            border-radius: 10px;
            background: var(--sa-body-bg);
            border: 1px solid #e8edf2;
            transition: box-shadow .2s;
        }
        .contact-item:hover { box-shadow: 0 4px 12px rgba(26,58,92,.1); }
        .contact-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, var(--sa-primary), #2a5a8c);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            color: #fff;
            flex-shrink: 0;
        }
        .contact-label { font-size: .8rem; color: #6c757d; margin-bottom: .2rem; }
        .contact-value { font-weight: 600; color: var(--sa-primary); word-break: break-all; }
        .contact-value a { color: var(--sa-primary); text-decoration: none; }
        .contact-value a:hover { color: var(--sa-accent); }
        .prose { line-height: 1.8; color: #374151; }
        .prose h2, .prose h3 { color: var(--sa-primary); font-weight: 700; margin-top: 1.5rem; }
        .prose p { margin-bottom: 1rem; }
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
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navKontak">
            <i class="bi bi-list text-white fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="navKontak">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/') ?>">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('syarat-ketentuan') ?>">Syarat & Ketentuan</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('kebijakan-privasi') ?>">Kebijakan Privasi</a></li>
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
            <div class="ph-icon"><i class="bi bi-headset"></i></div>
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size:.8rem; opacity:.7;">
                        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-white text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Hubungi Kami</li>
                    </ol>
                </nav>
                <h1><?= esc($judul ?? 'Hubungi Kami') ?></h1>
            </div>
        </div>
    </div>
</div>

<!-- Content -->
<div class="container" style="max-width:860px; padding-bottom:60px;">
    <div class="content-card">

        <!-- Konten deskripsi dari admin (opsional) -->
        <?php if (!empty($konten)): ?>
            <div class="prose mb-4"><?= $konten ?></div>
            <hr class="my-4">
        <?php endif; ?>

        <!-- Info Kontak -->
        <h5 class="fw-bold mb-3" style="color:var(--sa-primary);">
            <i class="bi bi-info-circle me-2" style="color:var(--sa-accent)"></i>Informasi Kontak
        </h5>
        <div class="row g-3">

            <?php if (!empty($email)): ?>
            <div class="col-md-6">
                <div class="contact-item">
                    <div class="contact-icon"><i class="bi bi-envelope-fill"></i></div>
                    <div>
                        <div class="contact-label">Email</div>
                        <div class="contact-value">
                            <a href="mailto:<?= esc($email) ?>"><?= esc($email) ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($wa)): ?>
            <div class="col-md-6">
                <div class="contact-item">
                    <div class="contact-icon" style="background:linear-gradient(135deg,#25d366,#128c7e)">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div>
                        <div class="contact-label">WhatsApp</div>
                        <div class="contact-value">
                            <?php
                                $waClean = preg_replace('/[^0-9]/', '', $wa);
                                $waLink  = 'https://wa.me/' . ltrim($waClean, '0');
                                $waLink  = str_replace('wa.me/0', 'wa.me/62', $waLink);
                            ?>
                            <a href="<?= $waLink ?>" target="_blank" rel="noopener">
                                <?= esc($wa) ?> <i class="bi bi-box-arrow-up-right" style="font-size:.75rem"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($alamat)): ?>
            <div class="col-12">
                <div class="contact-item">
                    <div class="contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <div>
                        <div class="contact-label">Alamat</div>
                        <div class="contact-value"><?= esc($alamat) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($email) && empty($wa) && empty($alamat)): ?>
            <div class="col-12">
                <div class="text-center text-muted py-4">
                    <i class="bi bi-telephone-x fs-1 d-block mb-2 opacity-50"></i>
                    Informasi kontak belum tersedia. Silakan hubungi administrator.
                </div>
            </div>
            <?php endif; ?>

        </div>
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
