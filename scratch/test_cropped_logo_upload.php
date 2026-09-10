<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Models\Institute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

$req = Request::create('/', 'GET');
$app->instance('request', $req);

echo "========================================================================\n";
echo "TESTING CIRCULAR CROPPED LOGO UPLOAD & INSTANT MULTI-PORTAL PROPAGATION\n";
echo "========================================================================\n\n";

// 1. Get an institute to test (e.g. ID 1 Superior or ID 2 Apex)
$institute = Institute::withoutGlobalScopes()->find(1) ?? Institute::withoutGlobalScopes()->first();
echo "Testing with Institute: ID {$institute->id} ({$institute->name})\n";

// 2. Generate a 1x1 test red circle PNG base64
// Minimal 1x1 PNG
$base64Png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

// Simulate Global Admin calling update with cropped_logo
$adminUser = User::where('role', User::ROLE_GLOBAL_ADMIN)->first();
Auth::login($adminUser);

$updateReq = Request::create(
    route('global-admin.institutes.update', $institute),
    'PUT',
    [
        'name'                    => $institute->name,
        'subscription_tier'       => $institute->subscription_tier,
        'subscription_starts_at'  => $institute->subscription_starts_at?->format('Y-m-d'),
        'subscription_expires_at' => $institute->subscription_expires_at?->format('Y-m-d'),
        'contact_email'           => $institute->contact_email,
        'contact_phone'           => $institute->contact_phone,
        'city'                    => $institute->city,
        'is_active'               => 1,
        'cropped_logo'            => $base64Png,
        'education_systems'       => $institute->education_systems ?? [],
    ]
);
$app->instance('request', $updateReq);

$controller = new \App\Http\Controllers\GlobalAdmin\InstituteController();
$response = $controller->update($updateReq, $institute);

echo "1. Controller update executed. Response status: " . $response->getStatusCode() . "\n";
assert($response->isRedirection(), "Expected redirection after update");

// 3. Refresh institute from DB
$institute->refresh();
echo "   - Updated logo_path: " . ($institute->logo_path ?? 'NULL') . "\n";
echo "   - Updated icon_path: " . ($institute->icon_path ?? 'NULL') . "\n";
echo "   - Resolved logo_url: " . ($institute->logo_url ?? 'NULL') . "\n";

assert(!empty($institute->logo_path), "logo_path should not be empty");
assert(Storage::disk('public')->exists($institute->logo_path), "Uploaded file must exist in public storage");
echo "   ✓ Cropped image successfully decoded, saved, and attached to institute!\n\n";

// 4. Verify propagation across Student and Principal Portals
echo "2. Testing propagation to Principal Portal Views:\n";
$principal = User::where('role', User::ROLE_PRINCIPAL)->where('institute_id', $institute->id)->first() 
    ?? User::where('role', User::ROLE_PRINCIPAL)->first();

if ($principal) {
    // Ensure principal belongs to this institute for test
    $origInstId = $principal->institute_id;
    $principal->institute_id = $institute->id;
    $principal->save();
    $principal->unsetRelation('institute');

    Auth::login($principal);
    $principalDashboardReq = Request::create('/principal/dashboard', 'GET');
    $app->instance('request', $principalDashboardReq);

    $principalView = View::make('partials.institute-loader')->render();
    assert(str_contains($principalView, $institute->logo_path), "Principal spinning loader must contain new logo path");
    echo "   ✓ Principal spinning loader reflects updated institute logo: {$institute->logo_path}\n";

    $brandHeaderView = View::make('partials.brand-header')->render();
    assert(str_contains($brandHeaderView, $institute->logo_path), "Principal brand header must contain new logo path");
    echo "   ✓ Principal header reflects updated institute logo: {$institute->logo_path}\n\n";

    // Restore principal institute_id
    $principal->institute_id = $origInstId;
    $principal->save();
}

// 5. Verify propagation to Student Portal Views
echo "3. Testing propagation to Student Portal Views:\n";
$studentUser = User::where('role', User::ROLE_STUDENT)->first();
if ($studentUser) {
    $origStudentInstId = $studentUser->institute_id;
    $studentUser->institute_id = $institute->id;
    $studentUser->save();
    $studentUser->unsetRelation('institute');

    Auth::login($studentUser);
    $studentReq = Request::create('/student/dashboard', 'GET');
    $app->instance('request', $studentReq);

    $studentLoaderView = View::make('partials.institute-loader')->render();
    assert(str_contains($studentLoaderView, $institute->logo_path), "Student spinning loader must contain new logo path");
    echo "   ✓ Student spinning loader reflects updated institute logo: {$institute->logo_path}\n";

    $studentNavView = View::make('layouts.navigation')->render();
    assert(str_contains($studentNavView, $institute->logo_path), "Student navigation sidebar reflects updated institute logo");
    echo "   ✓ Student navigation sidebar reflects updated institute logo: {$institute->logo_path}\n\n";

    // Restore
    $studentUser->institute_id = $origStudentInstId;
    $studentUser->save();
}

echo "========================================================================\n";
echo "CIRCULAR CROPPED LOGO UPLOAD & PROPAGATION VERIFIED SUCCESSFULLY!\n";
echo "========================================================================\n";
