<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-envelope-paper"></i></div>
        <div>
            <div class="ph-title">Blast Email</div>
            <div class="ph-subtitle">Kirim email ke semua user atau user tertentu</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Validation Errors -->
<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<!-- Form Kirim Email -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-send me-2 text-primary"></i>Kirim Email</h6>
    </div>
    <div class="card-body">
        <form method="post" action="<?= base_url('admin/blast-email/send') ?>" id="formBlastEmail">
            <?= csrf_field() ?>

            <div class="row g-3">

                <!-- Tipe Penerima -->
                <div class="col-12">
                    <label class="form-label fw-semibold">Penerima <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipe" id="tipeAll" value="all"
                                   <?= old('tipe', 'all') === 'all' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tipeAll">
                                <i class="bi bi-people me-1"></i>Semua User
                                <span class="badge bg-secondary ms-1" style="font-size:.68rem"><?= count($users) ?></span>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipe" id="tipeSubscribe" value="subscribe"
                                   <?= old('tipe') === 'subscribe' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tipeSubscribe">
                                <i class="bi bi-envelope-heart me-1"></i>Subscriber
                                <span class="badge bg-success ms-1" style="font-size:.68rem"><?= number_format($totalSubscriber) ?></span>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipe" id="tipeSubscribeSingle" value="subscribe_single"
                                   <?= old('tipe') === 'subscribe_single' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tipeSubscribeSingle">
                                <i class="bi bi-envelope-plus me-1"></i>Subscriber Tertentu
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipe" id="tipeSingle" value="single"
                                   <?= old('tipe') === 'single' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tipeSingle">
                                <i class="bi bi-person me-1"></i>User Tertentu
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipe" id="tipeManual" value="manual"
                                   <?= old('tipe') === 'manual' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tipeManual">
                                <i class="bi bi-keyboard me-1"></i>Email Manual
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Pilih Subscriber Tertentu -->
                <div class="col-12" id="wrapperTargetSubscriber" style="<?= old('tipe') === 'subscribe_single' ? '' : 'display:none' ?>">
                    <label for="target_subscriber_ids" class="form-label">Pilih Subscriber <span class="text-danger">*</span></label>
                    <select id="target_subscriber_ids" name="target_subscriber_ids[]"
                            class="form-select" multiple style="width:100%">
                        <?php foreach ($subscribers as $sub): ?>
                            <option value="<?= $sub['id'] ?>">
                                <?= esc($sub['name'] ?: 'Tanpa Nama') ?> — <?= esc($sub['email']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Ketik nama atau email untuk mencari. Bisa pilih lebih dari satu.</div>
                </div>

                <!-- Pilih User (muncul jika single) -->
                <div class="col-12 col-md-6" id="wrapperTargetUser" style="<?= old('tipe') === 'single' ? '' : 'display:none' ?>">
                    <label for="target_user_id" class="form-label">Pilih User <span class="text-danger">*</span></label>
                    <select id="target_user_id" name="target_user_id" class="form-select select2-user" style="width:100%">
                        <option value="">-- Cari user --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"
                                <?= (string) old('target_user_id') === (string) $u['id'] ? 'selected' : '' ?>>
                                <?= esc($u['nama']) ?> (<?= esc($u['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Email Manual -->
                <div class="col-12" id="wrapperManualEmails" style="<?= old('tipe') === 'manual' ? '' : 'display:none' ?>">
                    <label for="manual_emails" class="form-label">Alamat Email Manual <span class="text-danger">*</span></label>
                    <textarea id="manual_emails" name="manual_emails" class="form-control" rows="4"
                              placeholder="contoh@email.com
user2@email.com
user3@email.com"><?= esc(old('manual_emails', '')) ?></textarea>
                    <div class="form-text">Masukkan satu atau banyak email. Pisahkan dengan baris baru, koma, titik koma, atau spasi.</div>
                </div>

                <!-- Subject -->
                <div class="col-12">
                    <label for="subject" class="form-label">Subject Email <span class="text-danger">*</span></label>
                    <input type="text" id="subject" name="subject" class="form-control"
                           value="<?= esc(old('subject', '')) ?>"
                           placeholder="Masukkan subject email" required>
                </div>

                <!-- Body Email -->
                <div class="col-12">
                    <label for="body" class="form-label">Isi Email <span class="text-danger">*</span></label>
                    <textarea id="body" name="body" class="summernote-editor"><?= old('body', '') ?></textarea>
                </div>

            </div>

            <hr class="my-4">

            <div class="d-flex gap-2 align-items-center">
                <button type="submit" class="btn btn-primary" id="btnKirim">
                    <i class="bi bi-send me-1"></i> Kirim Email
                </button>
                <span class="text-muted small" id="infoTarget">
                    <i class="bi bi-info-circle me-1"></i>Email akan dikirim ke <strong>semua user</strong> terdaftar.
                </span>
            </div>

        </form>
    </div>
</div>

<!-- Riwayat Blast Email -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
        <i class="bi bi-clock-history text-muted"></i>
        <h6 class="mb-0 fw-semibold">Riwayat Pengiriman</h6>
    </div>

    <?php if (! empty($riwayat)): ?>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Subject</th>
                        <th>Tipe</th>
                        <th>Target</th>
                        <th class="text-center">Berhasil</th>
                        <th class="text-center">Gagal</th>
                        <th>Dikirim Oleh</th>
                        <th>Waktu</th>
                        <th class="text-center pe-3" style="width:70px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $i => $r): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td class="fw-medium"><?= esc($r['subject']) ?></td>
                            <td>
                                <?php if ($r['tipe'] === 'all'): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">Semua User</span>
                                <?php elseif ($r['tipe'] === 'subscribe'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Subscriber</span>
                                <?php elseif ($r['tipe'] === 'subscribe_single'): ?>
                                    <span class="badge bg-teal bg-opacity-10 text-success border border-success-subtle" style="background-color:rgba(32,201,151,.1)!important;color:#0d9e6e!important;border-color:rgba(32,201,151,.3)!important">Subscriber Tertentu</span>
                                <?php else: ?>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle">User Tertentu</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted">
                                <?= $r['tipe'] === 'single' ? esc($r['target_email']) : '—' ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success rounded-pill"><?= (int) $r['total_sent'] ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ((int) $r['total_failed'] > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= (int) $r['total_failed'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= esc($r['sent_by_nama'] ?? '-') ?></td>
                            <td class="small text-muted"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></td>
                            <td class="text-center pe-3">
                                <a href="<?= base_url("admin/blast-email/{$r['id']}/preview") ?>"
                                   class="btn btn-sm btn-outline-info py-0 px-2" title="Preview">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="card-body text-center py-5">
        <i class="bi bi-envelope text-muted" style="font-size:2rem"></i>
        <div class="mt-2 text-muted">Belum ada riwayat pengiriman email.</div>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Summernote editor
$('#body').summernote({
    placeholder: 'Tulis isi email di sini...',
    tabsize: 2,
    height: 300,
    toolbar: [
        ['style',   ['style']],
        ['font',    ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
        ['fontsize',['fontsize']],
        ['color',   ['color']],
        ['para',    ['ul', 'ol', 'paragraph']],
        ['table',   ['table']],
        ['insert',  ['link', 'picture', 'hr']],
        ['view',    ['fullscreen', 'codeview']]
    ]
});

// Submit form — pastikan body ter-sync
$('#formBlastEmail').on('submit', function (e) {
    $('#body').val($('#body').summernote('code'));

    const tipe = document.querySelector('input[name="tipe"]:checked').value;
    if (tipe === 'all') {
        if (!confirm('Anda yakin ingin mengirim email ke SEMUA user?')) {
            e.preventDefault();
            return false;
        }
    } else if (tipe === 'subscribe') {
        if (!confirm('Anda yakin ingin mengirim email ke <?= number_format($totalSubscriber) ?> subscriber?')) {
            e.preventDefault();
            return false;
        }
    } else if (tipe === 'subscribe_single') {
        const selected = $('#target_subscriber_ids').val();
        if (!selected || selected.length === 0) {
            alert('Pilih minimal satu subscriber.');
            e.preventDefault();
            return false;
        }
        if (!confirm('Anda yakin ingin mengirim email ke ' + selected.length + ' subscriber yang dipilih?')) {
            e.preventDefault();
            return false;
        }
    } else if (tipe === 'manual') {
        const raw = document.getElementById('manual_emails').value || '';
        const emails = raw.split(/[\s,;]+/).map(v => v.trim()).filter(Boolean);
        const uniqueEmails = Array.from(new Set(emails.map(v => v.toLowerCase())));
        if (uniqueEmails.length === 0) {
            alert('Masukkan minimal satu alamat email manual.');
            e.preventDefault();
            return false;
        }
        if (!confirm('Anda yakin ingin mengirim email ke ' + uniqueEmails.length + ' alamat email manual?')) {
            e.preventDefault();
            return false;
        }
    }
});

// Select2 untuk pilih user
$('#target_user_id').select2({
    theme: 'bootstrap-5',
    placeholder: '-- Cari user --',
    allowClear: true,
    width: '100%',
});

// Select2 untuk pilih subscriber tertentu
$('#target_subscriber_ids').select2({
    theme: 'bootstrap-5',
    placeholder: '-- Cari nama atau email subscriber --',
    allowClear: true,
    width: '100%',
    language: {
        noResults: function() { return 'Subscriber tidak ditemukan'; },
        searching: function() { return 'Mencari...'; },
    },
});

// Toggle penerima
(function () {
    const wrapperTarget = document.getElementById('wrapperTargetUser');
    const wrapperManual = document.getElementById('wrapperManualEmails');
    const infoTarget    = document.getElementById('infoTarget');
    const radios        = document.querySelectorAll('input[name="tipe"]');

    function toggle() {
        const val = document.querySelector('input[name="tipe"]:checked').value;
        wrapperTarget.style.display = 'none';
        wrapperManual.style.display = 'none';
        document.getElementById('wrapperTargetSubscriber').style.display = 'none';

        if (val === 'single') {
            wrapperTarget.style.display = '';
            infoTarget.innerHTML = '<i class="bi bi-info-circle me-1"></i>Email akan dikirim ke <strong>1 user</strong> yang dipilih.';
        } else if (val === 'subscribe') {
            infoTarget.innerHTML = '<i class="bi bi-envelope-heart me-1 text-success"></i>Email akan dikirim ke <strong><?= number_format($totalSubscriber) ?> subscriber</strong> dari tabel users_subscribe.';
            $('#target_user_id').val('').trigger('change');
        } else if (val === 'subscribe_single') {
            document.getElementById('wrapperTargetSubscriber').style.display = '';
            infoTarget.innerHTML = '<i class="bi bi-envelope-plus me-1 text-success"></i>Email akan dikirim ke <strong>subscriber yang dipilih</strong>.';
            $('#target_user_id').val('').trigger('change');
        } else if (val === 'manual') {
            wrapperManual.style.display = '';
            infoTarget.innerHTML = '<i class="bi bi-keyboard me-1 text-primary"></i>Email akan dikirim ke <strong>alamat email manual</strong> yang diinput.';
            $('#target_user_id').val('').trigger('change');
            $('#target_subscriber_ids').val(null).trigger('change');
        } else {
            infoTarget.innerHTML = '<i class="bi bi-info-circle me-1"></i>Email akan dikirim ke <strong>semua user</strong> terdaftar (<?= count($users) ?> user).';
            $('#target_user_id').val('').trigger('change');
        }
    }

    radios.forEach(r => r.addEventListener('change', toggle));
    toggle();
}());
</script>
<?= $this->endSection() ?>
