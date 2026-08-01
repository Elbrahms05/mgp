<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_complaints', function (Blueprint $table): void {
            $table->timestamp('sla_deadline')->nullable()->after('level');
            $table->timestamp('escalated_at')->nullable()->after('sla_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('worker_complaints', function (Blueprint $table): void {
            $table->dropColumn(['sla_deadline', 'escalated_at']);
        });
    }
};
