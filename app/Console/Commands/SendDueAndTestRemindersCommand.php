<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\DailyDiary;
use App\Models\Invoice;
use App\Models\User;
use App\Services\PortalNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDueAndTestRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uplyft:send-reminders {--force : Force send reminders even if already processed today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send fee due date reminders, test morning alerts, and teacher attendance reminders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();
        $inTwoDays = Carbon::today()->addDays(2);

        $this->info("🚀 Running UPLYFT Automated Reminders Engine for {$today->toDateString()}...");

        // 1. Fee Reminders: Due in 2 Days
        $invoicesInTwoDays = Invoice::withoutGlobalScopes()
            ->whereDate('due_date', $inTwoDays->toDateString())
            ->whereIn('status', ['unpaid', 'pending', 'issued'])
            ->with('student')
            ->get();

        $countTwoDays = 0;
        foreach ($invoicesInTwoDays as $inv) {
            PortalNotificationService::notifyStudentFeeDueReminder($inv, 2);
            $countTwoDays++;
        }
        $this->line("  ✓ Sent {$countTwoDays} fee due-in-2-days reminder(s).");

        // 2. Fee Reminders: Due Today
        $invoicesToday = Invoice::withoutGlobalScopes()
            ->whereDate('due_date', $today->toDateString())
            ->whereIn('status', ['unpaid', 'pending', 'issued', 'overdue'])
            ->with('student')
            ->get();

        $countDueToday = 0;
        foreach ($invoicesToday as $inv) {
            PortalNotificationService::notifyStudentFeeDueReminder($inv, 0);
            $countDueToday++;
        }
        $this->line("  ✓ Sent {$countDueToday} fee due-today reminder(s).");

        // 3. Test & Assignment Morning Reminders
        $testReminders = DailyDiary::withoutGlobalScopes()
            ->where('reminder_morning', true)
            ->whereDate('due_date', $today->toDateString())
            ->whereNull('reminder_sent_at')
            ->with(['teacher', 'subject', 'classSection'])
            ->get();

        $countTests = 0;
        foreach ($testReminders as $diary) {
            PortalNotificationService::notifyTeacherTestReminder($diary);
            $diary->update(['reminder_sent_at' => now()]);
            $countTests++;
        }
        $this->line("  ✓ Sent {$countTests} morning test reminder(s) to teachers.");

        // 4. Teacher Attendance Reminder
        $unmarkedSections = ClassSection::withoutGlobalScopes()
            ->whereNotNull('class_incharge_id')
            ->with(['classIncharge', 'instituteClass'])
            ->get();

        $countAttReminders = 0;
        foreach ($unmarkedSections as $section) {
            $hasAttendance = Attendance::withoutGlobalScopes()
                ->where('date', $today->toDateString())
                ->whereHas('student', function ($q) use ($section) {
                    $q->where('class_section_id', $section->id);
                })
                ->exists();

            if (!$hasAttendance && $section->classIncharge) {
                PortalNotificationService::notifyTeacherAttendanceReminder($section->classIncharge, $section);
                $countAttReminders++;
            }
        }
        $this->line("  ✓ Sent {$countAttReminders} teacher attendance reminder(s).");

        // 5. Overdue Fee Alerts: Invoices past due date that remain unpaid
        $overdueInvoices = Invoice::withoutGlobalScopes()
            ->whereDate('due_date', '<', $today->toDateString())
            ->whereIn('status', ['unpaid', 'pending', 'issued', 'overdue'])
            ->with(['student', 'classSection'])
            ->get();

        $countOverdueAlerts = 0;
        foreach ($overdueInvoices as $inv) {
            PortalNotificationService::notifyAccountantFeeOverdue($inv);
            PortalNotificationService::notifyStudentFeeOverdue($inv);
            \App\Services\PrincipalNotificationService::notifyFeeOverdue($inv);
            $countOverdueAlerts++;
        }
        $this->line("  ✓ Sent {$countOverdueAlerts} overdue fee alert(s) to Accountants, Students & Principal.");

        $this->info("✅ All reminders dispatched successfully.");
        return Command::SUCCESS;
    }
}
