<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Complaint\Models\ComplaintType;
use Illuminate\Database\Seeder;

class ComplaintTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'type_1', 'label' => 'Inaccessibilité aux informations liées au Projet',    'is_sensitive' => false, 'sort_order' => 1],
            ['code' => 'type_2', 'label' => 'Plaintes liées à l\'exécution des marchés',            'is_sensitive' => false, 'sort_order' => 2],
            ['code' => 'type_3', 'label' => 'Plaintes liées aux travaux de réhabilitation',         'is_sensitive' => false, 'sort_order' => 3],
            ['code' => 'type_4', 'label' => 'Plaintes liées aux conditions de travail',             'is_sensitive' => false, 'sort_order' => 4],
            ['code' => 'type_5', 'label' => 'Plaintes liées aux pertes/affectation biens physiques','is_sensitive' => false, 'sort_order' => 5],
            ['code' => 'type_6', 'label' => 'Plaintes liées à l\'octroi d\'équipements',           'is_sensitive' => false, 'sort_order' => 6],
            ['code' => 'type_7', 'label' => 'Plaintes liées aux formations',                        'is_sensitive' => false, 'sort_order' => 7],
            ['code' => 'type_8', 'label' => 'Plaintes VBG/EAS/HS et VCE',                           'is_sensitive' => true,  'sort_order' => 8],
        ];

        foreach ($types as $type) {
            ComplaintType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
