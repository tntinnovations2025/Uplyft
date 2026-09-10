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

$inst = Institute::where('slug', 'apex-science-commerce-academy')->first();
if (!$inst) {
    $inst = new Institute();
    $inst->name = 'Apex Science & Commerce Academy';
    $inst->slug = 'apex-science-commerce-academy';
    $inst->city = 'Lahore';
    $inst->subscription_tier = 'premium';
    $inst->is_active = true;
    $inst->education_systems = ['matric', 'acca'];
    $inst->save();
}

$principal = User::where('email', 'principal@apex.edu.pk')->first();
if (!$principal) {
    $principal = new User();
    $principal->email = 'principal@apex.edu.pk';
}
$principal->name = 'Dr. Ahmed Khan (Principal)';
$principal->identifier = 'PRIN-APEX-01';
$principal->password = Hash::make('Principal123!@#');
$principal->role = 'principal';
$principal->institute_id = $inst->id;
$principal->save();

$term = AcademicTerm::where('institute_id', $inst->id)->where('name', '2025-2026')->first();
if (!$term) {
    $term = new AcademicTerm();
    $term->institute_id = $inst->id;
    $term->name = '2025-2026';
    $term->start_date = '2025-08-01';
    $term->end_date = '2026-06-30';
    $term->is_active = true;
    $term->save();
}

$class10 = InstituteClass::where('institute_id', $inst->id)->where('custom_name', 'Grade 10')->first();
if (!$class10) {
    $class10 = new InstituteClass();
    $class10->institute_id = $inst->id;
    $class10->custom_name = 'Grade 10';
    $class10->save();
}

$phy = Subject::where('institute_class_id', $class10->id)->where('subject_name', 'Physics')->first();
if (!$phy) {
    $phy = new Subject();
    $phy->institute_class_id = $class10->id;
    $phy->subject_name = 'Physics';
    $phy->subject_code = 'PHY-101';
    $phy->credit_hours = 4;
    $phy->save();
}

$secA = ClassSection::where('institute_class_id', $class10->id)->where('section_name', '10-A')->first();
if (!$secA) {
    $secA = new ClassSection();
    $secA->institute_class_id = $class10->id;
    $secA->section_name = '10-A';
    $secA->capacity = 40;
    $secA->save();
}

$teacher = User::where('email', 'usman@apex.edu.pk')->first();
if (!$teacher) {
    $teacher = new User();
    $teacher->email = 'usman@apex.edu.pk';
}
$teacher->name = 'Prof. Usman Ali';
$teacher->identifier = 'EMP#402';
$teacher->password = Hash::make('Teacher123!@#');
$teacher->role = 'teacher';
$teacher->institute_id = $inst->id;
$teacher->is_delegated_admin = true;
$teacher->save();

echo "MODULE3_SEEDED_SUCCESSFULLY: principal@apex.edu.pk / Principal123!@#\n";
