<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routesToTest = [
    // Global Admin (8000)
    'http://127.0.0.1:8000/global-admin/login',
    // Principal (8001)
    'http://127.0.0.1:8001/login',
    // Student / Teacher LMS (8002)
    'http://127.0.0.1:8002/login',
];

echo "Testing HTTP Endpoints:\n";
foreach ($routesToTest as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    echo "[$httpCode] $url" . ($err ? " (Error: $err)" : "") . "\n";
}

// Test internal rendering of key views using Blade compiler
echo "\nTesting Blade View Compilation:\n";
$viewsToTest = [
    'layouts.navigation',
    'principal.layouts.app',
    'global-admin.layouts.app',
    'principal.subjects.index',
    'principal.classes-subjects.index',
    'principal.timetables.index',
    'principal.invoices.index',
    'principal.accounts.index',
    'principal.staff.index',
    'principal.students.index',
    'principal.settings.index',
    'principal.academic-terms.index',
    'principal.scholarships.index',
    'principal.rooms.index',
    'principal.teachers.availability',
    'principal.directory.index',
    'principal.security.edit',
    'principal.password-resets.index',
    'student.dashboard',
    'student.invoices',
    'student.attendance',
    'student.courses',
    'student.timetable',
    'student.datesheet',
    'student.exam_report',
    'student.lms',
    'lms.chatbot.index',
    'lms.materials.index',
    'lms.assessments.index',
    'lms.test_results.index',
    'lms.datesheet.index',
    'lms.exam_report.index',
    'lms.grades.weightages',
    'lms.practice_test.index',
    'global-admin.dashboard',
    'global-admin.institutes.index',
    'global-admin.accounts.principals-index'
];

foreach ($viewsToTest as $view) {
    try {
        if (view()->exists($view)) {
            echo "✓ [EXISTS] $view\n";
        } else {
            echo "✗ [MISSING] $view\n";
        }
    } catch (\Throwable $e) {
        echo "✗ [ERROR] $view: " . $e->getMessage() . "\n";
    }
}

echo "\nVerification complete!\n";
