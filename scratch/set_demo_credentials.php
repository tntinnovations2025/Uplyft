<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Ensure standard demo passwords for easy local testing
$admin = User::where('role', 'global_admin')->first();
if ($admin) {
    $admin->password = Hash::make('Admin123!@#');
    $admin->save();
}

$principal = User::where('role', 'principal')->first();
if ($principal) {
    $principal->password = Hash::make('password');
    $principal->save();
}

$teacher = User::where('role', 'teacher')->first();
if ($teacher) {
    $teacher->password = Hash::make('password');
    $teacher->save();
}

$student = User::where('role', 'student')->first();
if ($student) {
    $student->password = Hash::make('password');
    $student->save();
}

echo "✓ Demo credentials set successfully:\n";
echo "Global Admin: " . $admin->email . " | Password: Admin123!@#\n";
echo "Principal: " . $principal->email . " | Password: password\n";
echo "Teacher: " . $teacher->email . " | Password: password\n";
echo "Student: " . $student->email . " | Password: password\n";
