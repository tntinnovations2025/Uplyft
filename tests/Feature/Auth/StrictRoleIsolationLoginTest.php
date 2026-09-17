<?php

namespace Tests\Feature\Auth;

use App\Models\Institute;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrictRoleIsolationLoginTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected Institute $institute;
    protected User $principal;
    protected User $faculty;
    protected User $student;
    protected User $globalAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create([
            'name' => 'Apex Educational Network',
            'is_active' => true,
        ]);

        $this->institute = Institute::create([
            'name' => 'Apex College',
            'organization_id' => $this->org->id,
            'is_active' => true,
        ]);

        $this->principal = User::create([
            'name' => 'Principal Tariq',
            'email' => 'principal@apex.edu.pk',
            'password' => bcrypt('Password123!'),
            'role' => User::ROLE_PRINCIPAL,
            'institute_id' => $this->institute->id,
        ]);

        $this->faculty = User::create([
            'name' => 'Teacher Noman',
            'email' => 'teacher@apex.edu.pk',
            'password' => bcrypt('Password123!'),
            'role' => User::ROLE_TEACHER,
            'institute_id' => $this->institute->id,
        ]);

        $this->student = User::create([
            'name' => 'Student Ahmed',
            'email' => 'student@apex.edu.pk',
            'password' => bcrypt('Password123!'),
            'role' => User::ROLE_STUDENT,
            'institute_id' => $this->institute->id,
        ]);

        $this->globalAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@uplyft.com',
            'password' => bcrypt('Password123!'),
            'role' => User::ROLE_GLOBAL_ADMIN,
        ]);
    }

    public function test_principal_can_login_via_principal_portal(): void
    {
        $response = $this->post('/principal/login', [
            'credential' => 'principal@apex.edu.pk',
            'password' => 'Password123!',
            'role' => 'principal',
        ]);

        $this->assertAuthenticatedAs($this->principal);
        $response->assertRedirect(route('principal.dashboard'));
    }

    public function test_principal_cannot_login_via_student_portal_and_sees_wrong_credentials(): void
    {
        $response = $this->post('/student/login', [
            'credential' => 'principal@apex.edu.pk',
            'password' => 'Password123!',
            'role' => 'student',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'credential' => 'Wrong credentials!',
        ]);
    }

    public function test_principal_cannot_login_via_faculty_portal_and_sees_wrong_credentials(): void
    {
        $response = $this->post('/faculty/login', [
            'credential' => 'principal@apex.edu.pk',
            'password' => 'Password123!',
            'role' => 'faculty',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'credential' => 'Wrong credentials!',
        ]);
    }

    public function test_student_can_login_via_student_portal(): void
    {
        $response = $this->post('/student/login', [
            'credential' => 'student@apex.edu.pk',
            'password' => 'Password123!',
            'role' => 'student',
        ]);

        $this->assertAuthenticatedAs($this->student);
        $response->assertRedirect(route('student.dashboard'));
    }

    public function test_student_cannot_login_via_principal_portal_and_sees_wrong_credentials(): void
    {
        $response = $this->post('/principal/login', [
            'credential' => 'student@apex.edu.pk',
            'password' => 'Password123!',
            'role' => 'principal',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'credential' => 'Wrong credentials!',
        ]);
    }

    public function test_student_cannot_login_via_faculty_portal_and_sees_wrong_credentials(): void
    {
        $response = $this->post('/faculty/login', [
            'credential' => 'student@apex.edu.pk',
            'password' => 'Password123!',
            'role' => 'faculty',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'credential' => 'Wrong credentials!',
        ]);
    }

    public function test_faculty_can_login_via_faculty_portal(): void
    {
        $response = $this->post('/faculty/login', [
            'credential' => 'teacher@apex.edu.pk',
            'password' => 'Password123!',
            'role' => 'faculty',
        ]);

        $this->assertAuthenticatedAs($this->faculty);
        $response->assertRedirect($this->faculty->dashboardRoute());
    }

    public function test_faculty_cannot_login_via_principal_portal_and_sees_wrong_credentials(): void
    {
        $response = $this->post('/principal/login', [
            'credential' => 'teacher@apex.edu.pk',
            'password' => 'Password123!',
            'role' => 'principal',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'credential' => 'Wrong credentials!',
        ]);
    }

    public function test_faculty_cannot_login_via_student_portal_and_sees_wrong_credentials(): void
    {
        $response = $this->post('/student/login', [
            'credential' => 'teacher@apex.edu.pk',
            'password' => 'Password123!',
            'role' => 'student',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'credential' => 'Wrong credentials!',
        ]);
    }

    public function test_global_admin_can_login_via_global_admin_portal(): void
    {
        $response = $this->post('/globaladmin/login', [
            'credential' => 'admin@uplyft.com',
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticatedAs($this->globalAdmin);
        $response->assertRedirect(route('global-admin.dashboard'));
    }

    public function test_global_admin_cannot_login_via_principal_portal_and_sees_wrong_credentials(): void
    {
        $response = $this->post('/principal/login', [
            'credential' => 'admin@uplyft.com',
            'password' => 'Password123!',
            'role' => 'principal',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'credential' => 'Wrong credentials!',
        ]);
    }

    public function test_principal_cannot_login_via_global_admin_portal_and_sees_wrong_credentials(): void
    {
        $response = $this->post('/globaladmin/login', [
            'credential' => 'principal@apex.edu.pk',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'credential' => 'Wrong credentials!',
        ]);
    }
}
