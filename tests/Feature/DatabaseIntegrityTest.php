<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\ClassSubject;
use App\Models\DailyDiary;
use App\Models\Institute;
use App\Models\InstituteClass;
use App\Models\StudentSubjectEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;
use Tests\TestCase;

class DatabaseIntegrityTest extends TestCase
{
    /**
     * Test 1: Cascade Deletion on ClassSection.
     * Deleting a ClassSection must cascade-delete all corresponding
     * student_subject_enrollments linked to that section.
     */
    public function test_deleting_class_section_cascades_student_subject_enrollments(): void
    {
        $institute = Institute::withoutGlobalScopes()->where('slug', 'apex-college')->first();
        $this->assertNotNull($institute, 'Apex College must exist');

        // Get an institute class belonging to Apex College
        $class = InstituteClass::withoutGlobalScopes()->where('institute_id', $institute->id)->first();

        // Create a dedicated section for this deletion test
        $section = ClassSection::create([
            'institute_class_id' => $class->id,
            'section_name'       => 'Test Section Temp',
            'capacity'           => 30,
        ]);

        // Pick an existing student user and subject from the institute_class
        $studentUser = User::withoutGlobalScopes()
            ->where('institute_id', $institute->id)
            ->where('role', User::ROLE_STUDENT)
            ->first();

        $subject = Subject::withoutGlobalScopes()
            ->where('institute_class_id', $class->id)
            ->first();

        // Create enrollment in this section
        $enrollment = StudentSubjectEnrollment::create([
            'institute_id'      => $institute->id,
            'student_id'        => $studentUser->id,
            'class_section_id'  => $section->id,
            'subject_id'        => $subject->id,
            'enrollment_status' => 'active',
        ]);

        $this->assertDatabaseHas('student_subject_enrollments', [
            'id'               => $enrollment->id,
            'class_section_id' => $section->id,
        ]);

        // Delete the ClassSection
        $section->delete();

        // Assert 0 orphaned rows exist for this section
        $this->assertDatabaseMissing('student_subject_enrollments', [
            'id' => $enrollment->id,
        ]);
        $this->assertEquals(
            0,
            StudentSubjectEnrollment::withoutGlobalScopes()->where('class_section_id', $section->id)->count()
        );
    }

    /**
     * Test 2: Cascade Deletion on Institute.
     * Deleting an Institute cascades down and completely wipes all dependent data:
     * users, classes, materials, daily_diaries, and vectors.
     */
    public function test_deleting_institute_cascades_all_dependent_data(): void
    {
        // Create an isolated dummy institute to test full cascade deletion
        $tempInst = Institute::create([
            'name'                   => 'Purge Academy',
            'slug'                   => 'purge-academy-' . uniqid(),
            'subscription_tier'      => 'premium',
            'subscription_starts_at' => now(),
            'subscription_expires_at'=> now()->addYear(),
            'is_active'              => true,
            'is_onboarded'           => true,
            'contact_email'          => 'purge@academy.local',
            'city'                   => 'Lahore',
            'country'                => 'Pakistan',
        ]);

        $instId = $tempInst->id;

        $user = User::create([
            'name'         => 'Purge User',
            'email'        => 'user' . uniqid() . '@purge.local',
            'password'     => \Illuminate\Support\Facades\Hash::make(Str::random(16)),
            'role'         => User::ROLE_TEACHER,
            'institute_id' => $instId,
            'identifier'   => 'PURGE-' . uniqid(),
        ]);

        $class = InstituteClass::create([
            'institute_id'  => $instId,
            'name'          => 'Purge Class 1',
            'custom_name'   => 'Purge Class 1',
            'numeric_level' => 1,
        ]);

        $section = ClassSection::create([
            'institute_class_id' => $class->id,
            'section_name'       => 'Section P',
            'capacity'           => 20,
        ]);

        $subject = Subject::create([
            'institute_class_id' => $class->id,
            'subject_name'       => 'Purge Science',
            'subject_code'       => 'SCI-PURGE-' . uniqid(),
            'credit_hours'       => 3,
        ]);

        $diary = DailyDiary::create([
            'institute_id'     => $instId,
            'class_section_id' => $section->id,
            'subject_id'       => $subject->id,
            'teacher_id'       => $user->id,
            'entry_type'       => 'homework',
            'title'            => 'Purge Homework',
            'content'          => 'Purge content',
            'assigned_date'    => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('institutes', ['id' => $instId]);
        $this->assertDatabaseHas('users', ['institute_id' => $instId]);
        $this->assertDatabaseHas('institute_classes', ['institute_id' => $instId]);
        $this->assertDatabaseHas('daily_diaries', ['institute_id' => $instId]);

        // Trigger institute force-delete (bypasses SoftDeletes)
        $tempInst->forceDelete();

        // Verify cascading wipe — dependent data must all be gone
        $this->assertDatabaseMissing('institutes', ['id' => $instId]);
        $this->assertEquals(0, User::withoutGlobalScopes()->where('institute_id', $instId)->count());
        $this->assertEquals(0, InstituteClass::withoutGlobalScopes()->where('institute_id', $instId)->count());
        $this->assertEquals(0, DailyDiary::withoutGlobalScopes()->where('institute_id', $instId)->count());
    }

    /**
     * Test 3: Unique Compound Constraint on student_subject_enrollments.
     * Attempting to insert a duplicate enrollment for the same student and subject throws an exception.
     */
    public function test_unique_compound_constraint_on_student_subject_enrollments(): void
    {
        $institute = Institute::withoutGlobalScopes()->where('slug', 'apex-college')->first();
        $studentUser = User::withoutGlobalScopes()
            ->where('institute_id', $institute->id)
            ->where('role', User::ROLE_STUDENT)
            ->first();

        // Get a subject via the institute's classes
        $classIds = InstituteClass::withoutGlobalScopes()
            ->where('institute_id', $institute->id)->pluck('id');
        $subject = Subject::withoutGlobalScopes()
            ->whereIn('institute_class_id', $classIds)
            ->first();

        // Get an existing section via the institute class
        $section = ClassSection::whereIn('institute_class_id', $classIds)->first();

        // Remove any prior enrollment to guarantee uniqueness test is clean
        StudentSubjectEnrollment::withoutGlobalScopes()->where([
            'student_id' => $studentUser->id,
            'subject_id' => $subject->id,
        ])->delete();

        // First insert succeeds
        StudentSubjectEnrollment::create([
            'institute_id'      => $institute->id,
            'student_id'        => $studentUser->id,
            'class_section_id'  => $section->id,
            'subject_id'        => $subject->id,
            'enrollment_status' => 'active',
        ]);

        // Second duplicate insert must fail with Unique constraint violation
        $this->expectException(QueryException::class);

        StudentSubjectEnrollment::create([
            'institute_id'      => $institute->id,
            'student_id'        => $studentUser->id,
            'class_section_id'  => $section->id,
            'subject_id'        => $subject->id,
            'enrollment_status' => 'active',
        ]);
    }

    /**
     * Test 4: Unique Compound Constraint on class_subjects.
     * Attempting to add the same subject twice to a class pool throws an exception.
     */
    public function test_unique_compound_constraint_on_class_subjects(): void
    {
        $institute = Institute::withoutGlobalScopes()->where('slug', 'apex-college')->first();
        $class = InstituteClass::withoutGlobalScopes()->where('institute_id', $institute->id)->first();

        // Get a subject belonging to this institute class
        $subject = Subject::withoutGlobalScopes()
            ->where('institute_class_id', $class->id)
            ->first();

        // Clear any existing class_subjects for this pair
        ClassSubject::withoutGlobalScopes()->where([
            'class_id'   => $class->id,
            'subject_id' => $subject->id,
        ])->delete();

        // Insert first
        ClassSubject::create([
            'institute_id' => $institute->id,
            'class_id'     => $class->id,
            'subject_id'   => $subject->id,
            'subject_type' => 'compulsory',
        ]);

        // Second duplicate insert must fail
        $this->expectException(QueryException::class);

        ClassSubject::create([
            'institute_id' => $institute->id,
            'class_id'     => $class->id,
            'subject_id'   => $subject->id,
            'subject_type' => 'compulsory',
        ]);
    }

    /**
     * Test 5: DailyDiary auto-calculated expires_at.
     * Asserts that when a DailyDiary is created, expires_at is automatically assigned_date + 14 days.
     */
    public function test_daily_diary_expires_at_is_auto_calculated_to_fourteen_days(): void
    {
        $institute = Institute::withoutGlobalScopes()->where('slug', 'apex-college')->first();

        // Resolve section via institute_class_id (class_sections has no institute_id column)
        $classIds = InstituteClass::withoutGlobalScopes()
            ->where('institute_id', $institute->id)->pluck('id');
        $section = ClassSection::whereIn('institute_class_id', $classIds)->first();

        $subject = Subject::withoutGlobalScopes()
            ->whereIn('institute_class_id', $classIds)
            ->first();

        $teacher = User::withoutGlobalScopes()
            ->where('institute_id', $institute->id)
            ->where('role', User::ROLE_TEACHER)
            ->first();

        $assignedDate = '2026-09-12';

        $diary = DailyDiary::create([
            'institute_id'     => $institute->id,
            'class_section_id' => $section->id,
            'subject_id'       => $subject->id,
            'teacher_id'       => $teacher->id,
            'entry_type'       => 'announcement',
            'title'            => 'Automated Expiry Verification Diary',
            'content'          => 'Checking 14-day expiry calculation window',
            'assigned_date'    => $assignedDate,
        ]);

        $expectedExpiry = Carbon::parse($assignedDate)->addDays(14)->toDateString();

        $this->assertNotNull($diary->expires_at);
        $this->assertEquals($expectedExpiry, Carbon::parse($diary->expires_at)->toDateString());
    }
}
