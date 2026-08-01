<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Géographie (régions, départements, communes de base, puis communes supplémentaires)
            GeographySeeder::class,
            CommuneSeeder::class,      // Communes additionnelles (217-266)
            LocalitySeeder::class,     // Localités

            // 2. Rôles et permissions
            RolesAndPermissionsSeeder::class,

            // 3. Types de référentiels
            ComplaintTypesSeeder::class,
            SiteTypesSeeder::class,
            GbvimsClassificationSeeder::class,

            // 4. Settings Spatie
            SlaSettingsSeeder::class,
            ProjectSettingsSeeder::class,

            // 5. Documentation (guides et tooltips)
            PageGuidesSeeder::class,
            FieldTooltipsSeeder::class,
        ]);

        // Créer un super_admin de test en local
        if (app()->isLocal()) {
            $admin = User::factory()->create([
                'name'     => 'Super Admin PACIPA',
                'email'    => 'admin@pacipa-mgp.ne',
                'password' => bcrypt('Admin@2025'),
            ]);
            $admin->assignRole('super_admin');
        }
    }
}
