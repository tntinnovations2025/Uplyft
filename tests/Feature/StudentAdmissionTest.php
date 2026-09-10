<?php

namespace Tests\Feature;

use App\Models\Institute;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StudentAdmissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test student admission registration.
     */
    public function test_student_can_register_for_admission(): void
    {
        // 1. Create a dummy institute
        $institute = Institute::create([
            'name' => 'Test Academy',
            'slug' => 'test-academy-'.uniqid(),
            'logo_path' => 'logos/test_logo.png',
            'settings' => [
                'base_admission_fee' => 12000.00,
                'filer_tax_rate' => 0.05,
                'non_filer_tax_rate' => 0.15,
            ],
        ]);

        // 2. Submit admission form via API
        $payload = [
            'institute_id' => $institute->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '123456789',
            'date_of_birth' => '2010-05-15',
            'previous_marks' => 88.50,
            'guardian_tax_status' => 'filer',
            'base_fee' => 12000.00,
        ];

        $response = $this->postJson('/api/admissions', array_merge($payload, [
            'passport_picture' => UploadedFile::fake()->image('passport.jpg', 300, 300),
        ]));

        // 3. Assert response structure and content
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'student' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'roll_number',
                ],
                'credentials' => [
                    'email',
                    'password',
                    'portal',
                ],
                'invoice' => [
                    'grand_total',
                    'invoice_download_url',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Student admission registered successfully.',
                'student' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
                'invoice' => [
                    'grand_total' => 12600.00,
                ],
            ]);

        // 4. Assert a matching User + Invoice row were created
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'role' => 'student',
        ]);

        $this->assertDatabaseHas('invoices', [
            'student_id' => $response->json('student.id'),
            'amount_pkr' => 12600.00,
            'status' => 'unpaid',
        ]);
    }

    /**
     * Test invoice PDF generation.
     */
    public function test_can_download_invoice_pdf(): void
    {
        // 1. Create a dummy institute and student
        $institute = Institute::create([
            'name' => 'Test Academy',
            'settings' => [
                'base_admission_fee' => 10000.00,
                'filer_tax_rate' => 0.05,
                'non_filer_tax_rate' => 0.15,
            ],
        ]);

        $student = Student::create([
            'institute_id' => $institute->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '987654321',
            'date_of_birth' => '2011-08-20',
            'previous_marks' => 95.00,
            'guardian_tax_status' => 'non-filer',
            'base_fee' => 10000.00,
        ]);

        // 2. Request PDF generation route
        $response = $this->get("/api/admissions/{$student->id}/invoice");

        // 3. Assert response is a PDF download
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=uplyft_invoice_', $response->headers->get('Content-Disposition'));
    }
}
