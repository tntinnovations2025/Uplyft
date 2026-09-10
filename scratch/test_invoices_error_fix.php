<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$req = Illuminate\Http\Request::create('/student/invoices', 'GET');
$app->instance('request', $req);

$user = User::find(30); // User ID 30 from user's stack trace
Auth::login($user);

$response = $kernel->handle($req);

echo "Response status code: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() === 200) {
    echo "✓ SUCCESS: /student/invoices rendered cleanly with HTTP 200!\n";
} else {
    echo "Output snippet: " . substr($response->getContent(), 0, 500) . "\n";
}
