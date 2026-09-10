<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
$app->instance('request', Illuminate\Http\Request::create('/', 'GET'));

use Illuminate\Support\Facades\Storage;

echo "Public disk path: " . Storage::disk('public')->path('') . "\n";
echo "Storage link exists in public: " . (file_exists(public_path('storage')) ? 'YES' : 'NO') . "\n";

// Let's create a sample test logo if not existing so we can verify rendering
$sampleDir = Storage::disk('public')->path('institute-logos');
if (!file_exists($sampleDir)) {
    mkdir($sampleDir, 0777, true);
}

$sampleSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">
  <circle cx="50" cy="50" r="46" fill="#4f46e5" stroke="#ffffff" stroke-width="4"/>
  <text x="50" y="58" font-family="Arial, sans-serif" font-size="34" font-weight="bold" fill="#ffffff" text-anchor="middle">A</text>
  <polygon points="50,16 60,36 40,36" fill="#fde047"/>
</svg>';

file_put_contents($sampleDir . '/apex-sample-logo.svg', $sampleSvg);
echo "Created sample logo: " . $sampleDir . '/apex-sample-logo.svg' . "\n";

// Assign sample logo to Apex (ID 2)
$apex = \App\Models\Institute::withoutGlobalScopes()->find(2);
if ($apex) {
    $apex->logo_path = 'institute-logos/apex-sample-logo.svg';
    $apex->save();
    echo "Assigned sample logo to {$apex->name} -> {$apex->logo_url}\n";
}
