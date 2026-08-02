<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Documentation\Models\PageGuide;
use Database\Seeders\PageGuidesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rappel critique n°10 — Chaque guide d'aide contextuelle porte une référence MGP
 * obligatoire. Le seeder alimente les guides des pages du back-office.
 */
class PageGuideDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_seeder_cree_des_guides_avec_reference_mgp(): void
    {
        $this->seed(PageGuidesSeeder::class);

        $this->assertGreaterThan(0, PageGuide::count());

        $createGuide = PageGuide::where('route_name', 'complaints.create')->first();
        $this->assertNotNull($createGuide, 'La page de création de plainte doit disposer d\'un guide.');
        $this->assertNotEmpty($createGuide->mgp_reference);
    }

    public function test_tous_les_guides_portent_une_reference_mgp(): void
    {
        $this->seed(PageGuidesSeeder::class);

        $sansReference = PageGuide::whereNull('mgp_reference')
            ->orWhere('mgp_reference', '')
            ->pluck('route_name');

        $this->assertTrue(
            $sansReference->isEmpty(),
            'Guides sans référence MGP (règle n°10) : ' . $sansReference->implode(', '),
        );
    }
}
