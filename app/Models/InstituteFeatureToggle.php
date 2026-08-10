<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituteFeatureToggle extends Model
{
    protected $fillable = [
        'institute_id',
        'ai_bot',
        'attendance_system',
        'assessment_engine',
        'fee_invoicing',
        'timetable',
        'registration_portals',
        'lms_content',
        'grading_normalizer',
        'teacher_portal',
        'principal_portal',
        'parent_portal',
        'sms_notifications',
        'last_updated_by',
        'last_updated_at',
    ];

    protected $casts = [
        'ai_bot'                 => 'boolean',
        'attendance_system'      => 'boolean',
        'assessment_engine'      => 'boolean',
        'fee_invoicing'          => 'boolean',
        'timetable'              => 'boolean',
        'registration_portals'   => 'boolean',
        'lms_content'            => 'boolean',
        'grading_normalizer'     => 'boolean',
        'teacher_portal'         => 'boolean',
        'principal_portal'       => 'boolean',
        'parent_portal'          => 'boolean',
        'sms_notifications'      => 'boolean',
        'last_updated_at'        => 'datetime',
    ];

    // ── All toggle feature keys (used for validation & iteration) ─────────
    public static array $featureKeys = [
        'ai_bot',
        'attendance_system',
        'assessment_engine',
        'fee_invoicing',
        'timetable',
        'registration_portals',
        'lms_content',
        'grading_normalizer',
        'teacher_portal',
        'principal_portal',
        'parent_portal',
        'sms_notifications',
    ];

    // ── Human-readable labels for the dashboard UI ───────────────────────
    public static array $featureLabels = [
        'ai_bot'                 => 'AI / RAG Bot',
        'attendance_system'      => 'Attendance System',
        'assessment_engine'      => 'Assessment Engine',
        'fee_invoicing'          => 'Fee Invoicing',
        'timetable'              => 'Timetable Builder',
        'registration_portals'   => 'Registration Portals',
        'lms_content'            => 'LMS Content Upload',
        'grading_normalizer'     => 'Grade Normalizer',
        'teacher_portal'         => 'Teacher Portal',
        'principal_portal'       => 'Principal Portal',
        'parent_portal'          => 'Parent Portal',
        'sms_notifications'      => 'SMS Notifications',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}
