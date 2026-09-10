<?php

require 'd:/UPLYFT/uplifyt/vendor/autoload.php';
$app = require_once 'd:/UPLYFT/uplifyt/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

foreach (\App\Models\Institute::withoutGlobalScopes()->get() as $i) {
    echo "ID: {$i->id} | Name: {$i->name} | Logo: " . ($i->logo_path ?? 'NULL') . " | Icon: " . ($i->icon_path ?? 'NULL') . "\n";
}
