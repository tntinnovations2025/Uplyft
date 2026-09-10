<?php

namespace App\Services;

use App\Models\AcademicTerm;
use App\Models\AccountHead;
use App\Models\AccountTransaction;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\ChatHistory;
use App\Models\ClassSection;
use App\Models\FeeInvoice;
use App\Models\Institute;
use App\Models\InstituteClass;
use App\Models\InstituteClassAssignment;
use App\Models\InstituteFeatureToggle;
use App\Models\InstituteSetting;
use App\Models\Organization;
use App\Models\PracticeTestSession;
use App\Models\RagDocumentChunk;
use App\Models\RoleDefaultAuthority;
use App\Models\Room;
use App\Models\ScholarshipCategory;
use App\Models\Student;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentPracticeAttempt;
use App\Models\StudentSubjectResult;
use App\Models\Subject;
use App\Models\SubjectMaterial;
use App\Models\Teacher;
use App\Models\TeacherSalarySlip;
use App\Models\TeacherSubjectSection;
use App\Models\Timetable;
use App\Models\TimetableAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InstitutePurgeService
{
    /**
     * Completely delete an Institute, along with ALL linked data, users, and portals.
     */
    public function purgeInstitute(Institute $institute): void
    {
        DB::transaction(function () use ($institute) {
            $instituteId = $institute->id;
            Log::warning("Beginning complete purge of Institute ID: {$instituteId} ({$institute->name})");

            // 1. Student IDs for child record deletion
            $studentIds = Student::withoutGlobalScopes()
                ->where('institute_id', $instituteId)
                ->pluck('id')
                ->toArray();

            if (!empty($studentIds)) {
                DB::table('student_assessment_answers')->whereIn('student_id', $studentIds)->delete();
                DB::table('student_practice_attempts')->whereIn('student_id', $studentIds)->delete();
                DB::table('student_subject_results')->whereIn('student_id', $studentIds)->delete();
            }

            // 2. Class, Section, and Subject IDs
            $classIds = DB::table('institute_classes')->where('institute_id', $instituteId)->pluck('id')->toArray();
            $sectionIds = !empty($classIds) ? DB::table('class_sections')->whereIn('institute_class_id', $classIds)->pluck('id')->toArray() : [];
            $subjectIds = !empty($classIds) ? DB::table('subjects')->whereIn('institute_class_id', $classIds)->pluck('id')->toArray() : [];

            // 3. Teachers & allocations
            $teacherIds = Teacher::withoutGlobalScopes()
                ->where('institute_id', $instituteId)
                ->pluck('id')
                ->toArray();

            if (!empty($teacherIds)) {
                DB::table('teacher_salary_slips')->whereIn('teacher_id', $teacherIds)->delete();
                DB::table('teacher_availabilities')->whereIn('teacher_id', $teacherIds)->delete();
                DB::table('teacher_subject_sections')->whereIn('teacher_id', $teacherIds)->delete();
            }

            if (!empty($sectionIds)) {
                DB::table('teacher_subject_sections')->whereIn('class_section_id', $sectionIds)->delete();
            }

            // 4. Timetables (Direct table, no timetable_allocations)
            if (!empty($sectionIds)) {
                DB::table('timetables')->whereIn('class_section_id', $sectionIds)->delete();
            }
            if (!empty($subjectIds)) {
                DB::table('timetables')->whereIn('subject_id', $subjectIds)->delete();
            }
            if (!empty($teacherIds)) {
                DB::table('timetables')->whereIn('teacher_id', $teacherIds)->delete();
            }

            // 5. Assessments, grade weightages & materials
            if (!empty($subjectIds)) {
                $assessmentIds = DB::table('assessments')->whereIn('subject_id', $subjectIds)->pluck('id')->toArray();
                if (!empty($assessmentIds)) {
                    DB::table('assessment_questions')->whereIn('assessment_id', $assessmentIds)->delete();
                    DB::table('assessments')->whereIn('id', $assessmentIds)->delete();
                }
                DB::table('grade_weightages')->whereIn('subject_id', $subjectIds)->delete();
                DB::table('subject_materials')->whereIn('subject_id', $subjectIds)->delete();
                DB::table('subjects')->whereIn('id', $subjectIds)->delete();
            }
            if (!empty($sectionIds)) {
                DB::table('grade_weightages')->whereIn('class_section_id', $sectionIds)->delete();
                DB::table('assessments')->whereIn('class_section_id', $sectionIds)->delete();
            }

            // 6. Delete Sections & Classes
            if (!empty($sectionIds)) {
                DB::table('class_sections')->whereIn('id', $sectionIds)->delete();
            }
            if (!empty($classIds)) {
                DB::table('institute_classes')->whereIn('id', $classIds)->delete();
            }

            // 7. Institute Direct Tables (Safely verified against schema columns)
            $directTables = [
                'student_subject_results',
                'students',
                'teacher_salary_slips',
                'teachers',
                'attendances',
                'attendance_settings',
                'assignments',
                'invoices',
                'financial_transactions',
                'account_heads',
                'scholarship_categories',
                'rooms',
                'academic_terms',
                'institute_class_assignments',
                'role_default_authorities',
                'institute_feature_toggles',
                'institute_settings',
                'password_reset_notifications',
            ];

            foreach ($directTables as $tbl) {
                if (\Illuminate\Support\Facades\Schema::hasTable($tbl) && \Illuminate\Support\Facades\Schema::hasColumn($tbl, 'institute_id')) {
                    DB::table($tbl)->where('institute_id', $instituteId)->delete();
                }
            }

            // 8. Delete user-linked and subject-linked AI & LMS history
            $userIds = User::withoutGlobalScopes()
                ->where('institute_id', $instituteId)
                ->pluck('id')
                ->toArray();

            if (!empty($userIds)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('chat_histories') && \Illuminate\Support\Facades\Schema::hasColumn('chat_histories', 'user_id')) {
                    DB::table('chat_histories')->whereIn('user_id', $userIds)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('practice_test_sessions') && \Illuminate\Support\Facades\Schema::hasColumn('practice_test_sessions', 'user_id')) {
                    DB::table('practice_test_sessions')->whereIn('user_id', $userIds)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('student_practice_attempts') && \Illuminate\Support\Facades\Schema::hasColumn('student_practice_attempts', 'user_id')) {
                    DB::table('student_practice_attempts')->whereIn('user_id', $userIds)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('password_reset_notifications') && \Illuminate\Support\Facades\Schema::hasColumn('password_reset_notifications', 'user_id')) {
                    DB::table('password_reset_notifications')->whereIn('user_id', $userIds)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                    DB::table('sessions')->whereIn('user_id', $userIds)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                    DB::table('notifications')->where('notifiable_type', User::class)->whereIn('notifiable_id', $userIds)->delete();
                }
            }

            if (!empty($subjectIds)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('rag_document_chunks') && \Illuminate\Support\Facades\Schema::hasColumn('rag_document_chunks', 'subject_id')) {
                    DB::table('rag_document_chunks')->whereIn('subject_id', $subjectIds)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('chat_histories') && \Illuminate\Support\Facades\Schema::hasColumn('chat_histories', 'subject_id')) {
                    DB::table('chat_histories')->whereIn('subject_id', $subjectIds)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('practice_test_sessions') && \Illuminate\Support\Facades\Schema::hasColumn('practice_test_sessions', 'subject_id')) {
                    DB::table('practice_test_sessions')->whereIn('subject_id', $subjectIds)->delete();
                }
            }

            // 9. Delete uploaded files in storage
            if ($institute->logo_path) {
                Storage::disk('public')->delete($institute->logo_path);
            }
            if ($institute->icon_path && $institute->icon_path !== $institute->logo_path) {
                Storage::disk('public')->delete($institute->icon_path);
            }

            // 10. Force delete all user portal accounts linked to this institute
            User::withoutGlobalScopes()
                ->where('institute_id', $instituteId)
                ->forceDelete();

            // 11. Force delete the institute record itself
            $institute->forceDelete();

            Log::info("Successfully purged all data, users, and portals for Institute ID: {$instituteId}");
        });
    }

    /**
     * Completely delete an Organization, along with all member campuses and their data.
     */
    public function purgeOrganization(Organization $organization): void
    {
        DB::transaction(function () use ($organization) {
            $orgId = $organization->id;
            $orgName = $organization->name;
            Log::warning("Beginning complete purge of Organization ID: {$orgId} ({$orgName})");

            // 1. Purge all member institutes and their entire data trees
            $campuses = Institute::withoutGlobalScopes()
                ->withTrashed()
                ->where('organization_id', $orgId)
                ->get();

            foreach ($campuses as $campus) {
                $this->purgeInstitute($campus);
            }

            // 2. Delete any users explicitly belonging to this organization network
            User::withoutGlobalScopes()
                ->where('organization_id', $orgId)
                ->forceDelete();

            // 3. Force delete the organization record
            $organization->forceDelete();

            Log::info("Successfully purged Organization ID: {$orgId} ({$orgName}) and all linked campuses.");
        });
    }

    /**
     * Deactivate an institute and suspend all portals linked to it.
     */
    public function deactivateInstitute(Institute $institute): void
    {
        $institute->update(['is_active' => false]);

        // Invalidate active sessions for all users of this institute by changing remember_token
        User::withoutGlobalScopes()
            ->where('institute_id', $institute->id)
            ->update(['remember_token' => null]);

        Log::info("Institute ID: {$institute->id} deactivated. All linked portals suspended.");
    }

    /**
     * Reactivate an institute and restore portal access.
     */
    public function activateInstitute(Institute $institute): void
    {
        $institute->update(['is_active' => true]);
        Log::info("Institute ID: {$institute->id} reactivated. Portals restored.");
    }

    /**
     * Deactivate an organization and all campuses under it.
     */
    public function deactivateOrganization(Organization $organization): void
    {
        DB::transaction(function () use ($organization) {
            $organization->update(['is_active' => false]);

            $campuses = Institute::withoutGlobalScopes()
                ->where('organization_id', $organization->id)
                ->get();

            foreach ($campuses as $campus) {
                $this->deactivateInstitute($campus);
            }

            User::withoutGlobalScopes()
                ->where('organization_id', $organization->id)
                ->update(['remember_token' => null]);

            Log::info("Organization ID: {$organization->id} deactivated. All member campuses suspended.");
        });
    }

    /**
     * Reactivate an organization and all campuses under it.
     */
    public function activateOrganization(Organization $organization): void
    {
        DB::transaction(function () use ($organization) {
            $organization->update(['is_active' => true]);

            $campuses = Institute::withoutGlobalScopes()
                ->where('organization_id', $organization->id)
                ->get();

            foreach ($campuses as $campus) {
                $this->activateInstitute($campus);
            }

            Log::info("Organization ID: {$organization->id} reactivated. All member campuses restored.");
        });
    }
}
