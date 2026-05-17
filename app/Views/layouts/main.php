<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= config('App')->appName ?? 'SiapASN Simulation Center' ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('siapasn_favicon.ico') ?>">

    <!-- Noindex: halaman user dashboard tidak perlu diindeks mesin pencari -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0f2744">

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --sa-primary:    #1a3a5c;
            --sa-primary-dk: #0f2744;
            --sa-accent:     #f5a623;
            --sa-sidebar-bg: #0f2744;
            --sa-body-bg:    #f0f4f8;
            --sa-border:     #dde3ea;
        }

        body {
            background-color: var(--sa-body-bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 15px;
        }

        /* Sidebar */
        #sidebar {
            width: 260px;
            min-height: 100vh;
            background-color: var(--sa-sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-shadow: 2px 0 8px rgba(0,0,0,0.15);
        }

        #sidebar .sidebar-brand {
            /* padding: 1rem 1.25rem; */
            background-color: var(--sa-primary-dk);
            text-decoration: none;
            display: flex;
            align-items: center;
            border-bottom: 2px solid var(--sa-accent);
        }

        #sidebar .nav-link {
            color: #a8c0d6;
            padding: 0.65rem 1.25rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            border-radius: 0;
            transition: background-color 0.2s, color 0.2s;
            border-left: 3px solid transparent;
        }

        #sidebar .nav-link:hover {
            background-color: rgba(245, 166, 35, 0.12);
            color: #fff;
            border-left-color: var(--sa-accent);
        }

        #sidebar .nav-link.active {
            background-color: rgba(245, 166, 35, 0.2);
            color: var(--sa-accent);
            border-left-color: var(--sa-accent);
            font-weight: 600;
        }

        #sidebar .nav-link i {
            font-size: 1rem;
            width: 1.2rem;
            text-align: center;
        }

        /* Main content offset */
        #main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        #topbar {
            background-color: #fff;
            border-bottom: 1px solid var(--sa-border);
            padding: 0.5rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        /* Content area */
        .content-area {
            flex: 1;
            padding: 1.5rem;
        }

        /* Page Header Banner */
        .page-header-banner {
            background: linear-gradient(135deg, var(--sa-primary-dk) 0%, var(--sa-primary) 60%, #1e5080 100%);
            padding: 1.25rem 1.5rem 1.4rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(15,39,68,.18);
        }
        /* Dekorasi lingkaran di background */
        /* .page-header-banner::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(245,166,35,.12);
            pointer-events: none;
        }
        .page-header-banner::after {
            content: '';
            position: absolute;
            bottom: -30px; right: 120px;
            width: 100px; height: 100px;
            border-radius: 50%;
            background: rgba(245,166,35,.07);
            pointer-events: none;
        } */
        .page-header-banner .ph-icon {
            width: 42px; height: 42px;
            background: rgba(245,166,35,.18);
            border: 1.5px solid rgba(245,166,35,.4);
            border-radius: .6rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            color: var(--sa-accent);
            flex-shrink: 0;
        }
        .page-header-banner .ph-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.25;
            letter-spacing: -.01em;
        }
        .page-header-banner .ph-subtitle {
            font-size: .8rem;
            color: rgba(255,255,255,.65);
            margin-top: .15rem;
        }
        .page-header-banner .ph-accent-line {
            width: 36px; height: 3px;
            background: var(--sa-accent);
            border-radius: 2px;
            margin-top: .4rem;
        }

        .btn-primary {
            background-color: var(--sa-primary);
            border-color: var(--sa-primary);
        }
        .btn-primary:hover {
            background-color: var(--sa-primary-dk);
            border-color: var(--sa-primary-dk);
        }

        /* Footer */
        footer {
            background-color: #fff;
            border-top: 1px solid var(--sa-border);
            padding: 0.75rem 1.5rem;
            font-size: 0.85rem;
            color: #64748b;
            text-align: center;
        }

        /* Responsive: collapse sidebar on small screens */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.show {
                transform: translateX(0);
            }

            #main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar">
    <a href="<?= base_url('/user/dashboard') ?>" class="sidebar-brand">
        <img src="<?= base_url('assets/images/SiapASN.png') ?>"
             alt="Logo"
             style="height:auto; width:100%; object-fit:contain; vertical-align:middle;">
        <!-- <?= config('App')->appName ?? 'SiapASN Simulation Center' ?> -->
    </a>

    <ul class="nav flex-column mt-2">
        <?php if (!empty($menus)): ?>
            <?php foreach ($menus as $menu): ?>
                <?php if (empty($menu['parent_key']) && $menu['is_visible']): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= current_url() === base_url(ltrim($menu['url'], '/')) ? 'active' : '' ?>"
                           href="<?= $menu['url'] !== '#' ? base_url(ltrim($menu['url'], '/')) : '#' ?>">
                            <i class="bi <?= esc($menu['icon']) ?>"></i>
                            <?= esc($menu['label']) ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</nav>

<!-- Main Content -->
<div id="main-content">

    <!-- Top Navbar -->
    <nav id="topbar" class="navbar navbar-expand">
        <div class="container-fluid px-0">
            <!-- Sidebar toggle (mobile) -->
            <button class="btn btn-sm btn-outline-secondary d-md-none me-2" id="sidebarToggle" type="button">
                <i class="bi bi-list"></i>
            </button>

            <span class="navbar-brand mb-0 h6 d-none d-md-block text-muted">
                <?= config('App')->appName ?? 'SiapASN Simulation Center' ?>
            </span>

            <div class="ms-auto d-flex align-items-center gap-2">
                <!-- Keranjang Belanja -->
                <?php
                $cartCount = 0;
                try {
                    $cartSvc   = new \App\Services\CartService();
                    $cartCount = $cartSvc->count((int) session()->get('user_id'));
                } catch (\Exception $e) { $cartCount = 0; }
                ?>
                <a href="<?= base_url('user/cart') ?>"
                   class="btn btn-sm btn-outline-primary position-relative"
                   title="Keranjang Belanja">
                    <i class="bi bi-cart3 fs-6"></i>
                    <span id="cart-badge"
                          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          style="font-size:.6rem;<?= $cartCount === 0 ? 'display:none' : '' ?>">
                        <?= $cartCount ?>
                    </span>
                </a>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <span><?= esc(session('nama') ?? 'Pengguna') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <span class="dropdown-item-text text-muted small">
                                <?= esc(session('email') ?? '') ?>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('user/profil') ?>">
                                <i class="bi bi-person-gear me-1"></i> Profil Saya
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header Banner (diisi dari view via section 'page_header') -->
    <?php $pageHeader = $this->renderSection('page_header'); ?>
    <?php if (trim($pageHeader)): ?>
        <div class="page-header-banner">
            <?= $pageHeader ?>
        </div>
    <?php endif; ?>

    <!-- Content Area -->
    <div class="content-area">

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i>
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>

    </div><!-- /.content-area -->

    <footer>
        &copy; <?= date('Y') ?> <?= config('App')->appName ?? 'SiapASN Simulation Center' ?>. Hak cipta dilindungi.
    </footer>

</div><!-- /#main-content -->

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });
    }
</script>

</body>
</html>
