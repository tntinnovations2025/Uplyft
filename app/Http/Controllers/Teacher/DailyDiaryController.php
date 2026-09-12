<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\DailyDiary;
use App\Models\TeacherSubjectSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyDiaryController extends Controller
{
    /**
     * Store a newly created daily diary entry.
     * Enforces strict authorization against TeacherSubjectSection assignments (IDOR defense).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        abort_if(! $user, 401, 'Unauthenticated.');

        $validated = $request->validate([
            'class_section_id' => ['required', 'integer', 'exists:class_sections,id'],
            'subject_id'       => ['required', 'integer', 'exists:subjects,id'],
            'entry_type'       => ['nullable', 'string', 'in:homework,test_alert,announcement,classwork'],
            'title'            => ['required', 'string', 'max:255'],
            'content'          => ['required', 'string'],
            'assigned_date'    => ['nullable', 'date'],
        ]);

        // IDOR Authorization: Verify that this teacher is assigned to this section & subject
        if (! $user->isPrincipal() && ! $user->isGlobalAdmin()) {
            $isAssigned = TeacherSubjectSection::where('teacher_id', $user->id)
                ->where('class_section_id', $validated['class_section_id'])
                ->where('subject_id', $validated['subject_id'])
                ->exists();

            abort_if(! $isAssigned, 403, 'Unauthorized: You are not assigned to teach this subject and section.');
        }

        $diary = DailyDiary::create([
            'institute_id'     => $user->institute_id,
            'teacher_id'       => $user->id,
            'class_section_id' => $validated['class_section_id'],
            'subject_id'       => $validated['subject_id'],
            'entry_type'       => $validated['entry_type'] ?? 'homework',
            'title'            => $validated['title'],
            'content'          => $validated['content'],
            'assigned_date'    => $validated['assigned_date'] ?? now()->toDateString(),
            'is_active'        => true,
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Daily diary entry created successfully.',
                'diary'   => $diary,
            ], 201);
        }

        return redirect()->back()->with('success', 'Daily diary entry created successfully.');
    }
}
