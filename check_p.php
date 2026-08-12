<?php
use App\Models\User;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = User::where('role', 'principal')->first();
if ($p) {
    echo "PRINCIPAL_FOUND: {$p->email}\n";
} else {
    echo "NO_PRINCIPAL\n";
}
