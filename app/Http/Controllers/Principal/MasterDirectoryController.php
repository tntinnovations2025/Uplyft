<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherSalarySlip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MasterDirectoryController extends Controller
{
    /**
     * Display the Master Directory Search Interface for Students & Teachers.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $instituteId = $user->institute_id;
        $search = trim($request->input('search', ''));
        $filterType = $request->input('type', 'all'); // 'all', 'students', 'teachers'

        $students = collect();
        $teachers = collect();

        // 1. Fetch Students
        if ($filterType === 'all' || $filterType === 'students') {
            $studentQuery = Student::where('institute_id', $instituteId)
                ->with(['classSection.instituteClass', 'invoices', 'user']);

            if ($search !== '') {
                $studentQuery->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('roll_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('student_bform_cnic', 'like', "%{$search}%")
                        ->orWhere('father_guardian_cnic', 'like', "%{$search}%")
                        ->orWhere('father_guardian_name', 'like', "%{$search}%");
                });
            }

            $students = $studentQuery->orderBy('created_at', 'desc')->take(30)->get();
        }

        // 2. Fetch Teachers / Staff
        if ($filterType === 'all' || $filterType === 'teachers') {
            $teacherQuery = Teacher::where('institute_id', $instituteId)
                ->with(['user', 'salarySlips']);

            if ($search !== '') {
                $teacherQuery->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('qualification', 'like', "%{$search}%");
                });
            }

            $teachers = $teacherQuery->orderBy('created_at', 'desc')->get()
                ->unique(function ($t) {
                    return strtolower(trim($t->email ?? $t->user->email ?? "{$t->first_name} {$t->last_name}"));
                })
                ->take(30)
                ->values();
        }

        return view('principal.directory.index', compact('students', 'teachers', 'search', 'filterType'));
    }

    /**
     * Show full detailed view for a Teacher, including salary slips / payment screenshots.
     */
    public function showTeacher(Teacher $teacher): View
    {
        $this->authorizeAccess($teacher->institute_id);
        $teacher->load(['user', 'salarySlips']);

        return view('principal.directory.teacher-show', compact('teacher'));
    }

    /**
     * Show full detailed view for a Student.
     */
    public function showStudent(Student $student): View
    {
        $this->authorizeAccess($student->institute_id);
        $student->load(['classSection.instituteClass', 'invoices', 'user']);

        return view('principal.directory.student-show', compact('student'));
    }

    /**
     * Upload a Salary Slip or Payment Screenshot for a Teacher.
     */
    public function uploadSalarySlip(Request $request, Teacher $teacher): RedirectResponse
    {
        $this->authorizeAccess($teacher->institute_id);

        $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'month_year' => ['required', 'string', 'max:50'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'slip_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // 5MB max
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = auth()->user();
        $head = \App\Models\AccountHead::where('institute_id', $teacher->institute_id)
            ->where('type', 'expense')
            ->where(function($q) {
                $q->where('name', 'like', '%Salary%')
                  ->orWhere('name', 'like', '%Salaries%');
            })
            ->first();

        if (!$head) {
            $head = \App\Models\AccountHead::create([
                'institute_id' => $teacher->institute_id,
                'name' => 'Faculty & Staff Salaries',
                'type' => 'expense',
                'description' => 'Monthly compensation for teaching and support staff',
                'receipt_requirement' => 'none',
                'is_active' => true,
                'created_by' => $user->id,
            ]);
        }

        if ($head->receipt_requirement === 'mandatory' && !$request->hasFile('slip_file')) {
            return redirect()
                ->back()
                ->with('error', "Salary slip / payment picture attachment is MANDATORY for account head '{$head->name}'.");
        }

        $filePath = null;
        if ($request->hasFile('slip_file')) {
            $folder = "institutes/{$teacher->institute_id}/salary_slips/{$teacher->id}";
            $filePath = $request->file('slip_file')->store($folder, 'public');
        }

        $salarySlip = TeacherSalarySlip::create([
            'institute_id' => $teacher->institute_id,
            'teacher_id' => $teacher->id,
            'title' => $request->input('title'),
            'month_year' => $request->input('month_year'),
            'amount' => $request->input('amount'),
            'file_path' => $filePath,
            'notes' => $request->input('notes'),
        ]);

        // Auto-log to Financial Ledger (Expense) if amount is specified
        $amountVal = $request->input('amount') ?: 0;
        if ($amountVal > 0) {
            \App\Models\FinancialTransaction::create([
                'institute_id' => $teacher->institute_id,
                'account_head_id' => $head->id,
                'title' => "Teacher Salary Paid: {$teacher->full_name} ({$request->input('month_year')})",
                'type' => 'expense',
                'amount' => $amountVal,
                'transaction_date' => now()->format('Y-m-d'),
                'payment_method' => 'Bank Transfer',
                'reference_number' => "SAL-{$salarySlip->id}",
                'receipt_image' => $filePath,
                'notes' => $request->input('notes') ?: "Teacher salary slip uploaded for {$request->input('month_year')}",
                'created_by' => $user->id,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', "Salary slip for {$teacher->full_name} recorded & added to Expense ledger successfully!");
    }

    /**
     * Delete a Salary Slip or Payment Screenshot.
     */
    public function deleteSalarySlip(TeacherSalarySlip $slip): RedirectResponse
    {
        $this->authorizeAccess($slip->institute_id);

        if ($slip->file_path && Storage::disk('public')->exists($slip->file_path)) {
            Storage::disk('public')->delete($slip->file_path);
        }

        $slip->delete();

        return redirect()
            ->back()
            ->with('success', 'Salary slip / payment screenshot removed successfully.');
    }

    /**
     * Show form to edit sensitive profile details of a faculty/teacher (name, email, employee ID...).
     * Accessible to Principal OR any staff member granted the `profile_edit` edit permission.
     */
    public function editTeacher(Teacher $teacher): View
    {
        $this->authorizeAccess($teacher->institute_id);
        $teacher->load(['user']);

        return view('principal.directory.teacher-edit', compact('teacher'));
    }

    /**
     * Update sensitive profile details of a faculty/teacher + its linked login account.
     */
    public function updateTeacher(Request $request, Teacher $teacher): RedirectResponse
    {
        $this->authorizeAccess($teacher->institute_id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:190',
            'phone' => 'nullable|string|max:30',
            'employee_id' => 'nullable|string|max:50',
            'qualification' => 'nullable|string|max:200',
            'specialization_subjects' => 'nullable|string|max:255',
        ]);

        // Email uniqueness: must not collide with any other Teacher OR User login account in the institute
        $email = strtolower(trim($validated['email']));
        $userId = $teacher->user_id;
        $emailOwnerTeacher = Teacher::withoutGlobalScopes()
            ->where('institute_id', $teacher->institute_id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('id', '!=', $teacher->id)
            ->exists();
        $emailOwnerUser = \App\Models\User::withoutGlobalScopes()
            ->where('institute_id', $teacher->institute_id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->when($userId, fn ($q) => $q->where('id', '!=', $userId))
            ->exists();

        if ($emailOwnerTeacher || $emailOwnerUser) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', "The email '{$validated['email']}' is already registered to another account in this institute.");
        }

        $fullName = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $teacher->update([
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'employee_id' => $validated['employee_id'] ?: $teacher->employee_id,
            'qualification' => $validated['qualification'] ?: $teacher->qualification,
            'specialization_subjects' => $validated['specialization_subjects'] ?: $teacher->specialization_subjects,
        ]);

        // Keep the linked login account name/email in sync
        if ($teacher->user) {
            $teacher->user->update([
                'name' => $fullName,
                'email' => $validated['email'],
            ]);
        }

        $redirectTarget = (auth()->user() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin())
            ? auth()->user()->staffUrl("directory/teacher/{$teacher->id}")
            : route('principal.directory.teacher', $teacher);

        return redirect($redirectTarget)
            ->with('success', "Faculty profile '{$fullName}' updated successfully (name, email &amp; identification synced to login account).");
    }

    private function authorizeAccess(int $instituteId): void
    {
        if ($instituteId !== auth()->user()->institute_id && ! auth()->user()->isGlobalAdmin()) {
            abort(403, 'Unauthorized directory access.');
        }
    }
}
