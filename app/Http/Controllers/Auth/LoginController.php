<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate user with login_id (Roll No / Employee ID / email) and password.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginId  = $request->input('login_id');
        $password = $request->input('password');

        // Attempt with login_id field first (e.g. STD-2026-0001)
        $loginAttemptWithId = Auth::attempt(
            ['login_id' => $loginId, 'password' => $password],
            $request->boolean('remember')
        );

        // Fallback: attempt with email address
        if (!$loginAttemptWithId) {
            $loginAttemptWithEmail = Auth::attempt(
                ['email' => $loginId, 'password' => $password],
                $request->boolean('remember')
            );

            if (!$loginAttemptWithEmail) {
                return back()->withErrors([
                    'login_id' => 'The provided credentials do not match our records.',
                ])->withInput($request->only('login_id'));
            }
        }

        $request->session()->regenerate();

        // Dynamic role-based redirect
        $user = Auth::user();
        return redirect($user->dashboardRoute())
            ->with('success', "Welcome back, {$user->name}!");
    }

    /**
     * Log out the current user and invalidate the session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
