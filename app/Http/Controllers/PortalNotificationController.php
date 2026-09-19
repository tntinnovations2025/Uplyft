<?php

namespace App\Http\Controllers;

use App\Services\PortalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class PortalNotificationController extends Controller
{
    /**
     * Get live notification feed for the authenticated student, teacher, or user.
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
            'user_id' => $user->id,
            'user_role' => $user->role,
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
     * Delete a single notification.
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
     * Trigger a test notification for the active student or teacher.
     */
    public function testBroadcast(Request $request): JsonResponse
    {
        $user = $request->user() ?: auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $instituteId = method_exists($user, 'getActiveInstituteId') ? $user->getActiveInstituteId() : ($user->institute_id ?? 1);

        if ($user->role === 'student') {
            PortalNotificationService::notifyUser(
                user: $user,
                instituteId: (int) $instituteId,
                title: '📚 Homework Update: Mathematics',
                message: 'Teacher posted in your Daily Diary: Exercise 4.2 Questions 1-5 (Due: Tomorrow).',
                category: 'academics',
                icon: 'book-open',
                color: 'amber',
                actionUrl: route('student.diary.index'),
                actorName: 'Faculty',
                actorRole: 'Teacher'
            );
        } elseif ($user->staff_role === 'accountant' || (method_exists($user, 'hasPermission') && ($user->hasPermission('accounts') || $user->hasPermission('invoices')))) {
            PortalNotificationService::notifyUser(
                user: $user,
                instituteId: (int) $instituteId,
                title: '⚠️ Overdue Fee Alert: Hamza Tariq',
                message: 'Invoice #INV-9821 (PKR 35,000) for student Hamza Tariq (Roll #102, Class 10-A) is past due! Due date was 3 days ago. Please initiate collection.',
                category: 'finance',
                icon: 'file-invoice-dollar',
                color: 'rose',
                actionUrl: method_exists($user, 'staffUrl') ? $user->staffUrl('invoices') : url('/teacher/invoices'),
                actorName: 'Fee Recovery Audit',
                actorRole: 'System'
            );
        } else {
            PortalNotificationService::notifyUser(
                user: $user,
                instituteId: (int) $instituteId,
                title: '📝 Test Reminder: Physics',
                message: 'Scheduled quiz reminder for Class 10 - Section A this morning.',
                category: 'exam',
                icon: 'file-signature',
                color: 'rose',
                actionUrl: route('teacher.diary.index'),
                actorName: 'System Engine',
                actorRole: 'Administration'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Test real-time notification broadcasted via Laravel Reverb!',
        ]);
    }
}
