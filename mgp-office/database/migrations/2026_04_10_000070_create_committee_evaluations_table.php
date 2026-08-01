<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_evaluations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('committee_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedSmallInteger('members_total')->default(0);
            $table->unsignedSmallInteger('members_operational')->default(0);
            $table->unsignedInteger('complaints_registered')->default(0);
            $table->unsignedInteger('complaints_processed')->default(0);
            $table->unsignedInteger('complaints_resolved_local')->default(0);
            $table->unsignedInteger('complaints_transmitted')->default(0);
            $table->text('difficulties')->nullable();
            $table->text('solutions')->nullable();
            $table->text('community_feedback')->nullable();
            $table->text('improvement_suggestions')->nullable();
            $table->foreignId('submitted_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_evaluations');
    }
};
