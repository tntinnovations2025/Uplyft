<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutes', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('name');                          // Display name (set by Global Admin only)
            $table->string('slug')->unique();                // URL-safe identifier e.g. "city-grammar-school"
            $table->string('logo_path')->nullable();         // Stored in storage/app/public/institute-logos/

            // Subscription
            $table->enum('subscription_tier', ['basic', 'standard', 'premium'])->default('basic');
            $table->date('subscription_starts_at')->nullable();
            $table->date('subscription_expires_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true);     // Global Admin can deactivate entire institute
            $table->boolean('is_onboarded')->default(false); // Has completed onboarding flow

            // Metadata
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Pakistan');

            // Tenant database name (for future per-tenant DB isolation)
            $table->string('tenant_db_name')->nullable();    // e.g. uplifyt_inst_1

            $table->timestamps();
            $table->softDeletes();                           // Safe delete; data never hard-destroyed

            $table->index('slug');
            $table->index('is_active');
            $table->index('subscription_tier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutes');
    }
};
