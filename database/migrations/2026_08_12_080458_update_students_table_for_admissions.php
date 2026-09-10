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
        Schema::table('students', function (Blueprint $table) {
            $table->string('passport_picture_path')->nullable();
            $table->string('b_form_or_father_cnic')->nullable();
            $table->string('guardian_cnic')->nullable();
            $table->text('address')->nullable();
            $table->string('enrolled_class')->nullable();
            $table->decimal('base_fee', 10, 2)->default(50000);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'passport_picture_path',
                'b_form_or_father_cnic',
                'guardian_cnic',
                'address',
                'enrolled_class',
                'base_fee'
            ]);
        });
    }
};
