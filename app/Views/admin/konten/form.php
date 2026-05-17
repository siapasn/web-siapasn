<?= $this->extend('layouts/admin') ?>

<?php $isEdit = !empty($konten); ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-file-richtext"></i></div>
        <div>
            <div class="ph-title"><?= $isEdit ? 'Edit Konten' : 'Tambah Konten' ?></div>
            <div class="ph-subtitle">Web Content</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/konten') ?>" class="ph-action" style="background:transparent; border:1px solid rgba(255,255,255,.4); color:#fff;">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
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

<?php
$protected = [
    'syarat-ketentuan', 'kebijakan-privasi', 'hubungi-kami',
    'kontak_email', 'kontak_whatsapp', 'kontak_alamat',
    'hero_tagline', 'hero_deskripsi',
    'stat_pengguna', 'stat_soal', 'stat_paket',
];
$isProtected = $isEdit && in_array($konten['slug'] ?? '', $protected, true);
$currentTipe = old('tipe', $konten['tipe'] ?? 'halaman');
?>

<?php if ($isProtected): ?>
    <div class="alert alert-warning border-0 d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-lock-fill"></i>
        <div style="font-size:.875rem;">
            Ini adalah <strong>konten sistem</strong>. Slug tidak dapat diubah, namun isi konten bisa diedit.
        </div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post"
              action="<?= $isEdit ? base_url("admin/konten/{$konten['id']}/update") : base_url('admin/konten/store') ?>">
            <?= csrf_field() ?>

            <div class="row g-3">

                <!-- Judul -->
                <div class="col-12">
                    <label for="judul" class="form-label fw-semibold">
                        Judul <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="judul" name="judul" class="form-control"
                           value="<?= esc(old('judul', $konten['judul'] ?? '')) ?>"
                           placeholder="Contoh: Syarat dan Ketentuan" required>
                    <div class="form-text">Judul yang ditampilkan di halaman publik.</div>
                </div>

                <!-- Slug -->
                <div class="col-12 col-md-6">
                    <label for="slug" class="form-label fw-semibold">
                        Slug <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="slug" name="slug" class="form-control font-monospace"
                           value="<?= esc(old('slug', $konten['slug'] ?? '')) ?>"
                           placeholder="contoh: syarat-ketentuan"
                           <?= $isProtected ? 'readonly' : '' ?> required>
                    <div class="form-text">
                        Identifier unik, gunakan huruf kecil dan tanda hubung.
                        <?php if ($isProtected): ?>
                            <span class="text-warning"><i class="bi bi-lock me-1"></i>Slug sistem tidak dapat diubah.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tipe -->
                <div class="col-12 col-md-6">
                    <label for="tipe" class="form-label fw-semibold">
                        Tipe Konten <span class="text-danger">*</span>
                    </label>
                    <select id="tipe" name="tipe" class="form-select"
                            <?= $isProtected ? 'disabled' : '' ?> required>
                        <option value="halaman" <?= $currentTipe === 'halaman' ? 'selected' : '' ?>>
                            Halaman (HTML — editor teks kaya)
                        </option>
                        <option value="teks" <?= $currentTipe === 'teks' ? 'selected' : '' ?>>
                            Teks (plain text — satu baris)
                        </option>
                        <option value="angka" <?= $currentTipe === 'angka' ? 'selected' : '' ?>>
                            Angka (nilai numerik / statistik)
                        </option>
                    </select>
                    <?php if ($isProtected): ?>
                        <input type="hidden" name="tipe" value="<?= esc($konten['tipe']) ?>">
                    <?php endif; ?>
                    <div class="form-text">
                        <strong>halaman</strong> = konten HTML panjang (Syarat, Privasi, dll.) |
                        <strong>teks</strong> = teks pendek (email, WA, alamat) |
                        <strong>angka</strong> = statistik (jumlah pengguna, soal, dll.)
                    </div>
                </div>

                <!-- Konten — ditampilkan sesuai tipe -->
                <div class="col-12" id="fieldKontenHalaman" style="<?= $currentTipe !== 'halaman' ? 'display:none' : '' ?>">
                    <label for="konten_html" class="form-label fw-semibold">Isi Konten</label>
                    <textarea id="konten_html" name="konten" class="form-control summernote-editor"
                              rows="12"><?= old('konten', $konten['konten'] ?? '') ?></textarea>
                    <div class="form-text">Gunakan editor di atas untuk memformat teks, menambah gambar, dll.</div>
                </div>

                <div class="col-12" id="fieldKontenTeks" style="<?= $currentTipe !== 'teks' ? 'display:none' : '' ?>">
                    <label for="konten_teks" class="form-label fw-semibold">Nilai Teks</label>
                    <input type="text" id="konten_teks" class="form-control"
                           placeholder="Contoh: info@siapasn.id"
                           value="<?= $currentTipe === 'teks' ? esc(old('konten', $konten['konten'] ?? '')) : '' ?>">
                    <div class="form-text">Teks singkat yang akan ditampilkan langsung.</div>
                </div>

                <div class="col-12 col-md-4" id="fieldKontenAngka" style="<?= $currentTipe !== 'angka' ? 'display:none' : '' ?>">
                    <label for="konten_angka" class="form-label fw-semibold">Nilai Angka</label>
                    <input type="text" id="konten_angka" class="form-control"
                           placeholder="Contoh: 10.000+"
                           value="<?= $currentTipe === 'angka' ? esc(old('konten', $konten['konten'] ?? '')) : '' ?>">
                    <div class="form-text">Bisa berupa angka murni atau dengan satuan, misal: <code>10.000+</code></div>
                </div>

                <!-- Hidden field konten yang dikirim -->
                <input type="hidden" id="kontenHidden" name="konten_final">

                <!-- Status Aktif -->
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_active" name="is_active" value="1"
                               <?= old('is_active', $konten['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <div class="form-text">Konten yang tidak aktif tidak akan ditampilkan di halaman publik.</div>
                </div>

            </div><!-- /.row -->

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Konten' ?>
                </button>
                <a href="<?= base_url('admin/konten') ?>" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<script>
// Tampilkan field konten sesuai tipe yang dipilih
const tipeSelect   = document.getElementById('tipe');
const fieldHalaman = document.getElementById('fieldKontenHalaman');
const fieldTeks    = document.getElementById('fieldKontenTeks');
const fieldAngka   = document.getElementById('fieldKontenAngka');
const kontenInput  = document.querySelector('textarea[name="konten"]');

function switchTipe(tipe) {
    fieldHalaman.style.display = tipe === 'halaman' ? '' : 'none';
    fieldTeks.style.display    = tipe === 'teks'    ? '' : 'none';
    fieldAngka.style.display   = tipe === 'angka'   ? '' : 'none';
}

if (tipeSelect) {
    tipeSelect.addEventListener('change', function () {
        switchTipe(this.value);
    });
}

// Sebelum submit: salin nilai dari field aktif ke textarea name="konten"
document.querySelector('form').addEventListener('submit', function () {
    const tipe = (tipeSelect ? tipeSelect.value : '<?= esc($konten['tipe'] ?? 'halaman') ?>');
    if (tipe === 'teks') {
        kontenInput.value = document.getElementById('konten_teks').value;
    } else if (tipe === 'angka') {
        kontenInput.value = document.getElementById('konten_angka').value;
    }
    // Untuk 'halaman', Summernote sudah mengisi textarea secara otomatis
});

// Inisialisasi Summernote
$(document).ready(function () {
    $('.summernote-editor').summernote({
        height: 350,
        lang: 'id-ID',
        toolbar: [
            ['style',  ['style']],
            ['font',   ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['color',  ['color']],
            ['para',   ['ul', 'ol', 'paragraph']],
            ['table',  ['table']],
            ['insert', ['link', 'hr']],
            ['view',   ['fullscreen', 'codeview', 'help']],
        ],
    });
});
</script>

<?= $this->endSection() ?>
