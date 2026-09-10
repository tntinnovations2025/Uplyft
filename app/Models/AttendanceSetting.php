<?php

namespace App\Models;

use App\Models\Scopes\InstituteScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'attendance_mode',
        'start_time',
        'end_time',
        'allow_past_edits',
        'is_locked_override',
    ];

    protected $casts = [
        'allow_past_edits' => 'boolean',
        'is_locked_override' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new InstituteScope);

        static::creating(function ($setting) {
            if (empty($setting->institute_id)) {
                if (Auth::check() && isset(Auth::user()->institute_id)) {
                    $setting->institute_id = Auth::user()->institute_id;
                } elseif (app()->bound('current_institute_id')) {
                    $setting->institute_id = app('current_institute_id');
                }
            }
        });
    }

    /**
     * Get or create default attendance settings for an institute.
     */
    public static function getForInstitute(?int $instituteId = null): self
    {
        $instId = $instituteId ?? (Auth::check() ? Auth::user()->institute_id : 1);

        return static::withoutGlobalScopes()->firstOrCreate(
            ['institute_id' => $instId],
            [
                'attendance_mode' => 'daily',
                'start_time' => '08:00',
                'end_time' => '16:00',
                'allow_past_edits' => false,
                'is_locked_override' => false,
            ]
        );
    }

    /**
     * Check if attendance is locked for a specific date and time.
     * Returns array ['is_locked' => bool, 'reason' => string]
     */
    public function getLockStatusForDate(string $targetDateStr): array
    {
        // If principal explicitly forced unlock override
        if ($this->is_locked_override) {
            return [
                'is_locked' => false,
                'reason' => 'Principal explicitly unlocked attendance editing.',
            ];
        }

        $tz = config('app.timezone', 'Asia/Karachi');
        $now = Carbon::now($tz);
        $targetDate = Carbon::parse($targetDateStr, $tz)->startOfDay();
        $today = Carbon::today($tz);

        // Past dates check
        if ($targetDate->lt($today)) {
            if (!$this->allow_past_edits) {
                return [
                    'is_locked' => true,
                    'reason' => "Attendance for past dates is locked. Principal controls disallow editing past attendance records ({$targetDate->format('M d, Y')}).",
                ];
            }
        }

        // Future dates check
        if ($targetDate->gt($today)) {
            return [
                'is_locked' => true,
                'reason' => 'Attendance cannot be marked for future dates.',
            ];
        }

        // Today check - Time Window check
        $startTime = Carbon::createFromFormat('H:i', $this->start_time, $tz)->setDateFrom($now);
        $endTime = Carbon::createFromFormat('H:i', $this->end_time, $tz)->setDateFrom($now);

        if ($now->lt($startTime)) {
            $formattedStart = Carbon::createFromFormat('H:i', $this->start_time, $tz)->format('g:i A');
            return [
                'is_locked' => true,
                'reason' => "Attendance marking window has not opened yet. Today's window opens at {$formattedStart}.",
            ];
        }

        if ($now->gt($endTime)) {
            $formattedEnd = Carbon::createFromFormat('H:i', $this->end_time, $tz)->format('g:i A');
            return [
                'is_locked' => true,
                'reason' => "Attendance marking window closed today at {$formattedEnd}. Edits are locked by Principal Attendance Controls.",
            ];
        }

        return [
            'is_locked' => false,
            'reason' => 'Attendance window is active.',
        ];
    }
}
