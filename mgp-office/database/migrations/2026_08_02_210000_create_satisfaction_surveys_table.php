<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enquête de satisfaction du plaignant à la clôture d'une plainte (EF-08).
 * Une enquête au plus par plainte ; alimente les indicateurs du tableau de bord.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satisfaction_surveys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();

            // Notes de 1 (très insatisfait) à 5 (très satisfait)
            $table->unsignedTinyInteger('overall_rating');
            $table->unsignedTinyInteger('timeliness_rating')->nullable();
            $table->unsignedTinyInteger('staff_rating')->nullable();
            $table->unsignedTinyInteger('resolution_rating')->nullable();

            $table->boolean('would_recommend')->nullable();
            $table->text('comment')->nullable();
            $table->string('channel', 30)->default('in_person');

            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique('complaint_id');
            $table->index('overall_rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satisfaction_surveys');
    }
};
