<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registre de formation / recyclage des personnes de confiance VBG (update.md 2.5).
 *
 * Chaque personne de confiance doit être formée (approche centrée survivante, consentement,
 * confidentialité) et recyclée tous les 6 mois. Une formation valide est requise pour
 * pouvoir enregistrer un signalement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vbg_trusted_person_trainings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('trained_at');
            $table->date('valid_until');
            $table->string('topic', 200)->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vbg_trusted_person_trainings');
    }
};
