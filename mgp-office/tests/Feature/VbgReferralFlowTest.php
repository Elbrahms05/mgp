<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Vbg\Models\VbgReferral;
use App\Domains\Vbg\Models\VbgReport;
use App\Settings\SlaSettings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * EF-13 — Le référencement vers les services fixe une échéance à 72 h (délai
 * contractuel de référencement VBG), rattachée au signalement.
 */
class VbgReferralFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_referencement_fixe_une_echeance_a_72h(): void
    {
        Carbon::setTestNow('2026-01-01 08:00:00');

        $settings = app(SlaSettings::class);
        $this->assertSame(72, $settings->vbg_referral_hours);

        $report = VbgReport::create([
            'code'           => 'VBG-2026-00001',
            'channel'        => 'trusted_person',
            'is_anonymous'   => true,
            'classification' => 'physical_assault',
            'incident_zone'  => 'Dosso',
            'status'         => 'new',
        ]);

        $referral = VbgReferral::create([
            'vbg_report_id' => $report->id,
            'service_type'  => 'medical',
            'provider_name' => 'Centre holistique de Dosso',
            'referred_at'   => now(),
            'deadline_at'   => now()->addHours($settings->vbg_referral_hours),
        ]);

        $this->assertSame(1, $report->referrals()->count());
        $this->assertTrue($referral->deadline_at->equalTo(now()->addHours(72)));
        $this->assertSame(72, (int) $referral->referred_at->diffInHours($referral->deadline_at));

        Carbon::setTestNow();
    }
}
