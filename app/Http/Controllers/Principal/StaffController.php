<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\TeacherSubjectSection;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Rules\ValidIdentifier;
use App\Services\TimetableGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $instituteId = auth()->user()->institute_id;

        $staffMembers = User::where('institute_id', $instituteId)
            ->whereIn('role', [User::ROLE_TEACHER, User::ROLE_PRINCIPAL])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('principal.staff.index', compact('staffMembers'));
    }

    /**
     * Dedicated Assign Subject & Teacher Page
     */
    public function assignmentsIndex(): View
    {
        $instituteId = auth()->user()->institute_id;

        $staffMembers = User::where('institute_id', $instituteId)
            ->whereIn('role', [User::ROLE_TEACHER, User::ROLE_PRINCIPAL])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        $subjects = Subject::whereHas('instituteClass', fn($q) => $q->where('institute_id', $instituteId))
            ->with('instituteClass')
            ->get();

        $sections = ClassSection::whereHas('instituteClass', fn($q) => $q->where('institute_id', $instituteId))
            ->with('instituteClass')
            ->get();

        $assignments = collect();
        if ($activeTerm) {
            $assignments = TeacherSubjectSection::where('academic_term_id', $activeTerm->id)
                ->with(['teacher', 'subject', 'section.instituteClass'])
                ->get();
        }

        return view('principal.assignments.index', compact('staffMembers', 'activeTerm', 'subjects', 'sections', 'assignments'));
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|max:255|unique:users,email',
            'identifier'         => ['nullable', 'string', new ValidIdentifier, 'unique:users,identifier'],
            'password'           => ['required', 'string', 'confirmed', new StrongPassword],
            'role_option'        => 'required|string|in:teacher,accountant,administration,coordinator,custom',
            'custom_role_name'   => 'nullable|required_if:role_option,custom|string|max:100',
            'employment_type'    => 'nullable|required_if:role_option,teacher|in:permanent,contractual',
            'is_delegated_admin' => 'nullable|boolean',
            'permissions'        => 'nullable|array',
        ]);

        $instituteId = auth()->user()->institute_id;

        $staffRoleTitle = match ($validated['role_option']) {
            'teacher'        => 'Teacher',
            'accountant'     => 'Accountant',
            'administration' => 'Administration',
            'coordinator'    => 'Coordinator',
            'custom'         => $validated['custom_role_name'] ?? 'Staff',
        };

        $employmentType = ($validated['role_option'] === 'teacher') ? ($validated['employment_type'] ?? 'permanent') : null;

        $permissions = $validated['permissions'] ?? [
            'academics'   => !empty($validated['is_delegated_admin']),
            'timetables'  => !empty($validated['is_delegated_admin']),
            'staff'       => !empty($validated['is_delegated_admin']),
        ];

        User::create([
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'identifier'         => $validated['identifier'] ?? null,
            'password'           => Hash::make($validated['password']),
            'role'               => User::ROLE_TEACHER,
            'staff_role'         => $staffRoleTitle,
            'employment_type'    => $employmentType,
            'institute_id'       => $instituteId,
            'is_delegated_admin' => !empty($validated['is_delegated_admin']),
            'permissions'        => $permissions,
            'created_by'         => auth()->id(),
        ]);

        return redirect()
            ->route('principal.staff.index')
            ->with('success', "Staff account for {$validated['name']} ({$staffRoleTitle}) created successfully.");
    }

    public function toggleDelegation(User $staff): RedirectResponse
    {
        if ($staff->institute_id !== auth()->user()->institute_id || $staff->role !== User::ROLE_TEACHER) {
            abort(403);
        }

        $newVal = !$staff->is_delegated_admin;

        $staff->update([
            'is_delegated_admin' => $newVal,
        ]);

        $status = $newVal ? 'granted' : 'revoked';

        return redirect()
            ->route('principal.staff.index')
            ->with('success', "Master Admin rights {$status} for {$staff->name}.");
    }

    public function updatePermissionToggle(Request $request, User $staff): RedirectResponse
    {
        if ($staff->institute_id !== auth()->user()->institute_id || $staff->role !== User::ROLE_TEACHER) {
            abort(403);
        }

        $permissionKey = $request->input('permission_key');
        $currentPerms = $staff->permissions ?? [];
        
        if ($permissionKey) {
            $currentPerms[$permissionKey] = !($currentPerms[$permissionKey] ?? false);
        }

        $staff->update([
            'permissions' => $currentPerms,
        ]);

        return redirect()
            ->route('principal.staff.index')
            ->with('success', "Updated '{$permissionKey}' toggle for {$staff->name}.");
    }

    /**
     * Assign teacher to subject section
     */
    public function assignSubjectSection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id'       => 'required|exists:users,id',
            'subject_id'       => 'required|exists:subjects,id',
            'class_section_id' => 'required|exists:class_sections,id',
        ]);

        $instituteId = auth()->user()->institute_id;

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        if (!$activeTerm) {
            return back()->with('error', 'No active academic term found.');
        }

        TeacherSubjectSection::updateOrCreate(
            [
                'academic_term_id' => $activeTerm->id,
                'subject_id'       => $validated['subject_id'],
                'class_section_id' => $validated['class_section_id'],
            ],
            [
                'teacher_id'       => $validated['teacher_id'],
                'periods_per_week' => 3, // Default fixed periods per week
            ]
        );

        $result = $this->timetableGenerator->regenerateForActiveTerm($instituteId);

        $message = 'Teacher assigned to subject section successfully. The timetable was re-generated automatically.';

        if ($result && !empty($result['clashes'])) {
            return redirect()
                ->route('principal.assignments.index')
                ->with('success', $message)
                ->with('warning', "Timetable re-generated with {$result['scheduled_slots']} slot(s). Some periods could not be scheduled:<br>" . implode('<br>', $result['clashes']));
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
            'teacher_id'       => 'required|exists:users,id',
            'subject_id'       => 'required|exists:subjects,id',
            'class_section_id' => 'required|exists:class_sections,id',
        ]);

        $assignment->update([
            'teacher_id'       => $validated['teacher_id'],
            'subject_id'       => $validated['subject_id'],
            'class_section_id' => $validated['class_section_id'],
        ]);

        $result = $this->timetableGenerator->regenerateForActiveTerm(auth()->user()->institute_id);

        $message = 'Subject assignment updated successfully. The timetable was re-generated automatically.';

        if ($result && !empty($result['clashes'])) {
            return redirect()
                ->route('principal.assignments.index')
                ->with('success', $message)
                ->with('warning', "Timetable re-generated with {$result['scheduled_slots']} slot(s). Some periods could not be scheduled:<br>" . implode('<br>', $result['clashes']));
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

        if ($result && !empty($result['clashes'])) {
            return redirect()
                ->route('principal.assignments.index')
                ->with('success', $message)
                ->with('warning', "Timetable re-generated with {$result['scheduled_slots']} slot(s). Some periods could not be scheduled:<br>" . implode('<br>', $result['clashes']));
        }

        return redirect()
            ->route('principal.assignments.index')
            ->with('success', $message);
    }
}
