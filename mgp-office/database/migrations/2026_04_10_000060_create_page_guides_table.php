<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_guides', function (Blueprint $table): void {
            $table->id();
            $table->string('route_name', 100)->unique();
            $table->string('page_title', 200);
            $table->string('mgp_reference', 200)->nullable();
            $table->text('summary');
            $table->text('detailed_guide')->nullable();
            $table->jsonb('warnings')->nullable();
            $table->jsonb('related_glossary')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('display_mode', 20)->default('sidebar');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('field_tooltips', function (Blueprint $table): void {
            $table->id();
            $table->string('route_name', 100);
            $table->string('field_name', 100);
            $table->text('tooltip_text');
            $table->string('mgp_reference', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['route_name', 'field_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_tooltips');
        Schema::dropIfExists('page_guides');
    }
};
