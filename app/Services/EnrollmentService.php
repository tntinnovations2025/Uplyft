<?php

namespace App\Services;

use App\Models\ClassSection;
use App\Models\Institute;
use App\Models\InstituteSetting;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Enrollment Service (BUG-ENROLL-001 & BUG-FIN-001)
 *
 * Coordinates tax calculations, admission fee invoices, and the controlled
 * post-payment activation of student enrollments into class sections.
 */
class EnrollmentService
{
    protected FeeCalculationService $feeCalculator;
    protected TrackEnrollmentService $trackEnrollmentService;

    public function __construct(
        ?FeeCalculationService $feeCalculator = null,
        ?TrackEnrollmentService $trackEnrollmentService = null
    ) {
        $this->feeCalculator = $feeCalculator ?? app(FeeCalculationService::class);
        $this->trackEnrollmentService = $trackEnrollmentService ?? app(TrackEnrollmentService::class);
    }

    /**
     * Calculate tax and grand total based on Filer / Non-Filer status (BUG-FIN-001).
     * Resolves tax percentages dynamically via InstituteSetting.
     */
    public function calculateFee(float $baseFee, string $taxStatus, ?int $instituteId = null): array
    {
        $result = $this->feeCalculator->calculate(
            taxStatus: $taxStatus,
            instituteId: $instituteId,
            customBaseFee: $baseFee
        );

        $isFiler = strtolower(trim($taxStatus)) === 'filer';

        return [
            'base_fee' => $result['base_fee'],
            'is_filer' => $isFiler,
            'tax_rate' => $result['tax_rate'],
            'tax_amount' => $result['tax_amount'],
            'grand_total' => $result['grand_total'],
        ];
    }

    /**
     * Generate initial fee invoice for an admitted student.
     */
    public function generateAdmissionInvoice(Student $student, float $baseFee = 50000.00): Invoice
    {
        $feeDetails = $this->calculateFee($baseFee, $student->guardian_tax_status ?? 'Filer', $student->institute_id);

        return Invoice::create([
            'institute_id' => $student->institute_id,
            'student_id' => $student->id,
            'class_section_id' => $student->class_section_id,
            'title' => 'Admission & Tuition Fee',
            'amount_pkr' => $feeDetails['grand_total'],
            'admission_fee' => 0.00,
            'security_fee' => 0.00,
            'status' => 'unpaid',
            'due_date' => now()->addDays(14),
        ]);
    }

    /**
     * Complete student enrollment into Class & Section once admission fee is settled (BUG-ENROLL-001).
     * Assigns class_section_id, increments section counter, and sets status to enrolled.
     */
    public function enrollStudentAfterPayment(Invoice $invoice): bool
    {
        return DB::transaction(function () use ($invoice) {
            $student = $invoice->student;
            if (! $student) {
                return false;
            }

            // Determine target section from invoice or student
            $targetSectionId = $invoice->class_section_id ?? $student->class_section_id;

            // If already fully enrolled with section assigned, avoid duplicate counter increment
            if ($student->class_section_id && $student->annual_result_status === 'enrolled') {
                return true;
            }

            $updateData = [
                'admission_status' => 'enrolled',
                'annual_result_status' => 'enrolled',
            ];

            if ($targetSectionId) {
                $updateData['class_section_id'] = $targetSectionId;

                $section = ClassSection::with('instituteClass')->find($targetSectionId);
                if ($section) {
                    $section->increment('enrolled_students');

                    if ($section->instituteClass && empty($student->enrolled_program)) {
                        $updateData['enrolled_program'] = $section->instituteClass->name.' - '.$section->section_name;
                    }
                }
            }

            $student->update($updateData);

            // Dynamic Subject Architecture: Seed granular subject enrollments
            if ($targetSectionId && $student->user_id) {
                try {
                    $this->trackEnrollmentService->assignTrackToStudent(
                        studentUserId: (int) $student->user_id,
                        classSectionId: (int) $targetSectionId,
                        trackId: $student->academic_track_id ? (int) $student->academic_track_id : null,
                        customSubjectIds: is_array($student->selected_subject_ids) ? $student->selected_subject_ids : []
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Dynamic subject auto-enrollment failed for student #{$student->id}: " . $e->getMessage());
                }
            }

            return true;
        });
    }

    /**
     * Mark fee as Paid and trigger automatic enrollment into Class & Section.
     */
    public function markFeePaidAndEnroll(Invoice $invoice, ?string $paymentMethod = 'Bank Transfer'): bool
    {
        return DB::transaction(function () use ($invoice, $paymentMethod) {
            // Update invoice status
            $invoice->update([
                'status' => 'paid',
                'payment_method' => $paymentMethod ?? 'Bank Transfer',
                'paid_at' => now(),
            ]);

            return $this->enrollStudentAfterPayment($invoice);
        });
    }
}
