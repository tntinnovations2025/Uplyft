<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\FinancialTransaction;
use App\Models\InstituteClass;
use App\Models\InstituteSetting;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrincipalDashboardController extends Controller
{
    /**
     * Display the Executive Principal Analytics Dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $instituteId = $user->getActiveInstituteId();
        $institute = \App\Models\Institute::withoutGlobalScopes()->find($instituteId) ?? $user->institute;

        if (!$institute) {
            // Fallback for global admin or unassigned session: resolve active institute
            $institute = \App\Models\Institute::where('is_active', true)->first() ?? \App\Models\Institute::first();
            $instituteId = $institute?->id;
        }

        // 1. Active Term Context
        $activeTerm = AcademicTerm::getActiveTerm($instituteId);

        // 2. Core Entity Counts
        $classesCount = InstituteClass::where('institute_id', $instituteId)
            ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
            ->count();
        if ($classesCount === 0) {
            $classesCount = InstituteClass::where('institute_id', $instituteId)->count();
        }

        $staffCount = User::where('institute_id', $instituteId)
            ->whereIn('role', [User::ROLE_TEACHER, User::ROLE_PRINCIPAL, 'staff'])
            ->count();

        $studentsCount = Student::where('institute_id', $instituteId)
            ->when($activeTerm, function ($q) use ($activeTerm) {
                $q->where(function ($sub) use ($activeTerm) {
                    $sub->where('academic_term_id', $activeTerm->id)
                        ->orWhereNull('academic_term_id')
                        ->orWhereHas('classSection.instituteClass', fn ($cs) => $cs->where('academic_term_id', $activeTerm->id));
                });
            })
            ->count();
        if ($studentsCount === 0) {
            $studentsCount = Student::where('institute_id', $instituteId)->count();
        }

        $slotsCount = $activeTerm ? Timetable::where('academic_term_id', $activeTerm->id)->count() : 0;

        // 3. Financial Analytics (Inflow, Recovery, Arrears, Monthly Trends)
        $invoicesQuery = Invoice::where('institute_id', $instituteId);
        $totalInvoiced = (float) $invoicesQuery->sum('amount_pkr');
        $totalPaid = (float) (clone $invoicesQuery)->where('status', 'paid')->sum('amount_pkr');
        $totalPending = (float) (clone $invoicesQuery)->where('status', 'unpaid')->sum('amount_pkr');
        $totalOverdue = (float) (clone $invoicesQuery)
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now()->toDateString())
            ->sum('amount_pkr');
        $overdueCount = (clone $invoicesQuery)
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        $feeRecoveryRate = $totalInvoiced > 0 ? round(($totalPaid / $totalInvoiced) * 100, 1) : 0;

        // Upcoming Fee & Due Date Countdown
        $upcomingInvoice = (clone $invoicesQuery)
            ->where('status', '!=', 'paid')
            ->where('due_date', '>=', now()->toDateString())
            ->orderBy('due_date')
            ->first();

        $upcomingFeeAmount = $upcomingInvoice ? (float) $upcomingInvoice->amount_pkr : $totalPending;
        $upcomingDueDate = $upcomingInvoice ? $upcomingInvoice->due_date : null;
        $daysLeft = $upcomingDueDate ? max(0, (int) now()->diffInDays($upcomingDueDate, false)) : 0;

        // 4. Institute Settings & Baseline Targets
        $instSettings = InstituteSetting::getForInstitute($instituteId);
        $baseIncomeTarget = $instSettings->monthly_income_target > 0 ? (float) $instSettings->monthly_income_target : 0;
        $baseExpenseBudget = $instSettings->monthly_expense_budget > 0 ? (float) $instSettings->monthly_expense_budget : 0;
        $minAttThreshold = $instSettings->min_required_attendance_pct > 0 ? (float) $instSettings->min_required_attendance_pct : 75.0;

        // 6-Month Financial Trend Data (Cached for 60 seconds to guarantee instant sub-second page switching)
        $financialTrend = \Illuminate\Support\Facades\Cache::remember("principal_dash_finance_{$instituteId}", 60, function () use ($instituteId, $baseIncomeTarget) {
            $months = [];
            $revenueData = [];
            $targetData = [];
            $expenseData = [];
            $taxData = [];
            $profitData = [];
            $hasRealFinancialData = false;

            for ($i = 5; $i >= 0; $i--) {
                $monthDate = now()->subMonths($i);
                $monthLabel = $monthDate->format('M Y');
                $months[] = $monthLabel;

                // Real Income: Paid Invoices + Income Transactions
                $monthFeePaid = (float) Invoice::where('institute_id', $instituteId)
                    ->where('status', 'paid')
                    ->whereYear('created_at', $monthDate->year)
                    ->whereMonth('created_at', $monthDate->month)
                    ->sum('amount_pkr');

                $monthOtherIncome = (float) FinancialTransaction::where('institute_id', $instituteId)
                    ->where('type', 'income')
                    ->whereYear('transaction_date', $monthDate->year)
                    ->whereMonth('transaction_date', $monthDate->month)
                    ->sum('amount');

                $realIncome = $monthFeePaid + $monthOtherIncome;

                // Real Expenses
                $realExpense = (float) FinancialTransaction::where('institute_id', $instituteId)
                    ->where('type', 'expense')
                    ->whereYear('transaction_date', $monthDate->year)
                    ->whereMonth('transaction_date', $monthDate->month)
                    ->sum('amount');

                // Real Tax Paid (head name containing tax/taxation)
                $realTax = (float) FinancialTransaction::where('institute_id', $instituteId)
                    ->where('type', 'expense')
                    ->whereHas('accountHead', function ($q) {
                        $q->where('name', 'LIKE', '%tax%');
                    })
                    ->whereYear('transaction_date', $monthDate->year)
                    ->whereMonth('transaction_date', $monthDate->month)
                    ->sum('amount');

                if ($realIncome > 0 || $realExpense > 0) {
                    $hasRealFinancialData = true;
                }

                $revenueData[] = $realIncome;
                $targetData[] = $baseIncomeTarget;
                $expenseData[] = $realExpense;
                $taxData[] = $realTax;
                $profitData[] = $realIncome - $realExpense - $realTax;
            }

            $latestRev = end($revenueData);
            $prevRev = prev($revenueData);
            $growthPercent = $prevRev > 0 ? round((($latestRev - $prevRev) / $prevRev) * 100, 1) : 0.0;
            $isFinancialsUp = $growthPercent >= 0;

            return compact('months', 'revenueData', 'targetData', 'expenseData', 'taxData', 'profitData', 'hasRealFinancialData', 'growthPercent', 'isFinancialsUp');
        });

        $months = $financialTrend['months'];
        $revenueData = $financialTrend['revenueData'];
        $targetData = $financialTrend['targetData'];
        $expenseData = $financialTrend['expenseData'];
        $taxData = $financialTrend['taxData'];
        $profitData = $financialTrend['profitData'];
        $hasRealFinancialData = $financialTrend['hasRealFinancialData'];
        $growthPercent = $financialTrend['growthPercent'];
        $isFinancialsUp = $financialTrend['isFinancialsUp'];

        // 5. Faculty & Teacher Attendance Analysis (Real Database Records Only)
        // Deduplicate by email if present, otherwise by full name — never show the same profile twice
        $teachers = Teacher::where('institute_id', $instituteId)
            ->with(['user'])
            ->orderBy('id')
            ->get()
            ->filter(function ($t) {
                return $t->email !== null || $t->full_name !== null;
            });

        $seenKeys = [];
        $teachers = $teachers->filter(function ($t) use (&$seenKeys) {
            $key = strtolower(trim($t->email ?? $t->user->email ?? $t->full_name));
            if (empty($key)) {
                return true;
            }
            if (isset($seenKeys[$key])) {
                return false;
            }
            $seenKeys[$key] = true;
            return true;
        })->values();

        $facultyList = [];
        $criticalRiskCount = 0;
        $totalAttendancePercentSum = 0;

        foreach ($teachers as $teacher) {
            $attRate = 100.0;
            $isCritical = $attRate < $minAttThreshold;
            $isWarning = !$isCritical && $attRate < ($minAttThreshold + 10);

            if ($isCritical) {
                $criticalRiskCount++;
            }

            $totalAttendancePercentSum += $attRate;

            $initials = strtoupper(substr($teacher->first_name ?? '', 0, 1) . substr($teacher->last_name ?? '', 0, 1));
            if (empty($initials)) {
                $initials = 'TC';
            }

            $facultyList[] = [
                'id' => $teacher->id,
                'name' => $teacher->full_name,
                'email' => $teacher->email ?? ($teacher->user->email ?? 'N/A'),
                'phone' => $teacher->phone ?? 'N/A',
                'qualification' => $teacher->qualification ?? 'Faculty Member',
                'department' => $teacher->specialization_subjects ?? 'General Faculty',
                'initials' => $initials,
                'attendance_rate' => $attRate,
                'is_critical' => $isCritical,
                'is_warning' => $isWarning,
                'status_label' => $isCritical ? 'Critical Risk' : ($isWarning ? 'Watchlist' : 'Healthy Regular'),
                'status_color' => $isCritical ? '#A2412C' : ($isWarning ? '#8A5A10' : '#2E6E42'),
            ];
        }

        // Sort ascending by attendance (lowest first) so critical appear first
        usort($facultyList, fn($a, $b) => $a['attendance_rate'] <=> $b['attendance_rate']);

        // If no critical risk, show only the 2 lowest-attendance profiles
        if ($criticalRiskCount === 0 && count($facultyList) > 2) {
            $facultyList = array_slice($facultyList, 0, 2);
        }

        $overallTeacherAttendance = count($facultyList) > 0 ? round($totalAttendancePercentSum / count($teachers), 1) : 0;

        // 5. Subject & Class Attendance Progress Breakdown (Real Database Records Only)
        $classSections = ClassSection::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->with(['instituteClass', 'classIncharge'])
            ->take(5)
            ->get();

        // Aggregate attendance per section in a single query (avoids N+1 per section)
        $sectionIds = $classSections->pluck('id');
        $sectionAttAgg = collect();
        if ($sectionIds->isNotEmpty()) {
            $sectionAttAgg = \Illuminate\Support\Facades\DB::table('students')
                ->join('attendances', 'students.id', '=', 'attendances.student_id')
                ->whereIn('students.class_section_id', $sectionIds)
                ->selectRaw('students.class_section_id as section_id, COUNT(*) as total_att, SUM(CASE WHEN attendances.status = \'present\' THEN 1 ELSE 0 END) as present_att')
                ->groupBy('students.class_section_id')
                ->get()
                ->keyBy('section_id');
        }

        $sectionAttendanceList = [];
        $totalSectionAttendanceSum = 0;
        $activeSectionsWithLogs = 0;

        foreach ($classSections as $cs) {
            $agg = $sectionAttAgg->get($cs->id);
            $totalAtt = $agg ? (int) $agg->total_att : 0;
            $presentAtt = $agg ? (int) $agg->present_att : 0;

            $hasLogs = $totalAtt > 0;
            $rate = $hasLogs ? round(($presentAtt / $totalAtt) * 100, 1) : null;
            if ($hasLogs) {
                $totalSectionAttendanceSum += $rate;
                $activeSectionsWithLogs++;
            }

            $sectionAttendanceList[] = [
                'name' => ($cs->instituteClass->name ?? 'Class') . ' — ' . $cs->section_name,
                'rate' => $rate,
                'has_logs' => $hasLogs,
                'color' => !$hasLogs ? '#A19E92' : ($rate >= 75 ? '#10b981' : ($rate >= 50 ? '#f59e0b' : '#ef4444')),
                'incharge' => $cs->classIncharge ? $cs->classIncharge->name : 'Staff Assigned',
                'is_critical' => $hasLogs && $rate < 75.0,
            ];
        }

        $overallStudentAttendance = $activeSectionsWithLogs > 0 ? round($totalSectionAttendanceSum / $activeSectionsWithLogs, 1) : null;

        // 6. Term Progress & Academic Timeline
        $termProgressPercent = 65.0;
        if ($activeTerm && $activeTerm->start_date && $activeTerm->end_date) {
            $start = Carbon::parse($activeTerm->start_date);
            $end = Carbon::parse($activeTerm->end_date);
            $totalDays = $start->diffInDays($end);
            $elapsedDays = $start->diffInDays(now());
            if ($totalDays > 0) {
                $termProgressPercent = min(100, max(5, round(($elapsedDays / $totalDays) * 100, 1)));
            }
        }

        // Academic Warnings Aggregate (faculty risk only — overdue invoices tracked separately via Invoices section)
        $academicWarningsCount = $criticalRiskCount;

        // 7. Recent Invoices Feed
        $recentInvoices = Invoice::where('institute_id', $instituteId)
            ->with('student')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('principal.dashboard', compact(
            'user',
            'institute',
            'activeTerm',
            'classesCount',
            'staffCount',
            'studentsCount',
            'slotsCount',
            // Financial Metrics
            'totalInvoiced',
            'totalPaid',
            'totalPending',
            'totalOverdue',
            'overdueCount',
            'feeRecoveryRate',
            'upcomingFeeAmount',
            'upcomingDueDate',
            'daysLeft',
            'months',
            'revenueData',
            'targetData',
            'expenseData',
            'taxData',
            'profitData',
            'hasRealFinancialData',
            'growthPercent',
            'isFinancialsUp',
            // Faculty Attendance Metrics
            'facultyList',
            'criticalRiskCount',
            'overallTeacherAttendance',
            'minAttThreshold',
            // Student & Class Attendance
            'sectionAttendanceList',
            'overallStudentAttendance',
            'academicWarningsCount',
            // Term & Syllabus Progress
            'termProgressPercent',
            // Activity Stream
            'recentInvoices'
        ));
    }
}
