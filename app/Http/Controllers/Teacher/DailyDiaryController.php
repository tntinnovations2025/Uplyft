<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\DailyDiary;
use App\Models\Subject;
use App\Models\TeacherSubjectSection;
use App\Services\PortalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DailyDiaryController extends Controller
{
    /**
     * Only teachers (and principal/admin overrides) may access the Daily Diary.
     * Accountants, coordinators, and other non-teaching staff are blocked.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if ($user && !in_array($user->role, ['teacher']) && !$user->isPrincipal() && !$user->isGlobalAdmin()) {
                abort(403, 'Daily Diary is only available for teachers.');
            }
            return $next($request);
        });
    }

    /**
     * Display the Teacher's Daily Diary management studio.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_if(!$user, 401, 'Unauthenticated.');

        $instituteId = method_exists($user, 'getActiveInstituteId') ? $user->getActiveInstituteId() : $user->institute_id;

        // Fetch subjects & class sections assigned to this teacher
        $allocations = TeacherSubjectSection::where('teacher_id', $user->id)
            ->with(['subject', 'classSection.instituteClass'])
            ->get();

        // If no allocations found (e.g. principal or demo teacher), fallback to institute sections & subjects
        if ($allocations->isEmpty() && ($user->isPrincipal() || $user->isGlobalAdmin())) {
            $classSections = ClassSection::whereHas('instituteClass', function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })->with('instituteClass')->get();

            $subjects = Subject::whereHas('instituteClass', function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })->get();
        } else {
            $classSections = $allocations->pluck('classSection')->filter()->unique('id')->values();
            $subjects = $allocations->pluck('subject')->filter()->unique('id')->values();
        }

        // Filter parameters
        $selectedSectionId = $request->input('section_id');
        $selectedSubjectId = $request->input('subject_id');
        $selectedType = $request->input('type');

        $query = DailyDiary::where('teacher_id', $user->id)
            ->with(['subject', 'classSection.instituteClass']);

        if ($selectedSectionId) {
            $query->where('class_section_id', $selectedSectionId);
        }
        if ($selectedSubjectId) {
            $query->where('subject_id', $selectedSubjectId);
        }
        if ($selectedType && $selectedType !== 'all') {
            $query->where('entry_type', $selectedType);
        }

        $diaries = $query->latest('assigned_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('teacher.diary.index', compact(
            'allocations',
            'classSections',
            'subjects',
            'diaries',
            'selectedSectionId',
            'selectedSubjectId',
            'selectedType'
        ));
    }

    /**
     * Store a newly created daily diary entry.
     * Validates file size strictly < 10 MB (10240 KB).
     * Dispatches real-time notifications to all enrolled students in that class section.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        abort_if(! $user, 401, 'Unauthenticated.');

        $instituteId = method_exists($user, 'getActiveInstituteId') ? $user->getActiveInstituteId() : $user->institute_id;

        $validated = $request->validate([
            'class_section_id' => ['required', 'integer', 'exists:class_sections,id'],
            'subject_id'       => ['required', 'integer', 'exists:subjects,id'],
            'entry_type'       => ['required', 'string', 'in:homework,test,assignment,announcement,note,classwork'],
            'title'            => ['required', 'string', 'max:255'],
            'content'          => ['required', 'string'],
            'assigned_date'    => ['nullable', 'date'],
            'due_date'         => ['nullable', 'date'],
            'reminder_morning' => ['nullable', 'boolean'],
            'attachment'       => [
                'nullable',
                'file',
                'max:10240', // Strictly less than 10MB (10240 KB)
                'mimes:pdf,xls,xlsx,doc,docx,ppt,pptx,png,jpg,jpeg,webp,txt,zip',
            ],
        ], [
            'attachment.max' => 'The uploaded file exceeds the 10 MB limit. Please upload a file smaller than 10 MB.',
            'attachment.mimes' => 'Supported file formats: PDF, Excel (.xlsx, .xls), Word (.docx, .doc), PowerPoint, Images, Text, ZIP.',
        ]);

        // IDOR Authorization: Verify that this teacher is assigned to this section & subject
        if (! $user->isPrincipal() && ! $user->isGlobalAdmin()) {
            $isAssigned = TeacherSubjectSection::where('teacher_id', $user->id)
                ->where('class_section_id', $validated['class_section_id'])
                ->where('subject_id', $validated['subject_id'])
                ->exists();

            abort_if(! $isAssigned, 403, 'Unauthorized: You are not assigned to teach this subject and class section.');
        }

        $filePath = null;
        $fileName = null;
        $fileSize = null;
        $fileType = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileType = strtolower($file->getClientOriginalExtension());
            $filePath = $file->store('diary-attachments/' . $instituteId, 'public');
        }

        $assignedDate = $validated['assigned_date'] ?? now()->toDateString();

        $diary = DailyDiary::create([
            'institute_id'     => $instituteId,
            'teacher_id'       => $user->id,
            'class_section_id' => $validated['class_section_id'],
            'subject_id'       => $validated['subject_id'],
            'entry_type'       => $validated['entry_type'],
            'title'            => $validated['title'],
            'content'          => $validated['content'],
            'file_path'        => $filePath,
            'file_name'        => $fileName,
            'file_size'        => $fileSize,
            'file_type'        => $fileType,
            'assigned_date'    => $assignedDate,
            'due_date'         => $validated['due_date'] ?? null,
            'reminder_morning' => $request->boolean('reminder_morning'),
            'is_active'        => true,
        ]);

        // Dispatch Real-Time & In-App Notification to all students in this section
        try {
            PortalNotificationService::notifyStudentDiaryAssigned($diary);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to dispatch diary student notification: ' . $e->getMessage());
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Daily diary entry published and pushed to students successfully.',
                'diary'   => $diary,
            ], 201);
        }

        return redirect()->route('teacher.diary.index')->with('success', "✅ Daily diary entry published! Students have received notifications.");
    }

    /**
     * Remove a diary entry.
     */
    public function destroy(DailyDiary $diary): RedirectResponse
    {
        $user = Auth::user();
        abort_if(!$user, 401, 'Unauthenticated.');

        if ($diary->teacher_id !== $user->id && !$user->isPrincipal() && !$user->isGlobalAdmin()) {
            abort(403, 'Unauthorized to delete this diary entry.');
        }

        if ($diary->file_path && Storage::disk('public')->exists($diary->file_path)) {
            Storage::disk('public')->delete($diary->file_path);
        }

        $diary->delete();

        return redirect()->back()->with('success', 'Daily diary entry removed successfully.');
    }
}
