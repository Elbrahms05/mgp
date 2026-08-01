<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_complaint_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('worker_complaint_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('action', 50);      // register|start_review|escalate|resolve|close|sla_alert
            $table->text('comment')->nullable();
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_complaint_histories');
    }
};
