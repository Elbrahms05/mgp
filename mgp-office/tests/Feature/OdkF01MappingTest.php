<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Models\ComplaintType;
use App\Domains\OdkBridge\Services\SubmissionMapper;
use App\Domains\Site\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * update.md 3.3 (révisé) - Le formulaire F01 ne transporte plus de géographie : l'agent
 * saisit l'ID du site et le back-office déduit tout de cet identifiant.
 */
class OdkF01MappingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
        // Le mapper attribue registered_by = 3 (compte technique ODK).
        User::factory()->create(['id' => 3]);
    }

    private function submission(string $siteId, string $type = 'type1', string $horsSite = 'non'): string
    {
        return <<<XML
        <data id="pacipa_f01">
            <grp_identification>
                <plainte_hors_site>{$horsSite}</plainte_hors_site>
                <site_id>{$siteId}</site_id>
                <id_plainte>REF-2026-ABC</id_plainte>
            </grp_identification>
            <grp_plaignant>
                <anonymat>non</anonymat>
                <nom_prenom>Amadou Ali</nom_prenom>
                <age>34</age>
                <genre>M</genre>
                <lieu_residence>Dosso</lieu_residence>
                <contact>+22790123456</contact>
            </grp_plaignant>
            <grp_plainte>
                <type>{$type}</type>
                <personne_visee>Entreprise X</personne_visee>
                <description>Litige sur le chantier.</description>
                <date_faits>2026-01-15</date_faits>
            </grp_plainte>
            <grp_canal><canal>en_personne</canal></grp_canal>
        </data>
        XML;
    }

    private function makeSite(string $code): Site
    {
        DB::table('regions')->insert(['id' => 1, 'code' => 'DO', 'name' => 'Dosso']);
        DB::table('departments')->insert(['id' => 1, 'region_id' => 1, 'code' => 'DO-01', 'name' => 'Dosso']);
        DB::table('communes')->insert(['id' => 1, 'department_id' => 1, 'code' => 'DO-01-01', 'name' => 'Dosso']);
        DB::table('localities')->insert(['id' => 1, 'commune_id' => 1, 'code' => 'DO-01-01-01', 'name' => 'Dosso']);
        DB::table('site_types')->insert(['id' => 1, 'code' => 'irr', 'label' => 'Périmètre', 'component' => 1]);

        return Site::create([
            'code' => $code, 'name' => 'Site test', 'site_type_id' => 1, 'category' => 1,
            'locality_id' => 1, 'start_date' => '2025-01-01', 'expected_end_date' => '2027-01-01',
        ]);
    }

    public function test_le_site_est_resolu_depuis_son_id(): void
    {
        $type = ComplaintType::create(['code' => 'type_1', 'label' => 'Info', 'is_sensitive' => false, 'sort_order' => 1]);
        $site = $this->makeSite('PAC-DOSSO-001');

        $mapper = app(SubmissionMapper::class);
        $mapper->map('pacipa_f01', 'F01', $this->submission('PAC-DOSSO-001'));

        $complaint = Complaint::first();
        $this->assertNotNull($complaint);
        $this->assertSame($site->id, $complaint->site_id, "Le site doit être résolu depuis l'ID saisi.");
        $this->assertSame($type->id, $complaint->complaint_type_id, 'type1 → type_1');
        $this->assertSame('Amadou Ali', $complaint->complainant_name);
        $this->assertSame('REF-2026-ABC', $complaint->registration_number);
    }

    public function test_une_plainte_hors_site_n_a_pas_de_site(): void
    {
        ComplaintType::create(['code' => 'type_1', 'label' => 'Info', 'is_sensitive' => false, 'sort_order' => 1]);

        app(SubmissionMapper::class)->map('pacipa_f01', 'F01', $this->submission('', 'type1', 'oui'));

        $complaint = Complaint::first();
        $this->assertNotNull($complaint);
        $this->assertNull($complaint->site_id);
    }
}
