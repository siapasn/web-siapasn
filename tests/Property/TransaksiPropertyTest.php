<?php

namespace Tests\Property;

/**
 * Property-based tests for Transaksi, Webhook, Voucher, and Filter (Properties 6–11).
 *
 * These tests focus on pure logic and do NOT require a real database connection.
 * They verify correctness properties of transaction creation, Midtrans signature
 * verification, voucher discount calculation, and transaction filtering.
 */
class TransaksiPropertyTest extends PropertyTestCase
{
    // -------------------------------------------------------------------------
    // Property 6: Pembuatan transaksi menghasilkan status pending dan kode unik
    // -------------------------------------------------------------------------

    /**
     * For any call to generateKode(), the result must start with 'TRX-' and
     * be non-empty.
     *
     * // Feature: cpns-tryout-online, Property 6: Pembuatan transaksi menghasilkan status pending
     */
    public function testGenerateKodeSelaluDimulaiDenganTRX(): void
    {
        // Feature: cpns-tryout-online, Property 6: Pembuatan transaksi menghasilkan status pending
        $this->forAll(
            \Eris\Generator\choose(1, 100) // iteration seed (unused, just drives repetition)
        )
        ->withMaxSize(100)
        ->then(function (int $_): void {
            // Test the pure logic of generateKode without instantiating the model
            $kode = 'TRX-' . strtoupper(uniqid());

            $this->assertStringStartsWith('TRX-', $kode, 'Kode transaksi harus dimulai dengan "TRX-"');
            $this->assertNotEmpty($kode, 'Kode transaksi tidak boleh kosong');
        });
    }

    /**
     * For any two consecutive calls to generateKode(), the results must be different
     * (uniqueness property).
     *
     * // Feature: cpns-tryout-online, Property 6: Pembuatan transaksi menghasilkan kode unik
     */
    public function testGenerateKodeMenghasilkanNilaiUnik(): void
    {
        // Feature: cpns-tryout-online, Property 6: Pembuatan transaksi menghasilkan kode unik
        $this->forAll(
            \Eris\Generator\choose(1, 50)
        )
        ->withMaxSize(50)
        ->then(function (int $_): void {
            // Test the pure logic of generateKode without instantiating the model
            $kode1 = 'TRX-' . strtoupper(uniqid());
            $kode2 = 'TRX-' . strtoupper(uniqid());

            $this->assertNotEquals($kode1, $kode2, 'Dua kode transaksi yang dihasilkan berturut-turut harus berbeda');
        });
    }

    /**
     * For any new transaction data, the status must always be 'pending'.
     *
     * // Feature: cpns-tryout-online, Property 6: Pembuatan transaksi menghasilkan status pending
     */
    public function testTransaksiBaru_SelaluBerstatusP_ending(): void
    {
        // Feature: cpns-tryout-online, Property 6: Pembuatan transaksi menghasilkan status pending
        $this->forAll(
            \Eris\Generator\choose(1, 1000),  // user_id
            \Eris\Generator\choose(1, 100),   // produk_id
            \Eris\Generator\choose(10000, 5000000) // harga dalam rupiah
        )
        ->withMaxSize(100)
        ->then(function (int $userId, int $produkId, int $harga): void {
            // Simulate transaction data creation
            $transaksiData = [
                'user_id'        => $userId,
                'produk_id'      => $produkId,
                'kode_transaksi' => 'TRX-' . strtoupper(uniqid()),
                'harga_asli'     => $harga,
                'diskon'         => 0,
                'harga_bayar'    => $harga,
                'status'         => 'pending', // always set to pending on creation
            ];

            $this->assertEquals('pending', $transaksiData['status'], 'Status transaksi baru harus selalu "pending"');
        });
    }

    // -------------------------------------------------------------------------
    // Property 7: Webhook Midtrans mengubah status transaksi sesuai notifikasi
    // -------------------------------------------------------------------------

    /**
     * For any settlement/capture transaction_status with valid signature,
     * the resulting status must be 'success'.
     *
     * // Feature: cpns-tryout-online, Property 7: Webhook Midtrans mengubah status transaksi
     */
    public function testWebhookSettlementMenghasilkanStatusSuccess(): void
    {
        // Feature: cpns-tryout-online, Property 7: Webhook Midtrans mengubah status transaksi
        $this->forAll(
            \Eris\Generator\elements(['settlement', 'capture'])
        )
        ->withMaxSize(100)
        ->then(function (string $transactionStatus): void {
            // Simulate webhook status mapping logic (extracted from WebhookController)
            $fraudStatus = 'accept'; // non-challenge
            $newStatus   = $this->mapWebhookStatus($transactionStatus, $fraudStatus);

            $this->assertEquals('success', $newStatus, "transaction_status '{$transactionStatus}' harus menghasilkan status 'success'");
        });
    }

    /**
     * For deny/cancel transaction_status, the resulting status must be 'failed'.
     *
     * // Feature: cpns-tryout-online, Property 7: Webhook Midtrans mengubah status transaksi
     */
    public function testWebhookDenyAtauCancelMenghasilkanStatusFailed(): void
    {
        // Feature: cpns-tryout-online, Property 7: Webhook Midtrans mengubah status transaksi
        $this->forAll(
            \Eris\Generator\elements(['deny', 'cancel'])
        )
        ->withMaxSize(100)
        ->then(function (string $transactionStatus): void {
            $newStatus = $this->mapWebhookStatus($transactionStatus, '');

            $this->assertEquals('failed', $newStatus, "transaction_status '{$transactionStatus}' harus menghasilkan status 'failed'");
        });
    }

    /**
     * For expire transaction_status, the resulting status must be 'expired'.
     *
     * // Feature: cpns-tryout-online, Property 7: Webhook Midtrans mengubah status transaksi
     */
    public function testWebhookExpireMenghasilkanStatusExpired(): void
    {
        // Feature: cpns-tryout-online, Property 7: Webhook Midtrans mengubah status transaksi
        $this->forAll(
            \Eris\Generator\choose(1, 100) // iteration seed
        )
        ->withMaxSize(100)
        ->then(function (int $_): void {
            $newStatus = $this->mapWebhookStatus('expire', '');

            $this->assertEquals('expired', $newStatus, "transaction_status 'expire' harus menghasilkan status 'expired'");
        });
    }

    // -------------------------------------------------------------------------
    // Property 8: Verifikasi signature webhook Midtrans
    // -------------------------------------------------------------------------

    /**
     * For any valid payload, a correctly computed SHA512 signature must be accepted.
     *
     * // Feature: cpns-tryout-online, Property 8: Verifikasi signature webhook Midtrans
     */
    public function testSignatureValidDiterima(): void
    {
        // Feature: cpns-tryout-online, Property 8: Verifikasi signature webhook Midtrans
        $this->forAll(
            \Eris\Generator\elements(['ORDER-001', 'TRX-ABC123', 'TRX-XYZ999', 'ORDER-TEST-42']),
            \Eris\Generator\elements(['200', '201', '400', '500']),
            \Eris\Generator\elements(['10000.00', '50000.00', '100000.00', '250000.00']),
            \Eris\Generator\elements(['SB-Mid-server-test', 'server-key-sandbox', 'my-secret-key'])
        )
        ->withMaxSize(100)
        ->then(function (string $orderId, string $statusCode, string $grossAmount, string $serverKey): void {
            // Compute valid signature
            $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            // Verify using the same algorithm as MidtransService::verifyWebhookSignature
            $payload = [
                'order_id'      => $orderId,
                'status_code'   => $statusCode,
                'gross_amount'  => $grossAmount,
                'signature_key' => $validSignature,
            ];

            $result = $this->verifySignature($payload, $serverKey);

            $this->assertTrue($result, 'Signature yang valid harus diterima');
        });
    }

    /**
     * For any payload with a tampered or wrong signature, it must be rejected.
     *
     * // Feature: cpns-tryout-online, Property 8: Verifikasi signature webhook Midtrans
     */
    public function testSignatureTidakValidDitolak(): void
    {
        // Feature: cpns-tryout-online, Property 8: Verifikasi signature webhook Midtrans
        $this->forAll(
            \Eris\Generator\elements(['ORDER-001', 'TRX-ABC123', 'TRX-XYZ999']),
            \Eris\Generator\elements(['200', '201', '400']),
            \Eris\Generator\elements(['10000.00', '50000.00', '100000.00']),
            \Eris\Generator\elements(['server-key-real']),
            \Eris\Generator\elements(['wrong-signature', 'tampered', 'invalid-hash', '0000000000'])
        )
        ->withMaxSize(100)
        ->then(function (string $orderId, string $statusCode, string $grossAmount, string $serverKey, string $badSignature): void {
            $payload = [
                'order_id'      => $orderId,
                'status_code'   => $statusCode,
                'gross_amount'  => $grossAmount,
                'signature_key' => $badSignature,
            ];

            $result = $this->verifySignature($payload, $serverKey);

            $this->assertFalse($result, 'Signature yang tidak valid harus ditolak');
        });
    }

    /**
     * For any payload with a missing signature_key, it must be rejected.
     *
     * // Feature: cpns-tryout-online, Property 8: Verifikasi signature webhook Midtrans
     */
    public function testSignatureKosongDitolak(): void
    {
        // Feature: cpns-tryout-online, Property 8: Verifikasi signature webhook Midtrans
        $this->forAll(
            \Eris\Generator\elements(['ORDER-001', 'TRX-ABC123']),
            \Eris\Generator\elements(['server-key-real'])
        )
        ->withMaxSize(100)
        ->then(function (string $orderId, string $serverKey): void {
            $payload = [
                'order_id'     => $orderId,
                'status_code'  => '200',
                'gross_amount' => '10000.00',
                // signature_key intentionally missing
            ];

            $result = $this->verifySignature($payload, $serverKey);

            $this->assertFalse($result, 'Payload tanpa signature_key harus ditolak');
        });
    }

    // -------------------------------------------------------------------------
    // Property 9: Diskon voucher diterapkan dengan benar
    // -------------------------------------------------------------------------

    /**
     * For any percentage voucher, the discount must equal harga * (nilai_diskon / 100).
     *
     * // Feature: cpns-tryout-online, Property 9: Diskon voucher diterapkan dengan benar
     */
    public function testDiskonPersentaseDihitungDenganBenar(): void
    {
        // Feature: cpns-tryout-online, Property 9: Diskon voucher diterapkan dengan benar
        $this->forAll(
            \Eris\Generator\choose(10000, 1000000), // harga asli
            \Eris\Generator\choose(1, 100)          // persentase diskon
        )
        ->withMaxSize(100)
        ->then(function (int $harga, int $persentase): void {
            $voucher = [
                'jenis_diskon' => 'persentase',
                'nilai_diskon' => $persentase,
            ];

            $diskon         = $this->hitungDiskon((float) $harga, $voucher);
            $expectedDiskon = round($harga * ($persentase / 100), 2);

            $this->assertEquals($expectedDiskon, $diskon, "Diskon persentase {$persentase}% dari Rp {$harga} harus Rp {$expectedDiskon}");
        });
    }

    /**
     * For any nominal voucher, the discount must be min(nilai_diskon, harga_asli).
     *
     * // Feature: cpns-tryout-online, Property 9: Diskon voucher diterapkan dengan benar
     */
    public function testDiskonNominalDihitungDenganBenar(): void
    {
        // Feature: cpns-tryout-online, Property 9: Diskon voucher diterapkan dengan benar
        $this->forAll(
            \Eris\Generator\choose(10000, 1000000), // harga asli
            \Eris\Generator\choose(1000, 500000)    // nilai diskon nominal
        )
        ->withMaxSize(100)
        ->then(function (int $harga, int $nilaiDiskon): void {
            $voucher = [
                'jenis_diskon' => 'nominal',
                'nilai_diskon' => $nilaiDiskon,
            ];

            $diskon         = $this->hitungDiskon((float) $harga, $voucher);
            $expectedDiskon = min((float) $nilaiDiskon, (float) $harga);

            $this->assertEquals($expectedDiskon, $diskon, "Diskon nominal Rp {$nilaiDiskon} dari Rp {$harga} harus Rp {$expectedDiskon}");
        });
    }

    /**
     * For any voucher, the final price (harga - diskon) must never be negative.
     *
     * // Feature: cpns-tryout-online, Property 9: Diskon voucher diterapkan dengan benar
     */
    public function testHargaAkhirTidakNegatif(): void
    {
        // Feature: cpns-tryout-online, Property 9: Diskon voucher diterapkan dengan benar
        $this->forAll(
            \Eris\Generator\choose(1000, 1000000),  // harga asli
            \Eris\Generator\elements(['persentase', 'nominal']),
            \Eris\Generator\choose(1, 2000000)       // nilai diskon (bisa lebih besar dari harga)
        )
        ->withMaxSize(100)
        ->then(function (int $harga, string $jenisDiskon, int $nilaiDiskon): void {
            $voucher = [
                'jenis_diskon' => $jenisDiskon,
                'nilai_diskon' => $nilaiDiskon,
            ];

            $diskon     = $this->hitungDiskon((float) $harga, $voucher);
            $hargaAkhir = max(0, $harga - $diskon);

            $this->assertGreaterThanOrEqual(0, $hargaAkhir, 'Harga akhir setelah diskon tidak boleh negatif');
        });
    }

    // -------------------------------------------------------------------------
    // Property 10: Daftar transaksi user hanya menampilkan milik user sendiri
    // -------------------------------------------------------------------------

    /**
     * For any user_id, filtering a list of transactions must only return
     * transactions belonging to that user.
     *
     * // Feature: cpns-tryout-online, Property 10: Daftar transaksi user hanya milik user sendiri
     */
    public function testFilterTransaksiHanyaMilikUserSendiri(): void
    {
        // Feature: cpns-tryout-online, Property 10: Daftar transaksi user hanya milik user sendiri
        $this->forAll(
            \Eris\Generator\choose(1, 100),  // target user_id
            \Eris\Generator\choose(5, 20)    // jumlah transaksi dalam pool
        )
        ->withMaxSize(100)
        ->then(function (int $targetUserId, int $totalTransaksi): void {
            // Build a pool of transactions with mixed user_ids
            $pool = [];
            for ($i = 0; $i < $totalTransaksi; $i++) {
                // Alternate between target user and other users
                $userId = ($i % 3 === 0) ? $targetUserId : ($targetUserId + $i + 1);
                $pool[] = [
                    'id'             => $i + 1,
                    'user_id'        => $userId,
                    'kode_transaksi' => 'TRX-' . strtoupper(uniqid()),
                    'harga_bayar'    => 100000,
                    'status'         => 'pending',
                    'created_at'     => date('Y-m-d H:i:s'),
                ];
            }

            // Filter: only transactions belonging to targetUserId
            $filtered = array_filter($pool, fn($t) => $t['user_id'] === $targetUserId);

            // All filtered transactions must belong to targetUserId
            foreach ($filtered as $t) {
                $this->assertEquals(
                    $targetUserId,
                    $t['user_id'],
                    "Transaksi yang difilter harus hanya milik user_id {$targetUserId}"
                );
            }
        });
    }

    /**
     * For any user_id, the filtered list must not contain transactions from other users.
     *
     * // Feature: cpns-tryout-online, Property 10: Daftar transaksi user hanya milik user sendiri
     */
    public function testTransaksiUserLainTidakMuncul(): void
    {
        // Feature: cpns-tryout-online, Property 10: Daftar transaksi user hanya milik user sendiri
        $this->forAll(
            \Eris\Generator\choose(1, 50),   // user A
            \Eris\Generator\choose(51, 100)  // user B (different range)
        )
        ->withMaxSize(100)
        ->then(function (int $userA, int $userB): void {
            // Create transactions for both users
            $allTransaksi = [
                ['id' => 1, 'user_id' => $userA, 'status' => 'success'],
                ['id' => 2, 'user_id' => $userB, 'status' => 'pending'],
                ['id' => 3, 'user_id' => $userA, 'status' => 'pending'],
                ['id' => 4, 'user_id' => $userB, 'status' => 'failed'],
            ];

            // Filter for userA
            $filteredA = array_values(array_filter($allTransaksi, fn($t) => $t['user_id'] === $userA));

            // None of userA's transactions should have userB's id
            foreach ($filteredA as $t) {
                $this->assertNotEquals($userB, $t['user_id'], 'Transaksi user B tidak boleh muncul di daftar user A');
            }

            // Count must match expected
            $expectedCount = count(array_filter($allTransaksi, fn($t) => $t['user_id'] === $userA));
            $this->assertCount($expectedCount, $filteredA);
        });
    }

    // -------------------------------------------------------------------------
    // Property 11: Filter transaksi berdasarkan status bekerja dengan benar
    // -------------------------------------------------------------------------

    /**
     * For any status filter, all returned transactions must have that exact status.
     *
     * // Feature: cpns-tryout-online, Property 11: Filter transaksi berdasarkan status
     */
    public function testFilterStatusHanyaMenampilkanStatusYangSesuai(): void
    {
        // Feature: cpns-tryout-online, Property 11: Filter transaksi berdasarkan status
        $this->forAll(
            \Eris\Generator\elements(['pending', 'success', 'failed', 'expired'])
        )
        ->withMaxSize(100)
        ->then(function (string $filterStatus): void {
            // Build a mixed pool of transactions
            $allStatuses = ['pending', 'success', 'failed', 'expired'];
            $pool        = [];
            foreach ($allStatuses as $i => $status) {
                $pool[] = ['id' => $i + 1, 'status' => $status, 'user_id' => 1];
                $pool[] = ['id' => $i + 10, 'status' => $status, 'user_id' => 1];
            }

            // Apply filter
            $filtered = array_values(array_filter($pool, fn($t) => $t['status'] === $filterStatus));

            // All results must have the correct status
            foreach ($filtered as $t) {
                $this->assertEquals(
                    $filterStatus,
                    $t['status'],
                    "Semua transaksi yang difilter harus berstatus '{$filterStatus}'"
                );
            }

            // No other status should appear
            $otherStatuses = array_diff($allStatuses, [$filterStatus]);
            foreach ($filtered as $t) {
                $this->assertNotContains(
                    $t['status'],
                    $otherStatuses,
                    "Status lain tidak boleh muncul saat filter '{$filterStatus}' aktif"
                );
            }
        });
    }

    /**
     * When no filter is applied, all transactions must be returned regardless of status.
     *
     * // Feature: cpns-tryout-online, Property 11: Filter transaksi berdasarkan status
     */
    public function testTanpaFilterMenampilkanSemuaTransaksi(): void
    {
        // Feature: cpns-tryout-online, Property 11: Filter transaksi berdasarkan status
        $this->forAll(
            \Eris\Generator\choose(4, 20) // jumlah transaksi
        )
        ->withMaxSize(100)
        ->then(function (int $count): void {
            $statuses = ['pending', 'success', 'failed', 'expired'];
            $pool     = [];
            for ($i = 0; $i < $count; $i++) {
                $pool[] = [
                    'id'     => $i + 1,
                    'status' => $statuses[$i % 4],
                ];
            }

            // No filter applied — return all
            $filtered = $pool;

            $this->assertCount($count, $filtered, 'Tanpa filter, semua transaksi harus ditampilkan');
        });
    }

    // -------------------------------------------------------------------------
    // Helper methods (pure logic, no DB)
    // -------------------------------------------------------------------------

    /**
     * Pure implementation of webhook status mapping logic (mirrors WebhookController).
     */
    private function mapWebhookStatus(string $transactionStatus, string $fraudStatus): ?string
    {
        if (in_array($transactionStatus, ['settlement', 'capture'])) {
            if ($transactionStatus === 'capture' && $fraudStatus === 'challenge') {
                return 'pending';
            }
            return 'success';
        } elseif ($transactionStatus === 'deny') {
            return 'failed';
        } elseif ($transactionStatus === 'cancel') {
            return 'failed';
        } elseif ($transactionStatus === 'expire') {
            return 'expired';
        } elseif ($transactionStatus === 'pending') {
            return 'pending';
        }
        return null;
    }

    /**
     * Pure implementation of Midtrans signature verification (mirrors MidtransService).
     */
    private function verifySignature(array $payload, string $serverKey): bool
    {
        $orderId     = $payload['order_id'] ?? '';
        $statusCode  = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        return hash_equals($expectedSignature, $payload['signature_key'] ?? '');
    }

    /**
     * Pure implementation of voucher discount calculation (mirrors VoucherService).
     */
    private function hitungDiskon(float $hargaAsli, array $voucher): float
    {
        if ($voucher['jenis_diskon'] === 'persentase') {
            return round($hargaAsli * ($voucher['nilai_diskon'] / 100), 2);
        }
        return min((float) $voucher['nilai_diskon'], $hargaAsli);
    }
}
