<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Get class roster with daily attendance status for a given term and date.
     *
     * @param int $academicTermId
     * @param string $date
     * @return Collection
     */
    public function getRosterForTerm(int $academicTermId, string $date): Collection
    {
        // Fetch tenant-isolated students (automatically filtered by InstituteScope)
        $students = Student::all();

        // Fetch attendance logs for the specified term and date
        $attendances = Attendance::where('academic_term_id', $academicTermId)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        // Map students into a roster payload
        return $students->map(function ($student) use ($attendances) {
            $log = $attendances->get($student->id);
            return [
                'student_id' => $student->id,
                'full_name'  => $student->full_name,
                'email'      => $student->email,
                'phone'      => $student->phone,
                'status'     => $log ? $log->status : 'unmarked',
                'logged_at'  => $log ? $log->updated_at->toIso8601String() : null,
            ];
        });
    }

    /**
     * Bulk log or update student attendance records.
     *
     * @param array $data Validated attendance dataset
     * @return int Count of processed entries
     */
    public function markBulkAttendance(array $data): int
    {
        $academicTermId = $data['academic_term_id'];
        $date           = $data['date'];
        $records        = $data['attendances'];

        // Contextual tenant resolution
        $instituteId = Auth::check() ? Auth::user()->institute_id : null;
        if (!$instituteId && app()->bound('current_institute_id')) {
            $instituteId = app('current_institute_id');
        }
        if (!$instituteId) {
            $firstStudent = Student::first();
            $instituteId  = $firstStudent ? $firstStudent->institute_id : 1;
        }

        DB::transaction(function () use ($instituteId, $academicTermId, $date, $records) {
            foreach ($records as $item) {
                Attendance::updateOrCreate(
                    [
                        'institute_id'     => $instituteId,
                        'academic_term_id' => $academicTermId,
                        'student_id'       => $item['student_id'],
                        'date'             => $date,
                    ],
                    [
                        'status'           => $item['status'],
                    ]
                );
            }
        });

        return count($records);
    }
}
