<?php
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = Schema::getColumnListing('academic_terms');
echo "ACADEMIC_TERMS_COLS: " . implode(', ', $cols) . "\n";
