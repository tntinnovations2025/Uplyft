<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentAdmissionRequest;
use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use App\Services\FeeCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StudentAdmissionController extends Controller
{
    protected FeeCalculationService $feeCalculator;

    public function __construct(FeeCalculationService $feeCalculator)
    {
        $this->feeCalculator = $feeCalculator;
    }

    /**
     * Handle the incoming student admission form submission.
     * Creates a linked User login account with roll number credentials.
     */
    public function store(StoreStudentAdmissionRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $instituteId = $validated['institute_id']
                ?? (auth()->check() ? auth()->user()->institute_id : null)
                ?? (app()->bound('current_institute_id') ? app('current_institute_id') : 1);

            $validated['institute_id'] = $instituteId;

            $activeTerm = AcademicTerm::where('institute_id', $instituteId)
                ->where('is_active', true)
                ->first();

            // 1. Generate unique Roll Number: STD-YYYY-XXXX
            $year = now()->year;
            $sequence = str_pad(Student::withoutGlobalScopes()->count() + 1, 4, '0', STR_PAD_LEFT);
            $rollNumber = "STD-{$year}-{$sequence}";

            // Resolve name and email with fallbacks if fields not provided
            $studentName = trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? '')) ?: 'New Student';
            $studentEmail = !empty($validated['email']) ? $validated['email'] : ('stu-' . strtolower(\Illuminate\Support\Str::slug($studentName)) . '-' . $rollNumber . '@student.local');
            $validated['email'] = $studentEmail;

            $tempPassword = null;

            // 2. Create or reconcile global User login account for the student (including trashed accounts)
            $user = User::withTrashed()->where('email', $studentEmail)->first();
            if (! $user) {
                // Newly provisioned accounts get a one-time random password handed
                // back in the response. Existing accounts are never re-keyed.
                $tempPassword = \Illuminate\Support\Str::password(16);
                $user = User::create([
                    'institute_id' => $instituteId,
                    'name' => $studentName,
                    'role' => 'student',
                    'email' => $studentEmail,
                    'identifier' => $rollNumber,
                    'password' => Hash::make($tempPassword),
                ]);
            } else {
                if ($user->trashed()) {
                    $user->restore();
                }
                // If user account exists, reconcile to active institute without
                // changing its password, ensuring identifier is populated.
                $user->update([
                    'institute_id' => $instituteId,
                    'name' => $studentName,
                    'role' => 'student',
                    'email' => $studentEmail,
                    'identifier' => $user->identifier ?: $rollNumber,
                ]);
            }

            // 3. Handle passport picture upload
            $passportPath = null;
            if ($request->hasFile('passport_picture')) {
                $storagePath = "institutes/{$instituteId}/students/passports";
                $passportPath = $request->file('passport_picture')->store($storagePath, 'public');
            }

            // Controlled Enrollment Gate (BUG-ENROLL-001):
            // Do NOT assign class_section_id or increment enrolled_students at initial intake.
            // Retain designated section for post-payment activation in EnrollmentService.
            $designatedSectionId = $validated['class_section_id'] ?? null;
            $enrolledProgram = null;

            if (! empty($designatedSectionId)) {
                $section = ClassSection::with('instituteClass')->find($designatedSectionId);
                if ($section && $section->instituteClass) {
                    $trackSuffix = '';
                    if (! empty($validated['academic_track_id'])) {
                        $track = \App\Models\AcademicTrack::find($validated['academic_track_id']);
                        if ($track) {
                            $trackSuffix = ' (' . $track->track_name . ')';
                        }
                    }
                    $enrolledProgram = $section->instituteClass->name.' - '.$section->section_name . $trackSuffix;
                }
            }

            // Resolve scholarship category if assigned
            if (! empty($validated['scholarship_category_id'])) {
                $sch = \App\Models\ScholarshipCategory::find($validated['scholarship_category_id']);
                if ($sch) {
                    $validated['scholarship_name'] = $sch->title;
                    // If not principal, force discount percentage to category's configured default
                    if (empty($validated['scholarship_percentage']) || ! (auth()->check() && (auth()->user()->role === 'principal' || auth()->user()->role === 'admin'))) {
                        $validated['scholarship_percentage'] = $sch->discount_percentage;
                    }
                }
            }

            // 4. Persist Student profile with pending_payment status and unassigned section (BUG-ENROLL-001)
            $student = Student::create(array_merge($validated, [
                'user_id' => $user->id,
                'academic_term_id' => $activeTerm?->id,
                'roll_number' => $rollNumber,
                'class_section_id' => null, // Gated until invoice settlement
                'admission_status' => 'pending_payment',
                'annual_result_status' => 'pending',
                'enrolled_program' => $enrolledProgram,
                'passport_picture_path' => $passportPath,
            ]));

            // 5. Compute fee breakdown
            $feeBreakdown = $this->feeCalculator->calculate(
                $student->guardian_tax_status,
                $student->institute_id,
                $student->base_fee,
                $student->tax_percentage,
                $student->scholarship_percentage,
                $student->admission_fee,
                $student->security_fee
            );

            // 6. Generate DOMPDF and save to disk
            $institute = $student->institute;
            $logoBase64 = null;
            if ($institute && $institute->logo_path) {
                $logoPath = storage_path('app/public/'.$institute->logo_path);
                if (file_exists($logoPath)) {
                    $fileContent = file_get_contents($logoPath);
                    $mimeType = mime_content_type($logoPath) ?: 'image/png';
                    $logoBase64 = 'data:'.$mimeType.';base64,'.base64_encode($fileContent);
                }
            }

            $data = [
                'student' => $student,
                'institute' => $institute,
                'feeBreakdown' => $feeBreakdown,
                'logoBase64' => $logoBase64,
                'issuedAt' => now()->format('Y-m-d H:i:s'),
            ];

            $storagePath = null;
            try {
                $pdf = Pdf::loadView('pdf.invoice', $data);
                $pdfFileName = "invoices/{$student->id}_".time().'.pdf';
                $storagePath = "institutes/{$instituteId}/{$pdfFileName}";
                Storage::disk('public')->put($storagePath, $pdf->output());
            } catch (\Throwable $e) {
                Log::error('PDF Voucher Generation Error: '.$e->getMessage());
            }

            // 7. Create Invoice record
            $feeMonthName = now()->format('F Y');
            $invoice = Invoice::create([
                'institute_id' => $instituteId,
                'academic_term_id' => $activeTerm?->id,
                'student_id' => $student->id,
                'class_section_id' => $designatedSectionId, // Held until invoice is settled
                'title' => 'Admission & Initial Tuition Fee',
                'fee_month' => $feeMonthName,
                'amount_pkr' => $feeBreakdown['grand_total'],
                'admission_fee' => (float) ($student->admission_fee ?? 0.0),
                'security_fee' => (float) ($student->security_fee ?? 0.0),
                'due_date' => now()->addDays(7),
                'status' => 'unpaid',
                'pdf_path' => $storagePath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student admission registered successfully.',
                'student' => [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'email' => $student->email,
                    'roll_number' => $rollNumber,
                ],
                'credentials' => $tempPassword ? [
                    'email' => $student->email,
                    'password' => $tempPassword,
                    'portal' => url('/student/dashboard'),
                ] : null,
                'invoice' => [
                    'grand_total' => $feeBreakdown['grand_total'],
                    'invoice_download_url' => Storage::url($storagePath),
                ],
            ], 201);
        });
    }

    /**
     * Generate the PDF receipt/invoice dynamically.
     */
    public function generateInvoicePdf(Student $student): Response
    {
        $institute = $student->institute;

        $feeBreakdown = $this->feeCalculator->calculate(
            $student->guardian_tax_status,
            $student->institute_id,
            $student->base_fee,
            $student->tax_percentage,
            $student->scholarship_percentage,
            $student->admission_fee,
            $student->security_fee
        );

        $logoBase64 = null;
        if ($institute && $institute->logo_path) {
            $logoPath = storage_path('app/public/'.$institute->logo_path);
            if (file_exists($logoPath)) {
                $fileContent = file_get_contents($logoPath);
                $mimeType = mime_content_type($logoPath) ?: 'image/png';
                $logoBase64 = 'data:'.$mimeType.';base64,'.base64_encode($fileContent);
            }
        }

        $data = [
            'student' => $student,
            'institute' => $institute,
            'feeBreakdown' => $feeBreakdown,
            'logoBase64' => $logoBase64,
            'issuedAt' => now()->format('Y-m-d H:i:s'),
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);

        return $pdf->download("uplyft_invoice_{$student->id}.pdf");
    }

    /**
     * Update an admitted student profile.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $student = Student::withoutGlobalScopes()->findOrFail($id);
        $student->update($request->only(['first_name', 'last_name', 'phone', 'address']));
        return response()->json(['success' => true, 'student' => $student]);
    }
}
