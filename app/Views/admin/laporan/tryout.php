<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-bar-chart-line"></i></div>
        <div>
            <div class="ph-title">Laporan Tryout</div>
            <div class="ph-subtitle">Rekap hasil dan partisipasi tryout</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Filter Form -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= base_url('admin/laporan/tryout') ?>" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-medium">Tryout</label>
                <select name="tryout_id" class="form-select form-select-sm">
                    <option value="">Semua Tryout</option>
                    <?php foreach ($tryouts as $t): ?>
                        <option value="<?= $t['id'] ?>"
                            <?= (string) ($tryoutId ?? '') === (string) $t['id'] ? 'selected' : '' ?>>
                            <?= esc($t['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Statistik Tryout -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelTryout" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Nama Tryout</th>
                        <th class="text-center">Total Sesi</th>
                        <th class="text-end">Rata-rata Skor</th>
                        <th class="text-end">Skor Tertinggi</th>
                        <th class="text-end pe-3">Skor Terendah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($statistik)): ?>
                        <?php foreach ($statistik as $i => $s): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                                <td class="fw-medium"><?= esc($s['tryout_nama']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill">
                                        <?= (int) $s['total_sesi'] ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?= number_format((float) $s['rata_rata_skor'], 2, ',', '.') ?>
                                </td>
                                <td class="text-end text-success fw-medium">
                                    <?= number_format((float) $s['skor_tertinggi'], 2, ',', '.') ?>
                                </td>
                                <td class="text-end text-danger fw-medium pe-3">
                                    <?= number_format((float) $s['skor_terendah'], 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Belum ada data statistik tryout
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#tabelTryout').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        pageLength: 25,
        ordering: true,
        columnDefs: [
            { orderable: false, targets: [0] }
        ]
    });
});
</script>

<?= $this->endSection() ?>
