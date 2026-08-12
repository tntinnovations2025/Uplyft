<?php

use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\Institute;
use App\Models\InstituteClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Create Institute
$institute = Institute::firstOrCreate(
    ['code' => 'APEX-01'],
    [
        'name'               => 'Apex Science & Commerce Academy',
        'city'               => 'Lahore',
        'subscription_tier'  => 'premium',
        'is_active'          => true,
        'education_systems'  => ['Matriculation / Intermediate', 'ACCA / Professional'],
    ]
);

// 2. Create Principal Account
$principal = User::updateOrCreate(
    ['email' => 'principal@apex.edu.pk'],
    [
        'name'         => 'Dr. Ahmed Khan (Principal)',
        'identifier'   => 'PRIN-APEX-01',
        'password'     => Hash::make('Principal123!@#'),
        'role'         => User::ROLE_PRINCIPAL,
        'institute_id' => $institute->id,
    ]
);

// 3. Create Active Academic Term
$term = AcademicTerm::updateOrCreate(
    ['institute_id' => $institute->id, 'name' => '2025-2026'],
    [
        'start_date' => '2025-08-01',
        'end_date'   => '2026-06-30',
        'is_active'  => true,
    ]
);

// 4. Create Classes
$class10 = InstituteClass::firstOrCreate(
    ['institute_id' => $institute->id, 'custom_name' => 'Grade 10']
);

$classFA1 = InstituteClass::firstOrCreate(
    ['institute_id' => $institute->id, 'custom_name' => 'FA1 - Financial Accounting']
);

// 5. Create Subjects
$phy = Subject::firstOrCreate(
    ['institute_class_id' => $class10->id, 'subject_name' => 'Physics'],
    ['subject_code' => 'PHY-101', 'credit_hours' => 4]
);

$chem = Subject::firstOrCreate(
    ['institute_class_id' => $class10->id, 'subject_name' => 'Chemistry'],
    ['subject_code' => 'CHEM-101', 'credit_hours' => 4]
);

$fa1Sub = Subject::firstOrCreate(
    ['institute_class_id' => $classFA1->id, 'subject_name' => 'Financial Accounting Paper 1'],
    ['subject_code' => 'FA1-ACCA', 'credit_hours' => 3]
);

// 6. Create Sections
$secA = ClassSection::firstOrCreate(
    ['institute_class_id' => $class10->id, 'section_name' => '10-A'],
    ['capacity' => 40]
);

$secB = ClassSection::firstOrCreate(
    ['institute_class_id' => $class10->id, 'section_name' => '10-B'],
    ['capacity' => 35]
);

$secFA1 = ClassSection::firstOrCreate(
    ['institute_class_id' => $classFA1->id, 'section_name' => 'FA1-Sec 1'],
    ['capacity' => 50]
);

// 7. Create Teacher
$teacher = User::updateOrCreate(
    ['email' => 'usman@apex.edu.pk'],
    [
        'name'               => 'Prof. Usman Ali',
        'identifier'         => 'EMP#402',
        'password'           => Hash::make('Teacher123!@#'),
        'role'               => User::ROLE_TEACHER,
        'institute_id'       => $institute->id,
        'is_delegated_admin' => true,
    ]
);

echo "SUCCESS: Seeded Module 3 Test Data!\n";
echo "Principal Email: principal@apex.edu.pk\n";
echo "Principal Password: Principal123!@#\n";
echo "Active Term: {$term->name}\n";
