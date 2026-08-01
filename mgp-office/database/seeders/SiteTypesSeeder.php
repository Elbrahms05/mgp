<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Site\Models\SiteType;
use Illuminate\Database\Seeder;

class SiteTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'irrigated_perimeter', 'label' => 'Périmètre irrigué',            'component' => 1],
            ['code' => 'ces_drs_works',        'label' => 'Travaux CES/DRS',              'component' => 1],
            ['code' => 'nursery',              'label' => 'Pépinière',                    'component' => 1],
            ['code' => 'water_point',          'label' => 'Point d\'eau / forage',        'component' => 1],
            ['code' => 'livestock_shelter',    'label' => 'Étable d\'embouche',           'component' => 1],
            ['code' => 'vaccination_park',     'label' => 'Parc de vaccination',          'component' => 1],
            ['code' => 'storage_warehouse',    'label' => 'Magasin de stockage',          'component' => 2],
            ['code' => 'collection_center',    'label' => 'Centre de collecte',           'component' => 2],
            ['code' => 'rural_market',         'label' => 'Marché rural',                 'component' => 2],
            ['code' => 'processing_unit',      'label' => 'Unité de transformation',      'component' => 2],
            ['code' => 'financial_office',     'label' => 'Antenne IMF/finance',          'component' => 3],
            ['code' => 'training_center',      'label' => 'Centre de formation',          'component' => 4],
        ];

        foreach ($types as $type) {
            SiteType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
