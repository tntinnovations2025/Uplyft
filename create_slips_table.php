<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (! Schema::hasTable('teacher_salary_slips')) {
    Schema::create('teacher_salary_slips', function (Blueprint $table) {
        $table->id();
        $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
        $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
        $table->string('title');
        $table->string('month_year');
        $table->decimal('amount', 12, 2)->nullable();
        $table->string('file_path');
        $table->text('notes')->nullable();
        $table->timestamps();
    });
    echo "teacher_salary_slips table created successfully!\n";
} else {
    echo "teacher_salary_slips table already exists!\n";
}
