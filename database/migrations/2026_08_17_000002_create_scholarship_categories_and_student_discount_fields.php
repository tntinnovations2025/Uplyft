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
        Schema::create('scholarship_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained('institutes')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->json('questions')->nullable(); // List of custom verification questions
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->decimal('tax_percentage', 5, 2)->nullable()->after('guardian_tax_status');
            $table->foreignId('scholarship_category_id')->nullable()->constrained('scholarship_categories')->onDelete('set null')->after('base_fee');
            $table->string('scholarship_name')->nullable()->after('scholarship_category_id');
            $table->decimal('scholarship_percentage', 5, 2)->default(0.00)->after('scholarship_name');
            $table->text('scholarship_reason')->nullable()->after('scholarship_percentage');
            $table->json('scholarship_verification_answers')->nullable()->after('scholarship_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['scholarship_category_id']);
            $table->dropColumn([
                'tax_percentage',
                'scholarship_category_id',
                'scholarship_name',
                'scholarship_percentage',
                'scholarship_reason',
                'scholarship_verification_answers',
            ]);
        });

        Schema::dropIfExists('scholarship_categories');
    }
};
