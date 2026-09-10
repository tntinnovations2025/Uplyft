<?php

namespace Database\Seeders;

use App\Models\SystemClass;
use Illuminate\Database\Seeder;

class SystemClassSeeder extends Seeder
{
    public function run(): void
    {
        $systemClasses = [
            // ── MATRIC SYSTEM ────────────────────────────────────────────────────────
            [
                'name' => 'Play Group',
                'short_code' => 'PG',
                'education_type' => 'matric',
                'sort_order' => 1,
                'default_subjects' => [
                    ['name' => 'English Basics & Phonics', 'code' => 'ENG-PG', 'periods' => 5],
                    ['name' => 'Rhymes & Creative Arts', 'code' => 'ART-PG', 'periods' => 4],
                    ['name' => 'Urdu Alphabets', 'code' => 'URD-PG', 'periods' => 5],
                    ['name' => 'General Activity & Play', 'code' => 'ACT-PG', 'periods' => 4],
                ],
            ],
            [
                'name' => 'Nursery',
                'short_code' => 'NUR',
                'education_type' => 'matric',
                'sort_order' => 2,
                'default_subjects' => [
                    ['name' => 'English', 'code' => 'ENG-NUR', 'periods' => 5],
                    ['name' => 'Urdu', 'code' => 'URD-NUR', 'periods' => 5],
                    ['name' => 'Basic Mathematics', 'code' => 'MATH-NUR', 'periods' => 5],
                    ['name' => 'General Science & Activity', 'code' => 'SCI-NUR', 'periods' => 4],
                ],
            ],
            [
                'name' => 'Kindergarten (KG / Prep)',
                'short_code' => 'KG',
                'education_type' => 'matric',
                'sort_order' => 3,
                'default_subjects' => [
                    ['name' => 'English Grammar & Reading', 'code' => 'ENG-KG', 'periods' => 5],
                    ['name' => 'Urdu Grammar', 'code' => 'URD-KG', 'periods' => 5],
                    ['name' => 'Elementary Mathematics', 'code' => 'MATH-KG', 'periods' => 5],
                    ['name' => 'General Knowledge', 'code' => 'GK-KG', 'periods' => 4],
                    ['name' => 'Islamiat Basics', 'code' => 'ISL-KG', 'periods' => 3],
                ],
            ],
            [
                'name' => 'Grade 1',
                'short_code' => 'G01',
                'education_type' => 'matric',
                'sort_order' => 4,
                'default_subjects' => [
                    ['name' => 'English', 'code' => 'ENG-01', 'periods' => 5],
                    ['name' => 'Urdu', 'code' => 'URD-01', 'periods' => 5],
                    ['name' => 'Mathematics', 'code' => 'MATH-01', 'periods' => 5],
                    ['name' => 'General Science', 'code' => 'SCI-01', 'periods' => 4],
                    ['name' => 'Islamiat', 'code' => 'ISL-01', 'periods' => 3],
                ],
            ],
            [
                'name' => 'Grade 2',
                'short_code' => 'G02',
                'education_type' => 'matric',
                'sort_order' => 5,
                'default_subjects' => [
                    ['name' => 'English', 'code' => 'ENG-02', 'periods' => 5],
                    ['name' => 'Urdu', 'code' => 'URD-02', 'periods' => 5],
                    ['name' => 'Mathematics', 'code' => 'MATH-02', 'periods' => 5],
                    ['name' => 'General Science', 'code' => 'SCI-02', 'periods' => 4],
                    ['name' => 'Islamiat', 'code' => 'ISL-02', 'periods' => 3],
                ],
            ],
            [
                'name' => 'Grade 3',
                'short_code' => 'G03',
                'education_type' => 'matric',
                'sort_order' => 6,
                'default_subjects' => [
                    ['name' => 'English', 'code' => 'ENG-03', 'periods' => 5],
                    ['name' => 'Urdu', 'code' => 'URD-03', 'periods' => 5],
                    ['name' => 'Mathematics', 'code' => 'MATH-03', 'periods' => 5],
                    ['name' => 'General Science', 'code' => 'SCI-03', 'periods' => 4],
                    ['name' => 'Social Studies', 'code' => 'SST-03', 'periods' => 4],
                    ['name' => 'Islamiat', 'code' => 'ISL-03', 'periods' => 3],
                ],
            ],
            [
                'name' => 'Grade 4',
                'short_code' => 'G04',
                'education_type' => 'matric',
                'sort_order' => 7,
                'default_subjects' => [
                    ['name' => 'English', 'code' => 'ENG-04', 'periods' => 5],
                    ['name' => 'Urdu', 'code' => 'URD-04', 'periods' => 5],
                    ['name' => 'Mathematics', 'code' => 'MATH-04', 'periods' => 5],
                    ['name' => 'General Science', 'code' => 'SCI-04', 'periods' => 4],
                    ['name' => 'Social Studies', 'code' => 'SST-04', 'periods' => 4],
                    ['name' => 'Islamiat', 'code' => 'ISL-04', 'periods' => 3],
                ],
            ],
            [
                'name' => 'Grade 5',
                'short_code' => 'G05',
                'education_type' => 'matric',
                'sort_order' => 8,
                'default_subjects' => [
                    ['name' => 'English', 'code' => 'ENG-05', 'periods' => 5],
                    ['name' => 'Urdu', 'code' => 'URD-05', 'periods' => 5],
                    ['name' => 'Mathematics', 'code' => 'MATH-05', 'periods' => 5],
                    ['name' => 'General Science', 'code' => 'SCI-05', 'periods' => 4],
                    ['name' => 'Social Studies', 'code' => 'SST-05', 'periods' => 4],
                    ['name' => 'Computer Studies', 'code' => 'CS-05', 'periods' => 3],
                    ['name' => 'Islamiat', 'code' => 'ISL-05', 'periods' => 3],
                ],
            ],
            [
                'name' => 'Grade 6',
                'short_code' => 'G06',
                'education_type' => 'matric',
                'sort_order' => 9,
                'default_subjects' => [
                    ['name' => 'English', 'code' => 'ENG-06', 'periods' => 5],
                    ['name' => 'Urdu', 'code' => 'URD-06', 'periods' => 5],
                    ['name' => 'Mathematics', 'code' => 'MATH-06', 'periods' => 5],
                    ['name' => 'General Science', 'code' => 'SCI-06', 'periods' => 4],
                    ['name' => 'History & Geography', 'code' => 'HG-06', 'periods' => 4],
                    ['name' => 'Computer Science', 'code' => 'CS-06', 'periods' => 3],
                    ['name' => 'Islamiat', 'code' => 'ISL-06', 'periods' => 3],
                ],
            ],
            [
                'name' => 'Grade 7',
                'short_code' => 'G07',
                'education_type' => 'matric',
                'sort_order' => 10,
                'default_subjects' => [
                    ['name' => 'English', 'code' => 'ENG-07', 'periods' => 5],
                    ['name' => 'Urdu', 'code' => 'URD-07', 'periods' => 5],
                    ['name' => 'Mathematics', 'code' => 'MATH-07', 'periods' => 5],
                    ['name' => 'General Science', 'code' => 'SCI-07', 'periods' => 4],
                    ['name' => 'History & Geography', 'code' => 'HG-07', 'periods' => 4],
                    ['name' => 'Computer Science', 'code' => 'CS-07', 'periods' => 3],
                    ['name' => 'Islamiat', 'code' => 'ISL-07', 'periods' => 3],
                ],
            ],
            [
                'name' => 'Grade 8',
                'short_code' => 'G08',
                'education_type' => 'matric',
                'sort_order' => 11,
                'default_subjects' => [
                    ['name' => 'English', 'code' => 'ENG-08', 'periods' => 5],
                    ['name' => 'Urdu', 'code' => 'URD-08', 'periods' => 5],
                    ['name' => 'Mathematics', 'code' => 'MATH-08', 'periods' => 5],
                    ['name' => 'General Science', 'code' => 'SCI-08', 'periods' => 4],
                    ['name' => 'History & Geography', 'code' => 'HG-08', 'periods' => 4],
                    ['name' => 'Computer Science', 'code' => 'CS-08', 'periods' => 3],
                    ['name' => 'Islamiat', 'code' => 'ISL-08', 'periods' => 3],
                ],
            ],
            [
                'name' => 'Grade 9 (Matric Part 1)',
                'short_code' => 'G09',
                'education_type' => 'matric',
                'sort_order' => 12,
                'default_subjects' => [
                    ['name' => 'Mathematics', 'code' => 'MATH-09', 'periods' => 6],
                    ['name' => 'Physics', 'code' => 'PHY-09', 'periods' => 5],
                    ['name' => 'Chemistry', 'code' => 'CHEM-09', 'periods' => 5],
                    ['name' => 'Computer Science', 'code' => 'CS-09', 'periods' => 5],
                    ['name' => 'Biology', 'code' => 'BIO-09', 'periods' => 5],
                    ['name' => 'English Language & Comp', 'code' => 'ENG-09', 'periods' => 5],
                    ['name' => 'Urdu Compulsory', 'code' => 'URD-09', 'periods' => 4],
                    ['name' => 'Islamiat Compulsory', 'code' => 'ISL-09', 'periods' => 3],
                    ['name' => 'Tarjuma-tul-Quran', 'code' => 'TQ-09', 'periods' => 2],
                ],
            ],
            [
                'name' => 'Grade 10 (Matric Part 2)',
                'short_code' => 'G10',
                'education_type' => 'matric',
                'sort_order' => 13,
                'default_subjects' => [
                    ['name' => 'Mathematics', 'code' => 'MATH-10', 'periods' => 6],
                    ['name' => 'Physics', 'code' => 'PHY-10', 'periods' => 5],
                    ['name' => 'Chemistry', 'code' => 'CHEM-10', 'periods' => 5],
                    ['name' => 'Computer Science', 'code' => 'CS-10', 'periods' => 5],
                    ['name' => 'Biology', 'code' => 'BIO-10', 'periods' => 5],
                    ['name' => 'English Language & Comp', 'code' => 'ENG-10', 'periods' => 5],
                    ['name' => 'Urdu Compulsory', 'code' => 'URD-10', 'periods' => 4],
                    ['name' => 'Pakistan Studies', 'code' => 'PST-10', 'periods' => 3],
                    ['name' => 'Tarjuma-tul-Quran', 'code' => 'TQ-10', 'periods' => 2],
                ],
            ],

            // ── INTERMEDIATE / HIGHER SECONDARY SYSTEM ──────────────────────────────
            [
                'name' => '1st Year (F.Sc Pre-Medical)',
                'short_code' => 'FSC1-MED',
                'education_type' => 'higher_sec',
                'sort_order' => 20,
                'default_subjects' => [
                    ['name' => 'Biology Part 1', 'code' => 'BIO-11', 'periods' => 6],
                    ['name' => 'Physics Part 1', 'code' => 'PHY-11', 'periods' => 6],
                    ['name' => 'Chemistry Part 1', 'code' => 'CHEM-11', 'periods' => 6],
                    ['name' => 'English Part 1', 'code' => 'ENG-11', 'periods' => 5],
                    ['name' => 'Urdu Part 1', 'code' => 'URD-11', 'periods' => 4],
                    ['name' => 'Islamic Education', 'code' => 'ISL-11', 'periods' => 3],
                ],
            ],
            [
                'name' => '1st Year (F.Sc Pre-Engineering)',
                'short_code' => 'FSC1-ENG',
                'education_type' => 'higher_sec',
                'sort_order' => 21,
                'default_subjects' => [
                    ['name' => 'Mathematics Part 1', 'code' => 'MATH-11', 'periods' => 6],
                    ['name' => 'Physics Part 1', 'code' => 'PHY-11', 'periods' => 6],
                    ['name' => 'Chemistry Part 1', 'code' => 'CHEM-11', 'periods' => 6],
                    ['name' => 'English Part 1', 'code' => 'ENG-11', 'periods' => 5],
                    ['name' => 'Urdu Part 1', 'code' => 'URD-11', 'periods' => 4],
                    ['name' => 'Islamic Education', 'code' => 'ISL-11', 'periods' => 3],
                ],
            ],
            [
                'name' => '1st Year (ICS - Computer Science)',
                'short_code' => 'ICS1',
                'education_type' => 'higher_sec',
                'sort_order' => 22,
                'default_subjects' => [
                    ['name' => 'Computer Science Part 1', 'code' => 'CS-11', 'periods' => 6],
                    ['name' => 'Mathematics Part 1', 'code' => 'MATH-11', 'periods' => 6],
                    ['name' => 'Physics Part 1', 'code' => 'PHY-11', 'periods' => 6],
                    ['name' => 'English Part 1', 'code' => 'ENG-11', 'periods' => 5],
                    ['name' => 'Urdu Part 1', 'code' => 'URD-11', 'periods' => 4],
                    ['name' => 'Islamic Education', 'code' => 'ISL-11', 'periods' => 3],
                ],
            ],
            [
                'name' => '1st Year (I.Com - Commerce)',
                'short_code' => 'ICOM1',
                'education_type' => 'higher_sec',
                'sort_order' => 23,
                'default_subjects' => [
                    ['name' => 'Principles of Accounting Part 1', 'code' => 'ACT-11', 'periods' => 6],
                    ['name' => 'Principles of Commerce', 'code' => 'COM-11', 'periods' => 5],
                    ['name' => 'Principles of Economics', 'code' => 'ECO-11', 'periods' => 5],
                    ['name' => 'Business Mathematics', 'code' => 'BM-11', 'periods' => 5],
                    ['name' => 'English Part 1', 'code' => 'ENG-11', 'periods' => 5],
                    ['name' => 'Urdu Part 1', 'code' => 'URD-11', 'periods' => 4],
                ],
            ],
            [
                'name' => '2nd Year (F.Sc Pre-Medical)',
                'short_code' => 'FSC2-MED',
                'education_type' => 'higher_sec',
                'sort_order' => 25,
                'default_subjects' => [
                    ['name' => 'Biology Part 2', 'code' => 'BIO-12', 'periods' => 6],
                    ['name' => 'Physics Part 2', 'code' => 'PHY-12', 'periods' => 6],
                    ['name' => 'Chemistry Part 2', 'code' => 'CHEM-12', 'periods' => 6],
                    ['name' => 'English Part 2', 'code' => 'ENG-12', 'periods' => 5],
                    ['name' => 'Urdu Part 2', 'code' => 'URD-12', 'periods' => 4],
                    ['name' => 'Pakistan Studies', 'code' => 'PST-12', 'periods' => 3],
                ],
            ],
            [
                'name' => '2nd Year (F.Sc Pre-Engineering)',
                'short_code' => 'FSC2-ENG',
                'education_type' => 'higher_sec',
                'sort_order' => 26,
                'default_subjects' => [
                    ['name' => 'Mathematics Part 2', 'code' => 'MATH-12', 'periods' => 6],
                    ['name' => 'Physics Part 2', 'code' => 'PHY-12', 'periods' => 6],
                    ['name' => 'Chemistry Part 2', 'code' => 'CHEM-12', 'periods' => 6],
                    ['name' => 'English Part 2', 'code' => 'ENG-12', 'periods' => 5],
                    ['name' => 'Urdu Part 2', 'code' => 'URD-12', 'periods' => 4],
                    ['name' => 'Pakistan Studies', 'code' => 'PST-12', 'periods' => 3],
                ],
            ],
            [
                'name' => '2nd Year (ICS - Computer Science)',
                'short_code' => 'ICS2',
                'education_type' => 'higher_sec',
                'sort_order' => 27,
                'default_subjects' => [
                    ['name' => 'Computer Science Part 2', 'code' => 'CS-12', 'periods' => 6],
                    ['name' => 'Mathematics Part 2', 'code' => 'MATH-12', 'periods' => 6],
                    ['name' => 'Physics Part 2', 'code' => 'PHY-12', 'periods' => 6],
                    ['name' => 'English Part 2', 'code' => 'ENG-12', 'periods' => 5],
                    ['name' => 'Urdu Part 2', 'code' => 'URD-12', 'periods' => 4],
                    ['name' => 'Pakistan Studies', 'code' => 'PST-12', 'periods' => 3],
                ],
            ],
            [
                'name' => '2nd Year (I.Com - Commerce)',
                'short_code' => 'ICOM2',
                'education_type' => 'higher_sec',
                'sort_order' => 28,
                'default_subjects' => [
                    ['name' => 'Principles of Accounting Part 2', 'code' => 'ACT-12', 'periods' => 6],
                    ['name' => 'Commercial Geography', 'code' => 'CG-12', 'periods' => 5],
                    ['name' => 'Business Statistics', 'code' => 'BS-12', 'periods' => 5],
                    ['name' => 'English Part 2', 'code' => 'ENG-12', 'periods' => 5],
                    ['name' => 'Urdu Part 2', 'code' => 'URD-12', 'periods' => 4],
                    ['name' => 'Pakistan Studies', 'code' => 'PST-12', 'periods' => 3],
                ],
            ],

            // ── CAMBRIDGE O-LEVEL / A-LEVEL SYSTEM ──────────────────────────────────
            [
                'name' => 'O-Level Grade 9 (O1)',
                'short_code' => 'O1',
                'education_type' => 'o_a_level',
                'sort_order' => 30,
                'default_subjects' => [
                    ['name' => 'Cambridge Mathematics Syllabus D', 'code' => 'CAM-4024', 'periods' => 6],
                    ['name' => 'Cambridge Physics', 'code' => 'CAM-5054', 'periods' => 6],
                    ['name' => 'Cambridge Chemistry', 'code' => 'CAM-5070', 'periods' => 6],
                    ['name' => 'Cambridge English Language', 'code' => 'CAM-1123', 'periods' => 5],
                    ['name' => 'Pakistan Studies History & Culture', 'code' => 'CAM-2059-1', 'periods' => 4],
                    ['name' => 'Islamiyat', 'code' => 'CAM-2058', 'periods' => 4],
                ],
            ],
            [
                'name' => 'O-Level Grade 10 (O2)',
                'short_code' => 'O2',
                'education_type' => 'o_a_level',
                'sort_order' => 31,
                'default_subjects' => [
                    ['name' => 'Cambridge Mathematics Syllabus D', 'code' => 'CAM-4024', 'periods' => 6],
                    ['name' => 'Cambridge Physics', 'code' => 'CAM-5054', 'periods' => 6],
                    ['name' => 'Cambridge Chemistry', 'code' => 'CAM-5070', 'periods' => 6],
                    ['name' => 'Cambridge Biology / Computer Science', 'code' => 'CAM-2210', 'periods' => 6],
                    ['name' => 'Cambridge English Language', 'code' => 'CAM-1123', 'periods' => 5],
                    ['name' => 'Pakistan Studies Environment of Pakistan', 'code' => 'CAM-2059-2', 'periods' => 4],
                ],
            ],
            [
                'name' => 'O-Level Grade 11 (O3)',
                'short_code' => 'O3',
                'education_type' => 'o_a_level',
                'sort_order' => 32,
                'default_subjects' => [
                    ['name' => 'Cambridge Additional Mathematics', 'code' => 'CAM-4037', 'periods' => 6],
                    ['name' => 'Cambridge Physics', 'code' => 'CAM-5054', 'periods' => 6],
                    ['name' => 'Cambridge Chemistry', 'code' => 'CAM-5070', 'periods' => 6],
                    ['name' => 'Cambridge Computer Science', 'code' => 'CAM-2210', 'periods' => 6],
                    ['name' => 'Cambridge English Literature', 'code' => 'CAM-2010', 'periods' => 5],
                ],
            ],
            [
                'name' => 'A-Level AS Level (A1)',
                'short_code' => 'A1',
                'education_type' => 'o_a_level',
                'sort_order' => 33,
                'default_subjects' => [
                    ['name' => 'Cambridge International AS Mathematics', 'code' => 'CAM-9709', 'periods' => 7],
                    ['name' => 'Cambridge International AS Physics', 'code' => 'CAM-9702', 'periods' => 7],
                    ['name' => 'Cambridge International AS Chemistry', 'code' => 'CAM-9701', 'periods' => 7],
                    ['name' => 'Cambridge International AS Computer Science', 'code' => 'CAM-9618', 'periods' => 7],
                ],
            ],
            [
                'name' => 'A-Level A2 Level (A2)',
                'short_code' => 'A2',
                'education_type' => 'o_a_level',
                'sort_order' => 34,
                'default_subjects' => [
                    ['name' => 'Cambridge International A2 Mathematics', 'code' => 'CAM-9709-A2', 'periods' => 7],
                    ['name' => 'Cambridge International A2 Physics', 'code' => 'CAM-9702-A2', 'periods' => 7],
                    ['name' => 'Cambridge International A2 Chemistry', 'code' => 'CAM-9701-A2', 'periods' => 7],
                    ['name' => 'Cambridge International A2 Further Mathematics', 'code' => 'CAM-9231', 'periods' => 7],
                ],
            ],

            // ── ACCA / PROFESSIONAL SYSTEM ──────────────────────────────────────────
            [
                'name' => 'ACCA - Knowledge Level (BT, MA, FA)',
                'short_code' => 'ACCA-KNOW',
                'education_type' => 'professional',
                'sort_order' => 40,
                'default_subjects' => [
                    ['name' => 'Business and Technology (FBT / BT)', 'code' => 'ACCA-FBT', 'periods' => 6],
                    ['name' => 'Management Accounting (FMA / MA)', 'code' => 'ACCA-FMA', 'periods' => 6],
                    ['name' => 'Financial Accounting (FFA / FA)', 'code' => 'ACCA-FFA', 'periods' => 6],
                ],
            ],
            [
                'name' => 'ACCA - Applied Skills Level (LW, PM, TX, FR, AA, FM)',
                'short_code' => 'ACCA-SKILL',
                'education_type' => 'professional',
                'sort_order' => 41,
                'default_subjects' => [
                    ['name' => 'Corporate and Business Law (LW / F4)', 'code' => 'ACCA-LW', 'periods' => 5],
                    ['name' => 'Performance Management (PM / F5)', 'code' => 'ACCA-PM', 'periods' => 6],
                    ['name' => 'Taxation (TX / F6)', 'code' => 'ACCA-TX', 'periods' => 6],
                    ['name' => 'Financial Reporting (FR / F7)', 'code' => 'ACCA-FR', 'periods' => 6],
                    ['name' => 'Audit and Assurance (AA / F8)', 'code' => 'ACCA-AA', 'periods' => 6],
                    ['name' => 'Financial Management (FM / F9)', 'code' => 'ACCA-FM', 'periods' => 6],
                ],
            ],
            [
                'name' => 'ACCA - Strategic Professional Level (SBL, SBR, AFM, APM, AAA)',
                'short_code' => 'ACCA-PROF',
                'education_type' => 'professional',
                'sort_order' => 42,
                'default_subjects' => [
                    ['name' => 'Strategic Business Leader (SBL)', 'code' => 'ACCA-SBL', 'periods' => 7],
                    ['name' => 'Strategic Business Reporting (SBR)', 'code' => 'ACCA-SBR', 'periods' => 7],
                    ['name' => 'Advanced Financial Management (AFM)', 'code' => 'ACCA-AFM', 'periods' => 6],
                    ['name' => 'Advanced Performance Management (APM)', 'code' => 'ACCA-APM', 'periods' => 6],
                    ['name' => 'Advanced Audit and Assurance (AAA)', 'code' => 'ACCA-AAA', 'periods' => 6],
                ],
            ],
        ];

        foreach ($systemClasses as $data) {
            SystemClass::updateOrCreate(
                ['short_code' => $data['short_code']],
                $data + ['is_active' => true]
            );
        }
    }
}
