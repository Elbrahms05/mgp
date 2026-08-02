<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Models\ComplaintType;
use App\Domains\Evaluation\Models\SatisfactionSurvey;
use App\Domains\Statistic\Services\KpiCalculatorService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * EF-08 — Enquête de satisfaction à la clôture : enregistrement rattaché à la plainte
 * et remontée dans les indicateurs.
 */
class SatisfactionSurveyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
    }

    private function complaint(): Complaint
    {
        $type = ComplaintType::create(['code' => 't1', 'label' => 'Foncier', 'is_sensitive' => false, 'sort_order' => 1]);

        return Complaint::create([
            'registration_number' => 'REG-2026-000100',
            'complaint_type_id'   => $type->id,
            'description'         => 'x',
            'channel'             => 'in_person',
            'status'              => 'closed',
            'registered_by'       => User::factory()->create()->id,
        ]);
    }

    public function test_un_agent_enregistre_une_enquete_de_satisfaction(): void
    {
        \Spatie\Permission\Models\Permission::create(['name' => 'complaints.view_own_region']);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $agent = User::factory()->create();
        $agent->givePermissionTo('complaints.view_own_region');
        $complaint = $this->complaint();

        $this->actingAs($agent)
            ->post(route('complaints.satisfaction.store', $complaint), [
                'overall_rating'  => 5,
                'staff_rating'    => 4,
                'would_recommend' => '1',
                'channel'         => 'in_person',
                'comment'         => 'Bien traité',
            ])
            ->assertRedirect(route('complaints.show', $complaint));

        $this->assertDatabaseHas('satisfaction_surveys', [
            'complaint_id'   => $complaint->id,
            'overall_rating' => 5,
            'collected_by'   => $agent->id,
        ]);
    }

    public function test_les_indicateurs_de_satisfaction_sont_calcules(): void
    {
        $c1 = $this->complaint();
        SatisfactionSurvey::create(['complaint_id' => $c1->id, 'overall_rating' => 5, 'would_recommend' => true, 'channel' => 'in_person', 'submitted_at' => now()]);

        $stats = app(KpiCalculatorService::class)->getSatisfactionStats();

        $this->assertSame(1, $stats['count']);
        $this->assertSame(5.0, $stats['avg_overall']);
        $this->assertSame(100.0, $stats['satisfied_pct']);
        $this->assertSame(100.0, $stats['recommend_pct']);
    }
}
