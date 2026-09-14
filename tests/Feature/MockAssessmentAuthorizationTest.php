<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Institute;
use App\Models\User;
use Tests\TestCase;

class MockAssessmentAuthorizationTest extends TestCase
{
    protected ?Institute $apex = null;
    protected ?Institute $crescent = null;
    protected ?Assessment $assessment = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apex = Institute::withoutGlobalScopes()->where('slug', 'apex-college')->first();
        $this->crescent = Institute::withoutGlobalScopes()->where('slug', 'crescent-model')->first();
        $this->assessment = Assessment::find(5);
    }

    /**
     * Verify that an unauthenticated guest cannot access teacher mocks.
     */
    public function test_guest_is_redirected_from_teacher_mocks(): void
    {
        $response = $this->get('/teacher/mocks/5');
        $response->assertStatus(302);
    }

    /**
     * Verify that the Principal of Apex College can view Assessment 5 without 403.
     */
    public function test_principal_can_view_mock_assessment_details(): void
    {
        if (!$this->apex || !$this->assessment) {
            $this->markTestSkipped('Apex institute or Assessment 5 not found.');
        }

        $principal = User::where('institute_id', $this->apex->id)
            ->where('role', 'principal')
            ->firstOrFail();

        $response = $this->actingAs($principal)->get('/teacher/mocks/5');
        $response->assertStatus(200);
    }

    /**
     * Verify that the Principal of Apex College can download the PDF for Assessment 5 without 403.
     */
    public function test_principal_can_download_mock_assessment_pdf(): void
    {
        if (!$this->apex || !$this->assessment) {
            $this->markTestSkipped('Apex institute or Assessment 5 not found.');
        }

        $principal = User::where('institute_id', $this->apex->id)
            ->where('role', 'principal')
            ->firstOrFail();

        $response = $this->actingAs($principal)->get('/teacher/mocks/5/pdf?with_answers=1');
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type', ''), 'pdf'));
    }

    /**
     * Verify that the Principal of Apex College can view the printable Question Paper / Marking Scheme.
     */
    public function test_principal_can_view_printable_mock_assessment(): void
    {
        if (!$this->apex || !$this->assessment) {
            $this->markTestSkipped('Apex institute or Assessment 5 not found.');
        }

        $principal = User::where('institute_id', $this->apex->id)
            ->where('role', 'principal')
            ->firstOrFail();

        $response = $this->actingAs($principal)->get('/teacher/mocks/5/print');
        $response->assertStatus(200);
    }

    /**
     * Verify cross-tenant isolation: Principal of a different institute cannot access Assessment 5.
     */
    public function test_cross_tenant_principal_cannot_access_mock(): void
    {
        if (!$this->crescent || !$this->assessment) {
            $this->markTestSkipped('Crescent institute or Assessment 5 not found.');
        }

        $otherPrincipal = User::where('institute_id', $this->crescent->id)
            ->where('role', 'principal')
            ->first();

        if (!$otherPrincipal) {
            $this->markTestSkipped('Crescent principal not found.');
        }

        $response = $this->actingAs($otherPrincipal)->get('/teacher/mocks/5');
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404], true));
    }
}
