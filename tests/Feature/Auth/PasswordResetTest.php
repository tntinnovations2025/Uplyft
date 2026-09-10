<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_password_reset_request_starts_otp_flow(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['credential' => $user->email])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $notification = $user->passwordResetNotifications()
            ->pending()
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame(6, strlen((string) $notification->otp));
        $this->assertNotNull($notification->cancellation_token);
    }

    public function test_password_can_be_reset_with_valid_otp(): void
    {
        $user = User::factory()->create();
        $oldPassword = $user->password;

        $this->post('/forgot-password', ['credential' => $user->email])
            ->assertRedirect();

        $notification = $user->passwordResetNotifications()->pending()->first();
        $this->assertNotNull($notification);

        $this->post('/password/verify-otp', [
            'credential' => $user->email,
            'otp' => $notification->otp,
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ])->assertRedirect(route('login'));

        $this->assertNotSame($oldPassword, $user->refresh()->password);
        $this->assertTrue($this->hasher()->check('NewPass123!', $user->password));
    }

    public function test_password_reset_rejects_invalid_otp(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['credential' => $user->email])
            ->assertRedirect();

        $this->post('/password/verify-otp', [
            'credential' => $user->email,
            'otp' => '000000',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ])->assertSessionHasErrors('otp');

        $this->assertNotSame('NewPass123!', $user->refresh()->password);
    }

    private function hasher()
    {
        return app('hash');
    }
}
