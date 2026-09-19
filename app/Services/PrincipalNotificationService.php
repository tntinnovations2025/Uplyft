<?php

namespace App\Services;

use App\Events\PrincipalOperationEvent;
use App\Models\Assessment;
use App\Models\ClassSection;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use App\Notifications\PrincipalOperationNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PrincipalNotificationService
{
    /**
     * Dispatch an operational notification to all Principals & Delegated Admins of an institute.
     */
    public static function notify(
        int $instituteId,
        string $title,
        string $message,
        string $category = 'general',
        string $icon = 'bell',
        string $color = 'amber',
        ?string $actionUrl = null,
        ?string $actorName = null,
        ?string $actorRole = null,
        array $meta = []
    ): void {
        // 1. Persist to Database for all Principals / Delegated Admins in the Institute
        try {
            $principals = User::withoutGlobalScopes()
                ->where('institute_id', $instituteId)
                ->where(function ($q) {
                    $q->where('role', User::ROLE_PRINCIPAL)
                      ->orWhere('is_delegated_admin', true);
                })
                ->get();

            $notification = new PrincipalOperationNotification(
                instituteId: $instituteId,
                title: $title,
                message: $message,
                category: $category,
                icon: $icon,
                color: $color,
                actionUrl: $actionUrl,
                actorName: $actorName,
                actorRole: $actorRole,
                meta: $meta
            );

            if ($principals->isNotEmpty()) {
                Notification::send($principals, $notification);
            }
        } catch (\Throwable $e) {
            Log::warning('PrincipalNotificationService: DB Notification failed: ' . $e->getMessage(), [
                'institute_id' => $instituteId,
                'title' => $title,
            ]);
        }

        // 2. Real-time WebSocket Broadcast over Reverb
        try {
            broadcast(new PrincipalOperationEvent(
                instituteId: $instituteId,
                title: $title,
                message: $message,
                category: $category,
                icon: $icon,
                color: $color,
                actionUrl: $actionUrl,
                actorName: $actorName,
                actorRole: $actorRole,
                meta: $meta
            ));
        } catch (\Throwable $e) {
            Log::info('PrincipalNotificationService: Reverb broadcast skipped (server offline or async queue): ' . $e->getMessage(), [
                'institute_id' => $instituteId,
                'title' => $title,
            ]);
        }
    }

    /**
     * Helper: Accountant or Staff marked an invoice as Paid.
     */
    public static function notifyFeePaid(Invoice $invoice, ?User $actor = null): void
    {
        $actorName = $actor ? $actor->name : 'Accountant';
        $actorRole = $actor ? (ucfirst($actor->staff_role ?? 'Accountant')) : 'Accountant';
        $studentName = $invoice->student ? $invoice->student->full_name : "Student #{$invoice->student_id}";
        $amount = number_format($invoice->amount_pkr);

        $title = 'Fee Marked as Paid';
        $message = "{$actorRole} {$actorName} marked Invoice #INV-{$invoice->id} (PKR {$amount}) for {$studentName} as Paid.";

        self::notify(
            instituteId: (int) $invoice->institute_id,
            title: $title,
            message: $message,
            category: 'finance',
            icon: 'receipt',
            color: 'emerald',
            actionUrl: route('principal.invoices.index', ['search' => $invoice->student?->roll_number ?? $invoice->id]),
            actorName: $actorName,
            actorRole: $actorRole,
            meta: [
                'invoice_id' => $invoice->id,
                'amount_pkr' => $invoice->amount_pkr,
                'fee_month' => $invoice->fee_month,
                'student_id' => $invoice->student_id,
            ]
        );
    }

    /**
     * Helper: Fee invoice is overdue (past due date and unpaid).
     */
    public static function notifyFeeOverdue(Invoice $invoice): void
    {
        $studentName = $invoice->student ? $invoice->student->full_name : "Student #{$invoice->student_id}";
        $amount = number_format($invoice->amount_pkr);
        $dueStr = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'Past due';
        $daysOverdue = $invoice->due_date ? max(1, (int) \Carbon\Carbon::parse($invoice->due_date)->diffInDays(\Carbon\Carbon::today())) : 1;

        self::notify(
            instituteId: (int) $invoice->institute_id,
            title: "Overdue Fee Alert: {$studentName}",
            message: "Invoice #INV-{$invoice->id} (PKR {$amount}) for {$studentName} is {$daysOverdue} day(s) overdue (Due: {$dueStr}). Accountant flagged for follow-up.",
            category: 'finance',
            icon: 'file-invoice-dollar',
            color: 'rose',
            actionUrl: route('principal.invoices.index', ['status' => 'unpaid']),
            actorName: 'Fee Recovery System',
            actorRole: 'Automated Audit',
            meta: [
                'invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'amount_pkr' => $invoice->amount_pkr,
                'due_date' => $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : null,
                'days_overdue' => $daysOverdue,
            ]
        );
    }

    /**
     * Helper: New Student registered / admitted into the institute.
     */
    public static function notifyStudentRegistered(Student $student, ?User $actor = null): void
    {
        $actorName = $actor ? $actor->name : 'Admissions Desk';
        $actorRole = $actor ? (ucfirst($actor->staff_role ?? $actor->role)) : 'Administration';
        $studentName = $student->full_name;
        $rollNo = $student->roll_number ?: 'Pending Roll No';
        $className = $student->classSection ? ($student->classSection->instituteClass?->custom_name . ' - ' . $student->classSection->section_name) : 'Intake Roster';

        $title = 'New Student Registered';
        $message = "New student {$studentName} (Roll: {$rollNo}) was registered by {$actorRole} {$actorName} into {$className}.";

        self::notify(
            instituteId: (int) $student->institute_id,
            title: $title,
            message: $message,
            category: 'student',
            icon: 'user-plus',
            color: 'blue',
            actionUrl: route('principal.students.show', $student->id),
            actorName: $actorName,
            actorRole: $actorRole,
            meta: [
                'student_id' => $student->id,
                'roll_number' => $rollNo,
            ]
        );
    }

    /**
     * Helper: Teacher or Staff recorded daily/subject attendance for a class section.
     */
    public static function notifyAttendanceMarked(int $instituteId, ClassSection $section, int $studentCount, string $date, ?User $actor = null): void
    {
        $actorName = $actor ? $actor->name : 'Teacher';
        $actorRole = $actor ? (ucfirst($actor->staff_role ?? 'Teacher')) : 'Teacher';
        $className = $section->instituteClass ? ($section->instituteClass->custom_name . ' - Sec ' . $section->section_name) : ("Section #" . $section->id);
        $dateFormatted = date('M d, Y', strtotime($date));

        $title = 'Class Attendance Marked';
        $message = "{$actorRole} {$actorName} marked attendance for {$className} ({$studentCount} students) for {$dateFormatted}.";

        self::notify(
            instituteId: $instituteId,
            title: $title,
            message: $message,
            category: 'attendance',
            icon: 'clipboard-check',
            color: 'amber',
            actionUrl: route('principal.attendance-settings.index'),
            actorName: $actorName,
            actorRole: $actorRole,
            meta: [
                'section_id' => $section->id,
                'student_count' => $studentCount,
                'date' => $date,
            ]
        );
    }

    /**
     * Helper: Teacher submitted test results / marksheet.
     */
    public static function notifyMarksSubmitted(Assessment $assessment, ?User $actor = null): void
    {
        $actorName = $actor ? $actor->name : ($assessment->creator?->name ?? 'Teacher');
        $actorRole = $actor ? (ucfirst($actor->staff_role ?? 'Teacher')) : 'Teacher';
        $subjectName = $assessment->subject?->subject_name ?? 'Subject Test';
        $className = $assessment->classSection ? ($assessment->classSection->instituteClass?->custom_name . ' - ' . $assessment->classSection->section_name) : 'Class';

        $title = 'Test Marks Submitted';
        $message = "{$actorRole} {$actorName} submitted marks for '{$assessment->title}' ({$subjectName} — {$className}).";

        $instituteId = $assessment->classSection?->instituteClass?->institute_id
            ?? ($actor?->institute_id ?? 1);

        self::notify(
            instituteId: (int) $instituteId,
            title: $title,
            message: $message,
            category: 'exam',
            icon: 'file-signature',
            color: 'purple',
            actionUrl: route('lms.test-results.marksheet', ['id' => $assessment->id]),
            actorName: $actorName,
            actorRole: $actorRole,
            meta: [
                'assessment_id' => $assessment->id,
                'subject_id' => $assessment->subject_id,
                'total_marks' => $assessment->total_marks,
            ]
        );
    }

    /**
     * Helper: Expense or Financial Transaction logged.
     */
    public static function notifyExpenseRecorded(FinancialTransaction $transaction, ?User $actor = null): void
    {
        $actorName = $actor ? $actor->name : 'Accountant';
        $actorRole = $actor ? (ucfirst($actor->staff_role ?? 'Accountant')) : 'Accountant';
        $amount = number_format($transaction->amount);
        $headName = $transaction->accountHead?->name ?? 'General Expense';

        $title = 'Financial Expense Logged';
        $message = "{$actorRole} {$actorName} recorded an expense of PKR {$amount} under '{$headName}' ({$transaction->title}).";

        self::notify(
            instituteId: (int) $transaction->institute_id,
            title: $title,
            message: $message,
            category: 'finance',
            icon: 'money-bill-wave',
            color: 'rose',
            actionUrl: route('principal.accounts.index', ['tab' => 'ledger']),
            actorName: $actorName,
            actorRole: $actorRole,
            meta: [
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
            ]
        );
    }

    /**
     * Helper: Staff / Teacher onboarded.
     */
    public static function notifyStaffOnboarded(User $staff, ?User $actor = null): void
    {
        $actorName = $actor ? $actor->name : 'Administration';
        $actorRole = $actor ? (ucfirst($actor->staff_role ?? $actor->role)) : 'Principal';
        $roleName = ucfirst($staff->staff_role ?: $staff->role);

        $title = 'Staff Member Onboarded';
        $message = "New {$roleName} '{$staff->name}' ({$staff->email}) was onboarded by {$actorRole} {$actorName}.";

        self::notify(
            instituteId: (int) $staff->institute_id,
            title: $title,
            message: $message,
            category: 'staff',
            icon: 'user-tie',
            color: 'cyan',
            actionUrl: route('principal.staff.index'),
            actorName: $actorName,
            actorRole: $actorRole,
            meta: [
                'staff_id' => $staff->id,
                'staff_role' => $staff->staff_role,
            ]
        );
    }
}
