<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table): void {
            $table->id();
            $table->string('registration_number', 30)->unique();
            $table->foreignId('complaint_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('complainant_name', 150)->nullable();
            $table->unsignedTinyInteger('complainant_age')->nullable();
            $table->char('complainant_gender', 1)->nullable();
            $table->string('complainant_locality', 150)->nullable();
            $table->string('complainant_phone', 20)->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('subject', 300)->nullable();
            $table->text('description');
            $table->date('incident_date')->nullable();
            $table->string('accused_party', 200)->nullable();
            $table->string('channel', 30);
            $table->string('status', 30)->default('registered');
            $table->string('current_level', 10)->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('registered_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('sla_deadline')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('channel');
            $table->index('current_level');
            $table->index('sla_deadline');
            $table->index('registration_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
