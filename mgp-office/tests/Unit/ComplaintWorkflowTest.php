<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Models\ComplaintType;
use App\Domains\Complaint\Services\ComplaintWorkflowService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * EF-02 / EF-06 — L'enregistrement d'une plainte : attribue un code, positionne le
 * statut initial, journalise l'action et génère l'accusé de réception PDF.
 */
class ComplaintWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
    }

    private function registerComplaint(): Complaint
    {
        $type = ComplaintType::create([
            'code' => 'type1', 'label' => 'Foncier', 'is_sensitive' => false, 'sort_order' => 1,
        ]);
        $agent = User::factory()->create();

        return app(ComplaintWorkflowService::class)->register([
            'complaint_type_id' => $type->id,
            'description'       => 'Litige sur les limites de la parcelle.',
            'subject'           => 'Conflit foncier',
            'channel'           => 'in_person',
        ], $agent);
    }

    public function test_enregistrement_attribue_code_statut_et_historique(): void
    {
        $complaint = $this->registerComplaint();

        $this->assertSame('REG-' . now()->year . '-000001', $complaint->registration_number);
        $this->assertSame(ComplaintStatus::REGISTERED, $complaint->status);
        $this->assertNotNull($complaint->sla_deadline);
        $this->assertSame(1, $complaint->histories()->where('action', 'register')->count());
    }

    public function test_enregistrement_genere_un_accuse_pdf(): void
    {
        $complaint = $this->registerComplaint();

        $media = $complaint->getFirstMedia('acknowledgment');

        $this->assertNotNull($media, "L'accusé de réception PDF doit être généré (EF-06).");
        $this->assertSame('application/pdf', $media->mime_type);
        $this->assertDatabaseHas('notification_logs', [
            'event'  => 'complaint_acknowledgment_pdf',
            'status' => 'sent',
        ]);
    }
}
