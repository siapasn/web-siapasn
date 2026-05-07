<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-folder2-open"></i></div>
        <div>
            <div class="ph-title">Master Data File</div>
            <div class="ph-subtitle">Kelola file data pendukung aplikasi</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Upload Form -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-cloud-upload me-2 text-primary"></i>Unggah File Baru</h6>
    </div>
    <div class="card-body">
        <form id="formUpload" method="post" action="<?= base_url('admin/master/datafile/upload') ?>"
              enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-7">
                    <label for="file" class="form-label">Pilih File</label>
                    <input type="file" id="file" name="file" class="form-control"
                           accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx" required>
                    <div class="form-text">
                        Tipe yang diizinkan: JPG, JPEG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX.
                        Maks. 5 MB.
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary" id="btnUpload">
                        <i class="bi bi-upload me-1"></i> Unggah
                    </button>
                </div>
            </div>
        </form>

        <!-- AJAX upload progress -->
        <div id="uploadProgress" class="mt-3 d-none">
            <div class="progress" style="height:6px">
                <div class="progress-bar progress-bar-striped progress-bar-animated w-100"></div>
            </div>
            <small class="text-muted">Mengunggah...</small>
        </div>
        <div id="uploadResult" class="mt-2"></div>
    </div>
</div>

<!-- Files Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelDataFile" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Nama File</th>
                        <th class="text-center" style="width:100px">Tipe</th>
                        <th class="text-end" style="width:120px">Ukuran</th>
                        <th style="width:160px">Tanggal Upload</th>
                        <th class="text-center pe-3" style="width:80px">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyFiles">
                    <?php if (!empty($files)): ?>
                        <?php foreach ($files as $i => $f): ?>
                            <tr id="row-<?= $f['id'] ?>">
                                <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php
                                        $iconMap = [
                                            'pdf'  => ['bi-file-earmark-pdf', 'text-danger'],
                                            'doc'  => ['bi-file-earmark-word', 'text-primary'],
                                            'docx' => ['bi-file-earmark-word', 'text-primary'],
                                            'xls'  => ['bi-file-earmark-excel', 'text-success'],
                                            'xlsx' => ['bi-file-earmark-excel', 'text-success'],
                                            'jpg'  => ['bi-file-earmark-image', 'text-warning'],
                                            'jpeg' => ['bi-file-earmark-image', 'text-warning'],
                                            'png'  => ['bi-file-earmark-image', 'text-warning'],
                                            'gif'  => ['bi-file-earmark-image', 'text-warning'],
                                        ];
                                        $ic = $iconMap[strtolower($f['tipe'])] ?? ['bi-file-earmark', 'text-secondary'];
                                        ?>
                                        <i class="bi <?= $ic[0] ?> <?= $ic[1] ?> fs-5"></i>
                                        <span class="fw-medium"><?= esc($f['nama']) ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill text-uppercase">
                                        <?= esc($f['tipe']) ?>
                                    </span>
                                </td>
                                <td class="text-end text-muted small">
                                    <?php
                                    $kb = round($f['ukuran'] / 1024, 1);
                                    $mb = round($f['ukuran'] / (1024 * 1024), 2);
                                    echo $mb >= 1 ? "{$mb} MB" : "{$kb} KB";
                                    ?>
                                </td>
                                <td class="text-muted small">
                                    <?= !empty($f['created_at']) ? date('d M Y, H:i', strtotime($f['created_at'])) : '—' ?>
                                </td>
                                <td class="text-center pe-3">
                                    <form method="post"
                                          action="<?= base_url("admin/master/datafile/{$f['id']}/delete") ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus file ini secara permanen?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Belum ada file yang diunggah
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    // DataTables
    $(document).ready(function () {
        $('#tabelDataFile').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
            },
            pageLength: 25,
            ordering: true,
            columnDefs: [
                { orderable: false, targets: [0, 5] }
            ]
        });
    });

    // AJAX Upload
    const form       = document.getElementById('formUpload');
    const progress   = document.getElementById('uploadProgress');
    const result     = document.getElementById('uploadResult');
    const btnUpload  = document.getElementById('btnUpload');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const fileInput = document.getElementById('file');
            if (!fileInput.files.length) return;

            const formData = new FormData(form);

            progress.classList.remove('d-none');
            result.innerHTML = '';
            btnUpload.disabled = true;

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(res => res.json())
            .then(data => {
                progress.classList.add('d-none');
                btnUpload.disabled = false;

                if (data.status) {
                    result.innerHTML = '<div class="alert alert-success py-2 mb-0">' +
                        '<i class="bi bi-check-circle-fill me-1"></i> File berhasil diunggah. ' +
                        '<a href="" class="alert-link">Muat ulang halaman</a> untuk melihat daftar terbaru.' +
                        '</div>';
                    form.reset();
                } else {
                    result.innerHTML = '<div class="alert alert-danger py-2 mb-0">' +
                        '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' +
                        (data.message || 'Gagal mengunggah file.') +
                        '</div>';
                }
            })
            .catch(() => {
                progress.classList.add('d-none');
                btnUpload.disabled = false;
                result.innerHTML = '<div class="alert alert-danger py-2 mb-0">Terjadi kesalahan jaringan.</div>';
            });
        });
    }
}());
</script>

<?= $this->endSection() ?>
