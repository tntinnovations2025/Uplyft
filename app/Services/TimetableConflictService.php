<?php

namespace App\Services;

use App\Models\Timetable;

class TimetableConflictService
{
    /**
     * Validate timetable slot against double-booking conflicts.
     *
     * @param int $academicTermId
     * @param int $classSectionId
     * @param int $teacherId
     * @param string $dayOfWeek
     * @param string $startTime (HH:MM)
     * @param string $endTime (HH:MM)
     * @param int|null $roomId
     * @param int|null $ignoreId (for updates)
     * @return array ['has_conflict' => bool, 'message' => string|null]
     */
    public function validateConflict(
        int $academicTermId,
        int $classSectionId,
        int $teacherId,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $roomId = null,
        ?int $ignoreId = null
    ): array {
        // ── Check 1: Teacher Conflict ───────────────────────────────────────
        $teacherConflict = Timetable::where('academic_term_id', $academicTermId)
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', strtolower($dayOfWeek))
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereRaw('TIME_FORMAT(start_time, ?) < ?', ['%H:%i', $endTime])
                      ->whereRaw('TIME_FORMAT(end_time, ?) > ?', ['%H:%i', $startTime]);
            })
            ->with(['section.instituteClass', 'subject', 'teacher'])
            ->first();

        if ($teacherConflict) {
            $teacherName = $teacherConflict->teacher->name;
            $className   = $teacherConflict->section->instituteClass->custom_name ?? 'Class';
            $sectionName = $teacherConflict->section->section_name;
            $subjectName = $teacherConflict->subject->subject_name;
            $slotTime    = "{$teacherConflict->start_time} - {$teacherConflict->end_time}";

            return [
                'has_conflict' => true,
                'type'         => 'teacher',
                'message'      => "❌ Teacher Conflict: {$teacherName} is already teaching '{$subjectName}' for {$className} ({$sectionName}) on " . ucfirst($dayOfWeek) . " during {$slotTime}.",
            ];
        }

        // ── Check 2: Section Conflict ───────────────────────────────────────
        $sectionConflict = Timetable::where('academic_term_id', $academicTermId)
            ->where('class_section_id', $classSectionId)
            ->where('day_of_week', strtolower($dayOfWeek))
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereRaw('TIME_FORMAT(start_time, ?) < ?', ['%H:%i', $endTime])
                      ->whereRaw('TIME_FORMAT(end_time, ?) > ?', ['%H:%i', $startTime]);
            })
            ->with(['subject', 'teacher'])
            ->first();

        if ($sectionConflict) {
            $subjectName = $sectionConflict->subject->subject_name;
            $teacherName = $sectionConflict->teacher->name;
            $slotTime    = "{$sectionConflict->start_time} - {$sectionConflict->end_time}";

            return [
                'has_conflict' => true,
                'type'         => 'section',
                'message'      => "❌ Section Double-Booking: This section is already scheduled for '{$subjectName}' with {$teacherName} on " . ucfirst($dayOfWeek) . " during {$slotTime}.",
            ];
        }

        // ── Check 3: Room Conflict ──────────────────────────────────────────
        if ($roomId) {
            $roomConflict = Timetable::where('academic_term_id', $academicTermId)
                ->where('room_id', $roomId)
                ->where('day_of_week', strtolower($dayOfWeek))
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereRaw('TIME_FORMAT(start_time, ?) < ?', ['%H:%i', $endTime])
                          ->whereRaw('TIME_FORMAT(end_time, ?) > ?', ['%H:%i', $startTime]);
                })
                ->with(['section.instituteClass', 'subject', 'teacher', 'room'])
                ->first();

            if ($roomConflict) {
                $roomName    = $roomConflict->room->room_number ?? 'Room';
                $className   = $roomConflict->section->instituteClass->custom_name ?? 'Class';
                $sectionName = $roomConflict->section->section_name;
                $subjectName = $roomConflict->subject->subject_name;
                $teacherName = $roomConflict->teacher->name;
                $slotTime    = "{$roomConflict->start_time} - {$roomConflict->end_time}";

                return [
                    'has_conflict' => true,
                    'type'         => 'room',
                    'message'      => "❌ Room Conflict: {$roomName} is already booked for '{$subjectName}' ({$className} {$sectionName}) with {$teacherName} on " . ucfirst($dayOfWeek) . " during {$slotTime}.",
                ];
            }
        }

        return [
            'has_conflict' => false,
            'message'      => null,
        ];
    }
}
