<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

$req = Request::create('/global-admin/institutes/1', 'GET');
$app->instance('request', $req);

$adminUser = User::where('role', User::ROLE_GLOBAL_ADMIN)->first();
Auth::login($adminUser);

$loaderHtml = View::make('partials.institute-loader')->render();

echo "========================================================================\n";
echo "RENDERED INSTITUTE FULL-SCREEN LOADER HTML:\n";
echo "========================================================================\n";
echo $loaderHtml . "\n";
echo "========================================================================\n";

assert(str_contains($loaderHtml, 'uplyft-logo-frame'), "Must contain large uplyft-logo-frame");
assert(str_contains($loaderHtml, 'UPLYFT'), "Must contain UPLYFT headline");
assert(str_contains($loaderHtml, 'Superior'), "Must contain Institute name");
assert(str_contains($loaderHtml, 'uplyft-loader-bottom-right-attribution'), "Must contain bottom right attribution container");
assert(str_contains($loaderHtml, 'TNT innovations'), "Must contain TNT innovations");

echo "✓ VERIFIED: Full-screen splash loader renders perfectly!\n";
