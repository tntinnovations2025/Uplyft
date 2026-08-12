<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = User::where('role', 'global_admin')->first();

if (!$admin) {
    $admin = User::create([
        'name' => 'Global Admin',
        'email' => 'admin@uplyft.com',
        'password' => Hash::make('Admin123!@#'),
        'role' => 'global_admin',
    ]);
    echo "CREATED Global Admin:\nEmail: admin@uplyft.com\nPassword: Admin123!@#\n";
} else {
    echo "EXISTS Global Admin:\nEmail: {$admin->email}\n";
}
