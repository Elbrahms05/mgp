<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mediations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->date('session_date');
            $table->string('presided_by_name', 150);
            $table->string('presided_by_role', 100)->nullable();
            $table->text('result')->nullable();
            $table->boolean('is_agreement_reached')->default(false);
            $table->string('pv_path')->nullable();
            $table->foreignId('facilitated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediations');
    }
};
