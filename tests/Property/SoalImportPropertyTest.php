<?php

namespace Tests\Property;

use App\Controllers\Admin\Master\SoalController;

/**
 * Property-based tests for Soal Import Validation.
 *
 * These tests focus on pure row-validation logic (no DB required).
 * They verify correctness properties of the import validation rules.
 *
 * **Validates: Requirements 8.10, 8.11**
 */
class SoalImportPropertyTest extends PropertyTestCase
{
    /**
     * Valid kategori IDs used as a stand-in for "IDs that exist in DB".
     */
    private array $validKategoriIds = [1, 2, 3, 4, 5];

    /**
     * Valid kunci jawaban values.
     */
    private array $validKunci = ['a', 'b', 'c', 'd', 'e'];

    // -------------------------------------------------------------------------
    // Property: Validasi kunci jawaban harus a/b/c/d/e
    // -------------------------------------------------------------------------

    /**
     * For any kunci_jawaban value, only a/b/c/d/e (lowercase) are valid.
     * Any other value must produce a validation error.
     *
     * // Feature: cpns-tryout-online, Property: Validasi file impor soal — kunci jawaban
     */
    public function testKunciJawabanHarusABCDE(): void
    {
        // Feature: cpns-tryout-online, Property: Validasi file impor soal — kunci jawaban
        $this->forAll(
            \Eris\Generator\elements(['a', 'b', 'c', 'd', 'e'])
        )
        ->withMaxSize(100)
        ->then(function (string $kunci): void {
            $cols = $this->buildValidCols();
            $cols[7] = $kunci; // kolom H = kunci_jawaban

            $errors = SoalController::validateImportRow($cols, 1, $this->validKategoriIds, $this->validKunci);

            $this->assertEmpty(
                $errors,
                "Kunci jawaban '{$kunci}' seharusnya valid, tetapi menghasilkan error: " . implode(', ', $errors)
            );
        });
    }

    /**
     * For any kunci_jawaban value outside a/b/c/d/e, validation must fail.
     *
     * // Feature: cpns-tryout-online, Property: Validasi file impor soal — kunci jawaban tidak valid
     */
    public function testKunciJawabanTidakValidDitolak(): void
    {
        // Feature: cpns-tryout-online, Property: Validasi file impor soal — kunci jawaban tidak valid
        $this->forAll(
            // Uppercase A-E are normalised to lowercase by the validator, so they ARE valid.
            // Only truly invalid values (outside a-e after lowercasing) should be tested here.
            \Eris\Generator\elements(['f', 'g', 'x', 'z', '1', '2', '', ' ', 'ab', 'aa', 'ff'])
        )
        ->withMaxSize(100)
        ->then(function (string $kunci): void {
            $cols = $this->buildValidCols();
            $cols[7] = $kunci; // kolom H = kunci_jawaban

            $errors = SoalController::validateImportRow($cols, 1, $this->validKategoriIds, $this->validKunci);

            $this->assertNotEmpty(
                $errors,
                "Kunci jawaban '{$kunci}' seharusnya tidak valid, tetapi tidak menghasilkan error."
            );
        });
    }

    // -------------------------------------------------------------------------
    // Property: Pertanyaan tidak boleh kosong
    // -------------------------------------------------------------------------

    /**
     * For any empty pertanyaan value, validation must fail.
     *
     * // Feature: cpns-tryout-online, Property: Validasi file impor soal — pertanyaan kosong
     */
    public function testPertanyaanTidakBolehKosong(): void
    {
        // Feature: cpns-tryout-online, Property: Validasi file impor soal — pertanyaan kosong
        $this->forAll(
            \Eris\Generator\elements(['', '   ', "\t", "\n", "  \t  "])
        )
        ->withMaxSize(100)
        ->then(function (string $pertanyaan): void {
            $cols = $this->buildValidCols();
            $cols[1] = $pertanyaan; // kolom B = pertanyaan

            $errors = SoalController::validateImportRow($cols, 2, $this->validKategoriIds, $this->validKunci);

            $this->assertNotEmpty(
                $errors,
                "Pertanyaan kosong/whitespace seharusnya menghasilkan error validasi."
            );
        });
    }

    /**
     * For any non-empty pertanyaan, the pertanyaan field itself must not cause an error.
     *
     * // Feature: cpns-tryout-online, Property: Validasi file impor soal — pertanyaan valid
     */
    public function testPertanyaanTidakKosongLolosValidasi(): void
    {
        // Feature: cpns-tryout-online, Property: Validasi file impor soal — pertanyaan valid
        $this->forAll(
            \Eris\Generator\elements([
                'Apa ibu kota Indonesia?',
                'Siapakah presiden pertama RI?',
                'Berapa hasil dari 2 + 2?',
                'Manakah yang termasuk nilai Pancasila?',
                'Tes soal nomor 5',
            ])
        )
        ->withMaxSize(100)
        ->then(function (string $pertanyaan): void {
            $cols = $this->buildValidCols();
            $cols[1] = $pertanyaan;

            $errors = SoalController::validateImportRow($cols, 1, $this->validKategoriIds, $this->validKunci);

            // Pertanyaan yang valid tidak boleh menghasilkan error terkait pertanyaan
            $pertanyaanErrors = array_filter($errors, fn($e) => stripos($e, 'pertanyaan') !== false);
            $this->assertEmpty(
                $pertanyaanErrors,
                "Pertanyaan non-kosong '{$pertanyaan}' seharusnya tidak menghasilkan error pertanyaan."
            );
        });
    }

    // -------------------------------------------------------------------------
    // Property: Pilihan A/B/C/D wajib diisi
    // -------------------------------------------------------------------------

    /**
     * For any row missing pilihan_a, validation must fail.
     *
     * // Feature: cpns-tryout-online, Property: Validasi file impor soal — pilihan wajib
     */
    public function testPilihanWajibABCD(): void
    {
        // Feature: cpns-tryout-online, Property: Validasi file impor soal — pilihan wajib
        $this->forAll(
            \Eris\Generator\elements([
                [2, ''],   // pilihan_a kosong
                [3, ''],   // pilihan_b kosong
                [4, ''],   // pilihan_c kosong
                [5, ''],   // pilihan_d kosong
            ])
        )
        ->withMaxSize(100)
        ->then(function (array $emptyField): void {
            [$colIndex, $emptyValue] = $emptyField;

            $cols = $this->buildValidCols();
            $cols[$colIndex] = $emptyValue;

            $errors = SoalController::validateImportRow($cols, 3, $this->validKategoriIds, $this->validKunci);

            $this->assertNotEmpty(
                $errors,
                "Kolom pilihan index {$colIndex} yang kosong seharusnya menghasilkan error validasi."
            );
        });
    }

    /**
     * For any row with all pilihan_a/b/c/d filled, those fields must not cause errors.
     *
     * // Feature: cpns-tryout-online, Property: Validasi file impor soal — semua pilihan terisi
     */
    public function testSemuaPilihanTerisiLolosValidasi(): void
    {
        // Feature: cpns-tryout-online, Property: Validasi file impor soal — semua pilihan terisi
        $this->forAll(
            \Eris\Generator\elements(['Pilihan satu', 'Jawaban A', 'Opsi pertama', 'Benar', 'Salah'])
        )
        ->withMaxSize(100)
        ->then(function (string $pilihanValue): void {
            $cols = $this->buildValidCols();
            $cols[2] = $pilihanValue; // pilihan_a
            $cols[3] = $pilihanValue; // pilihan_b
            $cols[4] = $pilihanValue; // pilihan_c
            $cols[5] = $pilihanValue; // pilihan_d

            $errors = SoalController::validateImportRow($cols, 1, $this->validKategoriIds, $this->validKunci);

            // Tidak boleh ada error terkait pilihan
            $pilihanErrors = array_filter($errors, fn($e) => stripos($e, 'pilihan') !== false);
            $this->assertEmpty(
                $pilihanErrors,
                "Semua pilihan terisi seharusnya tidak menghasilkan error pilihan."
            );
        });
    }

    // -------------------------------------------------------------------------
    // Property: Error harus menyertakan nomor baris
    // -------------------------------------------------------------------------

    /**
     * For any invalid row, the error message must identify the row number.
     *
     * // Feature: cpns-tryout-online, Property: Validasi file impor soal — error menyertakan nomor baris
     */
    public function testRowValidasiMenghasilkanErrorYangTepat(): void
    {
        // Feature: cpns-tryout-online, Property: Validasi file impor soal — error menyertakan nomor baris
        $this->forAll(
            \Eris\Generator\choose(2, 1000) // nomor baris acak (mulai dari 2 karena baris 1 = header)
        )
        ->withMaxSize(100)
        ->then(function (int $rowNum): void {
            // Buat baris yang pasti tidak valid: pertanyaan kosong
            $cols = $this->buildValidCols();
            $cols[1] = ''; // pertanyaan kosong

            $errors = SoalController::validateImportRow($cols, $rowNum, $this->validKategoriIds, $this->validKunci);

            $this->assertNotEmpty($errors, "Baris tidak valid harus menghasilkan setidaknya satu error.");

            // Setiap pesan error harus menyebutkan nomor baris
            foreach ($errors as $errorMsg) {
                $this->assertStringContainsString(
                    (string) $rowNum,
                    $errorMsg,
                    "Pesan error harus menyertakan nomor baris {$rowNum}. Pesan: '{$errorMsg}'"
                );
            }
        });
    }

    /**
     * For any combination of invalid fields, each error message must reference the row number.
     *
     * // Feature: cpns-tryout-online, Property: Validasi file impor soal — semua error menyertakan nomor baris
     */
    public function testSetiapErrorMenyertakanNomorBaris(): void
    {
        // Feature: cpns-tryout-online, Property: Validasi file impor soal — semua error menyertakan nomor baris
        $this->forAll(
            \Eris\Generator\choose(2, 500)
        )
        ->withMaxSize(100)
        ->then(function (int $rowNum): void {
            // Baris dengan banyak field tidak valid
            $cols = [
                '',  // A: kategori_id kosong
                '',  // B: pertanyaan kosong
                '',  // C: pilihan_a kosong
                '',  // D: pilihan_b kosong
                '',  // E: pilihan_c kosong
                '',  // F: pilihan_d kosong
                '',  // G: pilihan_e (opsional)
                'z', // H: kunci_jawaban tidak valid
                '',  // I: pembahasan (opsional)
            ];

            $errors = SoalController::validateImportRow($cols, $rowNum, $this->validKategoriIds, $this->validKunci);

            $this->assertNotEmpty($errors, "Baris dengan semua field tidak valid harus menghasilkan error.");

            foreach ($errors as $errorMsg) {
                $this->assertStringContainsString(
                    "Baris {$rowNum}",
                    $errorMsg,
                    "Setiap pesan error harus diawali dengan 'Baris {$rowNum}'. Pesan: '{$errorMsg}'"
                );
            }
        });
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    /**
     * Bangun array kolom yang valid untuk digunakan sebagai dasar pengujian.
     * Kolom: A=kategori_id, B=pertanyaan, C=pilihan_a, D=pilihan_b,
     *        E=pilihan_c, F=pilihan_d, G=pilihan_e, H=kunci_jawaban, I=pembahasan
     */
    private function buildValidCols(): array
    {
        return [
            '1',                                    // A: kategori_id (valid)
            'Apa ibu kota Indonesia?',              // B: pertanyaan
            'Jakarta',                              // C: pilihan_a
            'Bandung',                              // D: pilihan_b
            'Surabaya',                             // E: pilihan_c
            'Medan',                                // F: pilihan_d
            'Yogyakarta',                           // G: pilihan_e (opsional)
            'a',                                    // H: kunci_jawaban
            'Jakarta adalah ibu kota Indonesia.',   // I: pembahasan
        ];
    }
}
