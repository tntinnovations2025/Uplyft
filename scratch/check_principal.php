<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

$p = App\Models\User::where('role', 'principal')->first();
echo "Principal User ID: " . $p->id . "\n";
echo "Principal Institute ID: " . $p->institute_id . "\n";
echo "Institute: " . ($p->institute ? $p->institute->name : "NULL") . "\n";
$inst = App\Models\Institute::withoutGlobalScopes()->find($p->institute_id);
echo "Institute without global scopes: " . ($inst ? $inst->name : "NULL") . "\n";
