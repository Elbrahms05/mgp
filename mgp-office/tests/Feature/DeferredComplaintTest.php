<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Models\ComplaintType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * EF-11 / EF-09 - Saisie différée d'une fiche papier : le SLA part de la date de
 * réception papier (pas de la saisie) et la fiche numérisée est jointe.
 */
class DeferredComplaintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
    }

    public function test_le_sla_est_calcule_depuis_la_date_de_reception_papier(): void
    {
        Carbon::setTestNow('2026-02-01 10:00:00');

        Permission::create(['name' => 'complaints.view_own_region']);
        Permission::create(['name' => 'complaints.create']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $agent = User::factory()->create();
        $agent->givePermissionTo(['complaints.view_own_region', 'complaints.create']);

        $type = ComplaintType::create(['code' => 't1', 'label' => 'Foncier', 'is_sensitive' => false, 'sort_order' => 1]);

        // Fiche reçue 5 jours avant la saisie
        $received = '2026-01-27';

        $this->actingAs($agent)
            ->post(route('complaints.deferred.store'), [
                'complaint_type_id' => $type->id,
                'received_at'       => $received,
                'description'       => 'Litige foncier reçu sur fiche papier',
                'scan'              => UploadedFile::fake()->create('fiche.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect();

        $complaint = Complaint::first();
        $this->assertNotNull($complaint);

        // CBGP = 10 jours à partir de la réception papier (27/01), pas de la saisie (01/02).
        $this->assertTrue(
            $complaint->sla_deadline->equalTo(Carbon::parse($received)->addDays(10)),
            "Le SLA doit partir de la date de réception papier.",
        );
        $this->assertTrue($complaint->received_at->equalTo(Carbon::parse($received)));

        // La fiche numérisée est jointe (EF-09).
        $this->assertSame(1, $complaint->getMedia('attachments')->count());

        Carbon::setTestNow();
    }
}
