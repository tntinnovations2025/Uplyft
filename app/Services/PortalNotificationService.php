<?php

namespace App\Services;

use App\Events\UserPortalEvent;
use App\Models\ClassSection;
use App\Models\DailyDiary;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use App\Notifications\PortalNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PortalNotificationService
{
    /**
     * Dispatch an in-app and real-time Reverb notification to a specific user.
     */
    public static function notifyUser(
        User $user,
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
        try {
            $notification = new PortalNotification(
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

            $user->notify($notification);
        } catch (\Throwable $e) {
            Log::warning('PortalNotificationService: DB Notification failed: ' . $e->getMessage());
        }

        try {
            broadcast(new UserPortalEvent(
                userId: $user->id,
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
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::info('PortalNotificationService: Reverb broadcast skipped: ' . $e->getMessage());
        }
    }

    /**
     * Notify all enrolled students in a class section when a new Daily Diary entry is published.
     */
    public static function notifyStudentDiaryAssigned(DailyDiary $diary): void
    {
        $students = Student::withoutGlobalScopes()
            ->where('class_section_id', $diary->class_section_id)
            ->where('institute_id', $diary->institute_id)
            ->with('user')
            ->get();

        $subjectName = $diary->subject?->subject_name ?? 'Subject';
        $teacherName = $diary->teacher?->name ?? 'Teacher';

        $isTest = $diary->isTest();
        $title = $isTest 
            ? "📝 Test Announced: {$subjectName}" 
            : "📚 New Homework: {$subjectName}";

        $color = $isTest ? 'rose' : 'amber';
        $icon = $isTest ? 'file-signature' : 'book-open';

        $duePart = $diary->due_date ? " (Due: " . $diary->due_date->format('M d, Y') . ")" : "";
        $message = "{$teacherName} assigned: \"{$diary->title}\"{$duePart}.";

        $actionUrl = route('student.diary.index', ['subject_id' => $diary->subject_id]);

        foreach ($students as $student) {
            $user = $student->user ?? User::find($student->user_id);
            if ($user) {
                self::notifyUser(
                    user: $user,
                    instituteId: $diary->institute_id,
                    title: $title,
                    message: $message,
                    category: 'academics',
                    icon: $icon,
                    color: $color,
                    actionUrl: $actionUrl,
                    actorName: $teacherName,
                    actorRole: 'Teacher',
                    meta: [
                        'diary_id' => $diary->id,
                        'subject_id' => $diary->subject_id,
                        'class_section_id' => $diary->class_section_id,
                        'entry_type' => $diary->entry_type,
                        'has_attachment' => $diary->hasAttachment(),
                    ]
                );
            }
        }
    }

    /**
     * Notify student when a fee invoice is assigned / generated.
     */
    public static function notifyStudentFeeAssigned(Invoice $invoice): void
    {
        $student = $invoice->student;
        if (!$student) return;

        $user = $student->user ?? User::find($student->user_id);
        if (!$user) return;

        $currency = $invoice->currency ?? 'PKR';
        $amountStr = number_format($invoice->total_amount ?? $invoice->amount ?? 0, 2);
        $dueStr = $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Due date not set';

        self::notifyUser(
            user: $user,
            instituteId: $invoice->institute_id,
            title: "💳 New Fee Voucher Issued",
            message: "Fee voucher #{$invoice->invoice_number} ({$currency} {$amountStr}) has been issued. Due: {$dueStr}.",
            category: 'finance',
            icon: 'file-invoice-dollar',
            color: 'emerald',
            actionUrl: route('student.invoices'),
            actorName: 'Accounts Department',
            actorRole: 'Accountant',
            meta: [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $invoice->total_amount ?? $invoice->amount,
            ]
        );
    }

    /**
     * Notify student of upcoming or due fee invoice.
     */
    public static function notifyStudentFeeDueReminder(Invoice $invoice, int $daysRemaining): void
    {
        $student = $invoice->student;
        if (!$student) return;

        $user = $student->user ?? User::find($student->user_id);
        if (!$user) return;

        $currency = $invoice->currency ?? 'PKR';
        $amountStr = number_format($invoice->total_amount ?? $invoice->amount ?? 0, 2);

        if ($daysRemaining === 0) {
            $title = "⚠️ Fee Due Today!";
            $message = "Urgent: Fee voucher #{$invoice->invoice_number} ({$currency} {$amountStr}) is due today! Please clear dues to prevent late fines.";
            $color = 'rose';
        } else {
            $title = "⏰ Fee Due in {$daysRemaining} Days";
            $dueStr = $invoice->due_date ? $invoice->due_date->format('M d, Y') : '';
            $message = "Reminder: Fee voucher #{$invoice->invoice_number} ({$currency} {$amountStr}) is due on {$dueStr}.";
            $color = 'amber';
        }

        self::notifyUser(
            user: $user,
            instituteId: $invoice->institute_id,
            title: $title,
            message: $message,
            category: 'finance',
            icon: 'clock',
            color: $color,
            actionUrl: route('student.invoices'),
            actorName: 'Finance Desk',
            actorRole: 'Administration',
            meta: [
                'invoice_id' => $invoice->id,
                'days_remaining' => $daysRemaining,
            ]
        );
    }

    /**
     * Notify Accountant & Finance Desk when a student's fee invoice is past due.
     */
    public static function notifyAccountantFeeOverdue(Invoice $invoice): void
    {
        $student = $invoice->student;
        $studentName = $student ? $student->full_name : "Student #{$invoice->student_id}";
        $rollNo = $student?->roll_number ? " (Roll #{$student->roll_number})" : '';
        $classSection = $student?->classSection;
        $className = $classSection ? ($classSection->instituteClass?->custom_name . ' - ' . $classSection->section_name) : '';

        $currency = $invoice->currency ?? 'PKR';
        $amountStr = number_format($invoice->total_amount ?? $invoice->amount_pkr ?? $invoice->amount ?? 0);
        $dueStr = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'Past due';
        $daysOverdue = $invoice->due_date ? max(1, (int) \Carbon\Carbon::parse($invoice->due_date)->diffInDays(\Carbon\Carbon::today())) : 1;

        // Query all accountants / finance staff of this institute
        $financeStaff = User::withoutGlobalScopes()
            ->where(function ($q) use ($invoice) {
                $q->where('institute_id', $invoice->institute_id)
                  ->orWhere('current_institute_id', $invoice->institute_id);
            })
            ->where(function ($q) {
                $q->where('staff_role', 'accountant')
                  ->orWhere('role', 'accountant')
                  ->orWhereJsonContains('permissions', 'invoices')
                  ->orWhereJsonContains('permissions', 'accounts');
            })
            ->get();

        // Resilient fallback: If no accountant is explicitly assigned to this campus, notify institute staff/accountants
        if ($financeStaff->isEmpty()) {
            $financeStaff = User::withoutGlobalScopes()
                ->where(function ($q) {
                    $q->where('staff_role', 'accountant')
                      ->orWhere('role', 'accountant');
                })
                ->get();
        }

        foreach ($financeStaff as $accountant) {
            self::notifyUser(
                user: $accountant,
                instituteId: (int) ($accountant->institute_id ?? $invoice->institute_id),
                title: "⚠️ Overdue Fee Alert: {$studentName}",
                message: "Invoice #INV-{$invoice->id} ({$currency} {$amountStr}) for {$studentName}{$rollNo} {$className} is {$daysOverdue} day(s) past due! Due date was {$dueStr}. Follow-up required.",
                category: 'finance',
                icon: 'file-invoice-dollar',
                color: 'rose',
                actionUrl: $accountant->staffUrl('invoices'),
                actorName: 'Fee Recovery Audit',
                actorRole: 'System',
                meta: [
                    'invoice_id' => $invoice->id,
                    'student_id' => $invoice->student_id,
                    'amount' => $invoice->total_amount ?? $invoice->amount_pkr,
                    'due_date' => $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : null,
                    'days_overdue' => $daysOverdue,
                ]
            );
        }
    }

    /**
     * Notify Student when their fee invoice is past due.
     */
    public static function notifyStudentFeeOverdue(Invoice $invoice): void
    {
        $student = $invoice->student;
        if (!$student) return;

        $user = $student->user ?? User::find($student->user_id);
        if (!$user) return;

        $currency = $invoice->currency ?? 'PKR';
        $amountStr = number_format($invoice->total_amount ?? $invoice->amount_pkr ?? $invoice->amount ?? 0);
        $dueStr = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'Past due';

        self::notifyUser(
            user: $user,
            instituteId: (int) $invoice->institute_id,
            title: "🚨 Overdue Fee Notice",
            message: "Urgent: Fee voucher #INV-{$invoice->id} ({$currency} {$amountStr}) was due on {$dueStr} and remains unpaid. Please settle dues immediately to avoid late fee penalties.",
            category: 'finance',
            icon: 'triangle-exclamation',
            color: 'rose',
            actionUrl: route('student.invoices'),
            actorName: 'Accounts Department',
            actorRole: 'Accountant',
            meta: [
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total_amount ?? $invoice->amount_pkr,
                'due_date' => $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : null,
            ]
        );
    }

    /**
     * Notify student when exam datesheet is published for their section.
     */
    public static function notifyStudentDatesheetPublished($examTitleOrInstituteId, $sectionOrSectionId, ?string $optionalTitle = null, ?string $optionalSectionName = null): void
    {
        if ($sectionOrSectionId instanceof ClassSection) {
            $section = $sectionOrSectionId;
            $examTitle = (string) $examTitleOrInstituteId;
        } else {
            $section = ClassSection::find((int) $sectionOrSectionId);
            $examTitle = $optionalTitle ?? 'Term Examination';
        }

        if (!$section) return;

        $students = Student::withoutGlobalScopes()
            ->where('class_section_id', $section->id)
            ->where('institute_id', $section->institute_id)
            ->with('user')
            ->get();

        $sectionTitle = ($section->instituteClass?->custom_name ?? 'Class') . " - {$section->section_name}";

        foreach ($students as $student) {
            $user = $student->user ?? User::find($student->user_id);
            if ($user) {
                self::notifyUser(
                    user: $user,
                    instituteId: $section->institute_id,
                    title: "📅 Exam Datesheet Published",
                    message: "Official datesheet for \"{$examTitle}\" ({$sectionTitle}) is now active on your portal.",
                    category: 'exam',
                    icon: 'calendar-week',
                    color: 'purple',
                    actionUrl: route('student.datesheet'),
                    actorName: 'Examination Board',
                    actorRole: 'Administration',
                    meta: [
                        'class_section_id' => $section->id,
                        'exam_title' => $examTitle,
                    ]
                );
            }
        }
    }

    /**
     * Notify student when attendance is recorded.
     */
    public static function notifyStudentAttendanceMarked(Student $student, string $status, string $date, string $sectionName): void
    {
        $user = $student->user ?? User::find($student->user_id);
        if (!$user) return;

        $statusClean = ucfirst(strtolower($status));
        $color = match (strtolower($status)) {
            'present' => 'emerald',
            'absent' => 'rose',
            'late' => 'amber',
            default => 'blue',
        };

        $icon = match (strtolower($status)) {
            'present' => 'clipboard-check',
            'absent' => 'user-xmark',
            'late' => 'clock',
            default => 'clipboard',
        };

        self::notifyUser(
            user: $user,
            instituteId: $student->institute_id,
            title: "📋 Attendance: {$statusClean}",
            message: "You have been marked {$statusClean} for {$date} ({$sectionName}).",
            category: 'attendance',
            icon: $icon,
            color: $color,
            actionUrl: route('student.attendance'),
            actorName: 'Faculty',
            actorRole: 'Teacher',
            meta: [
                'status' => strtolower($status),
                'date' => $date,
                'section' => $sectionName,
            ]
        );
    }

    /**
     * Notify teacher on the morning of a scheduled test they assigned in the diary.
     */
    public static function notifyTeacherTestReminder(DailyDiary $diary): void
    {
        $teacher = $diary->teacher;
        if (!$teacher) return;

        $subjectName = $diary->subject?->subject_name ?? 'Subject';
        $sectionName = $diary->classSection ? (($diary->classSection->instituteClass?->custom_name ?? 'Class') . ' - ' . $diary->classSection->section_name) : 'Class';

        self::notifyUser(
            user: $teacher,
            instituteId: $diary->institute_id,
            title: "📝 Scheduled Test Today: {$subjectName}",
            message: "Morning reminder: You have a scheduled test \"{$diary->title}\" today for {$sectionName}.",
            category: 'exam',
            icon: 'file-signature',
            color: 'rose',
            actionUrl: route('teacher.diary.index', ['section_id' => $diary->class_section_id]),
            actorName: 'Academic Calendar',
            actorRole: 'System',
            meta: [
                'diary_id' => $diary->id,
                'due_date' => $diary->due_date?->toDateString(),
            ]
        );
    }

    /**
     * Notify teacher to mark daily class attendance.
     */
    public static function notifyTeacherAttendanceReminder(User $teacher, ClassSection $section): void
    {
        $sectionName = ($section->instituteClass?->custom_name ?? 'Class') . ' - ' . $section->section_name;
        $instituteId = (int) ($section->instituteClass?->institute_id ?? $teacher->institute_id ?? 1);

        self::notifyUser(
            user: $teacher,
            instituteId: $instituteId,
            title: "⏰ Attendance Reminder",
            message: "Class {$sectionName} is in session. Please submit student attendance.",
            category: 'attendance',
            icon: 'clipboard-check',
            color: 'amber',
            actionUrl: route('teacher.attendance'),
            actorName: 'Attendance Automation',
            actorRole: 'System',
            meta: [
                'class_section_id' => $section->id,
            ]
        );
    }
}
