<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ajoute le champ 'password' au groupe de settings 'odk'.
 *
 * ODK Central requiert une authentification email+password via POST /v1/sessions
 * pour obtenir un token de session valide 24h.
 * Le champ 'api_token' reste utilisé pour KoboToolbox et ODK Aggregate (token statique).
 */
return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('settings')
            ->where('group', 'odk')
            ->where('name', 'password')
            ->exists();

        if (! $exists) {
            DB::table('settings')->insert([
                'group'   => 'odk',
                'name'    => 'password',
                'payload' => json_encode(''),
                'locked'  => false,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'odk')
            ->where('name', 'password')
            ->delete();
    }
};
