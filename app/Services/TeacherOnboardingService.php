<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherOnboardingService
{
    /**
     * Handle teacher onboarding, isolated file storage, and User account creation.
     *
     * @param array        $data
     * @param array        $files
     * @return array{teacher: Teacher, credentials: array}
     */
    public function onboard(array $data, array $files): array
    {
        return DB::transaction(function () use ($data, $files) {
            // Determine active institute ID context
            $instituteId = $data['institute_id']
                ?? (Auth::check() ? Auth::user()->institute_id : null)
                ?? (app()->bound('current_institute_id') ? app('current_institute_id') : 1);

            // 1. Generate unique Employee ID: TCH-YYYY-XXXX
            $year       = now()->year;
            $sequence   = str_pad(Teacher::withoutGlobalScopes()->count() + 1, 4, '0', STR_PAD_LEFT);
            $employeeId = "TCH-{$year}-{$sequence}";

            $defaultPassword = 'UplyftTeacher123!';

            // 2. Create the global User login account for the teacher
            $user = User::create([
                'institute_id' => $instituteId,
                'name'         => trim($data['first_name'] . ' ' . $data['last_name']),
                'login_id'     => $employeeId,
                'role'         => 'teacher',
                'email'        => $data['email'],
                'password'     => Hash::make($defaultPassword),
            ]);

            // 3. Store qualification files in isolated tenant directory
            $storagePath = "institutes/{$instituteId}/teachers/qualifications";
            
            $matriculationPath = isset($files['matriculation_cert']) ? $files['matriculation_cert']->store($storagePath, 'public') : null;
            $intermediatePath = isset($files['intermediate_cert']) ? $files['intermediate_cert']->store($storagePath, 'public') : null;
            $bachelorsPath = isset($files['bachelors_cert']) ? $files['bachelors_cert']->store($storagePath, 'public') : null;
            $mastersPath = isset($files['masters_cert']) ? $files['masters_cert']->store($storagePath, 'public') : null;
            $phdPath = isset($files['phd_cert']) ? $files['phd_cert']->store($storagePath, 'public') : null;

            // 4. Persist Teacher profile linked to the User account
            $teacher = Teacher::create([
                'institute_id'       => $instituteId,
                'user_id'            => $user->id,
                'employee_id'        => $employeeId,
                'first_name'         => $data['first_name'],
                'last_name'          => $data['last_name'],
                'email'              => $data['email'],
                'phone'              => $data['phone'] ?? null,
                'qualification'      => $data['qualification'],
                'matriculation_cert' => $matriculationPath,
                'intermediate_cert'  => $intermediatePath,
                'bachelors_cert'     => $bachelorsPath,
                'masters_cert'       => $mastersPath,
                'phd_cert'           => $phdPath,
                'years_of_experience' => $data['years_of_experience'] ?? null,
                'specialization_subjects' => $data['specialization_subjects'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'basic_salary_pkr' => $data['basic_salary_pkr'] ?? null,
            ]);

            return [
                'teacher'     => $teacher,
                'credentials' => [
                    'employee_id' => $employeeId,
                    'login_id'    => $employeeId,
                    'password'    => $defaultPassword,
                    'portal'      => url('/teacher/dashboard'),
                ],
            ];
        });
    }
}
