<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\StudentLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the portal login view based on port / route.
     */
    public function create(Request $request): View
    {
        $port = $request->getPort();

        if ($port == 8000) {
            return view('auth.global-admin-login');
        }

        if ($port == 8001) {
            return view('auth.principal-login');
        }

        // Port 8003 → Student portal (email-only dedicated login).
        if ($port == 8003) {
            return view('auth.login', ['studentPortal' => true]);
        }

        return view('auth.login');
    }

    /**
     * Display the Global Admin portal dedicated login view.
     */
    public function createGlobalAdmin(): View
    {
        return view('auth.global-admin-login');
    }

    /**
     * Display the Principal portal dedicated login view.
     */
    public function createPrincipal(): View
    {
        return view('auth.principal-login');
    }

    /**
     * Display the Student portal dedicated login view (email-only).
     */
    public function createStudent(): View
    {
        return view('auth.login', ['studentPortal' => true]);
    }

    /**
     * Handle Student portal login with strict email-only + role verification.
     */
    public function storeStudent(StudentLoginRequest $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->authenticate();

        $user = Auth::user();
        if (! $user->isStudent()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('student.login')
                ->withErrors(['credential' => 'Access Denied: This login portal is reserved exclusively for Students.']);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route('student.dashboard');
    }

    /**
     * Handle standard authentication request (Student / Teacher LMS).
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->authenticate();
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        $user = Auth::user();

        return match ($user->role) {
            'global_admin' => redirect()->route('global-admin.dashboard'),
            'principal' => redirect()->route('principal.dashboard'),
            'teacher' => redirect($user->dashboardRoute()),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }

    /**
     * Handle Global Admin dedicated login request with strict role verification.
     */
    public function storeGlobalAdmin(LoginRequest $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->authenticate();

        $user = Auth::user();
        if (! $user->isGlobalAdmin()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('global-admin.login')
                ->withErrors(['credential' => 'Access Denied: This login portal is reserved exclusively for Global SaaS Administrators.']);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route('global-admin.dashboard');
    }

    /**
     * Handle Principal dedicated login request with strict role verification.
     */
    public function storePrincipal(LoginRequest $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->authenticate();

        $user = Auth::user();
        if (! $user->isPrincipal() && ! $user->hasAnyDelegatedPermission() && ! $user->isGlobalAdmin()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('principal.login')
                ->withErrors(['credential' => 'Access Denied: This login portal is reserved exclusively for Institute Principals and Delegated Administrators.']);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        if ($user->isPrincipal() || $user->isGlobalAdmin()) {
            return redirect()->route('principal.dashboard');
        }

        return redirect($user->dashboardRoute());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
