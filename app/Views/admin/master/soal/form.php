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

                <!-- Kategori — Select2, semua kategori -->
                <div class="col-12 col-md-6">
                    <label for="kategori_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select id="kategori_id" name="kategori_id" class="form-select" style="width:100%" required>
                        <option value="">— Pilih Kategori —</option>
                        <?php
                        $selectedKategori = old('kategori_id', $soal['kategori_id'] ?? '');
                        foreach ($kategoris as $k):
                            $label = esc($k['nama']);
                            if (! empty($k['parent_nama'])) {
                                $label = esc($k['parent_nama']) . ' › ' . $label;
                            }
                        ?>
                            <option value="<?= $k['id'] ?>"
                                data-tipe="<?= esc($k['tipe_soal'] ?? '') ?>"
                                <?= (string) $selectedKategori === (string) $k['id'] ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="tipe_soal_badge" class="mt-1"></div>
                </div>

                <!-- Tambahkan ke Tryout (opsional) — Select2 searchable -->
                <div class="col-12 col-md-6">
                    <label for="tryout_id" class="form-label">
                        Tambahkan ke Tryout
                        <span class="text-muted small">(opsional)</span>
                    </label>
                    <select id="tryout_id" name="tryout_id" class="form-select" style="width:100%">
                        <option value="">— Tidak ditambahkan ke tryout —</option>
                        <?php
                        $selectedTryout = old('tryout_id', '');
                        if ($isEdit && ! empty($mappedTryoutIds ?? [])) {
                            $selectedTryout = (string) ($mappedTryoutIds[0] ?? '');
                        }
                        foreach ($tryouts ?? [] as $t):
                        ?>
                            <option value="<?= $t['id'] ?>"
                                <?= (string) $selectedTryout === (string) $t['id'] ? 'selected' : '' ?>>
                                <?= esc($t['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        <?php if ($isEdit && ! empty($mappedTryoutIds ?? [])): ?>
                            <i class="bi bi-info-circle me-1 text-info"></i>
                            Soal ini sudah terdapat di <strong><?= count($mappedTryoutIds) ?></strong> tryout.
                            Pilih tryout lain untuk menambahkan ke tryout baru.
                        <?php else: ?>
                            Jika dipilih, soal akan otomatis ditambahkan ke tryout tanpa perlu mapping manual.
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pertanyaan -->
                <div class="col-12">
                    <label for="pertanyaan" class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                    <textarea id="pertanyaan" name="pertanyaan" class="summernote-editor"
                              placeholder="Tulis pertanyaan di sini..."><?= old('pertanyaan', $soal['pertanyaan'] ?? '') ?></textarea>
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

                <!-- Kunci Jawaban (untuk tipe POINT) -->
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

                <!-- Nilai A-E (untuk tipe SCORE) -->
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    // ── Select2: Kategori ─────────────────────────────────────────────────────
    $('#kategori_id').select2({
        theme: 'bootstrap-5',
        placeholder: '— Pilih Kategori —',
        allowClear: true,
        width: '100%',
    });

    // ── Select2: Tryout ───────────────────────────────────────────────────────
    $('#tryout_id').select2({
        theme: 'bootstrap-5',
        placeholder: '— Tidak ditambahkan ke tryout —',
        allowClear: true,
        width: '100%',
    });

    // ── Toggle tampilan SCORE vs POINT ────────────────────────────────────────
    function updateTipeSoal(tipe) {
        const sectionKunci   = document.getElementById('section_kunci_jawaban');
        const sectionNilai   = document.getElementById('section_nilai_score');
        const tipeSoalHidden = document.getElementById('tipe_soal_hidden');
        const badge          = document.getElementById('tipe_soal_badge');

        if (tipeSoalHidden) tipeSoalHidden.value = tipe || '';

        if (tipe === 'SCORE') {
            // SCORE: tampilkan Kunci Jawaban, sembunyikan Nilai Per Pilihan
            if (sectionKunci) sectionKunci.style.display = '';
            if (sectionNilai) sectionNilai.style.display = 'none';
            if (badge) badge.innerHTML = '<span class="badge bg-info text-dark"><i class="bi bi-check-circle me-1"></i>Tipe: SCORE — pilihan ganda kunci jawaban</span>';
        } else if (tipe === 'POINT') {
            // POINT: tampilkan Nilai Per Pilihan, sembunyikan Kunci Jawaban
            if (sectionKunci) sectionKunci.style.display = 'none';
            if (sectionNilai) sectionNilai.style.display = '';
            document.querySelectorAll('input[name="kunci_jawaban"]').forEach(function(r) { r.required = false; });
            if (badge) badge.innerHTML = '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Tipe: POINT — nilai per pilihan (1–5)</span>';
        } else {
            if (sectionKunci) sectionKunci.style.display = 'none';
            if (sectionNilai) sectionNilai.style.display = 'none';
            if (badge) badge.innerHTML = '';
        }
    }

    // ── Event: saat kategori berubah → baca tipe_soal dari data-tipe ─────────
    $('#kategori_id').on('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const tipe = selectedOption ? (selectedOption.dataset.tipe || '') : '';
        updateTipeSoal(tipe);
    });

    // ── On page load: terapkan tipe dari server (mode edit / old input) ───────
    const initialTipe = document.getElementById('tipe_soal_hidden').value;
    if (initialTipe) {
        updateTipeSoal(initialTipe);
    } else {
        // Baca dari option yang terpilih saat ini
        const sel = document.getElementById('kategori_id');
        if (sel && sel.value) {
            const opt = sel.options[sel.selectedIndex];
            updateTipeSoal(opt ? (opt.dataset.tipe || '') : '');
        }
    }

    // ── Validasi nilai tidak boleh sama ───────────────────────────────────────
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

    // ── Summernote ────────────────────────────────────────────────────────────
    var snConfig = {
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

    $('#pertanyaan').summernote($.extend({}, snConfig, {
        placeholder: 'Tulis pertanyaan di sini...',
        height: 220
    }));

    $('#pembahasan').summernote($.extend({}, snConfig, {
        placeholder: 'Tulis pembahasan jawaban di sini...',
        height: 180
    }));

    $('form').on('submit', function (e) {
        if ($('#pertanyaan').summernote('isEmpty')) {
            e.preventDefault();
            alert('Pertanyaan tidak boleh kosong.');
            $('#pertanyaan').summernote('focus');
            return false;
        }
    });
}());
</script>
<?= $this->endSection() ?>
