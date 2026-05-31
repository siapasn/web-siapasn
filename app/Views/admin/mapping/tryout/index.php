<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-diagram-2"></i></div>
        <div>
            <div class="ph-title">Mapping Tryout ke Produk</div>
            <div class="ph-subtitle">Kelola tryout yang terdapat dalam setiap produk</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Produk Selector -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <label class="form-label mb-0 text-nowrap fw-medium">Pilih Produk:</label>
            <div style="min-width:320px; max-width:480px; flex:1">
                <select id="produk_select" class="form-select form-select-sm" style="width:100%">
                    <option value="0">— Pilih Produk —</option>
                    <?php foreach ($produks as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $produkId == $p['id'] ? 'selected' : '' ?>>
                            <?= esc($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<?php if ($produkId > 0 && $selectedProduk): ?>

<div class="row g-3">

    <!-- Panel Kiri: Tryout yang sudah di-mapping -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-ol me-2 text-primary"></i>
                    Tryout dalam Produk
                    <span class="badge bg-primary ms-1"><?= $totalTryout ?></span>
                </h6>
                <small class="text-muted">Drag untuk mengatur urutan</small>
            </div>
            <div class="card-body p-0">
                <?php if (! empty($mappedTryouts)): ?>
                    <ul id="sortable-tryout" class="list-group list-group-flush" style="min-height:60px">
                        <?php foreach ($mappedTryouts as $mt): ?>
                            <li class="list-group-item d-flex align-items-center gap-2 py-2 px-3"
                                data-id="<?= $mt['id'] ?>">
                                <!-- Drag handle -->
                                <span class="text-muted drag-handle" style="cursor:grab" title="Seret untuk mengatur urutan">
                                    <i class="bi bi-grip-vertical fs-5"></i>
                                </span>
                                <!-- Urutan badge -->
                                <span class="badge bg-secondary rounded-pill urutan-badge" style="min-width:28px">
                                    <?= $mt['urutan'] ?>
                                </span>
                                <!-- Nama tryout -->
                                <span class="flex-grow-1 small">
                                    <span class="fw-medium"><?= esc($mt['nama_tryout']) ?></span>
                                    <br>
                                    <span class="text-muted" style="font-size:0.75rem">
                                        <i class="bi bi-clock me-1"></i><?= $mt['durasi'] ?> menit
                                        &nbsp;·&nbsp;
                                        <i class="bi bi-question-circle me-1"></i><?= $mt['jumlah_soal'] ?> soal
                                    </span>
                                </span>
                                <!-- Hapus -->
                                <form method="post"
                                      action="<?= base_url("admin/mapping/tryout/{$mt['id']}/delete") ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus tryout ini dari produk?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Belum ada tryout dalam produk ini.<br>
                        <small>Tambahkan tryout dari panel kanan.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Panel Kanan: Tryout tersedia -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-collection me-2 text-success"></i>
                    Tryout Tersedia
                </h6>
                <?php if (! empty($availableTryouts)): ?>
                <div class="mt-2">
                    <input type="text" id="searchTryout" class="form-control form-control-sm"
                           placeholder="Cari nama tryout..." autocomplete="off">
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (! empty($availableTryouts)): ?>
                    <div class="table-responsive" style="max-height:520px;overflow-y:auto">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3">Nama Tryout</th>
                                    <th class="text-center">Durasi</th>
                                    <th class="text-center">Soal</th>
                                    <th class="text-center pe-3" style="width:90px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($availableTryouts as $t): ?>
                                    <tr>
                                        <td class="ps-3 fw-medium"><?= esc($t['nama']) ?></td>
                                        <td class="text-center text-muted"><?= $t['durasi'] ?> mnt</td>
                                        <td class="text-center text-muted"><?= $t['jumlah_soal'] ?></td>
                                        <td class="text-center pe-3">
                                            <button type="button"
                                                    class="btn btn-sm btn-success py-0 px-2 btn-tambah-tryout"
                                                    data-tryout-id="<?= $t['id'] ?>"
                                                    data-produk-id="<?= $produkId ?>"
                                                    title="Tambah ke produk">
                                                <i class="bi bi-plus-lg"></i> Tambah
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
                        Semua tryout sudah di-mapping<br>atau tidak ada tryout yang tersedia.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- /.row -->

<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        Pilih produk di atas untuk mulai mengatur mapping tryout.
    </div>
<?php endif; ?>

<!-- Toast notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="toastMapping" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive">
        <div class="d-flex">
            <div class="toast-body" id="toastMappingBody">Berhasil.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
(function () {
    const produkId  = <?= (int) $produkId ?>;
    const csrfName  = '<?= csrf_token() ?>';
    const csrfHash  = '<?= csrf_hash() ?>';
    const storeUrl  = '<?= base_url('admin/mapping/tryout/store') ?>';
    const urutanUrl = '<?= base_url('admin/mapping/tryout/urutan') ?>';

    // ── Select2: Pilih Produk ─────────────────────────────────────────────────
    $('#produk_select').select2({
        theme: 'bootstrap-5',
        placeholder: '— Pilih Produk —',
        allowClear: true,
        width: '100%',
    });

    $('#produk_select').on('change', function () {
        const val = this.value;
        if (val && val !== '0') {
            window.location.href = '<?= base_url('admin/mapping/tryout') ?>/' + val;
        } else {
            window.location.href = '<?= base_url('admin/mapping/tryout') ?>';
        }
    });

    // ── Toast helper ──────────────────────────────────────────────────────────
    function showToast(message, success) {
        const toastEl   = document.getElementById('toastMapping');
        const toastBody = document.getElementById('toastMappingBody');
        toastEl.classList.remove('bg-success', 'bg-danger');
        toastEl.classList.add(success ? 'bg-success' : 'bg-danger');
        toastBody.textContent = message;
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
    }

    // ── Tambah tryout via AJAX ────────────────────────────────────────────────
    document.querySelectorAll('.btn-tambah-tryout').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tryoutId = this.dataset.tryoutId;

            fetch(storeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    [csrfName]: csrfHash,
                    produk_id: produkId,
                    tryout_id: tryoutId,
                }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    showToast('Tryout berhasil ditambahkan.', true);
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(data.message || 'Gagal menambahkan tryout.', false);
                }
            })
            .catch(() => showToast('Terjadi kesalahan jaringan.', false));
        });
    });

    // ── SortableJS drag-and-drop ──────────────────────────────────────────────
    const sortableEl = document.getElementById('sortable-tryout');
    if (sortableEl) {
        Sortable.create(sortableEl, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function () {
                const items = [];
                sortableEl.querySelectorAll('li[data-id]').forEach(function (li, index) {
                    const newUrutan = index + 1;
                    const badge = li.querySelector('.urutan-badge');
                    if (badge) badge.textContent = newUrutan;
                    items.push({ id: li.dataset.id, urutan: newUrutan });
                });

                fetch(urutanUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(
                        Object.assign(
                            { [csrfName]: csrfHash },
                            ...items.map((item, i) => ({
                                [`items[${i}][id]`]:     item.id,
                                [`items[${i}][urutan]`]: item.urutan,
                            }))
                        )
                    ),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status) {
                        showToast('Urutan berhasil disimpan.', true);
                    } else {
                        showToast('Gagal menyimpan urutan.', false);
                    }
                })
                .catch(() => showToast('Terjadi kesalahan jaringan.', false));
            },
        });
    }
    // ── Pencarian Tryout Tersedia ─────────────────────────────────────────────
    const searchInput = document.getElementById('searchTryout');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.btn-tambah-tryout').forEach(function (btn) {
                const row = btn.closest('tr');
                if (!row) return;
                const nama = row.querySelector('td').textContent.trim().toLowerCase();
                row.style.display = (!q || nama.includes(q)) ? '' : 'none';
            });
        });
    }
}());
</script>
<?= $this->endSection() ?>
