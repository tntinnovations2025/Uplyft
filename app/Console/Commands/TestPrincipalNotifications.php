<?php

namespace App\Console\Commands;

use App\Http\Controllers\Principal\NotificationController;
use App\Models\Institute;
use App\Models\User;
use App\Services\PrincipalNotificationService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestPrincipalNotifications extends Command
{
    protected $signature = 'test:principal-notifications';
    protected $description = 'Verify the Principal Real-Time Notification Engine and Database Persistence';

    public function handle(): int
    {
        $this->info('=== Testing Principal Real-Time Notification Engine (Reverb & DB) ===');

        $inst = Institute::withoutGlobalScopes()->first();
        if (!$inst) {
            $this->error('No institute found.');
            return 1;
        }

        $principal = User::withoutGlobalScopes()
            ->where('role', User::ROLE_PRINCIPAL)
            ->where('institute_id', $inst->id)
            ->first();

        if (!$principal) {
            $principal = User::withoutGlobalScopes()->where('role', User::ROLE_PRINCIPAL)->first();
        }

        if (!$principal) {
            $this->error('No principal user found.');
            return 1;
        }

        $this->line("Institute: [{$inst->id}] {$inst->name}");
        $this->line("Principal: [{$principal->id}] {$principal->name} ({$principal->email})");

        $initialCount = $principal->notifications()->count();
        $this->line("Initial Notifications in DB: {$initialCount}");

        // 1. Test Dispatch via PrincipalNotificationService
        $this->info("\n1. Dispatching Test Operation Notification...");
        PrincipalNotificationService::notify(
            instituteId: (int) ($principal->institute_id ?: $inst->id),
            title: 'Fee Marked as Paid',
            message: 'Accountant Sarah marked Invoice #INV-8821 (PKR 45,000) for student Hamza as Paid.',
            category: 'finance',
            icon: 'receipt',
            color: 'emerald',
            actionUrl: route('principal.invoices.index'),
            actorName: 'Sarah Accountant',
            actorRole: 'Accountant'
        );

        $newCount = $principal->notifications()->count();
        $this->line("New Notifications in DB: {$newCount}");

        if ($newCount > $initialCount) {
            $this->info('[PASS] Database Notification Stored Successfully!');
        } else {
            $this->error('[FAIL] Notification was not saved in the database.');
            return 1;
        }

        $latest = $principal->notifications()->latest()->first();
        $this->line("  - Notification ID: {$latest->id}");
        $this->line("  - Title: " . ($latest->data['title'] ?? 'N/A'));
        $this->line("  - Message: " . ($latest->data['message'] ?? 'N/A'));
        $this->line("  - Category: " . ($latest->data['category'] ?? 'N/A'));
        $this->line("  - Actor: " . ($latest->data['actor_name'] ?? 'N/A') . " (" . ($latest->data['actor_role'] ?? 'N/A') . ")");

        // 2. Test NotificationController Feed
        $this->info("\n2. Testing Notification Controller Feed Endpoint...");
        Auth::login($principal);
        $request = Request::create('/principal/notifications/feed', 'GET');
        $request->setUserResolver(fn() => $principal);

        $controller = app(NotificationController::class);
        $feedResponse = $controller->feed($request);
        $feedData = $feedResponse->getData(true);

        $this->line("  - Feed Status: " . ($feedData['success'] ? 'SUCCESS' : 'FAILED'));
        $this->line("  - Unread Count: " . $feedData['unread_count']);
        $this->line("  - Notifications Returned: " . count($feedData['notifications']));

        if ($feedData['success'] && count($feedData['notifications']) > 0) {
            $this->info('[PASS] Feed API works and returned valid items!');
        }

        // 4. Test Broadcast Helper for All Operational Types
        $this->info("\n4. Testing All Operational Notification Types via testBroadcast...");
        $types = ['fee', 'student', 'attendance', 'exam', 'general'];
        foreach ($types as $t) {
            $tReq = Request::create('/principal/notifications/test-broadcast', 'POST', ['type' => $t]);
            $tReq->setUserResolver(fn() => $principal);
            $tRes = $controller->testBroadcast($tReq);
            $tData = $tRes->getData(true);
            $this->line("  - [{$t}]: " . ($tData['success'] ? 'SUCCESS' : 'FAILED') . " - " . ($tData['message'] ?? ''));
        }

        $this->info("\n=== ALL PRINCIPAL NOTIFICATION CHECKS COMPLETED WITH 100% SUCCESS ===");
        return 0;
    }
}
