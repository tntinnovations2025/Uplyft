<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentAdmissionRequest;
use App\Models\Student;
use App\Services\FeeCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
     */
    public function store(StoreStudentAdmissionRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            // Resolve the current tenant (institute) context
            $instituteId = auth()->user()->institute_id 
                ?? $request->input('institute_id') 
                ?? (app()->bound('current_institute_id') ? app('current_institute_id') : null);

            // 1. Process validation-cleaned inputs
            $validatedData = $request->validated();
            
            // Add institute scoping if resolved
            if ($instituteId) {
                $validatedData['institute_id'] = $instituteId;
            }

            // 2. Persist the Student Record (Global Scope & creation hooks apply here)
            $student = Student::create($validatedData);

            // 3. Delegate fee calculation logic to the dedicated domain service
            $feeBreakdown = $this->feeCalculator->calculate(
                $student->guardian_tax_status,
                $student->institute_id
            );

            // 4. Return structural json confirmation
            return response()->json([
                'success' => true,
                'message' => 'Student registration successful.',
                'data' => [
                    'student' => $student,
                    'fee_breakdown' => $feeBreakdown,
                    'invoice_download_url' => route('admissions.invoice', ['student' => $student->id])
                ]
            ], 201);
        });
    }

    /**
     * Generate the PDF receipt/invoice dynamically.
     */
    public function generateInvoicePdf(Student $student): Response
    {
        // 1. Fetch related institute (tenant context is already isolated via Global Scope on student queries)
        $institute = $student->institute;

        // 2. Compute live fee breakdown
        $feeBreakdown = $this->feeCalculator->calculate(
            $student->guardian_tax_status,
            $student->institute_id
        );

        // 3. Safely convert the logo to a Base64 string for reliable DomPDF execution.
        // This avoids sandbox exceptions or network lookup limits during PDF rendering.
        $logoBase64 = null;
        if ($institute && $institute->logo_path) {
            $logoPath = storage_path('app/public/' . $institute->logo_path);
            
            if (file_exists($logoPath)) {
                $fileContent = file_get_contents($logoPath);
                $mimeType = mime_content_type($logoPath) ?: 'image/png';
                $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($fileContent);
            }
        }

        // 4. Scaffolding dynamic payload for the Blade PDF layout
        $data = [
            'student'      => $student,
            'institute'    => $institute,
            'feeBreakdown' => $feeBreakdown,
            'logoBase64'   => $logoBase64,
            'issuedAt'     => now()->format('Y-m-d H:i:s'),
        ];

        // 5. Build and output PDF Stream
        $pdf = Pdf::loadView('pdf.invoice', $data);

        return $pdf->download("uplyft_invoice_{$student->id}.pdf");
    }
}
