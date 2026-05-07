<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-play-circle me-2"></i>Konfirmasi Mulai Tryout</h5>
            </div>
            <div class="card-body">

                <!-- Info tryout -->
                <div class="mb-4">
                    <h4 class="fw-bold"><?= esc($tryout['nama']) ?></h4>
                    <div class="row g-3 mt-1">
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 text-muted">
                                <i class="bi bi-clock fs-5 text-primary"></i>
                                <div>
                                    <div class="small text-muted">Durasi</div>
                                    <div class="fw-semibold"><?= (int) $tryout['durasi'] ?> menit</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 text-muted">
                                <i class="bi bi-list-ol fs-5 text-primary"></i>
                                <div>
                                    <div class="small text-muted">Jumlah Soal</div>
                                    <div class="fw-semibold"><?= (int) $tryout['jumlah_soal'] ?> soal</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Peringatan -->
                <div class="alert alert-warning d-flex gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                    <div>
                        <strong>Perhatian:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            <li>Setelah dimulai, timer akan berjalan dan tidak dapat dihentikan.</li>
                            <li>Tryout ini hanya dapat dikerjakan <strong>satu kali</strong>.</li>
                            <li>Jawaban akan tersimpan otomatis setiap kali Anda berpindah soal.</li>
                            <li>Pastikan koneksi internet Anda stabil sebelum memulai.</li>
                        </ul>
                    </div>
                </div>

                <?php if ($sesiAktif): ?>
                    <div class="alert alert-info d-flex gap-2" role="alert">
                        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                        <div>
                            Anda memiliki sesi yang sedang berlangsung untuk tryout ini.
                            Klik <strong>Lanjutkan</strong> untuk melanjutkan dari soal terakhir.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tombol aksi -->
                <div class="d-flex gap-2 mt-3">
                    <a href="<?= base_url('user/tryout') ?>" class="btn btn-outline-secondary flex-grow-1">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                    <form method="post" action="<?= base_url('user/tryout/' . $tryout['id'] . '/start') ?>" class="flex-grow-1">
                        <?= csrf_field() ?>
                        <?php if ($sesiAktif): ?>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-play-circle me-1"></i>Lanjutkan Tryout
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-play-fill me-1"></i>Mulai Sekarang
                            </button>
                        <?php endif; ?>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
