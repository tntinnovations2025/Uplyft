<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

$admin = App\Models\User::where('role', 'global_admin')->first();
$principal = App\Models\User::where('role', 'principal')->first();
$teacher = App\Models\User::where('role', 'teacher')->first();
$student = App\Models\User::where('role', 'student')->first();

echo "Admin password check (Admin123!@#): " . (Illuminate\Support\Facades\Hash::check('Admin123!@#', $admin->password) ? 'YES' : 'NO') . "\n";
echo "Principal password check (Admin123!@#): " . (Illuminate\Support\Facades\Hash::check('Admin123!@#', $principal->password) ? 'YES' : 'NO') . "\n";
echo "Teacher password check (Admin123!@#): " . (Illuminate\Support\Facades\Hash::check('Admin123!@#', $teacher->password) ? 'YES' : 'NO') . "\n";
echo "Student password check (Admin123!@#): " . (Illuminate\Support\Facades\Hash::check('Admin123!@#', $student->password) ? 'YES' : 'NO') . "\n";
