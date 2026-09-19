<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AccountHead;
use App\Models\FinancialTransaction;
use App\Services\AiFinancialAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AccountsController extends Controller
{
    protected AiFinancialAnalysisService $aiService;

    public function __construct(AiFinancialAnalysisService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Helper to resolve the appropriate accounts index route based on user portal.
     */
    protected function accountsIndexRoute(array $params = []): string
    {
        $user = auth()->user();
        if ($user && ! $user->isPrincipal() && ! $user->isGlobalAdmin()) {
            return $user->staffUrl('accounts', $params);
        }

        return route('principal.accounts.index', $params);
    }

    /**
     * Display the Accounts & Financial Ledger Dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $instituteId = $user->institute_id;

        if (!$user->isPrincipal() && !$user->isGlobalAdmin() && !$user->hasPermission('accounts') && !$user->hasPermission('invoices') && !(strtolower($user->staff_role ?? '') === 'accountant')) {
            abort(403, 'Unauthorized: You do not have access rights to the Accounts & Financial Ledger.');
        }

        // No default account heads seeded; only heads created by Principal/Administration will be displayed.

        $heads = AccountHead::where('institute_id', $instituteId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $activeTab = $request->input('tab', 'ledger');
        $period = $request->input('period', 'monthly');
        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');
        $headFilter = $request->input('head_id');
        $typeFilter = $request->input('type');

        // Fetch Faculty (Teachers) for Salaries Tab
        $salarySearch = trim($request->input('search', ''));
        $salarySection = $request->input('section', 'faculty'); // 'faculty' or 'staff'

        $facultyQuery = \App\Models\Teacher::where('institute_id', $instituteId)
            ->with(['user', 'salarySlips']);

        if ($salarySearch !== '') {
            $facultyQuery->where(function ($q) use ($salarySearch) {
                $q->where('first_name', 'like', "%{$salarySearch}%")
                    ->orWhere('last_name', 'like', "%{$salarySearch}%")
                    ->orWhere('employee_id', 'like', "%{$salarySearch}%")
                    ->orWhere('email', 'like', "%{$salarySearch}%")
                    ->orWhere('phone', 'like', "%{$salarySearch}%");
            });
        }

        $facultyList = $facultyQuery->orderBy('first_name')->get()
            ->unique(function ($t) {
                return strtolower(trim($t->email ?? $t->user->email ?? "{$t->first_name} {$t->last_name}"));
            })
            ->values();

        // Fetch Staff (Non-Teacher Users) for Salaries Tab
        $staffQuery = \App\Models\User::where('institute_id', $instituteId)
            ->whereNotIn('role', ['student', 'principal', 'teacher']);

        if ($salarySearch !== '') {
            $staffQuery->where(function ($q) use ($salarySearch) {
                $q->where('name', 'like', "%{$salarySearch}%")
                    ->orWhere('email', 'like', "%{$salarySearch}%")
                    ->orWhere('identifier', 'like', "%{$salarySearch}%");
            });
        }

        $staffList = $staffQuery->orderBy('name')->get();

        // Fetch transactions query
        $transactionsQuery = FinancialTransaction::where('institute_id', $instituteId)
            ->with(['accountHead', 'creator'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($headFilter)) {
            $transactionsQuery->where('account_head_id', $headFilter);
        }

        if (!empty($typeFilter)) {
            $transactionsQuery->where('type', $typeFilter);
        }

        $transactions = $transactionsQuery->paginate(20)->withQueryString();

        // Generate AI Financial & Profit/Loss Report
        $aiReportData = $this->aiService->generateReport($instituteId, $period, $customStart, $customEnd);

        // Financial Ledger Totals & Income/Expense Balances
        $totalIncome = (float) FinancialTransaction::where('institute_id', $instituteId)->where('type', 'income')->sum('amount');
        $totalExpense = (float) FinancialTransaction::where('institute_id', $instituteId)->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        // Total Configured Monthly Staff Payroll
        $teacherPayroll = (float) \App\Models\Teacher::where('institute_id', $instituteId)->sum('basic_salary_pkr');
        $staffPayroll = (float) \App\Models\User::where('institute_id', $instituteId)
            ->whereNotIn('role', ['student', 'principal', 'teacher'])
            ->sum('basic_salary_pkr');
        $totalConfiguredPayroll = $teacherPayroll + $staffPayroll;

        return view('principal.accounts.index', compact(
            'user',
            'heads',
            'transactions',
            'aiReportData',
            'activeTab',
            'period',
            'customStart',
            'customEnd',
            'headFilter',
            'typeFilter',
            'salarySearch',
            'salarySection',
            'facultyList',
            'staffList',
            'totalIncome',
            'totalExpense',
            'netBalance',
            'totalConfiguredPayroll'
        ));
    }

    /**
     * Auto-Disburse & Deduct All Staff Salaries from Accumulated Total Income.
     */
    public function autoDisburseSalaries(Request $request): RedirectResponse
    {
        if ($request->isMethod('get')) {
            return redirect()->to($this->accountsIndexRoute(['tab' => 'salaries']));
        }

        $user = auth()->user();
        $instituteId = $user->institute_id;
        $monthYear = now()->format('F Y');

        // 1. Fetch or create "Faculty & Staff Salaries" Account Head
        $head = AccountHead::where('institute_id', $instituteId)
            ->where('type', 'expense')
            ->where(function ($q) {
                $q->where('name', 'like', '%Salary%')
                    ->orWhere('name', 'like', '%Salaries%');
            })
            ->first();

        if (!$head) {
            $head = AccountHead::create([
                'institute_id' => $instituteId,
                'name' => 'Faculty & Staff Salaries',
                'type' => 'expense',
                'description' => 'Monthly compensation for teaching and support staff',
                'receipt_requirement' => 'optional',
                'is_active' => true,
                'created_by' => $user->id,
            ]);
        }

        // 2. Fetch all active faculty (Teachers) & support staff users
        $teachers = \App\Models\Teacher::where('institute_id', $instituteId)->get();
        $staffUsers = \App\Models\User::where('institute_id', $instituteId)
            ->whereNotIn('role', ['student', 'principal', 'teacher'])
            ->get();

        $processedCount = 0;
        $totalDisbursedAmount = 0.00;

        // Process Faculty Teachers
        foreach ($teachers as $teacher) {
            $sal = (float) ($teacher->basic_salary_pkr ?? ($teacher->user->basic_salary_pkr ?? 0));
            if ($sal <= 0) continue;

            // Check if already paid for current month
            $alreadyPaid = \App\Models\TeacherSalarySlip::where('teacher_id', $teacher->id)
                ->where('month_year', $monthYear)
                ->exists();

            if ($alreadyPaid) continue;

            $netPayout = $sal;

            // Record Teacher Salary Slip
            \App\Models\TeacherSalarySlip::create([
                'institute_id' => $instituteId,
                'teacher_id' => $teacher->id,
                'title' => "Monthly Salary Payout: {$monthYear}",
                'month_year' => $monthYear,
                'amount' => $netPayout,
                'file_path' => 'auto_disbursed_slip',
                'notes' => "Auto-disbursed salary on disbursement day. Deducted directly from total income.",
            ]);

            // Create Financial Transaction Expense
            FinancialTransaction::create([
                'institute_id' => $instituteId,
                'account_head_id' => $head->id,
                'title' => "Salary Paid: {$teacher->full_name} ({$monthYear})",
                'type' => 'expense',
                'amount' => $netPayout,
                'transaction_date' => now()->format('Y-m-d'),
                'payment_method' => 'Direct Payout / Auto-Deducted',
                'reference_number' => "AUTO-SAL-T{$teacher->id}-" . time(),
                'receipt_image' => null,
                'notes' => "Auto-disbursed monthly salary deducted directly from total collected income.",
                'created_by' => $user->id,
            ]);

            $processedCount++;
            $totalDisbursedAmount += $netPayout;
        }

        // Process Non-Teacher Staff Users
        foreach ($staffUsers as $staff) {
            $sal = (float) ($staff->basic_salary_pkr ?? 0);
            if ($sal <= 0) continue;

            // Check if already paid for current month
            $alreadyPaid = FinancialTransaction::where('institute_id', $instituteId)
                ->where('account_head_id', $head->id)
                ->where('title', 'like', "%{$staff->name} ({$monthYear})%")
                ->exists();

            if ($alreadyPaid) continue;

            $netPayout = $sal;

            FinancialTransaction::create([
                'institute_id' => $instituteId,
                'account_head_id' => $head->id,
                'title' => "Salary Paid: {$staff->name} ({$monthYear})",
                'type' => 'expense',
                'amount' => $netPayout,
                'transaction_date' => now()->format('Y-m-d'),
                'payment_method' => 'Direct Payout / Auto-Deducted',
                'reference_number' => "AUTO-SAL-U{$staff->id}-" . time(),
                'receipt_image' => null,
                'notes' => "Auto-disbursed monthly salary deducted directly from total collected income.",
                'created_by' => $user->id,
            ]);

            $processedCount++;
            $totalDisbursedAmount += $netPayout;
        }

        if ($processedCount === 0) {
            return redirect()->back()->with('warning', "All eligible staff salaries for {$monthYear} have already been disbursed, or basic salaries are not configured yet.");
        }

        $currencySymbol = \App\Models\InstituteSetting::getForInstitute($instituteId)->currency_symbol ?? 'PKR';
        $formattedAmount = number_format($totalDisbursedAmount, 2);

        return redirect()->back()->with('success', "🎉 Auto-Disbursement Successful! Disbursed total {$currencySymbol} {$formattedAmount} across {$processedCount} staff members and automatically deducted from total income for {$monthYear}.");
    }

    /**
     * Record a Salary Payment & Upload Salary Slip for a Faculty / Staff member.
     */
    public function storeSalaryPayment(Request $request, \App\Models\Teacher $teacher): RedirectResponse
    {
        if ($teacher->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'month_year' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0.01',
            'slip_file' => 'required|file|mimes:jpeg,png,jpg,pdf,webp|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $filePath = null;
        if ($request->hasFile('slip_file')) {
            $folder = "institutes/{$teacher->institute_id}/salary_slips/{$teacher->id}";
            $filePath = $request->file('slip_file')->store($folder, 'public');
        }

        $slip = \App\Models\TeacherSalarySlip::create([
            'institute_id' => $teacher->institute_id,
            'teacher_id' => $teacher->id,
            'title' => $validated['title'],
            'month_year' => $validated['month_year'],
            'amount' => $validated['amount'],
            'file_path' => $filePath,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Auto-create or find Faculty & Staff Salaries Account Head
        $head = AccountHead::where('institute_id', $teacher->institute_id)
            ->where('type', 'expense')
            ->where(function ($q) {
                $q->where('name', 'like', '%Salary%')
                    ->orWhere('name', 'like', '%Salaries%');
            })
            ->first();

        if (!$head) {
            $head = AccountHead::create([
                'institute_id' => $teacher->institute_id,
                'name' => 'Faculty & Staff Salaries',
                'type' => 'expense',
                'description' => 'Monthly compensation for teaching and support staff',
                'receipt_requirement' => 'optional',
                'is_active' => true,
                'created_by' => $user->id,
            ]);
        }

        FinancialTransaction::create([
            'institute_id' => $teacher->institute_id,
            'account_head_id' => $head->id,
            'title' => "Salary Paid: {$teacher->full_name} ({$validated['month_year']})",
            'type' => 'expense',
            'amount' => $validated['amount'],
            'transaction_date' => now()->format('Y-m-d'),
            'payment_method' => 'Bank Transfer',
            'reference_number' => "SAL-{$slip->id}",
            'receipt_image' => $filePath,
            'notes' => $validated['notes'] ?? "Salary payment recorded for {$teacher->full_name}",
            'created_by' => $user->id,
        ]);

        return redirect()->to($this->accountsIndexRoute(['tab' => 'salaries', 'section' => 'faculty']))
            ->with('success', "Salary payment of Rs. {$validated['amount']} recorded for {$teacher->full_name}!");
    }

    /**
     * Create a new customizable Account Head.
     */
    public function storeHead(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string|max:500',
            'receipt_requirement' => 'required|in:none,optional,mandatory',
        ]);

        $user = auth()->user();

        AccountHead::create([
            'institute_id' => $user->institute_id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'receipt_requirement' => $validated['receipt_requirement'],
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        return redirect()->to($this->accountsIndexRoute(['tab' => 'heads']))
            ->with('success', "Account Head '{$validated['name']}' created successfully.");
    }

    /**
     * Update an Account Head.
     */
    public function updateHead(Request $request, AccountHead $head): RedirectResponse
    {
        if ($head->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string|max:500',
            'receipt_requirement' => 'required|in:none,optional,mandatory',
            'is_active' => 'nullable|boolean',
        ]);

        $head->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'receipt_requirement' => $validated['receipt_requirement'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->to($this->accountsIndexRoute(['tab' => 'heads']))
            ->with('success', "Account Head '{$head->name}' updated.");
    }

    /**
     * Delete an Account Head.
     */
    public function destroyHead(AccountHead $head): RedirectResponse
    {
        if ($head->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $name = $head->name;
        $head->delete();

        return redirect()->to($this->accountsIndexRoute(['tab' => 'heads']))
            ->with('success', "Account Head '{$name}' removed.");
    }

    /**
     * Store an Income or Expense Transaction Entry.
     */
    public function storeTransaction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_head_id' => 'required|exists:account_heads,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'payment_method' => 'required|string|max:100',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'receipt_image' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:5120',
        ]);

        $user = auth()->user();
        $head = AccountHead::where('institute_id', $user->institute_id)
            ->findOrFail($validated['account_head_id']);

        // Check if receipt picture is mandatory for this head
        if ($head->receipt_requirement === 'mandatory' && !$request->hasFile('receipt_image')) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['receipt_image' => "A receipt / bill image is MANDATORY for head '{$head->name}'."]);
        }

        $receiptPath = null;
        if ($request->hasFile('receipt_image')) {
            $receiptPath = $request->file('receipt_image')->store('receipts', 'public');
        }

        $transaction = FinancialTransaction::create([
            'institute_id' => $user->institute_id,
            'account_head_id' => $head->id,
            'title' => $validated['title'],
            'type' => $head->type,
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'receipt_image' => $receiptPath,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        // Dispatch Real-Time Reverb Notification for expenses
        if ($head->type === 'expense') {
            \App\Services\PrincipalNotificationService::notifyExpenseRecorded($transaction, $user);
        }

        $redirectUrl = auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin()
            ? route('principal.accounts.index', ['tab' => 'ledger'])
            : auth()->user()->staffUrl('accounts?tab=ledger');

        return redirect()->to($redirectUrl)
            ->with('success', "Transaction '{$validated['title']}' recorded successfully and integrated into AI audit calculations.");
    }

    /**
     * Delete a Transaction Entry.
     */
    public function destroyTransaction(FinancialTransaction $transaction): RedirectResponse
    {
        if ($transaction->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        if ($transaction->receipt_image) {
            Storage::disk('public')->delete($transaction->receipt_image);
        }

        $title = $transaction->title;
        $transaction->delete();

        return redirect()->to($this->accountsIndexRoute(['tab' => 'ledger']))
            ->with('success', "Transaction '{$title}' removed.");
    }

    /**
     * Ensure default customizable account heads are created for new institutes.
     */
    protected function ensureDefaultHeads(int $instituteId, int $userId): void
    {
        $count = AccountHead::where('institute_id', $instituteId)->count();
        if ($count > 0) {
            return;
        }

        $defaults = [
            // Expenses
            ['name' => 'Utility Bills - Electricity & Gas', 'type' => 'expense', 'description' => 'Monthly electricity, gas, and power bills', 'receipt_requirement' => 'mandatory'],
            ['name' => 'Utility Bills - Internet & Water', 'type' => 'expense', 'description' => 'Campus broadband internet and water supply bills', 'receipt_requirement' => 'mandatory'],
            ['name' => 'Campus Maintenance & Repairs', 'type' => 'expense', 'description' => 'Plumbing, electrical, building repair & maintenance', 'receipt_requirement' => 'optional'],
            ['name' => 'Stationary & Office Supplies', 'type' => 'expense', 'description' => 'Paper, toner, pens, registers, and office stationary', 'receipt_requirement' => 'optional'],
            ['name' => 'Faculty & Staff Salaries', 'type' => 'expense', 'description' => 'Monthly compensation for teaching and support staff', 'receipt_requirement' => 'none'],
            ['name' => 'Lab Equipment & Tech Hardware', 'type' => 'expense', 'description' => 'Computer lab upgrades, science lab chemicals & tools', 'receipt_requirement' => 'mandatory'],
            ['name' => 'Miscellaneous Operational Expense', 'type' => 'expense', 'description' => 'Other petty cash & daily operational costs', 'receipt_requirement' => 'optional'],
            // Income
            ['name' => 'Tuition & Admission Fees', 'type' => 'income', 'description' => 'Monthly student tuition, registration, and admission fees', 'receipt_requirement' => 'none'],
            ['name' => 'Canteen & Lease Revenue', 'type' => 'income', 'description' => 'Canteen contract lease, cafeteria sales, or vending income', 'receipt_requirement' => 'optional'],
            ['name' => 'Donations & Grants', 'type' => 'income', 'description' => 'Special educational grants, alumni donations, and gifts', 'receipt_requirement' => 'optional'],
        ];

        foreach ($defaults as $item) {
            AccountHead::create([
                'institute_id' => $instituteId,
                'name' => $item['name'],
                'type' => $item['type'],
                'description' => $item['description'],
                'receipt_requirement' => $item['receipt_requirement'],
                'is_active' => true,
                'created_by' => $userId,
            ]);
        }
    }
}
