<?php

namespace App\Services;

use App\Models\AcademicTerm;
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

        // Contextual tenant resolution (no blind fallback)
        $instituteId = Auth::check() ? Auth::user()->getActiveInstituteId() : null;
        if (!$instituteId && app()->bound('current_institute_id')) {
            $instituteId = app('current_institute_id');
        }
        if (!$instituteId) {
            return 0;
        }

        $instituteId = (int) $instituteId;

        // The academic term must belong to the resolved institute
        $termExists = AcademicTerm::where('institute_id', $instituteId)
            ->whereKey($academicTermId)
            ->exists();

        if (!$termExists) {
            return 0;
        }

        // Whitelist: only students registered at the resolved institute may be marked
        $rosterStudentIds = Student::withoutGlobalScopes()
            ->where('institute_id', $instituteId)
            ->pluck('id')
            ->flip();

        $processed = 0;

        DB::transaction(function () use ($instituteId, $academicTermId, $date, $records, $rosterStudentIds, &$processed) {
            foreach ($records as $item) {
                if (!isset($rosterStudentIds[$item['student_id']])) {
                    continue;
                }

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

                $processed++;
            }
        });

        return $processed;
    }
}
