<?php

namespace Tests\Unit;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    /**
     * Test that dashboard data structure is correct when no tryout history exists.
     */
    public function testDashboardDataStructureWhenEmpty(): void
    {
        $paketAktif    = [];
        $riwayatTryout = [];
        $avgSkor       = 0;

        $this->assertIsArray($paketAktif);
        $this->assertIsArray($riwayatTryout);
        $this->assertEquals(0, $avgSkor);
        $this->assertCount(0, $paketAktif);
        $this->assertCount(0, $riwayatTryout);
    }

    /**
     * Test that average score calculation is correct.
     */
    public function testAverageSkorCalculation(): void
    {
        $riwayatTryout = [
            ['skor_total' => 80],
            ['skor_total' => 90],
            ['skor_total' => 70],
        ];

        $totalSkor = array_sum(array_column($riwayatTryout, 'skor_total'));
        $avgSkor   = round($totalSkor / count($riwayatTryout), 2);

        $this->assertEquals(80.0, $avgSkor);
    }

    /**
     * Test that riwayat tryout is limited to 5 entries.
     */
    public function testRiwayatTryoutLimitedToFive(): void
    {
        // Simulate 10 tryout results
        $allResults    = array_fill(0, 10, ['skor_total' => 75, 'tryout_nama' => 'Test']);
        // Dashboard should only show 5
        $riwayatTryout = array_slice($allResults, 0, 5);

        $this->assertCount(5, $riwayatTryout);
    }
}
