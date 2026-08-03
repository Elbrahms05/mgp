<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Committee\Enums\CommitteeLevel;
use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Models\ComplaintType;
use App\Domains\Complaint\Services\ComplaintWorkflowService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * EF-03 - L'escalade d'une plainte change le niveau de traitement, recalcule
 * l'échéance SLA du nouveau niveau et journalise la transition.
 */
class ComplaintEscalationTest extends TestCase
{
    use RefreshDatabase;

    public function test_escalade_cbgp_vers_ccgp_recalcule_le_sla_et_journalise(): void
    {
        Notification::fake();
        Storage::fake('local');
        Carbon::setTestNow('2026-01-01 08:00:00');

        $type  = ComplaintType::create(['code' => 'type1', 'label' => 'Foncier', 'is_sensitive' => false, 'sort_order' => 1]);
        $agent = User::factory()->create();
        $workflow = app(ComplaintWorkflowService::class);

        $complaint = $workflow->register([
            'complaint_type_id' => $type->id,
            'description'       => 'Test escalade',
            'channel'           => 'in_person',
        ], $agent);

        $this->assertSame(CommitteeLevel::CBGP, $complaint->current_level);

        $workflow->escalate($complaint, CommitteeLevel::CCGP, 'Non résolu au niveau local', $agent);
        $complaint->refresh();

        $this->assertSame(CommitteeLevel::CCGP, $complaint->current_level);
        $this->assertSame(ComplaintStatus::ESCALATED, $complaint->status);
        // CCGP = 3 + 4 = 7 jours
        $this->assertSame(7, (int) now()->diffInDays($complaint->sla_deadline));
        $this->assertSame(1, $complaint->histories()->where('action', 'escalate')->count());

        Carbon::setTestNow();
    }
}
