<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timetable extends Model
{
    use HasFactory, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereHas('academicTerm', fn ($q) => $q->whereIn('institute_id', $campusIds));
    }

    protected $fillable = [
        'academic_term_id',
        'class_section_id',
        'subject_id',
        'teacher_id',
        'room_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function getFormattedTimeRangeAttribute(): string
    {
        $start = date('g:i A', strtotime($this->start_time));
        $end = date('g:i A', strtotime($this->end_time));
        return "{$start} – {$end}";
    }

    public function getDurationHoursFormattedAttribute(): string
    {
        $startSec = strtotime($this->start_time);
        $endSec = strtotime($this->end_time);
        $diffMins = max(0, ($endSec - $startSec) / 60);

        $hours = floor($diffMins / 60);
        $mins = $diffMins % 60;

        if ($hours > 0 && $mins > 0) {
            $decimal = round($diffMins / 60, 2);
            return "{$decimal} Hours ({$hours}h {$mins}m)";
        } elseif ($hours > 0) {
            return "{$hours} " . ($hours == 1 ? 'Hour' : 'Hours');
        } else {
            return "{$mins} Mins";
        }
    }

    public function getFormattedRoomNameAttribute(): string
    {
        if (!$this->room) {
            return 'Campus Room';
        }
        $num = trim($this->room->room_number);
        if (preg_match('/^room\s+/i', $num)) {
            return $num;
        }
        return "Room {$num}";
    }

    /**
     * Merge contiguous timetable slots for the same day, subject, teacher, section, and room.
     * Guaranteed to return slots sorted chronologically by day of week and start time.
     */
    public static function mergeContiguousSlots($slotsCollection)
    {
        if (!$slotsCollection || $slotsCollection->isEmpty()) {
            return collect();
        }

        $dayOrder = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
        ];

        $groupedByDay = $slotsCollection->groupBy(fn ($s) => strtolower($s->day_of_week));
        $sortedDayKeys = $groupedByDay->keys()->sortBy(fn ($day) => $dayOrder[$day] ?? 99);

        $mergedResult = collect();

        foreach ($sortedDayKeys as $day) {
            $daySlots = $groupedByDay[$day];
            $sorted = $daySlots->sortBy('start_time')->values();
            $mergedDaySlots = [];

            foreach ($sorted as $slot) {
                if (empty($mergedDaySlots)) {
                    $mergedDaySlots[] = clone $slot;
                } else {
                    $lastIndex = count($mergedDaySlots) - 1;
                    $last = $mergedDaySlots[$lastIndex];

                    $lastEndSec = strtotime($last->end_time);
                    $slotStartSec = strtotime($slot->start_time);
                    $slotEndSec = strtotime($slot->end_time);

                    $sameSubject = ($last->subject_id == $slot->subject_id);
                    $sameTeacher = ($last->teacher_id == $slot->teacher_id);
                    $sameRoom = ($last->room_id == $slot->room_id || (!$last->room_id && !$slot->room_id));
                    $sameSection = ($last->class_section_id == $slot->class_section_id);

                    // Merge contiguous (or overlapping) slots for the same class/subject
                    if ($sameSubject && $sameTeacher && $sameSection && $sameRoom && $slotStartSec <= ($lastEndSec + 60)) {
                        if ($slotEndSec > $lastEndSec) {
                            $last->end_time = $slot->end_time;
                        }
                    } else {
                        $mergedDaySlots[] = clone $slot;
                    }
                }
            }

            foreach ($mergedDaySlots as $mSlot) {
                $mergedResult->push($mSlot);
            }
        }

        return $mergedResult->sortBy(function ($slot) use ($dayOrder) {
            $d = strtolower($slot->day_of_week);
            $rank = $dayOrder[$d] ?? 99;
            return sprintf('%02d_%s', $rank, $slot->start_time);
        })->values();
    }
}
