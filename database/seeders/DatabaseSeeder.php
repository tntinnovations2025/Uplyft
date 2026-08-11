<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with one demo institute + 3 accounts.
     */
    public function run(): void
    {
        // ─── 1. Demo Institute ───────────────────────────────────────────────
        $institute = Institute::create([
            'name'      => 'Uplyft Academy',
            'logo_path' => 'logos/uplyft_academy.png',
            'settings'  => [
                'base_admission_fee'  => 15000.00,
                'filer_tax_rate'      => 0.04,
                'non_filer_tax_rate'  => 0.12,
            ],
        ]);

        // ─── 2. Admin Account ────────────────────────────────────────────────
        $admin = User::create([
            'institute_id' => $institute->id,
            'name'         => 'Admin User',
            'login_id'     => 'ADM-2026-0001',
            'role'         => 'admin',
            'email'        => 'admin@uplyft.edu',
            'password'     => Hash::make('UplyftAdmin123!'),
        ]);

        // ─── 3. Teacher Account + Profile ───────────────────────────────────
        $teacherUser = User::create([
            'institute_id' => $institute->id,
            'name'         => 'Sarah Connor',
            'login_id'     => 'TCH-2026-0001',
            'role'         => 'teacher',
            'email'        => 'sarah.connor@uplyft.edu',
            'password'     => Hash::make('UplyftTeacher123!'),
        ]);

        Teacher::withoutGlobalScopes()->create([
            'institute_id'             => $institute->id,
            'user_id'                  => $teacherUser->id,
            'employee_id'              => 'TCH-2026-0001',
            'first_name'               => 'Sarah',
            'last_name'                => 'Connor',
            'email'                    => 'sarah.connor@uplyft.edu',
            'phone'                    => '0300-1234567',
            'qualification'            => 'M.Sc. Computer Science',
            'qualifications_file_path' => 'institutes/1/teachers/qualifications/demo_transcript.pdf',
        ]);

        // ─── 4. Student Account + Profile ───────────────────────────────────
        $studentUser = User::create([
            'institute_id' => $institute->id,
            'name'         => 'Ali Khan',
            'login_id'     => 'STD-2026-0001',
            'role'         => 'student',
            'email'        => 'ali.khan@uplyft.edu',
            'password'     => Hash::make('UplyftStudent123!'),
        ]);

        Student::withoutGlobalScopes()->create([
            'institute_id'       => $institute->id,
            'user_id'            => $studentUser->id,
            'roll_number'        => 'STD-2026-0001',
            'first_name'         => 'Ali',
            'last_name'          => 'Khan',
            'email'              => 'ali.khan@uplyft.edu',
            'phone'              => '0300-9876543',
            'date_of_birth'      => '2005-03-15',
            'previous_marks'     => 88.5,
            'guardian_tax_status'=> 'filer',
            'blood_group'        => 'O+',
        ]);

        // ─── Print Login Credentials ─────────────────────────────────────────
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════╗');
        $this->command->info('║          UPLYFT - SEED CREDENTIALS               ║');
        $this->command->info('╠══════════════════════════════════════════════════╣');
        $this->command->info('║  ADMIN   Login: ADM-2026-0001                    ║');
        $this->command->info('║          Email: admin@uplyft.edu                 ║');
        $this->command->info('║          Pass:  UplyftAdmin123!                  ║');
        $this->command->info('║  URL:    http://127.0.0.1:8000/dashboard          ║');
        $this->command->info('╠══════════════════════════════════════════════════╣');
        $this->command->info('║  TEACHER Login: TCH-2026-0001                    ║');
        $this->command->info('║          Email: sarah.connor@uplyft.edu          ║');
        $this->command->info('║          Pass:  UplyftTeacher123!                ║');
        $this->command->info('║  URL:    http://127.0.0.1:8000/teacher/dashboard  ║');
        $this->command->info('╠══════════════════════════════════════════════════╣');
        $this->command->info('║  STUDENT Login: STD-2026-0001                    ║');
        $this->command->info('║          Email: ali.khan@uplyft.edu              ║');
        $this->command->info('║          Pass:  UplyftStudent123!                ║');
        $this->command->info('║  URL:    http://127.0.0.1:8000/student/dashboard  ║');
        $this->command->info('╚══════════════════════════════════════════════════╝');
        $this->command->info('');
    }
}
