<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('event', 100);
            $table->string('channel', 30);
            $table->string('recipient', 200);
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->morphs('notifiable');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('event');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
