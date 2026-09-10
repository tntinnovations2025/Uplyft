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
     * @param  string  $dayStartTime  (e.g. '08:00')
     * @param  string  $dayEndTime  (e.g. '15:00')
     * @param  int  $slotDurationMinutes  (e.g. 60)
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
                'success' => false,
                'scheduled_slots' => 0,
                'clashes' => ['No teacher subject assignments found for this academic term. Please assign teachers to subjects & sections first.'],
            ];
        }

        // Fetch all teacher availability windows
        $teacherIds = $assignments->pluck('teacher_id')->unique()->toArray();
        $availabilities = TeacherAvailability::whereIn('teacher_id', $teacherIds)->get()
            ->groupBy(fn ($item) => $item->teacher_id.'_'.strtolower($item->day_of_week));

        // Fetch all rooms for institute
        $rooms = Room::where('institute_id', $instituteId)->get();

        // Generate candidate start time slots with 15-minute granularity (08:00, 08:15, 08:30, etc.)
        $timeSlots = [];
        $start = Carbon::createFromFormat('H:i', $dayStartTime);
        $end = Carbon::createFromFormat('H:i', $dayEndTime);

        while ($start->copy()->addMinutes(15)->lte($end)) {
            $slotStart = $start->format('H:i');
            $timeSlots[] = [
                'start' => $slotStart,
            ];
            $start->addMinutes(15);
        }

        // In-memory state tracking to prevent overlaps during generation loop
        $teacherBusyIntervals = []; // [teacher_id][day][] = ['start' => '10:00', 'end' => '12:00']
        $sectionBusyIntervals = []; // [class_section_id][day][] = ['start' => '10:00', 'end' => '12:00']
        $roomBusyIntervals = []; // [room_id][day][] = ['start' => '10:00', 'end' => '12:00']
        $sectionSubjectDayBusy = []; // [class_section_id][subject_id][day] = true
        $sectionPreferredRoom = []; // [class_section_id] = room_id (keeps 1 class in 1 room)

        $scheduledSlotsCount = 0;
        $clashes = [];

        // Helper closure to check interval overlap
        $hasOverlap = function (array $intervals, string $sTime, string $eTime): bool {
            foreach ($intervals as $inv) {
                if ($sTime < $inv['end'] && $eTime > $inv['start']) {
                    return true;
                }
            }
            return false;
        };

        foreach ($assignments as $assignment) {
            $periodsNeeded = (int) ($assignment->periods_per_week ?: 3);
            $periodsScheduled = 0;
            $teacherName = $assignment->teacher->name ?? 'Teacher';
            $subjectName = $assignment->subject->subject_name ?? 'Subject';
            $className = $assignment->section->instituteClass->custom_name ?? 'Class';
            $sectionName = $assignment->section->section_name ?? 'Section';

            // Get allowed days specified by principal for this allocation
            $allowedDays = $assignment->allowed_days_list;

            // Try to distribute periods across allowed days
            foreach ($days as $day) {
                if ($periodsScheduled >= $periodsNeeded) {
                    break;
                }

                $lowerDay = strtolower($day);
                if (!in_array($lowerDay, $allowedDays, true)) {
                    continue; // Skip days unchecked by principal for this lecture allocation
                }

                // Rule: A class can study a subject at most 1 time per day
                if (isset($sectionSubjectDayBusy[$assignment->class_section_id][$assignment->subject_id][$lowerDay])) {
                    continue;
                }

                // Check teacher availability record for this day
                $availKey = $assignment->teacher_id.'_'.$lowerDay;
                $teacherAvail = $availabilities->get($availKey)?->first();

                // Check each time slot on this day
                foreach ($timeSlots as $slot) {
                    if ($periodsScheduled >= $periodsNeeded) {
                        break;
                    }

                    $sTime = $slot['start'];
                    $customDuration = (int) ($assignment->duration_minutes ?: ($assignment->subject->lecture_duration_minutes ?: 60));
                    $eTime = Carbon::createFromFormat('H:i', $sTime)->addMinutes($customDuration)->format('H:i');

                    // 1. Check Teacher Daily Window Availability.
                    if ($teacherAvail) {
                        if (! $teacherAvail->is_available) {
                            continue; // Teacher explicitly set as non-working on this day
                        }
                        $tWindowStart = Carbon::createFromFormat('H:i:s', strlen($teacherAvail->start_time) === 5 ? $teacherAvail->start_time.':00' : $teacherAvail->start_time)->format('H:i');
                        $tWindowEnd = Carbon::createFromFormat('H:i:s', strlen($teacherAvail->end_time) === 5 ? $teacherAvail->end_time.':00' : $teacherAvail->end_time)->format('H:i');
                    } else {
                        $tWindowStart = '08:00';
                        $tWindowEnd = '15:00';
                    }

                    if ($sTime < $tWindowStart || $eTime > $tWindowEnd) {
                        continue; // Slot outside teacher's availability window
                    }

                    // 2. Check Teacher Interval Overlap (Teacher cannot teach elsewhere during an active lecture e.g. 10:00 - 12:00)
                    $existingTeacherSlots = $teacherBusyIntervals[$assignment->teacher_id][$lowerDay] ?? [];
                    if ($hasOverlap($existingTeacherSlots, $sTime, $eTime)) {
                        continue;
                    }

                    // 3. Check Section Interval Overlap
                    $existingSectionSlots = $sectionBusyIntervals[$assignment->class_section_id][$lowerDay] ?? [];
                    if ($hasOverlap($existingSectionSlots, $sTime, $eTime)) {
                        continue;
                    }

                    // 4. Room Allocation Check:
                    $assignedRoomId = null;
                    $subjectRoomId = $assignment->subject->room_id ?? null;
                    $sectionRoomId = $assignment->section->room_id ?? null;
                    $prefRoomId = $sectionPreferredRoom[$assignment->class_section_id] ?? null;

                    if ($subjectRoomId && ! $hasOverlap($roomBusyIntervals[$subjectRoomId][$lowerDay] ?? [], $sTime, $eTime)) {
                        $assignedRoomId = $subjectRoomId;
                    } elseif ($sectionRoomId && ! $hasOverlap($roomBusyIntervals[$sectionRoomId][$lowerDay] ?? [], $sTime, $eTime)) {
                        $assignedRoomId = $sectionRoomId;
                    } elseif ($prefRoomId && ! $hasOverlap($roomBusyIntervals[$prefRoomId][$lowerDay] ?? [], $sTime, $eTime)) {
                        $assignedRoomId = $prefRoomId;
                    } elseif ($rooms->isNotEmpty()) {
                        foreach ($rooms as $rm) {
                            if (! $hasOverlap($roomBusyIntervals[$rm->id][$lowerDay] ?? [], $sTime, $eTime)) {
                                $assignedRoomId = $rm->id;
                                $sectionPreferredRoom[$assignment->class_section_id] = $rm->id;
                                break;
                            }
                        }
                    }

                    // If no room available, continue to next slot
                    if ($rooms->isNotEmpty() && ! $assignedRoomId) {
                        continue;
                    }

                    // We found a valid conflict-free slot! Schedule it!
                    Timetable::create([
                        'academic_term_id' => $academicTermId,
                        'class_section_id' => $assignment->class_section_id,
                        'subject_id' => $assignment->subject_id,
                        'teacher_id' => $assignment->teacher_id,
                        'room_id' => $assignedRoomId,
                        'day_of_week' => strtolower($day),
                        'start_time' => $sTime,
                        'end_time' => $eTime,
                    ]);

                    // Mark in-memory interval busy
                    $teacherBusyIntervals[$assignment->teacher_id][$lowerDay][] = ['start' => $sTime, 'end' => $eTime];
                    $sectionBusyIntervals[$assignment->class_section_id][$lowerDay][] = ['start' => $sTime, 'end' => $eTime];
                    $sectionSubjectDayBusy[$assignment->class_section_id][$assignment->subject_id][$lowerDay] = true;
                    if ($assignedRoomId) {
                        $roomBusyIntervals[$assignedRoomId][$lowerDay][] = ['start' => $sTime, 'end' => $eTime];
                    }

                    $periodsScheduled++;
                    $scheduledSlotsCount++;

                    // Only 1 lecture per subject per class per day -> move to next day
                    break;
                }
            }

            // Report any unscheduled periods as detailed clashes
            if ($periodsScheduled < $periodsNeeded) {
                $missing = $periodsNeeded - $periodsScheduled;
                $clashes[] = "There is a clash of time/daily limit for: {$teacherName}."
                    ." (Subject '{$subjectName}', {$className} {$sectionName} — {$missing} of {$periodsNeeded} period(s) could not be scheduled due to daily limit or time/room availability limits)";
            }
        }

        return [
            'success' => true,
            'scheduled_slots' => $scheduledSlotsCount,
            'clashes' => $clashes,
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

        if (! $activeTerm) {
            return null;
        }

        return $this->generate($activeTerm->id, $instituteId);
    }
}
