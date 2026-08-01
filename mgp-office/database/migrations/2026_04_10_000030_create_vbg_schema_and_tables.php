<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recréer le schéma proprement (utile après migrate:fresh qui ne touche pas les schémas non-public)
        DB::statement('DROP SCHEMA IF EXISTS vbg CASCADE');
        DB::statement('CREATE SCHEMA vbg');

        // Table vbg_reports dans le schéma vbg
        Schema::create('vbg.vbg_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('channel', 30);
            $table->boolean('is_anonymous')->default(false);
            $table->text('caller_phone_encrypted')->nullable();
            $table->boolean('consent_given')->default(false);
            $table->timestamp('consent_given_at')->nullable();
            $table->string('survivor_age_group', 20)->nullable();
            $table->char('survivor_gender', 1)->nullable();
            $table->date('incident_date')->nullable();
            $table->string('incident_zone', 200)->nullable();
            $table->string('author_link', 30)->nullable();
            $table->string('classification', 50)->nullable();
            $table->jsonb('services_received')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('trusted_person_id')->nullable();
            $table->string('ussd_session_id', 100)->nullable();
            $table->text('raw_sms_content')->nullable();
            $table->string('voice_memo_path')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->timestamp('ugp_notified_at')->nullable();
            $table->timestamp('worldbank_notified_at')->nullable();
            $table->string('status', 30)->default('received');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('channel');
            $table->index('code');
        });

        Schema::create('vbg.vbg_referrals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vbg_report_id');
            $table->string('service_type', 30);
            $table->string('provider_name', 200);
            $table->text('provider_contact')->nullable();
            $table->timestamp('referred_at');
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->timestamps();

            $table->index('vbg_report_id');
        });

        Schema::create('vbg.vbg_enquiries', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vbg_report_id')->unique();
            $table->timestamp('opened_at');
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('project_link_confirmed')->nullable();
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamp('worldbank_notified_at')->nullable();
            $table->unsignedBigInteger('opened_by')->nullable();
            $table->timestamps();
        });

        Schema::create('vbg.vbg_consents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vbg_report_id');
            $table->boolean('consent_given');
            $table->timestamp('consented_at');
            $table->timestamp('withdrawn_at')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('vbg_report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vbg.vbg_consents');
        Schema::dropIfExists('vbg.vbg_enquiries');
        Schema::dropIfExists('vbg.vbg_referrals');
        Schema::dropIfExists('vbg.vbg_reports');
        DB::statement('DROP SCHEMA IF EXISTS vbg CASCADE');
    }
};
