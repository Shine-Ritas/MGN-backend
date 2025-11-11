<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add columns to login_histories table
        Schema::table('login_histories', function (Blueprint $table) {
            $table->string('device_fingerprint', 255)->nullable()->after('device');
            $table->boolean('is_active')->default(false)->after('device_fingerprint');
            $table->index(['user_id', 'device_fingerprint', 'is_active']);
        });

        // Add columns to personal_access_tokens table (for API authentication)
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->string('device_fingerprint', 255)->nullable()->after('name');
            $table->index(['tokenable_id', 'tokenable_type', 'device_fingerprint']);
        });

        // Add columns to sessions table (for web authentication)
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('device_fingerprint', 255)->nullable()->after('user_agent');
            $table->index(['user_id', 'device_fingerprint']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_histories', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'device_fingerprint', 'is_active']);
            $table->dropColumn(['device_fingerprint', 'is_active']);
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropIndex(['tokenable_id', 'tokenable_type', 'device_fingerprint']);
            $table->dropColumn('device_fingerprint');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'device_fingerprint']);
            $table->dropColumn('device_fingerprint');
        });
    }
};
