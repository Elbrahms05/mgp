<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 200);
            $table->foreignId('site_type_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('category');
            $table->foreignId('locality_id')->constrained()->restrictOnDelete();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('area_hectares', 12, 4)->nullable();
            $table->unsignedInteger('beneficiaries_count')->nullable();
            $table->date('start_date');
            $table->date('expected_end_date');
            $table->date('actual_end_date')->nullable();
            $table->string('status', 30)->default('planned');
            $table->text('description')->nullable();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('committee_id')->nullable(); // FK added after committees table
            $table->string('risk_level', 20)->default('low');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('risk_level');
            $table->index('locality_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
