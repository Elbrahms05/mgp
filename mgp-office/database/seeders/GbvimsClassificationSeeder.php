<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GbvimsClassificationSeeder extends Seeder
{
    /**
     * Insère les classifications GBVIMS dans les settings (utilisé comme référence métier).
     * Ces données ne nécessitent pas de table dédiée - elles sont définies dans config/vbg.php
     * et documentées ici pour traçabilité.
     */
    public function run(): void
    {
        // Les classifications GBVIMS sont gérées via l'enum GbvimsClassification
        // et le fichier config/vbg.php. Ce seeder sert de documentation et peut
        // être étendu si une table de référence est ajoutée.
        $this->command->info('GBVIMS classifications are defined in App\Domains\Vbg\Enums\GbvimsClassification and config/vbg.php');
    }
}
