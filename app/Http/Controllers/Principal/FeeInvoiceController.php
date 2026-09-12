<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\InstituteClass;
use App\Models\Invoice;
use App\Models\Student;
use App\Services\FeeCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Principal Portal Fee Assignment & Invoice Generation Engine.
 */
class FeeInvoiceController extends Controller
{
    public function __construct(protected FeeCalculationService $feeCalculator) {}

    /**
     * Display fee invoices roster and revenue overview.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $activeTerm = AcademicTerm::where('institute_id', $user->institute_id)
            ->where('is_active', true)
            ->first();

        $query = Invoice::where('institute_id', $user->institute_id)
            ->when($activeTerm, function ($q) use ($activeTerm) {
                $q->where(function ($sub) use ($activeTerm) {
                    $sub->where('academic_term_id', $activeTerm->id)
                        ->orWhereHas('classSection.instituteClass', fn ($cs) => $cs->where('academic_term_id', $activeTerm->id));
                });
            })
            ->with(['student.classSection.instituteClass', 'classSection.instituteClass'])
            ->orderBy('created_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('fee_month', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('roll_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($sectionId = $request->input('class_section_id')) {
            $query->where('class_section_id', $sectionId);
        }

        $invoices = $query->paginate(25)->withQueryString();

        $termScope = fn ($q) => $activeTerm ? $q->where(function ($sub) use ($activeTerm) {
            $sub->where('academic_term_id', $activeTerm->id)
                ->orWhereHas('classSection.instituteClass', fn ($cs) => $cs->where('academic_term_id', $activeTerm->id));
        }) : $q;

        $totalIssued = Invoice::where('institute_id', $user->institute_id)->tap($termScope)->count();
        $totalAmount = Invoice::where('institute_id', $user->institute_id)->tap($termScope)->sum('amount_pkr');
        $totalPaid = Invoice::where('institute_id', $user->institute_id)->where('status', 'paid')->tap($termScope)->sum('amount_pkr');
        $totalUnpaid = Invoice::where('institute_id', $user->institute_id)->where('status', 'unpaid')->tap($termScope)->sum('amount_pkr');

        $classes = InstituteClass::where('institute_id', $user->institute_id)
            ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
            ->with('sections')
            ->get();

        return view('principal.invoices.index', compact(
            'invoices', 'totalIssued', 'totalAmount', 'totalPaid', 'totalUnpaid', 'classes', 'activeTerm'
        ));
    }

    /**
     * Display the Fee Assignment & Voucher Generation form.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $institute = $user->institute;
        $activeTerm = AcademicTerm::where('institute_id', $user->institute_id)
            ->where('is_active', true)
            ->first();

        $classes = InstituteClass::where('institute_id', $user->institute_id)
            ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
            ->with(['systemClass', 'sections'])
            ->get();

        $students = Student::where('institute_id', $user->institute_id)
            ->when($activeTerm, function ($q) use ($activeTerm) {
                $q->where(function ($sub) use ($activeTerm) {
                    $sub->where('academic_term_id', $activeTerm->id)
                        ->orWhereHas('classSection.instituteClass', fn ($cs) => $cs->where('academic_term_id', $activeTerm->id));
                });
            })
            ->with('classSection.instituteClass')
            ->orderBy('first_name', 'asc')
            ->get();

        $monthsList = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];

        $yearsList = [
            now()->year - 1,
            now()->year,
            now()->year + 1,
            now()->year + 2,
        ];

        return view('principal.invoices.create', compact('institute', 'classes', 'students', 'monthsList', 'yearsList'));
    }

    /**
     * Store and assign bulk or targeted fee invoices to students.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_scope' => ['required', 'string', 'in:all_institute,class_section,single_student'],
            'class_section_id' => ['required_if:target_scope,class_section', 'nullable', 'exists:class_sections,id'],
            'student_id' => ['required_if:target_scope,single_student', 'nullable', 'exists:students,id'],
            'from_month' => ['nullable', 'string'],
            'to_month' => ['nullable', 'string'],
            'fee_year' => ['nullable', 'string'],
            'fee_month' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:150'],
            'base_fee' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $user = $request->user();
        $institute = $user->institute;

        if (! empty($validated['from_month']) && ! empty($validated['to_month']) && ! empty($validated['fee_year'])) {
            if ($validated['from_month'] === $validated['to_month']) {
                $feeMonth = "{$validated['from_month']} {$validated['fee_year']}";
            } else {
                $feeMonth = "{$validated['from_month']} - {$validated['to_month']} {$validated['fee_year']}";
            }
        } else {
            $feeMonth = $validated['fee_month'] ?? now()->format('F Y');
        }

        // Determine target student list
        $studentsQuery = Student::where('institute_id', $user->institute_id);

        if ($validated['target_scope'] === 'class_section') {
            $studentsQuery->where('class_section_id', $validated['class_section_id']);
        } elseif ($validated['target_scope'] === 'single_student') {
            $studentsQuery->where('id', $validated['student_id']);
        }

        $targetStudents = $studentsQuery->get();

        if ($targetStudents->isEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'No active students found under the selected target criteria.');
        }

        $createdCount = 0;

        DB::transaction(function () use ($targetStudents, $validated, $feeMonth, $institute, $user, &$createdCount) {
            foreach ($targetStudents as $student) {
                $baseFee = ! empty($validated['base_fee']) && $validated['base_fee'] > 0
                    ? $validated['base_fee']
                    : ($student->base_fee ?? 50000);

                // Compute fee breakdown (Admission and Security fees are ONE-TIME charges; set to 0.0 for subsequent fee assignments)
                $feeBreakdown = $this->feeCalculator->calculate(
                    $student->guardian_tax_status,
                    $student->institute_id,
                    $baseFee,
                    $student->tax_percentage,
                    $student->scholarship_percentage,
                    0.0, // admission_fee = 0 for subsequent fee assignments
                    0.0  // security_fee = 0 for subsequent fee assignments
                );

                // Generate PDF Voucher
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
                    'title' => $validated['title'],
                    'feeMonth' => $feeMonth,
                    'issuedAt' => now()->format('Y-m-d H:i:s'),
                ];

                $pdf = Pdf::loadView('pdf.invoice', $data);
                $pdfFileName = "invoices/{$student->id}_".time().'_'.rand(100, 999).'.pdf';
                $storagePath = "institutes/{$institute->id}/{$pdfFileName}";

                Storage::disk('public')->put($storagePath, $pdf->output());

                $activeTerm = AcademicTerm::where('institute_id', $user->institute_id)
                    ->where('is_active', true)
                    ->first();

                // Persist invoice record (no admission/security fee on recurring invoices)
                Invoice::create([
                    'institute_id' => $institute->id,
                    'academic_term_id' => $activeTerm?->id,
                    'student_id' => $student->id,
                    'class_section_id' => $student->class_section_id,
                    'title' => $validated['title'],
                    'fee_month' => $feeMonth,
                    'amount_pkr' => $feeBreakdown['grand_total'],
                    'admission_fee' => 0.0,
                    'security_fee' => 0.0,
                    'due_date' => $validated['due_date'],
                    'status' => 'unpaid',
                    'pdf_path' => $storagePath,
                ]);

                $createdCount++;
            }
        });

        $redirectTarget = (auth()->user() && ! auth()->user()->isPrincipal() && ! auth()->user()->isGlobalAdmin())
            ? auth()->user()->staffUrl('invoices')
            : route('principal.invoices.index');

        return redirect($redirectTarget)
            ->with('success', "Fee successfully assigned! Created {$createdCount} fee voucher invoice(s) for '{$feeMonth}'.");
    }

    /**
     * Mark fee invoice as Paid and log income to Financial Ledger.
     */
    public function markPaid(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->institute_id !== auth()->user()->institute_id && ! auth()->user()->isGlobalAdmin()) {
            abort(403);
        }

        $user = auth()->user();
        $head = \App\Models\AccountHead::where('institute_id', $invoice->institute_id)
            ->where('type', 'income')
            ->where(function($q) {
                $q->where('name', 'like', '%Tuition%')
                  ->orWhere('name', 'like', '%Fee%');
            })
            ->first();

        if (!$head) {
            $head = \App\Models\AccountHead::create([
                'institute_id' => $invoice->institute_id,
                'name' => 'Tuition & Admission Fees',
                'type' => 'income',
                'description' => 'Monthly student tuition and admission fees',
                'receipt_requirement' => 'optional',
                'is_active' => true,
                'created_by' => $user->id,
            ]);
        }

        // Validate receipt picture if mandatory for this head
        if ($head->receipt_requirement === 'mandatory' && !$request->hasFile('paid_slip') && empty($invoice->paid_slip_path)) {
            return redirect()
                ->back()
                ->with('error', "Fee slip picture attachment is MANDATORY for head '{$head->name}' before marking as Paid.");
        }

        $slipPath = $invoice->paid_slip_path;
        if ($request->hasFile('paid_slip')) {
            $folder = "institutes/{$invoice->institute_id}/fee_slips";
            $slipPath = $request->file('paid_slip')->store($folder, 'public');
        }

        $invoice->update([
            'status' => 'paid',
            'paid_slip_path' => $slipPath,
        ]);

        // Trigger controlled class section enrollment upon invoice settlement (BUG-ENROLL-001)
        app(\App\Services\EnrollmentService::class)->enrollStudentAfterPayment($invoice);

        // Auto-log to Financial Ledger (Total Income)
        \App\Models\FinancialTransaction::create([
            'institute_id' => $invoice->institute_id,
            'account_head_id' => $head->id,
            'title' => "Student Fee Invoice: {$invoice->title} - " . ($invoice->student ? $invoice->student->full_name : "Student #{$invoice->student_id}"),
            'type' => 'income',
            'amount' => $invoice->amount_pkr,
            'transaction_date' => now()->format('Y-m-d'),
            'payment_method' => $request->input('payment_method', 'Bank Transfer'),
            'reference_number' => "INV-{$invoice->id}",
            'receipt_image' => $slipPath,
            'notes' => "Auto-logged from Student Fee Voucher #INV-{$invoice->id} ({$invoice->fee_month})",
            'created_by' => $user->id,
        ]);

        return redirect()
            ->back()
            ->with('success', "Invoice #INV-{$invoice->id} marked as Paid and added to Total Income ledger.");
    }
}
