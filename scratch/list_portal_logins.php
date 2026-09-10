<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Models\User;

$users = User::all(['id', 'name', 'email', 'role', 'institute_id']);

echo "=== PORTAL CREDENTIALS ===\n\n";
foreach ($users as $u) {
    echo "Role: " . strtoupper($u->role) . " | Name: " . $u->name . "\n";
    echo "Email: " . $u->email . " | Institute ID: " . ($u->institute_id ?? 'N/A') . "\n";
    echo "--------------------------------------------------------\n";
}
