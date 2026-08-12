<?php
use App\Models\User;
use App\Models\Institute;
use App\Models\AcademicTerm;
use App\Models\InstituteClass;
use App\Models\Subject;
use App\Models\ClassSection;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::where('email', 'huzaifamunir455@gmail.com')
    ->orWhere('email', 'huzaifamunir45@gmail.com')
    ->first();

if (!$user) {
    // Create institute if needed
    $inst = Institute::firstOrCreate(
        ['slug' => 'apex-science-commerce-academy'],
        [
            'name' => 'Apex Science & Commerce Academy',
            'city' => 'Lahore',
            'subscription_tier' => 'premium',
            'is_active' => true,
            'education_systems' => ['matric', 'acca'],
        ]
    );

    $user = User::create([
        'name' => 'Huzaifa Munir (Principal)',
        'email' => 'huzaifamunir45@gmail.com',
        'identifier' => 'PRIN-HUDF-01',
        'password' => Hash::make('Hexxi0710@'),
        'role' => 'principal',
        'institute_id' => $inst->id,
    ]);
} else {
    $user->email = 'huzaifamunir45@gmail.com';
    $user->password = Hash::make('Hexxi0710@');
    $user->role = 'principal';
    
    if (!$user->institute_id) {
        $inst = Institute::firstOrCreate(
            ['slug' => 'apex-science-commerce-academy'],
            [
                'name' => 'Apex Science & Commerce Academy',
                'city' => 'Lahore',
                'subscription_tier' => 'premium',
                'is_active' => true,
                'education_systems' => ['matric', 'acca'],
            ]
        );
        $user->institute_id = $inst->id;
    }
    $user->save();
}

// Make sure an active academic term exists for this user's institute
$activeTerm = AcademicTerm::where('institute_id', $user->institute_id)->where('is_active', true)->first();
if (!$activeTerm) {
    $activeTerm = AcademicTerm::create([
        'institute_id' => $user->institute_id,
        'name' => '2025-2026',
        'start_date' => '2025-08-01',
        'end_date' => '2026-06-30',
        'is_active' => true,
    ]);
}

// Make sure classes, subjects, and sections exist
$class10 = InstituteClass::firstOrCreate(
    ['institute_id' => $user->institute_id, 'custom_name' => 'Grade 10']
);

$phy = Subject::firstOrCreate(
    ['institute_class_id' => $class10->id, 'subject_name' => 'Physics'],
    ['subject_code' => 'PHY-101', 'credit_hours' => 4]
);

$secA = ClassSection::firstOrCreate(
    ['institute_class_id' => $class10->id, 'section_name' => '10-A'],
    ['capacity' => 40]
);

echo "UPDATED_USER: {$user->email} | Password set to Hexxi0710@ | Institute ID: {$user->institute_id} | Role: {$user->role}\n";
