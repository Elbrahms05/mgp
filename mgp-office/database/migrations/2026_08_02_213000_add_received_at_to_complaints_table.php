<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Date de réception réelle de la plainte (EF-11) - distincte de la date de saisie.
 *
 * Pour une fiche papier F01 saisie en différé, le calcul SLA doit partir de la date de
 * réception papier, pas de la date d'encodage. `created_at` reste la date de saisie.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table): void {
            $table->timestamp('received_at')->nullable()->after('channel');
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table): void {
            $table->dropColumn('received_at');
        });
    }
};
