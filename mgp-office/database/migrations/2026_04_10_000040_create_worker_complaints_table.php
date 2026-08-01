<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_complaints', function (Blueprint $table): void {
            $table->id();
            $table->string('registration_number', 30)->unique();
            $table->string('worker_type', 30);
            $table->string('employer_name', 200)->nullable();
            $table->string('worker_name', 150)->nullable();
            $table->string('worker_phone', 20)->nullable();
            $table->string('subject', 300);
            $table->text('description');
            $table->date('incident_date')->nullable();
            $table->string('status', 30)->default('registered');
            $table->unsignedTinyInteger('level')->default(1);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('registered_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_complaints');
    }
};
