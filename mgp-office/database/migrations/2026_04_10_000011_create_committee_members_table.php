<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('committee_id')->constrained()->cascadeOnDelete();
            $table->string('full_name', 150);
            $table->string('role', 100);
            $table->char('gender', 1)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('election_mode', 30)->nullable();
            $table->boolean('is_vbg_focal_point')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_members');
    }
};
