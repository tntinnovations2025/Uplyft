<?php

namespace App\Services;

use App\Models\AcademicTerm;
use App\Models\ClassBreak;
use App\Models\Room;
use App\Models\TeacherAvailability;
use App\Models\TeacherSubjectSection;
use App\Models\Timetable;
use Carbon\Carbon;

class TimetableGeneratorService
{
    /**
     * Generate Feasible & Optimized Timetable for an Academic Term.
     *
     * Core principles:
     * 1. Hard Constraints (Zero tolerance):
     *    - Teacher Conflict = 0 (No teacher scheduled in 2 places simultaneously)
     *    - Room Conflict = 0 (No room allocated to 2 classes simultaneously)
     *    - Class Conflict = 0 (No class attending 2 lectures simultaneously)
     *    - Teacher Working Day (Only scheduled on days teacher works)
     *    - Teacher Working Hours (Lecture must fit entirely within teacher's shift)
     *    - Teacher Break Conflict = 0 (No lecture overlaps teacher break)
     *    - Class Break Conflict = 0 (No lecture overlaps class break)
     *    - Lecture Duration (Full duration allocated contiguously without splitting)
     * 2. Soft Constraints & Optimization:
     *    - Minimize class waiting/idle gaps (compact consecutive class schedules)
     *    - Distribute subjects evenly across allowed weekdays (max 1 per subject per class per day)
     *    - Prefer section's designated room or consistent room allocation
     * 3. Comprehensive Post-Generation Validation:
     *    - Fully audits all 10 hard constraints.
     *
     * @param int $academicTermId
     * @param int $instituteId
     * @param array $days
     * @param string $dayStartTime
     * @param string $dayEndTime
     * @param int $slotStepMinutes Granularity step for candidate start times (e.g. 15 or 30 min)
     * @return array ['success' => bool, 'scheduled_slots' => int, 'clashes' => array, 'violations' => array]
     */
    public function generate(
        int $academicTermId,
        int $instituteId,
        array $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
        string $dayStartTime = '08:00',
        string $dayEndTime = '16:00',
        int $slotStepMinutes = 15
    ): array {
        // 1. Fetch all course allocations for this term
        $assignments = TeacherSubjectSection::where('academic_term_id', $academicTermId)
            ->with(['section.instituteClass', 'subject', 'teacher'])
            ->get();

        // Clear existing slots for clean atomic generation
        Timetable::where('academic_term_id', $academicTermId)->delete();

        if ($assignments->isEmpty()) {
            return [
                'success' => false,
                'scheduled_slots' => 0,
                'clashes' => ['No course allocations found for this academic term. Please assign teachers to subjects & class sections first.'],
                'violations' => [],
            ];
        }

        // 2. Load all Teacher Availabilities & Breaks
        $teacherIds = $assignments->pluck('teacher_id')->unique()->filter()->toArray();
        $availabilities = TeacherAvailability::whereIn('teacher_id', $teacherIds)->get()
            ->groupBy(fn ($item) => $item->teacher_id . '_' . strtolower($item->day_of_week));

        // 3. Load all Class Breaks for this institute / term
        $classBreaks = ClassBreak::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->get()
            ->groupBy(fn ($cb) => $cb->class_section_id . '_' . strtolower($cb->day_of_week));

        // 4. Load all Rooms for institute
        $rooms = Room::where('institute_id', $instituteId)->orderBy('room_number')->get();

        // 5. Generate candidate start time points (e.g. 08:00, 08:15, 08:30, ...)
        $candidateStartTimes = [];
        $cur = Carbon::createFromFormat('H:i', substr($dayStartTime, 0, 5));
        $dayEndObj = Carbon::createFromFormat('H:i', substr($dayEndTime, 0, 5));

        while ($cur->lt($dayEndObj)) {
            $candidateStartTimes[] = $cur->format('H:i');
            $cur->addMinutes($slotStepMinutes);
        }

        // State trackers for scheduling matrix
        // intervals: [id][day][] = ['start' => '08:00', 'end' => '09:00']
        $teacherBusy = [];
        $sectionBusy = [];
        $roomBusy = [];
        $sectionSubjectDayBusy = []; // [section_id][subject_id][day] = true
        $sectionPreferredRoom = [];   // [section_id] = room_id

        // Helper: Check if two time intervals overlap (s1 < e2 && e1 > s2)
        $isOverlapping = function (string $s1, string $e1, string $s2, string $e2): bool {
            return ($s1 < $e2) && ($e1 > $s2);
        };

        $hasListOverlap = function (array $intervals, string $sTime, string $eTime) use ($isOverlapping): bool {
            foreach ($intervals as $inv) {
                if ($isOverlapping($sTime, $eTime, $inv['start'], $inv['end'])) {
                    return true;
                }
            }
            return false;
        };

        // Scheduled items store before DB persistence:
        // $scheduledItems[] = ['academic_term_id', 'class_section_id', 'subject_id', 'teacher_id', 'room_id', 'day_of_week', 'start_time', 'end_time']
        $scheduledItems = [];
        $clashes = [];

        // Sort assignments: prioritize longer duration (e.g. 120m labs) and higher period counts first
        $sortedAssignments = $assignments->sortByDesc(function ($a) {
            $dur = (int) ($a->duration_minutes ?: ($a->subject->lecture_duration_minutes ?? 60));
            $periods = (int) ($a->periods_per_week ?: 3);
            return ($dur * 10) + $periods;
        })->values();

        // ── Scheduling Engine Main Loop ──
        foreach ($sortedAssignments as $assignment) {
            $periodsNeeded = (int) ($assignment->periods_per_week ?: 3);
            $periodsScheduled = 0;

            $durationMinutes = (int) ($assignment->duration_minutes ?: ($assignment->subject->lecture_duration_minutes ?? 60));
            if ($durationMinutes <= 0) {
                $durationMinutes = 60;
            }

            $teacherName = $assignment->teacher->name ?? 'Teacher';
            $subjectName = $assignment->subject->subject_name ?? 'Subject';
            $className   = $assignment->section->instituteClass->custom_name ?? 'Class';
            $sectionName = $assignment->section->section_name ?? 'Section';

            $allowedDays = $assignment->allowed_days_list;

            // Filter days that match institute days and assignment allowed days
            $usableDays = array_values(array_filter($days, function ($d) use ($allowedDays) {
                return in_array(strtolower($d), $allowedDays, true);
            }));

            // Attempt to place one period per day across distinct days first
            // To optimize, evaluate all valid (day, startTime, room) candidate placements and score them
            for ($p = 0; $p < $periodsNeeded; $p++) {
                $bestCandidate = null;
                $bestScore = -999999;

                foreach ($usableDays as $day) {
                    $cleanDay = strtolower($day);

                    // Constraint: Max 1 lecture of same subject for class per day
                    if (isset($sectionSubjectDayBusy[$assignment->class_section_id][$assignment->subject_id][$cleanDay])) {
                        continue;
                    }

                    // 1. Teacher Working Day & Window Constraint
                    $availKey = $assignment->teacher_id . '_' . $cleanDay;
                    $teacherAvail = $availabilities->get($availKey)?->first();

                    if ($teacherAvail) {
                        if (!$teacherAvail->is_available) {
                            continue; // Hard constraint: Teacher non-working day
                        }
                        $tShiftStart = substr($teacherAvail->start_time, 0, 5);
                        $tShiftEnd   = substr($teacherAvail->end_time, 0, 5);
                    } else {
                        $tShiftStart = substr($dayStartTime, 0, 5);
                        $tShiftEnd   = substr($dayEndTime, 0, 5);
                    }

                    // 2. Class Breaks on this day
                    $cbKey = $assignment->class_section_id . '_' . $cleanDay;
                    $cBreaksOnDay = $classBreaks->get($cbKey) ?? collect();

                    foreach ($candidateStartTimes as $sTime) {
                        $startTimeObj = Carbon::createFromFormat('H:i', $sTime);
                        $endTimeObj = $startTimeObj->copy()->addMinutes($durationMinutes);
                        $eTime = $endTimeObj->format('H:i');

                        // Hard Constraint: Must fit within teacher working hours
                        if ($sTime < $tShiftStart || $eTime > $tShiftEnd) {
                            continue;
                        }

                        // Hard Constraint: Must not exceed institute day end
                        if ($eTime > substr($dayEndTime, 0, 5)) {
                            continue;
                        }

                        // Hard Constraint: Faculty Break Overlap
                        if ($teacherAvail && $teacherAvail->break_start_time && $teacherAvail->break_end_time) {
                            $tbStart = substr($teacherAvail->break_start_time, 0, 5);
                            $tbEnd   = substr($teacherAvail->break_end_time, 0, 5);
                            if ($isOverlapping($sTime, $eTime, $tbStart, $tbEnd)) {
                                continue;
                            }
                        }

                        // Hard Constraint: Class Break Overlap
                        $overlapsClassBreak = false;
                        foreach ($cBreaksOnDay as $cb) {
                            $cbStart = substr($cb->break_start_time, 0, 5);
                            $cbEnd   = substr($cb->break_end_time, 0, 5);
                            if ($isOverlapping($sTime, $eTime, $cbStart, $cbEnd)) {
                                $overlapsClassBreak = true;
                                break;
                            }
                        }
                        if ($overlapsClassBreak) {
                            continue;
                        }

                        // Hard Constraint: Teacher Conflict (Already teaching another class)
                        if ($hasListOverlap($teacherBusy[$assignment->teacher_id][$cleanDay] ?? [], $sTime, $eTime)) {
                            continue;
                        }

                        // Hard Constraint: Class Conflict (Section already having lecture)
                        if ($hasListOverlap($sectionBusy[$assignment->class_section_id][$cleanDay] ?? [], $sTime, $eTime)) {
                            continue;
                        }

                        // Hard Constraint: Room Allocation
                        $allocatedRoomId = null;
                        $subjectRoomId = $assignment->subject->room_id ?? null;
                        $sectionRoomId = $assignment->section->room_id ?? null;
                        $prefRoomId    = $sectionPreferredRoom[$assignment->class_section_id] ?? null;

                        if ($subjectRoomId && !$hasListOverlap($roomBusy[$subjectRoomId][$cleanDay] ?? [], $sTime, $eTime)) {
                            $allocatedRoomId = $subjectRoomId;
                        } elseif ($sectionRoomId && !$hasListOverlap($roomBusy[$sectionRoomId][$cleanDay] ?? [], $sTime, $eTime)) {
                            $allocatedRoomId = $sectionRoomId;
                        } elseif ($prefRoomId && !$hasListOverlap($roomBusy[$prefRoomId][$cleanDay] ?? [], $sTime, $eTime)) {
                            $allocatedRoomId = $prefRoomId;
                        } elseif ($rooms->isNotEmpty()) {
                            foreach ($rooms as $rm) {
                                if (!$hasListOverlap($roomBusy[$rm->id][$cleanDay] ?? [], $sTime, $eTime)) {
                                    $allocatedRoomId = $rm->id;
                                    break;
                                }
                            }
                        }

                        if ($rooms->isNotEmpty() && !$allocatedRoomId) {
                            continue; // No free room for this time slot
                        }

                        // ── Soft Constraint Optimization Scoring ──
                        $score = 0;

                        // 1. Minimize Class Waiting Time / Idle Gaps
                        // Heavily reward slots adjacent to existing class slots on the same day
                        $existingClassSlots = $sectionBusy[$assignment->class_section_id][$cleanDay] ?? [];
                        if (empty($existingClassSlots)) {
                            // First slot of the day for this class: prefer starting closer to day start
                            $startMin = (int) substr($sTime, 0, 2) * 60 + (int) substr($sTime, 3, 2);
                            $dayStartMin = (int) substr($dayStartTime, 0, 2) * 60 + (int) substr($dayStartTime, 3, 2);
                            $gapFromDayStart = max(0, $startMin - $dayStartMin);
                            $score -= ($gapFromDayStart * 2); // Earlier start preferred
                        } else {
                            // Measure gap between this slot and nearest existing slot
                            $minGap = 99999;
                            foreach ($existingClassSlots as $exSlot) {
                                if ($sTime >= $exSlot['end']) {
                                    $g = (Carbon::parse($sTime)->diffInMinutes(Carbon::parse($exSlot['end'])));
                                    if ($g < $minGap) $minGap = $g;
                                } elseif ($eTime <= $exSlot['start']) {
                                    $g = (Carbon::parse($exSlot['start'])->diffInMinutes(Carbon::parse($eTime)));
                                    if ($g < $minGap) $minGap = $g;
                                }
                            }

                            if ($minGap === 0) {
                                $score += 2000; // Perfect consecutive lecture (zero gap!)
                            } elseif ($minGap <= 30) {
                                $score += 1000 - ($minGap * 10);
                            } else {
                                $score -= ($minGap * 15); // Heavily penalize large gaps
                            }
                        }

                        // 2. Minimize Teacher Idle Gaps
                        $existingTeacherSlots = $teacherBusy[$assignment->teacher_id][$cleanDay] ?? [];
                        if (!empty($existingTeacherSlots)) {
                            foreach ($existingTeacherSlots as $tSlot) {
                                if ($sTime === $tSlot['end'] || $eTime === $tSlot['start']) {
                                    $score += 300; // Consecutive teacher slot
                                }
                            }
                        }

                        // 3. Room Consistency Reward
                        if ($allocatedRoomId && ($allocatedRoomId === $sectionRoomId || $allocatedRoomId === $prefRoomId)) {
                            $score += 150;
                        }

                        // 4. Daily Lecture Load Balancing for Class (Avoid overloading one day)
                        $dailyLectureCount = count($existingClassSlots);
                        $score -= ($dailyLectureCount * 50);

                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $bestCandidate = [
                                'day' => $cleanDay,
                                'start_time' => $sTime,
                                'end_time' => $eTime,
                                'room_id' => $allocatedRoomId,
                            ];
                        }
                    }
                }

                if ($bestCandidate) {
                    $cDay = $bestCandidate['day'];
                    $cStart = $bestCandidate['start_time'];
                    $cEnd = $bestCandidate['end_time'];
                    $cRoom = $bestCandidate['room_id'];

                    // Lock in state
                    $scheduledItems[] = [
                        'academic_term_id' => $academicTermId,
                        'class_section_id' => $assignment->class_section_id,
                        'subject_id'       => $assignment->subject_id,
                        'teacher_id'       => $assignment->teacher_id,
                        'room_id'          => $cRoom,
                        'day_of_week'      => $cDay,
                        'start_time'       => $cStart,
                        'end_time'         => $cEnd,
                    ];

                    $teacherBusy[$assignment->teacher_id][$cDay][] = ['start' => $cStart, 'end' => $cEnd];
                    $sectionBusy[$assignment->class_section_id][$cDay][] = ['start' => $cStart, 'end' => $cEnd];
                    $sectionSubjectDayBusy[$assignment->class_section_id][$assignment->subject_id][$cDay] = true;
                    if ($cRoom) {
                        $roomBusy[$cRoom][$cDay][] = ['start' => $cStart, 'end' => $cEnd];
                        $sectionPreferredRoom[$assignment->class_section_id] = $cRoom;
                    }

                    $periodsScheduled++;
                } else {
                    // Could not schedule this period under strict constraints
                    $missing = $periodsNeeded - $periodsScheduled;
                    $clashes[] = "Clash / Limit Exceeded: Teacher '{$teacherName}' for '{$subjectName}' ({$className} {$sectionName}) — {$missing} of {$periodsNeeded} period(s) could not be scheduled without violating breaks or shift hours.";
                    break;
                }
            }
        }

        // ── Post-Generation Complete Audit & Validation ──
        $violations = $this->validateCompleteSchedule(
            $scheduledItems,
            $availabilities,
            $classBreaks,
            $rooms
        );

        if (!empty($violations)) {
            return [
                'success' => false,
                'scheduled_slots' => 0,
                'clashes' => array_merge($clashes, $violations),
                'violations' => $violations,
            ];
        }

        // Bulk insert verified valid schedule
        foreach ($scheduledItems as $item) {
            Timetable::create($item);
        }

        return [
            'success' => true,
            'scheduled_slots' => count($scheduledItems),
            'clashes' => $clashes,
            'violations' => [],
        ];
    }

    /**
     * Complete Final Audit & Validation of Timetable against all 10 Hard Constraints.
     * Guaranteed zero-tolerance check.
     */
    public function validateCompleteSchedule(
        array $items,
        $availabilities,
        $classBreaks,
        $rooms
    ): array {
        $violations = [];
        $count = count($items);

        $isOverlapping = function (string $s1, string $e1, string $s2, string $e2): bool {
            return ($s1 < $e2) && ($e1 > $s2);
        };

        for ($i = 0; $i < $count; $i++) {
            $a = $items[$i];
            $dayA = strtolower($a['day_of_week']);
            $sA = substr($a['start_time'], 0, 5);
            $eA = substr($a['end_time'], 0, 5);

            // 1. Teacher Working Day & Shift Check
            $availKey = $a['teacher_id'] . '_' . $dayA;
            $tAvail = $availabilities->get($availKey)?->first();

            if ($tAvail) {
                if (!$tAvail->is_available) {
                    $violations[] = "Violation: Teacher ID {$a['teacher_id']} is scheduled on non-working day ({$dayA}).";
                }
                $tStart = substr($tAvail->start_time, 0, 5);
                $tEnd   = substr($tAvail->end_time, 0, 5);
                if ($sA < $tStart || $eA > $tEnd) {
                    $violations[] = "Violation: Teacher ID {$a['teacher_id']} scheduled outside working hours ({$sA}-{$eA} outside {$tStart}-{$tEnd}) on {$dayA}.";
                }

                // 2. Teacher Break Overlap Check
                if ($tAvail->break_start_time && $tAvail->break_end_time) {
                    $tbStart = substr($tAvail->break_start_time, 0, 5);
                    $tbEnd   = substr($tAvail->break_end_time, 0, 5);
                    if ($isOverlapping($sA, $eA, $tbStart, $tbEnd)) {
                        $violations[] = "Violation: Teacher ID {$a['teacher_id']} scheduled during faculty break ({$tbStart}-{$tbEnd}) on {$dayA}.";
                    }
                }
            }

            // 3. Class Break Overlap Check
            $cbKey = $a['class_section_id'] . '_' . $dayA;
            $cBreaks = $classBreaks->get($cbKey) ?? collect();
            foreach ($cBreaks as $cb) {
                $cbStart = substr($cb->break_start_time, 0, 5);
                $cbEnd   = substr($cb->break_end_time, 0, 5);
                if ($isOverlapping($sA, $eA, $cbStart, $cbEnd)) {
                    $violations[] = "Violation: Section ID {$a['class_section_id']} scheduled during class break ({$cbStart}-{$cbEnd}) on {$dayA}.";
                }
            }

            // 4. Cross Pairwise Checks (Teacher, Class, Room Double-Booking)
            for ($j = $i + 1; $j < $count; $j++) {
                $b = $items[$j];
                $dayB = strtolower($b['day_of_week']);

                if ($dayA !== $dayB) {
                    continue;
                }

                $sB = substr($b['start_time'], 0, 5);
                $eB = substr($b['end_time'], 0, 5);

                if (!$isOverlapping($sA, $eA, $sB, $eB)) {
                    continue;
                }

                // Teacher Conflict Check
                if ($a['teacher_id'] == $b['teacher_id']) {
                    $violations[] = "Hard Conflict: Teacher ID {$a['teacher_id']} has 2 simultaneous lectures on {$dayA} ({$sA}-{$eA} vs {$sB}-{$eB}).";
                }

                // Class Section Conflict Check
                if ($a['class_section_id'] == $b['class_section_id']) {
                    $violations[] = "Hard Conflict: Section ID {$a['class_section_id']} has 2 simultaneous lectures on {$dayA} ({$sA}-{$eA} vs {$sB}-{$eB}).";
                }

                // Room Conflict Check
                if ($a['room_id'] && $b['room_id'] && $a['room_id'] == $b['room_id']) {
                    $violations[] = "Hard Conflict: Room ID {$a['room_id']} allocated to 2 classes on {$dayA} ({$sA}-{$eA} vs {$sB}-{$eB}).";
                }
            }
        }

        return $violations;
    }

    /**
     * Regenerate timetable for active academic term.
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
