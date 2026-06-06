<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-calendar3"></i></div>
    <div>
        <div class="ph-title">Kalender Event Tryout</div>
        <div class="ph-subtitle">Jadwal pendaftaran dan pelaksanaan event tryout nasional</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Legenda -->
<div class="d-flex flex-wrap gap-3 mb-3">
    <div class="d-flex align-items-center gap-2">
        <span style="width:14px;height:14px;border-radius:3px;background:#0dcaf0;display:inline-block"></span>
        <span class="small text-muted">Pendaftaran Dibuka</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span style="width:14px;height:14px;border-radius:3px;background:#198754;display:inline-block"></span>
        <span class="small text-muted">Pelaksanaan</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span style="width:14px;height:14px;border-radius:3px;background:#f5a623;display:inline-block"></span>
        <span class="small text-muted">Hari Ini</span>
    </div>
</div>

<!-- Kalender -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-0">
        <div id="kalender"></div>
    </div>
</div>

<!-- Daftar Event -->
<?php if (! empty($events)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-list-ul me-2"></i>Semua Event</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nama Event</th>
                        <th>Pendaftaran</th>
                        <th>Pelaksanaan</th>
                        <th class="text-center">Peserta</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): ?>
                    <tr>
                        <td class="ps-3 fw-medium"><?= esc($ev['nama']) ?></td>
                        <td class="text-muted">
                            <?= date('d M Y', strtotime($ev['mulai_pendaftaran'])) ?>
                            <span class="text-muted">—</span>
                            <?= date('d M Y', strtotime($ev['tutup_pendaftaran'])) ?>
                        </td>
                        <td class="text-muted">
                            <?= date('d M Y H:i', strtotime($ev['mulai_pelaksanaan'])) ?>
                            <span class="text-muted">—</span>
                            <?= date('d M Y H:i', strtotime($ev['tutup_pelaksanaan'])) ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill"><?= (int) $ev['total_peserta'] ?></span>
                        </td>
                        <td class="text-center pe-3">
                            <a href="<?= base_url('user/tryout-event/' . ($ev['slug'] ?: $ev['id'])) ?>"
                               class="btn btn-sm btn-outline-primary py-0 px-2">
                                Detail
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- FullCalendar -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/id.global.min.js"></script>

<style>
#kalender { padding: 1rem; }
.fc-event { cursor: pointer; font-size: .75rem; }
.fc-toolbar-title { font-size: 1rem !important; font-weight: 700; }
@media (max-width: 576px) {
    .fc-toolbar { flex-direction: column; gap: .5rem; }
    .fc-toolbar-title { font-size: .9rem !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const events = <?= json_encode($calendarEvents) ?>;

    const cal = new FullCalendar.Calendar(document.getElementById('kalender'), {
        locale: 'id',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth'
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            list: 'Daftar',
        },
        events: events,
        eventClick: function (info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        },
        eventDidMount: function (info) {
            // Tooltip
            info.el.setAttribute('title', info.event.title + '\n' + (info.event.extendedProps.desc || ''));
        },
        height: 'auto',
        dayMaxEvents: 3,
    });

    cal.render();
});
</script>
<?= $this->endSection() ?>
