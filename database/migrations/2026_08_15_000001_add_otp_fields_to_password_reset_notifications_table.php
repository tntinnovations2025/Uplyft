<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds OTP, OTP expiration, and one-click cancellation token fields.
     */
    public function up(): void
    {
        Schema::table('password_reset_notifications', function (Blueprint $table) {
            $table->string('otp', 10)->nullable()->after('target_role');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
            $table->string('cancellation_token', 64)->nullable()->unique()->after('otp_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_notifications', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_expires_at', 'cancellation_token']);
        });
    }
};
