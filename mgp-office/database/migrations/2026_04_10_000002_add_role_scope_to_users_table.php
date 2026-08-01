<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role_scope_type')->nullable()->after('email');
            $table->unsignedBigInteger('role_scope_id')->nullable()->after('role_scope_type');
            $table->string('phone', 20)->nullable()->after('role_scope_id');
            $table->string('locale', 5)->default('fr')->after('phone');
            $table->boolean('two_factor_enabled')->default(false)->after('locale');
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_confirmed_at');
            $table->boolean('is_active')->default(true)->after('last_login_at');

            $table->index(['role_scope_type', 'role_scope_id'], 'users_role_scope_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_role_scope_index');
            $table->dropColumn([
                'role_scope_type', 'role_scope_id', 'phone', 'locale',
                'two_factor_enabled', 'two_factor_secret', 'two_factor_confirmed_at',
                'last_login_at', 'is_active',
            ]);
        });
    }
};
