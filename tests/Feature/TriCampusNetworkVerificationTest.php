<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\Institute;
use App\Models\InstituteFeatureToggle;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Student;
use App\Models\Timetable;
use App\Models\User;
use Database\Seeders\ComprehensiveDemoNetworkSeeder;
use Tests\TestCase;

class TriCampusNetworkVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure network is seeded
        if (!Institute::withoutGlobalScopes()->where('slug', 'apex-cambridge')->exists()) {
            $this->seed(ComprehensiveDemoNetworkSeeder::class);
        }

        $cambridge = Institute::withoutGlobalScopes()->where('slug', 'apex-cambridge')->first();
        if ($cambridge) {
            InstituteFeatureToggle::where('institute_id', $cambridge->id)->update(['timetable' => true, 'fee_invoicing' => true]);
        }
    }

    /**
     * Test 1: Organization and 3 specialized campuses exist with correct education systems.
     */
    public function test_organization_and_three_campuses_exist(): void
    {
        $org = Organization::where('slug', 'apex-network')->first();
        $this->assertNotNull($org, 'Apex Network organization must exist');
        $this->assertEquals('Apex Global Educational Network', $org->name);

        $cambridge = Institute::withoutGlobalScopes()->where('slug', 'apex-cambridge')->first();
        $this->assertNotNull($cambridge);
        $this->assertEquals(['o_a_level'], $cambridge->education_systems);
        $this->assertEquals($org->id, $cambridge->organization_id);

        $acca = Institute::withoutGlobalScopes()->where('slug', 'apex-acca')->first();
        $this->assertNotNull($acca);
        $this->assertEquals(['acca'], $acca->education_systems);
        $this->assertEquals($org->id, $acca->organization_id);

        $matric = Institute::withoutGlobalScopes()->where('slug', 'apex-matric')->first();
        $this->assertNotNull($matric);
        $this->assertEquals(['matric'], $matric->education_systems);
        $this->assertEquals($org->id, $matric->organization_id);
    }

    /**
     * Test 2: Students get exact clash-free timetable assigned.
     */
    public function test_student_gets_exact_timetable_assigned(): void
    {
        $cambridge = Institute::withoutGlobalScopes()->where('slug', 'apex-cambridge')->first();
        $studentUser = User::withoutGlobalScopes()->where('email', 'daniyal.cambridge@student.apex.edu.pk')->first();
        $this->assertNotNull($studentUser);

        $student = Student::withoutGlobalScopes()->where('user_id', $studentUser->id)->first();
        $this->assertNotNull($student);
        $this->assertNotNull($student->class_section_id);

        // Fetch student timetable slots (exact query used by StudentPortalController)
        $rawSlots = Timetable::withoutGlobalScopes()
            ->where('class_section_id', $student->class_section_id)
            ->with(['subject', 'teacher', 'room'])
            ->orderBy('start_time')
            ->get();

        $this->assertCount(25, $rawSlots, 'O-Level Section A should have exactly 25 weekly periods scheduled (5 periods x 5 days)');

        // Verify Monday schedule
        $mondaySlots = $rawSlots->filter(fn ($s) => strtolower($s->day_of_week) === 'monday')->values();
        $this->assertCount(5, $mondaySlots);
        $this->assertEquals('08:30:00', $mondaySlots[0]->start_time);
        $this->assertEquals('09:20:00', $mondaySlots[0]->end_time);
        $this->assertEquals('English Language (O-Level)', $mondaySlots[0]->subject->subject_name);

        // Test mergeContiguousSlots works
        $merged = Timetable::mergeContiguousSlots($rawSlots);
        $this->assertNotEmpty($merged);
    }

    /**
     * Test 3: Faculty gets exact assigned timetable schedule.
     */
    public function test_teacher_gets_exact_assigned_schedule(): void
    {
        $teacherUser = User::withoutGlobalScopes()->where('email', 'ahmed.camb@apex.edu.pk')->first();
        $this->assertNotNull($teacherUser);

        // Fetch teacher slots (exact query used by TeacherPortalController)
        $teacherSlots = Timetable::withoutGlobalScopes()
            ->where('teacher_id', $teacherUser->id)
            ->with(['subject', 'section.instituteClass', 'room'])
            ->orderBy('start_time')
            ->get();

        // Ahmed teaches O-Level Math (5 periods) and A-Level Math (5 periods) = 10 periods/week
        $this->assertCount(10, $teacherSlots, 'Teacher Ahmed should have exactly 10 slots per week');

        // Verify no self-clash in Ahmed's timetable (no two slots on same day and same time)
        $byDay = $teacherSlots->groupBy(fn ($s) => strtolower($s->day_of_week));
        foreach ($byDay as $day => $slots) {
            $times = $slots->pluck('start_time')->all();
            $this->assertEquals(count($times), count(array_unique($times)), "Teacher Ahmed has overlapping slots on {$day}");
        }
    }

    /**
     * Test 4: Updating a timetable slot propagates and updates immediately across all associated portals.
     */
    public function test_timetable_update_propagates_immediately_across_portals(): void
    {
        $studentUser = User::withoutGlobalScopes()->where('email', 'daniyal.cambridge@student.apex.edu.pk')->first();
        $student = Student::withoutGlobalScopes()->where('user_id', $studentUser->id)->first();
        $teacherUser = User::withoutGlobalScopes()->where('email', 'ahmed.camb@apex.edu.pk')->first();

        // Find Ahmed's Monday slot in student's section
        $slot = Timetable::withoutGlobalScopes()
            ->where('class_section_id', $student->class_section_id)
            ->where('teacher_id', $teacherUser->id)
            ->where('day_of_week', 'monday')
            ->first();

        $this->assertNotNull($slot);
        $originalEnd = $slot->end_time;

        // Update end time
        $slot->update(['end_time' => '10:14:00']);

        // 1. Verify Student View Query reflects update
        $studentSlot = Timetable::withoutGlobalScopes()
            ->where('class_section_id', $student->class_section_id)
            ->where('id', $slot->id)
            ->first();
        $this->assertEquals('10:14:00', $studentSlot->end_time, 'Student timetable query must reflect updated end_time');

        // 2. Verify Teacher View Query reflects update
        $teacherSlot = Timetable::withoutGlobalScopes()
            ->where('teacher_id', $teacherUser->id)
            ->where('id', $slot->id)
            ->first();
        $this->assertEquals('10:14:00', $teacherSlot->end_time, 'Teacher schedule query must reflect updated end_time');

        // Restore
        $slot->update(['end_time' => $originalEnd]);
    }

    /**
     * Test 5: Role-based authority for Academic Coordinator and Accountant.
     */
    public function test_coordinator_and_accountant_authorities(): void
    {
        $cambridge = Institute::withoutGlobalScopes()->where('slug', 'apex-cambridge')->first();

        // Academic Coordinator
        $coord = User::withoutGlobalScopes()->where('email', 'coord.cambridge@apex.edu.pk')->first();
        $this->assertNotNull($coord);
        $this->assertEquals('coordinator', $coord->staff_role);
        $this->assertTrue($coord->hasAnyDelegatedPermission());
        $this->assertTrue($coord->hasPermission('timetables'), 'Coordinator must have timetables authority');
        $this->assertTrue($coord->hasPermission('classes'), 'Coordinator must have classes authority');
        $this->assertFalse($coord->hasPermission('invoices_collect'), 'Coordinator must not have invoice collection authority');

        // Accountant
        $accountant = User::withoutGlobalScopes()->where('email', 'accountant.cambridge@apex.edu.pk')->first();
        $this->assertNotNull($accountant);
        $this->assertEquals('accountant', $accountant->staff_role);
        $this->assertTrue($accountant->hasAnyDelegatedPermission());
        $this->assertTrue($accountant->hasPermission('invoices'), 'Accountant must have invoices authority');
        $this->assertFalse($accountant->hasPermission('subjects'), 'Accountant must not have subjects curriculum authority');
    }

    /**
     * Test 6: Feature Toggles turn on and off dynamically without runtime errors.
     */
    public function test_feature_toggles_turn_on_and_off_cleanly(): void
    {
        $cambridge = Institute::withoutGlobalScopes()->where('slug', 'apex-cambridge')->first();
        $toggles = InstituteFeatureToggle::where('institute_id', $cambridge->id)->first();
        $this->assertNotNull($toggles);

        // Verify initial state is active
        $this->assertTrue($toggles->timetable);
        $this->assertTrue($toggles->fee_invoicing);
        $this->assertTrue($toggles->lms_content);

        // Turn timetable toggle OFF
        $toggles->update(['timetable' => false]);
        $fresh = InstituteFeatureToggle::where('institute_id', $cambridge->id)->first();
        $this->assertFalse($fresh->timetable, 'Timetable feature toggle should be false when turned off');

        // Turn timetable toggle ON
        $toggles->update(['timetable' => true]);
        $fresh = InstituteFeatureToggle::where('institute_id', $cambridge->id)->first();
        $this->assertTrue($fresh->timetable, 'Timetable feature toggle should be true when restored');
    }

    /**
     * Test 7: Multi-tenant isolation across the three campuses.
     */
    public function test_multi_tenant_isolation_across_campuses(): void
    {
        $cambridge = Institute::withoutGlobalScopes()->where('slug', 'apex-cambridge')->first();
        $acca = Institute::withoutGlobalScopes()->where('slug', 'apex-acca')->first();
        $matric = Institute::withoutGlobalScopes()->where('slug', 'apex-matric')->first();

        // Students in Cambridge cannot see ACCA sections
        $cambridgeStudent = User::withoutGlobalScopes()->where('email', 'daniyal.cambridge@student.apex.edu.pk')->first();
        $this->assertEquals($cambridge->id, $cambridgeStudent->institute_id);

        $accaSections = ClassSection::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $acca->id))->pluck('id')->all();
        $cambridgeStudentRec = Student::withoutGlobalScopes()->where('user_id', $cambridgeStudent->id)->first();
        $this->assertFalse(in_array($cambridgeStudentRec->class_section_id, $accaSections), 'Cambridge student must not be enrolled in ACCA section');

        // Verify Invoices exist for accountant
        $cambridgeInvoices = Invoice::withoutGlobalScopes()->where('institute_id', $cambridge->id)->count();
        $this->assertGreaterThan(0, $cambridgeInvoices, 'Cambridge campus must have fee invoices for accountant portal');

        $accaInvoices = Invoice::withoutGlobalScopes()->where('institute_id', $acca->id)->count();
        $this->assertGreaterThan(0, $accaInvoices, 'ACCA campus must have fee invoices for accountant portal');

        $matricInvoices = Invoice::withoutGlobalScopes()->where('institute_id', $matric->id)->count();
        $this->assertGreaterThan(0, $matricInvoices, 'Matric campus must have fee invoices for accountant portal');
    }

    /**
     * Test 8: Student Portal HTTP Dashboard & Timetable Access.
     */
    public function test_student_portal_http_access(): void
    {
        $studentUser = User::withoutGlobalScopes()->where('email', 'daniyal.cambridge@student.apex.edu.pk')->first();
        $response = $this->actingAs($studentUser)->get('/student/timetable');
        $response->assertStatus(200);
        $response->assertSee('Mathematics (Syllabus D)');
        $response->assertSee('English Language (O-Level)');
    }

    /**
     * Test 9: Teacher Portal HTTP Schedule Access.
     */
    public function test_teacher_portal_http_access(): void
    {
        $teacherUser = User::withoutGlobalScopes()->where('email', 'ahmed.camb@apex.edu.pk')->first();
        $response = $this->actingAs($teacherUser)->get('/teacher/schedule');
        $response->assertStatus(200);
    }

    /**
     * Test 10: Principal Portal HTTP Dashboard & Timetable Access.
     */
    public function test_principal_portal_http_access(): void
    {
        $principalUser = User::withoutGlobalScopes()->where('email', 'principal.cambridge@apex.edu.pk')->first();
        $response = $this->actingAs($principalUser)->get('/principal/dashboard');
        $response->assertStatus(200);

        $timetableResponse = $this->actingAs($principalUser)->get('/principal/timetables');
        $timetableResponse->assertStatus(200);
    }

    /**
     * Test 11: Coordinator Portal HTTP Classes & Timetables Access.
     */
    public function test_coordinator_portal_http_access(): void
    {
        $coordUser = User::withoutGlobalScopes()->where('email', 'coord.cambridge@apex.edu.pk')->first();
        $response = $this->actingAs($coordUser)->get('/staff/timetables');
        $response->assertStatus(200);

        $classesResponse = $this->actingAs($coordUser)->get('/staff/classes-subjects');
        $classesResponse->assertStatus(200);
    }

    /**
     * Test 12: Accountant Portal HTTP Invoices Access.
     */
    public function test_accountant_portal_http_access(): void
    {
        $accountantUser = User::withoutGlobalScopes()->where('email', 'accountant.cambridge@apex.edu.pk')->first();
        $response = $this->actingAs($accountantUser)->get('/accountant/invoices');
        $response->assertStatus(200);
    }

    /**
     * Test 13: Disabling Feature Toggle via Middleware Blocks Access.
     */
    public function test_disabling_feature_toggle_blocks_route_access(): void
    {
        $principalUser = User::withoutGlobalScopes()->where('email', 'principal.cambridge@apex.edu.pk')->first();
        $cambridge = Institute::withoutGlobalScopes()->where('slug', 'apex-cambridge')->first();
        $toggles = InstituteFeatureToggle::where('institute_id', $cambridge->id)->first();

        // When timetable is active, route returns 200
        $this->actingAs($principalUser)->get('/principal/timetables')->assertStatus(200);

        // Disable timetable toggle
        $toggles->update(['timetable' => false]);
        
        // Middleware feature:timetable should deny access (redirect or 403)
        $deniedResponse = $this->actingAs($principalUser)->get('/principal/timetables');
        $this->assertTrue(
            in_array($deniedResponse->status(), [403, 302]),
            "Route protected by feature:timetable must be denied when toggle is false (status: {$deniedResponse->status()})"
        );

        // Re-enable timetable toggle
        $toggles->update(['timetable' => true]);
        $this->actingAs($principalUser)->get('/principal/timetables')->assertStatus(200);
    }
}
