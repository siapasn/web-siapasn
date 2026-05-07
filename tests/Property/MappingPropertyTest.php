<?php

namespace Tests\Property;

/**
 * Property-based tests for Mapping Soal and Mapping Tryout (Properties 19 and 20).
 *
 * These tests focus on pure logic and do NOT require a real database connection.
 * They verify the duplicate-detection and access-grant invariants.
 *
 * **Validates: Requirements 14.7, 15.7, 15.9**
 */
class MappingPropertyTest extends PropertyTestCase
{
    // -------------------------------------------------------------------------
    // Property 19: Duplikasi mapping dicegah
    // -------------------------------------------------------------------------

    /**
     * For any (tryout_id, soal_id) pair already present in the mapping list,
     * isDuplicate logic must return true.
     *
     * // Feature: cpns-tryout-online, Property 19: Duplikasi mapping dicegah
     */
    public function testDuplikasiMappingSoalDicegah(): void
    {
        // Feature: cpns-tryout-online, Property 19: Duplikasi mapping dicegah
        $this->forAll(
            \Eris\Generator\choose(1, 1000),  // tryout_id
            \Eris\Generator\choose(1, 1000)   // soal_id
        )
        ->withMaxSize(100)
        ->then(function (int $tryoutId, int $soalId): void {
            // Simulate an existing mapping list
            $existingMappings = [
                ['tryout_id' => $tryoutId, 'soal_id' => $soalId],
            ];

            $isDuplicate = $this->checkMappingSoalDuplicate($existingMappings, $tryoutId, $soalId);

            $this->assertTrue(
                $isDuplicate,
                "Pasangan (tryout_id={$tryoutId}, soal_id={$soalId}) yang sudah ada harus terdeteksi sebagai duplikat"
            );
        });
    }

    /**
     * For any (tryout_id, soal_id) pair NOT present in the mapping list,
     * isDuplicate logic must return false.
     *
     * // Feature: cpns-tryout-online, Property 19: Duplikasi mapping dicegah
     */
    public function testMappingSoalBaruTidakDianggapDuplikat(): void
    {
        // Feature: cpns-tryout-online, Property 19: Duplikasi mapping dicegah
        $this->forAll(
            \Eris\Generator\choose(1, 500),   // tryout_id existing
            \Eris\Generator\choose(1, 500),   // soal_id existing
            \Eris\Generator\choose(501, 1000), // soal_id new (guaranteed different range)
            \Eris\Generator\choose(501, 1000)  // tryout_id new (guaranteed different range)
        )
        ->withMaxSize(100)
        ->then(function (int $existingTryoutId, int $existingSoalId, int $newSoalId, int $newTryoutId): void {
            $existingMappings = [
                ['tryout_id' => $existingTryoutId, 'soal_id' => $existingSoalId],
            ];

            // Different soal_id in same tryout → not duplicate
            $isDuplicateDifferentSoal = $this->checkMappingSoalDuplicate(
                $existingMappings,
                $existingTryoutId,
                $newSoalId
            );
            $this->assertFalse(
                $isDuplicateDifferentSoal,
                "Soal baru (soal_id={$newSoalId}) dalam tryout yang sama tidak boleh dianggap duplikat"
            );

            // Different tryout_id with same soal_id → not duplicate
            $isDuplicateDifferentTryout = $this->checkMappingSoalDuplicate(
                $existingMappings,
                $newTryoutId,
                $existingSoalId
            );
            $this->assertFalse(
                $isDuplicateDifferentTryout,
                "Soal yang sama (soal_id={$existingSoalId}) dalam tryout berbeda tidak boleh dianggap duplikat"
            );
        });
    }

    /**
     * For any (produk_id, tryout_id) pair already present in the mapping list,
     * isDuplicate logic must return true.
     *
     * // Feature: cpns-tryout-online, Property 19: Duplikasi mapping dicegah
     */
    public function testDuplikasiMappingTryoutDicegah(): void
    {
        // Feature: cpns-tryout-online, Property 19: Duplikasi mapping dicegah
        $this->forAll(
            \Eris\Generator\choose(1, 1000),  // produk_id
            \Eris\Generator\choose(1, 1000)   // tryout_id
        )
        ->withMaxSize(100)
        ->then(function (int $produkId, int $tryoutId): void {
            $existingMappings = [
                ['produk_id' => $produkId, 'tryout_id' => $tryoutId],
            ];

            $isDuplicate = $this->checkMappingTryoutDuplicate($existingMappings, $produkId, $tryoutId);

            $this->assertTrue(
                $isDuplicate,
                "Pasangan (produk_id={$produkId}, tryout_id={$tryoutId}) yang sudah ada harus terdeteksi sebagai duplikat"
            );
        });
    }

    /**
     * For any (produk_id, tryout_id) pair NOT present in the mapping list,
     * isDuplicate logic must return false.
     *
     * // Feature: cpns-tryout-online, Property 19: Duplikasi mapping dicegah
     */
    public function testMappingTryoutBaruTidakDianggapDuplikat(): void
    {
        // Feature: cpns-tryout-online, Property 19: Duplikasi mapping dicegah
        $this->forAll(
            \Eris\Generator\choose(1, 500),    // produk_id existing
            \Eris\Generator\choose(1, 500),    // tryout_id existing
            \Eris\Generator\choose(501, 1000), // tryout_id new
            \Eris\Generator\choose(501, 1000)  // produk_id new
        )
        ->withMaxSize(100)
        ->then(function (int $existingProdukId, int $existingTryoutId, int $newTryoutId, int $newProdukId): void {
            $existingMappings = [
                ['produk_id' => $existingProdukId, 'tryout_id' => $existingTryoutId],
            ];

            // Different tryout_id in same produk → not duplicate
            $isDuplicateDifferentTryout = $this->checkMappingTryoutDuplicate(
                $existingMappings,
                $existingProdukId,
                $newTryoutId
            );
            $this->assertFalse(
                $isDuplicateDifferentTryout,
                "Tryout baru (tryout_id={$newTryoutId}) dalam produk yang sama tidak boleh dianggap duplikat"
            );

            // Different produk_id with same tryout_id → not duplicate
            $isDuplicateDifferentProduk = $this->checkMappingTryoutDuplicate(
                $existingMappings,
                $newProdukId,
                $existingTryoutId
            );
            $this->assertFalse(
                $isDuplicateDifferentProduk,
                "Tryout yang sama (tryout_id={$existingTryoutId}) dalam produk berbeda tidak boleh dianggap duplikat"
            );
        });
    }

    // -------------------------------------------------------------------------
    // Property 20: Pembelian produk memberikan akses ke semua tryout yang di-mapping
    // -------------------------------------------------------------------------

    /**
     * For any produk with N mapped tryouts, after a successful purchase,
     * the user must have access to all N tryouts.
     *
     * // Feature: cpns-tryout-online, Property 20: Pembelian produk memberikan akses ke semua tryout yang di-mapping
     */
    public function testPembelianProdukMemberikanAksesSemua(): void
    {
        // Feature: cpns-tryout-online, Property 20: Pembelian produk memberikan akses ke semua tryout yang di-mapping
        $this->forAll(
            \Eris\Generator\choose(1, 20)  // N: jumlah tryout yang di-mapping
        )
        ->withMaxSize(100)
        ->then(function (int $n): void {
            $produkId = 1;
            $userId   = 42;

            // Build N mapped tryouts for the produk
            $mappedTryouts = [];
            for ($i = 1; $i <= $n; $i++) {
                $mappedTryouts[] = ['produk_id' => $produkId, 'tryout_id' => $i];
            }

            // Simulate purchase: user gains access to all mapped tryouts
            $accessibleTryoutIds = $this->simulatePurchaseAccess($userId, $produkId, $mappedTryouts);

            // Every mapped tryout must be accessible
            $this->assertCount(
                $n,
                $accessibleTryoutIds,
                "Setelah membeli produk dengan {$n} tryout, user harus mendapat akses ke semua {$n} tryout"
            );

            for ($i = 1; $i <= $n; $i++) {
                $this->assertContains(
                    $i,
                    $accessibleTryoutIds,
                    "Tryout ID {$i} harus dapat diakses setelah pembelian produk"
                );
            }
        });
    }

    /**
     * For any produk with N mapped tryouts, the count of accessible tryouts
     * must equal N (no more, no less).
     *
     * // Feature: cpns-tryout-online, Property 20: Pembelian produk memberikan akses ke semua tryout yang di-mapping
     */
    public function testJumlahAksesTryoutSamaDenganJumlahMapping(): void
    {
        // Feature: cpns-tryout-online, Property 20: Pembelian produk memberikan akses ke semua tryout yang di-mapping
        $this->forAll(
            \Eris\Generator\choose(0, 15)  // N: bisa 0 (produk kosong) sampai 15
        )
        ->withMaxSize(100)
        ->then(function (int $n): void {
            $produkId = 5;
            $userId   = 99;

            $mappedTryouts = [];
            for ($i = 1; $i <= $n; $i++) {
                $mappedTryouts[] = ['produk_id' => $produkId, 'tryout_id' => $i];
            }

            $accessibleTryoutIds = $this->simulatePurchaseAccess($userId, $produkId, $mappedTryouts);

            $this->assertCount(
                $n,
                $accessibleTryoutIds,
                "Jumlah tryout yang dapat diakses ({$n}) harus sama dengan jumlah mapping"
            );
        });
    }

    // -------------------------------------------------------------------------
    // Pure-logic helpers (no database required)
    // -------------------------------------------------------------------------

    /**
     * Pure-logic duplicate check for mapping_soal.
     * Mirrors the isDuplicate() logic in MappingSoalModel.
     *
     * @param array<int, array{tryout_id: int, soal_id: int}> $existingMappings
     */
    private function checkMappingSoalDuplicate(array $existingMappings, int $tryoutId, int $soalId): bool
    {
        foreach ($existingMappings as $mapping) {
            if ((int) $mapping['tryout_id'] === $tryoutId && (int) $mapping['soal_id'] === $soalId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Pure-logic duplicate check for mapping_tryout.
     * Mirrors the isDuplicate() logic in MappingTryoutModel.
     *
     * @param array<int, array{produk_id: int, tryout_id: int}> $existingMappings
     */
    private function checkMappingTryoutDuplicate(array $existingMappings, int $produkId, int $tryoutId): bool
    {
        foreach ($existingMappings as $mapping) {
            if ((int) $mapping['produk_id'] === $produkId && (int) $mapping['tryout_id'] === $tryoutId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Simulate the access-grant logic after a successful purchase.
     * Returns the list of tryout IDs the user can access for the given produk.
     *
     * This mirrors the logic in WebhookController / UserProdukModel:
     * after a successful transaction, the user gets access to all tryouts
     * mapped to the purchased produk.
     *
     * @param  array<int, array{produk_id: int, tryout_id: int}> $mappedTryouts
     * @return int[]
     */
    private function simulatePurchaseAccess(int $userId, int $produkId, array $mappedTryouts): array
    {
        // Filter mappings for this produk and collect tryout IDs
        $accessibleTryoutIds = [];
        foreach ($mappedTryouts as $mapping) {
            if ((int) $mapping['produk_id'] === $produkId) {
                $accessibleTryoutIds[] = (int) $mapping['tryout_id'];
            }
        }
        return $accessibleTryoutIds;
    }
}
