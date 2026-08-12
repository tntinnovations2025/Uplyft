<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\User::where('email', 'Minhajasghar5@gmail.com')->update([
    'is_delegated_admin' => false,
    'permissions'        => ['academics' => false, 'timetables' => true],
]);

echo "SUCCESS: Master Admin turned OFF while Timetables toggle is ON independently!\n";
