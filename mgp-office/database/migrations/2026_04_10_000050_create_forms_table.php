<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table): void {
            $table->id();
            $table->string('form_id', 50)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->foreignId('complaint_type_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->jsonb('active_regions')->nullable();
            $table->string('current_version', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('form_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('version_number', 20);
            $table->string('xlsform_path')->nullable();
            $table->string('xform_xml_path')->nullable();
            $table->timestamp('deployed_at')->nullable();
            $table->string('odk_asset_uid', 100)->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_versions');
        Schema::dropIfExists('forms');
    }
};
