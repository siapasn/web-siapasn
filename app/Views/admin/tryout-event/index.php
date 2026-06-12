<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-calendar-event"></i></div>
        <div>
            <div class="ph-title">Tryout Event / Nasional</div>
            <div class="ph-subtitle">Kelola event tryout gratis dan kompetitif</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/tryout-event/create') ?>" class="ph-action">
        <i class="bi bi-plus-lg"></i> Buat Event
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (! empty($events)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Nama Event</th>
                        <th>Tryout</th>
                        <th class="text-center">Peserta</th>
                        <th class="text-center">Max Percobaan</th>
                        <th>Pelaksanaan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3" style="width:150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $now = date('Y-m-d H:i:s'); ?>
                    <?php foreach ($events as $i => $e): ?>
                    <?php
                        if ($now < $e['mulai_pelaksanaan']) $status = ['Menunggu', 'bg-warning text-dark'];
                        elseif ($now <= $e['tutup_pelaksanaan']) $status = ['Berlangsung', 'bg-success'];
                        else $status = ['Selesai', 'bg-secondary'];
                    ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td class="fw-medium"><?= esc($e['nama']) ?></td>
                            <td class="small text-muted"><?= esc($e['tryout_nama']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill"><?= (int) $e['total_peserta'] ?></span>
                            </td>
                            <td class="text-center"><?= (int) $e['max_percobaan'] ?>x</td>
                            <td class="small">
                                <?= date('d M Y H:i', strtotime($e['mulai_pelaksanaan'])) ?><br>
                                <span class="text-muted">s/d <?= date('d M Y H:i', strtotime($e['tutup_pelaksanaan'])) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $status[1] ?> rounded-pill"><?= $status[0] ?></span>
                            </td>
                            <td class="text-center pe-3">
                                <a href="<?= base_url("admin/tryout-event/{$e['id']}/peserta") ?>"
                                   class="btn btn-sm btn-outline-info py-0 px-2" title="Peserta">
                                    <i class="bi bi-people"></i>
                                </a>
                                <a href="<?= base_url("admin/tryout-event/{$e['id']}/edit") ?>"
                                   class="btn btn-sm btn-outline-primary py-0 px-2" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="post"
                                      action="<?= base_url("admin/tryout-event/{$e['id']}/delete") ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus event ini?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus">
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
        <i class="bi bi-calendar-event text-muted" style="font-size:2.5rem"></i>
        <div class="mt-3 fw-semibold text-muted">Belum ada event tryout</div>
        <div class="text-muted small mt-1">
            <a href="<?= base_url('admin/tryout-event/create') ?>">Buat event pertama</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
