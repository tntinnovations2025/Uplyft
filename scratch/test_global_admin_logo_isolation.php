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
echo "TESTING GLOBAL ADMIN LOGO ISOLATION & FULL EDITABILITY\n";
echo "========================================================================\n\n";

$adminUser = User::where('role', User::ROLE_GLOBAL_ADMIN)->first();
$institute = Institute::withoutGlobalScopes()->first();

// 1. Verify Global Admin sees UPLYFT master branding even on Institute Edit page
$req = Request::create('/global-admin/institutes/' . $institute->id . '/edit', 'GET');
$app->instance('request', $req);
Auth::login($adminUser);

$headerHtml = View::make('partials.brand-header')->render();
echo "1. Global Admin Brand Header on Institute Edit Page:\n";
assert(str_contains($headerHtml, 'UPLYFT'), "Global Admin header must show UPLYFT");
assert(str_contains($headerHtml, 'GLOBAL GOVERNANCE'), "Global Admin header must show GLOBAL GOVERNANCE");
assert(!str_contains($headerHtml, $institute->name), "Global Admin header must NOT be overridden by tenant institute name");
echo "   ✓ Global Admin header strictly preserves master UPLYFT brand!\n\n";

// 2. Global Admin edits all details of the institute
echo "2. Testing editing all institute details:\n";
$controller = new \App\Http\Controllers\GlobalAdmin\InstituteController();
$updateReq = Request::create(
    route('global-admin.institutes.update', $institute),
    'PUT',
    [
        'name'                    => $institute->name,
        'subscription_tier'       => 'premium',
        'subscription_starts_at'  => '2026-01-01',
        'subscription_expires_at' => '2027-01-01',
        'contact_email'           => 'admin@' . $institute->slug . '.edu.pk',
        'contact_phone'           => '+92 300 1234567',
        'city'                    => 'Lahore',
        'country'                 => 'Pakistan',
        'is_active'               => 1,
        'education_systems'       => ['matric', 'higher_sec', 'o_a_level'],
    ]
);
$app->instance('request', $updateReq);
$response = $controller->update($updateReq, $institute);
assert($response->isRedirection(), "Expected redirect after successful institute update");

$institute->refresh();
assert($institute->subscription_tier === 'premium', "Subscription tier should be premium");
assert($institute->city === 'Lahore', "City should be Lahore");
assert(in_array('o_a_level', $institute->education_systems), "Education systems should contain o_a_level");
echo "   ✓ All institute details successfully updated at any time!\n\n";

// 3. Test Global Admin updating master platform logo via Profile settings
echo "3. Testing Global Admin master platform logo update in Profile Settings:\n";
$profileController = new \App\Http\Controllers\ProfileController();
$base64Logo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

$logoReq = Request::create(
    route('profile.platform-logo.update'),
    'POST',
    ['cropped_logo' => $base64Logo]
);
$logoReq->setUserResolver(fn() => $adminUser);
$app->instance('request', $logoReq);

$logoResponse = $profileController->updatePlatformLogo($logoReq);
assert($logoResponse->isRedirection(), "Expected redirect after platform logo update");

$savedPlatformLogo = PlatformSetting::get('platform_logo_path');
assert(!empty($savedPlatformLogo), "Platform logo path should be set in PlatformSetting");
echo "   ✓ Master UPLYFT platform logo saved: {$savedPlatformLogo}\n\n";

// 4. Verify Global Admin header now renders the updated UPLYFT Master Logo
$globalAdminDashboardReq = Request::create('/global-admin/dashboard', 'GET');
$app->instance('request', $globalAdminDashboardReq);
Auth::login($adminUser);

$updatedHeaderHtml = View::make('partials.brand-header')->render();
assert(str_contains($updatedHeaderHtml, $savedPlatformLogo), "Global Admin header must now render updated master platform logo");
echo "   ✓ Global Admin header displays the updated master platform logo!\n\n";

echo "========================================================================\n";
echo "GLOBAL ADMIN LOGO ISOLATION & FULL EDITABILITY VERIFIED SUCCESSFULLY!\n";
echo "========================================================================\n";
