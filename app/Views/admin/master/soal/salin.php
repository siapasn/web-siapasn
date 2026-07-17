<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-copy"></i></div>
        <div>
            <div class="ph-title">Salin Soal Antar Tryout</div>
            <div class="ph-subtitle">Salin semua soal dari satu tryout ke tryout lain</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/master/soal') ?>" class="ph-action">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row g-4">

    <!-- Form Salin -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-copy me-1"></i> Form Salin Soal</h6>
            </div>
            <div class="card-body">
                <form method="post" action="<?= base_url('admin/master/soal/salin') ?>" id="formSalin">
                    <?= csrf_field() ?>

                    <!-- Tryout Sumber -->
                    <div class="mb-4">
                        <label for="tryout_sumber_id" class="form-label fw-semibold">
                            Tryout Sumber <span class="text-danger">*</span>
                        </label>
                        <select id="tryout_sumber_id" name="tryout_sumber_id"
                                class="form-select" style="width:100%" required>
                            <option value="">— Pilih Tryout Sumber —</option>
                            <?php foreach ($tryouts as $t): ?>
                                <option value="<?= $t['id'] ?>"
                                    <?= old('tryout_sumber_id') == $t['id'] ? 'selected' : '' ?>>
                                    <?= esc($t['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Tryout yang soalnya akan disalin.</div>
                        <!-- Info jumlah soal sumber -->
                        <div id="info_sumber" class="mt-2"></div>
                    </div>

                    <!-- Panah -->
                    <div class="text-center mb-4">
                        <i class="bi bi-arrow-down-circle-fill text-primary" style="font-size:1.8rem"></i>
                        <div class="text-muted small mt-1">Semua soal akan disalin ke</div>
                    </div>

                    <!-- Tryout Tujuan -->
                    <div class="mb-4">
                        <label for="tryout_tujuan_id" class="form-label fw-semibold">
                            Tryout Tujuan <span class="text-danger">*</span>
                        </label>
                        <select id="tryout_tujuan_id" name="tryout_tujuan_id"
                                class="form-select" style="width:100%" required>
                            <option value="">— Pilih Tryout Tujuan —</option>
                            <?php foreach ($tryouts as $t): ?>
                                <option value="<?= $t['id'] ?>"
                                    <?= old('tryout_tujuan_id') == $t['id'] ? 'selected' : '' ?>>
                                    <?= esc($t['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Tryout yang akan menerima salinan soal.</div>
                        <!-- Info jumlah soal tujuan -->
                        <div id="info_tujuan" class="mt-2"></div>
                    </div>

                    <div id="alert_same" class="alert alert-warning py-2 small d-none">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Tryout sumber dan tujuan tidak boleh sama.
                    </div>

                    <button type="submit" id="btnSalin" class="btn btn-primary">
                        <i class="bi bi-copy me-1"></i> Salin Soal Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Panduan -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-1"></i> Cara Kerja</h6>
            </div>
            <div class="card-body">
                <ol class="small text-muted ps-3 mb-3">
                    <li class="mb-2">Pilih <strong>Tryout Sumber</strong> — tryout yang soalnya ingin disalin.</li>
                    <li class="mb-2">Pilih <strong>Tryout Tujuan</strong> — tryout yang akan menerima soal.</li>
                    <li class="mb-2">Klik <strong>Salin Soal Sekarang</strong>.</li>
                    <li class="mb-2">Sistem akan menyalin semua soal dari tryout sumber ke tryout tujuan.</li>
                    <li>Soal yang sudah ada di tryout tujuan akan <strong>dilewati</strong> (tidak duplikat).</li>
                </ol>

                <div class="alert alert-info py-2 small mb-3">
                    <i class="bi bi-lightbulb me-1"></i>
                    <strong>Catatan:</strong> Proses ini hanya menyalin <em>mapping</em> soal ke tryout tujuan.
                    Data soal asli tidak berubah dan tetap bisa digunakan di tryout lain.
                </div>

                <div class="alert alert-warning py-2 small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Urutan soal di tryout tujuan akan dilanjutkan dari urutan terakhir yang sudah ada.
                </div>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const sumberSelect  = document.getElementById('tryout_sumber_id');
    const tujuanSelect  = document.getElementById('tryout_tujuan_id');
    const alertSame     = document.getElementById('alert_same');
    const btnSalin      = document.getElementById('btnSalin');
    const infoSumber    = document.getElementById('info_sumber');
    const infoTujuan    = document.getElementById('info_tujuan');
    const baseUrl       = '<?= rtrim(base_url('admin/mapping/soal'), '/') ?>';

    // Select2
    $('#tryout_sumber_id').select2({
        theme: 'bootstrap-5',
        placeholder: '— Pilih Tryout Sumber —',
        allowClear: true,
        width: '100%',
    });
    $('#tryout_tujuan_id').select2({
        theme: 'bootstrap-5',
        placeholder: '— Pilih Tryout Tujuan —',
        allowClear: true,
        width: '100%',
    });

    function loadJumlahSoal(tryoutId, infoEl) {
        if (! tryoutId) {
            infoEl.innerHTML = '';
            return;
        }
        infoEl.innerHTML = '<span class="text-muted small"><i class="bi bi-hourglass-split me-1"></i>Memuat...</span>';

        fetch('<?= base_url('admin/mapping/soal/') ?>' + tryoutId, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        })
        .then(function(r) {
            // Kita tidak bisa parse HTML, gunakan endpoint khusus
            // Fallback: tampilkan link ke mapping soal
            infoEl.innerHTML = '<a href="<?= base_url('admin/mapping/soal/') ?>' + tryoutId + '" target="_blank" class="small text-info"><i class="bi bi-box-arrow-up-right me-1"></i>Lihat soal di tryout ini</a>';
        })
        .catch(function() {
            infoEl.innerHTML = '';
        });
    }

    function checkSame() {
        const s = $('#tryout_sumber_id').val();
        const t = $('#tryout_tujuan_id').val();
        if (s && t && s === t) {
            alertSame.classList.remove('d-none');
            btnSalin.disabled = true;
        } else {
            alertSame.classList.add('d-none');
            btnSalin.disabled = false;
        }
    }

    $('#tryout_sumber_id').on('change', function () {
        loadJumlahSoal(this.value, infoSumber);
        checkSame();
    });

    $('#tryout_tujuan_id').on('change', function () {
        loadJumlahSoal(this.value, infoTujuan);
        checkSame();
    });

    // Konfirmasi sebelum submit
    document.getElementById('formSalin').addEventListener('submit', function (e) {
        const s = $('#tryout_sumber_id').val();
        const t = $('#tryout_tujuan_id').val();
        if (! s || ! t) return;
        if (s === t) { e.preventDefault(); return; }

        const sNama = $('#tryout_sumber_id option:selected').text().trim();
        const tNama = $('#tryout_tujuan_id option:selected').text().trim();
        if (! confirm('Salin semua soal dari:\n\n"' + sNama + '"\n\nke:\n\n"' + tNama + '"\n\nLanjutkan?')) {
            e.preventDefault();
        }
    });
}());
</script>
<?= $this->endSection() ?>
