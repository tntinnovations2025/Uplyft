<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\StudentLoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the unified multi-role portal login view (Principal, Faculty, Student).
     */
    public function create(Request $request): View
    {
        $selectedRole = $request->query('role');
        if (! in_array($selectedRole, ['principal', 'faculty', 'student'], true)) {
            $selectedRole = null;
        }

        return view('auth.login', [
            'selectedRole' => $selectedRole,
            'studentPortal' => ($selectedRole === 'student'),
        ]);
    }

    /**
     * Display the Global Admin portal dedicated login view.
     */
    public function createGlobalAdmin(): View
    {
        return view('auth.global-admin-login');
    }

    /**
     * Display the Principal portal on the unified login view.
     */
    public function createPrincipal(): View
    {
        return view('auth.login', [
            'selectedRole' => 'principal',
            'studentPortal' => false,
        ]);
    }

    /**
     * Display the Faculty portal on the unified login view.
     */
    public function createFaculty(): View
    {
        return view('auth.login', [
            'selectedRole' => 'faculty',
            'studentPortal' => false,
        ]);
    }

    /**
     * Display the Student portal on the unified login view.
     */
    public function createStudent(): View
    {
        return view('auth.login', [
            'selectedRole' => 'student',
            'studentPortal' => true,
        ]);
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
                ->withInput($request->only('credential', 'remember', 'role'))
                ->withErrors(['credential' => 'Wrong credentials!']);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route('student.dashboard');
    }

    /**
     * Handle Faculty portal login with strict role verification.
     */
    public function storeFaculty(LoginRequest $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->authenticate();

        $user = Auth::user();
        if (! $user->isTeacher()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('faculty.login')
                ->withInput($request->only('credential', 'remember', 'role'))
                ->withErrors(['credential' => 'Wrong credentials!']);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect($user->dashboardRoute());
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
        $user = Auth::user();

        // Enforce role matching if role was selected on the gateway
        $targetRole = $request->getTargetRole();
        if ($targetRole !== null) {
            $matches = match ($targetRole) {
                User::ROLE_PRINCIPAL    => $user->isPrincipal(),
                User::ROLE_TEACHER      => $user->isTeacher(),
                User::ROLE_STUDENT      => $user->isStudent(),
                User::ROLE_GLOBAL_ADMIN => $user->isGlobalAdmin(),
                default                 => false,
            };

            if (! $matches) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withInput($request->only('credential', 'remember', 'role'))
                    ->withErrors(['credential' => 'Wrong credentials!']);
            }
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

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
                ->withInput($request->only('credential', 'remember'))
                ->withErrors(['credential' => 'Wrong credentials!']);
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
        if (! $user->isPrincipal()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('principal.login')
                ->withInput($request->only('credential', 'remember', 'role'))
                ->withErrors(['credential' => 'Wrong credentials!']);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route('principal.dashboard');
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
