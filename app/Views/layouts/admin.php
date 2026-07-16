<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= config('App')->appName ?? 'SiapASN Simulation Center' ?> — Admin</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('siapasn_favicon.ico') ?>">

    <!-- Noindex: halaman admin tidak perlu diindeks mesin pencari -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0f2744">

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <!-- Summernote CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css">

    <style>
        /* ============================================================
           SiapASN Simulation Center — Color Palette
           Primary  : #1a3a5c (biru tua)
           Accent   : #f5a623 (kuning emas)
           Sidebar  : #0f2744 (biru sangat gelap)
           BG       : #f0f4f8 (abu biru muda)
           ============================================================ */

        :root {
            --sa-primary:     #1a3a5c;
            --sa-primary-dk:  #0f2744;
            --sa-accent:      #f5a623;
            --sa-accent-dk:   #d4891a;
            --sa-sidebar-bg:  #0f2744;
            --sa-sidebar-txt: #a8c0d6;
            --sa-sidebar-hover-bg: rgba(245, 166, 35, 0.12);
            --sa-sidebar-active-bg: rgba(245, 166, 35, 0.2);
            --sa-sidebar-active-txt: #f5a623;
            --sa-body-bg:     #f0f4f8;
            --sa-topbar-bg:   #ffffff;
            --sa-border:      #dde3ea;
        }

        /* ── Select2 Global Small Size ── */
        .select2-container--bootstrap-5 .select2-selection--single {
            font-size: .8rem !important;
            min-height: 32px !important;
            padding: 2px 8px;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            font-size: .8rem !important;
            line-height: 1;
            padding-top: 0;
            padding-bottom: 0;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 100%;
            display: flex;
            align-items: center;
        }
        .select2-container--bootstrap-5 .select2-dropdown .select2-results__option {
            font-size: .8rem !important;
            padding: 5px 10px;
        }
        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            font-size: .8rem !important;
            padding: 4px 8px;
        }

        body {
            background-color: var(--sa-body-bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* ---- Sidebar ---- */
        #sidebar {
            width: 260px;
            height: 100vh;
            max-height: 100vh;
            background-color: var(--sa-sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.3s ease;
            box-shadow: 2px 0 8px rgba(0,0,0,0.15);
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.15) transparent;
        }
        #sidebar::-webkit-scrollbar {
            width: 4px;
        }
        #sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        #sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.2);
            border-radius: 2px;
        }

        #sidebar .sidebar-brand {
            /* padding: 1rem 1.25rem; */
            background-color: var(--sa-primary-dk);
            text-decoration: none;
            display: flex;
            align-items: center;
            border-bottom: 2px solid var(--sa-accent);
        }

        /* Top-level nav links */
        #sidebar .nav-link {
            color: var(--sa-sidebar-txt);
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
            background-color: var(--sa-sidebar-hover-bg);
            color: #fff;
            border-left-color: var(--sa-accent);
        }

        #sidebar .nav-link.active {
            background-color: var(--sa-sidebar-active-bg);
            color: var(--sa-sidebar-active-txt);
            border-left-color: var(--sa-accent);
            font-weight: 600;
        }

        #sidebar .nav-link i {
            font-size: 1rem;
            width: 1.2rem;
            text-align: center;
            flex-shrink: 0;
        }

        /* Collapse toggle arrow */
        #sidebar .nav-link[data-bs-toggle="collapse"]::after {
            content: '\F282';
            font-family: 'bootstrap-icons';
            margin-left: auto;
            font-size: 0.7rem;
            transition: transform 0.2s;
            opacity: 0.6;
        }

        #sidebar .nav-link[data-bs-toggle="collapse"].collapsed::after {
            transform: rotate(-90deg);
        }

        /* Sub-menu */
        #sidebar .submenu {
            background-color: rgba(0, 0, 0, 0.2);
        }

        #sidebar .submenu .nav-link {
            padding-left: 3rem;
            font-size: 0.83rem;
            color: #7fa8c9;
            border-left: 3px solid transparent;
        }

        #sidebar .submenu .nav-link:hover {
            background-color: var(--sa-sidebar-hover-bg);
            color: #fff;
            border-left-color: var(--sa-accent);
        }

        #sidebar .submenu .nav-link.active {
            background-color: var(--sa-sidebar-active-bg);
            color: var(--sa-sidebar-active-txt);
            border-left-color: var(--sa-accent);
            font-weight: 600;
        }

        /* ---- Topbar ---- */
        #main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        #topbar {
            background-color: var(--sa-topbar-bg);
            border-bottom: 1px solid var(--sa-border);
            padding: 0.5rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        /* ---- Content ---- */
        .content-area {
            flex: 1;
            padding: 1.5rem;
        }

        .notif-menu {
            width: 340px;
            max-height: 400px;
            overflow-y: auto;
        }

        /* Mobile Version */
        @media (max-width: 768px) {
            .content-area {
                padding: 0.5rem;
            }

            .notif-menu.show {
                position: fixed !important;
                top: 64px !important;
                left: 50% !important;
                right: auto !important;
                transform: translateX(-50%) !important;
                width: min(340px, calc(100vw - 32px)) !important;
                max-height: min(420px, calc(100vh - 96px));
                border-radius: .75rem;
                overflow-y: auto;
            }
        }
        
        /* ---- Page Header Banner ---- */
        .page-header-banner {
            background: linear-gradient(135deg, var(--sa-primary-dk) 0%, var(--sa-primary) 60%, #1e5080 100%);
            padding: 1.1rem 1.5rem 1.25rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(15,39,68,.18);
        }
        /* .page-header-banner::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(245,166,35,.1);
            pointer-events: none;
        }
        .page-header-banner::after {
            content: '';
            position: absolute;
            bottom: -30px; right: 120px;
            width: 90px; height: 90px;
            border-radius: 50%;
            background: rgba(245,166,35,.06);
            pointer-events: none;
        } */
        .page-header-banner .ph-icon {
            width: 40px; height: 40px;
            background: rgba(245,166,35,.18);
            border: 1.5px solid rgba(245,166,35,.4);
            border-radius: .55rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem;
            color: var(--sa-accent);
            flex-shrink: 0;
        }
        .page-header-banner .ph-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.25;
            letter-spacing: -.01em;
        }
        .page-header-banner .ph-subtitle {
            font-size: .78rem;
            color: rgba(255,255,255,.6);
            margin-top: .1rem;
        }
        .page-header-banner .ph-accent-line {
            width: 32px; height: 3px;
            background: var(--sa-accent);
            border-radius: 2px;
            margin-top: .35rem;
        }
        .page-header-banner .ph-action {
            background: rgba(245,166,35,.18);
            border: 1px solid rgba(245,166,35,.4);
            color: var(--sa-accent);
            font-weight: 600;
            font-size: .8rem;
            border-radius: .45rem;
            padding: .35rem .85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            transition: background .15s;
            white-space: nowrap;
        }
        .page-header-banner .ph-action:hover {
            background: rgba(245,166,35,.3);
            color: #fff;
        }

        /* ---- Cards override ---- */
        .card {
            border-radius: 10px;
        }

        /* ---- Buttons override ---- */
        .btn-primary {
            background-color: var(--sa-primary);
            border-color: var(--sa-primary);
        }
        .btn-primary:hover {
            background-color: var(--sa-primary-dk);
            border-color: var(--sa-primary-dk);
        }

        /* ---- Badge role ---- */
        .badge.bg-danger { background-color: #c0392b !important; }
        .badge.bg-primary { background-color: var(--sa-primary) !important; }

        /* ---- Footer ---- */
        footer {
            background-color: var(--sa-topbar-bg);
            border-top: 1px solid var(--sa-border);
            padding: 0.75rem 1.5rem;
            font-size: 0.82rem;
            color: #64748b;
            text-align: center;
        }

        /* ---- Responsive ---- */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar">
    <a href="<?= base_url('/admin/dashboard') ?>" class="sidebar-brand">
        <img src="<?= base_url('assets/images/SiapASN.png') ?>"
             alt="Logo"
             style="height:auto; width:100%; object-fit:contain; vertical-align:middle;">
        <!-- <?= config('App')->appName ?? 'SiapASN Simulation Center' ?> -->
    </a>

    <ul class="nav flex-column mt-2" id="sidebarMenu">
        <?php if (!empty($menus)): ?>
            <?php
            // Separate top-level and child menus
            $topMenus = [];
            $childMenus = [];
            foreach ($menus as $menu) {
                if (!$menu['is_visible']) continue;
                if (empty($menu['parent_key'])) {
                    $topMenus[] = $menu;
                } else {
                    $childMenus[$menu['parent_key']][] = $menu;
                }
            }
            // Sort top-level by urutan
            usort($topMenus, fn($a, $b) => $a['urutan'] <=> $b['urutan']);
            ?>

            <?php foreach ($topMenus as $menu): ?>
                <?php $hasChildren = !empty($childMenus[$menu['menu_key']]); ?>

                <?php if ($hasChildren): ?>
                    <?php
                    // Sort children by urutan
                    usort($childMenus[$menu['menu_key']], fn($a, $b) => $a['urutan'] <=> $b['urutan']);
                    $collapseId = 'collapse_' . esc($menu['menu_key']);
                    // Check if any child is active
                    $isGroupActive = false;
                    foreach ($childMenus[$menu['menu_key']] as $child) {
                        if (current_url() === base_url(ltrim($child['url'], '/'))) {
                            $isGroupActive = true;
                            break;
                        }
                    }
                    ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $isGroupActive ? '' : 'collapsed' ?>"
                           href="#<?= $collapseId ?>"
                           data-bs-toggle="collapse"
                           aria-expanded="<?= $isGroupActive ? 'true' : 'false' ?>">
                            <i class="bi <?= esc($menu['icon']) ?>"></i>
                            <?= esc($menu['label']) ?>
                        </a>
                        <div id="<?= $collapseId ?>" class="collapse <?= $isGroupActive ? 'show' : '' ?>">
                            <ul class="nav flex-column submenu">
                                <?php foreach ($childMenus[$menu['menu_key']] as $child): ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?= current_url() === base_url(ltrim($child['url'], '/')) ? 'active' : '' ?>"
                                           href="<?= base_url(ltrim($child['url'], '/')) ?>">
                                            <i class="bi <?= esc($child['icon']) ?>"></i>
                                            <?= esc($child['label']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                <?php else: ?>
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
                <!-- Notifikasi -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary position-relative" type="button"
                            id="notifDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                            title="Notifikasi">
                        <i class="bi bi-bell fs-6"></i>
                        <span id="notif-badge"
                              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              style="font-size:.6rem;display:none">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm p-0 notif-menu" aria-labelledby="notifDropdown">
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                            <span class="fw-semibold small">Notifikasi</span>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" id="markAllReadBtn" style="font-size:.75rem">
                                Tandai semua dibaca
                            </button>
                        </div>
                        <div id="notifList" class="py-1">
                            <div class="text-center text-muted py-3 small">Memuat...</div>
                        </div>
                    </div>
                </div>

                <!-- Role badge -->
                <?php $role = session('role') ?? 'admin'; ?>
                <span class="badge bg-<?= $role === 'super_admin' ? 'danger' : 'primary' ?> text-uppercase small">
                    <?= esc(str_replace('_', ' ', $role)) ?>
                </span>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <span><?= esc(session('nama') ?? 'Admin') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <span class="dropdown-item-text text-muted small">
                                <?= esc(session('email') ?? '') ?>
                            </span>
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

    <!-- Page Header Banner -->
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

        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill me-1"></i>
                <?= esc(session()->getFlashdata('warning')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-1"></i>
                <?= esc(session()->getFlashdata('info')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>

    </div><!-- /.content-area -->

    <footer>
        &copy; <?= date('Y') ?> <?= config('App')->appName ?? 'SiapASN Simulation Center' ?>. Hak cipta dilindungi.
    </footer>

</div><!-- /#main-content -->

<!-- jQuery — harus pertama sebelum Bootstrap dan semua plugin -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Summernote JS — lite build, tidak bergantung Bootstrap tooltip -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

<?= $this->renderSection('scripts') ?>

<script>
// ── Notifikasi Admin ──
(function () {
    const badge    = document.getElementById('notif-badge');
    const list     = document.getElementById('notifList');
    const markBtn  = document.getElementById('markAllReadBtn');
    const dropdown = document.getElementById('notifDropdown');

    if (!badge || !list) return;

    const baseNotifUrl = '<?= base_url('admin/notifikasi') ?>';

    function loadNotif() {
        fetch(baseNotifUrl)
            .then(r => r.json())
            .then(data => {
                if (data.unread > 0) {
                    badge.textContent = data.unread > 99 ? '99+' : data.unread;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }

                if (!data.notifikasi || data.notifikasi.length === 0) {
                    list.innerHTML = '<div class="text-center text-muted py-3 small">Belum ada notifikasi</div>';
                    return;
                }

                let html = '';
                data.notifikasi.forEach(function (n) {
                    const isUnread = parseInt(n.is_read) === 0;
                    const bg = isUnread ? 'background:#f0f7ff;' : '';
                    const readUrl = baseNotifUrl + '/' + n.id + '/read';
                    const timeAgo = n.created_at ? n.created_at.substring(5, 16) : '';

                    html += '<a href="' + readUrl + '" class="dropdown-item px-3 py-2 border-bottom" style="white-space:normal;' + bg + '">';
                    html += '<div class="d-flex gap-2">';
                    html += '<i class="bi bi-' + getIcon(n.tipe) + ' mt-1 flex-shrink-0" style="color:' + getColor(n.tipe) + '"></i>';
                    html += '<div>';
                    html += '<div class="fw-semibold" style="font-size:.8rem">' + escHtml(n.judul) + '</div>';
                    if (n.pesan) html += '<div class="text-muted" style="font-size:.72rem">' + escHtml(n.pesan).substring(0, 80) + '</div>';
                    html += '<div class="text-muted" style="font-size:.65rem">' + timeAgo + '</div>';
                    html += '</div></div></a>';
                });

                list.innerHTML = html;
            })
            .catch(function () {
                list.innerHTML = '<div class="text-center text-muted py-3 small">Gagal memuat</div>';
            });
    }

    function getIcon(tipe) {
        switch (tipe) { case 'transaksi': return 'receipt'; case 'request_formasi': return 'inbox'; case 'event': return 'calendar-event'; default: return 'bell'; }
    }
    function getColor(tipe) {
        switch (tipe) { case 'transaksi': return '#198754'; case 'request_formasi': return '#f5a623'; case 'event': return '#0dcaf0'; default: return '#6c757d'; }
    }
    function escHtml(str) { var d = document.createElement('div'); d.textContent = str; return d.innerHTML; }

    dropdown.addEventListener('show.bs.dropdown', loadNotif);

    if (markBtn) {
        markBtn.addEventListener('click', function () {
            fetch(baseNotifUrl + '/mark-all-read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: '<?= csrf_token() ?>=<?= csrf_hash() ?>',
            }).then(function () {
                badge.style.display = 'none';
                list.querySelectorAll('.dropdown-item').forEach(function (el) { el.style.background = ''; });
            });
        });
    }

    // Initial badge count
    fetch(baseNotifUrl).then(r => r.json()).then(data => {
        if (data.unread > 0) { badge.textContent = data.unread > 99 ? '99+' : data.unread; badge.style.display = ''; }
    }).catch(function () {});
}());
</script>

</body>
</html>
