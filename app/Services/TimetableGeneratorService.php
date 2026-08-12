<?php

namespace App\Services;

use App\Models\AcademicTerm;
use App\Models\Room;
use App\Models\TeacherAvailability;
use App\Models\TeacherSubjectSection;
use App\Models\Timetable;
use Carbon\Carbon;

class TimetableGeneratorService
{
    /**
     * Generate Optimistic Timetable for an Academic Term.
     *
     * Source of truth is the TeacherSubjectSection assignments
     * (teacher → subject → class section) combined with each teacher's
     * per-day availability windows and the institute's rooms.
     *
     * Constraints enforced:
     *  - A teacher is never scheduled outside their availability window.
     *  - No two classes ever share the same room at the same time.
     *  - A teacher / section never has overlapping slots.
     *
     * @param int $academicTermId
     * @param int $instituteId
     * @param array $days
     * @param string $dayStartTime (e.g. '08:00')
     * @param string $dayEndTime (e.g. '15:00')
     * @param int $slotDurationMinutes (e.g. 60)
     * @return array
     */
    public function generate(
        int $academicTermId,
        int $instituteId,
        array $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
        string $dayStartTime = '08:00',
        string $dayEndTime = '15:00',
        int $slotDurationMinutes = 60
    ): array {
        // Fetch all teacher subject section assignments for this term
        $assignments = TeacherSubjectSection::where('academic_term_id', $academicTermId)
            ->with(['section.instituteClass', 'subject', 'teacher'])
            ->get();

        // Always clear previous optimistic slots so the schedule always
        // reflects the latest teacher assignments & availability windows.
        Timetable::where('academic_term_id', $academicTermId)->delete();

        if ($assignments->isEmpty()) {
            return [
                'success'         => false,
                'scheduled_slots' => 0,
                'clashes'         => ['No teacher subject assignments found for this academic term. Please assign teachers to subjects & sections first.'],
            ];
        }

        // Fetch all teacher availability windows
        $teacherIds = $assignments->pluck('teacher_id')->unique()->toArray();
        $availabilities = TeacherAvailability::whereIn('teacher_id', $teacherIds)->get()
            ->groupBy(fn($item) => $item->teacher_id . '_' . strtolower($item->day_of_week));

        // Fetch all rooms for institute
        $rooms = Room::where('institute_id', $instituteId)->get();

        // Generate time slots (e.g. 08:00-09:00, 09:00-10:00, etc.)
        $timeSlots = [];
        $start = Carbon::createFromFormat('H:i', $dayStartTime);
        $end   = Carbon::createFromFormat('H:i', $dayEndTime);

        while ($start->copy()->addMinutes($slotDurationMinutes)->lte($end)) {
            $slotStart = $start->format('H:i');
            $slotEnd   = $start->copy()->addMinutes($slotDurationMinutes)->format('H:i');
            $timeSlots[] = [
                'start' => $slotStart,
                'end'   => $slotEnd,
            ];
            $start->addMinutes($slotDurationMinutes);
        }

        // In-memory state tracking to prevent overlaps during generation loop
        $teacherBusy = []; // [teacher_id][day][start_time] = true
        $sectionBusy = []; // [class_section_id][day][start_time] = true
        $roomBusy    = []; // [room_id][day][start_time] = true

        $scheduledSlotsCount = 0;
        $clashes = [];

        foreach ($assignments as $assignment) {
            $periodsNeeded = (int) ($assignment->periods_per_week ?: 3);
            $periodsScheduled = 0;
            $teacherName = $assignment->teacher->name ?? 'Teacher';
            $subjectName = $assignment->subject->subject_name ?? 'Subject';
            $className   = $assignment->section->instituteClass->custom_name ?? 'Class';
            $sectionName = $assignment->section->section_name ?? 'Section';

            // Try to distribute periods evenly across days
            foreach ($days as $day) {
                if ($periodsScheduled >= $periodsNeeded) {
                    break;
                }

                // Check teacher availability record for this day
                $availKey = $assignment->teacher_id . '_' . strtolower($day);
                $teacherAvail = $availabilities->get($availKey)?->first();

                // Check each time slot on this day
                foreach ($timeSlots as $slot) {
                    if ($periodsScheduled >= $periodsNeeded) {
                        break;
                    }

                    $sTime = $slot['start'];
                    $eTime = $slot['end'];

                    // 1. Check Teacher Daily Window Availability.
                    // If no explicit window is set, teacher is available by default (08:00 - 15:00).
                    if ($teacherAvail) {
                        if (!$teacherAvail->is_available) {
                            continue; // Teacher explicitly set as non-working on this day
                        }
                        $tWindowStart = Carbon::createFromFormat('H:i:s', strlen($teacherAvail->start_time) === 5 ? $teacherAvail->start_time . ':00' : $teacherAvail->start_time)->format('H:i');
                        $tWindowEnd   = Carbon::createFromFormat('H:i:s', strlen($teacherAvail->end_time) === 5 ? $teacherAvail->end_time . ':00' : $teacherAvail->end_time)->format('H:i');
                    } else {
                        $tWindowStart = '08:00';
                        $tWindowEnd   = '15:00';
                    }

                    if ($sTime < $tWindowStart || $eTime > $tWindowEnd) {
                        continue; // Slot outside teacher's availability window
                    }

                    // 2. Check Teacher Collision
                    if (isset($teacherBusy[$assignment->teacher_id][$day][$sTime])) {
                        continue;
                    }

                    // 3. Check Section Collision
                    if (isset($sectionBusy[$assignment->class_section_id][$day][$sTime])) {
                        continue;
                    }

                    // 4. Room Allocation Check
                    $assignedRoomId = null;
                    if ($rooms->isNotEmpty()) {
                        foreach ($rooms as $rm) {
                            if (!isset($roomBusy[$rm->id][$day][$sTime])) {
                                $assignedRoomId = $rm->id;
                                break;
                            }
                        }
                    }

                    // If no room available, continue to next slot
                    if ($rooms->isNotEmpty() && !$assignedRoomId) {
                        continue;
                    }

                    // We found a valid conflict-free slot! Schedule it!
                    Timetable::create([
                        'academic_term_id' => $academicTermId,
                        'class_section_id' => $assignment->class_section_id,
                        'subject_id'       => $assignment->subject_id,
                        'teacher_id'       => $assignment->teacher_id,
                        'room_id'          => $assignedRoomId,
                        'day_of_week'      => strtolower($day),
                        'start_time'       => $sTime,
                        'end_time'         => $eTime,
                    ]);

                    // Mark in-memory busy
                    $teacherBusy[$assignment->teacher_id][$day][$sTime] = true;
                    $sectionBusy[$assignment->class_section_id][$day][$sTime] = true;
                    if ($assignedRoomId) {
                        $roomBusy[$assignedRoomId][$day][$sTime] = true;
                    }

                    $periodsScheduled++;
                    $scheduledSlotsCount++;
                }
            }

            // Report any unscheduled periods as detailed clashes
            if ($periodsScheduled < $periodsNeeded) {
                $missing = $periodsNeeded - $periodsScheduled;
                $clashes[] = "There is a clash of time for: {$teacherName} due to his time availability/room availability."
                    . " (Subject '{$subjectName}', {$className} {$sectionName} — {$missing} of {$periodsNeeded} period(s) could not be scheduled)";
            }
        }

        return [
            'success'         => true,
            'scheduled_slots' => $scheduledSlotsCount,
            'clashes'         => $clashes,
        ];
    }

    /**
     * Regenerate the timetable for the institute's active academic term.
     * Returns the generator result, or null when there is no active term.
     */
    public function regenerateForActiveTerm(int $instituteId): ?array
    {
        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        if (!$activeTerm) {
            return null;
        }

        return $this->generate($activeTerm->id, $instituteId);
    }
}
