<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('communes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('localities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('commune_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('localities');
        Schema::dropIfExists('communes');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('regions');
    }
};
