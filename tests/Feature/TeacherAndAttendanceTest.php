<?php

namespace Tests\Feature;

use App\Models\Institute;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherAndAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected Institute $institute;

    protected function setUp(): void
    {
        parent::setUp();

        // Create default tenant institute
        $this->institute = Institute::create([
            'name'      => 'Uplyft Testing Academy',
            'settings'  => [
                'base_admission_fee' => 15000,
                'filer_tax_rate'     => 0.04,
                'non_filer_tax_rate' => 0.12,
            ],
        ]);
    }

    /** @test */
    public function teacher_can_onboard_with_qualification_document(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('transcript.pdf', 1024, 'application/pdf');

        $response = $this->postJson('/api/teachers/onboarding', [
            'institute_id'        => $this->institute->id,
            'first_name'          => 'Sarah',
            'last_name'           => 'Connor',
            'email'               => 'sarah.connor@uplyft.edu',
            'phone'               => '+923009876543',
            'qualification'       => 'MSc Computer Science',
            'qualifications_file' => $file,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'id',
                         'first_name',
                         'last_name',
                         'email',
                         'qualifications_file_path',
                     ],
                 ]);

        $teacher = Teacher::where('email', 'sarah.connor@uplyft.edu')->first();
        $this->assertNotNull($teacher);
        $this->assertEquals($this->institute->id, $teacher->institute_id);

        // Verify file stored under isolated directory
        $expectedPath = "institutes/{$this->institute->id}/teachers/qualifications/" . $file->hashName();
        Storage::disk('public')->assertExists($expectedPath);
    }

    /** @test */
    public function teacher_onboarding_fails_if_file_exceeds_5mb(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('huge_transcript.pdf', 6000, 'application/pdf'); // > 5MB

        $response = $this->postJson('/api/teachers/onboarding', [
            'institute_id'        => $this->institute->id,
            'first_name'          => 'John',
            'last_name'           => 'Doe',
            'email'               => 'john.doe@uplyft.edu',
            'qualification'       => 'PhD Physics',
            'qualifications_file' => $file,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['qualifications_file']);
    }

    /** @test */
    public function can_fetch_class_roster_and_mark_bulk_attendance(): void
    {
        // 1. Create 2 students for the institute
        $student1 = Student::create([
            'institute_id'        => $this->institute->id,
            'first_name'          => 'Ali',
            'last_name'           => 'Khan',
            'email'               => 'ali.khan@example.com',
            'date_of_birth'       => '2005-05-15',
            'previous_marks'      => 88.50,
            'guardian_tax_status' => 'filer',
        ]);

        $student2 = Student::create([
            'institute_id'        => $this->institute->id,
            'first_name'          => 'Zainab',
            'last_name'           => 'Ahmed',
            'email'               => 'zainab.ahmed@example.com',
            'date_of_birth'       => '2006-03-20',
            'previous_marks'      => 92.00,
            'guardian_tax_status' => 'non-filer',
        ]);

        $academicTermId = 101; // Developer A domain term ID
        $date = '2026-08-11';

        // 2. Fetch class roster (initially unmarked)
        $rosterResponse = $this->getJson("/api/attendance/roster?academic_term_id={$academicTermId}&date={$date}");
        $rosterResponse->assertStatus(200)
                       ->assertJson([
                           'academic_term_id' => $academicTermId,
                           'date'             => $date,
                           'total_students'   => 2,
                       ]);

        // 3. Mark bulk attendance
        $markResponse = $this->postJson('/api/attendance', [
            'academic_term_id' => $academicTermId,
            'date'             => $date,
            'attendances'      => [
                ['student_id' => $student1->id, 'status' => 'present'],
                ['student_id' => $student2->id, 'status' => 'late'],
            ],
        ]);

        $markResponse->assertStatus(200)
                     ->assertJson([
                         'processed' => 2,
                     ]);

        // 4. Verify database state
        $this->assertDatabaseHas('attendances', [
            'institute_id'     => $this->institute->id,
            'academic_term_id' => $academicTermId,
            'student_id'       => $student1->id,
            'date'             => $date,
            'status'           => 'present',
        ]);

        $this->assertDatabaseHas('attendances', [
            'institute_id'     => $this->institute->id,
            'academic_term_id' => $academicTermId,
            'student_id'       => $student2->id,
            'date'             => $date,
            'status'           => 'late',
        ]);

        // 5. Update attendance status for student2 from 'late' to 'present' (upsert check)
        $updateResponse = $this->postJson('/api/attendance', [
            'academic_term_id' => $academicTermId,
            'date'             => $date,
            'attendances'      => [
                ['student_id' => $student2->id, 'status' => 'present'],
            ],
        ]);

        $updateResponse->assertStatus(200);

        // Verify student2 is now present and total count remains 2
        $this->assertEquals(2, Attendance::count());
        $this->assertDatabaseHas('attendances', [
            'student_id' => $student2->id,
            'status'     => 'present',
        ]);
    }
}
