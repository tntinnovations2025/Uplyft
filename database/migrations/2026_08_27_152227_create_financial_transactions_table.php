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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_head_id')->constrained('account_heads')->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['income', 'expense'])->default('expense');
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->string('payment_method')->default('Cash');
            $table->string('reference_number')->nullable();
            $table->string('receipt_image')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
