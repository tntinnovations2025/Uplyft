<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;
use Illuminate\Support\Facades\DB;

$conflicts = DB::table('assessments')
    ->select('class_section_id', 'start_time', DB::raw('COUNT(*) as total'))
    ->groupBy('class_section_id', 'start_time')
    ->having('total', '>', 1)
    ->get();

echo "Overlapping time conflicts in DB: " . $conflicts->count() . "\n";

foreach ($conflicts as $c) {
    $exams = Assessment::where('class_section_id', $c->class_section_id)
        ->where('start_time', $c->start_time)
        ->with('subject')
        ->get();
    echo "Class Section ID {$c->class_section_id} on {$c->start_time}:\n";
    foreach ($exams as $e) {
        echo " - ID: {$e->id} | Subject: {$e->subject?->subject_name} | Title: {$e->title}\n";
    }
}
