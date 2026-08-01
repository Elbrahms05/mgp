<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('action', 100)->nullable();
            $table->text('comment')->nullable();
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('complaint_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_histories');
    }
};
