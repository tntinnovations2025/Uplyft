<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTrack;
use App\Models\ClassSubject;
use App\Models\InstituteClass;
use App\Models\Subject;
use App\Services\TrackEnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AcademicTrackController extends Controller
{
    protected TrackEnrollmentService $trackService;

    public function __construct(TrackEnrollmentService $trackService)
    {
        $this->trackService = $trackService;
    }

    /**
     * API endpoint returning class subject pool, compulsory/elective categorization,
     * and configured tracks for AJAX admission form dynamic loading.
     */
    public function tracksAndSubjects(InstituteClass $class): JsonResponse
    {
        $data = $this->trackService->getAvailableSubjectsForClass($class->id);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Display the Track & Subject Pool configuration page for a class.
     */
    public function index(InstituteClass $class): View|JsonResponse
    {
        $data = $this->trackService->getAvailableSubjectsForClass($class->id);
        $allClassSubjects = Subject::where('institute_class_id', $class->id)->get();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'class' => $class,
                'all_class_subjects' => $allClassSubjects,
                'pool_data' => $data,
            ]);
        }

        return view('principal.classes-subjects.tracks', [
            'class' => $class,
            'allClassSubjects' => $allClassSubjects,
            'poolData' => $data,
        ]);
    }

    /**
     * Configure or batch-update the subject pool (Compulsory vs. Elective) for a class.
     */
    public function storeSubjectPool(Request $request, InstituteClass $class): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'subjects' => 'required|array',
            'subjects.*.subject_id' => 'required|integer|exists:subjects,id',
            'subjects.*.subject_type' => 'required|string|in:compulsory,elective',
            'subjects.*.credit_hours' => 'nullable|integer|min:0',
            'subjects.*.weightage' => 'nullable|numeric|min:0|max:100',
        ]);

        $instituteId = $class->institute_id ?? auth()->user()?->institute_id;

        DB::transaction(function () use ($class, $instituteId, $validated) {
            $existingIds = [];

            foreach ($validated['subjects'] as $subjectItem) {
                $classSub = ClassSubject::updateOrCreate(
                    [
                        'class_id' => $class->id,
                        'subject_id' => $subjectItem['subject_id'],
                    ],
                    [
                        'institute_id' => $instituteId,
                        'subject_type' => $subjectItem['subject_type'],
                        'credit_hours' => $subjectItem['credit_hours'] ?? null,
                        'weightage' => $subjectItem['weightage'] ?? null,
                    ]
                );
                $existingIds[] = $classSub->id;
            }

            // Remove any class_subject mappings for this class not present in the payload
            ClassSubject::where('class_id', $class->id)
                ->whereNotIn('id', $existingIds)
                ->delete();
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Class subject pool successfully updated.',
                'data' => $this->trackService->getAvailableSubjectsForClass($class->id),
            ]);
        }

        return redirect()->back()->with('success', 'Subject pool updated successfully.');
    }

    /**
     * Create a new Academic Track (Bundle / Pack) for a class.
     */
    public function storeTrack(Request $request, InstituteClass $class): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'track_name' => 'required|string|max:255',
            'track_code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'allow_custom_electives' => 'nullable|boolean',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'integer|exists:subjects,id',
        ]);

        $instituteId = $class->institute_id ?? auth()->user()?->institute_id;

        $track = DB::transaction(function () use ($class, $instituteId, $validated) {
            $track = AcademicTrack::create([
                'institute_id' => $instituteId,
                'class_id' => $class->id,
                'track_name' => trim($validated['track_name']),
                'track_code' => !empty($validated['track_code']) ? strtoupper(trim($validated['track_code'])) : null,
                'description' => $validated['description'] ?? null,
                'allow_custom_electives' => (bool) ($validated['allow_custom_electives'] ?? false),
                'is_active' => true,
            ]);

            $track->subjects()->sync($validated['subject_ids']);

            return $track->load('subjects');
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Academic Track successfully created.',
                'track' => $track,
            ], 201);
        }

        return redirect()->back()->with('success', "Track '{$track->track_name}' created successfully.");
    }

    /**
     * Update an existing Academic Track.
     */
    public function updateTrack(Request $request, AcademicTrack $track): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'track_name' => 'sometimes|required|string|max:255',
            'track_code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'allow_custom_electives' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'subject_ids' => 'sometimes|required|array|min:1',
            'subject_ids.*' => 'integer|exists:subjects,id',
        ]);

        DB::transaction(function () use ($track, $validated) {
            $updateData = [];
            if (isset($validated['track_name'])) {
                $updateData['track_name'] = trim($validated['track_name']);
            }
            if (array_key_exists('track_code', $validated)) {
                $updateData['track_code'] = !empty($validated['track_code']) ? strtoupper(trim($validated['track_code'])) : null;
            }
            if (array_key_exists('description', $validated)) {
                $updateData['description'] = $validated['description'];
            }
            if (array_key_exists('allow_custom_electives', $validated)) {
                $updateData['allow_custom_electives'] = (bool) $validated['allow_custom_electives'];
            }
            if (array_key_exists('is_active', $validated)) {
                $updateData['is_active'] = (bool) $validated['is_active'];
            }

            if (!empty($updateData)) {
                $track->update($updateData);
            }

            if (isset($validated['subject_ids'])) {
                $track->subjects()->sync($validated['subject_ids']);
            }
        });

        $track->load('subjects');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Academic Track updated successfully.',
                'track' => $track,
            ]);
        }

        return redirect()->back()->with('success', 'Track updated successfully.');
    }

    /**
     * Delete an Academic Track.
     */
    public function destroyTrack(AcademicTrack $track): JsonResponse|RedirectResponse
    {
        $trackName = $track->track_name;
        $track->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Academic Track '{$trackName}' deleted successfully.",
            ]);
        }

        return redirect()->back()->with('success', "Track '{$trackName}' deleted successfully.");
    }
}
