<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Models\Institute;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

echo "========================================================================\n";
echo "TESTING UNIVERSAL LOADER & MULTI-TENANT BRAND LOGO INDEPENDENCE\n";
echo "========================================================================\n\n";

$adminUser = User::where('role', User::ROLE_GLOBAL_ADMIN)->first();
$principalUser = User::where('role', User::ROLE_PRINCIPAL)->first();
$studentUser = User::where('role', User::ROLE_STUDENT)->where('institute_id', $principalUser->institute_id)->first();
$teacherUser = User::where('role', User::ROLE_TEACHER)->where('institute_id', $principalUser->institute_id)->first();
$institute = $principalUser->institute;

$app->instance('request', Request::create('/global-admin/dashboard', 'GET'));

// 1. Global Admin Portal Loader uses UPLYFT Brand
Auth::login($adminUser);
$globalAdminLoaderHtml = View::make('partials.institute-loader')->render();
echo "1. Global Admin Portal Loader:\n";
assert(str_contains($globalAdminLoaderHtml, 'UPLYFT'), "Global Admin loader must show UPLYFT");
assert(!str_contains($globalAdminLoaderHtml, $institute->name), "Global Admin loader must NOT contain tenant campus name");
echo "   ✓ Global Admin loader preserves master UPLYFT brand identity.\n\n";

// 2. Tenant Portals Loader before change
$app->instance('request', Request::create('/dashboard', 'GET'));
Auth::login($principalUser);
$principalLoaderHtml = View::make('partials.institute-loader', ['institute' => $institute])->render();
echo "2. Principal Portal Loader:\n";
assert(str_contains($principalLoaderHtml, 'UPLYFT'), "Principal loader shows UPLYFT prefix");
assert(str_contains($principalLoaderHtml, e($institute->name)), "Principal loader shows tenant campus name");
echo "   ✓ Principal loader displays campus name (" . e($institute->name) . ").\n\n";

// 3. Principal updates their campus logo from Principal Settings
echo "3. Testing Principal updating Campus Logo in Principal Settings:\n";
$settingController = new \App\Http\Controllers\Principal\InstituteSettingController();
$fakeCroppedLogo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAEklEQVR42mNk+M9Qz8DAwMIAAAkBA/809wKqAAAAAElFTkSuQmCC';

$principalSettingsReq = Request::create(
    route('principal.settings.update'),
    'PUT',
    [
        'monthly_income_target' => 500000,
        'monthly_expense_budget' => 350000,
        'fee_due_day_of_month' => 10,
        'late_fee_fine_amount' => 500,
        'attendance_mode' => 'subject',
        'min_required_attendance_pct' => 75,
        'attendance_start_time' => '08:00',
        'attendance_end_time' => '14:00',
        'passing_percentage' => 40,
        'institute_name' => $institute->name,
        'cropped_logo' => $fakeCroppedLogo,
    ]
);
$principalSettingsReq->setUserResolver(fn() => $principalUser);
$app->instance('request', $principalSettingsReq);

$response = $settingController->update($principalSettingsReq);
assert($response->isRedirection(), "Expected redirect after saving principal settings");

$institute->refresh();
assert(!empty($institute->logo_path), "Institute logo_path should be updated");
echo "   ✓ Campus logo successfully updated by Principal: {$institute->logo_path}\n\n";

// 4. Verify Student and Teacher portals automatically reflect the new campus logo
$app->instance('request', Request::create('/student/dashboard', 'GET'));
Auth::login($studentUser);
$studentLoaderHtml = View::make('partials.institute-loader', ['institute' => $institute])->render();
assert(str_contains($studentLoaderHtml, $institute->logo_url), "Student portal must reflect updated campus logo");
echo "4. Student Portal Loader:\n";
echo "   ✓ Student portal displays the new campus logo!\n\n";

// 5. Verify Global Admin portal is STILL UPLYFT (Untouched)
$globalAdminReq = Request::create('/global-admin/dashboard', 'GET');
$app->instance('request', $globalAdminReq);
Auth::login($adminUser);
$globalAdminHeaderHtml = View::make('partials.brand-header')->render();
assert(str_contains($globalAdminHeaderHtml, 'UPLYFT'), "Global Admin header must remain UPLYFT");
assert(str_contains($globalAdminHeaderHtml, 'GLOBAL GOVERNANCE'), "Global Admin role subtitle must remain GLOBAL GOVERNANCE");
assert(!str_contains($globalAdminHeaderHtml, $institute->name), "Global Admin header must NOT have campus name");
echo "5. Global Admin Isolation Verification:\n";
echo "   ✓ Global Admin header and logo strictly remained UPLYFT master branding!\n\n";

echo "========================================================================\n";
echo "ALL MULTI-TENANT LOGO INDEPENDENCE ASSERTIONS PASSED!\n";
echo "========================================================================\n";
