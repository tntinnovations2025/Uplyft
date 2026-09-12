<?php

namespace Database\Seeders;

use App\Models\AcademicTerm;
use App\Models\AcademicTrack;
use App\Models\AcademicTrackSubject;
use App\Models\Assessment;
use App\Models\ClassSection;
use App\Models\ClassSubject;
use App\Models\DailyDiary;
use App\Models\Institute;
use App\Models\InstituteClass;
use App\Models\InstituteFeatureToggle;
use App\Models\InstituteSetting;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\StudentSubjectEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectSection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UplyftDummyDataSeeder extends Seeder
{
    /**
     * Demo account passwords for seeded users. Sourced from the environment so
     * no real credentials are committed; a random password is used when unset.
     *
     * @var array<string, string>
     */
    private array $demoPasswords = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->demoPasswords = [
            'staff' => env('DEMO_STAFF_PASSWORD', Str::random(14)),
            'student' => env('DEMO_STUDENT_PASSWORD', Str::random(14)),
            'crescent_staff' => env('DEMO_CRESCENT_STAFF_PASSWORD', Str::random(14)),
            'crescent_student' => env('DEMO_CRESCENT_STUDENT_PASSWORD', Str::random(14)),
        ];

        DB::transaction(function () {
            $this->seedTenantOneApexCollege();
            $this->seedTenantTwoCrescentModel();
        });
    }

    /**
     * â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
     * TENANT 1: Apex College of Commerce & Sciences (Hybrid College & ACCA)
     * â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
     */
    protected function seedTenantOneApexCollege(): array
    {
        // 1. Create or Find Institute
        $institute = Institute::withoutGlobalScopes()->updateOrCreate(
            ['slug' => 'apex-college'],
            [
                'name'                   => 'Apex College of Commerce & Sciences',
                'subscription_tier'      => 'premium',
                'subscription_starts_at' => now()->startOfYear(),
                'subscription_expires_at'=> now()->addYear(),
                'is_active'              => true,
                'is_onboarded'           => true,
                'contact_email'          => 'admin@apex.edu.pk',
                'contact_phone'          => '+92 42 35789001',
                'city'                   => 'Lahore',
                'country'                => 'Pakistan',
                'education_systems'      => ['higher_sec', 'acca'],
                'tenant_db_name'         => 'uplifyt_inst_apex_college',
            ]
        );

        InstituteFeatureToggle::updateOrCreate(
            ['institute_id' => $institute->id],
            [
                'lms_content'       => true,
                'ai_bot'            => true,
                'assessment_engine' => true,
                'practice_tests'    => true,
                'datesheet_manager' => true,
                'fee_invoicing'     => true,
                'teacher_portal'    => true,
                'classes_sections'  => true,
            ]
        );

        InstituteSetting::updateOrCreate(
            ['institute_id' => $institute->id],
            [
                'filer_tax_rate'                 => 0.05,
                'non_filer_tax_rate'             => 0.10,
                'allowed_absent_days_per_month'  => 3,
                'salary_disbursement_day'        => 5,
                'salary_deduction_type'          => 'pro_rata',
            ]
        );

        // 2. Active Term: Fall 2026-2027
        $activeTerm = AcademicTerm::withoutGlobalScopes()->updateOrCreate(
            [
                'institute_id' => $institute->id,
                'name'         => 'Fall 2026-2027',
            ],
            [
                'start_date'   => Carbon::parse('2026-08-15'),
                'end_date'     => Carbon::parse('2027-06-30'),
                'is_active'    => true,
            ]
        );

        // 3. Classes & Sections
        // Class 11 (HSSC-I)
        $class11 = InstituteClass::withoutGlobalScopes()->updateOrCreate(
            [
                'institute_id' => $institute->id,
                'custom_name'  => 'Class 11 (HSSC-I)',
            ],
            [
                'academic_term_id' => $activeTerm->id,
            ]
        );

        $section11A = ClassSection::updateOrCreate(
            [
                'institute_class_id' => $class11->id,
                'section_name'       => 'Section A',
            ],
            [
                'room_number' => 'Hall 101',
                'capacity'    => 50,
            ]
        );

        // ACCA Foundation
        $classAcca = InstituteClass::withoutGlobalScopes()->updateOrCreate(
            [
                'institute_id' => $institute->id,
                'custom_name'  => 'ACCA Foundation',
            ],
            [
                'academic_term_id' => $activeTerm->id,
            ]
        );

        $sectionAccaA = ClassSection::updateOrCreate(
            [
                'institute_class_id' => $classAcca->id,
                'section_name'       => 'Foundation Batch A',
            ],
            [
                'room_number' => 'Lab 2',
                'capacity'   => 40,
            ]
        );

        // 4. Subjects for Class 11
        $subEnglish = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $class11->id, 'subject_code' => 'ENG-11'],
            ['subject_name' => 'English']
        );
        $subUrdu = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $class11->id, 'subject_code' => 'URD-11'],
            ['subject_name' => 'Urdu']
        );
        $subIslamiat = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $class11->id, 'subject_code' => 'ISL-11'],
            ['subject_name' => 'Islamic Studies']
        );
        $subBiology = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $class11->id, 'subject_code' => 'BIO-11'],
            ['subject_name' => 'Biology']
        );
        $subChemistry = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $class11->id, 'subject_code' => 'CHE-11'],
            ['subject_name' => 'Chemistry']
        );
        $subPhysics = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $class11->id, 'subject_code' => 'PHY-11'],
            ['subject_name' => 'Physics']
        );
        $subComputer = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $class11->id, 'subject_code' => 'CS-11'],
            ['subject_name' => 'Computer Science']
        );
        $subMath = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $class11->id, 'subject_code' => 'MTH-11'],
            ['subject_name' => 'Mathematics']
        );

        // Class Subjects Configuration (Class 11 pool)
        $compulsory11 = [$subEnglish, $subUrdu, $subIslamiat];
        foreach ($compulsory11 as $sub) {
            ClassSubject::updateOrCreate(
                ['class_id' => $class11->id, 'subject_id' => $sub->id],
                ['institute_id' => $institute->id, 'subject_type' => 'compulsory', 'credit_hours' => 3]
            );
        }

        $electives11 = [$subBiology, $subChemistry, $subPhysics, $subComputer, $subMath];
        foreach ($electives11 as $sub) {
            ClassSubject::updateOrCreate(
                ['class_id' => $class11->id, 'subject_id' => $sub->id],
                ['institute_id' => $institute->id, 'subject_type' => 'elective', 'credit_hours' => 4]
            );
        }

        // Academic Tracks for Class 11
        $trackPreMed = AcademicTrack::updateOrCreate(
            ['class_id' => $class11->id, 'track_name' => 'FSc Pre-Medical'],
            ['institute_id' => $institute->id, 'track_code' => 'PRE-MED', 'description' => 'Medical track including Biology, Chemistry, and Physics.', 'is_active' => true]
        );
        $trackPreMed->subjects()->syncWithoutDetaching([$subBiology->id, $subChemistry->id, $subPhysics->id]);

        $trackIcs = AcademicTrack::updateOrCreate(
            ['class_id' => $class11->id, 'track_name' => 'ICS (Computer Science)'],
            ['institute_id' => $institute->id, 'track_code' => 'ICS', 'description' => 'Computer Science track with Math and Physics.', 'is_active' => true]
        );
        $trackIcs->subjects()->syncWithoutDetaching([$subComputer->id, $subMath->id, $subPhysics->id]);

        // Subjects for ACCA Foundation (FA1 & MA1)
        $subFA1 = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $classAcca->id, 'subject_code' => 'FA1'],
            ['subject_name' => 'Recording Financial Transactions (FA1)']
        );
        $subMA1 = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $classAcca->id, 'subject_code' => 'MA1'],
            ['subject_name' => 'Management Information (MA1)']
        );

        ClassSubject::updateOrCreate(
            ['class_id' => $classAcca->id, 'subject_id' => $subFA1->id],
            ['institute_id' => $institute->id, 'subject_type' => 'compulsory', 'credit_hours' => 5]
        );
        ClassSubject::updateOrCreate(
            ['class_id' => $classAcca->id, 'subject_id' => $subMA1->id],
            ['institute_id' => $institute->id, 'subject_type' => 'compulsory', 'credit_hours' => 5]
        );

        // 5. Faculty & Staff
        // Principal: EMP-01
        $principalUser = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'principal@apex.edu.pk'],
            [
                'name'              => 'Dr. Tariq Mehmood',
                'password'          => Hash::make($this->demoPasswords['staff']),
                'role'              => User::ROLE_PRINCIPAL,
                'staff_role'        => 'principal',
                'institute_id'      => $institute->id,
                'identifier'        => 'EMP-01',
                'basic_salary_pkr'  => 250000.00,
                'allowed_absent_days_per_month' => 4,
                'salary_disbursement_day' => 1,
                'salary_deduction_type' => 'pro_rata',
            ]
        );

        // Accountant: EMP-02
        $accountantUser = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'accountant@apex.edu.pk'],
            [
                'name'              => 'Kamran Akmal',
                'password'          => Hash::make($this->demoPasswords['staff']),
                'role'              => User::ROLE_TEACHER,
                'staff_role'        => 'accountant',
                'institute_id'      => $institute->id,
                'identifier'        => 'EMP-02',
                'basic_salary_pkr'  => 85000.00,
                'permissions'       => [
                    'accounts'        => true,
                    'accounts_view'   => true,
                    'accounts_edit'   => true,
                    'invoices'        => true,
                    'invoices_view'   => true,
                    'invoices_edit'   => true,
                ],
            ]
        );

        // Admission Counselor: EMP-03 (View-Only permissions)
        $counselorUser = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'counselor@apex.edu.pk'],
            [
                'name'              => 'Sara Ahmed',
                'password'          => Hash::make($this->demoPasswords['staff']),
                'role'              => User::ROLE_TEACHER,
                'staff_role'        => 'counselor',
                'institute_id'      => $institute->id,
                'identifier'        => 'EMP-03',
                'basic_salary_pkr'  => 70000.00,
                'permissions'       => [
                    'student_registration_view' => true,
                    'students_view'             => true,
                    'student_registration_edit' => false,
                    'students_edit'             => false,
                ],
            ]
        );

        // 4 Teachers
        $teacherData = [
            [
                'email'      => 'noman.bio@apex.edu.pk',
                'name'       => 'Prof. Noman Siddiqui',
                'identifier' => 'EMP-04',
                'salary'     => 95000.00,
                'subs'       => [$subBiology->id, $subChemistry->id],
                'sec'        => $section11A->id,
            ],
            [
                'email'      => 'usman.phy@apex.edu.pk',
                'name'       => 'Prof. Usman Javed',
                'identifier' => 'EMP-05',
                'salary'     => 90000.00,
                'subs'       => [$subPhysics->id, $subMath->id],
                'sec'        => $section11A->id,
            ],
            [
                'email'      => 'zainab.cs@apex.edu.pk',
                'name'       => 'Prof. Zainab Farooq',
                'identifier' => 'EMP-06',
                'salary'     => 92000.00,
                'subs'       => [$subComputer->id, $subEnglish->id],
                'sec'        => $section11A->id,
            ],
            [
                'email'      => 'bilal.acca@apex.edu.pk',
                'name'       => 'Prof. Bilal Qureshi',
                'identifier' => 'EMP-07',
                'salary'     => 110000.00,
                'subs'       => [$subFA1->id, $subMA1->id],
                'sec'        => $sectionAccaA->id,
            ],
        ];

        $teachers = [];
        foreach ($teacherData as $td) {
            $tUser = User::withoutGlobalScopes()->updateOrCreate(
                ['email' => $td['email']],
                [
                    'name'              => $td['name'],
                    'password'          => Hash::make($this->demoPasswords['staff']),
                    'role'              => User::ROLE_TEACHER,
                    'staff_role'        => 'teacher',
                    'institute_id'      => $institute->id,
                    'identifier'        => $td['identifier'],
                    'basic_salary_pkr'  => $td['salary'],
                ]
            );

            $tParts = explode(' ', $td['name']);
            Teacher::withoutGlobalScopes()->updateOrCreate(
                ['user_id' => $tUser->id],
                [
                    'institute_id'  => $institute->id,
                    'employee_id'   => $td['identifier'],
                    'first_name'    => $tParts[0] . (isset($tParts[1]) ? ' '.$tParts[1] : ''),
                    'last_name'     => $tParts[count($tParts)-1] ?? 'Faculty',
                    'email'              => $td['email'],
                    'qualification'      => 'Master of Science',
                    'matriculation_cert' => 'documents/matric.pdf',
                    'intermediate_cert'  => 'documents/inter.pdf',
                    'bachelors_cert'     => 'documents/bachelor.pdf',
                ]
            );

            foreach ($td['subs'] as $subId) {
                TeacherSubjectSection::updateOrCreate(
                    [
                        'academic_term_id' => $activeTerm->id,
                        'teacher_id'       => $tUser->id,
                        'subject_id'       => $subId,
                        'class_section_id' => $td['sec'],
                    ],
                    [
                        'periods_per_week' => 5,
                        'duration_minutes' => 60,
                    ]
                );
            }

            $teachers[] = $tUser;
        }

        // 6. Students & Enrollment
        // 10 Enrolled Students with Paid Invoices
        $enrolledDefs = [
            // 5 FSc Pre-Medical students
            ['name' => 'Ahmed Khan',   'email' => 'ahmed.k@apex.local',   'roll' => 'STU-101', 'track' => $trackPreMed, 'sec' => $section11A, 'subs' => [$subEnglish, $subUrdu, $subIslamiat, $subBiology, $subChemistry, $subPhysics]],
            ['name' => 'Fatima Noor',  'email' => 'fatima.n@apex.local',  'roll' => 'STU-102', 'track' => $trackPreMed, 'sec' => $section11A, 'subs' => [$subEnglish, $subUrdu, $subIslamiat, $subBiology, $subChemistry, $subPhysics]],
            ['name' => 'Hamza Ali',    'email' => 'hamza.a@apex.local',   'roll' => 'STU-103', 'track' => $trackPreMed, 'sec' => $section11A, 'subs' => [$subEnglish, $subUrdu, $subIslamiat, $subBiology, $subChemistry, $subPhysics]],
            ['name' => 'Ayesha Bibi',  'email' => 'ayesha.b@apex.local',  'roll' => 'STU-104', 'track' => $trackPreMed, 'sec' => $section11A, 'subs' => [$subEnglish, $subUrdu, $subIslamiat, $subBiology, $subChemistry, $subPhysics]],
            ['name' => 'Bilal Hassan', 'email' => 'bilal.h@apex.local',   'roll' => 'STU-105', 'track' => $trackPreMed, 'sec' => $section11A, 'subs' => [$subEnglish, $subUrdu, $subIslamiat, $subBiology, $subChemistry, $subPhysics]],
            // 3 ICS students
            ['name' => 'Danish Rauf',  'email' => 'danish.r@apex.local',  'roll' => 'STU-106', 'track' => $trackIcs,     'sec' => $section11A, 'subs' => [$subEnglish, $subUrdu, $subIslamiat, $subComputer, $subMath, $subPhysics]],
            ['name' => 'Hira Mani',    'email' => 'hira.m@apex.local',    'roll' => 'STU-107', 'track' => $trackIcs,     'sec' => $section11A, 'subs' => [$subEnglish, $subUrdu, $subIslamiat, $subComputer, $subMath, $subPhysics]],
            ['name' => 'Saad Tariq',   'email' => 'saad.t@apex.local',    'roll' => 'STU-108', 'track' => $trackIcs,     'sec' => $section11A, 'subs' => [$subEnglish, $subUrdu, $subIslamiat, $subComputer, $subMath, $subPhysics]],
            // 2 ACCA students
            ['name' => 'Omer Sharif',  'email' => 'omer.s@apex.local',    'roll' => 'STU-109', 'track' => null,          'sec' => $sectionAccaA, 'subs' => [$subFA1, $subMA1]],
            ['name' => 'Zainab Shah',  'email' => 'zainab.s@apex.local',  'roll' => 'STU-110', 'track' => null,          'sec' => $sectionAccaA, 'subs' => [$subFA1, $subMA1]],
        ];

        foreach ($enrolledDefs as $ed) {
            $names = explode(' ', $ed['name']);
            $sUser = User::withoutGlobalScopes()->updateOrCreate(
                ['email' => $ed['email']],
                [
                    'name'         => $ed['name'],
                    'password'     => Hash::make($this->demoPasswords['student']),
                    'role'         => User::ROLE_STUDENT,
                    'institute_id' => $institute->id,
                    'identifier'   => $ed['roll'],
                ]
            );

            $student = Student::withoutGlobalScopes()->updateOrCreate(
                ['user_id' => $sUser->id],
                [
                    'institute_id'        => $institute->id,
                    'academic_term_id'    => $activeTerm->id,
                    'first_name'          => $names[0],
                    'last_name'           => $names[1] ?? 'Student',
                    'email'               => $ed['email'],
                    'roll_number'         => $ed['roll'],
                    'date_of_birth'       => '2008-04-12',
                    'guardian_tax_status' => 'non-filer',
                    'admission_status'    => 'enrolled',
                    'annual_result_status'=> 'pending',
                    'class_section_id'    => $ed['sec']->id,
                    'academic_track_id'   => $ed['track']?->id,
                    'selected_subject_ids'=> array_map(fn($s) => $s->id, $ed['subs']),
                    'previous_marks'      => 85.00,
                    'base_fee'            => 45000.00,
                ]
            );

            // Paid Invoice
            Invoice::withoutGlobalScopes()->updateOrCreate(
                ['student_id' => $student->id, 'title' => 'Admission & First Term Tuition Fee'],
                [
                    'institute_id'   => $institute->id,
                    'amount_pkr'     => 45000.00,
                    'due_date'       => Carbon::parse('2026-09-01'),
                    'status'         => 'paid',
                ]
            );

            // Create subject enrollments
            foreach ($ed['subs'] as $sub) {
                StudentSubjectEnrollment::updateOrCreate(
                    [
                        'student_id' => $sUser->id,
                        'subject_id' => $sub->id,
                    ],
                    [
                        'institute_id'      => $institute->id,
                        'class_section_id'  => $ed['sec']->id,
                        'academic_track_id' => $ed['track']?->id,
                        'enrollment_status' => 'active',
                    ]
                );
            }
        }

        // 2 Admitted Students with Unpaid Fee Vouchers (blocked from subject enrollments)
        $unpaidDefs = [
            ['name' => 'Mustafa Kamal', 'email' => 'mustafa.k@apex.local', 'roll' => 'STU-111', 'sec' => $section11A,   'track' => $trackPreMed],
            ['name' => 'Maryam Nawaz',   'email' => 'maryam.n@apex.local',  'roll' => 'STU-112', 'sec' => $sectionAccaA, 'track' => null],
        ];

        foreach ($unpaidDefs as $ud) {
            $names = explode(' ', $ud['name']);
            $uUser = User::withoutGlobalScopes()->updateOrCreate(
                ['email' => $ud['email']],
                [
                    'name'         => $ud['name'],
                    'password'     => Hash::make($this->demoPasswords['student']),
                    'role'         => User::ROLE_STUDENT,
                    'institute_id' => $institute->id,
                    'identifier'   => $ud['roll'],
                ]
            );

            $unpaidStudent = Student::withoutGlobalScopes()->updateOrCreate(
                ['user_id' => $uUser->id],
                [
                    'institute_id'        => $institute->id,
                    'academic_term_id'    => $activeTerm->id,
                    'first_name'          => $names[0],
                    'last_name'           => $names[1] ?? 'Applicant',
                    'email'               => $ud['email'],
                    'roll_number'         => $ud['roll'],
                    'date_of_birth'       => '2008-07-20',
                    'guardian_tax_status' => 'non-filer',
                    'admission_status'    => 'pending_payment',
                    'annual_result_status'=> 'pending',
                    'class_section_id'    => $ud['sec']->id,
                    'academic_track_id'   => $ud['track']?->id,
                    'previous_marks'      => 85.00,
                    'base_fee'            => 45000.00,
                ]
            );

            // Unpaid Invoice
            Invoice::withoutGlobalScopes()->updateOrCreate(
                ['student_id' => $unpaidStudent->id, 'title' => 'Admission & Initial Tuition Voucher'],
                [
                    'institute_id'   => $institute->id,
                    'amount_pkr'     => 45000.00,
                    'due_date'       => Carbon::parse('2026-10-15'),
                    'status'         => 'unpaid',
                ]
            );

            // Zero StudentSubjectEnrollment records created for unpaid students!
        }

        // 7. LMS & Daily Diary Entries (with exact 14-day expiry)
        DailyDiary::updateOrCreate(
            [
                'institute_id'     => $institute->id,
                'class_section_id' => $section11A->id,
                'subject_id'       => $subBiology->id,
                'title'            => 'Cell Biology Lab Notes & Microscopy Assignment',
            ],
            [
                'teacher_id'    => $teachers[0]->id,
                'entry_type'    => 'homework',
                'content'       => 'Complete Question 1 to 5 on Chapter 2 (Cell Structures). Bring lab reports on Monday.',
                'assigned_date' => now()->toDateString(),
                'expires_at'    => now()->copy()->addDays(14),
                'is_active'     => true,
            ]
        );

        DailyDiary::updateOrCreate(
            [
                'institute_id'     => $institute->id,
                'class_section_id' => $section11A->id,
                'subject_id'       => $subPhysics->id,
                'title'            => 'Upcoming Physics Quiz: Vectors & Equilibrium',
            ],
            [
                'teacher_id'    => $teachers[1]->id,
                'entry_type'    => 'test_alert',
                'content'       => 'Test Alert: 25 MCQs on Chapter 2 Vectors scheduled for upcoming Friday.',
                'assigned_date' => now()->toDateString(),
                'expires_at'    => now()->copy()->addDays(14),
                'is_active'     => true,
            ]
        );

        DailyDiary::updateOrCreate(
            [
                'institute_id'     => $institute->id,
                'class_section_id' => $sectionAccaA->id,
                'subject_id'       => $subFA1->id,
                'title'            => 'Ledger Postings & Double Entry Bookkeeping',
            ],
            [
                'teacher_id'    => $teachers[3]->id,
                'entry_type'    => 'homework',
                'content'       => 'Practice exercises 4.1 to 4.8 from the BPP Foundation textbook.',
                'assigned_date' => now()->toDateString(),
                'expires_at'    => now()->copy()->addDays(14),
                'is_active'     => true,
            ]
        );

        // Datesheet Assessment for Tenant 1
        Assessment::updateOrCreate(
            [
                'subject_id'       => $subPhysics->id,
                'class_section_id' => $section11A->id,
                'title'            => 'Physics HSSC-I Midterm Exam',
            ],
            [
                'academic_term_id' => $activeTerm->id,
                'creator_id'       => $principalUser->id,
                'type'             => 'midterm',
                'total_marks'      => 100,
                'start_time'       => Carbon::parse('2026-10-20 09:00:00'),
                'end_time'         => Carbon::parse('2026-10-20 12:00:00'),
                'result_deadline'  => Carbon::parse('2026-10-27 23:59:59'),
                'status'           => 'published',
            ]
        );

        return [
            'institute' => $institute,
            'term'      => $activeTerm,
            'principal' => $principalUser,
            'accountant'=> $accountantUser,
            'counselor' => $counselorUser,
            'teachers'  => $teachers,
        ];
    }

    /**
     * â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
     * TENANT 2: Crescent Model High School (Matric System)
     * â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
     */
    protected function seedTenantTwoCrescentModel(): array
    {
        // 1. Create or Find Institute
        $institute = Institute::withoutGlobalScopes()->updateOrCreate(
            ['slug' => 'crescent-model'],
            [
                'name'                   => 'Crescent Model High School',
                'subscription_tier'      => 'standard',
                'subscription_starts_at' => now()->startOfYear(),
                'subscription_expires_at'=> now()->addYear(),
                'is_active'              => true,
                'is_onboarded'           => true,
                'contact_email'          => 'admin@crescent.edu.pk',
                'contact_phone'          => '+92 42 37890123',
                'city'                   => 'Lahore',
                'country'                => 'Pakistan',
                'education_systems'      => ['matric'],
                'tenant_db_name'         => 'uplifyt_inst_crescent_model',
            ]
        );

        InstituteFeatureToggle::updateOrCreate(
            ['institute_id' => $institute->id],
            [
                'lms_content'       => true,
                'ai_bot'            => true,
                'assessment_engine' => true,
                'datesheet_manager' => true,
                'fee_invoicing'     => true,
                'teacher_portal'    => true,
                'classes_sections'  => true,
            ]
        );

        InstituteSetting::updateOrCreate(
            ['institute_id' => $institute->id],
            [
                'filer_tax_rate'                 => 0.05,
                'non_filer_tax_rate'             => 0.10,
                'allowed_absent_days_per_month'  => 2,
                'salary_disbursement_day'        => 1,
                'salary_deduction_type'          => 'pro_rata',
            ]
        );

        // 2. Active Term: Academic Year 2026-2027
        $activeTerm = AcademicTerm::withoutGlobalScopes()->updateOrCreate(
            [
                'institute_id' => $institute->id,
                'name'         => 'Academic Year 2026-2027',
            ],
            [
                'start_date'   => Carbon::parse('2026-08-01'),
                'end_date'     => Carbon::parse('2027-05-31'),
                'is_active'    => true,
            ]
        );

        // 3. Classes: Grade 9 and Grade 10 (Pure Compulsory Pool)
        $grade9 = InstituteClass::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'custom_name' => 'Grade 9'],
            [
                'academic_term_id' => $activeTerm->id,
            ]
        );

        $section9A = ClassSection::updateOrCreate(
            ['institute_class_id' => $grade9->id, 'section_name' => 'Section A'],
            ['room_number' => 'Room 9A', 'capacity' => 45]
        );

        $grade10 = InstituteClass::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'custom_name' => 'Grade 10'],
            [
                'academic_term_id' => $activeTerm->id,
            ]
        );

        $section10A = ClassSection::updateOrCreate(
            ['institute_class_id' => $grade10->id, 'section_name' => 'Section A'],
            ['room_number' => 'Room 10A', 'capacity' => 45]
        );

        // Pure compulsory subjects for Grade 9
        $g9Math = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $grade9->id, 'subject_code' => 'MTH-9'],
            ['subject_name' => 'Mathematics']
        );
        $g9Eng = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $grade9->id, 'subject_code' => 'ENG-9'],
            ['subject_name' => 'English']
        );
        $g9Urdu = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $grade9->id, 'subject_code' => 'URD-9'],
            ['subject_name' => 'Urdu']
        );
        $g9Sci = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $grade9->id, 'subject_code' => 'SCI-9'],
            ['subject_name' => 'General Science']
        );
        $g9Isl = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $grade9->id, 'subject_code' => 'ISL-9'],
            ['subject_name' => 'Islamiat']
        );

        $g9Subjects = [$g9Math, $g9Eng, $g9Urdu, $g9Sci, $g9Isl];
        foreach ($g9Subjects as $sub) {
            ClassSubject::updateOrCreate(
                ['class_id' => $grade9->id, 'subject_id' => $sub->id],
                ['institute_id' => $institute->id, 'subject_type' => 'compulsory', 'credit_hours' => 3]
            );
        }

        // Pure compulsory subjects for Grade 10
        $g10Math = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $grade10->id, 'subject_code' => 'MTH-10'],
            ['subject_name' => 'Mathematics']
        );
        $g10Eng = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $grade10->id, 'subject_code' => 'ENG-10'],
            ['subject_name' => 'English']
        );
        $g10Urdu = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $grade10->id, 'subject_code' => 'URD-10'],
            ['subject_name' => 'Urdu']
        );
        $g10Pak = Subject::withoutGlobalScopes()->updateOrCreate(
            ['institute_class_id' => $grade10->id, 'subject_code' => 'PAK-10'],
            ['subject_name' => 'Pakistan Studies']
        );

        $g10Subjects = [$g10Math, $g10Eng, $g10Urdu, $g10Pak];
        foreach ($g10Subjects as $sub) {
            ClassSubject::updateOrCreate(
                ['class_id' => $grade10->id, 'subject_id' => $sub->id],
                ['institute_id' => $institute->id, 'subject_type' => 'compulsory', 'credit_hours' => 3]
            );
        }

        // 4. Staff with Conflicting Employee IDs (EMP-01, EMP-02, EMP-03, EMP-04)
        // Principal 2: Haji Muhammad Aslam (Identifier EMP-01 collision!)
        $principalUser2 = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'principal@crescent.edu.pk'],
            [
                'name'              => 'Haji Muhammad Aslam',
                'password'          => Hash::make($this->demoPasswords['crescent_staff']),
                'role'              => User::ROLE_PRINCIPAL,
                'staff_role'        => 'principal',
                'institute_id'      => $institute->id,
                'identifier'        => 'EMP-01', // COLLISION!
                'basic_salary_pkr'  => 180000.00,
                'allowed_absent_days_per_month' => 3,
                'salary_disbursement_day' => 1,
                'salary_deduction_type' => 'pro_rata',
            ]
        );

        // Accountant 2: Tariq Aziz (Identifier EMP-02 collision!)
        $accountantUser2 = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'accountant@crescent.edu.pk'],
            [
                'name'              => 'Tariq Aziz',
                'password'          => Hash::make($this->demoPasswords['crescent_staff']),
                'role'              => User::ROLE_TEACHER,
                'staff_role'        => 'accountant',
                'institute_id'      => $institute->id,
                'identifier'        => 'EMP-02', // COLLISION!
                'basic_salary_pkr'  => 65000.00,
                'permissions'       => [
                    'accounts'      => true,
                    'accounts_view' => true,
                    'accounts_edit' => true,
                ],
            ]
        );

        // Teacher 1: Rashid Mahmood (Identifier EMP-03 collision!)
        $t1User = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'rashid.teacher@crescent.edu.pk'],
            [
                'name'              => 'Rashid Mahmood',
                'password'          => Hash::make($this->demoPasswords['crescent_staff']),
                'role'              => User::ROLE_TEACHER,
                'staff_role'        => 'teacher',
                'institute_id'      => $institute->id,
                'identifier'        => 'EMP-03', // COLLISION!
                'basic_salary_pkr'  => 60000.00,
            ]
        );
        Teacher::withoutGlobalScopes()->updateOrCreate(
            ['user_id' => $t1User->id],
            [
                'institute_id'       => $institute->id,
                'employee_id'        => 'EMP-03',
                'first_name'         => 'Rashid',
                'last_name'          => 'Mahmood',
                'email'              => 'rashid.teacher@crescent.edu.pk',
                'qualification'      => 'B.Ed',
                'matriculation_cert' => 'documents/matric.pdf',
                'intermediate_cert'  => 'documents/inter.pdf',
                'bachelors_cert'     => 'documents/bachelor.pdf',
            ]
        );
        TeacherSubjectSection::updateOrCreate(
            [
                'academic_term_id' => $activeTerm->id,
                'teacher_id'       => $t1User->id,
                'subject_id'       => $g9Math->id,
                'class_section_id' => $section9A->id,
            ],
            ['periods_per_week' => 6, 'duration_minutes' => 45]
        );

        // Teacher 2: Munir Niazi (Identifier EMP-04 collision!)
        $t2User = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'munir.teacher@crescent.edu.pk'],
            [
                'name'              => 'Munir Niazi',
                'password'          => Hash::make($this->demoPasswords['crescent_staff']),
                'role'              => User::ROLE_TEACHER,
                'staff_role'        => 'teacher',
                'institute_id'      => $institute->id,
                'identifier'        => 'EMP-04', // COLLISION!
                'basic_salary_pkr'  => 62000.00,
            ]
        );
        Teacher::withoutGlobalScopes()->updateOrCreate(
            ['user_id' => $t2User->id],
            [
                'institute_id'       => $institute->id,
                'employee_id'        => 'EMP-04',
                'first_name'         => 'Munir',
                'last_name'          => 'Niazi',
                'email'              => 'munir.teacher@crescent.edu.pk',
                'qualification'      => 'M.A Urdu',
                'matriculation_cert' => 'documents/matric.pdf',
                'intermediate_cert'  => 'documents/inter.pdf',
                'bachelors_cert'     => 'documents/bachelor.pdf',
            ]
        );
        TeacherSubjectSection::updateOrCreate(
            [
                'academic_term_id' => $activeTerm->id,
                'teacher_id'       => $t2User->id,
                'subject_id'       => $g9Urdu->id,
                'class_section_id' => $section9A->id,
            ],
            ['periods_per_week' => 6, 'duration_minutes' => 45]
        );

        // 5. 5 Fully Enrolled Students with Conflicting Roll Numbers (STU-101 .. STU-105)
        $t2Students = [
            ['name' => 'Kashif Ali',   'email' => 'kashif.crescent@school.local',  'roll' => 'STU-101', 'sec' => $section9A,  'subs' => $g9Subjects],
            ['name' => 'Waqas Ahmad',  'email' => 'waqas.crescent@school.local',   'roll' => 'STU-102', 'sec' => $section9A,  'subs' => $g9Subjects],
            ['name' => 'Zubair Butt',  'email' => 'zubair.crescent@school.local',  'roll' => 'STU-103', 'sec' => $section9A,  'subs' => $g9Subjects],
            ['name' => 'Naveed Akhtar','email' => 'naveed.crescent@school.local',  'roll' => 'STU-104', 'sec' => $section10A, 'subs' => $g10Subjects],
            ['name' => 'Shahbaz Gill', 'email' => 'shahbaz.crescent@school.local', 'roll' => 'STU-105', 'sec' => $section10A, 'subs' => $g10Subjects],
        ];

        foreach ($t2Students as $tsd) {
            $names = explode(' ', $tsd['name']);
            $stuUser2 = User::withoutGlobalScopes()->updateOrCreate(
                ['email' => $tsd['email']],
                [
                    'name'         => $tsd['name'],
                    'password'     => Hash::make($this->demoPasswords['crescent_student']),
                    'role'         => User::ROLE_STUDENT,
                    'institute_id' => $institute->id,
                    'identifier'   => $tsd['roll'], // COLLISION with Tenant 1!
                ]
            );

            $student2 = Student::withoutGlobalScopes()->updateOrCreate(
                ['user_id' => $stuUser2->id],
                [
                    'institute_id'        => $institute->id,
                    'academic_term_id'    => $activeTerm->id,
                    'first_name'          => $names[0],
                    'last_name'           => $names[1] ?? 'Student',
                    'email'               => $tsd['email'],
                    'roll_number'         => $tsd['roll'],
                    'date_of_birth'       => '2010-02-15',
                    'guardian_tax_status' => 'non-filer',
                    'admission_status'    => 'enrolled',
                    'annual_result_status'=> 'pending',
                    'class_section_id'    => $tsd['sec']->id,
                    'previous_marks'      => 85.00,
                    'base_fee'            => 15000.00,
                ]
            );

            // Paid Invoice
            Invoice::withoutGlobalScopes()->updateOrCreate(
                ['student_id' => $student2->id, 'title' => 'Matric Annual Tuition Voucher'],
                [
                    'institute_id'   => $institute->id,
                    'amount_pkr'     => 15000.00,
                    'due_date'       => Carbon::parse('2026-09-01'),
                    'status'         => 'paid',
                ]
            );

            // Student Subject Enrollments
            foreach ($tsd['subs'] as $sub) {
                StudentSubjectEnrollment::updateOrCreate(
                    [
                        'student_id' => $stuUser2->id,
                        'subject_id' => $sub->id,
                    ],
                    [
                        'institute_id'      => $institute->id,
                        'class_section_id'  => $tsd['sec']->id,
                        'enrollment_status' => 'active',
                    ]
                );
            }
        }

        // Datesheet Assessment for Tenant 2 (Grade 9 Math)
        Assessment::updateOrCreate(
            [
                'subject_id'       => $g9Math->id,
                'class_section_id' => $section9A->id,
                'title'            => 'Grade 9 Matric Mathematics Final Exam',
            ],
            [
                'academic_term_id' => $activeTerm->id,
                'creator_id'       => $principalUser2->id,
                'type'             => 'final',
                'total_marks'      => 75,
                'start_time'       => Carbon::parse('2026-11-10 09:00:00'),
                'end_time'         => Carbon::parse('2026-11-10 12:00:00'),
                'result_deadline'  => Carbon::parse('2026-11-18 23:59:59'),
                'status'           => 'published',
            ]
        );

        return [
            'institute' => $institute,
            'term'      => $activeTerm,
            'principal' => $principalUser2,
            'accountant'=> $accountantUser2,
            'teachers'  => [$t1User, $t2User],
        ];
    }
}
