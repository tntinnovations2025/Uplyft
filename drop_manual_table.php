<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Schema;

Schema::dropIfExists('teacher_salary_slips');
echo 'Dropped manual teacher_salary_slips table. Exists now: '.(Schema::hasTable('teacher_salary_slips') ? 'YES' : 'NO').PHP_EOL;
