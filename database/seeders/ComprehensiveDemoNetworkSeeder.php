<?php

namespace Database\Seeders;

use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\ClassSubject;
use App\Models\DailyDiary;
use App\Models\Institute;
use App\Models\InstituteClass;
use App\Models\InstituteFeatureToggle;
use App\Models\InstituteSetting;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\RoleDefaultAuthority;
use App\Models\Room;
use App\Models\Student;
use App\Models\StudentSubjectEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectSection;
use App\Models\Timetable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ComprehensiveDemoNetworkSeeder extends Seeder
{
    /**
     * Standard demo password used across all generated accounts for easy QA.
     */
    protected string $demoPassword;

    public function __construct()
    {
        $this->demoPassword = env('DEMO_PASSWORD', 'Password123!');
    }

    /**
     * Run the multi-campus network seeder.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Create Umbrella Organization
            $org = $this->seedOrganization();

            // 2. Seed Campus 1: O / A Levels (Cambridge International)
            $this->seedCambridgeCampus($org);

            // 3. Seed Campus 2: ACCA Professional Campus
            $this->seedAccaCampus($org);

            // 4. Seed Campus 3: Matriculation Campus
            $this->seedMatricCampus($org);

            // 5. Seed 6-Month Historical Financial Ledger (Apr 2026 - Sep 2026)
            (new DemoFinancialLedgerSeeder())->run();
        });

        if (isset($this->command)) {
            $this->command->info("\n========================================================");
            $this->command->info("  TRI-CAMPUS EDUCATIONAL NETWORK PROVISIONED!");
            $this->command->info("========================================================");
            $this->command->line("  Universal Password: <comment>{$this->demoPassword}</comment>\n");
            $this->command->line("  1. Cambridge Campus:  http://localhost/login (Select Cambridge Campus)");
            $this->command->line("     Principal:         principal.cambridge@apex.edu.pk");
            $this->command->line("     Coordinator:       coord.cambridge@apex.edu.pk");
            $this->command->line("     Accountant:        accountant.cambridge@apex.edu.pk");
            $this->command->line("     Teacher:           ahmed.camb@apex.edu.pk");
            $this->command->line("     Student:           daniyal.cambridge@student.apex.edu.pk\n");
            $this->command->line("  2. ACCA Campus:       http://localhost/login (Select ACCA Campus)");
            $this->command->line("     Principal / Dean:  principal.acca@apex.edu.pk");
            $this->command->line("     Coordinator:       coord.acca@apex.edu.pk");
            $this->command->line("     Accountant:        accountant.acca@apex.edu.pk");
            $this->command->line("     Teacher:           hamza.acca@apex.edu.pk");
            $this->command->line("     Student:           omer.acca@student.apex.edu.pk\n");
            $this->command->line("  3. Matric Campus:     http://localhost/login (Select Matric Campus)");
            $this->command->line("     Principal:         principal.matric@apex.edu.pk");
            $this->command->line("     Coordinator:       coord.matric@apex.edu.pk");
            $this->command->line("     Accountant:        accountant.matric@apex.edu.pk");
            $this->command->line("     Teacher:           rashid.matr@apex.edu.pk");
            $this->command->line("     Student:           ali.matric@student.apex.edu.pk\n");
            $this->command->info("========================================================");
        }
    }

    /**
     * ── 1. Umbrella Organization Network ─────────────────────────────
     */
    protected function seedOrganization(): Organization
    {
        return Organization::updateOrCreate(
            ['slug' => 'apex-network'],
            [
                'name'         => 'Apex Global Educational Network',
                'code'         => 'ORG-2026-APEX',
                'max_campuses' => 10,
                'is_active'    => true,
            ]
        );
    }

    /**
     * ── 2. Campus 1: Cambridge International (O / A Levels) ───────────
     */
    protected function seedCambridgeCampus(Organization $org): void
    {
        $institute = Institute::withoutGlobalScopes()->updateOrCreate(
            ['slug' => 'apex-cambridge'],
            [
                'organization_id'        => $org->id,
                'name'                   => 'Apex Cambridge International Campus (O/A Levels)',
                'subscription_tier'      => 'premium',
                'subscription_starts_at' => now()->startOfYear(),
                'subscription_expires_at'=> now()->addYears(2),
                'is_active'              => true,
                'is_onboarded'           => true,
                'contact_email'          => 'info.cambridge@apex.edu.pk',
                'contact_phone'          => '+92 51 2891001',
                'city'                   => 'Islamabad',
                'country'                => 'Pakistan',
                'education_systems'      => ['o_a_level'],
                'tenant_db_name'         => 'uplifyt_inst_apex_cambridge',
            ]
        );

        $this->seedTogglesAndSettings($institute);

        // Term
        $term = AcademicTerm::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'name' => 'Cambridge Academic Session 2026-2027'],
            ['start_date' => Carbon::parse('2026-08-15'), 'end_date' => Carbon::parse('2027-06-30'), 'is_active' => true]
        );

        // Rooms
        $room101 = Room::updateOrCreate(['institute_id' => $institute->id, 'room_number' => 'C-101'], ['building_block' => 'Cambridge Block', 'capacity' => 35]);
        $room102 = Room::updateOrCreate(['institute_id' => $institute->id, 'room_number' => 'C-102'], ['building_block' => 'Cambridge Block', 'capacity' => 35]);
        $scienceLab = Room::updateOrCreate(['institute_id' => $institute->id, 'room_number' => 'Science Lab S-1'], ['building_block' => 'Laboratory Wing', 'capacity' => 30]);
        $computerLab = Room::updateOrCreate(['institute_id' => $institute->id, 'room_number' => 'Computing Center C-2'], ['building_block' => 'Tech Wing', 'capacity' => 40]);

        // Classes & Sections
        $classOLevel = InstituteClass::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'custom_name' => 'Cambridge O-Level (Grade 10 / O2)'],
            ['academic_term_id' => $term->id]
        );
        $secOLevelA = ClassSection::updateOrCreate(
            ['institute_class_id' => $classOLevel->id, 'section_name' => 'O-Level Section A'],
            ['room_number' => 'C-101', 'capacity' => 35]
        );

        $classALevel = InstituteClass::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'custom_name' => 'Cambridge A-Level (AS / A2)'],
            ['academic_term_id' => $term->id]
        );
        $secALevelAlpha = ClassSection::updateOrCreate(
            ['institute_class_id' => $classALevel->id, 'section_name' => 'A-Level Science Section Alpha'],
            ['room_number' => 'C-102', 'capacity' => 35]
        );

        // Subjects for O-Level
        $subMathO = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classOLevel->id, 'subject_code' => 'MATH-4024'], ['subject_name' => 'Mathematics (Syllabus D)']);
        $subPhysO = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classOLevel->id, 'subject_code' => 'PHYS-5054'], ['subject_name' => 'Physics (O-Level)']);
        $subChemO = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classOLevel->id, 'subject_code' => 'CHEM-5070'], ['subject_name' => 'Chemistry (O-Level)']);
        $subCsO   = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classOLevel->id, 'subject_code' => 'CS-2210'], ['subject_name' => 'Computer Science (O-Level)']);
        $subEngO  = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classOLevel->id, 'subject_code' => 'ENG-1123'], ['subject_name' => 'English Language (O-Level)']);

        foreach ([$subMathO, $subPhysO, $subChemO, $subCsO, $subEngO] as $s) {
            ClassSubject::updateOrCreate(['class_id' => $classOLevel->id, 'subject_id' => $s->id], ['institute_id' => $institute->id, 'subject_type' => 'compulsory', 'credit_hours' => 4]);
        }

        // Subjects for A-Level
        $subMathA = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classALevel->id, 'subject_code' => 'MATH-9709'], ['subject_name' => 'Mathematics (A-Level)']);
        $subPhysA = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classALevel->id, 'subject_code' => 'PHYS-9702'], ['subject_name' => 'Physics (A-Level)']);
        $subChemA = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classALevel->id, 'subject_code' => 'CHEM-9701'], ['subject_name' => 'Chemistry (A-Level)']);
        $subEconA = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classALevel->id, 'subject_code' => 'ECON-9708'], ['subject_name' => 'Economics (A-Level)']);

        foreach ([$subMathA, $subPhysA, $subChemA, $subEconA] as $s) {
            ClassSubject::updateOrCreate(['class_id' => $classALevel->id, 'subject_id' => $s->id], ['institute_id' => $institute->id, 'subject_type' => 'compulsory', 'credit_hours' => 5]);
        }

        // Staff Accounts
        // 1. Principal
        $princUser = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'principal.cambridge@apex.edu.pk'],
            [
                'name' => 'Dr. Tariq Mehmood',
                'password' => Hash::make($this->demoPassword),
                'role' => User::ROLE_PRINCIPAL,
                'institute_id' => $institute->id,
                'organization_id' => $org->id,
                'identifier' => 'PRIN-CAMB-01',
                'is_primary_principal' => true,
            ]
        );
        if (!$org->owner_user_id) {
            $org->update(['owner_user_id' => $princUser->id]);
        }

        // 2. Academic Coordinator
        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'coord.cambridge@apex.edu.pk'],
            [
                'name' => 'Mr. Bilal Saeed (Coordinator)',
                'password' => Hash::make($this->demoPassword),
                'role' => User::ROLE_TEACHER,
                'staff_role' => 'coordinator',
                'institute_id' => $institute->id,
                'organization_id' => $org->id,
                'identifier' => 'COORD-CAMB-01',
            ]
        );

        // 3. Accountant
        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'accountant.cambridge@apex.edu.pk'],
            [
                'name' => 'Ms. Sarah Khan (Accountant)',
                'password' => Hash::make($this->demoPassword),
                'role' => User::ROLE_TEACHER,
                'staff_role' => 'accountant',
                'institute_id' => $institute->id,
                'organization_id' => $org->id,
                'identifier' => 'ACC-CAMB-01',
            ]
        );

        // 4. Faculty Teachers
        $tAhmed = $this->createTeacher($institute, 'Sir Ahmed Hassan', 'ahmed.camb@apex.edu.pk', 'FAC-CAMB-01', 'O/A Level Mathematics', 120000);
        $tZain  = $this->createTeacher($institute, 'Dr. Zain Ul Abideen', 'zain.camb@apex.edu.pk', 'FAC-CAMB-02', 'Cambridge Physics & Chemistry', 135000);
        $tAyesh = $this->createTeacher($institute, 'Ms. Ayesha Siddiqa', 'ayesha.camb@apex.edu.pk', 'FAC-CAMB-03', 'English & Economics', 110000);
        $tSalm  = $this->createTeacher($institute, 'Sir Salman Farooq', 'salman.camb@apex.edu.pk', 'FAC-CAMB-04', 'Computer Science', 115000);

        // Allocations
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tAhmed->id, 'subject_id' => $subMathO->id, 'class_section_id' => $secOLevelA->id], ['periods_per_week' => 5, 'duration_minutes' => 50]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tAhmed->id, 'subject_id' => $subMathA->id, 'class_section_id' => $secALevelAlpha->id], ['periods_per_week' => 5, 'duration_minutes' => 50]);

        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tZain->id, 'subject_id' => $subPhysO->id, 'class_section_id' => $secOLevelA->id], ['periods_per_week' => 5, 'duration_minutes' => 50]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tZain->id, 'subject_id' => $subChemO->id, 'class_section_id' => $secOLevelA->id], ['periods_per_week' => 5, 'duration_minutes' => 50]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tZain->id, 'subject_id' => $subPhysA->id, 'class_section_id' => $secALevelAlpha->id], ['periods_per_week' => 5, 'duration_minutes' => 50]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tZain->id, 'subject_id' => $subChemA->id, 'class_section_id' => $secALevelAlpha->id], ['periods_per_week' => 5, 'duration_minutes' => 50]);

        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tAyesh->id, 'subject_id' => $subEngO->id, 'class_section_id' => $secOLevelA->id], ['periods_per_week' => 5, 'duration_minutes' => 50]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tAyesh->id, 'subject_id' => $subEconA->id, 'class_section_id' => $secALevelAlpha->id], ['periods_per_week' => 5, 'duration_minutes' => 50]);

        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tSalm->id, 'subject_id' => $subCsO->id, 'class_section_id' => $secOLevelA->id], ['periods_per_week' => 5, 'duration_minutes' => 50]);

        // Clash-Free Timetables (Monday through Friday)
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            // O-Level Section A (Room C-101 / Labs)
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secOLevelA->id, 'day_of_week' => $day, 'start_time' => '08:30:00'],
                ['subject_id' => $subEngO->id, 'teacher_id' => $tAyesh->id, 'room_id' => $room101->id, 'end_time' => '09:20:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secOLevelA->id, 'day_of_week' => $day, 'start_time' => '09:25:00'],
                ['subject_id' => $subMathO->id, 'teacher_id' => $tAhmed->id, 'room_id' => $room101->id, 'end_time' => '10:15:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secOLevelA->id, 'day_of_week' => $day, 'start_time' => '10:20:00'],
                ['subject_id' => $subCsO->id, 'teacher_id' => $tSalm->id, 'room_id' => $computerLab->id, 'end_time' => '11:10:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secOLevelA->id, 'day_of_week' => $day, 'start_time' => '11:30:00'],
                ['subject_id' => $subPhysO->id, 'teacher_id' => $tZain->id, 'room_id' => $scienceLab->id, 'end_time' => '12:20:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secOLevelA->id, 'day_of_week' => $day, 'start_time' => '12:25:00'],
                ['subject_id' => $subChemO->id, 'teacher_id' => $tZain->id, 'room_id' => $scienceLab->id, 'end_time' => '13:15:00']
            );

            // A-Level Alpha (Room C-102) — Zero teacher and zero room overlaps!
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secALevelAlpha->id, 'day_of_week' => $day, 'start_time' => '08:30:00'],
                ['subject_id' => $subMathA->id, 'teacher_id' => $tAhmed->id, 'room_id' => $room102->id, 'end_time' => '09:20:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secALevelAlpha->id, 'day_of_week' => $day, 'start_time' => '09:25:00'],
                ['subject_id' => $subEconA->id, 'teacher_id' => $tAyesh->id, 'room_id' => $room102->id, 'end_time' => '10:15:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secALevelAlpha->id, 'day_of_week' => $day, 'start_time' => '10:20:00'],
                ['subject_id' => $subPhysA->id, 'teacher_id' => $tZain->id, 'room_id' => $room102->id, 'end_time' => '11:10:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secALevelAlpha->id, 'day_of_week' => $day, 'start_time' => '11:30:00'],
                ['subject_id' => $subChemA->id, 'teacher_id' => $tZain->id, 'room_id' => $room102->id, 'end_time' => '12:20:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secALevelAlpha->id, 'day_of_week' => $day, 'start_time' => '12:25:00'],
                ['subject_id' => $subEconA->id, 'teacher_id' => $tAyesh->id, 'room_id' => $room102->id, 'end_time' => '13:15:00']
            );
        }

        // Students
        $this->createStudent(
            $institute, $term, $secOLevelA,
            'Daniyal Khan', 'daniyal.cambridge@student.apex.edu.pk', 'CAMB-2026-001',
            [$subMathO, $subPhysO, $subChemO, $subCsO, $subEngO], 65000, 'paid'
        );
        $this->createStudent(
            $institute, $term, $secALevelAlpha,
            'Zainab Fatima', 'zainab.cambridge@student.apex.edu.pk', 'CAMB-2026-002',
            [$subMathA, $subPhysA, $subChemA, $subEconA], 75000, 'paid'
        );
    }

    /**
     * ── 3. Campus 2: Professional Accountancy (ACCA) ──────────────────
     */
    protected function seedAccaCampus(Organization $org): void
    {
        $institute = Institute::withoutGlobalScopes()->updateOrCreate(
            ['slug' => 'apex-acca'],
            [
                'organization_id'        => $org->id,
                'name'                   => 'Apex School of Professional Accountancy (ACCA)',
                'subscription_tier'      => 'premium',
                'subscription_starts_at' => now()->startOfYear(),
                'subscription_expires_at'=> now()->addYears(2),
                'is_active'              => true,
                'is_onboarded'           => true,
                'contact_email'          => 'info.acca@apex.edu.pk',
                'contact_phone'          => '+92 42 35789100',
                'city'                   => 'Lahore',
                'country'                => 'Pakistan',
                'education_systems'      => ['acca'],
                'tenant_db_name'         => 'uplifyt_inst_apex_acca',
            ]
        );

        $this->seedTogglesAndSettings($institute);

        // Term
        $term = AcademicTerm::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'name' => 'ACCA Professional Session 2026-2027'],
            ['start_date' => Carbon::parse('2026-08-15'), 'end_date' => Carbon::parse('2027-06-30'), 'is_active' => true]
        );

        // Facilities / Rooms
        $hall101 = Room::updateOrCreate(['institute_id' => $institute->id, 'room_number' => 'Executive Hall 1'], ['building_block' => 'ACCA Tower', 'capacity' => 60]);
        $auditoriumA = Room::updateOrCreate(['institute_id' => $institute->id, 'room_number' => 'Auditorium A'], ['building_block' => 'Main Wing', 'capacity' => 120]);
        $finLab = Room::updateOrCreate(['institute_id' => $institute->id, 'room_number' => 'Financial Modeling Lab'], ['building_block' => 'Tech Floor', 'capacity' => 45]);

        // Classes & Sections
        $classKnowledge = InstituteClass::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'custom_name' => 'ACCA Applied Knowledge'],
            ['academic_term_id' => $term->id]
        );
        $secKnowledgeK1 = ClassSection::updateOrCreate(
            ['institute_class_id' => $classKnowledge->id, 'section_name' => 'Knowledge Batch K-1'],
            ['room_number' => 'Executive Hall 1', 'capacity' => 60]
        );

        $classSkills = InstituteClass::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'custom_name' => 'ACCA Applied Skills'],
            ['academic_term_id' => $term->id]
        );
        $secSkillsS1 = ClassSection::updateOrCreate(
            ['institute_class_id' => $classSkills->id, 'section_name' => 'Skills Batch S-1'],
            ['room_number' => 'Auditorium A', 'capacity' => 60]
        );

        // Subjects
        $subBT = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classKnowledge->id, 'subject_code' => 'ACCA-BT'], ['subject_name' => 'Business and Technology (BT)']);
        $subMA = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classKnowledge->id, 'subject_code' => 'ACCA-MA'], ['subject_name' => 'Management Accounting (MA)']);
        $subFA = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classKnowledge->id, 'subject_code' => 'ACCA-FA'], ['subject_name' => 'Financial Accounting (FA)']);

        foreach ([$subBT, $subMA, $subFA] as $s) {
            ClassSubject::updateOrCreate(['class_id' => $classKnowledge->id, 'subject_id' => $s->id], ['institute_id' => $institute->id, 'subject_type' => 'compulsory', 'credit_hours' => 4]);
        }

        $subLW = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classSkills->id, 'subject_code' => 'ACCA-LW'], ['subject_name' => 'Corporate and Business Law (LW)']);
        $subPM = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classSkills->id, 'subject_code' => 'ACCA-PM'], ['subject_name' => 'Performance Management (PM)']);
        $subFR = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classSkills->id, 'subject_code' => 'ACCA-FR'], ['subject_name' => 'Financial Reporting (FR)']);
        $subTX = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $classSkills->id, 'subject_code' => 'ACCA-TX'], ['subject_name' => 'Taxation (TX)']);

        foreach ([$subLW, $subPM, $subFR, $subTX] as $s) {
            ClassSubject::updateOrCreate(['class_id' => $classSkills->id, 'subject_id' => $s->id], ['institute_id' => $institute->id, 'subject_type' => 'compulsory', 'credit_hours' => 5]);
        }

        // Staff
        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'principal.acca@apex.edu.pk'],
            [
                'name' => 'FCA Haroon Rashid (Dean)',
                'password' => Hash::make($this->demoPassword),
                'role' => User::ROLE_PRINCIPAL,
                'institute_id' => $institute->id,
                'organization_id' => $org->id,
                'identifier' => 'PRIN-ACCA-01',
                'is_primary_principal' => false,
            ]
        );

        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'coord.acca@apex.edu.pk'],
            [
                'name' => 'Mr. Danish Ali (Coordinator)',
                'password' => Hash::make($this->demoPassword),
                'role' => User::ROLE_TEACHER,
                'staff_role' => 'coordinator',
                'institute_id' => $institute->id,
                'organization_id' => $org->id,
                'identifier' => 'COORD-ACCA-01',
            ]
        );

        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'accountant.acca@apex.edu.pk'],
            [
                'name' => 'Mr. Rehan Finance (Accountant)',
                'password' => Hash::make($this->demoPassword),
                'role' => User::ROLE_TEACHER,
                'staff_role' => 'accountant',
                'institute_id' => $institute->id,
                'organization_id' => $org->id,
                'identifier' => 'ACC-ACCA-01',
            ]
        );

        $tHamza = $this->createTeacher($institute, 'Sir Hamza FCCA', 'hamza.acca@apex.edu.pk', 'FAC-ACCA-01', 'Financial Accounting & FR', 150000);
        $tSaad  = $this->createTeacher($institute, 'Sir Saad ACMA', 'saad.acca@apex.edu.pk', 'FAC-ACCA-02', 'Management Accounting & PM', 140000);
        $tMary  = $this->createTeacher($institute, 'Ms. Maryam Advocate', 'maryam.acca@apex.edu.pk', 'FAC-ACCA-03', 'Business Law & Taxation', 130000);

        // Allocations
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tHamza->id, 'subject_id' => $subFA->id, 'class_section_id' => $secKnowledgeK1->id], ['periods_per_week' => 5, 'duration_minutes' => 75]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tHamza->id, 'subject_id' => $subFR->id, 'class_section_id' => $secSkillsS1->id], ['periods_per_week' => 5, 'duration_minutes' => 75]);

        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tSaad->id, 'subject_id' => $subMA->id, 'class_section_id' => $secKnowledgeK1->id], ['periods_per_week' => 5, 'duration_minutes' => 75]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tSaad->id, 'subject_id' => $subPM->id, 'class_section_id' => $secSkillsS1->id], ['periods_per_week' => 5, 'duration_minutes' => 75]);

        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tMary->id, 'subject_id' => $subBT->id, 'class_section_id' => $secKnowledgeK1->id], ['periods_per_week' => 5, 'duration_minutes' => 75]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tMary->id, 'subject_id' => $subLW->id, 'class_section_id' => $secSkillsS1->id], ['periods_per_week' => 5, 'duration_minutes' => 75]);

        // Clash-Free Timetables (Monday through Friday)
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            // Knowledge Batch K-1 (Executive Hall 1)
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secKnowledgeK1->id, 'day_of_week' => $day, 'start_time' => '09:00:00'],
                ['subject_id' => $subFA->id, 'teacher_id' => $tHamza->id, 'room_id' => $hall101->id, 'end_time' => '10:15:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secKnowledgeK1->id, 'day_of_week' => $day, 'start_time' => '10:30:00'],
                ['subject_id' => $subMA->id, 'teacher_id' => $tSaad->id, 'room_id' => $hall101->id, 'end_time' => '11:45:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secKnowledgeK1->id, 'day_of_week' => $day, 'start_time' => '12:00:00'],
                ['subject_id' => $subBT->id, 'teacher_id' => $tMary->id, 'room_id' => $hall101->id, 'end_time' => '13:15:00']
            );

            // Skills Batch S-1 (Auditorium A) — Alternating teachers, perfectly clash-free!
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secSkillsS1->id, 'day_of_week' => $day, 'start_time' => '09:00:00'],
                ['subject_id' => $subPM->id, 'teacher_id' => $tSaad->id, 'room_id' => $auditoriumA->id, 'end_time' => '10:15:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secSkillsS1->id, 'day_of_week' => $day, 'start_time' => '10:30:00'],
                ['subject_id' => $subLW->id, 'teacher_id' => $tMary->id, 'room_id' => $auditoriumA->id, 'end_time' => '11:45:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $secSkillsS1->id, 'day_of_week' => $day, 'start_time' => '12:00:00'],
                ['subject_id' => $subFR->id, 'teacher_id' => $tHamza->id, 'room_id' => $auditoriumA->id, 'end_time' => '13:15:00']
            );
        }

        // Students
        $this->createStudent(
            $institute, $term, $secKnowledgeK1,
            'Omer Farooq', 'omer.acca@student.apex.edu.pk', 'ACCA-2026-001',
            [$subBT, $subMA, $subFA], 80000, 'paid'
        );
        $this->createStudent(
            $institute, $term, $secSkillsS1,
            'Fatima Noor', 'fatima.acca@student.apex.edu.pk', 'ACCA-2026-002',
            [$subLW, $subPM, $subFR, $subTX], 95000, 'paid'
        );
    }

    /**
     * ── 4. Campus 3: Model High School (Matric Campus) ────────────────
     */
    protected function seedMatricCampus(Organization $org): void
    {
        $institute = Institute::withoutGlobalScopes()->updateOrCreate(
            ['slug' => 'apex-matric'],
            [
                'organization_id'        => $org->id,
                'name'                   => 'Apex Model High School (Matric Campus)',
                'subscription_tier'      => 'premium',
                'subscription_starts_at' => now()->startOfYear(),
                'subscription_expires_at'=> now()->addYears(2),
                'is_active'              => true,
                'is_onboarded'           => true,
                'contact_email'          => 'info.matric@apex.edu.pk',
                'contact_phone'          => '+92 41 8765432',
                'city'                   => 'Faisalabad',
                'country'                => 'Pakistan',
                'education_systems'      => ['matric'],
                'tenant_db_name'         => 'uplifyt_inst_apex_matric',
            ]
        );

        $this->seedTogglesAndSettings($institute);

        // Term
        $term = AcademicTerm::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'name' => 'Matric Academic Session 2026-2027'],
            ['start_date' => Carbon::parse('2026-08-15'), 'end_date' => Carbon::parse('2027-06-30'), 'is_active' => true]
        );

        // Rooms
        $roomM1 = Room::updateOrCreate(['institute_id' => $institute->id, 'room_number' => 'Room M-1'], ['building_block' => 'Senior Wing', 'capacity' => 45]);
        $roomM2 = Room::updateOrCreate(['institute_id' => $institute->id, 'room_number' => 'Room M-2'], ['building_block' => 'Senior Wing', 'capacity' => 45]);
        $sciLab = Room::updateOrCreate(['institute_id' => $institute->id, 'room_number' => 'Matric Science Lab'], ['building_block' => 'Lab Wing', 'capacity' => 35]);

        // Classes & Sections
        $class9 = InstituteClass::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'custom_name' => 'Grade 9 (Matric-I)'],
            ['academic_term_id' => $term->id]
        );
        $sec9 = ClassSection::updateOrCreate(
            ['institute_class_id' => $class9->id, 'section_name' => '9th Science Jinnah Section'],
            ['room_number' => 'Room M-1', 'capacity' => 45]
        );

        $class10 = InstituteClass::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'custom_name' => 'Grade 10 (Matric-II)'],
            ['academic_term_id' => $term->id]
        );
        $sec10 = ClassSection::updateOrCreate(
            ['institute_class_id' => $class10->id, 'section_name' => '10th Science Iqbal Section'],
            ['room_number' => 'Room M-2', 'capacity' => 45]
        );

        // Subjects 9th
        $subEng9  = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $class9->id, 'subject_code' => 'ENG-09'], ['subject_name' => 'English 9']);
        $subUrd9  = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $class9->id, 'subject_code' => 'URD-09'], ['subject_name' => 'Urdu 9']);
        $subMath9 = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $class9->id, 'subject_code' => 'MATH-09'], ['subject_name' => 'Mathematics 9']);
        $subPhy9  = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $class9->id, 'subject_code' => 'PHY-09'], ['subject_name' => 'Physics 9']);
        $subBio9  = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $class9->id, 'subject_code' => 'BIO-09'], ['subject_name' => 'Biology 9']);

        foreach ([$subEng9, $subUrd9, $subMath9, $subPhy9, $subBio9] as $s) {
            ClassSubject::updateOrCreate(['class_id' => $class9->id, 'subject_id' => $s->id], ['institute_id' => $institute->id, 'subject_type' => 'compulsory', 'credit_hours' => 4]);
        }

        // Subjects 10th
        $subEng10  = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $class10->id, 'subject_code' => 'ENG-10'], ['subject_name' => 'English 10']);
        $subMath10 = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $class10->id, 'subject_code' => 'MATH-10'], ['subject_name' => 'Mathematics 10']);
        $subChem10 = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $class10->id, 'subject_code' => 'CHEM-10'], ['subject_name' => 'Chemistry 10']);
        $subIsl10  = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $class10->id, 'subject_code' => 'ISL-10'], ['subject_name' => 'Islamiyat Compulsory 10']);
        $subPst10  = Subject::withoutGlobalScopes()->updateOrCreate(['institute_class_id' => $class10->id, 'subject_code' => 'PST-10'], ['subject_name' => 'Pakistan Studies 10']);

        foreach ([$subEng10, $subMath10, $subChem10, $subIsl10, $subPst10] as $s) {
            ClassSubject::updateOrCreate(['class_id' => $class10->id, 'subject_id' => $s->id], ['institute_id' => $institute->id, 'subject_type' => 'compulsory', 'credit_hours' => 4]);
        }

        // Staff
        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'principal.matric@apex.edu.pk'],
            [
                'name' => 'Madam Nasreen Akhtar (Principal)',
                'password' => Hash::make($this->demoPassword),
                'role' => User::ROLE_PRINCIPAL,
                'institute_id' => $institute->id,
                'organization_id' => $org->id,
                'identifier' => 'PRIN-MATR-01',
                'is_primary_principal' => false,
            ]
        );

        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'coord.matric@apex.edu.pk'],
            [
                'name' => 'Sir Qasim Raza (Coordinator)',
                'password' => Hash::make($this->demoPassword),
                'role' => User::ROLE_TEACHER,
                'staff_role' => 'coordinator',
                'institute_id' => $institute->id,
                'organization_id' => $org->id,
                'identifier' => 'COORD-MATR-01',
            ]
        );

        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'accountant.matric@apex.edu.pk'],
            [
                'name' => 'Mr. Zubair Javed (Accountant)',
                'password' => Hash::make($this->demoPassword),
                'role' => User::ROLE_TEACHER,
                'staff_role' => 'accountant',
                'institute_id' => $institute->id,
                'organization_id' => $org->id,
                'identifier' => 'ACC-MATR-01',
            ]
        );

        $tRashid = $this->createTeacher($institute, 'Sir Rashid Minhas', 'rashid.matr@apex.edu.pk', 'FAC-MATR-01', 'Mathematics & Physics', 95000);
        $tSadia  = $this->createTeacher($institute, 'Madam Sadia Parveen', 'sadia.matr@apex.edu.pk', 'FAC-MATR-02', 'Urdu & Islamiyat', 85000);
        $tNoman  = $this->createTeacher($institute, 'Sir Noman Tariq', 'noman.matr@apex.edu.pk', 'FAC-MATR-03', 'Biology & Chemistry', 90000);
        $tUsman  = $this->createTeacher($institute, 'Sir Usman Ghani', 'usman.matr@apex.edu.pk', 'FAC-MATR-04', 'English & Pakistan Studies', 88000);

        // Allocations
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tUsman->id, 'subject_id' => $subEng9->id, 'class_section_id' => $sec9->id], ['periods_per_week' => 5, 'duration_minutes' => 45]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tRashid->id, 'subject_id' => $subMath9->id, 'class_section_id' => $sec9->id], ['periods_per_week' => 5, 'duration_minutes' => 45]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tNoman->id, 'subject_id' => $subBio9->id, 'class_section_id' => $sec9->id], ['periods_per_week' => 5, 'duration_minutes' => 45]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tRashid->id, 'subject_id' => $subPhy9->id, 'class_section_id' => $sec9->id], ['periods_per_week' => 5, 'duration_minutes' => 45]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tSadia->id, 'subject_id' => $subUrd9->id, 'class_section_id' => $sec9->id], ['periods_per_week' => 5, 'duration_minutes' => 45]);

        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tRashid->id, 'subject_id' => $subMath10->id, 'class_section_id' => $sec10->id], ['periods_per_week' => 5, 'duration_minutes' => 45]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tUsman->id, 'subject_id' => $subEng10->id, 'class_section_id' => $sec10->id], ['periods_per_week' => 5, 'duration_minutes' => 45]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tSadia->id, 'subject_id' => $subIsl10->id, 'class_section_id' => $sec10->id], ['periods_per_week' => 5, 'duration_minutes' => 45]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tNoman->id, 'subject_id' => $subChem10->id, 'class_section_id' => $sec10->id], ['periods_per_week' => 5, 'duration_minutes' => 45]);
        TeacherSubjectSection::updateOrCreate(['academic_term_id' => $term->id, 'teacher_id' => $tUsman->id, 'subject_id' => $subPst10->id, 'class_section_id' => $sec10->id], ['periods_per_week' => 5, 'duration_minutes' => 45]);

        // Clash-Free Timetables (Monday through Friday)
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            // 9th Science Jinnah (Room M-1 / Sci Lab)
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $sec9->id, 'day_of_week' => $day, 'start_time' => '08:00:00'],
                ['subject_id' => $subEng9->id, 'teacher_id' => $tUsman->id, 'room_id' => $roomM1->id, 'end_time' => '08:45:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $sec9->id, 'day_of_week' => $day, 'start_time' => '08:50:00'],
                ['subject_id' => $subMath9->id, 'teacher_id' => $tRashid->id, 'room_id' => $roomM1->id, 'end_time' => '09:35:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $sec9->id, 'day_of_week' => $day, 'start_time' => '09:40:00'],
                ['subject_id' => $subBio9->id, 'teacher_id' => $tNoman->id, 'room_id' => $sciLab->id, 'end_time' => '10:25:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $sec9->id, 'day_of_week' => $day, 'start_time' => '10:55:00'],
                ['subject_id' => $subPhy9->id, 'teacher_id' => $tRashid->id, 'room_id' => $roomM1->id, 'end_time' => '11:40:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $sec9->id, 'day_of_week' => $day, 'start_time' => '11:45:00'],
                ['subject_id' => $subUrd9->id, 'teacher_id' => $tSadia->id, 'room_id' => $roomM1->id, 'end_time' => '12:30:00']
            );

            // 10th Science Iqbal (Room M-2 / Sci Lab) — Non-overlapping teachers and rooms!
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $sec10->id, 'day_of_week' => $day, 'start_time' => '08:00:00'],
                ['subject_id' => $subMath10->id, 'teacher_id' => $tRashid->id, 'room_id' => $roomM2->id, 'end_time' => '08:45:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $sec10->id, 'day_of_week' => $day, 'start_time' => '08:50:00'],
                ['subject_id' => $subEng10->id, 'teacher_id' => $tUsman->id, 'room_id' => $roomM2->id, 'end_time' => '09:35:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $sec10->id, 'day_of_week' => $day, 'start_time' => '09:40:00'],
                ['subject_id' => $subIsl10->id, 'teacher_id' => $tSadia->id, 'room_id' => $roomM2->id, 'end_time' => '10:25:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $sec10->id, 'day_of_week' => $day, 'start_time' => '10:55:00'],
                ['subject_id' => $subChem10->id, 'teacher_id' => $tNoman->id, 'room_id' => $sciLab->id, 'end_time' => '11:40:00']
            );
            Timetable::updateOrCreate(
                ['academic_term_id' => $term->id, 'class_section_id' => $sec10->id, 'day_of_week' => $day, 'start_time' => '11:45:00'],
                ['subject_id' => $subPst10->id, 'teacher_id' => $tUsman->id, 'room_id' => $roomM2->id, 'end_time' => '12:30:00']
            );
        }

        // Students
        $this->createStudent(
            $institute, $term, $sec9,
            'Ali Hassan', 'ali.matric@student.apex.edu.pk', 'MATR-2026-001',
            [$subEng9, $subUrd9, $subMath9, $subPhy9, $subBio9], 22000, 'paid'
        );
        $this->createStudent(
            $institute, $term, $sec10,
            'Amina Bibi', 'amina.matric@student.apex.edu.pk', 'MATR-2026-002',
            [$subEng10, $subMath10, $subChem10, $subIsl10, $subPst10], 25000, 'paid'
        );
    }

    /**
     * Helper: Seed default toggles, authorities, and financial settings.
     */
    protected function seedTogglesAndSettings(Institute $institute): void
    {
        InstituteFeatureToggle::updateOrCreate(
            ['institute_id' => $institute->id],
            [
                'principal_portal'     => true,
                'teacher_portal'       => true,
                'parent_portal'        => true,
                'staff_governance'     => true,
                'security_management'  => true,
                'registration_portals' => true,
                'master_directory'     => true,
                'fee_invoicing'        => true,
                'financial_accounts'   => true,
                'scholarships'         => true,
                'classes_sections'     => true,
                'subjects_catalog'     => true,
                'teacher_allocations'  => true,
                'faculty_hours'        => true,
                'rooms_facilities'     => true,
                'attendance_system'    => true,
                'timetable'            => true,
                'ai_bot'               => true,
                'practice_tests'       => true,
                'lms_content'          => true,
                'assessment_engine'    => true,
                'datesheet_manager'    => true,
                'exam_reports'         => true,
                'grading_normalizer'   => true,
                'sms_notifications'    => true,
            ]
        );

        InstituteSetting::updateOrCreate(
            ['institute_id' => $institute->id],
            [
                'filer_tax_rate'                => 0.05,
                'non_filer_tax_rate'            => 0.10,
                'allowed_absent_days_per_month' => 3,
                'salary_disbursement_day'       => 5,
                'salary_deduction_type'         => 'pro_rata',
            ]
        );

        RoleDefaultAuthority::seedDefaultsForInstitute($institute->id);
    }

    /**
     * Helper: Create Teacher User and profile.
     */
    protected function createTeacher(Institute $institute, string $name, string $email, string $identifier, string $qualification, float $salary): User
    {
        $user = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => $email],
            [
                'name'             => $name,
                'password'         => Hash::make($this->demoPassword),
                'role'             => User::ROLE_TEACHER,
                'staff_role'       => 'teacher',
                'institute_id'     => $institute->id,
                'organization_id'  => $institute->organization_id,
                'identifier'       => $identifier,
                'basic_salary_pkr' => $salary,
            ]
        );

        $nameParts = explode(' ', $name);
        Teacher::withoutGlobalScopes()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'institute_id'        => $institute->id,
                'employee_id'         => $identifier,
                'first_name'          => $nameParts[0] . (isset($nameParts[1]) ? ' ' . $nameParts[1] : ''),
                'last_name'           => $nameParts[count($nameParts) - 1] ?? 'Faculty',
                'email'               => $email,
                'qualification'       => $qualification,
                'basic_salary_pkr'    => $salary,
                'matriculation_cert'  => 'documents/matric.pdf',
                'intermediate_cert'   => 'documents/inter.pdf',
                'bachelors_cert'      => 'documents/bachelor.pdf',
            ]
        );

        return $user;
    }

    /**
     * Helper: Create Student User, Student profile, Invoice, and Subject Enrollments.
     */
    protected function createStudent(
        Institute $institute,
        AcademicTerm $term,
        ClassSection $section,
        string $name,
        string $email,
        string $rollNumber,
        array $subjects,
        float $feeAmount,
        string $invoiceStatus = 'paid'
    ): User {
        $user = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => $email],
            [
                'name'            => $name,
                'password'        => Hash::make($this->demoPassword),
                'role'            => User::ROLE_STUDENT,
                'institute_id'    => $institute->id,
                'organization_id' => $institute->organization_id,
                'identifier'      => $rollNumber,
            ]
        );

        $nameParts = explode(' ', $name);
        $student = Student::withoutGlobalScopes()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'institute_id'        => $institute->id,
                'academic_term_id'    => $term->id,
                'class_section_id'    => $section->id,
                'roll_number'         => $rollNumber,
                'first_name'          => $nameParts[0],
                'last_name'           => $nameParts[count($nameParts) - 1] ?? 'Student',
                'email'               => $email,
                'date_of_birth'       => '2008-05-15',
                'previous_marks'      => 85.50,
                'guardian_tax_status' => 'non-filer',
                'guardian_phone'      => '+92 300 1234567',
                'father_guardian_name'=> 'Guardian of ' . $name,
                'admission_status'    => 'approved',
                'base_fee'            => $feeAmount,
                'selected_subject_ids'=> array_map(fn($s) => $s->id, $subjects),
            ]
        );

        // Invoice
        Invoice::withoutGlobalScopes()->updateOrCreate(
            ['student_id' => $student->id, 'title' => 'First Term Tuition & Admission Voucher'],
            [
                'institute_id'     => $institute->id,
                'academic_term_id' => $term->id,
                'class_section_id' => $section->id,
                'amount_pkr'       => $feeAmount,
                'due_date'         => Carbon::now()->addDays(15),
                'status'           => $invoiceStatus,
                'paid_slip_path'   => $invoiceStatus === 'paid' ? 'slips/receipt.pdf' : null,
            ]
        );

        // Subject enrollments
        foreach ($subjects as $sub) {
            StudentSubjectEnrollment::updateOrCreate(
                [
                    'student_id' => $user->id,
                    'subject_id' => $sub->id,
                ],
                [
                    'institute_id'      => $institute->id,
                    'class_section_id'  => $section->id,
                    'enrollment_status' => 'active',
                ]
            );
        }

        // Daily Diary entry
        if (!empty($subjects)) {
            $diaryTeacherId = TeacherSubjectSection::where('subject_id', $subjects[0]->id)
                ->where('class_section_id', $section->id)
                ->value('teacher_id')
                ?: User::where('institute_id', $institute->id)->where('role', User::ROLE_TEACHER)->value('id');

            if ($diaryTeacherId) {
                DailyDiary::withoutGlobalScopes()->updateOrCreate(
                    [
                        'institute_id'     => $institute->id,
                        'class_section_id' => $section->id,
                        'subject_id'       => $subjects[0]->id,
                        'title'            => 'Daily Lecture & Assignment',
                    ],
                    [
                        'teacher_id'   => $diaryTeacherId,
                        'entry_type'   => 'homework',
                        'content'      => 'Read and summarize chapter 1 concepts for tomorrow discussion.',
                        'assigned_date'=> Carbon::today()->toDateString(),
                        'is_active'    => true,
                    ]
                );
            }
        }

        return $user;
    }
}
