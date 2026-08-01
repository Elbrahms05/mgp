<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('label', 200);
            $table->boolean('is_sensitive')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_types');
    }
};
