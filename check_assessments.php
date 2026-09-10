<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;

$records = Assessment::with(['subject', 'classSection.instituteClass'])->get();

echo "Total records: " . $records->count() . "\n";
foreach ($records as $a) {
    $cName = ($a->classSection?->instituteClass?->custom_name ?? 'Class') . ' ' . ($a->classSection?->section_name ?? '');
    $sName = $a->subject?->subject_name ?? 'Subject';
    $start = $a->start_time ? $a->start_time->format('Y-m-d H:i') : 'N/A';
    echo "ID: {$a->id} | Class: {$cName} | Subject: {$sName} | Start: {$start}\n";
}
