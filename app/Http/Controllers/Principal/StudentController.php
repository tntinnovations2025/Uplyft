<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentAdmissionController;
use App\Http\Requests\StoreStudentAdmissionRequest;
use App\Models\AcademicTerm;
use App\Models\InstituteClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Principal Portal Student Registration & Roster Management.
 */
class StudentController extends Controller
{
    public function __construct(protected StudentAdmissionController $admissionController) {}

    /**
     * Display a listing of enrolled students in the principal's institute.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $activeTerm = AcademicTerm::where('institute_id', $user->institute_id)
            ->where('is_active', true)
            ->first();

        $query = Student::where('institute_id', $user->institute_id)
            ->when($activeTerm, function ($q) use ($activeTerm) {
                $q->where(function ($sub) use ($activeTerm) {
                    $sub->where('academic_term_id', $activeTerm->id)
                        ->orWhereHas('classSection.instituteClass', fn ($cs) => $cs->where('academic_term_id', $activeTerm->id));
                });
            })
            ->with(['classSection.instituteClass', 'user'])
            ->orderBy('created_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('roll_number', 'like', "%{$search}%")
                    ->orWhere('father_guardian_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($sectionId = $request->input('class_section_id')) {
            $query->where('class_section_id', $sectionId);
        }

        $students = $query->paginate(20)->withQueryString();

        // Calculate term attendance statistics for each student in the roster
        $studentIds = $students->pluck('id');
        $allTermLogs = \App\Models\Attendance::whereIn('student_id', $studentIds)
            ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
            ->get()
            ->groupBy('student_id');

        foreach ($students as $student) {
            $sLogs = $allTermLogs->get($student->id, collect());
            $totalSessions = $sLogs->count();
            $presentSessions = $sLogs->filter(fn ($l) => in_array(strtolower($l->status), ['present', 'late']))->count();
            $student->attendance_total_sessions = $totalSessions;
            $student->attendance_present_sessions = $presentSessions;
            $student->attendance_percentage = $totalSessions > 0 ? round(($presentSessions / $totalSessions) * 100, 1) : 100;
        }

        $classes = InstituteClass::where('institute_id', $user->institute_id)
            ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
            ->with(['systemClass', 'sections.room'])
            ->get();

        $totalStudents = Student::where('institute_id', $user->institute_id)
            ->when($activeTerm, function ($q) use ($activeTerm) {
                $q->where(function ($sub) use ($activeTerm) {
                    $sub->where('academic_term_id', $activeTerm->id)
                        ->orWhereHas('classSection.instituteClass', fn ($cs) => $cs->where('academic_term_id', $activeTerm->id));
                });
            })->count();

        return view('principal.students.index', compact('students', 'classes', 'totalStudents', 'activeTerm'));
    }

    /**
     * Show the form for registering a new student.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $institute = $user->institute;
        $activeTerm = AcademicTerm::where('institute_id', $user->institute_id)
            ->where('is_active', true)
            ->first();

        $classes = InstituteClass::where('institute_id', $user->institute_id)
            ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
            ->with(['systemClass', 'sections.room'])
            ->get();

        $scholarships = \App\Models\ScholarshipCategory::where('institute_id', $user->institute_id)
            ->where('is_active', true)
            ->get();

        $year = now()->year;
        $seq = str_pad(Student::withoutGlobalScopes()->count() + 1, 4, '0', STR_PAD_LEFT);
        $autoStudentId = "STD-{$year}-{$seq}";

        return view('principal.students.create', compact('institute', 'classes', 'activeTerm', 'scholarships', 'autoStudentId'));
    }

    /**
     * Store a newly registered student via Principal Portal.
     */
    public function store(StoreStudentAdmissionRequest $request): RedirectResponse
    {
        $response = $this->admissionController->store($request);
        $data = $response->getData(true);

        if (isset($data['success']) && $data['success']) {
            $studentName = $data['student']['first_name'].' '.$data['student']['last_name'];
            $rollNo = $data['student']['roll_number'];

            $redirectTarget = (auth()->user() && ! auth()->user()->isPrincipal() && ! auth()->user()->isGlobalAdmin())
                ? auth()->user()->staffUrl('students')
                : route('principal.students.index');

            $message = "Student '{$studentName}' enrolled successfully! Roll Number: {$rollNo}.";
            $credentials = $data['credentials'] ?? null;
            if (is_array($credentials) && ! empty($credentials['password'])) {
                $message .= " Student login email: {$credentials['email']} | Default Password: {$credentials['password']}";
            } else {
                $message .= ' A login account already exists for this email; its password was not changed.';
            }

            return redirect($redirectTarget)
                ->with('success', $message);
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $data['message'] ?? 'Failed to register student.');
    }

    /**
     * Display a specific student profile.
     */
    public function show(Student $student): View
    {
        $this->authorizeStudentAccess($student);
        $student->load(['classSection.instituteClass', 'invoices', 'user']);

        $activeTerm = AcademicTerm::where('institute_id', $student->institute_id)
            ->where('is_active', true)
            ->first();

        $logs = \App\Models\Attendance::where('student_id', $student->id)
            ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
            ->get();

        $totalSessions = $logs->count();
        $presentSessions = $logs->filter(fn ($l) => in_array(strtolower($l->status), ['present', 'late']))->count();
        $absentSessions = $logs->filter(fn ($l) => strtolower($l->status) === 'absent')->count();
        $leaveSessions = $logs->filter(fn ($l) => strtolower($l->status) === 'leave')->count();

        $student->attendance_total_sessions = $totalSessions;
        $student->attendance_present_sessions = $presentSessions;
        $student->attendance_absent_sessions = $absentSessions;
        $student->attendance_leave_sessions = $leaveSessions;
        $student->attendance_percentage = $totalSessions > 0 ? round(($presentSessions / $totalSessions) * 100, 1) : 100;

        return view('principal.students.show', compact('student', 'logs'));
    }

    /**
     * Update / Shift student class section.
     */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudentAccess($student);

        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
        ]);

        $oldSection = $student->classSection;
        $newSection = \App\Models\ClassSection::with('instituteClass')->findOrFail($validated['class_section_id']);

        if ($oldSection && $oldSection->id !== $newSection->id) {
            if ($oldSection->enrolled_students > 0) {
                $oldSection->decrement('enrolled_students');
            }
            $newSection->increment('enrolled_students');
        } elseif (! $oldSection) {
            $newSection->increment('enrolled_students');
        }

        $enrolledProgram = $newSection->instituteClass
            ? $newSection->instituteClass->name.' - '.$newSection->section_name
            : $newSection->section_name;

        $student->update([
            'class_section_id' => $newSection->id,
            'enrolled_program' => $enrolledProgram,
        ]);

        $redirectTarget = (auth()->user() && ! auth()->user()->isPrincipal() && ! auth()->user()->isGlobalAdmin())
            ? auth()->user()->staffUrl('students')
            : route('principal.students.index');

        return redirect($redirectTarget)
            ->with('success', "Student '{$student->full_name}' successfully shifted to {$enrolledProgram}.");
    }

    /**
     * Delete / unenroll a student.
     */
    public function destroy(Student $student): RedirectResponse
    {
        $this->authorizeStudentAccess($student);

        $name = $student->full_name;
        $termName = $student->academicTerm ? $student->academicTerm->name : 'Session';

        if ($student->classSection && $student->classSection->enrolled_students > 0) {
            $student->classSection->decrement('enrolled_students');
        }

        // Check if student has records in other academic sessions
        $hasOtherSessionRecords = false;
        if ($student->user_id) {
            $hasOtherSessionRecords = Student::where('user_id', $student->user_id)
                ->where('id', '!=', $student->id)
                ->exists();
        }

        // Only delete user login account if student has NO records in any other academic terms
        if ($student->user && !$hasOtherSessionRecords) {
            $student->user->delete();
        }

        $student->delete();

        $redirectTarget = (auth()->user() && ! auth()->user()->isPrincipal() && ! auth()->user()->isGlobalAdmin())
            ? auth()->user()->staffUrl('students')
            : route('principal.students.index');

        return redirect($redirectTarget)
            ->with('success', "Student '{$name}' removed from {$termName}. Historical enrollment records in previous terms remain intact.");
    }

    public function updateResultStatus(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudentAccess($student);

        $validated = $request->validate([
            'annual_result_status' => 'required|in:passed,failed,pending',
        ]);

        $student->update([
            'annual_result_status' => $validated['annual_result_status'],
        ]);

        $statusLabel = strtoupper($validated['annual_result_status']);

        return redirect()
            ->back()
            ->with('success', "Annual result status for '{$student->full_name}' set to: {$statusLabel}.");
    }

    /**
     * Show form to edit sensitive profile details of a student (name, email, roll number, CNIC...).
     * Accessible to Principal OR any staff member granted the `profile_edit` edit permission.
     */
    public function edit(Student $student): View
    {
        $this->authorizeStudentAccess($student);
        $student->load(['classSection.instituteClass', 'user']);

        $activeTerm = AcademicTerm::where('institute_id', auth()->user()->institute_id)
            ->where('is_active', true)
            ->first();

        $classes = InstituteClass::where('institute_id', auth()->user()->institute_id)
            ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
            ->with(['systemClass', 'sections.room'])
            ->get();

        return view('principal.students.edit', compact('student', 'classes'));
    }

    /**
     * Update sensitive profile details of a student + its linked login account.
     */
    public function updateProfile(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudentAccess($student);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:190',
            'phone' => 'nullable|string|max:30',
            'roll_number' => 'required|string|max:60',
            'student_bform_cnic' => 'nullable|string|max:50',
            'father_guardian_cnic' => 'nullable|string|max:50',
            'father_guardian_name' => 'nullable|string|max:150',
            'guardian_phone' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
        ]);

        $email = strtolower(trim($validated['email']));
        $userId = $student->user_id;

        $emailOwnerStudent = \App\Models\Student::withoutGlobalScopes()
            ->where('institute_id', $student->institute_id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('id', '!=', $student->id)
            ->exists();
        $emailOwnerUser = \App\Models\User::withoutGlobalScopes()
            ->where('institute_id', $student->institute_id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->when($userId, fn ($q) => $q->where('id', '!=', $userId))
            ->exists();

        if ($emailOwnerStudent || $emailOwnerUser) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', "The email '{$validated['email']}' is already registered to another account in this institute.");
        }

        $studentName = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $student->update([
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: $student->phone,
            'roll_number' => $validated['roll_number'],
            'student_bform_cnic' => $validated['student_bform_cnic'] ?: $student->student_bform_cnic,
            'father_guardian_cnic' => $validated['father_guardian_cnic'] ?: $student->father_guardian_cnic,
            'father_guardian_name' => $validated['father_guardian_name'] ?: $student->father_guardian_name,
            'guardian_phone' => $validated['guardian_phone'] ?: $student->guardian_phone,
            'date_of_birth' => $validated['date_of_birth'] ?: $student->date_of_birth,
            'address' => $validated['address'] ?: $student->address,
        ]);

        // Keep the linked login account name/email in sync
        if ($student->user) {
            $student->user->update([
                'name' => $studentName,
                'email' => $validated['email'],
            ]);
        }

        $redirectTarget = (auth()->user() && ! auth()->user()->isPrincipal() && ! auth()->user()->isGlobalAdmin())
            ? auth()->user()->staffUrl("students/{$student->id}")
            : route('principal.students.show', $student);

        return redirect($redirectTarget)
            ->with('success', "Student profile '{$studentName}' updated successfully (name &amp; email synced to login account).");
    }

    /**
     * Authorize that the current principal owns this student record.
     */
    private function authorizeStudentAccess(Student $student): void
    {
        if ($student->institute_id !== auth()->user()->institute_id && ! auth()->user()->isGlobalAdmin()) {
            abort(403, 'Unauthorized access to student record.');
        }
    }
}
