<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_site', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('responsible_name')->comment('Nom du responsable de suivi des plaintes');
            $table->string('responsible_phone')->nullable();
            $table->string('responsible_email')->nullable();
            $table->timestamps();
            
            $table->unique(['company_id', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_site');
    }
};
