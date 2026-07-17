<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-ticket-perforated"></i></div>
        <div>
            <div class="ph-title">Voucher</div>
            <div class="ph-subtitle">Kelola kode voucher diskon</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/voucher/create') ?>" class="ph-action">
        <i class="bi bi-plus-lg"></i> Tambah Voucher
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Table -->
<?php if (!empty($vouchers)): ?>
<div class="card border-0 shadow-sm">
    <style>
        #tabelVoucher_wrapper .dataTables_length label,
        #tabelVoucher_wrapper .dataTables_filter label { margin-bottom:0; font-size:.875rem; }
        #tabelVoucher_wrapper .dataTables_filter input { margin-left:.4rem; border-radius:.375rem; border:1px solid #dee2e6; padding:.25rem .5rem; font-size:.875rem; }
        #tabelVoucher_wrapper .dataTables_info, #tabelVoucher_wrapper .dataTables_paginate { font-size:.875rem; }
        #tabelVoucher_wrapper .paginate_button { border-radius:.375rem !important; }
    </style>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelVoucher" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Kode</th>
                        <th>Jenis</th>
                        <th class="text-end">Nilai</th>
                        <th class="text-center">Batas / Digunakan</th>
                        <th>Expired</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3" style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vouchers as $i => $v): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td class="fw-medium font-monospace"><?= esc($v['kode']) ?></td>
                            <td>
                                <?php if ($v['jenis_diskon'] === 'persentase'): ?>
                                    <span class="badge bg-info text-dark">Persentase</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Nominal</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($v['jenis_diskon'] === 'persentase'): ?>
                                    <?= esc($v['nilai_diskon']) ?>%
                                <?php else: ?>
                                    Rp <?= number_format((float) $v['nilai_diskon'], 0, ',', '.') ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($v['batas_penggunaan'] !== null): ?>
                                    <?= (int) $v['jumlah_digunakan'] ?> / <?= (int) $v['batas_penggunaan'] ?>
                                <?php else: ?>
                                    <?= (int) $v['jumlah_digunakan'] ?> / &infin;
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $v['expired_at']
                                    ? date('d/m/Y H:i', strtotime($v['expired_at']))
                                    : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td class="text-center">
                                <?php if ($v['is_active']): ?>
                                    <span class="badge bg-success rounded-pill">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center pe-3">
                                <a href="<?= base_url("admin/voucher/{$v['id']}/edit") ?>"
                                   class="btn btn-sm btn-outline-primary py-0 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="post"
                                      action="<?= base_url("admin/voucher/{$v['id']}/delete") ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus voucher ini?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-ticket-perforated text-muted" style="font-size:2.5rem"></i>
        <div class="mt-3 fw-semibold text-muted">Belum ada voucher</div>
        <div class="text-muted small mt-1">Mulai dengan <a href="<?= base_url('admin/voucher/create') ?>">menambah data baru</a></div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (!empty($vouchers)): ?>
<script>
$(document).ready(function () {
    $('#tabelVoucher').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        dom: '<"px-3 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2"lf>rt<"px-3 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2"ip>',
        pageLength: 25,
        ordering: true,
        columnDefs: [
            { orderable: false, targets: [0, 7] }
        ]
    });
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
