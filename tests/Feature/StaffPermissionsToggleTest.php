<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\Institute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffPermissionsToggleTest extends TestCase
{
    use RefreshDatabase;

    private function makeInstitute(): Institute
    {
        return Institute::create(['name' => 'Test Academy', 'slug' => 'test-academy-'.uniqid()]);
    }

    private function withActiveTerm(Institute $institute): AcademicTerm
    {
        return AcademicTerm::create([
            'institute_id' => $institute->id,
            'name' => 'Spring 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);
    }

    private function makePrincipal(mixed $institute = null): User
    {
        $institute ??= $this->makeInstitute();

        return User::create([
            'name' => 'Principal One',
            'email' => 'principal@test.academy',
            'password' => 'Secret123!',
            'role' => 'principal',
            'institute_id' => $institute->id,
        ]);
    }

    private function makeTeacher(Institute $institute): User
    {
        return User::create([
            'name' => 'Teacher Jane',
            'email' => 'teacher.jane@test.academy',
            'password' => 'Secret123!',
            'role' => 'teacher',
            'institute_id' => $institute->id,
        ]);
    }

    public function test_permission_toggle_grants_and_revokes_instantly(): void
    {
        $institute = $this->makeInstitute();
        $this->withActiveTerm($institute);
        $principal = $this->makePrincipal($institute);
        $teacher = $this->makeTeacher($institute);

        // Grant
        $this->actingAs($principal)
            ->post(route('principal.staff.toggle-permission', $teacher), [
                'permission_key' => 'directory',
                'state' => '1',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['success' => true, 'key' => 'directory', 'value' => true]);

        $this->assertTrue((bool) ($teacher->refresh()->permissions['directory'] ?? false));

        // Revoke
        $this->actingAs($principal)
            ->post(route('principal.staff.toggle-permission', $teacher), [
                'permission_key' => 'directory',
                'state' => '0',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['success' => true, 'key' => 'directory', 'value' => false]);

        $this->assertEmpty($teacher->refresh()->permissions);
    }

    public function test_delegation_toggle_grants_and_revokes_instantly(): void
    {
        $institute = $this->makeInstitute();
        $this->withActiveTerm($institute);
        $principal = $this->makePrincipal($institute);
        $teacher = $this->makeTeacher($institute);

        $teacher->update(['is_delegated_admin' => false]);

        // Grant
        $this->actingAs($principal)
            ->post(route('principal.staff.toggle-delegation', $teacher), [
                'state' => '1',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['success' => true, 'value' => true]);

        $this->assertTrue((bool) $teacher->refresh()->is_delegated_admin);

        // Revoke
        $this->actingAs($principal)
            ->post(route('principal.staff.toggle-delegation', $teacher), [
                'state' => '0',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['success' => true, 'value' => false]);

        $this->assertFalse((bool) $teacher->refresh()->is_delegated_admin);
    }

    public function test_staff_index_page_loads(): void
    {
        $institute = $this->makeInstitute();
        $this->withActiveTerm($institute);
        $principal = $this->makePrincipal($institute);

        $this->actingAs($principal)
            ->get(route('principal.staff.index'))
            ->assertOk()
            ->assertSee('Rights');
    }

    public function test_teacher_cannot_toggle_another_institutes_staff(): void
    {
        $instituteA = $this->makeInstitute();
        $this->withActiveTerm($instituteA);
        $instituteB = $this->makeInstitute();
        $principal = $this->makePrincipal($instituteA);
        $foreignTeacher = $this->makeTeacher($instituteB);

        $this->actingAs($principal)
            ->post(route('principal.staff.toggle-delegation', $foreignTeacher), [
                'state' => '1',
            ], ['Accept' => 'application/json'])
            ->assertForbidden();
    }
}
