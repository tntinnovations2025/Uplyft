<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Models\Institute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

$req = Request::create('/global-admin/institutes/1', 'GET');
$app->instance('request', $req);

$adminUser = User::where('role', User::ROLE_GLOBAL_ADMIN)->first();
Auth::login($adminUser);

$viewHtml = View::make('partials.brand-header')->render();

echo "========================================================================\n";
echo "RENDERED BRAND HEADER HTML:\n";
echo "========================================================================\n";
echo $viewHtml . "\n";
echo "========================================================================\n";

assert(str_contains($viewHtml, 'UPLYFT'), "Header must contain UPLYFT");
assert(str_contains($viewHtml, 'Superior'), "Header must contain Institute name");
assert(str_contains($viewHtml, 'w-12 h-12'), "Logo must have larger w-12 h-12 container");

echo "✓ VERIFIED: Brand header renders larger logo and UPLYFT — {Institute Name}!\n";
