<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Services\PrincipalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Get live notification feed for the authenticated principal / admin.
     */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user() ?: auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'notifications' => [], 'unread_count' => 0], 401);
        }

        $query = $user->notifications()->latest();

        $category = $request->input('category');
        if ($category && $category !== 'all') {
            $query->where('data->category', $category);
        }

        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        $notifications = $query->take(30)->get()->map(function (DatabaseNotification $n) {
            $data = $n->data ?? [];
            return [
                'id' => $n->id,
                'title' => $data['title'] ?? 'System Update',
                'message' => $data['message'] ?? '',
                'category' => $data['category'] ?? 'general',
                'icon' => $data['icon'] ?? 'bell',
                'color' => $data['color'] ?? 'amber',
                'action_url' => $data['action_url'] ?? null,
                'actor_name' => $data['actor_name'] ?? null,
                'actor_role' => $data['actor_role'] ?? null,
                'meta' => $data['meta'] ?? [],
                'read_at' => $n->read_at ? $n->read_at->toIso8601String() : null,
                'created_at' => $n->created_at->toIso8601String(),
                'created_at_human' => $n->created_at->diffForHumans(),
            ];
        });

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'institute_id' => method_exists($user, 'getActiveInstituteId') ? $user->getActiveInstituteId() : $user->institute_id,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user() ?: auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user() ?: auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * Delete / Dismiss a single notification.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user() ?: auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user->notifications()->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Trigger a test notification (useful for testing live Reverb connection).
     */
    public function testBroadcast(Request $request): JsonResponse
    {
        $user = $request->user() ?: auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $instituteId = method_exists($user, 'getActiveInstituteId') ? $user->getActiveInstituteId() : $user->institute_id;

        $type = $request->input('type', 'fee');

        switch ($type) {
            case 'fee':
                PrincipalNotificationService::notify(
                    instituteId: (int) $instituteId,
                    title: 'Fee Marked as Paid',
                    message: 'Accountant Mark paid Invoice #INV-882 (PKR 45,000) for student Hamza Tariq.',
                    category: 'finance',
                    icon: 'receipt',
                    color: 'emerald',
                    actionUrl: route('principal.invoices.index'),
                    actorName: 'Mark Accountant',
                    actorRole: 'Accountant'
                );
                break;

            case 'student':
                PrincipalNotificationService::notify(
                    instituteId: (int) $instituteId,
                    title: 'New Student Registered',
                    message: 'New student Ayesha Noor (Roll: STD-2026-0042) registered into Class 9 - Section A.',
                    category: 'student',
                    icon: 'user-plus',
                    color: 'blue',
                    actionUrl: route('principal.students.index'),
                    actorName: 'Admissions Desk',
                    actorRole: 'Staff'
                );
                break;

            case 'attendance':
                PrincipalNotificationService::notify(
                    instituteId: (int) $instituteId,
                    title: 'Class Attendance Marked',
                    message: 'Teacher Sarah Jenkins marked daily attendance for Class 10 - Section B (28 students).',
                    category: 'attendance',
                    icon: 'clipboard-check',
                    color: 'amber',
                    actionUrl: route('principal.attendance-settings.index'),
                    actorName: 'Sarah Jenkins',
                    actorRole: 'Teacher'
                );
                break;

            case 'exam':
                PrincipalNotificationService::notify(
                    instituteId: (int) $instituteId,
                    title: 'Test Marks Submitted',
                    message: 'Teacher Dr. Usman submitted midterm exam marks for Physics (Class 10 - Sec A).',
                    category: 'exam',
                    icon: 'file-signature',
                    color: 'purple',
                    actionUrl: route('lms.test-results.index'),
                    actorName: 'Dr. Usman',
                    actorRole: 'Teacher'
                );
                break;

            default:
                PrincipalNotificationService::notify(
                    instituteId: (int) $instituteId,
                    title: 'System Operational Alert',
                    message: 'A real-time operation was successfully executed via Laravel Reverb.',
                    category: 'general',
                    icon: 'bell',
                    color: 'amber',
                    actionUrl: route('principal.dashboard'),
                    actorName: 'System Engine',
                    actorRole: 'Administration'
                );
                break;
        }

        return response()->json([
            'success' => true,
            'message' => "Test '{$type}' notification dispatched via Laravel Reverb!",
        ]);
    }
}
