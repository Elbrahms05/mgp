<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Vbg\Services\TrustedPersonComplianceService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * update.md 2.5 — Personnes de confiance VBG : recyclage 6 mois obligatoire et contrôle
 * automatique de conflit d'intérêts à la saisie d'un signalement.
 */
class TrustedPersonComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Permission::findOrCreate('vbg.receive_direct', 'web');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function trustedPerson(array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        $user->givePermissionTo('vbg.receive_direct');

        return $user;
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'consent_given' => true,
            'incident_date' => now()->subDay()->toDateString(),
            'incident_zone' => 'Dosso',
        ], $override);
    }

    public function test_le_recyclage_est_valide_6_mois(): void
    {
        $service = app(TrustedPersonComplianceService::class);
        $user    = User::factory()->create();

        $service->recordTraining($user, Carbon::parse('2026-01-10'), 'Consentement', null);

        $this->assertTrue($service->trainingValidUntil($user)->equalTo(Carbon::parse('2026-07-10')));
    }

    public function test_une_formation_trop_ancienne_n_est_plus_valide(): void
    {
        $service = app(TrustedPersonComplianceService::class);
        $user    = User::factory()->create();

        $service->recordTraining($user, now()->subMonths(7), null, null);

        $this->assertFalse($service->isTrainingValid($user));
    }

    public function test_le_conflit_est_detecte_par_numero_identique(): void
    {
        $service = app(TrustedPersonComplianceService::class);
        $user    = User::factory()->create(['phone' => '+227 90 00 00 00']);

        $this->assertTrue($service->detectConflict(['caller_phone' => '90000000'], $user));
        $this->assertFalse($service->detectConflict(['caller_phone' => '+22791111111'], $user));
    }

    public function test_signalement_refuse_si_formation_non_a_jour(): void
    {
        $user = $this->trustedPerson();
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/vbg/trusted-person', $this->payload())
            ->assertStatus(403);
    }

    public function test_signalement_refuse_en_cas_de_conflit_declare(): void
    {
        $user = $this->trustedPerson();
        app(TrustedPersonComplianceService::class)->recordTraining($user, now()->subMonth(), null, null);
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/vbg/trusted-person', $this->payload(['conflict_declared' => true]))
            ->assertStatus(422);
    }

    public function test_signalement_accepte_si_forme_et_sans_conflit(): void
    {
        $user = $this->trustedPerson();
        app(TrustedPersonComplianceService::class)->recordTraining($user, now()->subMonth(), null, null);
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/vbg/trusted-person', $this->payload())
            ->assertStatus(201)
            ->assertJsonStructure(['code', 'message']);
    }
}
