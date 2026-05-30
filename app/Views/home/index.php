<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('siapasn_favicon.ico') ?>">

    <?php
    $seo_title       = '';
    $seo_description = 'Platform simulasi tryout CPNS & PPPK terlengkap. Ribuan soal SKD & SKB terverifikasi, pembahasan lengkap, analisis nilai real-time. Persiapkan dirimu lolos seleksi ASN bersama SiapASN.';
    $seo_keywords    = 'tryout CPNS online, simulasi CPNS, latihan soal CPNS, SKD online, SKB online, PPPK, seleksi ASN, bimbel CPNS, passing grade CPNS, soal CPNS 2025';
    $seo_canonical   = base_url('/');
    $seo_page_type   = 'home';
    ?>
    <?= $this->include('partials/_seo_head') ?>

    <!-- Bootstrap 5.3 CSS -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --sa-primary:    #1a3a5c;
            --sa-primary-dk: #0f2744;
            --sa-accent:     #f5a623;
            --sa-accent-dk:  #d4891a;
            --sa-body-bg:    #f0f4f8;
        }

        * { scroll-behavior: smooth; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #fff;
        }

        /* ---- Navbar ---- */
        .navbar-home {
            background-color: var(--sa-primary-dk);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
        }
        .navbar-home .navbar-brand {
            color: #fff;
            font-weight: 700;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar-home .navbar-brand span.accent { color: var(--sa-accent); }
        .navbar-home .nav-link {
            color: rgba(255,255,255,.8) !important;
            font-size: 0.9rem;
            transition: color .2s;
        }
        .navbar-home .nav-link:hover { color: var(--sa-accent) !important; }
        .btn-login {
            background-color: var(--sa-accent);
            color: var(--sa-primary-dk) !important;
            font-weight: 600;
            border-radius: 6px;
            padding: 0.4rem 1.2rem;
            transition: background-color .2s;
        }
        .btn-login:hover { background-color: var(--sa-accent-dk); }

        /* ---- Hero ---- */
        #hero {
            background: linear-gradient(135deg, var(--sa-primary-dk) 0%, var(--sa-primary) 60%, #1e5080 100%);
            color: #fff;
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
        }
        #hero::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 400px; height: 400px;
            background: rgba(245,166,35,.08);
            border-radius: 50%;
        }
        #hero::after {
            content: '';
            position: absolute;
            bottom: -100px; left: -60px;
            width: 300px; height: 300px;
            background: rgba(245,166,35,.06);
            border-radius: 50%;
        }
        #hero h1 {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            line-height: 1.2;
        }
        #hero h1 .accent { color: var(--sa-accent); }
        #hero p.lead {
            font-size: 1.1rem;
            color: rgba(255,255,255,.85);
            max-width: 560px;
        }
        .btn-hero-primary {
            background-color: var(--sa-accent);
            color: var(--sa-primary-dk);
            font-weight: 700;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            border: none;
            transition: background-color .2s, transform .15s;
        }
        .btn-hero-primary:hover {
            background-color: var(--sa-accent-dk);
            transform: translateY(-2px);
        }
        .btn-hero-outline {
            background: transparent;
            color: #fff;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            border: 2px solid rgba(255,255,255,.5);
            transition: border-color .2s, background .2s;
        }
        .btn-hero-outline:hover {
            border-color: #fff;
            background: rgba(255,255,255,.1);
            color: #fff;
        }

        /* ---- Stats ---- */
        #stats {
            background-color: var(--sa-primary);
            padding: 40px 0;
        }
        .stat-item { text-align: center; color: #fff; }
        .stat-item .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--sa-accent);
            line-height: 1;
        }
        .stat-item .stat-label {
            font-size: 0.9rem;
            color: rgba(255,255,255,.75);
            margin-top: 4px;
        }

        /* ---- Fitur ---- */
        #fitur {
            padding: 80px 0;
            background-color: var(--sa-body-bg);
        }
        .section-title {
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--sa-primary);
        }
        .section-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }
        .feature-card {
            background: #fff;
            border-radius: 12px;
            padding: 2rem 1.5rem;
            text-align: center;
            border: 1px solid #e8edf2;
            transition: box-shadow .25s, transform .25s;
            height: 100%;
        }
        .feature-card:hover {
            box-shadow: 0 8px 24px rgba(26,58,92,.12);
            transform: translateY(-4px);
        }
        .feature-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--sa-primary), #2a5a8c);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 1.75rem;
            color: #fff;
        }
        .feature-card h5 {
            font-weight: 700;
            color: var(--sa-primary);
            margin-bottom: .5rem;
        }
        .feature-card p {
            color: #6c757d;
            font-size: .9rem;
            margin: 0;
        }

        /* ---- Cara Kerja ---- */
        #cara-kerja {
            padding: 80px 0;
            background: #fff;
        }
        .step-number {
            width: 48px; height: 48px;
            background-color: var(--sa-accent);
            color: var(--sa-primary-dk);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        /* ---- CTA ---- */
        #cta {
            background: linear-gradient(135deg, var(--sa-primary-dk), var(--sa-primary));
            color: #fff;
            padding: 80px 0;
            text-align: center;
        }
        #cta h2 { font-weight: 800; font-size: 2rem; }
        #cta p { color: rgba(255,255,255,.8); font-size: 1.05rem; }

        /* ---- Footer ---- */
        footer {
            background-color: var(--sa-primary-dk);
            color: rgba(255,255,255,.65);
            padding: 40px 0 20px;
            font-size: .875rem;
        }
        footer .footer-brand {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
        }
        footer .footer-brand span { color: var(--sa-accent); }
        footer a {
            color: rgba(255,255,255,.65);
            text-decoration: none;
            transition: color .2s;
        }
        footer a:hover { color: var(--sa-accent); }
        footer hr { border-color: rgba(255,255,255,.1); }
        .footer-link-list { list-style: none; padding: 0; margin: 0; }
        .footer-link-list li { margin-bottom: .4rem; }

        /* ---- Responsive ---- */
        @media (max-width: 768px) {
            #hero { padding: 70px 0 60px; }
            .stat-item .stat-number { font-size: 2rem; }
        }
    </style>
</head>
<body>

<!-- ================================================================
     NAVBAR
================================================================ -->
<nav class="navbar navbar-home navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('/') ?>">
            <i class="bi bi-mortarboard-fill" style="color:var(--sa-accent)"></i>
            SiapASN <span class="accent">Simulation</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navHome">
            <i class="bi bi-list text-white fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="navHome">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li>
                <li class="nav-item"><a class="nav-link" href="#cara-kerja">Cara Kerja</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('hubungi-kami') ?>">Hubungi Kami</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="nav-link btn-login" href="<?= base_url('login') ?>">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('register') ?>"
                       style="border:1px solid rgba(255,255,255,.4); border-radius:6px; padding:.4rem 1.2rem;">
                        Daftar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ================================================================
     HERO
================================================================ -->
<section id="hero">
    <div class="container position-relative" style="z-index:1">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7">
                <h1>
                    <?= esc($content['hero_tagline'] ?? 'Persiapkan Dirimu<br>Lolos <span class="accent">Seleksi CPNS</span>') ?>
                </h1>
                <p class="lead mt-3 mb-4">
                    <?= esc($content['hero_deskripsi'] ?? 'Platform tryout online terlengkap untuk persiapan seleksi CPNS & PPPK. Latihan soal, analisis hasil, dan raih passing grade impianmu.') ?>
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= base_url('register') ?>" class="btn-hero-primary">
                        <i class="bi bi-rocket-takeoff me-1"></i> Mulai Gratis
                    </a>
                    <a href="#fitur" class="btn-hero-outline">
                        Pelajari Lebih Lanjut
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <div style="background:rgba(255,255,255,.07); border-radius:20px; padding:2.5rem; border:1px solid rgba(255,255,255,.12);">
                    <i class="bi bi-clipboard2-check" style="font-size:6rem; color:var(--sa-accent); opacity:.9;"></i>
                    <div class="mt-3 text-white fw-semibold fs-5">Tryout Online Terpercaya</div>
                    <div class="mt-1" style="color:rgba(255,255,255,.65); font-size:.9rem;">Ribuan soal terverifikasi & pembahasan lengkap</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     STATS
================================================================ -->
<!-- <section id="stats">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?= esc($content['stat_pengguna'] ?? '10.000+') ?></div>
                    <div class="stat-label"><i class="bi bi-people me-1"></i>Pengguna Aktif</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?= esc($content['stat_soal'] ?? '5.000+') ?></div>
                    <div class="stat-label"><i class="bi bi-question-circle me-1"></i>Bank Soal</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?= esc($content['stat_paket'] ?? '50+') ?></div>
                    <div class="stat-label"><i class="bi bi-box-seam me-1"></i>Paket Tryout</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?= $totalProduk > 0 ? $totalProduk . '+' : '20+' ?></div>
                    <div class="stat-label"><i class="bi bi-grid me-1"></i>Produk Tersedia</div>
                </div>
            </div>
        </div>
    </div>
</section> -->

<!-- ================================================================
     FITUR
================================================================ -->
<section id="fitur">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-title">Kenapa Pilih SiapASN?</div>
            <p class="section-subtitle mt-2">Semua yang kamu butuhkan untuk lolos seleksi CPNS ada di sini</p>
        </div>
        <div class="row g-4">
            <div class="col-sm-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-journal-text"></i></div>
                    <h5>Bank Soal Lengkap</h5>
                    <p>Ribuan soal SKD & SKB yang diperbarui sesuai kisi-kisi terbaru BKN.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-bar-chart-line"></i></div>
                    <h5>Analisis Hasil Detail</h5>
                    <p>Lihat skor per kategori, perbandingan dengan passing grade, dan progres belajarmu.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-stopwatch"></i></div>
                    <h5>Simulasi Waktu Nyata</h5>
                    <p>Tryout dengan timer persis seperti ujian sesungguhnya agar kamu terbiasa.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-lightbulb"></i></div>
                    <h5>Pembahasan Lengkap</h5>
                    <p>Setiap soal dilengkapi pembahasan mendalam agar kamu benar-benar paham.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-phone"></i></div>
                    <h5>Akses Multi-Perangkat</h5>
                    <p>Belajar kapan saja dan di mana saja melalui browser di HP maupun laptop.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                    <h5>Soal Terverifikasi</h5>
                    <p>Semua soal dikurasi oleh tim ahli berpengalaman di bidang seleksi ASN.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     CARA KERJA
================================================================ -->
<section id="cara-kerja">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-title">Cara Mulai Belajar</div>
            <p class="section-subtitle mt-2">Hanya 3 langkah mudah untuk memulai persiapanmu</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="d-flex gap-3 align-items-start">
                    <div class="step-number">1</div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Daftar Akun</h6>
                        <p class="text-muted mb-0" style="font-size:.9rem;">Buat akun gratis dalam hitungan detik. Tidak perlu kartu kredit.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-3 align-items-start">
                    <div class="step-number">2</div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Pilih Paket</h6>
                        <p class="text-muted mb-0" style="font-size:.9rem;">Pilih paket tryout yang sesuai dengan formasi yang kamu lamar.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-3 align-items-start">
                    <div class="step-number">3</div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Mulai Tryout</h6>
                        <p class="text-muted mb-0" style="font-size:.9rem;">Kerjakan soal, lihat hasil, dan pelajari pembahasannya.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     CTA
================================================================ -->
<section id="cta">
    <div class="container">
        <h2>Siap Lolos Seleksi CPNS?</h2>
        <p class="mt-2 mb-4">Bergabung dengan ribuan peserta yang sudah mempersiapkan diri bersama SiapASN.</p>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <a href="<?= base_url('register') ?>" class="btn-hero-primary">
                <i class="bi bi-person-plus me-1"></i> Daftar Sekarang — Gratis
            </a>
            <a href="<?= base_url('login') ?>" class="btn-hero-outline">
                Sudah punya akun? Masuk
            </a>
        </div>
    </div>
</section>

<!-- ================================================================
     FOOTER
================================================================ -->
<footer>
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="footer-brand mb-2">
                    <i class="bi bi-mortarboard-fill me-1" style="color:var(--sa-accent)"></i>
                    SiapASN <span>Simulation</span>
                </div>
                <p class="mb-0" style="font-size:.85rem;">
                    Platform tryout online terpercaya untuk persiapan seleksi CPNS & PPPK Indonesia.
                </p>
            </div>
            <div class="col-md-2 offset-md-2">
                <div class="fw-semibold text-white mb-2" style="font-size:.9rem;">Navigasi</div>
                <ul class="footer-link-list">
                    <li><a href="#fitur">Fitur</a></li>
                    <li><a href="#cara-kerja">Cara Kerja</a></li>
                    <li><a href="<?= base_url('login') ?>">Masuk</a></li>
                    <li><a href="<?= base_url('register') ?>">Daftar</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <div class="fw-semibold text-white mb-2" style="font-size:.9rem;">Legal & Bantuan</div>
                <ul class="footer-link-list">
                    <li><a href="<?= base_url('syarat-ketentuan') ?>">Syarat dan Ketentuan</a></li>
                    <li><a href="<?= base_url('kebijakan-privasi') ?>">Kebijakan Privasi</a></li>
                    <li><a href="<?= base_url('hubungi-kami') ?>">Hubungi Kami</a></li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="text-center" style="font-size:.8rem;">
            &copy; <?= date('Y') ?> SiapASN Simulation Center. Hak cipta dilindungi.
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- FAQ Schema — Rich Snippet Google -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "Apa itu SiapASN Simulation Center?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "SiapASN Simulation Center adalah platform tryout online untuk persiapan seleksi CPNS dan PPPK. Kami menyediakan ribuan soal SKD dan SKB terverifikasi, pembahasan lengkap, dan analisis nilai real-time."
            }
        },
        {
            "@type": "Question",
            "name": "Apakah tryout di SiapASN gratis?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Pendaftaran akun di SiapASN gratis. Tersedia berbagai paket tryout yang bisa dipilih sesuai kebutuhan persiapan seleksi ASN Anda."
            }
        },
        {
            "@type": "Question",
            "name": "Soal apa saja yang tersedia di SiapASN?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "SiapASN menyediakan soal SKD (Seleksi Kompetensi Dasar) meliputi TWK, TIU, dan TKP, serta soal SKB (Seleksi Kompetensi Bidang) untuk berbagai formasi CPNS dan PPPK."
            }
        },
        {
            "@type": "Question",
            "name": "Bagaimana cara mendaftar di SiapASN?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Klik tombol Daftar di halaman utama, isi nama, email, dan password, lalu verifikasi email Anda. Proses pendaftaran selesai dalam hitungan menit."
            }
        }
    ]
}
</script>

</body>
</html>
