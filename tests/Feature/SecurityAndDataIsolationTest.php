<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\ClassSection;
use App\Models\Institute;
use App\Models\InstituteClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityAndDataIsolationTest extends TestCase
{
    protected Institute $tenant1;
    protected Institute $tenant2;

    // ─── Seeded identifiers (confirmed via tinker) ───────────────────────────
    // Tenant 1: Apex College
    //   Principal: principal@apex.edu.pk / EMP-01
    //   Teacher:   noman.bio@apex.edu.pk  / EMP-04
    //   Students:  ahmed.k@apex.local (paid), mustafa.k@apex.local (unpaid)
    //   Sections:  "Section A" (id:1, class_id:1), "Foundation Batch A" (id:2, class_id:2)
    //   Subjects:  "FA1" = id 9 (ACCA section), code: FA1
    // Tenant 2: Crescent Model School
    //   Teacher:   rashid.teacher@crescent.edu.pk
    //   Principal: EMP-01

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant1 = Institute::withoutGlobalScopes()->where('slug', 'apex-college')->firstOrFail();
        $this->tenant2 = Institute::withoutGlobalScopes()->where('slug', 'crescent-model')->firstOrFail();
    }

    /**
     * Test 1: Cross-Tenant Data Leakage Prevention.
     *  - Direct DB query as Tenant 1 context yields 0 records belonging to Tenant 2.
     *  - Calling /principal/settings/staff/{tenant2_staff_id}/payroll returns 403.
     *  - Calling DELETE /principal/datesheets/{tenant2_assessment_id} returns 403.
     */
    public function test_cross_tenant_data_leakage_prevention(): void
    {
        $tenant1Principal = User::withoutGlobalScopes()
            ->where('institute_id', $this->tenant1->id)
            ->where('role', 'principal')
            ->firstOrFail();

        $tenant2Teacher = User::withoutGlobalScopes()
            ->where('institute_id', $this->tenant2->id)
            ->where('role', 'teacher')
            ->firstOrFail();

        $tenant2Assessment = Assessment::withoutGlobalScopes()
            ->where('title', 'Grade 9 Matric Mathematics Final Exam')
            ->firstOrFail();

        // 1. Cross-tenant isolation: Students for Tenant 1 must have institute_id = tenant1.id
        //    The global scope on Student ensures this at model level.
        //    We simulate: set the auth user and then count students the model would return.
        $this->actingAs($tenant1Principal, 'web');

        // All Students visible to Tenant 1 model (with global scope active) must belong to tenant 1
        $studentIds = \App\Models\Student::pluck('institute_id')->unique()->toArray();
        foreach ($studentIds as $instId) {
            $this->assertEquals(
                $this->tenant1->id,
                $instId,
                "Global scope breach: Tenant 2 student data returned in Tenant 1 context!"
            );
        }

        // 2. Cross-tenant Staff Payroll IDOR prevention
        $payrollResponse = $this->actingAs($tenant1Principal, 'web')
            ->getJson("/principal/settings/staff/{$tenant2Teacher->id}/payroll");

        $payrollResponse->assertStatus(403);

        // 3. Cross-tenant Datesheet Deletion IDOR prevention
        $datesheetResponse = $this->actingAs($tenant1Principal, 'web')
            ->deleteJson("/principal/datesheets/{$tenant2Assessment->id}");

        $datesheetResponse->assertStatus(403);
    }

    /**
     * Test 2: Multi-Tenant Identifier Collision Auth Boundary.
     * Both tenants have EMP-01 (Principal) but with different generated passwords
     * and institute scopes. Using the wrong tenant's password against Tenant 1's
     * login context MUST fail. Using Tenant 1's own password MUST succeed.
     */
    public function test_identifier_collision_multi_tenant_auth_boundary(): void
    {
        // Generate per-run credentials so no passwords are committed to the repo.
        $tenant1Password = Str::random(16);
        $tenant2Password = Str::random(16);

        $tenant1Principal = User::withoutGlobalScopes()
            ->where('institute_id', $this->tenant1->id)
            ->where('identifier', 'EMP-01')
            ->where('role', 'principal')
            ->firstOrFail();

        $tenant2Principal = User::withoutGlobalScopes()
            ->where('institute_id', $this->tenant2->id)
            ->where('identifier', 'EMP-01')
            ->where('role', 'principal')
            ->firstOrFail();

        $tenant1Principal->update(['password' => Hash::make($tenant1Password)]);
        $tenant2Principal->update(['password' => Hash::make($tenant2Password)]);

        // Attempt login using Tenant 2's password against Tenant 1
        $failedResponse = $this->post('/login', [
            'credential'   => 'EMP-01',
            'password'     => $tenant2Password,
            'institute_id' => $this->tenant1->id,
        ], [
            'X-Institute-Id' => (string) $this->tenant1->id,
        ]);

        $failedResponse->assertSessionHasErrors(['credential']);
        $this->assertGuest();

        // Attempt login using correct Tenant 1 password against Tenant 1
        $successResponse = $this->post('/login', [
            'credential'   => 'EMP-01',
            'password'     => $tenant1Password,
            'institute_id' => $this->tenant1->id,
        ], [
            'X-Institute-Id' => (string) $this->tenant1->id,
        ]);

        $this->assertAuthenticated();
        $this->assertEquals($this->tenant1->id, Auth::user()->institute_id);
        $this->assertEquals($tenant1Principal->email, Auth::user()->email);
    }

    /**
     * Test 3: Unpaid Financial Gate.
     * A student with pending/unpaid status is blocked by EnsureStudentFeePaid middleware.
     * A paid student can access LMS routes unrestricted.
     */
    public function test_unpaid_financial_gate_blocks_pending_payment_student(): void
    {
        // Seeded: STU-111 / mustafa.k@apex.local is the "unpaid" student
        $unpaidStudentUser = User::withoutGlobalScopes()
            ->where('institute_id', $this->tenant1->id)
            ->where('email', 'mustafa.k@apex.local')
            ->firstOrFail();

        // Seeded: STU-101 / ahmed.k@apex.local is the "paid" student
        $paidStudentUser = User::withoutGlobalScopes()
            ->where('institute_id', $this->tenant1->id)
            ->where('email', 'ahmed.k@apex.local')
            ->firstOrFail();

        // JSON requests for unpaid student return 403
        $this->actingAs($unpaidStudentUser, 'web')
            ->getJson('/student/subjects')
            ->assertStatus(403)
            ->assertJsonPath('success', false);

        $this->actingAs($unpaidStudentUser, 'web')
            ->getJson('/student/materials/1')
            ->assertStatus(403)
            ->assertJsonPath('success', false);

        $this->actingAs($unpaidStudentUser, 'web')
            ->postJson('/student/rag/chat', ['message' => 'Explain mitosis'])
            ->assertStatus(403)
            ->assertJsonPath('success', false);

        // Standard web request redirects to student.fees page
        $this->actingAs($unpaidStudentUser, 'web')
            ->get('/student/subjects')
            ->assertRedirect(route('student.fees'));

        // Paid student passes the gate
        $paidResponse = $this->actingAs($paidStudentUser, 'web')
            ->get('/student/subjects');

        $paidResponse->assertStatus(200);
    }

    /**
     * Test 4: Privilege Escalation & IDOR on Teacher Actions.
     *  - Teacher attempting to access /principal/settings is denied (403).
     *  - Teacher (Noman Bio, assigned to Section A / Biology) attempts to POST diary
     *    for ACCA "Foundation Batch A" + FA1 subject (an unauthorized section/subject).
     */
    public function test_privilege_escalation_and_idor_on_teacher_actions(): void
    {
        // EMP-04 Noman teaches Biology/Chemistry in Class 11 Section A
        $teacherUser = User::withoutGlobalScopes()
            ->where('institute_id', $this->tenant1->id)
            ->where('email', 'noman.bio@apex.edu.pk')
            ->firstOrFail();

        // 1. Role enforcement: Teacher accessing Principal settings gets 403
        $this->actingAs($teacherUser, 'web')
            ->get('/principal/settings')
            ->assertStatus(403);

        // 2. IDOR enforcement: Teacher Noman (assigned to Section A) attempts to post diary
        // for ACCA "Foundation Batch A" (section_id:2) + FA1 subject (id:9).
        // Both belong to Apex ACCA class assigned to Bilal (EMP-07), not Noman.
        $sectionAcca = ClassSection::where('section_name', 'Foundation Batch A')->firstOrFail();
        $subFA1 = Subject::withoutGlobalScopes()->where('subject_code', 'FA1')->firstOrFail();

        $idorPayload = [
            'class_section_id' => $sectionAcca->id,
            'subject_id'       => $subFA1->id,
            'title'            => 'Malicious Teacher Diary Insertion',
            'content'          => 'Injecting diary to unauthorized class section',
            'entry_type'       => 'homework',
            'assigned_date'    => now()->toDateString(),
        ];

        $response = $this->actingAs($teacherUser, 'web')
            ->postJson('/teacher/diary', $idorPayload);

        $response->assertStatus(403);
    }

    /**
     * Test 5: View-Only vs. Edit RBAC Permissions.
     * Admission Counselor (EMP-03, counselor@apex.edu.pk) can view admissions (200),
     * but cannot store or update (403).
     */
    public function test_view_only_vs_edit_rbac_permissions(): void
    {
        $counselorUser = User::withoutGlobalScopes()
            ->where('institute_id', $this->tenant1->id)
            ->where('email', 'counselor@apex.edu.pk')
            ->firstOrFail();

        // View admissions roster -> Allowed (200)
        $this->actingAs($counselorUser, 'web')
            ->get('/admissions')
            ->assertStatus(200);

        // Store new admission -> Forbidden (403)
        $this->actingAs($counselorUser, 'web')
            ->post('/admissions', [
                'first_name' => 'Unauthorized',
                'last_name'  => 'Attempt',
            ])
            ->assertStatus(403);

        // Update existing admission -> Forbidden (403)
        $this->actingAs($counselorUser, 'web')
            ->put('/admissions/1', [
                'first_name' => 'Tampered',
            ])
            ->assertStatus(403);
    }
}
