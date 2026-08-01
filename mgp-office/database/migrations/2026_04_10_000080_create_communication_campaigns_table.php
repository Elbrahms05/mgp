<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 200);
            $table->string('type', 50);
            $table->text('description')->nullable();
            $table->jsonb('regions')->nullable();
            $table->string('target_audience', 200)->nullable();
            $table->jsonb('languages')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('planned');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_campaigns');
    }
};
