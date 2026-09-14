<?php

namespace App\Services;

use App\Models\ClassBreak;
use App\Models\TeacherAvailability;
use App\Models\Timetable;
use Carbon\Carbon;

class TimetableConflictService
{
    /**
     * Validate timetable slot against double-booking conflicts, working hours, breaks, and subject daily limit.
     *
     * @param int $academicTermId
     * @param int $classSectionId
     * @param int $teacherId
     * @param string $dayOfWeek
     * @param string $startTime (HH:MM or HH:MM:SS)
     * @param string $endTime (HH:MM or HH:MM:SS)
     * @param int|null $roomId
     * @param int|null $ignoreId (for updates)
     * @param int|null $subjectId
     * @return array ['has_conflict' => bool, 'type' => string|null, 'message' => string|null]
     */
    public function validateConflict(
        int $academicTermId,
        int $classSectionId,
        int $teacherId,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $roomId = null,
        ?int $ignoreId = null,
        ?int $subjectId = null
    ): array {
        $cleanDay = strtolower($dayOfWeek);
        $sTime = substr($startTime, 0, 5);
        $eTime = substr($endTime, 0, 5);

        // Helper for time interval overlap: (s1 < e2 && e1 > s2)
        $hasTimeOverlap = function (string $startA, string $endA, string $startB, string $endB): bool {
            return ($startA < $endB) && ($endA > $startB);
        };

        // ── Check 1: Teacher Working Day & Hours ─────────────────────────────
        $teacherAvail = TeacherAvailability::where('teacher_id', $teacherId)
            ->where('day_of_week', $cleanDay)
            ->first();

        if ($teacherAvail) {
            if (!$teacherAvail->is_available) {
                return [
                    'has_conflict' => true,
                    'type'         => 'teacher_working_day',
                    'message'      => "❌ Teacher Non-Working Day: Teacher is configured as not available on " . ucfirst($dayOfWeek) . ".",
                ];
            }

            $tWindowStart = substr($teacherAvail->start_time, 0, 5);
            $tWindowEnd   = substr($teacherAvail->end_time, 0, 5);

            if ($sTime < $tWindowStart || $eTime > $tWindowEnd) {
                return [
                    'has_conflict' => true,
                    'type'         => 'teacher_working_hours',
                    'message'      => "❌ Teacher Working Hours Violation: Slot ({$sTime} - {$eTime}) falls outside the teacher's working shift ({$tWindowStart} - {$tWindowEnd}) on " . ucfirst($dayOfWeek) . ".",
                ];
            }

            // ── Check 2: Teacher Break Overlap ──────────────────────────────
            if ($teacherAvail->break_start_time && $teacherAvail->break_end_time) {
                $tbStart = substr($teacherAvail->break_start_time, 0, 5);
                $tbEnd   = substr($teacherAvail->break_end_time, 0, 5);

                if ($hasTimeOverlap($sTime, $eTime, $tbStart, $tbEnd)) {
                    return [
                        'has_conflict' => true,
                        'type'         => 'teacher_break',
                        'message'      => "❌ Faculty Break Conflict: Teacher has a scheduled break from {$tbStart} to {$tbEnd} on " . ucfirst($dayOfWeek) . ". No lecture may overlap this break.",
                    ];
                }
            }
        }

        // ── Check 3: Class Break Overlap ────────────────────────────────────
        $classBreak = ClassBreak::where('class_section_id', $classSectionId)
            ->where('day_of_week', $cleanDay)
            ->where('is_active', true)
            ->first();

        if ($classBreak) {
            $cbStart = substr($classBreak->break_start_time, 0, 5);
            $cbEnd   = substr($classBreak->break_end_time, 0, 5);

            if ($hasTimeOverlap($sTime, $eTime, $cbStart, $cbEnd)) {
                return [
                    'has_conflict' => true,
                    'type'         => 'class_break',
                    'message'      => "❌ Class Break Conflict: This class has a configured break from {$cbStart} to {$cbEnd} on " . ucfirst($dayOfWeek) . ". Lectures cannot overlap this break.",
                ];
            }
        }

        // ── Check 4: Subject Daily Limit Conflict (Max 1 lecture of a subject per class per day) ──
        if ($subjectId) {
            $subjectDailyConflict = Timetable::where('academic_term_id', $academicTermId)
                ->where('class_section_id', $classSectionId)
                ->where('subject_id', $subjectId)
                ->where('day_of_week', $cleanDay)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->with(['subject', 'section.instituteClass'])
                ->first();

            if ($subjectDailyConflict) {
                $subjectName = $subjectDailyConflict->subject->subject_name ?? 'Subject';
                $className   = $subjectDailyConflict->section->instituteClass->custom_name ?? 'Class';
                $sectionName = $subjectDailyConflict->section->section_name ?? 'Section';
                $slotTime    = substr($subjectDailyConflict->start_time, 0, 5) . " - " . substr($subjectDailyConflict->end_time, 0, 5);

                return [
                    'has_conflict' => true,
                    'type'         => 'subject_daily_limit',
                    'message'      => "❌ Daily Subject Limit Exceeded: {$className} ({$sectionName}) already has a lecture for '{$subjectName}' on " . ucfirst($dayOfWeek) . " during {$slotTime}. A class can study a subject at most 1 time per day.",
                ];
            }
        }

        // ── Check 5: Teacher Conflict ───────────────────────────────────────
        $teacherConflict = Timetable::where('academic_term_id', $academicTermId)
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $cleanDay)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($sTime, $eTime) {
                $query->whereRaw('TIME_FORMAT(start_time, ?) < ?', ['%H:%i', $eTime])
                      ->whereRaw('TIME_FORMAT(end_time, ?) > ?', ['%H:%i', $sTime]);
            })
            ->with(['section.instituteClass', 'subject', 'teacher'])
            ->first();

        if ($teacherConflict) {
            $teacherName = $teacherConflict->teacher->name;
            $className   = $teacherConflict->section->instituteClass->custom_name ?? 'Class';
            $sectionName = $teacherConflict->section->section_name;
            $subjectName = $teacherConflict->subject->subject_name;
            $slotTime    = substr($teacherConflict->start_time, 0, 5) . " - " . substr($teacherConflict->end_time, 0, 5);

            return [
                'has_conflict' => true,
                'type'         => 'teacher',
                'message'      => "❌ Teacher Conflict: {$teacherName} is already teaching '{$subjectName}' for {$className} ({$sectionName}) on " . ucfirst($dayOfWeek) . " during {$slotTime}.",
            ];
        }

        // ── Check 6: Section Conflict ───────────────────────────────────────
        $sectionConflict = Timetable::where('academic_term_id', $academicTermId)
            ->where('class_section_id', $classSectionId)
            ->where('day_of_week', $cleanDay)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($sTime, $eTime) {
                $query->whereRaw('TIME_FORMAT(start_time, ?) < ?', ['%H:%i', $eTime])
                      ->whereRaw('TIME_FORMAT(end_time, ?) > ?', ['%H:%i', $sTime]);
            })
            ->with(['subject', 'teacher'])
            ->first();

        if ($sectionConflict) {
            $subjectName = $sectionConflict->subject->subject_name;
            $teacherName = $sectionConflict->teacher->name;
            $slotTime    = substr($sectionConflict->start_time, 0, 5) . " - " . substr($sectionConflict->end_time, 0, 5);

            return [
                'has_conflict' => true,
                'type'         => 'section',
                'message'      => "❌ Section Double-Booking: This section is already scheduled for '{$subjectName}' with {$teacherName} on " . ucfirst($dayOfWeek) . " during {$slotTime}.",
            ];
        }

        // ── Check 7: Room Conflict ──────────────────────────────────────────
        if ($roomId) {
            $roomConflict = Timetable::where('academic_term_id', $academicTermId)
                ->where('room_id', $roomId)
                ->where('day_of_week', $cleanDay)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where(function ($query) use ($sTime, $eTime) {
                    $query->whereRaw('TIME_FORMAT(start_time, ?) < ?', ['%H:%i', $eTime])
                          ->whereRaw('TIME_FORMAT(end_time, ?) > ?', ['%H:%i', $sTime]);
                })
                ->with(['section.instituteClass', 'subject', 'teacher', 'room'])
                ->first();

            if ($roomConflict) {
                $roomName    = $roomConflict->room->room_number ?? 'Room';
                $className   = $roomConflict->section->instituteClass->custom_name ?? 'Class';
                $sectionName = $roomConflict->section->section_name;
                $subjectName = $roomConflict->subject->subject_name;
                $teacherName = $roomConflict->teacher->name;
                $slotTime    = substr($roomConflict->start_time, 0, 5) . " - " . substr($roomConflict->end_time, 0, 5);

                return [
                    'has_conflict' => true,
                    'type'         => 'room',
                    'message'      => "❌ Room Conflict: {$roomName} is already booked for '{$subjectName}' ({$className} {$sectionName}) with {$teacherName} on " . ucfirst($dayOfWeek) . " during {$slotTime}.",
                ];
            }
        }

        return [
            'has_conflict' => false,
            'type'         => null,
            'message'      => null,
        ];
    }
}
