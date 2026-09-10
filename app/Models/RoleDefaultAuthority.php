<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleDefaultAuthority extends Model
{
    use HasFactory, TenantIsolated;

    protected $fillable = [
        'institute_id',
        'role_slug',
        'role_name',
        'permissions',
        'is_custom',
        'description',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_custom' => 'boolean',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    /**
     * Get or initialize default role authorities for an institute.
     */
    public static function seedDefaultsForInstitute(int $instituteId): void
    {
        $defaults = [
            [
                'role_slug' => 'teacher',
                'role_name' => 'Teacher',
                'is_custom' => false,
                'description' => 'Faculty member with attendance marking, LMS content management, and student evaluation permissions.',
                'permissions' => [
                    'attendance' => true,
                    'attendance_view' => true,
                    'attendance_edit' => true,
                    'lms_content' => true,
                    'lms_notes_upload' => true,
                    'ai_bot' => true,
                    'assessment_engine' => true,
                    'grading_normalizer' => true,
                    'directory' => true,
                    'directory_export' => false,
                    'student_registration' => false,
                    'students' => false,
                    'students_view' => true,
                    'students_edit' => false,
                    'students_promote' => false,
                    'profile_edit' => false,
                    'invoices' => false,
                    'invoices_collect' => false,
                    'invoices_discount' => false,
                    'staff' => false,
                    'staff_onboard' => false,
                    'staff_view' => false,
                    'staff_salaries' => false,
                    'staff_authorities' => false,
                    'academics' => true,
                    'classes' => true,
                    'subjects' => true,
                    'rooms' => false,
                    'faculty_hours' => true,
                    'allocations' => true,
                    'timetables' => true,
                    'exams_datesheet' => true,
                    'security' => false,
                ],
            ],
            [
                'role_slug' => 'administration',
                'role_name' => 'Administration',
                'is_custom' => false,
                'description' => 'Administrative officer with student registration, roster governance, academics, and scheduling rights.',
                'permissions' => [
                    'attendance' => true,
                    'attendance_view' => true,
                    'attendance_edit' => true,
                    'lms_content' => true,
                    'lms_notes_upload' => true,
                    'ai_bot' => true,
                    'assessment_engine' => true,
                    'grading_normalizer' => true,
                    'directory' => true,
                    'directory_export' => true,
                    'student_registration' => true,
                    'students' => true,
                    'students_view' => true,
                    'students_edit' => true,
                    'students_promote' => true,
                    'profile_edit' => true,
                    'invoices' => true,
                    'invoices_collect' => true,
                    'invoices_discount' => true,
                    'staff' => true,
                    'staff_onboard' => true,
                    'staff_view' => true,
                    'staff_salaries' => true,
                    'staff_authorities' => true,
                    'academics' => true,
                    'classes' => true,
                    'subjects' => true,
                    'rooms' => true,
                    'faculty_hours' => true,
                    'allocations' => true,
                    'timetables' => true,
                    'scholarships' => true,
                    'exams_datesheet' => true,
                    'security' => true,
                ],
            ],
            [
                'role_slug' => 'accountant',
                'role_name' => 'Accountant',
                'is_custom' => false,
                'description' => 'Financial officer responsible for accounts, faculty/staff payroll salaries, student billing, and financial audits.',
                'permissions' => [
                    'attendance' => false,
                    'attendance_view' => false,
                    'attendance_edit' => false,
                    'lms_content' => false,
                    'lms_notes_upload' => false,
                    'ai_bot' => false,
                    'assessment_engine' => false,
                    'grading_normalizer' => false,
                    'directory' => true,
                    'directory_export' => true,
                    'student_registration' => false,
                    'students' => false,
                    'students_view' => true,
                    'students_edit' => false,
                    'students_promote' => false,
                    'profile_edit' => false,
                    'invoices' => true,
                    'invoices_collect' => true,
                    'invoices_discount' => true,
                    'accounts' => true,
                    'staff' => false,
                    'staff_onboard' => false,
                    'staff_view' => true,
                    'staff_salaries' => true,
                    'staff_authorities' => false,
                    'scholarships' => true,
                    'academics' => false,
                    'classes' => false,
                    'subjects' => false,
                    'rooms' => false,
                    'faculty_hours' => false,
                    'allocations' => false,
                    'timetables' => false,
                    'exams_datesheet' => false,
                    'security' => false,
                ],
            ],
            [
                'role_slug' => 'coordinator',
                'role_name' => 'Academic Coordinator',
                'is_custom' => false,
                'description' => 'Academic supervisor managing course catalogs, subject allocations, and timetable matrices.',
                'permissions' => [
                    'attendance' => true,
                    'attendance_view' => true,
                    'attendance_edit' => true,
                    'lms_content' => true,
                    'lms_notes_upload' => true,
                    'ai_bot' => true,
                    'assessment_engine' => true,
                    'grading_normalizer' => true,
                    'directory' => true,
                    'directory_export' => true,
                    'student_registration' => false,
                    'students' => false,
                    'students_view' => true,
                    'students_edit' => false,
                    'students_promote' => false,
                    'profile_edit' => false,
                    'invoices' => false,
                    'invoices_collect' => false,
                    'invoices_discount' => false,
                    'staff' => true,
                    'staff_onboard' => false,
                    'staff_view' => true,
                    'staff_salaries' => false,
                    'staff_authorities' => false,
                    'scholarships' => false,
                    'academics' => true,
                    'classes' => true,
                    'subjects' => true,
                    'rooms' => true,
                    'faculty_hours' => true,
                    'allocations' => true,
                    'timetables' => true,
                    'exams_datesheet' => true,
                    'security' => false,
                ],
            ],
        ];

        foreach ($defaults as $def) {
            static::updateOrCreate(
                [
                    'institute_id' => $instituteId,
                    'role_slug' => $def['role_slug'],
                ],
                [
                    'role_name' => $def['role_name'],
                    'permissions' => $def['permissions'],
                    'is_custom' => $def['is_custom'],
                    'description' => $def['description'],
                ]
            );
        }
    }
}
