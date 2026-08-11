<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentAdmissionRequest;
use App\Models\Student;
use App\Models\User;
use App\Services\FeeCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            $validated    = $request->validated();
            $instituteId  = $validated['institute_id']
                ?? (auth()->check() ? auth()->user()->institute_id : null)
                ?? (app()->bound('current_institute_id') ? app('current_institute_id') : 1);

            $validated['institute_id'] = $instituteId;

            // 1. Generate unique Roll Number: STD-YYYY-XXXX
            $year     = now()->year;
            $sequence = str_pad(Student::withoutGlobalScopes()->count() + 1, 4, '0', STR_PAD_LEFT);
            $rollNumber = "STD-{$year}-{$sequence}";

            $defaultPassword = 'UplyftStudent123!';

            // 2. Create a global User login account for the student
            $user = User::create([
                'institute_id' => $instituteId,
                'name'         => trim($validated['first_name'] . ' ' . $validated['last_name']),
                'login_id'     => $rollNumber,
                'role'         => 'student',
                'email'        => $validated['email'],
                'password'     => Hash::make($defaultPassword),
            ]);

            // 3. Persist the Student profile linked to the User account
            $student = Student::create(array_merge($validated, [
                'user_id'     => $user->id,
                'roll_number' => $rollNumber,
            ]));

            // 4. Compute fee breakdown
            $feeBreakdown = $this->feeCalculator->calculate(
                $student->guardian_tax_status,
                $student->institute_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Student admission registered successfully.',
                'student' => [
                    'id'         => $student->id,
                    'first_name' => $student->first_name,
                    'last_name'  => $student->last_name,
                    'email'      => $student->email,
                    'roll_number'=> $rollNumber,
                ],
                'credentials' => [
                    'login_id' => $rollNumber,
                    'password' => $defaultPassword,
                    'portal'   => url('/student/dashboard'),
                ],
                'invoice' => [
                    'grand_total'          => $feeBreakdown['grand_total'],
                    'invoice_download_url' => route('admissions.invoice', ['student' => $student->id]),
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
            $student->institute_id
        );

        $logoBase64 = null;
        if ($institute && $institute->logo_path) {
            $logoPath = storage_path('app/public/' . $institute->logo_path);
            if (file_exists($logoPath)) {
                $fileContent = file_get_contents($logoPath);
                $mimeType    = mime_content_type($logoPath) ?: 'image/png';
                $logoBase64  = 'data:' . $mimeType . ';base64,' . base64_encode($fileContent);
            }
        }

        $data = [
            'student'      => $student,
            'institute'    => $institute,
            'feeBreakdown' => $feeBreakdown,
            'logoBase64'   => $logoBase64,
            'issuedAt'     => now()->format('Y-m-d H:i:s'),
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);

        return $pdf->download("uplyft_invoice_{$student->id}.pdf");
    }
}
