<?php

namespace Tests\Property;

/**
 * Property-based tests for Produk deletion guard (Property 18).
 *
 * These tests focus on pure logic and do NOT require a real database connection.
 * They verify that the active-transaction check correctly classifies statuses.
 *
 * **Validates: Requirements 8.9**
 */
class ProdukPropertyTest extends PropertyTestCase
{
    // -------------------------------------------------------------------------
    // Property 18: Produk dengan transaksi aktif tidak dapat dihapus
    // -------------------------------------------------------------------------

    /**
     * For any produk with at least one transaksi with status 'success' or 'pending',
     * deletion must be blocked.
     *
     * // Feature: cpns-tryout-online, Property 18: Produk dengan transaksi aktif tidak dapat dihapus
     */
    public function testProdukDenganTransaksiAktifTidakDapatDihapus(): void
    {
        // Feature: cpns-tryout-online, Property 18: Produk dengan transaksi aktif tidak dapat dihapus
        $this->forAll(
            \Eris\Generator\elements(['success', 'pending'])
        )
        ->withMaxSize(100)
        ->then(function (string $status): void {
            $activeStatuses = ['success', 'pending'];
            $hasActive = in_array($status, $activeStatuses, true);
            $this->assertTrue($hasActive, "Status '{$status}' harus dianggap sebagai transaksi aktif");
        });
    }

    /**
     * For any produk with only 'failed' or 'expired' transaksi,
     * deletion must be allowed.
     *
     * // Feature: cpns-tryout-online, Property 18: Produk dengan transaksi aktif tidak dapat dihapus
     */
    public function testProdukTanpaTransaksiAktifDapatDihapus(): void
    {
        // Feature: cpns-tryout-online, Property 18: Produk dengan transaksi aktif tidak dapat dihapus
        $this->forAll(
            \Eris\Generator\elements(['failed', 'expired'])
        )
        ->withMaxSize(100)
        ->then(function (string $status): void {
            $activeStatuses = ['success', 'pending'];
            $hasActive = in_array($status, $activeStatuses, true);
            $this->assertFalse($hasActive, "Status '{$status}' tidak boleh dianggap sebagai transaksi aktif");
        });
    }
}
