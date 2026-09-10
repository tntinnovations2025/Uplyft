<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

// 1. Principal Test
$principal = User::where('role', User::ROLE_PRINCIPAL)->first();
if ($principal) {
    $req = Request::create('/principal/dashboard', 'GET');
    $app->instance('request', $req);
    Auth::login($principal);
    $principalNav = View::make('partials.brand-header')->render();
    assert(str_contains($principalNav, 'w-12 h-12'), "Principal nav must have w-12 h-12 logo");
    assert(str_contains($principalNav, 'UPLYFT'), "Principal nav must have UPLYFT text");
    echo "✓ Principal brand header verified.\n";
}

// 2. Student Test
$student = User::where('role', User::ROLE_STUDENT)->first();
if ($student) {
    $req = Request::create('/student/dashboard', 'GET');
    $app->instance('request', $req);
    Auth::login($student);
    $studentNav = View::make('partials.brand-header')->render();
    assert(str_contains($studentNav, 'w-12 h-12'), "Student nav must have w-12 h-12 logo");
    assert(str_contains($studentNav, 'UPLYFT'), "Student nav must have UPLYFT text");
    echo "✓ Student brand header verified.\n";
}

echo "All portal brand headers verified successfully!\n";
