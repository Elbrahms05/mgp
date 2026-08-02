<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Committee\Enums\CommitteeLevel;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Services\SlaMonitorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * EF-03 — Le calcul des échéances SLA suit les délais contractuels par niveau :
 * CBGP 3+7 = 10 j, CCGP 3+4 = 7 j, CNGP 7+3 = 10 j (rappel critique n°4).
 */
class SlaCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-01-01 08:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * @return array<string, array{0: CommitteeLevel, 1: int}>
     */
    public static function levels(): array
    {
        return [
            'CBGP' => [CommitteeLevel::CBGP, 10],
            'CCGP' => [CommitteeLevel::CCGP, 7],
            'CNGP' => [CommitteeLevel::CNGP, 10],
        ];
    }

    /**
     * @dataProvider levels
     */
    public function test_echeance_par_niveau(CommitteeLevel $level, int $expectedDays): void
    {
        $service  = app(SlaMonitorService::class);
        $deadline = $service->computeDeadline(new Complaint(), $level);

        $this->assertSame($expectedDays, (int) now()->diffInDays($deadline));
    }
}
