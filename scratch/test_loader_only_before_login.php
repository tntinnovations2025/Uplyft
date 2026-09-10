<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Models\Institute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

echo "========================================================================\n";
echo "TESTING POPUP-ONCE BEFORE LOGIN & INSTANT POST-LOGIN NAVIGATION\n";
echo "========================================================================\n\n";

$principalUser = User::where('role', User::ROLE_PRINCIPAL)->first();
$institute = $principalUser->institute;

// 1. Unauthenticated (Guest / Login Screen before login)
$app->instance('request', Request::create('/login', 'GET'));
Auth::logout();

$guestLoaderHtml = View::make('partials.institute-loader', ['institute' => $institute])->render();
echo "1. Guest / Login Page (Before Login):\n";
assert(str_contains($guestLoaderHtml, 'uplyft-global-loader'), "Loader screen MUST be rendered before login on entrance / login submit");
assert(str_contains($guestLoaderHtml, 'uprightCoinSpin360'), "Loader must contain 360-degree spin animation");
echo "   ✓ Spinning logo loader renders on guest / login screen before login!\n\n";

// 2. Authenticated Portal Navigation (After login - shifting between options)
Auth::login($principalUser);
$app->instance('request', Request::create('/dashboard', 'GET'));

$authDashboardLoaderHtml = View::make('partials.institute-loader', ['institute' => $institute])->render();
echo "2. Authenticated Portal Navigation (Inside Dashboard / Subpages):\n";
assert(empty(trim($authDashboardLoaderHtml)), "Loader screen MUST be empty/omitted when authenticated to ensure instant shifting");
echo "   ✓ Loader is completely omitted inside authenticated portals for instant navigation!\n\n";

// 3. Authenticated Settings Navigation
$app->instance('request', Request::create('/settings', 'GET'));
$authSettingsLoaderHtml = View::make('partials.institute-loader', ['institute' => $institute])->render();
assert(empty(trim($authSettingsLoaderHtml)), "Loader screen MUST be empty on settings page navigation");
echo "3. Authenticated Settings Page Navigation:\n";
echo "   ✓ Switching to Settings is instant with no spinning logo page delay!\n\n";

echo "========================================================================\n";
echo "ALL NAVIGATION PERFORMANCE & POPUP-ONCE ASSERTIONS PASSED!\n";
echo "========================================================================\n";
