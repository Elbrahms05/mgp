<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Models\ComplaintType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * EF-01 / ENF-01 — API mobile Sanctum : authentification par jeton, distribution des
 * formulaires (F03/VBG cloisonné), remontée d'état d'une plainte.
 */
class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
    }

    public function test_login_emet_un_jeton(): void
    {
        User::factory()->create(['email' => 'agent@pacipa.ne']);

        $response = $this->postJson('/api/mobile/login', [
            'email'       => 'agent@pacipa.ne',
            'password'    => 'password',
            'device_name' => 'Tablette-Dosso-01',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'roles']]);
    }

    public function test_login_refuse_de_mauvais_identifiants(): void
    {
        User::factory()->create(['email' => 'agent@pacipa.ne']);

        $this->postJson('/api/mobile/login', [
            'email'       => 'agent@pacipa.ne',
            'password'    => 'mauvais',
            'device_name' => 'Tablette',
        ])->assertStatus(422);
    }

    public function test_les_endpoints_exigent_un_jeton(): void
    {
        $this->getJson('/api/mobile/forms')->assertUnauthorized();
    }

    public function test_le_catalogue_exclut_le_formulaire_vbg_sans_habilitation(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $response = $this->getJson('/api/mobile/forms')->assertOk();

        $codes = collect($response->json('forms'))->pluck('code');
        $this->assertTrue($codes->contains('F01'));
        $this->assertFalse($codes->contains('F03'), 'F03 (VBG) ne doit pas être distribué sans habilitation.');
    }

    public function test_un_agent_habilite_vbg_voit_le_formulaire_f03(): void
    {
        Permission::create(['name' => 'vbg.receive']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->givePermissionTo('vbg.receive');

        Sanctum::actingAs($user, ['*']);

        $codes = collect($this->getJson('/api/mobile/forms')->json('forms'))->pluck('code');
        $this->assertTrue($codes->contains('F03'));
    }

    public function test_le_telechargement_f03_est_refuse_sans_habilitation(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->get('/api/mobile/forms/F03')->assertForbidden();
    }

    public function test_le_telechargement_f01_reussit(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->get('/api/mobile/forms/F01')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml');
    }

    public function test_la_remontee_d_etat_renvoie_le_statut(): void
    {
        $type = ComplaintType::create(['code' => 't1', 'label' => 'Foncier', 'is_sensitive' => false, 'sort_order' => 1]);
        $agent = User::factory()->create();
        $complaint = Complaint::create([
            'registration_number' => 'REG-2026-000042',
            'complaint_type_id'   => $type->id,
            'description'         => 'x',
            'channel'             => 'in_person',
            'status'              => 'registered',
            'registered_by'       => $agent->id,
        ]);

        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->getJson("/api/mobile/complaints/{$complaint->registration_number}/status")
            ->assertOk()
            ->assertJson([
                'registration_number' => 'REG-2026-000042',
                'status'              => 'registered',
            ]);
    }
}
