<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$credentials = [
    'email' => env('GLOBAL_ADMIN_EMAIL', 'admin@uplyft.com'),
    'password' => env('GLOBAL_ADMIN_PASSWORD', 'unset-global-admin-password-in-env'),
];

$admin = User::where('role', 'global_admin')->first();

if (!$admin) {
    $admin = User::create([
        'name' => 'Global Admin',
        'email' => $credentials['email'],
        'password' => Hash::make($credentials['password']),
        'role' => 'global_admin',
    ]);
    echo "CREATED Global Admin:\nEmail: {$credentials['email']}\nPassword: {$credentials['password']}\n";
} else {
    echo "EXISTS Global Admin:\nEmail: {$admin->email}\n";
}
