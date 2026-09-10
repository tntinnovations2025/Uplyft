<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

foreach (['teachers', 'students', 'users', 'invoices', 'teacher_salary_slips', 'chat_histories', 'attendances', 'assignments', 'password_reset_notifications'] as $t) {
    echo $t.': '.(Schema::hasTable($t) ? 'YES' : 'NO').PHP_EOL;
}

echo '--- users columns ---'.PHP_EOL;
$cols = Schema::getColumnListing('users');
echo implode(', ', $cols).PHP_EOL;

echo '--- students columns ---'.PHP_EOL;
$cols = Schema::getColumnListing('students');
echo implode(', ', $cols).PHP_EOL;

echo '--- teachers columns ---'.PHP_EOL;
$cols = Schema::getColumnListing('teachers');
echo implode(', ', $cols).PHP_EOL;

echo '--- invoices columns ---'.PHP_EOL;
$cols = Schema::getColumnListing('invoices');
echo implode(', ', $cols).PHP_EOL;
