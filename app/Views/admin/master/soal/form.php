<?= $this->extend('layouts/admin') ?>

<?php $isEdit = ! empty($soal); ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-question-circle"></i></div>
        <div>
            <div class="ph-title"><?= $isEdit ? 'Edit Soal' : 'Tambah Soal' ?></div>
            <div class="ph-subtitle">Master Soal</div>
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

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= $action ?>">
            <?= csrf_field() ?>

            <div class="row g-3">

                <!-- Kategori (hanya parent) -->
                <div class="col-12 col-md-6">
                    <label for="kategori_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select id="kategori_id" name="kategori_id" class="form-select" required>
                        <option value="">— Pilih Kategori —</option>
                        <?php
                        // $kategoris hanya berisi parent (parent_id IS NULL)
                        $selectedKategori = old('kategori_id', $soal['kategori_id'] ?? '');
                        foreach ($kategoris as $k):
                        ?>
                            <option value="<?= $k['id'] ?>"
                                <?= (string) $selectedKategori === (string) $k['id'] ? 'selected' : '' ?>>
                                <?= esc($k['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sub Kategori (load AJAX berdasarkan kategori yang dipilih) -->
                <div class="col-12 col-md-6">
                    <label for="sub_kategori_id" class="form-label">
                        Sub Kategori
                        <span id="sub_kategori_required_badge" class="text-danger d-none">*</span>
                        <span id="sub_kategori_optional_badge" class="text-muted small">(opsional — muncul jika kategori punya sub)</span>
                    </label>
                    <select id="sub_kategori_id" name="sub_kategori_id" class="form-select" disabled>
                        <option value="">— Pilih Kategori dulu —</option>
                    </select>
                    <div id="sub_kategori_info" class="form-text text-muted"></div>
                </div>

                <!-- Pertanyaan -->
                <div class="col-12">
                    <label for="pertanyaan" class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                    <textarea id="pertanyaan" name="pertanyaan" class="summernote-editor"
                              placeholder="Tulis pertanyaan di sini..."><?= old('pertanyaan', $soal['pertanyaan'] ?? '') ?></textarea>
                    <!-- Hidden field untuk validasi required -->
                    <input type="hidden" id="pertanyaan_check" value="">
                </div>

                <!-- Pilihan A -->
                <div class="col-12 col-md-6">
                    <label for="pilihan_a" class="form-label">Pilihan A <span class="text-danger">*</span></label>
                    <textarea id="pilihan_a" name="pilihan_a" class="form-control" rows="2"
                              placeholder="Pilihan A" required><?= esc(old('pilihan_a', $soal['pilihan_a'] ?? '')) ?></textarea>
                </div>

                <!-- Pilihan B -->
                <div class="col-12 col-md-6">
                    <label for="pilihan_b" class="form-label">Pilihan B <span class="text-danger">*</span></label>
                    <textarea id="pilihan_b" name="pilihan_b" class="form-control" rows="2"
                              placeholder="Pilihan B" required><?= esc(old('pilihan_b', $soal['pilihan_b'] ?? '')) ?></textarea>
                </div>

                <!-- Pilihan C -->
                <div class="col-12 col-md-6">
                    <label for="pilihan_c" class="form-label">Pilihan C <span class="text-danger">*</span></label>
                    <textarea id="pilihan_c" name="pilihan_c" class="form-control" rows="2"
                              placeholder="Pilihan C" required><?= esc(old('pilihan_c', $soal['pilihan_c'] ?? '')) ?></textarea>
                </div>

                <!-- Pilihan D -->
                <div class="col-12 col-md-6">
                    <label for="pilihan_d" class="form-label">Pilihan D <span class="text-danger">*</span></label>
                    <textarea id="pilihan_d" name="pilihan_d" class="form-control" rows="2"
                              placeholder="Pilihan D" required><?= esc(old('pilihan_d', $soal['pilihan_d'] ?? '')) ?></textarea>
                </div>

                <!-- Pilihan E (opsional) -->
                <div class="col-12 col-md-6">
                    <label for="pilihan_e" class="form-label">Pilihan E <span class="text-muted">(opsional)</span></label>
                    <textarea id="pilihan_e" name="pilihan_e" class="form-control" rows="2"
                              placeholder="Pilihan E (opsional)"><?= esc(old('pilihan_e', $soal['pilihan_e'] ?? '')) ?></textarea>
                </div>

                <!-- Kunci Jawaban (hanya untuk POINT: TWK, TIU) -->
                <div class="col-12 col-md-6" id="section_kunci_jawaban" style="display:none">
                    <label class="form-label">Kunci Jawaban <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3 flex-wrap mt-1">
                        <?php
                        $selectedKunci = old('kunci_jawaban', $soal['kunci_jawaban'] ?? '');
                        foreach (['a', 'b', 'c', 'd', 'e'] as $opt):
                        ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="kunci_jawaban" id="kunci_<?= $opt ?>"
                                       value="<?= $opt ?>"
                                       <?= $selectedKunci === $opt ? 'checked' : '' ?>>
                                <label class="form-check-label text-uppercase fw-semibold" for="kunci_<?= $opt ?>">
                                    <?= strtoupper($opt) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-text text-muted">Pilih satu jawaban yang benar.</div>
                </div>

                <!-- Nilai A-E (hanya untuk SCORE: TKP) -->
                <div class="col-12" id="section_nilai_score" style="display:none">
                    <label class="form-label fw-semibold">
                        Nilai Per Pilihan <span class="text-danger">*</span>
                        <span class="badge bg-warning text-dark ms-1">SCORE</span>
                    </label>
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Isi nilai 1–5 untuk setiap pilihan. Setiap nilai <strong>tidak boleh sama</strong>.
                    </div>
                    <div class="row g-2">
                        <?php foreach (['a','b','c','d','e'] as $opt): ?>
                        <div class="col-6 col-md-2">
                            <label for="nilai_<?= $opt ?>" class="form-label">
                                Nilai <?= strtoupper($opt) ?> <span class="text-danger">*</span>
                            </label>
                            <select id="nilai_<?= $opt ?>" name="nilai_<?= $opt ?>" class="form-select nilai-select">
                                <option value="">—</option>
                                <?php for ($v = 1; $v <= 5; $v++): ?>
                                    <option value="<?= $v ?>"
                                        <?= (string) old('nilai_'.$opt, $soal['nilai_'.$opt] ?? '') === (string) $v ? 'selected' : '' ?>>
                                        <?= $v ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="nilai_error" class="text-danger small mt-2" style="display:none">
                        <i class="bi bi-exclamation-triangle me-1"></i>Nilai setiap pilihan tidak boleh sama.
                    </div>
                </div>

                <!-- Hidden field tipe_soal -->
                <input type="hidden" id="tipe_soal_hidden" name="tipe_soal_hidden" value="<?= esc($tipeSoal ?? '') ?>">

                <!-- Pembahasan -->
                <div class="col-12">
                    <label for="pembahasan" class="form-label">Pembahasan <span class="text-muted">(opsional)</span></label>
                    <textarea id="pembahasan" name="pembahasan" class="summernote-editor"
                              placeholder="Tulis pembahasan jawaban di sini..."><?= old('pembahasan', $soal['pembahasan'] ?? '') ?></textarea>
                </div>

            </div><!-- /.row -->

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Soal' ?>
                </button>
                <a href="<?= base_url('admin/master/soal') ?>" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<script>
(function () {
    const kategoriSelect    = document.getElementById('kategori_id');
    const subKategoriSelect = document.getElementById('sub_kategori_id');
    const subKategoriInfo   = document.getElementById('sub_kategori_info');
    const baseAjaxUrl       = '<?= rtrim(base_url('admin/master/soal/sub-kategori'), '/') ?>';
    const preselectedSub    = '<?= old('sub_kategori_id', $soal['sub_kategori_id'] ?? '') ?>';

    function loadSubKategori(kategoriId, selectedId) {
        if (! kategoriId) {
            subKategoriSelect.innerHTML = '<option value="">— Pilih Kategori dulu —</option>';
            subKategoriSelect.disabled  = true;
            subKategoriSelect.required  = false;
            if (subKategoriInfo) subKategoriInfo.textContent = '';
            // Reset label
            document.getElementById('sub_kategori_required_badge').classList.add('d-none');
            document.getElementById('sub_kategori_optional_badge').classList.remove('d-none');
            return;
        }

        subKategoriSelect.innerHTML = '<option value="">— Memuat... —</option>';
        subKategoriSelect.disabled  = true;
        subKategoriSelect.required  = false;
        if (subKategoriInfo) {
            subKategoriInfo.textContent = 'Memuat sub kategori...';
            subKategoriInfo.className   = 'form-text text-muted';
        }

        const url = baseAjaxUrl + '/' + kategoriId;

        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(r) {
            if (r.redirected || r.url.includes('/login')) {
                window.location.href = '<?= base_url('login') ?>';
                return null;
            }
            if (! r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(data) {
            if (! data) return;
            if (data.status && data.data && data.data.length > 0) {
                subKategoriSelect.innerHTML = '<option value="">— Pilih Sub Kategori —</option>';
                var autoSelectedTipe = '';
                data.data.forEach(function(sub) {
                    const opt = document.createElement('option');
                    opt.value            = sub.id;
                    opt.textContent      = sub.nama;
                    opt.dataset.tipeSoal = sub.tipe_soal || '';
                    if (String(sub.id) === String(selectedId)) {
                        opt.selected     = true;
                        autoSelectedTipe = sub.tipe_soal || '';
                    }
                    subKategoriSelect.appendChild(opt);
                });
                subKategoriSelect.disabled = false;
                subKategoriSelect.required = true;
                document.getElementById('sub_kategori_required_badge').classList.remove('d-none');
                document.getElementById('sub_kategori_optional_badge').classList.add('d-none');
                if (subKategoriInfo) {
                    subKategoriInfo.textContent = data.data.length + ' sub kategori tersedia. Wajib dipilih.';
                    subKategoriInfo.className   = 'form-text text-warning fw-semibold';
                }
                // Update tipe soal berdasarkan sub-kategori yang ter-select
                updateTipeSoal(autoSelectedTipe);
            } else {
                subKategoriSelect.innerHTML = '<option value="">— Tidak ada sub kategori —</option>';
                subKategoriSelect.disabled  = true;
                subKategoriSelect.required  = false;
                document.getElementById('sub_kategori_required_badge').classList.add('d-none');
                document.getElementById('sub_kategori_optional_badge').classList.remove('d-none');
                if (subKategoriInfo) {
                    subKategoriInfo.textContent = 'Kategori ini tidak memiliki sub kategori.';
                    subKategoriInfo.className   = 'form-text text-muted';
                }
                updateTipeSoal('');
            }
        })
        .catch(function(err) {
            console.error('Sub kategori load error:', err);
            subKategoriSelect.innerHTML = '<option value="">— Gagal memuat —</option>';
            subKategoriSelect.disabled  = false;
            subKategoriSelect.required  = false;
            if (subKategoriInfo) {
                subKategoriInfo.textContent = 'Gagal memuat. Error: ' + err.message;
                subKategoriInfo.className   = 'form-text text-danger';
            }
        });
    }

    // Event: saat kategori berubah
    kategoriSelect.addEventListener('change', function () {
        loadSubKategori(this.value, '');
    });

    // Event: saat sub-kategori berubah → update tipe soal
    subKategoriSelect.addEventListener('change', function () {
        const selectedOpt = this.options[this.selectedIndex];
        const tipe = selectedOpt ? (selectedOpt.dataset.tipeSoal || '') : '';
        updateTipeSoal(tipe);
    });

    // Toggle tampilan SCORE vs POINT
    function updateTipeSoal(tipe) {
        const sectionKunci   = document.getElementById('section_kunci_jawaban');
        const sectionNilai   = document.getElementById('section_nilai_score');
        const tipeSoalHidden = document.getElementById('tipe_soal_hidden');

        // Jika tipe tidak diberikan, ambil dari option yang terpilih
        if (tipe === undefined || tipe === null) {
            const selectedOpt = subKategoriSelect.options[subKategoriSelect.selectedIndex];
            tipe = selectedOpt ? (selectedOpt.dataset.tipeSoal || '') : '';
        }

        if (tipeSoalHidden) tipeSoalHidden.value = tipe;

        if (tipe === 'SCORE') {
            // SCORE (TKP): tampilkan nilai A-E, sembunyikan kunci jawaban
            if (sectionKunci) sectionKunci.style.display = 'none';
            if (sectionNilai) sectionNilai.style.display = '';
            document.querySelectorAll('input[name="kunci_jawaban"]').forEach(function(r) { r.required = false; });
        } else if (tipe === 'POINT') {
            // POINT (TWK, TIU): tampilkan kunci jawaban, sembunyikan nilai A-E
            if (sectionKunci) sectionKunci.style.display = '';
            if (sectionNilai) sectionNilai.style.display = 'none';
        } else {
            // Belum ada sub kategori dipilih: sembunyikan keduanya
            if (sectionKunci) sectionKunci.style.display = 'none';
            if (sectionNilai) sectionNilai.style.display = 'none';
        }
    }

    // On page load: jika kategori sudah dipilih (mode edit atau old input)
    if (kategoriSelect.value) {
        loadSubKategori(kategoriSelect.value, preselectedSub);
    } else {
        // Tidak ada kategori dipilih → pastikan tampilan default (kunci jawaban)
        updateTipeSoal('');
    }

    // Fallback: jika tipe_soal_hidden sudah terisi dari server (mode edit),
    // terapkan segera sebelum AJAX selesai (mencegah flash kunci jawaban)
    const initialTipe = document.getElementById('tipe_soal_hidden').value;
    if (initialTipe) {
        updateTipeSoal(initialTipe);
    }

    // Validasi nilai tidak boleh sama
    document.querySelectorAll('.nilai-select').forEach(function(sel) {
        sel.addEventListener('change', function () {
            const values = Array.from(document.querySelectorAll('.nilai-select'))
                .map(function(s) { return s.value; })
                .filter(function(v) { return v !== ''; });
            const unique = new Set(values);
            const errEl  = document.getElementById('nilai_error');
            if (values.length !== unique.size) {
                if (errEl) errEl.style.display = '';
            } else {
                if (errEl) errEl.style.display = 'none';
            }
        });
    });
}());

// Inisialisasi Summernote — tunggu sampai semua script di-load
window.addEventListener('load', function () {
    if (typeof $ === 'undefined' || typeof $.fn.summernote === 'undefined') {
        console.error('jQuery atau Summernote belum tersedia');
        return;
    }

    var snConfig = {
        lang: 'id-ID',
        tabsize: 2,
        height: 200,
        toolbar: [
            ['style',   ['style']],
            ['font',    ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
            ['fontsize',['fontsize']],
            ['color',   ['color']],
            ['para',    ['ul', 'ol', 'paragraph']],
            ['table',   ['table']],
            ['insert',  ['link', 'picture', 'hr']],
            ['view',    ['fullscreen', 'codeview', 'help']]
        ]
    };

    // Field Pertanyaan
    $('#pertanyaan').summernote($.extend({}, snConfig, {
        placeholder: 'Tulis pertanyaan di sini...',
        height: 220
    }));

    // Field Pembahasan
    $('#pembahasan').summernote($.extend({}, snConfig, {
        placeholder: 'Tulis pembahasan jawaban di sini...',
        height: 180
    }));

    // Validasi sebelum submit
    $('form').on('submit', function (e) {
        if ($('#pertanyaan').summernote('isEmpty')) {
            e.preventDefault();
            alert('Pertanyaan tidak boleh kosong.');
            $('#pertanyaan').summernote('focus');
            return false;
        }
    });
});
</script>

<?= $this->endSection() ?>
