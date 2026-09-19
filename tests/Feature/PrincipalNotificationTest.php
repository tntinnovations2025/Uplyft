<?php

namespace Tests\Feature;

use App\Events\PrincipalOperationEvent;
use App\Models\ClassSection;
use App\Models\Institute;
use App\Models\InstituteClass;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use App\Notifications\PrincipalOperationNotification;
use App\Services\PrincipalNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PrincipalNotificationTest extends TestCase
{
    public function test_principal_notification_persists_to_database_and_broadcasts_event(): void
    {
        Notification::fake();
        Event::fake([PrincipalOperationEvent::class]);

        $uniq = uniqid();
        $institute = Institute::create([
            'name' => 'Apex High ' . $uniq,
            'code' => 'APEX-' . $uniq,
            'email' => "apex_{$uniq}@test.com",
            'phone' => '1234567890',
            'address' => 'Test Street',
            'is_active' => true,
        ]);

        $principal = User::create([
            'name' => 'Principal Skinner',
            'email' => "skinner_{$uniq}@test.com",
            'password' => 'password123',
            'role' => User::ROLE_PRINCIPAL,
            'institute_id' => $institute->id,
        ]);

        PrincipalNotificationService::notify(
            instituteId: $institute->id,
            title: 'Fee Marked as Paid',
            message: 'Invoice #101 was paid.',
            category: 'finance',
            icon: 'receipt',
            color: 'emerald'
        );

        Notification::assertSentTo(
            $principal,
            PrincipalOperationNotification::class,
            function ($notification) use ($institute) {
                return $notification->instituteId === $institute->id
                    && $notification->category === 'finance'
                    && $notification->title === 'Fee Marked as Paid';
            }
        );

        Event::assertDispatched(
            PrincipalOperationEvent::class,
            function ($event) use ($institute) {
                return $event->instituteId === $institute->id
                    && $event->title === 'Fee Marked as Paid'
                    && $event->category === 'finance';
            }
        );
    }

    public function test_notification_controller_feed_endpoint(): void
    {
        $uniq = uniqid();
        $institute = Institute::create([
            'name' => 'Beaconhouse ' . $uniq,
            'code' => 'BSS-' . $uniq,
            'email' => "bss_{$uniq}@test.com",
            'phone' => '1234567890',
            'address' => 'Test Street',
            'is_active' => true,
        ]);

        $principal = User::create([
            'name' => 'Principal Skinner',
            'email' => "skinner2_{$uniq}@test.com",
            'password' => 'password123',
            'role' => User::ROLE_PRINCIPAL,
            'institute_id' => $institute->id,
        ]);

        $principal->notify(new PrincipalOperationNotification(
            instituteId: $institute->id,
            title: 'New Student Registered',
            message: 'Student John Doe admitted into Grade 9-A',
            category: 'student',
            icon: 'user-plus',
            color: 'blue'
        ));

        $response = $this->actingAs($principal)->getJson(route('principal.notifications.feed'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'unread_count',
            'notifications',
        ]);
        $response->assertJson([
            'success' => true,
            'unread_count' => 1,
        ]);
    }
}
