<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('number_registre')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->string('sector')->nullable()->comment('Agriculture, Transport, Construction, etc.');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('sector');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
