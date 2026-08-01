<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committees', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 200);
            $table->string('level', 10);
            $table->foreignId('locality_id')->nullable()->constrained()->nullOnDelete();
            $table->date('installation_date')->nullable();
            $table->string('pv_installation_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committees');
    }
};
