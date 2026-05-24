<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-diagram-3"></i></div>
        <div>
            <div class="ph-title">Mapping Soal ke Tryout</div>
            <div class="ph-subtitle">Kelola soal yang terdapat dalam setiap sesi tryout</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Tryout Selector -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <label class="form-label mb-0 text-nowrap fw-medium">Pilih Tryout:</label>
            <div style="min-width:320px; max-width:480px; flex:1">
                <select id="tryout_select" class="form-select form-select-sm" style="width:100%">
                    <option value="0">— Pilih Tryout —</option>
                    <?php foreach ($tryouts as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $tryoutId == $t['id'] ? 'selected' : '' ?>>
                            <?= esc($t['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<?php if ($tryoutId > 0 && $selectedTryout): ?>

<div class="row g-3">

    <!-- Panel Kiri: Soal yang sudah di-mapping -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-ol me-2 text-primary"></i>
                    Soal dalam Tryout
                    <span class="badge bg-primary ms-1"><?= $totalSoal ?></span>
                </h6>
                <small class="text-muted">Drag untuk mengatur urutan</small>
            </div>
            <div class="card-body p-0">
                <?php if (! empty($mappedSoals)): ?>
                    <ul id="sortable-soal" class="list-group list-group-flush" style="min-height:60px">
                        <?php foreach ($mappedSoals as $ms): ?>
                            <li class="list-group-item d-flex align-items-center gap-2 py-2 px-3"
                                data-id="<?= $ms['id'] ?>">
                                <!-- Drag handle -->
                                <span class="text-muted drag-handle" style="cursor:grab" title="Seret untuk mengatur urutan">
                                    <i class="bi bi-grip-vertical fs-5"></i>
                                </span>
                                <!-- Urutan badge -->
                                <span class="badge bg-secondary rounded-pill urutan-badge" style="min-width:28px">
                                    <?= $ms['urutan'] ?>
                                </span>
                                <!-- Pertanyaan -->
                                <span class="flex-grow-1 small" title="<?= esc(strip_tags($ms['pertanyaan'])) ?>">
                                    <?php
                                    $plain = strip_tags($ms['pertanyaan']);
                                    echo esc(mb_strlen($plain) > 70 ? mb_substr($plain, 0, 70) . '…' : $plain);
                                    ?>
                                    <?php if (! empty($ms['nama_kategori'])): ?>
                                        <br><span class="text-muted" style="font-size:0.75rem"><?= esc($ms['nama_kategori']) ?></span>
                                    <?php endif; ?>
                                </span>
                                <!-- Hapus -->
                                <form method="post"
                                      action="<?= base_url("admin/mapping/soal/{$ms['id']}/delete") ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus soal ini dari tryout?')">
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
                        Belum ada soal dalam tryout ini.<br>
                        <small>Tambahkan soal dari panel kanan.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Panel Kanan: Soal tersedia -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-collection me-2 text-success"></i>
                    Soal Tersedia
                </h6>
                <!-- Filter kategori -->
                <form method="get" action="<?= base_url("admin/mapping/soal/{$tryoutId}") ?>"
                      id="formFilterMapping" class="mt-2">
                    <div class="d-flex flex-wrap gap-2 align-items-center">

                        <!-- Kategori — Select2, semua kategori -->
                        <div style="min-width:220px; flex:1">
                            <select id="filter_kategori_id" name="kategori_id"
                                    class="form-select form-select-sm" style="width:100%">
                                <option value="">— Semua Kategori —</option>
                                <?php foreach ($kategoris as $k):
                                    $label = esc($k['nama']);
                                    if (! empty($k['parent_nama'])) {
                                        $label = esc($k['parent_nama']) . ' › ' . $label;
                                    }
                                ?>
                                    <option value="<?= $k['id'] ?>"
                                        <?= (string) $kategoriFilter === (string) $k['id'] ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <?php if ($kategoriFilter !== ''): ?>
                            <a href="<?= base_url("admin/mapping/soal/{$tryoutId}") ?>"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-lg me-1"></i>Reset
                            </a>
                        <?php endif; ?>

                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <?php if (! empty($availableSoals)): ?>
                    <div class="table-responsive" style="max-height:520px;overflow-y:auto">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3">Pertanyaan</th>
                                    <th>Kategori</th>
                                    <th class="text-center pe-3" style="width:80px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($availableSoals as $s): ?>
                                    <tr>
                                        <td class="ps-3" title="<?= esc(strip_tags($s['pertanyaan'])) ?>">
                                            <?php
                                            $plain = strip_tags($s['pertanyaan']);
                                            echo esc(mb_strlen($plain) > 60 ? mb_substr($plain, 0, 60) . '…' : $plain);
                                            ?>
                                        </td>
                                        <td class="text-muted small"><?= esc($s['nama_kategori'] ?? '—') ?></td>
                                        <td class="text-center pe-3">
                                            <button type="button"
                                                    class="btn btn-sm btn-success py-0 px-2 btn-tambah-soal"
                                                    data-soal-id="<?= $s['id'] ?>"
                                                    data-tryout-id="<?= $tryoutId ?>"
                                                    title="Tambah ke tryout">
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
                        Semua soal sudah di-mapping<br>atau tidak ada soal yang tersedia.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- /.row -->

<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        Pilih tryout di atas untuk mulai mengatur mapping soal.
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
    const tryoutId  = <?= (int) $tryoutId ?>;
    const csrfName  = '<?= csrf_token() ?>';
    const csrfHash  = '<?= csrf_hash() ?>';
    const storeUrl  = '<?= base_url('admin/mapping/soal/store') ?>';
    const urutanUrl = '<?= base_url('admin/mapping/soal/urutan') ?>';

    // ── Select2: Pilih Tryout ─────────────────────────────────────────────────
    $('#tryout_select').select2({
        theme: 'bootstrap-5',
        placeholder: '— Pilih Tryout —',
        allowClear: true,
        width: '100%',
    });

    $('#tryout_select').on('change', function () {
        const val = this.value;
        if (val && val !== '0') {
            window.location.href = '<?= base_url('admin/mapping/soal') ?>/' + val;
        } else {
            window.location.href = '<?= base_url('admin/mapping/soal') ?>';
        }
    });

    // ── Select2: Filter Kategori ──────────────────────────────────────────────
    $('#filter_kategori_id').select2({
        theme: 'bootstrap-5',
        placeholder: '— Semua Kategori —',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#formFilterMapping'),
    });

    // Auto-submit saat kategori dipilih
    $('#filter_kategori_id').on('change', function () {
        document.getElementById('formFilterMapping').submit();
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

    // ── Tambah soal via AJAX ──────────────────────────────────────────────────
    document.querySelectorAll('.btn-tambah-soal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const soalId = this.dataset.soalId;

            fetch(storeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    [csrfName]: csrfHash,
                    tryout_id: tryoutId,
                    soal_id:   soalId,
                }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    showToast('Soal berhasil ditambahkan.', true);
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(data.message || 'Gagal menambahkan soal.', false);
                }
            })
            .catch(() => showToast('Terjadi kesalahan jaringan.', false));
        });
    });

    // ── SortableJS drag-and-drop ──────────────────────────────────────────────
    const sortableEl = document.getElementById('sortable-soal');
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
}());
</script>
<?= $this->endSection() ?>
