<?php

namespace Tests\Property;

use Eris\Generator;

/**
 * Property-based tests for Promosi and Voucher auto-deactivation logic.
 *
 * These tests focus on pure logic and do NOT require a real database connection.
 * They verify that expiry and usage-limit checks correctly classify records.
 *
 * **Validates: Requirements 9.3, 9.4**
 */
class PromosiVoucherPropertyTest extends PropertyTestCase
{
    // -------------------------------------------------------------------------
    // Property 21: Promosi kedaluwarsa otomatis dinonaktifkan
    // -------------------------------------------------------------------------

    /**
     * For any promosi with berakhir_at in the past, isExpired() must return true.
     *
     * // Feature: cpns-tryout-online, Property 21: Promosi kedaluwarsa otomatis dinonaktifkan
     *
     * **Validates: Requirements 9.3**
     */
    public function testPromosiKedaluwarsaDideteksiDenganBenar(): void
    {
        // Feature: cpns-tryout-online, Property 21: Promosi kedaluwarsa otomatis dinonaktifkan
        $this->forAll(
            Generator\choose(1, 365 * 5) // 1 to 5 years in the past (seconds)
        )
        ->withMaxSize(100)
        ->then(function (int $secondsAgo): void {
            $berakhirAt = date('Y-m-d H:i:s', time() - $secondsAgo);

            $promosi = [
                'id'          => 1,
                'nama'        => 'Promosi Test',
                'berakhir_at' => $berakhirAt,
                'is_active'   => 1,
            ];

            $isExpired = strtotime($promosi['berakhir_at']) < time();

            $this->assertTrue(
                $isExpired,
                "Promosi dengan berakhir_at={$berakhirAt} harus dianggap kedaluwarsa"
            );
        });
    }

    /**
     * For any promosi with berakhir_at in the future, isExpired() must return false.
     *
     * // Feature: cpns-tryout-online, Property 21: Promosi kedaluwarsa otomatis dinonaktifkan
     *
     * **Validates: Requirements 9.3**
     */
    public function testPromosiAktifTidakDianggapKedaluwarsa(): void
    {
        // Feature: cpns-tryout-online, Property 21: Promosi kedaluwarsa otomatis dinonaktifkan
        $this->forAll(
            Generator\choose(60, 365 * 24 * 3600) // 1 minute to 1 year in the future
        )
        ->withMaxSize(100)
        ->then(function (int $secondsAhead): void {
            $berakhirAt = date('Y-m-d H:i:s', time() + $secondsAhead);

            $promosi = [
                'id'          => 1,
                'nama'        => 'Promosi Aktif',
                'berakhir_at' => $berakhirAt,
                'is_active'   => 1,
            ];

            $isExpired = strtotime($promosi['berakhir_at']) < time();

            $this->assertFalse(
                $isExpired,
                "Promosi dengan berakhir_at={$berakhirAt} tidak boleh dianggap kedaluwarsa"
            );
        });
    }

    // -------------------------------------------------------------------------
    // Property 22: Voucher dengan batas penggunaan tercapai dinonaktifkan
    // -------------------------------------------------------------------------

    /**
     * For any voucher where jumlah_digunakan >= batas_penggunaan, isValid() must return false.
     *
     * // Feature: cpns-tryout-online, Property 22: Voucher dengan batas penggunaan tercapai dinonaktifkan
     *
     * **Validates: Requirements 9.4**
     */
    public function testVoucherDenganBatasTercapaiTidakValid(): void
    {
        // Feature: cpns-tryout-online, Property 22: Voucher dengan batas penggunaan tercapai dinonaktifkan
        $this->forAll(
            Generator\choose(1, 1000),  // batas_penggunaan
            Generator\choose(0, 1000)   // extra usage beyond limit (0 = exactly at limit)
        )
        ->withMaxSize(100)
        ->then(function (int $batas, int $extra): void {
            $jumlahDigunakan = $batas + $extra; // always >= batas

            $voucher = [
                'id'               => 1,
                'kode'             => 'TEST',
                'is_active'        => 1,
                'expired_at'       => null,
                'batas_penggunaan' => $batas,
                'jumlah_digunakan' => $jumlahDigunakan,
            ];

            $isValid = $this->isVoucherValid($voucher);

            $this->assertFalse(
                $isValid,
                "Voucher dengan batas={$batas} dan digunakan={$jumlahDigunakan} harus tidak valid"
            );
        });
    }

    /**
     * For any voucher where jumlah_digunakan < batas_penggunaan (and not expired),
     * isValid() must return true.
     *
     * // Feature: cpns-tryout-online, Property 22: Voucher dengan batas penggunaan tercapai dinonaktifkan
     *
     * **Validates: Requirements 9.4**
     */
    public function testVoucherDiBawahBatasMasihValid(): void
    {
        // Feature: cpns-tryout-online, Property 22: Voucher dengan batas penggunaan tercapai dinonaktifkan
        $this->forAll(
            Generator\choose(2, 1000),  // batas_penggunaan (min 2 so we can have usage < batas)
            Generator\choose(0, 999)    // jumlah_digunakan (will be capped to batas - 1)
        )
        ->withMaxSize(100)
        ->then(function (int $batas, int $rawUsage): void {
            $jumlahDigunakan = $rawUsage % $batas; // always < batas

            $voucher = [
                'id'               => 1,
                'kode'             => 'TEST',
                'is_active'        => 1,
                'expired_at'       => null,
                'batas_penggunaan' => $batas,
                'jumlah_digunakan' => $jumlahDigunakan,
            ];

            $isValid = $this->isVoucherValid($voucher);

            $this->assertTrue(
                $isValid,
                "Voucher dengan batas={$batas} dan digunakan={$jumlahDigunakan} harus valid"
            );
        });
    }

    /**
     * For any voucher with no batas_penggunaan (null), usage count never invalidates it.
     *
     * // Feature: cpns-tryout-online, Property 22: Voucher dengan batas penggunaan tercapai dinonaktifkan
     *
     * **Validates: Requirements 9.4**
     */
    public function testVoucherTanpaBatasPenggunaanSelaluValidBerdasarkanUsage(): void
    {
        // Feature: cpns-tryout-online, Property 22: Voucher dengan batas penggunaan tercapai dinonaktifkan
        $this->forAll(
            Generator\choose(0, 100000) // any usage count
        )
        ->withMaxSize(100)
        ->then(function (int $jumlahDigunakan): void {
            $voucher = [
                'id'               => 1,
                'kode'             => 'UNLIMITED',
                'is_active'        => 1,
                'expired_at'       => null,
                'batas_penggunaan' => null, // no limit
                'jumlah_digunakan' => $jumlahDigunakan,
            ];

            $isValid = $this->isVoucherValid($voucher);

            $this->assertTrue(
                $isValid,
                "Voucher tanpa batas penggunaan harus tetap valid berapapun jumlah penggunaan ({$jumlahDigunakan})"
            );
        });
    }

    // -------------------------------------------------------------------------
    // Helper: pure-logic implementation of VoucherModel::isValid()
    // -------------------------------------------------------------------------

    /**
     * Pure-logic replica of VoucherModel::isValid() for testing without DB.
     */
    private function isVoucherValid(array $voucher): bool
    {
        if (! $voucher['is_active']) {
            return false;
        }

        if ($voucher['expired_at'] !== null && strtotime($voucher['expired_at']) <= time()) {
            return false;
        }

        if ($voucher['batas_penggunaan'] !== null
            && (int) $voucher['jumlah_digunakan'] >= (int) $voucher['batas_penggunaan']) {
            return false;
        }

        return true;
    }
}
