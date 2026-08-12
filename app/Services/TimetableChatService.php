<?php

namespace App\Services;

use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\Room;
use App\Models\Subject;
use App\Models\TeacherAvailability;
use App\Models\TeacherSubjectSection;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Conversational Schedule Assistant.
 *
 * Parses natural-language requests typed into the Timetable conversation box
 * and adjusts the schedule accordingly. It is a rule-based ("built-in smart
 * parser") engine — no external AI API required. It understands common
 * phrasings for moving, adding, removing, swapping and optimizing slots.
 */
class TimetableChatService
{
    public function __construct(
        protected TimetableConflictService $conflictService
    ) {}

    /**
     * Entry point. Accepts a free-text message and executes the matching action.
     *
     * @return array{success: bool, changed: bool, message: string}
     */
    public function handle(string $message, int $instituteId, ?AcademicTerm $activeTerm): array
    {
        $msg = trim($message);

        if ($msg === '') {
            return $this->reply(false, false, 'Please tell me what you would like to do — for example: "Optimize Class 10A timetable" or "Move Maths to Monday 9:00-10:00".');
        }

        if (!$activeTerm) {
            return $this->reply(false, false, 'No active academic term found. Activate a term first under Academic Terms, then I can adjust the timetable.');
        }

        if ($this->isHelp($msg)) {
            return $this->helpMessage();
        }

        $sections = ClassSection::whereHas('instituteClass', fn($q) => $q->where('institute_id', $instituteId))
            ->with('instituteClass')
            ->get();

        if ($sections->isEmpty()) {
            return $this->reply(false, false, 'No class sections have been created yet. Add classes and sections first, then I can adjust the timetable.');
        }

        $subjects = Subject::whereHas('instituteClass', fn($q) => $q->where('institute_id', $instituteId))
            ->with('instituteClass')
            ->orderByRaw('LENGTH(subject_name) DESC')
            ->get();

        $teachers = User::where('institute_id', $instituteId)
            ->where('role', User::ROLE_TEACHER)
            ->orderBy('name')
            ->get();

        $rooms = Room::where('institute_id', $instituteId)->get();

        // "Show me the schedule" is a read-only intent
        if ($this->hasAnyWord($msg, ['show', 'see', 'display', 'list', 'current'])) {
            return $this->showSchedule($msg, $activeTerm, $sections, $subjects, $teachers);
        }

        $intent = $this->detectIntent($msg);

        switch ($intent) {
            case 'move':
                return $this->doMove($msg, $activeTerm, $sections, $subjects, $teachers);
            case 'swap':
                return $this->doSwap($msg, $activeTerm, $sections, $subjects);
            case 'add':
                return $this->doAdd($msg, $activeTerm, $sections, $subjects, $teachers, $rooms);
            case 'remove':
                return $this->doRemove($msg, $activeTerm, $sections, $subjects, $teachers);
            case 'optimize':
                return $this->doOptimize($msg, $activeTerm, $sections);
            default:
                return $this->helpMessage();
        }
    }

    /* ─────────────────────────── Intent Detection ─────────────────────────── */

    protected function detectIntent(string $msg): string
    {
        if ($this->hasAnyWord($msg, ['swap', 'exchange', 'switch', 'interchange'])) {
            return 'swap';
        }
        if ($this->hasAnyWord($msg, ['remove', 'delete', 'cancel', 'clear', 'drop'])) {
            return 'remove';
        }
        if ($this->hasAnyWord($msg, ['add', 'schedule', 'insert', 'book', 'put in', 'create'])) {
            return 'add';
        }
        if ($this->hasAnyWord($msg, ['optimize', 'optimise', 'compact', 'close the gap', 'close gaps', 'fill gap', 'fill the gap', 'back to back', 'back-to-back', 'gap', 'not optimized', 'not optimised', 'rebalance', 'minimize gaps', 'reduce gap'])) {
            return 'optimize';
        }
        if ($this->hasAnyWord($msg, ['move', 'shift', 'reschedule', 'change', 'resched', 'adjust'])) {
            return 'move';
        }

        return 'unknown';
    }

    /* ─────────────────────────── Move (change time/day) ───────────────────── */

    protected function doMove(string $msg, AcademicTerm $term, Collection $sections, Collection $subjects, Collection $teachers): array
    {
        $section = $this->resolveSection($msg, $sections);
        if (!$section) {
            return $this->reply(false, false, 'Which class would you like to move a lecture for? Please include the class, e.g. "Move Maths for Class 10A to Monday 9:00-10:00".');
        }

        $ranges = $this->findAllTimeRanges($msg);
        if (empty($ranges)) {
            return $this->reply(false, false, 'Which time should I move it to? For example: "Move Maths for Class 10A to Tuesday 11:00-12:00".');
        }

        $subject = $this->resolveSubject($msg, $subjects, $section);
        $teacher = $this->resolveTeacher($msg, $teachers);

        $target = $ranges[count($ranges) - 1];
        $fromRange = count($ranges) >= 2 ? $ranges[0] : null;

        $days = $this->findAllDays($msg);
        $fromDay = count($days) >= 2 ? $days[0] : null;
        $targetDay = count($days) >= 2 ? $days[1] : (count($days) === 1 ? $days[0] : null);

        $query = Timetable::where('academic_term_id', $term->id)
            ->where('class_section_id', $section->id);

        if ($subject) {
            $query->where('subject_id', $subject->id);
        }
        if ($teacher) {
            $query->where('teacher_id', $teacher->id);
        }
        if ($fromDay) {
            $query->where('day_of_week', $fromDay);
        }
        if ($fromRange) {
            $this->applyTimeEqual($query, 'start_time', $fromRange['start']);
            $this->applyTimeEqual($query, 'end_time', $fromRange['end']);
        }

        $slots = $query->with(['subject', 'teacher', 'section.instituteClass', 'room'])
            ->get()
            ->sortBy(fn($s) => $this->h($s->start_time))
            ->values();

        if ($slots->isEmpty()) {
            $hint = $subject ? " for '{$subject->subject_name}'" : '';
            return $this->reply(false, false, $this->noSlotsMessage($term, $section, $sections, $subjects, $teachers, $msg, $hint));
        }

        $slot = $slots->first();
        $targetDay = $targetDay ?: $slot->day_of_week;

        // Teacher availability must cover the new slot
        $availError = $this->availabilityCovers($slot->teacher_id, $targetDay, $target['start'], $target['end']);
        if ($availError) {
            return $this->reply(false, false, $availError);
        }

        $validation = $this->conflictService->validateConflict(
            $term->id,
            $section->id,
            $slot->teacher_id,
            $targetDay,
            $target['start'],
            $target['end'],
            $slot->room_id,
            $slot->id
        );

        if ($validation['has_conflict']) {
            return $this->reply(false, false, $validation['message']);
        }

        $slot->update([
            'day_of_week' => $targetDay,
            'start_time'  => $target['start'],
            'end_time'    => $target['end'],
        ]);

        $subjectName = $slot->subject->subject_name;
        $className   = $this->sectionLabel($section);

        return $this->reply(true, true, "✅ Done! Moved <strong>{$subjectName}</strong> for {$className} to " . ucfirst($targetDay) . " {$target['start']} – {$target['end']} with no conflicts.");
    }

    /* ─────────────────────────── Swap two lectures ────────────────────────── */

    protected function doSwap(string $msg, AcademicTerm $term, Collection $sections, Collection $subjects): array
    {
        $section = $this->resolveSection($msg, $sections);
        if (!$section) {
            return $this->reply(false, false, 'Which class would you like to swap lectures for? Please include the class, e.g. "Swap Maths and English for Class 10A".');
        }

        $subject = $this->resolveSubject($msg, $subjects, $section);
        $matchedSubjects = $this->findAllSubjects($msg, $subjects, $section);

        if (count($matchedSubjects) >= 2) {
            $subjA = $matchedSubjects[0];
            $subjB = $matchedSubjects[1];

            $slotsA = $this->sectionSubjectSlots($term, $section, $subjA);
            $slotsB = $this->sectionSubjectSlots($term, $section, $subjB);

            if ($slotsA->isEmpty() || $slotsB->isEmpty()) {
                return $this->reply(false, false, "I found both '{$subjA->subject_name}' and '{$subjB->subject_name}' for {$this->sectionLabel($section)}, but one of them has no scheduled lectures yet.");
            }

            $a = $slotsA->first();
            $b = $slotsB->first();

            if ($a->id === $b->id) {
                return $this->reply(false, false, 'Those refer to the same lecture. Please name two different subjects or time slots.');
            }

            return $this->swapSlots($term, $section, $a, $b);
        }

        if ($subject) {
            $slots = Timetable::where('academic_term_id', $term->id)
                ->where('class_section_id', $section->id)
                ->where('subject_id', $subject->id)
                ->with(['subject', 'teacher', 'section.instituteClass', 'room'])
                ->get()
                ->sortBy(fn($s) => $this->dayOrder($s->day_of_week) . ' ' . $this->h($s->start_time))
                ->values();

            if ($slots->count() < 2) {
                return $this->reply(false, false, "Could not find two lectures to swap for {$this->sectionLabel($section)}. Please name two subjects, e.g. \"Swap Maths and English for Class 10A\".");
            }

            $a = $slots[0];
            $b = $slots[1];

            return $this->swapSlots($term, $section, $a, $b);
        }

        // Time-based swap: two time ranges or two single times in the message
        $ranges = $this->findAllTimeRanges($msg);
        $points = $this->findAllTimes($msg);
        $days   = $this->findAllDays($msg);

        if (count($ranges) >= 2 || count($points) >= 2) {
            $day = $days[0] ?? null;

            if (count($ranges) >= 2) {
                $pointA = $ranges[0]['start'];
                $pointB = $ranges[1]['start'];
            } else {
                $pointA = $points[0];
                $pointB = $points[1];
            }

            $aSlot = $this->slotAtTime($term, $section, $pointA, $day);
            $bSlot = $this->slotAtTime($term, $section, $pointB, $day);

            if (!$aSlot || !$bSlot) {
                return $this->reply(false, false, "I could not find two lectures at {$pointA} and {$pointB} for {$this->sectionLabel($section)} on " . ($day ? ucfirst($day) : 'that day') . ".");
            }

            return $this->swapSlots($term, $section, $aSlot, $bSlot);
        }

        return $this->reply(false, false, 'I can swap two lectures by subject (e.g. "Swap Maths and English for Class 10A") or by time (e.g. "Swap the 10:00 and 14:00 lectures for Class 10A").');
    }

    protected function swapSlots(AcademicTerm $term, ClassSection $section, Timetable $a, Timetable $b): array
    {
        $aTimes = $this->slotTimes($a);
        $bTimes = $this->slotTimes($b);

        // Validate both new positions, ignoring BOTH swapped slots
        $err1 = $this->slotFreeAt($term->id, $a, $bTimes['day'], $bTimes['start'], $bTimes['end'], [$a->id, $b->id]);
        if ($err1) {
            return $this->reply(false, false, $err1);
        }

        $err2 = $this->slotFreeAt($term->id, $b, $aTimes['day'], $aTimes['start'], $aTimes['end'], [$a->id, $b->id]);
        if ($err2) {
            return $this->reply(false, false, $err2);
        }

        DB::transaction(function () use ($a, $b, $aTimes, $bTimes) {
            $a->update($bTimes);
            $b->update($aTimes);
        });

        $subjectA = $a->subject->subject_name;
        $subjectB = $b->subject->subject_name;

        if ($subjectA === $subjectB) {
            return $this->reply(
                true,
                true,
                "🔁 Swapped two <strong>{$subjectA}</strong> lectures for {$this->sectionLabel($section)}. "
                . "They are now on " . ucfirst($bTimes['day']) . " {$bTimes['start']}–{$bTimes['end']} and " . ucfirst($aTimes['day']) . " {$aTimes['start']}–{$aTimes['end']}."
            );
        }

        return $this->reply(
            true,
            true,
            "🔁 Swapped <strong>{$subjectA}</strong> and <strong>{$subjectB}</strong> for {$this->sectionLabel($section)}. "
            . "Now {$subjectA} is on " . ucfirst($bTimes['day']) . " {$bTimes['start']}–{$bTimes['end']} and {$subjectB} is on " . ucfirst($aTimes['day']) . " {$aTimes['start']}–{$aTimes['end']}."
        );
    }

    /**
     * Validate that $slot can be placed at [start, end] on $day, ignoring the
     * given slot ids (used when swapping two slots so they can exchange times).
     */
    protected function slotFreeAt(int $termId, Timetable $slot, string $day, string $start, string $end, array $ignoreIds): ?string
    {
        $availError = $this->availabilityCovers($slot->teacher_id, $day, $start, $end);
        if ($availError) {
            return $availError;
        }

        $clash = Timetable::where('academic_term_id', $termId)
            ->whereNotIn('id', $ignoreIds)
            ->where('day_of_week', strtolower($day))
            ->where(function ($q) use ($start, $end) {
                $this->applyTimeOverlap($q, $start, $end);
            })
            ->where(function ($q) use ($slot) {
                $q->where('teacher_id', $slot->teacher_id)
                    ->orWhere('class_section_id', $slot->class_section_id)
                    ->orWhere('room_id', $slot->room_id);
            })
            ->with(['section.instituteClass', 'subject', 'teacher'])
            ->first();

        if ($clash) {
            $className = $clash->section?->instituteClass?->custom_name ?? 'another class';
            $sectionName = $clash->section?->section_name ?? '';
            return "❌ Conflict: {$slot->teacher->name} (or room/class) is already occupied on " . ucfirst($day) . " {$start}–{$end} by '{$clash->subject->subject_name}' ({$className} {$sectionName}).";
        }

        return null;
    }

    /* ─────────────────────────── Add a lecture ────────────────────────────── */

    protected function doAdd(string $msg, AcademicTerm $term, Collection $sections, Collection $subjects, Collection $teachers, Collection $rooms): array
    {
        $section = $this->resolveSection($msg, $sections);
        if (!$section) {
            return $this->reply(false, false, 'Which class would you like to add a lecture for? Please include the class, e.g. "Add English for Class 10A on Tuesday 9:00-10:00".');
        }

        $subject = $this->resolveSubject($msg, $subjects, $section);
        if (!$subject) {
            return $this->reply(false, false, 'Which subject would you like to add? For example: "Add English for Class 10A on Tuesday 9:00-10:00".');
        }

        $day = $this->findDay($msg);
        if (!$day) {
            return $this->reply(false, false, 'Which day should the new lecture be on? For example: "Add English for Class 10A on Tuesday 9:00-10:00".');
        }

        $ranges = $this->findAllTimeRanges($msg);
        if (empty($ranges)) {
            return $this->reply(false, false, 'Which time should the new lecture start and end? For example: "Add English for Class 10A on Tuesday 9:00-10:00".');
        }

        $start = $ranges[0]['start'];
        $end   = $ranges[0]['end'];

        $teacher = $this->resolveTeacher($msg, $teachers);

        // Infer teacher from the assignment if not mentioned
        if (!$teacher) {
            $assignment = TeacherSubjectSection::where('academic_term_id', $term->id)
                ->where('class_section_id', $section->id)
                ->where('subject_id', $subject->id)
                ->with('teacher')
                ->first();

            if ($assignment && $assignment->teacher) {
                $teacher = $assignment->teacher;
            }
        }

        if (!$teacher) {
            return $this->reply(false, false, "No teacher found for '{$subject->subject_name}' in {$this->sectionLabel($section)}. Please mention the teacher or assign one under Faculty & Staff Roster.");
        }

        $availError = $this->availabilityCovers($teacher->id, $day, $start, $end);
        if ($availError) {
            return $this->reply(false, false, $availError);
        }

        $room = $this->findFreeRoom($term->id, $day, $start, $end, $rooms);

        $validation = $this->conflictService->validateConflict(
            $term->id, $section->id, $teacher->id, $day, $start, $end, $room?->id
        );

        if ($validation['has_conflict']) {
            return $this->reply(false, false, $validation['message']);
        }

        Timetable::create([
            'academic_term_id' => $term->id,
            'class_section_id' => $section->id,
            'subject_id'       => $subject->id,
            'teacher_id'       => $teacher->id,
            'room_id'          => $room?->id,
            'day_of_week'      => $day,
            'start_time'       => $start,
            'end_time'         => $end,
        ]);

        $msg = "✅ Added <strong>{$subject->subject_name}</strong> for {$this->sectionLabel($section)} with {$teacher->name} on " . ucfirst($day) . " {$start} – {$end}."
            . ($room ? " 📍 Room {$room->room_number}." : '');

        return $this->reply(true, true, $msg);
    }

    /* ─────────────────────────── Remove a lecture ─────────────────────────── */

    protected function doRemove(string $msg, AcademicTerm $term, Collection $sections, Collection $subjects, Collection $teachers): array
    {
        $section = $this->resolveSection($msg, $sections);
        if (!$section) {
            return $this->reply(false, false, 'Which class would you like to remove a lecture from? Please include the class, e.g. "Remove Maths for Class 10A on Monday 10:00-11:00".');
        }

        $subject = $this->resolveSubject($msg, $subjects, $section);
        $teacher = $this->resolveTeacher($msg, $teachers);
        $day     = $this->findDay($msg);
        $ranges  = $this->findAllTimeRanges($msg);

        if (!$subject && !$teacher && !$day && empty($ranges)) {
            return $this->reply(false, false, 'To remove lectures, tell me which subject or which day/time. For example: "Remove Maths for Class 10A on Monday" or "Remove the 10:00 lecture for Class 10A on Tuesday".');
        }

        $query = Timetable::where('academic_term_id', $term->id)
            ->where('class_section_id', $section->id);

        if ($subject) {
            $query->where('subject_id', $subject->id);
        }
        if ($teacher) {
            $query->where('teacher_id', $teacher->id);
        }
        if ($day) {
            $query->where('day_of_week', $day);
        }
        if (!empty($ranges)) {
            $start = $ranges[0]['start'];
            $end   = $ranges[0]['end'];
            $query->where(function ($q) use ($start, $end) {
                $this->applyTimeOverlap($q, $start, $end);
            });
        }

        $slots = $query->with(['subject', 'teacher'])->get();

        if ($slots->isEmpty()) {
            return $this->reply(false, false, "I couldn't find any lecture matching that for {$this->sectionLabel($section)}.");
        }

        $count = $slots->count();
        $names = $slots->map(fn($s) => $s->subject->subject_name)->unique()->take(3)->implode(', ');

        Timetable::whereIn('id', $slots->pluck('id'))->delete();

        return $this->reply(true, true, "🗑️ Removed <strong>{$count}</strong> lecture slot(s) ({$names}) from {$this->sectionLabel($section)}.");
    }

    /* ─────────────────────────── Optimize / close gaps ────────────────────── */

    protected function doOptimize(string $msg, AcademicTerm $term, Collection $sections): array
    {
        $section = $this->resolveSection($msg, $sections);
        if (!$section) {
            return $this->reply(false, false, 'Which class should I optimize? Please include the class, e.g. "Optimize Class 10A timetable" or "Close the gaps for Class 10A on Monday".');
        }

        $day = $this->findDay($msg);

        $query = Timetable::where('academic_term_id', $term->id)
            ->where('class_section_id', $section->id)
            ->with(['subject', 'teacher', 'room']);

        if ($day) {
            $query->where('day_of_week', $day);
        }

        $slots = $query->get();

        if ($slots->isEmpty()) {
            return $this->reply(false, false, "{$this->sectionLabel($section)} has no scheduled lectures" . ($day ? " on " . ucfirst($day) : '') . " to optimize.");
        }

        if ($slots->count() < 2) {
            return $this->reply(false, false, "{$this->sectionLabel($section)} only has one lecture on " . ($day ? ucfirst($day) . ' so there' : 'each day, so there') . " are no gaps to close.");
        }

        $daysToCompact = $day ? [$day] : $slots->pluck('day_of_week')->unique()->values()->all();

        $moved = 0;
        DB::transaction(function () use ($term, $section, $slots, $daysToCompact, &$moved) {
            foreach ($daysToCompact as $d) {
                $daySlots = $slots->filter(fn($s) => $s->day_of_week === $d)
                    ->sortBy(fn($s) => $this->h($s->start_time))
                    ->values();

                if ($daySlots->count() < 2) {
                    continue;
                }

                $cursor = $this->h($daySlots->first()->start_time);

                foreach ($daySlots as $slot) {
                    $dur = $this->minutesBetween($slot);
                    $slotStart = $this->h($slot->start_time);
                    $slotEnd   = $this->h($slot->end_time);

                    if ($slotStart === $cursor) {
                        $cursor = $this->addMinutes($cursor, $dur);
                        continue;
                    }

                    $placed = false;
                    $candidate = $cursor;

                    // Try every 15-minute offset from the cursor until a free, availability-compliant slot is found
                    while ($this->h($candidate) < '23:45') {
                        $candEnd = $this->addMinutes($candidate, $dur);

                        if ($this->canPlace($slot, $d, $candidate, $candEnd, $term->id)) {
                            $slot->update([
                                'day_of_week' => $d,
                                'start_time'  => $candidate,
                                'end_time'    => $candEnd,
                            ]);
                            $cursor = $candEnd;
                            $moved++;
                            $placed = true;
                            break;
                        }

                        $candidate = $this->addMinutes($candidate, 15);
                    }

                    if (!$placed) {
                        $cursor = $slotEnd; // keep the lecture as-is and continue after it
                    }
                }
            }
        });

        if ($moved === 0) {
            return $this->reply(true, false, "👍 {$this->sectionLabel($section)}" . ($day ? ' on ' . ucfirst($day) : '') . " is already compact — I couldn't close any further gaps without causing teacher or room conflicts.");
        }

        $where = $day ? ' on ' . ucfirst($day) : '';
        return $this->reply(
            true,
            true,
            "📐 Optimized {$this->sectionLabel($section)}{$where}: moved <strong>{$moved}</strong> lecture(s) to close time gaps. Lectures are now back-to-back as much as possible without conflicts."
        );
    }

    /**
     * Can a slot be placed at [start, end] on $day without breaking the
     * teacher's availability window or creating a teacher/section/room clash?
     */
    protected function canPlace(Timetable $slot, string $day, string $start, string $end, int $termId): bool
    {
        $avail = TeacherAvailability::where('teacher_id', $slot->teacher_id)
            ->where('day_of_week', $day)
            ->first();

        if (!$avail || !$avail->is_available) {
            return false;
        }

        $ws = $this->h($avail->start_time);
        $we = $this->h($avail->end_time);

        if ($start < $ws || $end > $we) {
            return false;
        }

        $clash = Timetable::where('academic_term_id', $termId)
            ->where('id', '!=', $slot->id)
            ->where('day_of_week', $day)
            ->where(function ($q) use ($start, $end) {
                $this->applyTimeOverlap($q, $start, $end);
            })
            ->where(function ($q) use ($slot) {
                $q->where('teacher_id', $slot->teacher_id)
                    ->orWhere('class_section_id', $slot->class_section_id)
                    ->orWhere('room_id', $slot->room_id);
            })
            ->exists();

        return !$clash;
    }

    /* ─────────────────────────── Show current schedule ────────────────────── */

    protected function showSchedule(string $msg, AcademicTerm $term, Collection $sections, Collection $subjects, Collection $teachers): array
    {
        $section = $this->resolveSection($msg, $sections);
        if (!$section) {
            return $this->reply(false, false, 'Which class schedule would you like to see? For example: "Show Class 10A schedule".');
        }

        $day = $this->findDay($msg);

        $query = Timetable::where('academic_term_id', $term->id)
            ->where('class_section_id', $section->id)
            ->with(['subject', 'teacher', 'room']);

        if ($day) {
            $query->where('day_of_week', $day);
        }

        $slots = $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        if ($slots->isEmpty()) {
            return $this->reply(false, false, "{$this->sectionLabel($section)} has no scheduled lectures" . ($day ? ' on ' . ucfirst($day) : '') . " yet.");
        }

        $lines = $slots->map(fn($s) =>
            '• <strong>' . ucfirst($s->day_of_week) . '</strong> ' . $this->h($s->start_time) . '–' . $this->h($s->end_time)
            . ' → ' . $s->subject->subject_name . ' (' . $s->teacher->name . ')'
            . ($s->room ? ' 📍 ' . $s->room->room_number : '')
        );

        return $this->reply(true, false, "🗓️ Here is the schedule for <strong>{$this->sectionLabel($section)}</strong>" . ($day ? ' on ' . ucfirst($day) : '') . ":<br>" . $lines->implode('<br>'));
    }

    /* ─────────────────────────── Entity resolvers ─────────────────────────── */

    protected function resolveSection(string $msg, Collection $sections): ?ClassSection
    {
        $n = strtolower($msg);

        // "class 10a", "10 A", "grade 10-a", "10th a", "class 10 a"
        if (preg_match('/(?:class|grade|standard|classroom|gr\.?|std\.?|std|section)?\s*(\d{1,2})(?:th|rd|st|nd)?\s*[-_\s]?\s*([a-zA-Z])\b/', $n, $m)) {
            $num = (int) $m[1];
            $letter = strtolower($m[2]);

            foreach ($sections as $s) {
                $cnNum = $this->extractNumeric($s->instituteClass->custom_name ?? '');
                $secLetter = $this->extractSectionLetter($s->section_name);

                if ($cnNum !== null && $cnNum === $num && $secLetter === $letter) {
                    return $s;
                }
            }
        }

        // Fallback: plain label containment, e.g. custom_name "Grade 10" + "A"
        foreach ($sections as $s) {
            $label = strtolower(trim(($s->instituteClass->custom_name ?? '') . ' ' . ($s->section_name ?? '')));
            if ($label !== '' && str_contains($n, $label)) {
                return $s;
            }
        }

        return null;
    }

    protected function resolveSubject(string $msg, Collection $subjects, ?ClassSection $section = null): ?Subject
    {
        $n = strtolower($msg);

        // Prefer subjects that belong to the resolved class
        if ($section) {
            foreach ($subjects as $subject) {
                if ($subject->institute_class_id !== $section->institute_class_id) {
                    continue;
                }
                if ($this->subjectMatches($subject, $n)) {
                    return $subject;
                }
            }
        }

        foreach ($subjects as $subject) {
            if ($this->subjectMatches($subject, $n)) {
                return $subject;
            }
        }

        return null;
    }

    protected function subjectMatches(Subject $subject, string $normalizedMsg): bool
    {
        $name = strtolower($subject->subject_name);
        $code = strtolower((string) $subject->subject_code);

        return ($name !== '' && str_contains($normalizedMsg, $name))
            || ($code !== '' && str_contains($normalizedMsg, $code));
    }

    protected function findAllSubjects(string $msg, Collection $subjects, ?ClassSection $section = null): Collection
    {
        $n = strtolower($msg);
        $matched = collect();
        $matchedNames = [];

        $consider = function (Subject $subject) use (&$matched, &$matchedNames, $n) {
            $name = strtolower($subject->subject_name);
            if ($this->subjectMatches($subject, $n) && !in_array($name, $matchedNames, true)) {
                $matched->push($subject);
                $matchedNames[] = $name;
            }
        };

        // Class-scoped first
        if ($section) {
            foreach ($subjects as $subject) {
                if ($subject->institute_class_id === $section->institute_class_id) {
                    $consider($subject);
                }
            }
        }

        // Then global fallback (only fills in names not already matched)
        foreach ($subjects as $subject) {
            $consider($subject);
        }

        return $matched;
    }

    protected function sectionSubjectSlots(AcademicTerm $term, ClassSection $section, Subject $subject): Collection
    {
        return Timetable::where('academic_term_id', $term->id)
            ->where('class_section_id', $section->id)
            ->where('subject_id', $subject->id)
            ->with(['subject', 'teacher', 'section.instituteClass', 'room'])
            ->get()
            ->sortBy(fn($s) => $this->dayOrder($s->day_of_week) . ' ' . $this->h($s->start_time))
            ->values();
    }

    protected function slotAtTime(AcademicTerm $term, ClassSection $section, string $time, ?string $day): ?Timetable
    {
        $q = Timetable::where('academic_term_id', $term->id)
            ->where('class_section_id', $section->id)
            ->with(['subject', 'teacher', 'section.instituteClass', 'room']);

        if ($day) {
            $q->where('day_of_week', $day);
        }

        $this->applyTimeEqual($q, 'start_time', $time);

        return $q->first();
    }

    protected function dayOrder(string $day): int
    {
        return array_search(strtolower($day), ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], true) ?: 0;
    }

    protected function applyTimeEqual($query, string $column, string $time): void
    {
        $query->whereRaw('TIME_FORMAT(' . $column . ', ?) = ?', ['%H:%i', $time]);
    }

    protected function applyTimeOverlap($query, string $start, string $end): void
    {
        $query->whereRaw('TIME_FORMAT(start_time, ?) < ?', ['%H:%i', $end])
            ->whereRaw('TIME_FORMAT(end_time, ?) > ?', ['%H:%i', $start]);
    }

    protected function resolveTeacher(string $msg, Collection $teachers): ?User
    {
        $n = strtolower($msg);

        foreach ($teachers as $teacher) {
            $full = strtolower($teacher->name);
            if ($full !== '' && str_contains($n, $full)) {
                return $teacher;
            }
        }

        foreach ($teachers as $teacher) {
            $first = strtolower(explode(' ', $teacher->name)[0] ?? '');
            if ($first !== '' && strlen($first) > 2 && str_contains($n, $first)) {
                return $teacher;
            }
        }

        return null;
    }

    /* ─────────────────────────── Text extractors ──────────────────────────── */

    protected function findAllTimeRanges(string $msg): array
    {
        $ranges = [];

        $pattern = '/(\d{1,2})(?::(\d{2}))?\s*(am|pm)?\s*(?:-|–|to|until|till|through)\s*(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/i';

        if (preg_match_all($pattern, $msg, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $start = $this->normalizeTime($m[1], $m[2] ?? null, $m[3] ?? null);
                $end   = $this->normalizeTime($m[4], $m[5] ?? null, $m[6] ?? null);

                if ($start && $end && $end > $start) {
                    $ranges[] = ['start' => $start, 'end' => $end];
                }
            }
        }

        if (empty($ranges)) {
            // Fallback: single time reference → assume 60-minute lecture
            $single = '/(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/i';
            if (preg_match($single, $msg, $m)) {
                $explicit = ($m[2] ?? null) !== null || !empty($m[3]);
                if ($explicit) {
                    $start = $this->normalizeTime($m[1], $m[2] ?? null, $m[3] ?? null);
                    if ($start) {
                        $ranges[] = ['start' => $start, 'end' => $this->addMinutes($start, 60)];
                    }
                }
            }
        }

        return $ranges;
    }

    protected function findAllTimes(string $msg): array
    {
        $times = [];

        if (preg_match_all('/(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/i', $msg, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $explicit = ($m[2] ?? null) !== null || !empty($m[3]);
                if (!$explicit) {
                    continue;
                }
                $time = $this->normalizeTime($m[1], $m[2] ?? null, $m[3] ?? null);
                if ($time && !in_array($time, $times, true)) {
                    $times[] = $time;
                }
            }
        }

        return $times;
    }

    protected function findAllDays(string $msg): array
    {
        $days = [];
        $n = strtolower($msg);

        $patterns = [
            'monday'     => '/\bmon(?:day)?\b/',
            'tuesday'    => '/\btue(?:s(?:day)?)?\b/',
            'wednesday'  => '/\bwed(?:nesday)?\b/',
            'thursday'   => '/\bthu(?:r(?:s(?:day)?)?)?\b/',
            'friday'     => '/\bfri(?:day)?\b/',
            'saturday'   => '/\bsat(?:urday)?\b/',
            'sunday'     => '/\bsun(?:day)?\b/',
        ];

        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $n)) {
                $days[] = $name;
            }
        }

        return $days;
    }

    protected function findDay(string $msg): ?string
    {
        return $this->findAllDays($msg)[0] ?? null;
    }

    protected function normalizeTime(string $h, ?string $min, ?string $meridiem): ?string
    {
        $hour = (int) $h;
        $minute = $min !== null ? (int) $min : 0;

        $meridiem = strtolower((string) $meridiem);

        if ($meridiem === 'pm' && $hour < 12) {
            $hour += 12;
        }
        if ($meridiem === 'am' && $hour === 12) {
            $hour = 0;
        }

        if ($hour > 23 || $minute > 59) {
            return null;
        }

        return sprintf('%02d:%02d', $hour, $minute);
    }

    /* ─────────────────────────── Helpers ──────────────────────────────────── */

    protected function availabilityCovers(int $teacherId, string $day, string $start, string $end): ?string
    {
        $avail = TeacherAvailability::where('teacher_id', $teacherId)
            ->where('day_of_week', strtolower($day))
            ->first();

        if (!$avail) {
            return "⚠️ This teacher has no availability set for " . ucfirst($day) . ". Set it under Teacher Availability first.";
        }

        if (!$avail->is_available) {
            return "⚠️ This teacher is marked <strong>unavailable</strong> on " . ucfirst($day) . ".";
        }

        $ws = $this->h($avail->start_time);
        $we = $this->h($avail->end_time);

        if ($start < $ws || $end > $we) {
            return "⚠️ The teacher's availability on " . ucfirst($day) . " is {$ws}–{$we}, so the {$start}–{$end} slot falls outside it.";
        }

        return null;
    }

    protected function findFreeRoom(int $termId, string $day, string $start, string $end, Collection $rooms): ?Room
    {
        foreach ($rooms as $room) {
            $conflict = Timetable::where('academic_term_id', $termId)
                ->where('room_id', $room->id)
                ->where('day_of_week', $day)
                ->where(function ($q) use ($start, $end) {
                    $this->applyTimeOverlap($q, $start, $end);
                })
                ->exists();

            if (!$conflict) {
                return $room;
            }
        }

        return null;
    }

    protected function slotTimes(Timetable $slot): array
    {
        return [
            'day'   => $slot->day_of_week,
            'start' => $this->h($slot->start_time),
            'end'   => $this->h($slot->end_time),
        ];
    }

    protected function h(?string $t): string
    {
        $t = (string) $t;

        return strlen($t) >= 5 ? substr($t, 0, 5) : $t;
    }

    protected function addMinutes(string $time, int $minutes): string
    {
        [$h, $m] = array_map('intval', explode(':', $time));
        $total = $h * 60 + $m + $minutes;

        return sprintf('%02d:%02d', intdiv($total, 60) % 24, $total % 60);
    }

    protected function minutesBetween(Timetable $slot): int
    {
        [$sh, $sm] = array_map('intval', explode(':', $this->h($slot->start_time)));
        [$eh, $em] = array_map('intval', explode(':', $this->h($slot->end_time)));

        return (($eh * 60 + $em) - ($sh * 60 + $sm)) ?: 60;
    }

    protected function extractNumeric(?string $s): ?int
    {
        if (preg_match('/\d{1,2}/', (string) $s, $m)) {
            return (int) $m[0];
        }

        return null;
    }

    protected function extractSectionLetter(?string $s): string
    {
        if (preg_match('/[a-zA-Z]/', (string) $s, $m)) {
            return strtolower($m[0]);
        }

        return strtolower((string) $s);
    }

    protected function sectionLabel(ClassSection $section): string
    {
        $class = $section->instituteClass->custom_name ?? 'Class';
        $sectionName = $section->section_name ?? '';

        return trim($class . ' ' . $sectionName);
    }

    protected function hasAnyWord(string $msg, array $words): bool
    {
        $n = strtolower($msg);

        foreach ($words as $word) {
            if (str_contains($n, strtolower($word))) {
                return true;
            }
        }

        return false;
    }

    protected function isHelp(string $msg): bool
    {
        return in_array(strtolower($msg), ['help', 'hi', 'hello', 'hey', '?'], true)
            || $this->hasAnyWord($msg, ['what can you do', 'how does this work', 'how do i']);
    }

    protected function noSlotsMessage(AcademicTerm $term, ClassSection $section, Collection $sections, Collection $subjects, Collection $teachers, string $msg, string $hint): string
    {
        $existing = Timetable::where('academic_term_id', $term->id)
            ->where('class_section_id', $section->id)
            ->with(['subject', 'teacher'])
            ->get();

        if ($existing->isEmpty()) {
            return "{$this->sectionLabel($section)} has no scheduled lectures" . $hint . ". Try adding one first, e.g. \"Add English for {$this->sectionLabel($section)} on Tuesday 9:00-10:00\".";
        }

        $list = $existing->map(fn($s) => '• ' . ucfirst($s->day_of_week) . ' ' . $this->h($s->start_time) . '–' . $this->h($s->end_time) . ' → ' . $s->subject->subject_name)
            ->implode('<br>');

        return "I couldn't find a matching lecture" . $hint . " for {$this->sectionLabel($section)}. Current lectures are:<br>" . $list;
    }

    protected function helpMessage(): array
    {
        $help = "🤖 <strong>Schedule Assistant</strong> — I understand natural language. Here's what I can do:<br>"
            . "📐 <strong>Optimize / close gaps:</strong> \"Optimize Class 10A timetable\" or \"Close the gap for Class 10A on Monday\"<br>"
            . "↔️ <strong>Move a lecture:</strong> \"Move Maths for Class 10A to Tuesday 9:00-10:00\"<br>"
            . "🔁 <strong>Swap lectures:</strong> \"Swap Maths and English for Class 10A\"<br>"
            . "➕ <strong>Add a lecture:</strong> \"Add English for Class 10A on Tuesday 9:00-10:00\"<br>"
            . "🗑️ <strong>Remove a lecture:</strong> \"Remove Maths for Class 10A on Monday 10:00-11:00\"<br>"
            . "🗓️ <strong>Show schedule:</strong> \"Show Class 10A schedule\"<br><br>"
            . "Just type it like you'd say it to a real coordinator — I'll adjust the schedule with conflict checking and tell you what changed.";

        return $this->reply(true, false, $help);
    }

    protected function reply(bool $success, bool $changed, string $message): array
    {
        return [
            'success' => $success,
            'changed' => $changed,
            'message' => $message,
        ];
    }
}
