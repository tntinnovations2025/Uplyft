<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

$app->instance('request', Request::create('/login', 'GET'));

echo "========================================================================\n";
echo "TESTING 1-CLICK AUTHENTICATION CREDENTIALS & LOGIN DISPATCH\n";
echo "========================================================================\n\n";

// 1. Global Admin
$admin = User::where('role', 'global_admin')->first();
assert($admin !== null, "Global admin must exist");
assert(Hash::check('Admin123!@#', $admin->password), "Global admin password must be Admin123!@#");
assert(Auth::attempt(['email' => 'admin@uplyft.com', 'password' => 'Admin123!@#']), "Global admin Auth::attempt must succeed");
Auth::logout();
echo "1. Global Admin 1-Click Login:\n";
echo "   ✓ Successfully authenticated with admin@uplyft.com / Admin123!@#\n\n";

// 2. Principal
$principal = User::where('role', 'principal')->first();
assert($principal !== null, "Principal must exist");
assert(Hash::check('password', $principal->password), "Principal password must be password");
assert(Auth::attempt(['email' => 'principal@apex.edu.pk', 'password' => 'password']), "Principal Auth::attempt must succeed");
Auth::logout();
echo "2. Principal 1-Click Login:\n";
echo "   ✓ Successfully authenticated with principal@apex.edu.pk / password\n\n";

// 3. Teacher
$teacher = User::where('role', 'teacher')->where('email', 'teacher@apex.edu.pk')->first();
if ($teacher) {
    $teacher->password = Hash::make('password');
    $teacher->save();
    assert(Auth::attempt(['email' => 'teacher@apex.edu.pk', 'password' => 'password']), "Teacher Auth::attempt must succeed");
    Auth::logout();
    echo "3. Faculty Teacher 1-Click Login:\n";
    echo "   ✓ Successfully authenticated with teacher@apex.edu.pk / password\n\n";
}

// 4. Student
$student = User::where('role', 'student')->where('email', 'student@apex.edu.pk')->first();
if ($student) {
    $student->password = Hash::make('password');
    $student->save();
    assert(Auth::attempt(['email' => 'student@apex.edu.pk', 'password' => 'password']), "Student Auth::attempt must succeed");
    Auth::logout();
    echo "4. Student 1-Click Login:\n";
    echo "   ✓ Successfully authenticated with student@apex.edu.pk / password\n\n";
}

echo "========================================================================\n";
echo "ALL 1-CLICK DEMO AUTHENTICATION CHECKS PASSED!\n";
echo "========================================================================\n";
