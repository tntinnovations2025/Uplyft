<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Models\Institute;
use App\Models\User;
use App\View\Composers\InstituteBrandingComposer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

$req = Request::create('/', 'GET');
$app->instance('request', $req);

echo "========================================================================\n";
echo "MULTI-TENANT DYNAMIC BRANDING & CUSTOM SPINNING LOADER VERIFICATION\n";
echo "========================================================================\n\n";

// 1. Test View Composer with Guest / No User
Auth::logout();
$composer = new InstituteBrandingComposer();
$viewData = [];
$mockView = View::make('partials.institute-loader');
$composer->compose($mockView);
$brandingGuest = $mockView->getData()['instituteBranding'] ?? null;

echo "1. Guest / Unauthenticated Branding:\n";
echo "   - Name: " . ($brandingGuest->name ?? 'N/A') . "\n";
echo "   - Is Tenant: " . ($brandingGuest->is_tenant ? 'Yes' : 'No') . "\n";
echo "   - Powered By: " . ($brandingGuest->powered_by ?? 'N/A') . "\n";
assert(!$brandingGuest->is_tenant, "Guest should not be flagged as tenant");
echo "   ✓ Guest default branding resolved successfully.\n\n";

// 2. Test View Composer with Principal User
$principal = User::where('role', User::ROLE_PRINCIPAL)->first();
if ($principal) {
    Auth::login($principal);
    $mockViewPrincipal = View::make('partials.brand-header');
    $composer->compose($mockViewPrincipal);
    $brandingPrincipal = $mockViewPrincipal->getData()['instituteBranding'] ?? null;

    echo "2. Principal User (" . $principal->email . ") Branding:\n";
    echo "   - Institute: " . ($brandingPrincipal->name ?? 'N/A') . "\n";
    echo "   - Is Tenant: " . ($brandingPrincipal->is_tenant ? 'Yes' : 'No') . "\n";
    echo "   - Logo URL: " . ($brandingPrincipal->logo_url ?? 'None (Fallback)') . "\n";
    echo "   - Icon URL: " . ($brandingPrincipal->icon_url ?? 'None (Fallback)') . "\n";
    echo "   ✓ Principal tenant branding resolved successfully.\n\n";
}

// 3. Test View Composer with Student User
$studentUser = User::where('role', User::ROLE_STUDENT)->first();
if ($studentUser) {
    Auth::login($studentUser);
    $mockViewStudent = View::make('partials.institute-loader');
    $composer->compose($mockViewStudent);
    $brandingStudent = $mockViewStudent->getData()['instituteBranding'] ?? null;

    echo "3. Student User (" . $studentUser->email . ") Branding:\n";
    echo "   - Institute: " . ($brandingStudent->name ?? 'N/A') . "\n";
    echo "   - Initial: " . ($brandingStudent->initial ?? 'N/A') . "\n";
    echo "   ✓ Student tenant branding resolved successfully.\n\n";
}

// 4. Test Rendering of <InstituteLoader /> Partial
echo "4. Testing <InstituteLoader /> HTML Output:\n";
$loaderHtml = View::make('partials.institute-loader')->render();
assert(str_contains($loaderHtml, 'uplyft-global-loader'), "Loader container id missing");
assert(str_contains($loaderHtml, 'uplyft-tyre-spin-frame'), "Tyre spin animation container missing");
assert(str_contains($loaderHtml, 'uplyft'), "Platform title missing");
assert(str_contains($loaderHtml, 'powered by') && str_contains($loaderHtml, 'TNT Innovations'), "TNT Innovations attribution missing");
assert(str_contains($loaderHtml, 'tyreRollSpin'), "Tyre roll keyframes missing");

echo "   - Size: " . strlen($loaderHtml) . " bytes\n";
echo "   - Contains 'uplyft-tyre-spin-frame': Yes\n";
echo "   - Contains 'uplyft' stylized title: Yes\n";
echo "   - Contains 'powered by TNT Innovations': Yes\n";
echo "   ✓ Universal loader partial rendered perfectly!\n\n";

// 5. Test Rendering of <BrandHeader /> Partial
echo "5. Testing <BrandHeader /> HTML Output:\n";
$brandHeaderHtml = View::make('partials.brand-header')->render();
assert(str_contains($brandHeaderHtml, 'brand-header-container'), "Brand header container missing");
echo "   - Size: " . strlen($brandHeaderHtml) . " bytes\n";
echo "   ✓ Dynamic brand-header partial rendered perfectly!\n\n";

// 6. Test Model Accessors and Temporary Logo Update
echo "6. Testing Institute Model Logo & Icon Update / Accessors:\n";
$testInst = $principal && $principal->institute_id ? Institute::withoutGlobalScopes()->find($principal->institute_id) : Institute::withoutGlobalScopes()->first();
if ($testInst) {
    $origLogo = $testInst->logo_path;
    $origIcon = $testInst->icon_path;

    $testInst->logo_path = 'institute-logos/test-brand-logo.png';
    $testInst->icon_path = 'institute-logos/test-brand-icon.png';
    $testInst->save();

    echo "   - Set logo_path: " . $testInst->logo_path . "\n";
    echo "   - Logo URL: " . $testInst->logo_url . "\n";
    echo "   - Icon URL: " . $testInst->icon_url . "\n";
    echo "   - Has Custom Logo: " . ($testInst->hasCustomLogo() ? 'Yes' : 'No') . "\n";
    echo "   - Has Custom Icon: " . ($testInst->hasCustomIcon() ? 'Yes' : 'No') . "\n";

    assert($testInst->hasCustomLogo(), "hasCustomLogo should be true");
    assert(str_contains($testInst->logo_url, 'test-brand-logo.png'), "logo_url should contain logo filename");
    assert(str_contains($testInst->icon_url, 'test-brand-icon.png'), "icon_url should contain icon filename");

    // Test loader rendering with custom logo/icon
    if ($principal) {
        $principal->unsetRelation('institute');
        Auth::login($principal);
        $customLoaderHtml = View::make('partials.institute-loader')->render();
        assert(str_contains($customLoaderHtml, 'test-brand-icon.png') || str_contains($customLoaderHtml, 'test-brand-logo.png'), "Custom icon/logo should be present in loader");
        echo "   ✓ Custom logo and icon properly reflected in spinning loader HTML!\n";
    }

    // Restore original state
    $testInst->logo_path = $origLogo;
    $testInst->icon_path = $origIcon;
    $testInst->save();
    if ($principal) {
        $principal->unsetRelation('institute');
    }
}

// 7. Verify Key Page Layout Integration
echo "\n7. Testing Full Page Template Rendering across Portals:\n";

// Global Admin Dashboard
$adminUser = User::where('role', User::ROLE_GLOBAL_ADMIN)->first();
if ($adminUser) {
    Auth::login($adminUser);
    $req = Request::create('/global-admin/dashboard', 'GET');
    $app->instance('request', $req);
    $adminController = new \App\Http\Controllers\GlobalAdmin\DashboardController();
    $adminView = $adminController->index();
    $adminHtml = $adminView->render();
    assert(str_contains($adminHtml, 'uplyft-global-loader'), "Admin page missing global loader");
    assert(str_contains($adminHtml, 'GLOBAL GOVERNANCE'), "Admin page missing Global Governance label");
    echo "   ✓ Global Admin Dashboard rendered with dynamic branding & loader.\n";
}

// Principal Dashboard
if ($principal) {
    Auth::login($principal);
    $req = Request::create('/principal/dashboard', 'GET');
    $app->instance('request', $req);
    $principalController = new \App\Http\Controllers\Principal\PrincipalDashboardController();
    $principalView = $principalController->index($req);
    $principalHtml = $principalView->render();
    assert(str_contains($principalHtml, 'uplyft-global-loader'), "Principal page missing global loader");
    assert(str_contains($principalHtml, 'EXECUTIVE PRINCIPAL SUITE') || str_contains($principalHtml, 'brand-header-container'), "Principal page missing brand header");
    echo "   ✓ Principal Dashboard rendered with dynamic branding & loader.\n";
}

// Student Dashboard
if ($studentUser) {
    Auth::login($studentUser);
    $req = Request::create('/student/dashboard', 'GET');
    $app->instance('request', $req);
    $studentController = new \App\Http\Controllers\StudentPortalController();
    $studentView = $studentController->dashboard();
    $studentHtml = $studentView->render();
    assert(str_contains($studentHtml, 'uplyft-global-loader'), "Student page missing global loader");
    assert(str_contains($studentHtml, 'STUDENT WORKSPACE') || str_contains($studentHtml, 'brand-header-container'), "Student page missing brand header");
    echo "   ✓ Student Dashboard rendered with dynamic branding & loader.\n";
}

// Teacher Dashboard
$teacherUser = User::where('role', User::ROLE_TEACHER)->first();
if ($teacherUser) {
    Auth::login($teacherUser);
    $req = Request::create('/teacher/dashboard', 'GET');
    $app->instance('request', $req);
    $teacherController = new \App\Http\Controllers\TeacherPortalController();
    $teacherView = $teacherController->dashboard();
    $teacherHtml = $teacherView->render();
    assert(str_contains($teacherHtml, 'uplyft-global-loader'), "Teacher page missing global loader");
    assert(str_contains($teacherHtml, 'WORKSPACE') || str_contains($teacherHtml, 'PORTAL') || str_contains($teacherHtml, 'brand-header-container'), "Teacher page missing brand header");
    echo "   ✓ Teacher Dashboard rendered with dynamic branding & loader.\n";
}

// Guest Login Page
Auth::logout();
$loginView = View::make('auth.login', ['errors' => new \Illuminate\Support\ViewErrorBag()]);
$loginHtml = $loginView->render();
assert(str_contains($loginHtml, 'uplyft-global-loader'), "Login page missing global loader");
echo "   ✓ Auth / Login page rendered with universal loader.\n";

echo "\n========================================================================\n";
echo "ALL TESTS PASSED SUCCESSFULLY!\n";
echo "========================================================================\n";
