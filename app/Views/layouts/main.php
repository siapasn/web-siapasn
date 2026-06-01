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
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

    <style>
        :root {
            --sa-primary:    #1a3a5c;
            --sa-primary-dk: #0f2744;
            --sa-accent:     #f5a623;
            --sa-sidebar-bg: #0f2744;
            --sa-body-bg:    #f0f4f8;
            --sa-border:     #dde3ea;
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

       /* ---- Content ---- */
        .content-area {
            flex: 1;
            padding: 1.5rem;
        }

        /* Mobile Version */
        @media (max-width: 768px) {
            .content-area {
                padding: 0.5rem;
            }
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

        /* Button di page header */
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
        /* Tombol solid di header (lebih jelas) */
        .page-header-banner .btn-header {
            background: var(--sa-accent);
            color: var(--sa-primary-dk);
            font-weight: 600;
            font-size: .8rem;
            border-radius: .45rem;
            padding: .35rem .85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border: none;
            transition: background .15s;
            white-space: nowrap;
        }
        .page-header-banner .btn-header:hover {
            background: var(--sa-accent-dk);
            color: var(--sa-primary-dk);
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
                    <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width:340px;max-height:400px;overflow-y:auto" aria-labelledby="notifDropdown">
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
<!-- jQuery + Select2 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<?= $this->renderSection('scripts') ?>

<script>
    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });
    }

    // ── Anti Copy & Anti DevTools ──
    (function () {
        // Disable right-click context menu
        document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
        });

        // Disable text selection via CSS (exclude form inputs)
        document.body.style.userSelect = 'none';
        document.body.style.webkitUserSelect = 'none';
        document.body.style.msUserSelect = 'none';

        // Allow selection in form inputs
        var style = document.createElement('style');
        style.textContent = 'input, textarea, select, [contenteditable] { user-select: text !important; -webkit-user-select: text !important; }';
        document.head.appendChild(style);

        // Disable keyboard shortcuts
        document.addEventListener('keydown', function (e) {
            var tag = (e.target.tagName || '').toLowerCase();
            var isInput = (tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable);

            // Ctrl+U (View Source)
            if (e.ctrlKey && e.key === 'u') { e.preventDefault(); return false; }
            // Ctrl+S (Save)
            if (e.ctrlKey && e.key === 's') { e.preventDefault(); return false; }
            // Ctrl+Shift+I (DevTools)
            if (e.ctrlKey && e.shiftKey && e.key === 'I') { e.preventDefault(); return false; }
            // Ctrl+Shift+J (Console)
            if (e.ctrlKey && e.shiftKey && e.key === 'J') { e.preventDefault(); return false; }
            // Ctrl+Shift+C (Inspect Element)
            if (e.ctrlKey && e.shiftKey && e.key === 'C') { e.preventDefault(); return false; }
            // Ctrl+Shift+S (Screenshot Browser)
            if (e.ctrlKey && e.shiftKey && e.key === 'S') { e.preventDefault(); return false; }
            // F12 (DevTools)
            if (e.key === 'F12') { e.preventDefault(); return false; }
            // Ctrl+P (Print)
            if (e.ctrlKey && e.key === 'p') { e.preventDefault(); return false; }

            // Ctrl+A, Ctrl+C — block hanya di luar form input
            if (!isInput) {
                if (e.ctrlKey && e.key === 'a') { e.preventDefault(); return false; }
                if (e.ctrlKey && e.key === 'c') { e.preventDefault(); return false; }
            }
        });

        // Disable copy event
        document.addEventListener('copy', function (e) {
            e.preventDefault();
        });

        // Disable cut event
        document.addEventListener('cut', function (e) {
            e.preventDefault();
        });

        // Disable drag
        document.addEventListener('dragstart', function (e) {
            e.preventDefault();
        });
    }());
</script>

<script>
// ── Notifikasi ──
(function () {
    const badge    = document.getElementById('notif-badge');
    const list     = document.getElementById('notifList');
    const markBtn  = document.getElementById('markAllReadBtn');
    const dropdown = document.getElementById('notifDropdown');

    if (!badge || !list) return;

    const role = '<?= session()->get('role') ?? 'user' ?>';
    const baseNotifUrl = role === 'admin' || role === 'super_admin'
        ? '<?= base_url('admin/notifikasi') ?>'
        : '<?= base_url('user/notifikasi') ?>';

    function loadNotif() {
        fetch(baseNotifUrl)
            .then(r => r.json())
            .then(data => {
                // Update badge
                if (data.unread > 0) {
                    badge.textContent = data.unread > 99 ? '99+' : data.unread;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }

                // Render list
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
        switch (tipe) {
            case 'transaksi': return 'receipt';
            case 'request_formasi': return 'inbox';
            case 'event': return 'calendar-event';
            case 'produk': return 'box-seam';
            default: return 'bell';
        }
    }

    function getColor(tipe) {
        switch (tipe) {
            case 'transaksi': return '#198754';
            case 'request_formasi': return '#f5a623';
            case 'event': return '#0dcaf0';
            default: return '#6c757d';
        }
    }

    function escHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Load saat dropdown dibuka
    dropdown.addEventListener('show.bs.dropdown', loadNotif);

    // Mark all read
    if (markBtn) {
        markBtn.addEventListener('click', function () {
            fetch(baseNotifUrl + '/mark-all-read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: '<?= csrf_token() ?>=<?= csrf_hash() ?>',
            }).then(function () {
                badge.style.display = 'none';
                list.querySelectorAll('.dropdown-item').forEach(function (el) {
                    el.style.background = '';
                });
            });
        });
    }

    // Initial badge count
    fetch(baseNotifUrl)
        .then(r => r.json())
        .then(data => {
            if (data.unread > 0) {
                badge.textContent = data.unread > 99 ? '99+' : data.unread;
                badge.style.display = '';
            }
        }).catch(function () {});
}());
</script>

</body>
</html>
