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
     * @param UploadedFile $file
     * @return array{teacher: Teacher, credentials: array}
     */
    public function onboard(array $data, UploadedFile $file): array
    {
        return DB::transaction(function () use ($data, $file) {
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

            // 3. Store qualification file in isolated tenant directory
            $storagePath = "institutes/{$instituteId}/teachers/qualifications";
            $filePath    = $file->store($storagePath, 'public');

            // 4. Persist Teacher profile linked to the User account
            $teacher = Teacher::create([
                'institute_id'             => $instituteId,
                'user_id'                  => $user->id,
                'employee_id'              => $employeeId,
                'first_name'               => $data['first_name'],
                'last_name'                => $data['last_name'],
                'email'                    => $data['email'],
                'phone'                    => $data['phone'] ?? null,
                'qualification'            => $data['qualification'],
                'qualifications_file_path' => $filePath,
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
