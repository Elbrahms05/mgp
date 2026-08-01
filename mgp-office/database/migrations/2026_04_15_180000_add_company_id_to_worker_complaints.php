<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_complaints', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('worker_type')
                ->constrained('companies')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('worker_complaints', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Domains\Company\Models\Company::class);
            $table->dropColumn('company_id');
        });
    }
};
