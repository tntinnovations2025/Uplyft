<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\RoleDefaultAuthority;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectSection;
use App\Models\User;
use App\Services\TimetableGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(
        protected TimetableGeneratorService $timetableGenerator
    ) {}

    /**
     * Faculty & Staff Governance / Roster Page
     */
    public function index(): View
    {
        $user = auth()->user();
        if (! $user->isPrincipal() && ! $user->isGlobalAdmin() && ! $user->hasPermission('staff', 'view') && ! $user->hasPermission('directory', 'view')) {
            abort(403, 'Unauthorized: You do not have access rights to the Faculty & Staff Directory.');
        }

        $instituteId = $user->institute_id;
        $institute = $user->institute;

        RoleDefaultAuthority::seedDefaultsForInstitute($instituteId);
        $roleAuthorities = RoleDefaultAuthority::where('institute_id', $instituteId)->orderBy('id', 'asc')->get();

        $featureToggles = $institute ? ($institute->featureToggles ?? new \App\Models\InstituteFeatureToggle()) : new \App\Models\InstituteFeatureToggle();
        $activeFeatureKeys = collect(\App\Models\InstituteFeatureToggle::$featureKeys)
            ->filter(fn ($k) => (bool) ($featureToggles->{$k} ?? false))
            ->values()
            ->toArray();

        $staffMembers = User::whereIn('institute_id', $user->authorizedCampusIds() ?? [])
            ->whereIn('role', [User::ROLE_TEACHER, User::ROLE_PRINCIPAL])
            ->with(['teacherProfile', 'creator'])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('principal.staff.index', compact('staffMembers', 'roleAuthorities', 'featureToggles', 'activeFeatureKeys'));
    }

    /**
     * Dedicated Assign Subject & Teacher Page
     */
    public function assignmentsIndex(): View
    {
        $instituteId = auth()->user()->institute_id;

        $staffMembers = User::whereIn('institute_id', auth()->user()->authorizedCampusIds() ?? [])
            ->whereIn('role', [User::ROLE_TEACHER, User::ROLE_PRINCIPAL])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        $subjects = Subject::whereHas('instituteClass', function ($q) use ($instituteId, $activeTerm) {
            $q->where('institute_id', $instituteId);
            if ($activeTerm) {
                $q->where('academic_term_id', $activeTerm->id);
            }
        })
            ->with('instituteClass')
            ->get();

        $sections = ClassSection::whereHas('instituteClass', function ($q) use ($instituteId, $activeTerm) {
            $q->where('institute_id', $instituteId);
            if ($activeTerm) {
                $q->where('academic_term_id', $activeTerm->id);
            }
        })
            ->with(['instituteClass.subjects'])
            ->get();

        $assignments = collect();
        if ($activeTerm) {
            $assignments = TeacherSubjectSection::where('academic_term_id', $activeTerm->id)
                ->with(['teacher', 'subject', 'section.instituteClass'])
                ->get();
        }

        // Build comprehensive list of class section subject offerings
        $offerings = collect();
        $assignmentsMap = [];

        foreach ($sections as $sec) {
            if ($sec->instituteClass && $sec->instituteClass->subjects) {
                foreach ($sec->instituteClass->subjects as $sub) {
                    $existing = $assignments->first(function ($a) use ($sec, $sub) {
                        return $a->class_section_id == $sec->id && $a->subject_id == $sub->id;
                    });

                    $key = "{$sec->id}_{$sub->id}";
                    if ($existing && $existing->teacher) {
                        $teacherRoleStr = $existing->teacher->staff_role ?? ucfirst($existing->teacher->role);
                        $assignmentsMap[$key] = [
                            'teacher_id' => $existing->teacher_id,
                            'teacher_name' => "{$existing->teacher->name} ({$teacherRoleStr})",
                            'assignment_id' => $existing->id,
                        ];
                    }

                    $offerings->push((object) [
                        'section' => $sec,
                        'subject' => $sub,
                        'assignment' => $existing,
                        'teacher' => $existing?->teacher,
                        'is_assigned' => ! is_null($existing) && ! is_null($existing->teacher),
                    ]);
                }
            }
        }

        // Group offerings by Class Name
        $byClass = $offerings->groupBy(function ($off) {
            return $off->section->instituteClass->custom_name ?? 'Unassigned Class';
        });

        // Group offerings by Teacher Name (including all staff members)
        $byTeacher = collect();

        foreach ($staffMembers as $staff) {
            $roleStr = $staff->staff_role ?? ucfirst($staff->role);
            $key = "{$staff->name} ({$roleStr})";
            $assignedOfferings = $offerings->filter(function ($off) use ($staff) {
                return $off->is_assigned && $off->teacher && $off->teacher->id == $staff->id;
            })->values();

            $byTeacher->put($key, (object) [
                'teacher' => $staff,
                'name' => $key,
                'offerings' => $assignedOfferings,
                'is_unassigned_group' => false,
            ]);
        }

        // Group unassigned offerings by Class Name
        $unassignedByClass = $offerings->filter(fn ($off) => ! $off->is_assigned)
            ->groupBy(function ($off) {
                return $off->section->instituteClass->custom_name ?? 'Unassigned Class';
            });

        return view('principal.assignments.index', compact('staffMembers', 'activeTerm', 'subjects', 'sections', 'assignments', 'offerings', 'assignmentsMap', 'byClass', 'byTeacher', 'unassignedByClass'));
    }

    public function create(): View
    {
        $instituteId = auth()->user()->institute_id;
        $year = now()->year;
        $sequence = str_pad(Teacher::withoutGlobalScopes()->count() + 1, 4, '0', STR_PAD_LEFT);
        $autoEmployeeId = "EMP-{$year}-{$sequence}";

        RoleDefaultAuthority::seedDefaultsForInstitute($instituteId);
        $roleAuthorities = RoleDefaultAuthority::where('institute_id', $instituteId)->orderBy('id', 'asc')->get();

        return view('principal.staff.create', compact('autoEmployeeId', 'roleAuthorities'));
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $instituteId = auth()->user()->institute_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'identifier' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'role_option' => 'required|string',
            'custom_role_name' => 'nullable|required_if:role_option,custom|string|max:100',
            'employment_type' => 'nullable|required_if:role_option,teacher|in:permanent,contractual',
            'qualification' => 'nullable|string|max:100',
            'years_of_experience' => 'nullable|integer|min:0',
            'basic_salary_pkr' => 'nullable|numeric|min:0',
            'is_delegated_admin' => 'nullable|boolean',
            'permissions' => 'nullable|array',

            // Mandatory Faculty Photo & Results
            'profile_picture' => 'required|file|image|mimes:jpg,jpeg,png|max:5120',
            'matriculation_cert' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'intermediate_cert' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'bachelors_cert' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',

            // Optional MS & PhD Results
            'masters_cert' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'phd_cert' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->filled('phone')) {
            $code = $request->input('phone_country_code', '+92');
            $num = preg_replace('/\D/', '', $request->input('phone'));
            $validated['phone'] = $code . ' ' . $num;
        }

        $year = now()->year;
        $sequence = str_pad(Teacher::withoutGlobalScopes()->count() + 1, 4, '0', STR_PAD_LEFT);
        $employeeId = ! empty($validated['identifier']) ? $validated['identifier'] : "EMP-{$year}-{$sequence}";

        $existingUser = User::withoutGlobalScopes()->where('email', strtolower($validated['email']))->first();
        if ($existingUser) {
            if ($existingUser->isPrincipal() || $existingUser->isPrimaryPrincipal() || $existingUser->id === auth()->id()) {
                return back()->withInput()->withErrors([
                    'email' => 'Security Conflict: This email address belongs to the Institute Principal account. Principal accounts cannot be converted to staff.'
                ]);
            }
            if ($existingUser->isGlobalAdmin()) {
                return back()->withInput()->withErrors([
                    'email' => 'Security Conflict: This email belongs to a Platform Global Administrator.'
                ]);
            }
            if ($existingUser->institute_id !== $instituteId) {
                return back()->withInput()->withErrors([
                    'email' => 'This email address is already in use by another institutional account.'
                ]);
            }
            if ($existingUser->role !== User::ROLE_TEACHER) {
                return back()->withInput()->withErrors([
                    'email' => 'This email address is already registered as a ' . ucfirst($existingUser->role) . ' account.'
                ]);
            }
        }

        // Determine Role Title
        $roleOption = strtolower($validated['role_option']);
        if ($roleOption === 'custom') {
            $staffRoleTitle = trim($validated['custom_role_name'] ?? 'Staff');
        } else {
            $authBySlug = RoleDefaultAuthority::where('institute_id', $instituteId)
                ->where('role_slug', $roleOption)->first();
            $staffRoleTitle = $authBySlug ? $authBySlug->role_name : ucfirst($validated['role_option']);
        }

        $employmentType = ($roleOption === 'teacher') ? ($validated['employment_type'] ?? 'permanent') : null;
        $plainPassword = ! empty($validated['password']) ? $validated['password'] : \Illuminate\Support\Str::password(16);

        // Check if role exists in Default Authorities, else create it automatically
        $roleAuth = RoleDefaultAuthority::where('institute_id', $instituteId)
            ->where(function($q) use ($roleOption, $staffRoleTitle) {
                $q->where('role_slug', $roleOption)
                  ->orWhere('role_name', $staffRoleTitle);
            })->first();

        if (!$roleAuth) {
            $roleSlug = \Illuminate\Support\Str::slug($staffRoleTitle);
            $roleAuth = RoleDefaultAuthority::create([
                'institute_id' => $instituteId,
                'role_name' => $staffRoleTitle,
                'role_slug' => $roleSlug,
                'description' => "Custom role for {$staffRoleTitle}.",
                'is_custom' => true,
                'permissions' => $validated['permissions'] ?? [
                    'academics' => ! empty($validated['is_delegated_admin']),
                    'timetables' => ! empty($validated['is_delegated_admin']),
                    'staff' => ! empty($validated['is_delegated_admin']),
                ],
            ]);
        }

        $defaultPerms = $roleAuth ? ($roleAuth->permissions ?? []) : [
            'academics' => ! empty($validated['is_delegated_admin']),
            'timetables' => ! empty($validated['is_delegated_admin']),
            'staff' => ! empty($validated['is_delegated_admin']),
        ];

        $permissions = $validated['permissions'] ?? $defaultPerms;

        // Store picture and transcript files in isolated tenant directories
        $storagePath = "institutes/{$instituteId}/teachers";
        $profilePicPath = $request->file('profile_picture') ? $request->file('profile_picture')->store("{$storagePath}/photos", 'public') : null;
        $matricCertPath = $request->file('matriculation_cert') ? $request->file('matriculation_cert')->store("{$storagePath}/qualifications", 'public') : null;
        $interCertPath = $request->file('intermediate_cert') ? $request->file('intermediate_cert')->store("{$storagePath}/qualifications", 'public') : null;
        $bachCertPath = $request->file('bachelors_cert') ? $request->file('bachelors_cert')->store("{$storagePath}/qualifications", 'public') : null;
        $msCertPath = $request->file('masters_cert') ? $request->file('masters_cert')->store("{$storagePath}/qualifications", 'public') : null;
        $phdCertPath = $request->file('phd_cert') ? $request->file('phd_cert')->store("{$storagePath}/qualifications", 'public') : null;

        if ($existingUser) {
            $updateData = [
                'name' => $validated['name'],
                'role' => User::ROLE_TEACHER,
                'staff_role' => $staffRoleTitle,
                'employment_type' => $employmentType,
                'institute_id' => $instituteId,
                'is_delegated_admin' => ! empty($validated['is_delegated_admin']),
                'permissions' => $permissions,
                'identifier' => $existingUser->identifier ?: $employeeId,
            ];
            if (! empty($validated['password'])) {
                $updateData['password'] = Hash::make($plainPassword);
            }
            $existingUser->update($updateData);
            $user = $existingUser;
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'identifier' => $employeeId,
                'password' => Hash::make($plainPassword),
                'role' => User::ROLE_TEACHER,
                'staff_role' => $staffRoleTitle,
                'employment_type' => $employmentType,
                'institute_id' => $instituteId,
                'is_delegated_admin' => ! empty($validated['is_delegated_admin']),
                'permissions' => $permissions,
                'created_by' => auth()->id(),
            ]);
        }

        // Create or update corresponding Teacher record
        $nameParts = explode(' ', trim($validated['name']), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        Teacher::updateOrCreate(
            ['user_id' => $user->id],
            [
                'institute_id' => $instituteId,
                'employee_id' => $employeeId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($validated['email']),
                'phone' => $validated['phone'] ?? null,
                'profile_picture_path' => $profilePicPath,
                'qualification' => $validated['qualification'] ?? ($validated['role_option'] === 'teacher' ? 'Faculty Member' : $staffRoleTitle),
                'matriculation_cert' => $matricCertPath,
                'intermediate_cert' => $interCertPath,
                'bachelors_cert' => $bachCertPath,
                'masters_cert' => $msCertPath,
                'phd_cert' => $phdCertPath,
                'years_of_experience' => $validated['years_of_experience'] ?? null,
                'basic_salary_pkr' => $validated['basic_salary_pkr'] ?? null,
            ]
        );

        // Send welcome email with login credentials
        try {
            Mail::raw(
                "Dear {$user->name},\n\nWelcome to UPLYFT SaaS School Management Platform!\nYour {$staffRoleTitle} account has been created by the Institute Principal.\n\nLogin Credentials:\nEmail: {$user->email}\nPassword: {$plainPassword}\n\nPlease log in to access your portal.\n\nRegards,\nUPLYFT Academic Management Team",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('🎉 Welcome to UPLYFT - Staff Account Credentials');
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Welcome email failed: '.$e->getMessage());
        }

        return redirect()
            ->route('principal.staff.index')
            ->with('success', "🎉 Staff account for {$user->name} ({$staffRoleTitle}) created successfully! Default Password: '{$plainPassword}'.");
    }

    public function destroy(User $staff): RedirectResponse
    {
        $authUser = auth()->user();
        if ($staff->is_primary_principal && ! $authUser->isGlobalAdmin()) {
            abort(403, 'Security Policy: The Primary Principal account created by Global Admin cannot be modified or deleted by secondary staff.');
        }

        if (! $authUser->canAccessInstitute($staff->getHomeInstituteId()) || $staff->role !== User::ROLE_TEACHER) {
            abort(403);
        }

        $name = $staff->name;
        $staff->delete();

        return redirect()
            ->route('principal.staff.index')
            ->with('success', "Staff member {$name} deleted successfully.");
    }

    /**
     * Update staff member role, designation, contract type (permanent/contractual), and permissions
     */
    public function updateRole(Request $request, User $staff): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();
        if (!$authUser->isPrincipal() && !$authUser->isGlobalAdmin() && !$authUser->hasPermission('staff', 'edit')) {
            abort(403, 'Unauthorized: Only the Principal or authorized staff with Staff Edit rights can modify employee roles.');
        }

        if ($staff->is_primary_principal && ! $authUser->isGlobalAdmin()) {
            abort(403, 'Security Policy: The Primary Principal account created by Global Admin cannot be modified or demoted.');
        }

        if (!$authUser->isGlobalAdmin() && ! $authUser->canAccessInstitute($staff->getHomeInstituteId())) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|nullable|string|max:255',
            'system_role' => 'nullable|in:teacher,student,staff,admin',
            'role_option' => 'required|string',
            'custom_role_name' => 'nullable|required_if:role_option,custom|string|max:100',
            'employment_type' => 'nullable|in:permanent,contractual',
            'qualification' => 'nullable|string|max:100',
            'basic_salary_pkr' => 'nullable|numeric|min:0',
            'sync_default_permissions' => 'nullable|boolean',
        ]);

        // Security: only a Global Administrator may mint or demote Global Admin / Principal accounts.
        if (! $authUser->isGlobalAdmin()) {
            if (! empty($validated['system_role'])
                && in_array($validated['system_role'], ['principal', 'global_admin'], true)) {
                abort(403, 'Only a Global Administrator can grant Principal or Global Admin roles.');
            }

            // Non-principal staff may only manage teacher/staff/student-level targets.
            if (! $authUser->isPrincipal()) {
                $nextRole = $validated['system_role'] ?? $staff->role ?? '';
                if (! in_array($nextRole, ['teacher', 'staff', 'student', 'admin'], true)) {
                    abort(403, 'Staff may not grant system roles.');
                }
            }

            // Prevent self-promotion into an elevated role through this endpoint.
            if ($staff->id === $authUser->id
                && ! empty($validated['system_role'])
                && $validated['system_role'] !== $staff->role) {
                abort(403, 'You cannot change your own system role through this page.');
            }
        }

        $instituteId = $staff->institute_id ?? $authUser->institute_id;
        $roleOption = strtolower($validated['role_option']);

        if ($roleOption === 'custom') {
            $staffRoleTitle = trim($validated['custom_role_name'] ?? 'Staff');
        } else {
            $authBySlug = RoleDefaultAuthority::where('institute_id', $instituteId)
                ->where('role_slug', $roleOption)->first();
            $staffRoleTitle = $authBySlug ? $authBySlug->role_name : ucfirst($validated['role_option']);
        }

        $employmentType = $validated['employment_type'] ?? null;

        $userUpdates = [
            'staff_role' => $staffRoleTitle,
            'employment_type' => $employmentType,
        ];

        if (!empty($validated['name'])) {
            $userUpdates['name'] = $validated['name'];
        }

        if ($instituteId) {
            RoleDefaultAuthority::seedDefaultsForInstitute($instituteId);
        }

        if (!empty($validated['system_role'])) {
            $newSysRole = $validated['system_role'];
            $userUpdates['role'] = $newSysRole;
            $userUpdates['is_delegated_admin'] = false;

            if ($newSysRole === User::ROLE_PRINCIPAL) {
                $userUpdates['staff_role'] = 'Principal';
                $userUpdates['permissions'] = null;
            } elseif ($newSysRole === User::ROLE_TEACHER) {
                \App\Models\Teacher::firstOrCreate(
                    ['user_id' => $staff->id],
                    [
                        'institute_id' => $staff->institute_id,
                        'first_name' => explode(' ', trim($staff->name))[0] ?? 'Faculty',
                        'last_name' => explode(' ', trim($staff->name), 2)[1] ?? '',
                        'email' => $staff->email,
                        'qualification' => 'Faculty Member',
                        'matriculation_cert' => '',
                        'intermediate_cert' => '',
                        'bachelors_cert' => '',
                        'masters_cert' => '',
                        'phd_cert' => '',
                    ]
                );
            }
        }

        // Optionally sync default permissions of the new role preset
        if ($request->boolean('sync_default_permissions', true) && ($userUpdates['role'] ?? $staff->role) !== User::ROLE_PRINCIPAL) {
            $staffRoleSlug = \Illuminate\Support\Str::slug($staffRoleTitle, '_');
            $roleAuth = RoleDefaultAuthority::where('institute_id', $instituteId)
                ->where(function ($q) use ($roleOption, $staffRoleTitle, $staffRoleSlug) {
                    $q->where('role_slug', $staffRoleSlug)
                      ->orWhere('role_slug', $roleOption)
                      ->orWhere('role_name', $staffRoleTitle);
                })->first();

            if (!$roleAuth) {
                RoleDefaultAuthority::seedDefaultsForInstitute($instituteId);
                $roleAuth = RoleDefaultAuthority::where('institute_id', $instituteId)
                    ->where(function ($q) use ($roleOption, $staffRoleTitle, $staffRoleSlug) {
                        $q->where('role_slug', $staffRoleSlug)
                          ->orWhere('role_slug', $roleOption)
                          ->orWhere('role_name', $staffRoleTitle);
                    })->first();
            }

            if ($roleAuth && !empty($roleAuth->permissions)) {
                $userUpdates['permissions'] = $roleAuth->permissions;
            } elseif ($roleOption === 'teacher') {
                $userUpdates['permissions'] = null;
            }
        }

        $staff->update($userUpdates);

        // Update linked teacher profile if exists
        if ($staff->teacherProfile) {
            $teacherUpdates = [];
            if (!empty($validated['name'])) {
                $nameParts = explode(' ', trim($validated['name']), 2);
                $teacherUpdates['first_name'] = $nameParts[0];
                $teacherUpdates['last_name'] = $nameParts[1] ?? '';
            }
            if (array_key_exists('qualification', $validated)) {
                $teacherUpdates['qualification'] = $validated['qualification'];
            }
            if (array_key_exists('basic_salary_pkr', $validated)) {
                $teacherUpdates['basic_salary_pkr'] = $validated['basic_salary_pkr'];
            }
            if (!empty($teacherUpdates)) {
                $staff->teacherProfile->update($teacherUpdates);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Role and contract updated to {$staffRoleTitle}" . ($employmentType ? " ({$employmentType})" : '') . " for {$staff->name}.",
                'staff' => $staff->fresh(['teacherProfile']),
            ]);
        }

        $contractLabel = $employmentType ? " (" . ucfirst($employmentType) . ")" : "";
        return redirect()
            ->route('principal.staff.index')
            ->with('success', "🎉 Role & designation for '{$staff->name}' updated to '{$staffRoleTitle}{$contractLabel}' successfully!");
    }

    public function toggleDelegation(Request $request, User $staff): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();
        if (!$authUser->isPrincipal() && !$authUser->isGlobalAdmin() && !$authUser->hasPermission('staff', 'edit')) {
            abort(403, 'Unauthorized: Only the Principal or authorized staff with Staff Edit rights can modify delegations.');
        }

        if ($staff->is_primary_principal && ! $authUser->isGlobalAdmin()) {
            abort(403, 'Security Policy: The Primary Principal account permissions cannot be modified.');
        }

        if (! $authUser->canAccessInstitute($staff->getHomeInstituteId()) || $staff->role !== User::ROLE_TEACHER) {
            abort(403);
        }

        // Honor explicit on/off state, otherwise toggle.
        if ($request->has('state')) {
            $newVal = $request->boolean('state');
        } else {
            $newVal = ! $staff->is_delegated_admin;
        }

        $staff->update([
            'is_delegated_admin' => $newVal,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'value' => $newVal,
                'message' => $newVal ? "Master Admin rights granted to {$staff->name}." : "Master Admin rights revoked from {$staff->name}.",
            ]);
        }

        $status = $newVal ? 'granted' : 'revoked';

        return redirect()
            ->back()
            ->with('success', "Master Admin rights {$status} for {$staff->name}.");
    }

    public function updatePermissionToggle(Request $request, User $staff): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();
        if (!$authUser->isPrincipal() && !$authUser->isGlobalAdmin() && !$authUser->hasPermission('staff', 'edit')) {
            abort(403, 'Unauthorized: Only the Principal or authorized staff with Staff Edit rights can modify permissions.');
        }

        if ($staff->is_primary_principal && ! $authUser->isGlobalAdmin()) {
            abort(403, 'Security Policy: The Primary Principal account permissions cannot be modified.');
        }

        if (! $authUser->canAccessInstitute($staff->getHomeInstituteId()) || $staff->role !== User::ROLE_TEACHER) {
            abort(403);
        }

        $permissionKey = $request->input('permission_key');
        $effectivePerms = $staff->getEffectivePermissions();
        $userOverrides = $staff->permissions ?? [];

        if ($permissionKey) {
            $currentVal = !empty($effectivePerms[$permissionKey] ?? null);
            $newState = $request->has('state') ? $request->boolean('state') : !$currentVal;

            $userOverrides[$permissionKey] = $newState;

            // Harmonize paired view/edit keys
            if ($newState === false) {
                if (str_ends_with($permissionKey, '_view')) {
                    $base = substr($permissionKey, 0, -5);
                    $userOverrides[$base . '_edit'] = false;
                    $userOverrides[$base] = false;
                } elseif (str_ends_with($permissionKey, '_edit')) {
                    $base = substr($permissionKey, 0, -5);
                    $userOverrides[$base] = 'view';
                } else {
                    $userOverrides[$permissionKey . '_view'] = false;
                    $userOverrides[$permissionKey . '_edit'] = false;
                }
            } else {
                if (str_ends_with($permissionKey, '_edit')) {
                    $base = substr($permissionKey, 0, -5);
                    $userOverrides[$base . '_view'] = true;
                    $userOverrides[$base] = true;
                } elseif (str_ends_with($permissionKey, '_view')) {
                    $base = substr($permissionKey, 0, -5);
                    $userOverrides[$base] = true;
                } else {
                    $userOverrides[$permissionKey . '_view'] = true;
                }
            }

            $staff->update([
                'permissions' => $userOverrides,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'key' => $permissionKey,
                'value' => ! empty($userOverrides[$permissionKey] ?? null),
                'effective_permissions' => $staff->fresh()->getEffectivePermissions(),
                'message' => $permissionKey
                    ? (! empty($userOverrides[$permissionKey]) ? "Enabled '{$permissionKey}' for {$staff->name}." : "Disabled '{$permissionKey}' for {$staff->name}.")
                    : '',
            ]);
        }

        return redirect()
            ->route('principal.staff.index')
            ->with('success', "Updated '{$permissionKey}' toggle for {$staff->name}.");
    }

    public function updatePermissionsBulk(Request $request, User $staff): RedirectResponse
    {
        $authUser = auth()->user();
        if (!$authUser->isPrincipal() && !$authUser->isGlobalAdmin() && !$authUser->hasPermission('staff', 'edit')) {
            abort(403, 'Unauthorized: Only the Principal or authorized staff with Staff Edit rights can modify permissions.');
        }

        if ($staff->is_primary_principal && ! $authUser->isGlobalAdmin()) {
            abort(403, 'Security Policy: The Primary Principal account permissions cannot be modified.');
        }

        if (! $authUser->canAccessInstitute($staff->getHomeInstituteId()) || $staff->role !== User::ROLE_TEACHER) {
            abort(403);
        }

        $allKeys = [
            'directory', 'student_registration', 'students_view', 'invoices',
            'staff_onboard', 'staff_view', 'academics', 'rooms', 'classes',
            'subjects', 'faculty_hours', 'allocations', 'timetables', 'security',
        ];

        $submittedPerms = $request->input('permissions', []);
        $updatedPerms = [];

        foreach ($allKeys as $key) {
            $updatedPerms[$key] = ! empty($submittedPerms[$key]);
        }

        $staff->update([
            'permissions' => $updatedPerms,
        ]);

        return redirect()
            ->route('principal.staff.index')
            ->with('success', "🎉 Updated all portal rights and access permissions for '{$staff->name}'.");
    }

    /**
     * Assign teacher to subject section
     */
    public function assignSubjectSection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_section_id' => 'required|exists:class_sections,id',
            'periods_per_week' => 'nullable|integer|min:1|max:20',
            'duration_minutes' => 'nullable|integer|min:15|max:480',
            'allowed_days' => 'nullable|array',
            'allowed_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ]);

        $instituteId = auth()->user()->institute_id;

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        if (! $activeTerm) {
            return back()->with('error', 'No active academic term found.');
        }

        $allowedDays = !empty($validated['allowed_days'])
            ? array_map('strtolower', $validated['allowed_days'])
            : ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        TeacherSubjectSection::updateOrCreate(
            [
                'academic_term_id' => $activeTerm->id,
                'subject_id' => $validated['subject_id'],
                'class_section_id' => $validated['class_section_id'],
            ],
            [
                'teacher_id' => $validated['teacher_id'],
                'periods_per_week' => $validated['periods_per_week'] ?? 3,
                'duration_minutes' => $validated['duration_minutes'] ?? 60,
                'allowed_days' => $allowedDays,
            ]
        );

        $result = $this->timetableGenerator->regenerateForActiveTerm($instituteId);

        $message = 'Teacher assigned to subject section successfully. The timetable was re-generated automatically.';

        if ($result && ! empty($result['clashes'])) {
            return redirect()
                ->route('principal.assignments.index')
                ->with('success', $message)
                ->with('warning', "Timetable re-generated with {$result['scheduled_slots']} slot(s). Some periods could not be scheduled:<br>".implode('<br>', $result['clashes']));
        }

        return redirect()
            ->route('principal.assignments.index')
            ->with('success', $message);
    }

    /**
     * Update existing teacher subject section assignment
     */
    public function updateAssignment(Request $request, TeacherSubjectSection $assignment): RedirectResponse
    {
        if ($assignment->academicTerm->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_section_id' => 'required|exists:class_sections,id',
            'periods_per_week' => 'nullable|integer|min:1|max:20',
            'duration_minutes' => 'nullable|integer|min:15|max:480',
            'allowed_days' => 'nullable|array',
            'allowed_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ]);

        $updateData = [
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'class_section_id' => $validated['class_section_id'],
        ];

        if (isset($validated['periods_per_week'])) {
            $updateData['periods_per_week'] = $validated['periods_per_week'];
        }
        if (isset($validated['duration_minutes'])) {
            $updateData['duration_minutes'] = $validated['duration_minutes'];
        }
        if (isset($validated['allowed_days'])) {
            $updateData['allowed_days'] = array_map('strtolower', $validated['allowed_days']);
        }

        $assignment->update($updateData);

        $result = $this->timetableGenerator->regenerateForActiveTerm(auth()->user()->institute_id);

        $message = 'Subject assignment updated successfully. The timetable was re-generated automatically.';

        if ($result && ! empty($result['clashes'])) {
            return redirect()
                ->route('principal.assignments.index')
                ->with('success', $message)
                ->with('warning', "Timetable re-generated with {$result['scheduled_slots']} slot(s). Some periods could not be scheduled:<br>".implode('<br>', $result['clashes']));
        }

        return redirect()
            ->route('principal.assignments.index')
            ->with('success', $message);
    }

    /**
     * Remove teacher subject assignment
     */
    public function removeAssignment(TeacherSubjectSection $assignment): RedirectResponse
    {
        if ($assignment->academicTerm->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $assignment->delete();

        $result = $this->timetableGenerator->regenerateForActiveTerm(auth()->user()->institute_id);

        $message = 'Subject assignment removed. The timetable was re-generated automatically.';

        if ($result && ! empty($result['clashes'])) {
            return redirect()
                ->route('principal.assignments.index')
                ->with('success', $message)
                ->with('warning', "Timetable re-generated with {$result['scheduled_slots']} slot(s). Some periods could not be scheduled:<br>".implode('<br>', $result['clashes']));
        }

        return redirect()
            ->route('principal.assignments.index')
            ->with('success', $message);
    }

    /**
     * View Default Authorities & Role Governance Page
     */
    public function authoritiesIndex(): View
    {
        $instituteId = auth()->user()->institute_id;
        RoleDefaultAuthority::seedDefaultsForInstitute($instituteId);

        $roleAuthorities = RoleDefaultAuthority::where('institute_id', $instituteId)
            ->orderBy('is_custom')
            ->orderBy('role_name')
            ->get();

        $allAuthorityModules = [
            'attendance' => ['label' => 'Daily Class Attendance Roster', 'category' => 'Operations'],
            'student_registration' => ['label' => 'Register New Students', 'category' => 'Students'],
            'students' => ['label' => 'View Student Directory & Roster', 'category' => 'Students'],
            'profile_edit' => ['label' => 'Edit Student, Faculty & Staff Sensitive Profile Records (Name, Email, Student/Employee IDs)', 'category' => 'Students'],
            'scholarships' => ['label' => 'Scholarship Policy & Discounts', 'category' => 'Students'],
            'invoices' => ['label' => 'Student Fee Vouchers & Billing', 'category' => 'Financial'],
            'accounts' => ['label' => 'Manage Account Heads & Log Expenses', 'category' => 'Financial'],
            'salaries' => ['label' => 'Record Faculty & Staff Salary Payments', 'category' => 'Financial'],
            'ai_report' => ['label' => 'View AI Financial Profit/Loss Audit & Reports', 'category' => 'Financial'],
            'staff' => ['label' => 'View Staff Roster & Governance', 'category' => 'Staff'],
            'staff_onboard' => ['label' => 'Register & Onboard New Staff Accounts', 'category' => 'Staff'],
            'academics' => ['label' => 'Manage Academic Terms, Classes & Subjects', 'category' => 'Academics'],
            'timetables' => ['label' => 'Weekly Timetable Matrix Engine', 'category' => 'Academics'],
            'allocations' => ['label' => 'Assign Teachers to Subjects & Sections', 'category' => 'Academics'],
            'rooms' => ['label' => 'Rooms & Campus Facilities Management', 'category' => 'Academics'],
            'faculty_hours' => ['label' => 'Faculty Workload & Attendance Timings', 'category' => 'Operations'],
            'lms_content' => ['label' => 'Course Study Materials & LMS Uploads', 'category' => 'LMS & AI'],
            'ai_bot' => ['label' => 'Access AI RAG Student Chatbot', 'category' => 'LMS & AI'],
            'assessment_engine' => ['label' => 'Assessment & Exam Generator', 'category' => 'LMS & AI'],
            'grading_normalizer' => ['label' => 'Grade Weightages & Exam Reports', 'category' => 'LMS & AI'],
            'security' => ['label' => 'Security & Account Credentials Settings', 'category' => 'Security'],
        ];

        return view('principal.staff.authorities', compact('roleAuthorities', 'allAuthorityModules'));
    }

    /**
     * Store or Update Default Role Authority Permissions
     */
    public function storeRoleAuthority(Request $request): RedirectResponse
    {
        $instituteId = auth()->user()->institute_id;

        $validated = $request->validate([
            'role_name' => 'required|string|max:100',
            'role_slug' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'sync_to_existing' => 'nullable|boolean',
            'is_custom' => 'nullable|boolean',
        ]);

        $roleName = trim($validated['role_name']);
        $roleSlug = !empty($validated['role_slug'])
            ? \Illuminate\Support\Str::slug($validated['role_slug'], '_')
            : \Illuminate\Support\Str::slug($roleName, '_');

        $permissions = $validated['permissions'] ?? [];
        // Convert input array to boolean permissions map
        $formattedPermissions = [];
        foreach ($permissions as $key => $val) {
            $formattedPermissions[$key] = (bool) $val;
        }

        $authority = RoleDefaultAuthority::updateOrCreate(
            [
                'institute_id' => $instituteId,
                'role_slug' => $roleSlug,
            ],
            [
                'role_name' => $roleName,
                'description' => $validated['description'] ?? null,
                'permissions' => $formattedPermissions,
                'is_custom' => !empty($validated['is_custom']),
            ]
        );

        $syncedCount = 0;
        if ($request->boolean('sync_to_existing')) {
            // Sync these updated default authorities to all existing staff members matching this role
            // Match by staff_role title, slug, or null staff_role for default teacher accounts
            $usersToUpdate = User::where('institute_id', $instituteId)
                ->where(function ($q) use ($roleName, $roleSlug) {
                    $q->where('staff_role', $roleName)
                        ->orWhere('staff_role', ucfirst($roleSlug))
                        ->orWhereRaw('LOWER(staff_role) = ?', [strtolower($roleName)])
                        ->orWhereRaw('LOWER(staff_role) = ?', [strtolower($roleSlug)]);
                    if (strtolower($roleSlug) === 'teacher') {
                        $q->orWhere(function ($sub) {
                            $sub->whereNull('staff_role')->where('role', User::ROLE_TEACHER);
                        });
                    }
                })->get();

            foreach ($usersToUpdate as $user) {
                $user->update(['permissions' => $formattedPermissions]);
                $syncedCount++;
            }
        }

        $msg = "🎉 Default Authorities for role '{$roleName}' saved successfully.";
        if ($syncedCount > 0) {
            $msg .= " Updated authorities synced to {$syncedCount} active account(s).";
        }

        return redirect()
            ->route('principal.staff.authorities')
            ->with('success', $msg);
    }

    /**
     * Delete Custom Role Default Authority
     */
    public function destroyRoleAuthority(RoleDefaultAuthority $authority): RedirectResponse
    {
        if ($authority->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $roleName = $authority->role_name;
        $authority->delete();

        return redirect()
            ->route('principal.staff.authorities')
            ->with('success', "Role Authority '{$roleName}' deleted successfully.");
    }
}
