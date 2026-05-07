<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-layout-sidebar"></i></div>
        <div>
            <div class="ph-title">Menu Mapping</div>
            <div class="ph-subtitle">Konfigurasi menu navigasi per role</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<!-- Alert area -->
<div id="alertArea"></div>

<!-- Role Tabs -->
<ul class="nav nav-tabs mb-4" id="roleTabs" role="tablist">
    <?php
    $roleLabels = [
        'user'        => 'User',
        'admin'       => 'Admin',
        'super_admin' => 'Super Admin',
    ];
    ?>
    <?php foreach ($roles as $r): ?>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= $activeRole === $r ? 'active' : '' ?>"
               href="<?= base_url('admin/menu-mapping?role=' . $r) ?>">
                <?= esc($roleLabels[$r] ?? ucfirst($r)) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="row g-4">
    <!-- Left: Menu Editor -->
    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-ul me-2 text-primary"></i>
                    Menu untuk Role: <span class="text-primary"><?= esc($roleLabels[$activeRole] ?? $activeRole) ?></span>
                </h6>
                <button id="btnSave" class="btn btn-primary btn-sm">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
            </div>
            <div class="card-body p-0">
                <?php
                // Pisahkan top-level dan children
                $topItems   = [];
                $childItems = [];
                foreach ($menuItems as $item) {
                    if (empty($item['parent_key'])) {
                        $topItems[] = $item;
                    } else {
                        $childItems[$item['parent_key']][] = $item;
                    }
                }
                // Urutkan top-level berdasarkan urutan
                usort($topItems, fn($a, $b) => (int)$a['urutan'] <=> (int)$b['urutan']);
                ?>

                <?php if (empty($topItems)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Belum ada menu untuk role ini.
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush" id="sortableMenuList">
                        <?php foreach ($topItems as $menu): ?>
                            <?php
                            $hasChildren = ! empty($childItems[$menu['menu_key']]);
                            if ($hasChildren) {
                                usort($childItems[$menu['menu_key']], fn($a, $b) => (int)$a['urutan'] <=> (int)$b['urutan']);
                            }
                            ?>
                            <!-- Top-level item -->
                            <li class="list-group-item px-3 py-2 menu-item"
                                data-id="<?= (int)$menu['id'] ?>"
                                data-urutan="<?= (int)$menu['urutan'] ?>"
                                data-visible="<?= (int)$menu['is_visible'] ?>">
                                <div class="d-flex align-items-center gap-3">
                                    <!-- Drag handle -->
                                    <span class="drag-handle text-muted" style="cursor:grab" title="Seret untuk mengubah urutan">
                                        <i class="bi bi-grip-vertical fs-5"></i>
                                    </span>

                                    <!-- Icon + Label -->
                                    <span class="d-flex align-items-center gap-2 flex-grow-1">
                                        <i class="bi <?= esc($menu['icon']) ?> text-secondary"></i>
                                        <span class="fw-medium"><?= esc($menu['label']) ?></span>
                                        <?php if ($hasChildren): ?>
                                            <span class="badge bg-light text-secondary border ms-1" style="font-size:0.7rem">
                                                <?= count($childItems[$menu['menu_key']]) ?> sub-menu
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($menu['url'] && $menu['url'] !== '#'): ?>
                                            <small class="text-muted ms-1"><?= esc($menu['url']) ?></small>
                                        <?php endif; ?>
                                    </span>

                                    <!-- Toggle visibility -->
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input toggle-visible"
                                               type="checkbox"
                                               role="switch"
                                               id="vis_<?= (int)$menu['id'] ?>"
                                               <?= $menu['is_visible'] ? 'checked' : '' ?>
                                               title="Tampilkan/sembunyikan menu">
                                        <label class="form-check-label visually-hidden" for="vis_<?= (int)$menu['id'] ?>">
                                            Visibilitas
                                        </label>
                                    </div>
                                </div>

                                <!-- Sub-menu items (indented, also sortable) -->
                                <?php if ($hasChildren): ?>
                                    <ul class="list-group list-group-flush mt-2 ms-4 submenu-list"
                                        data-parent-key="<?= esc($menu['menu_key']) ?>">
                                        <?php foreach ($childItems[$menu['menu_key']] as $child): ?>
                                            <li class="list-group-item px-2 py-1 menu-item border-start border-2 border-light"
                                                data-id="<?= (int)$child['id'] ?>"
                                                data-urutan="<?= (int)$child['urutan'] ?>"
                                                data-visible="<?= (int)$child['is_visible'] ?>">
                                                <div class="d-flex align-items-center gap-3">
                                                    <!-- Drag handle -->
                                                    <span class="drag-handle text-muted" style="cursor:grab" title="Seret untuk mengubah urutan">
                                                        <i class="bi bi-grip-vertical"></i>
                                                    </span>

                                                    <!-- Icon + Label -->
                                                    <span class="d-flex align-items-center gap-2 flex-grow-1">
                                                        <i class="bi <?= esc($child['icon']) ?> text-secondary" style="font-size:0.85rem"></i>
                                                        <span style="font-size:0.9rem"><?= esc($child['label']) ?></span>
                                                        <small class="text-muted"><?= esc($child['url']) ?></small>
                                                    </span>

                                                    <!-- Toggle visibility -->
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-visible"
                                                               type="checkbox"
                                                               role="switch"
                                                               id="vis_<?= (int)$child['id'] ?>"
                                                               <?= $child['is_visible'] ? 'checked' : '' ?>
                                                               title="Tampilkan/sembunyikan menu">
                                                        <label class="form-check-label visually-hidden" for="vis_<?= (int)$child['id'] ?>">
                                                            Visibilitas
                                                        </label>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white border-top py-2 text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                Seret item untuk mengubah urutan. Toggle switch untuk mengatur visibilitas.
                Perubahan berlaku langsung setelah disimpan tanpa perlu restart server.
            </div>
        </div>
    </div>

    <!-- Right: Preview Panel -->
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-eye me-2 text-success"></i>
                    Preview Sidebar
                </h6>
                <button id="btnPreview" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh Preview
                </button>
            </div>
            <div class="card-body p-0">
                <!-- Sidebar mockup -->
                <div id="previewContainer"
                     style="background:#1e293b; min-height:400px; border-radius:0 0 0.375rem 0.375rem; overflow:hidden;">
                    <div style="padding:1rem 1.25rem; background:#0f172a; color:#fff; font-weight:700; font-size:0.9rem; border-bottom:1px solid rgba(255,255,255,0.08);">
                        <i class="bi bi-mortarboard-fill me-2"></i> SiapASN Simulation Center
                    </div>
                    <ul id="previewMenuList" class="list-unstyled mb-0 mt-1" style="padding:0.25rem 0;">
                        <li class="text-center text-secondary py-4" style="font-size:0.85rem">
                            <i class="bi bi-arrow-clockwise d-block mb-1 fs-5"></i>
                            Klik "Refresh Preview" untuk melihat tampilan menu
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-footer bg-white border-top py-2 text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                Preview menampilkan kondisi menu saat ini (sebelum disimpan) berdasarkan toggle di sebelah kiri.
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const activeRole = <?= json_encode($activeRole) ?>;
    const saveUrl    = <?= json_encode(base_url('admin/menu-mapping/save')) ?>;
    const previewUrl = <?= json_encode(base_url('admin/menu-mapping/preview')) ?>;
    const csrfName   = <?= json_encode(csrf_token()) ?>;
    const csrfHash   = <?= json_encode(csrf_hash()) ?>;

    // -------------------------------------------------------
    // SortableJS — top-level list
    // -------------------------------------------------------
    const mainList = document.getElementById('sortableMenuList');
    if (mainList) {
        Sortable.create(mainList, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'bg-light',
        });
    }

    // SortableJS — sub-menu lists
    document.querySelectorAll('.submenu-list').forEach(function (subList) {
        Sortable.create(subList, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'bg-light',
        });
    });

    // -------------------------------------------------------
    // Collect current state from DOM
    // -------------------------------------------------------
    function collectItems() {
        const items = [];
        let topUrutan = 1;

        document.querySelectorAll('#sortableMenuList > .menu-item').forEach(function (li) {
            const id        = parseInt(li.dataset.id, 10);
            const toggle    = li.querySelector('.toggle-visible');
            const isVisible = toggle && toggle.checked ? 1 : 0;

            items.push({ id: id, is_visible: isVisible, urutan: topUrutan++ });

            // Children
            let childUrutan = 1;
            li.querySelectorAll('.submenu-list > .menu-item').forEach(function (childLi) {
                const childId      = parseInt(childLi.dataset.id, 10);
                const childToggle  = childLi.querySelector('.toggle-visible');
                const childVisible = childToggle && childToggle.checked ? 1 : 0;

                items.push({ id: childId, is_visible: childVisible, urutan: childUrutan++ });
            });
        });

        return items;
    }

    // -------------------------------------------------------
    // Save button
    // -------------------------------------------------------
    document.getElementById('btnSave').addEventListener('click', function () {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        const items = collectItems();

        fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfHash,
            },
            body: JSON.stringify({ items: items, [csrfName]: csrfHash }),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            showAlert(data.status ? 'success' : 'danger', data.message || 'Terjadi kesalahan.');
        })
        .catch(function () {
            showAlert('danger', 'Gagal menghubungi server.');
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Perubahan';
        });
    });

    // -------------------------------------------------------
    // Preview button — build preview from current DOM state
    // -------------------------------------------------------
    document.getElementById('btnPreview').addEventListener('click', function () {
        buildPreviewFromCurrentState();
    });

    function buildPreviewFromCurrentState() {
        const previewList = document.getElementById('previewMenuList');
        previewList.innerHTML = '';

        document.querySelectorAll('#sortableMenuList > .menu-item').forEach(function (li) {
            const toggle    = li.querySelector(':scope > div > .form-check > .toggle-visible');
            const isVisible = toggle && toggle.checked;

            if (! isVisible) return; // skip hidden items

            const labelEl = li.querySelector(':scope > div > span > span.fw-medium');
            const iconEl  = li.querySelector(':scope > div > span > i.bi');
            const label   = labelEl ? labelEl.textContent.trim() : '—';
            const iconClass = iconEl ? (iconEl.className.match(/bi-[\w-]+/) || ['bi-circle'])[0] : 'bi-circle';

            // Check for visible children
            const childItems = [];
            li.querySelectorAll('.submenu-list > .menu-item').forEach(function (childLi) {
                const childToggle  = childLi.querySelector('.toggle-visible');
                const childVisible = childToggle && childToggle.checked;
                if (! childVisible) return;

                const childLabelEl  = childLi.querySelector('span > span');
                const childIconEl   = childLi.querySelector('span > i.bi');
                const childLabel    = childLabelEl ? childLabelEl.textContent.trim() : '—';
                const childIconCls  = childIconEl ? (childIconEl.className.match(/bi-[\w-]+/) || ['bi-circle'])[0] : 'bi-circle';
                childItems.push({ label: childLabel, icon: childIconCls });
            });

            const hasChildren = childItems.length > 0;

            // Build top-level item
            const topLi = document.createElement('li');
            topLi.style.cssText = 'list-style:none;';

            const topLink = document.createElement('a');
            topLink.href  = '#';
            topLink.style.cssText = 'display:flex;align-items:center;gap:0.5rem;padding:0.5rem 1.25rem;color:#cbd5e1;font-size:0.85rem;text-decoration:none;transition:background 0.15s;';
            topLink.innerHTML = '<i class="bi ' + escHtml(iconClass) + '" style="width:1.1rem;text-align:center;"></i> ' + escHtml(label);

            topLink.addEventListener('mouseenter', function () { this.style.background = 'rgba(255,255,255,0.08)'; this.style.color = '#fff'; });
            topLink.addEventListener('mouseleave', function () { this.style.background = ''; this.style.color = '#cbd5e1'; });

            topLi.appendChild(topLink);

            if (hasChildren) {
                // Add collapse indicator
                topLink.innerHTML += ' <i class="bi bi-chevron-down" style="margin-left:auto;font-size:0.7rem;"></i>';

                const subUl = document.createElement('ul');
                subUl.style.cssText = 'list-style:none;padding:0;margin:0;background:rgba(0,0,0,0.15);';

                childItems.forEach(function (child) {
                    const childLi   = document.createElement('li');
                    const childLink = document.createElement('a');
                    childLink.href  = '#';
                    childLink.style.cssText = 'display:flex;align-items:center;gap:0.5rem;padding:0.4rem 1.25rem 0.4rem 2.75rem;color:#94a3b8;font-size:0.82rem;text-decoration:none;transition:background 0.15s;';
                    childLink.innerHTML = '<i class="bi ' + escHtml(child.icon) + '" style="width:1rem;text-align:center;font-size:0.85rem;"></i> ' + escHtml(child.label);

                    childLink.addEventListener('mouseenter', function () { this.style.background = 'rgba(255,255,255,0.06)'; this.style.color = '#e2e8f0'; });
                    childLink.addEventListener('mouseleave', function () { this.style.background = ''; this.style.color = '#94a3b8'; });

                    childLi.appendChild(childLink);
                    subUl.appendChild(childLi);
                });

                topLi.appendChild(subUl);
            }

            previewList.appendChild(topLi);
        });

        if (previewList.children.length === 0) {
            previewList.innerHTML = '<li class="text-center py-4" style="color:#64748b;font-size:0.85rem;"><i class="bi bi-eye-slash d-block mb-1 fs-5"></i>Semua menu disembunyikan</li>';
        }
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------
    function showAlert(type, message) {
        const alertArea = document.getElementById('alertArea');
        alertArea.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">'
            + '<i class="bi bi-' + (type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill') + ' me-1"></i>'
            + escHtml(message)
            + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>'
            + '</div>';

        // Auto-dismiss after 4 seconds
        setTimeout(function () {
            const alert = alertArea.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(function () { alertArea.innerHTML = ''; }, 300);
            }
        }, 4000);
    }

    function escHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // Auto-build preview on page load
    buildPreviewFromCurrentState();

}());
</script>

<?= $this->endSection() ?>
