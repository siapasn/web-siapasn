<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-briefcase"></i></div>
    <div>
        <div class="ph-title">Daftar Formasi SKB</div>
        <div class="ph-subtitle">Ketersediaan paket tryout per formasi jabatan</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Statistik -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-list-check me-1"></i>Total Formasi</div>
            <div class="fs-3 fw-bold" style="color:#1a3a5c"><?= $totalFormasi ?></div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-check-circle me-1"></i>Tryout Tersedia</div>
            <div class="fs-3 fw-bold text-success"><?= $totalTersedia ?></div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-clock me-1"></i>Segera Hadir</div>
            <div class="fs-3 fw-bold" style="color:#f5a623"><?= $totalBelumAda ?></div>
        </div>
    </div>
</div>

<!-- Pencarian cepat -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="input-group input-group-sm" style="max-width:360px">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" id="cariFormasi" class="form-control border-start-0 ps-0"
                   placeholder="Cari nama formasi..." autocomplete="off">
        </div>
    </div>
</div>

<?php if (empty($formasiByKategori)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-briefcase fs-1 d-block mb-3"></i>
        <p class="mb-0">Belum ada data formasi.</p>
    </div>
</div>
<?php else: ?>

<?php
// Nomor baris global
$globalNo = 1;
?>

<?php foreach ($formasiByKategori as $kat): ?>
<div class="card border-0 shadow-sm mb-4 formasi-kategori-card">
    <!-- Header Kategori -->
    <div class="card-header border-0 d-flex align-items-center gap-2 py-3"
         style="background:linear-gradient(135deg,#1a3a5c,#2d6a9f);border-radius:.75rem .75rem 0 0 !important">
        <i class="<?= esc($kat['kategori_icon']) ?> text-white fs-5"></i>
        <span class="fw-bold text-white"><?= esc($kat['kategori_nama']) ?></span>
        <span class="ms-auto badge bg-white bg-opacity-25 text-white"
              style="font-size:.7rem"><?= count($kat['items']) ?> formasi</span>
    </div>

    <!-- Tabel -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 formasi-table">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 text-center" style="width:52px">No</th>
                        <th>Nama Formasi</th>
                        <th class="text-center" style="width:160px">Tryout SKB</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kat['items'] as $f): ?>
                    <tr class="formasi-row">
                        <td class="ps-3 text-center text-muted small"><?= $globalNo++ ?></td>
                        <td>
                            <span class="fw-medium formasi-nama"><?= esc($f['nama']) ?></span>
                            <?php if (! empty($f['deskripsi'])): ?>
                                <div class="text-muted" style="font-size:.72rem"><?= esc($f['deskripsi']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($f['has_produk']): ?>
                                <!-- Produk tersedia -->
                                <a href="<?= base_url('user/produk/' . $f['produk']['produk_slug']) ?>"
                                   class="d-inline-flex align-items-center gap-1 text-success fw-semibold text-decoration-none"
                                   style="font-size:.82rem" title="Lihat paket: <?= esc($f['produk']['produk_nama']) ?>">
                                    <i class="bi bi-check-circle-fill"></i> Tersedia
                                </a>
                            <?php elseif ($f['has_requested']): ?>
                                <!-- Sudah request -->
                                <span class="badge bg-warning text-dark fw-semibold"
                                      style="font-size:.72rem">
                                    <i class="bi bi-clock me-1"></i>Requested
                                </span>
                            <?php else: ?>
                                <!-- Belum ada produk -->
                                <button type="button"
                                        class="btn btn-sm btn-request-tryout fw-semibold"
                                        style="font-size:.75rem;background:#1a3a5c;color:#fff;border:none;padding:4px 10px;border-radius:.4rem"
                                        data-formasi-id="<?= $f['id'] ?>"
                                        data-formasi-nama="<?= esc($f['nama'], 'attr') ?>">
                                    <i class="bi bi-send me-1"></i>Request Tryout
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<!-- ── Modal Request Tryout ── -->
<div class="modal fade" id="modalRequestFormasi" tabindex="-1" aria-labelledby="modalRequestFormasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post" action="<?= base_url('user/request-formasi') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="formasi_id" id="requestFormasiId">

                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" id="modalRequestFormasiLabel">
                        <i class="bi bi-send me-2 text-primary"></i>Request Tryout SKB
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Anda akan request pembuatan paket tryout untuk formasi:
                        <div class="fw-bold mt-1" id="requestFormasiNama">—</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Pesan / Catatan <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea name="pesan" class="form-control form-control-sm" rows="3"
                                  placeholder="Contoh: Saya butuh tryout untuk persiapan SKB formasi ini..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                        <i class="bi bi-send me-1"></i>Kirim Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    // ── Trigger modal dari tombol Request Tryout ──
    document.querySelectorAll('.btn-request-tryout').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const formasiId   = this.dataset.formasiId;
            const formasiNama = this.dataset.formasiNama;

            document.getElementById('requestFormasiId').value        = formasiId;
            document.getElementById('requestFormasiNama').textContent = formasiNama;

            new bootstrap.Modal(document.getElementById('modalRequestFormasi')).show();
        });
    });

    // ── Live search formasi ──
    const searchInput = document.getElementById('cariFormasi');
    if (! searchInput) return;

    searchInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();

        document.querySelectorAll('.formasi-kategori-card').forEach(function (card) {
            const rows   = card.querySelectorAll('.formasi-row');
            let visible  = 0;

            rows.forEach(function (row) {
                const nama = row.querySelector('.formasi-nama')?.textContent.toLowerCase() || '';
                const show = q === '' || nama.includes(q);
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            // Sembunyikan card kategori jika tidak ada baris yang cocok
            card.style.display = visible === 0 && q !== '' ? 'none' : '';
        });
    });
}());
</script>
<?= $this->endSection() ?>
