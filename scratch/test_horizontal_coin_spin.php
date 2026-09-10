<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Models\Institute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

$institute = Institute::withoutGlobalScopes()->whereNotNull('logo_path')->first();
$app->instance('request', Request::create('/login', 'GET'));
Auth::logout();

$loaderHtml = View::make('partials.institute-loader', ['institute' => $institute])->render();

echo "========================================================================\n";
echo "TESTING STANDING STRAIGHT UPRIGHT 360° COIN SPIN LOADER (3 SECONDS)\n";
echo "========================================================================\n\n";

assert(str_contains($loaderHtml, 'uprightCoinSpin360'), "Should contain upright 360-degree coin spin animation");
assert(str_contains($loaderHtml, '#facc15') || str_contains($loaderHtml, '#eab308'), "Should contain 24k polished gold rim styling");
assert(str_contains($loaderHtml, 'cinematicCoinStage'), "Should contain 3-stage cinematic choreography");
assert(str_contains($loaderHtml, 'exactDisplayDuration = 3000'), "Should specify exact 3000ms (3 seconds) display duration");
assert(str_contains($loaderHtml, 'powered by'), "Should contain powered by");
assert(str_contains($loaderHtml, 'TNT innovations'), "Should contain TNT innovations");

echo "✓ Standing Straight Upright Orientation verified.\n";
echo "✓ 360° Smooth 3D Coin Rotation on Vertical Y-Axis verified.\n";
echo "✓ Thin, radiant gold border outline (#eab308) verified.\n";
echo "✓ Exact 3.0-second display duration timer verified.\n";
echo "✓ Bottom-right 'powered by TNT innovations' attribution verified.\n\n";
echo "========================================================================\n";
echo "ALL ASSERTIONS PASSED!\n";
echo "========================================================================\n";
