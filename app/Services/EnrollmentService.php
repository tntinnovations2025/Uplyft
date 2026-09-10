<?php

namespace App\Services;

use App\Models\Institute;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnrollmentService
{
    /**
     * Calculate tax and grand total based on Filer / Non-Filer status.
     */
    public function calculateFee(float $baseFee, string $taxStatus, ?int $instituteId = null): array
    {
        $isFiler = strtolower($taxStatus) === 'filer';

        // 0% for Filers, 5% for Non-Filers (or tenant setting)
        $taxRate = $isFiler ? 0.00 : 0.05;

        if ($instituteId) {
            $institute = Institute::find($instituteId);
            if ($institute && isset($institute->settings['non_filer_tax_rate'])) {
                $taxRate = $isFiler ? 0.00 : (float) $institute->settings['non_filer_tax_rate'];
            }
        }

        $taxAmount = round($baseFee * $taxRate, 2);
        $grandTotal = round($baseFee + $taxAmount, 2);

        return [
            'base_fee' => $baseFee,
            'is_filer' => $isFiler,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
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
            'invoice_number' => 'INV-'.strtoupper(Str::random(8)),
            'title' => 'Admission & Tuition Fee',
            'amount' => $feeDetails['grand_total'],
            'base_amount' => $feeDetails['base_fee'],
            'tax_amount' => $feeDetails['tax_amount'],
            'status' => 'Pending',
            'due_date' => now()->addDays(14),
        ]);
    }

    /**
     * Mark fee as Paid and trigger automatic enrollment into Class & Section.
     */
    public function markFeePaidAndEnroll(Invoice $invoice, ?string $paymentMethod = 'Bank Transfer'): bool
    {
        return DB::transaction(function () use ($invoice, $paymentMethod) {
            // Update invoice status
            $invoice->update([
                'status' => 'Paid',
                'payment_method' => $paymentMethod,
                'paid_at' => now(),
            ]);

            // Retrieve student and auto-enroll
            $student = $invoice->student;
            if ($student) {
                $student->update([
                    'status' => 'Active',
                    'is_enrolled' => true,
                    'enrolled_at' => now(),
                ]);
            }

            return true;
        });
    }
}
