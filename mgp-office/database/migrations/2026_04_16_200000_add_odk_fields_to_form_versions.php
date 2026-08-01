<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_versions', function (Blueprint $table): void {
            $table->string('odk_state', 20)->nullable()->after('odk_asset_uid');  // open|closing|closed
            $table->string('odk_hash', 64)->nullable()->after('odk_state');       // sha256 du XForm
            $table->unsignedInteger('odk_submissions_count')->default(0)->after('odk_hash');
        });
    }

    public function down(): void
    {
        Schema::table('form_versions', function (Blueprint $table): void {
            $table->dropColumn(['odk_state', 'odk_hash', 'odk_submissions_count']);
        });
    }
};
