<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\Assessment;
use App\Models\ClassSection;
use App\Models\Institute;
use App\Models\InstituteClass;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\PrincipalOperationNotification;
use App\Services\PrincipalNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PrincipalOperationNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Institute $institute;
    protected User $principal;
    protected AcademicTerm $academicTerm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institute = Institute::create([
            'name' => 'Apex Grammar School',
            'code' => 'APX-01',
            'email' => 'apex@uplyft.test',
            'phone' => '+92 300 1234567',
            'address' => 'Main Campus',
            'is_active' => true,
        ]);

        $this->principal = User::create([
            'institute_id' => $this->institute->id,
            'name' => 'Principal Harrison',
            'email' => 'principal@apex.test',
            'password' => bcrypt('password'),
            'role' => User::ROLE_PRINCIPAL,
            'is_primary_principal' => true,
        ]);

        $this->academicTerm = AcademicTerm::create([
            'institute_id' => $this->institute->id,
            'name' => 'Fall 2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);
    }

    public function test_principal_receives_notification_and_broadcast_when_fee_is_paid(): void
    {
        Notification::fake();

        $student = Student::create([
            'institute_id' => $this->institute->id,
            'first_name' => 'Ali',
            'last_name' => 'Raza',
            'roll_number' => 'STD-2026-0001',
            'email' => 'ali@student.test',
            'guardian_tax_status' => 'non_filer',
            'base_fee' => 30000,
        ]);

        $invoice = Invoice::create([
            'institute_id' => $this->institute->id,
            'student_id' => $student->id,
            'title' => 'Monthly Tuition Fee',
            'fee_month' => 'September 2026',
            'amount_pkr' => 30000,
            'due_date' => now()->addDays(7),
            'status' => 'unpaid',
        ]);

        $accountant = User::create([
            'institute_id' => $this->institute->id,
            'name' => 'John Accountant',
            'email' => 'accountant@apex.test',
            'password' => bcrypt('password'),
            'role' => User::ROLE_TEACHER,
            'staff_role' => 'accountant',
        ]);

        PrincipalNotificationService::notifyFeePaid($invoice, $accountant);

        Notification::assertSentTo(
            $this->principal,
            PrincipalOperationNotification::class,
            function (PrincipalOperationNotification $notification) use ($invoice) {
                return $notification->category === 'finance'
                    && str_contains($notification->message, "Invoice #INV-{$invoice->id}")
                    && $notification->instituteId === $this->institute->id;
            }
        );
    }

    public function test_principal_receives_notification_when_student_is_registered(): void
    {
        Notification::fake();

        $student = Student::create([
            'institute_id' => $this->institute->id,
            'first_name' => 'Fatima',
            'last_name' => 'Zahra',
            'roll_number' => 'STD-2026-0002',
            'email' => 'fatima@student.test',
            'guardian_tax_status' => 'filer',
            'base_fee' => 25000,
        ]);

        PrincipalNotificationService::notifyStudentRegistered($student, $this->principal);

        Notification::assertSentTo(
            $this->principal,
            PrincipalOperationNotification::class,
            function (PrincipalOperationNotification $notification) use ($student) {
                return $notification->category === 'student'
                    && str_contains($notification->message, $student->full_name);
            }
        );
    }

    public function test_principal_receives_notification_when_teacher_marks_attendance(): void
    {
        Notification::fake();

        $class = InstituteClass::create([
            'institute_id' => $this->institute->id,
            'academic_term_id' => $this->academicTerm->id,
            'name' => 'Grade 10',
            'custom_name' => 'Grade 10',
        ]);

        $section = ClassSection::create([
            'institute_class_id' => $class->id,
            'section_name' => 'Section A',
            'capacity' => 40,
        ]);

        $teacherUser = User::create([
            'institute_id' => $this->institute->id,
            'name' => 'Sarah Jenkins',
            'email' => 'sarah@apex.test',
            'password' => bcrypt('password'),
            'role' => User::ROLE_TEACHER,
            'staff_role' => 'teacher',
        ]);

        PrincipalNotificationService::notifyAttendanceMarked(
            $this->institute->id,
            $section,
            25,
            now()->format('Y-m-d'),
            $teacherUser
        );

        Notification::assertSentTo(
            $this->principal,
            PrincipalOperationNotification::class,
            function (PrincipalOperationNotification $notification) {
                return $notification->category === 'attendance'
                    && str_contains($notification->message, 'Sarah Jenkins');
            }
        );
    }

    public function test_principal_receives_notification_when_teacher_submits_marks(): void
    {
        Notification::fake();

        $class = InstituteClass::create([
            'institute_id' => $this->institute->id,
            'academic_term_id' => $this->academicTerm->id,
            'name' => 'Grade 9',
            'custom_name' => 'Grade 9',
        ]);

        $section = ClassSection::create([
            'institute_class_id' => $class->id,
            'section_name' => 'Section B',
            'capacity' => 35,
        ]);

        $subject = Subject::create([
            'institute_class_id' => $class->id,
            'subject_name' => 'Physics',
            'subject_code' => 'PHY-101',
        ]);

        $teacherUser = User::create([
            'institute_id' => $this->institute->id,
            'name' => 'Dr. Usman',
            'email' => 'usman@apex.test',
            'password' => bcrypt('password'),
            'role' => User::ROLE_TEACHER,
            'staff_role' => 'teacher',
        ]);

        $assessment = Assessment::create([
            'subject_id' => $subject->id,
            'academic_term_id' => $this->academicTerm->id,
            'class_section_id' => $section->id,
            'creator_id' => $teacherUser->id,
            'title' => 'Midterm Physics Exam',
            'total_marks' => 100,
            'status' => 'draft',
        ]);

        PrincipalNotificationService::notifyMarksSubmitted($assessment, $teacherUser);

        Notification::assertSentTo(
            $this->principal,
            PrincipalOperationNotification::class,
            function (PrincipalOperationNotification $notification) {
                return $notification->category === 'exam'
                    && str_contains($notification->message, 'Midterm Physics Exam');
            }
        );
    }

    public function test_principal_notifications_feed_api_and_mark_read(): void
    {
        // Dispatch real notification into DB
        $this->principal->notify(new PrincipalOperationNotification(
            instituteId: $this->institute->id,
            title: 'Fee Marked as Paid',
            message: 'Invoice #INV-100 marked as Paid.',
            category: 'finance',
            icon: 'receipt',
            color: 'emerald'
        ));

        $this->assertEquals(1, $this->principal->unreadNotifications()->count());

        $response = $this->actingAs($this->principal)->getJson(route('principal.notifications.feed'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 1,
            ]);

        $notificationId = $response->json('notifications.0.id');

        // Mark single as read
        $readResponse = $this->actingAs($this->principal)->postJson(route('principal.notifications.mark-read', ['id' => $notificationId]));
        $readResponse->assertStatus(200)->assertJson(['success' => true, 'unread_count' => 0]);

        // Mark all as read endpoint
        $allReadResponse = $this->actingAs($this->principal)->postJson(route('principal.notifications.mark-all-read'));
        $allReadResponse->assertStatus(200)->assertJson(['success' => true, 'unread_count' => 0]);
    }
}
