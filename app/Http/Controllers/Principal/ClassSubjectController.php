<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\InstituteClass;
use App\Models\Room;
use App\Models\Subject;
use App\Models\SubjectMaterial;
use App\Models\RagDocumentChunk;
use App\Models\SystemClass;
use App\Models\TeacherSubjectSection;
use App\Services\ClassSectionConsolidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassSubjectController extends Controller
{
    public function __construct(protected ClassSectionConsolidationService $consolidationService) {}

    /**
     * Automatically find and assign the most optimal available room for a class section.
     * Criteria:
     * 1. Fits required seating capacity.
     * 2. Lowest current section allocation count for facility load balancing.
     * 3. Best capacity fit to avoid wasting large auditoriums on small sections.
     */
    protected function findOptimalRoom(int $instituteId, int $requiredCapacity = 40): ?Room
    {
        $rooms = Room::where('institute_id', $instituteId)
            ->withCount('classSections')
            ->get();

        if ($rooms->isEmpty()) {
            return null;
        }

        // Filter rooms that fit capacity, sorted by:
        // 1. Lowest current section allocation count (utilization balancing)
        // 2. Smallest capacity difference (optimal capacity fit)
        $optimalRoom = $rooms
            ->filter(fn ($r) => $r->capacity >= $requiredCapacity)
            ->sortBy([
                ['class_sections_count', 'asc'],
                ['capacity', 'asc'],
            ])
            ->first();

        // Fallback if no room meets required capacity: pick largest room with lowest load
        if (! $optimalRoom) {
            $optimalRoom = $rooms->sortBy([
                ['class_sections_count', 'asc'],
                ['capacity', 'desc'],
            ])->first();
        }

        return $optimalRoom;
    }

    public function index(): View
    {
        $user = auth()->user();
        $instituteId = $user->institute_id;
        $institute = $user->institute;

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        // Auto-consolidate existing classes for the active term
        if ($activeTerm) {
            $this->consolidationService->consolidateInstituteClasses($instituteId, $activeTerm->id);
        }

        $classes = InstituteClass::where('institute_id', $instituteId)
            ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
            ->with([
                'systemClass',
                'subjects',
                'sections' => function ($q) {
                    $q->withCount('students')->with(['students', 'classIncharge']);
                },
                'sections.room',
            ])
            ->withCount('students')
            ->get();

        $rooms = Room::where('institute_id', $instituteId)->orderBy('room_number')->get();

        // Filter education systems based on Global Admin institute configuration
        $enabledSystems = $institute->education_systems ?? [];
        if (empty($enabledSystems)) {
            // Default fallback if not explicitly restricted: enable all SaaS education systems
            $enabledSystems = ['matric', 'higher_sec', 'o_a_level', 'acca', 'professional', 'other'];
        } else {
            if (in_array('acca', $enabledSystems) || in_array('professional', $enabledSystems)) {
                $enabledSystems[] = 'acca';
                $enabledSystems[] = 'professional';
            }
        }

        $allTypeLabels = SystemClass::$educationTypeLabels;
        $educationTypeLabels = array_filter(
            $allTypeLabels,
            fn ($key) => in_array($key, $enabledSystems),
            ARRAY_FILTER_USE_KEY
        );

        $systemClasses = SystemClass::where('is_active', true)
            ->whereIn('education_type', $enabledSystems)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $allTerms = AcademicTerm::where('institute_id', $instituteId)
            ->orderByDesc('start_date')
            ->get();

        $allClasses = InstituteClass::where('institute_id', $instituteId)
            ->with(['academicTerm', 'sections'])
            ->orderBy('custom_name')
            ->get();

        $teachers = \App\Models\User::where('institute_id', $instituteId)
            ->whereIn('role', ['teacher', 'principal'])
            ->orderBy('name')
            ->get();

        return view('principal.classes-subjects.index', compact('classes', 'rooms', 'systemClasses', 'educationTypeLabels', 'teachers', 'allTerms', 'allClasses', 'activeTerm'));
    }

    public function updateClass(Request $request, InstituteClass $class): RedirectResponse
    {
        if ($class->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $validated = $request->validate([
            'custom_name' => 'required|string|max:255',
        ]);

        $oldName = $class->custom_name;
        $class->update([
            'custom_name' => trim($validated['custom_name']),
        ]);

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', "Class '{$oldName}' successfully renamed to '{$class->custom_name}'.");
    }

    public function updateSection(Request $request, ClassSection $section): RedirectResponse
    {
        if ($section->instituteClass->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $validated = $request->validate([
            'section_name' => 'required|string|max:100',
            'capacity'     => 'required|integer|min:1|max:1000',
        ]);

        $oldName = $section->section_name;
        $newCapacity = $validated['capacity'];
        $instituteId = auth()->user()->institute_id;

        $roomId = $section->room_id;
        $roomNumber = $section->room_number;

        if ($section->room && $section->room->capacity < $newCapacity) {
            $optimalRoom = $this->findOptimalRoom($instituteId, $newCapacity);
            if ($optimalRoom) {
                $roomId = $optimalRoom->id;
                $roomNumber = $optimalRoom->room_number;
            }
        }

        $section->update([
            'section_name' => trim($validated['section_name']),
            'capacity'     => $newCapacity,
            'room_id'      => $roomId,
            'room_number'  => $roomNumber,
        ]);

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', "Section '{$oldName}' updated to '{$section->section_name}' (Capacity: {$section->capacity}).");
    }

    /**
     * Dedicated full page for Class & Student Session Promotion workflow.
     */
    public function promotePage(Request $request)
    {
        $user = auth()->user();
        $instituteId = $user->institute_id;

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        if (! $activeTerm || ! $activeTerm->isPromotionWindowOpen()) {
            return redirect()
                ->route('principal.classes-subjects.index')
                ->with('error', 'Class Promotion is currently closed. The promotion deadline has expired or no promotion deadline has been configured for the active academic session.');
        }

        $allTerms = AcademicTerm::where('institute_id', $instituteId)
            ->orderByDesc('start_date')
            ->get();

        $allClasses = InstituteClass::where('institute_id', $instituteId)
            ->with(['academicTerm', 'sections'])
            ->withCount('students')
            ->get();

        $selectedSourceClassId = $request->query('source_class_id');

        return view('principal.classes-subjects.promote', compact(
            'activeTerm',
            'allTerms',
            'allClasses',
            'selectedSourceClassId'
        ));
    }

    /**
     * Preview promotion: Returns JSON with students who failed specific subjects
     * so the principal can decide how to handle them before confirming.
     */
    public function previewPromotion(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'source_class_id' => 'required|exists:institute_classes,id',
        ]);

        $instituteId = auth()->user()->institute_id;
        $sourceClass = InstituteClass::where('institute_id', $instituteId)
            ->with(['sections', 'subjects'])
            ->findOrFail($validated['source_class_id']);

        $failedStudents = [];
        $totalStudents = 0;
        $passedCount = 0;
        $failedCount = 0;

        foreach ($sourceClass->sections as $sec) {
            $students = \App\Models\Student::where('institute_id', $instituteId)
                ->where('class_section_id', $sec->id)
                ->with(['subjectResults.subject'])
                ->get();

            $totalStudents += $students->count();

            foreach ($students as $student) {
                $status = strtolower($student->annual_result_status ?? 'passed');

                if ($status === 'failed') {
                    $failedSubjects = $student->subjectResults
                        ->where('result_status', 'failed')
                        ->map(function ($sr) {
                            return [
                                'subject_name' => $sr->subject ? $sr->subject->subject_name : 'Unknown',
                                'marks_obtained' => $sr->marks_obtained,
                                'total_marks' => $sr->total_marks,
                            ];
                        })->values()->toArray();

                    $failedStudents[] = [
                        'id' => $student->id,
                        'name' => $student->full_name,
                        'roll_number' => $student->roll_number,
                        'section' => $sec->section_name,
                        'failed_subjects' => $failedSubjects,
                        'failed_subject_count' => count($failedSubjects),
                    ];
                    $failedCount++;
                } else {
                    $passedCount++;
                }
            }
        }

        return response()->json([
            'class_name' => $sourceClass->custom_name,
            'total_students' => $totalStudents,
            'passed_count' => $passedCount,
            'failed_count' => $failedCount,
            'failed_students' => $failedStudents,
        ]);
    }

    public function promoteClassRoster(Request $request): RedirectResponse
    {
        $instituteId = auth()->user()->institute_id;

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        if (! $activeTerm || ! $activeTerm->isPromotionWindowOpen()) {
            return back()->with('error', 'Class Promotion is currently closed. The promotion deadline has expired or no promotion deadline has been configured for the active academic session.');
        }

        $validated = $request->validate([
            'source_session_id'        => 'nullable|exists:academic_terms,id',
            'source_class_id'          => 'required|exists:institute_classes,id',
            'target_session_id'        => 'required|exists:academic_terms,id',
            'target_class_id'          => 'nullable|exists:institute_classes,id',
            'custom_target_class_name' => 'nullable|string|max:255',
            'promote_students'         => 'nullable|boolean',
            'copy_faculty_allocations' => 'nullable|boolean',
            'copy_textbook_materials'  => 'nullable|boolean',
            'failed_student_action'    => 'nullable|string|in:retain,promote_with_failed_subjects,promote_fully',
        ]);

        $sourceClass = InstituteClass::where('institute_id', $instituteId)
            ->with(['sections.students', 'subjects'])
            ->findOrFail($validated['source_class_id']);

        $targetSessionId = $validated['target_session_id'];

        // Determine or Create Target Class
        if (!empty($validated['target_class_id'])) {
            $targetClass = InstituteClass::where('institute_id', $instituteId)
                ->with('sections')
                ->findOrFail($validated['target_class_id']);
        } else if (!empty($validated['custom_target_class_name'])) {
            $targetClass = InstituteClass::firstOrCreate(
                [
                    'institute_id'     => $instituteId,
                    'academic_term_id' => $targetSessionId,
                    'custom_name'      => trim($validated['custom_target_class_name']),
                ],
                [
                    'system_class_id'  => $sourceClass->system_class_id,
                ]
            );
        } else {
            return back()->withInput()->with('error', 'Please select an existing target class OR enter a custom target class name.');
        }

        if ($sourceClass->id === $targetClass->id) {
            return back()->withInput()->with('error', 'Source class and Target class to promote to cannot be identical.');
        }

        $promotedStudentsCount = 0;
        $retainedStudentsCount = 0;
        $promotedWithFailedCount = 0;
        $transferredFacultyCount = 0;
        $linkedMaterialsCount = 0;

        $promoteStudents = $request->has('promote_students') ? (bool) $request->input('promote_students') : true;
        $copyFaculty     = $request->has('copy_faculty_allocations') ? (bool) $request->input('copy_faculty_allocations') : true;
        $copyMaterials   = $request->has('copy_textbook_materials') ? (bool) $request->input('copy_textbook_materials') : true;
        $failedAction    = $validated['failed_student_action'] ?? 'retain'; // retain | promote_with_failed_subjects | promote_fully

        // If source class has no sections, provision default section A
        if ($sourceClass->sections->isEmpty()) {
            $optRoom = $this->findOptimalRoom($instituteId, 40);
            ClassSection::firstOrCreate(
                ['institute_class_id' => $sourceClass->id, 'section_name' => 'A'],
                ['room_id' => $optRoom?->id, 'room_number' => $optRoom?->room_number ?? 'Unassigned Room', 'capacity' => $optRoom?->capacity ?? 40, 'enrolled_students' => 0]
            );
            $sourceClass->load('sections');
        }

        // Promote all sections and their students together based on Annual Result (Pass / Fail)
        foreach ($sourceClass->sections as $sourceSec) {
            $optRoom = $this->findOptimalRoom($instituteId, $sourceSec->capacity ?? 40);
            
            // Find or create matching section in target class with optimal room assignment
            $targetSec = ClassSection::firstOrCreate(
                [
                    'institute_class_id' => $targetClass->id,
                    'section_name'       => $sourceSec->section_name,
                ],
                [
                    'room_id'           => $optRoom?->id ?? $sourceSec->room_id,
                    'room_number'       => $optRoom?->room_number ?? $sourceSec->room_number ?? 'Unassigned Room',
                    'capacity'          => $optRoom?->capacity ?? $sourceSec->capacity ?? 40,
                    'enrolled_students' => 0,
                ]
            );

            // 1. PROMOTE STUDENTS BASED ON ANNUAL PASS/FAIL RESULT & FAILED ACTION PREFERENCE
            if ($promoteStudents) {
                $secStudents = \App\Models\Student::where('institute_id', $instituteId)
                    ->where('class_section_id', $sourceSec->id)
                    ->with('subjectResults')
                    ->get();

                foreach ($secStudents as $student) {
                    $isPassed = strtolower($student->annual_result_status ?? 'passed') !== 'failed';

                    if ($isPassed || $failedAction === 'promote_fully') {
                        // PASSED or PROMOTE_FULLY: Move to Target Class (e.g. Class 10)
                        $destSectionId = $targetSec->id;
                        $destClassName = $targetClass->custom_name;
                        $counterType = $isPassed ? 'promoted' : 'promote_fully';
                    } elseif ($failedAction === 'promote_with_failed_subjects') {
                        // PROMOTE WITH FAILED SUBJECTS: Move to Target Class but carry failed subject records
                        $destSectionId = $targetSec->id;
                        $destClassName = $targetClass->custom_name;
                        $counterType = 'promote_with_failed';
                    } else {
                        // RETAIN (default): Keep in Source Level Class in new session
                        $repeatClass = InstituteClass::firstOrCreate(
                            [
                                'institute_id'     => $instituteId,
                                'academic_term_id' => $targetSessionId,
                                'custom_name'      => $sourceClass->custom_name,
                            ],
                            [
                                'system_class_id'  => $sourceClass->system_class_id,
                            ]
                        );

                        $repeatRoom = $this->findOptimalRoom($instituteId, $sourceSec->capacity ?? 40);
                        $repeatSec = ClassSection::firstOrCreate(
                            [
                                'institute_class_id' => $repeatClass->id,
                                'section_name'       => $sourceSec->section_name,
                            ],
                            [
                                'room_id'           => $repeatRoom?->id ?? $sourceSec->room_id,
                                'room_number'       => $repeatRoom?->room_number ?? $sourceSec->room_number ?? 'Unassigned Room',
                                'capacity'          => $repeatRoom?->capacity ?? $sourceSec->capacity ?? 40,
                                'enrolled_students' => 0,
                            ]
                        );

                        $destSectionId = $repeatSec->id;
                        $destClassName = $repeatClass->custom_name;
                        $counterType = 'retained';
                    }

                    // Check if student already has a record in the target session
                    $existingTargetStudent = \App\Models\Student::where('institute_id', $instituteId)
                        ->where('academic_term_id', $targetSessionId)
                        ->where(function ($q) use ($student) {
                            if ($student->user_id) {
                                $q->where('user_id', $student->user_id);
                            } else {
                                $q->where('roll_number', $student->roll_number);
                            }
                        })
                        ->first();

                    if (!$existingTargetStudent) {
                        $newStudentData = $student->toArray();
                        unset($newStudentData['id'], $newStudentData['created_at'], $newStudentData['updated_at']);
                        $newStudentData['academic_term_id'] = $targetSessionId;
                        $newStudentData['class_section_id'] = $destSectionId;
                        $newStudentData['enrolled_program'] = $destClassName;

                        // Reset annual result status for new session (pending)
                        if ($counterType === 'promoted' || $counterType === 'promote_fully') {
                            $newStudentData['annual_result_status'] = 'pending';
                        }

                        $newStudent = \App\Models\Student::create($newStudentData);

                        // If promoting with failed subjects, copy the failed subject results to new session
                        if ($counterType === 'promote_with_failed') {
                            $failedResults = $student->subjectResults->where('result_status', 'failed');
                            foreach ($failedResults as $fr) {
                                // Find or create matching subject in target class
                                $matchingSub = Subject::where('institute_class_id', $targetClass->id)
                                    ->where('subject_name', $fr->subject ? $fr->subject->subject_name : '')
                                    ->first();

                                if ($matchingSub) {
                                    \App\Models\StudentSubjectResult::create([
                                        'institute_id'    => $instituteId,
                                        'student_id'      => $newStudent->id,
                                        'subject_id'      => $matchingSub->id,
                                        'academic_term_id' => $targetSessionId,
                                        'marks_obtained'  => $fr->marks_obtained,
                                        'total_marks'     => $fr->total_marks,
                                        'result_status'   => 'failed',
                                        'remarks'         => 'Carried forward from previous session — subject failure.',
                                    ]);
                                }
                            }
                        }
                    } else {
                        $existingTargetStudent->update([
                            'class_section_id' => $destSectionId,
                            'enrolled_program' => $destClassName,
                        ]);
                        $newStudent = $existingTargetStudent;
                    }

                    // Increment appropriate counter
                    match ($counterType) {
                        'promoted', 'promote_fully' => $promotedStudentsCount++,
                        'promote_with_failed'       => $promotedWithFailedCount++,
                        'retained'                  => $retainedStudentsCount++,
                    };
                }

                $targetSec->update(['enrolled_students' => \App\Models\Student::where('class_section_id', $targetSec->id)->count()]);
            }

            // 2. RETAIN & COPY FACULTY ALLOCATIONS FOR THIS SECTION
            if ($copyFaculty) {
                $sourceAllocations = TeacherSubjectSection::where('class_section_id', $sourceSec->id)->get();

                foreach ($sourceAllocations as $alloc) {
                    $sourceSub = $alloc->subject;
                    if (!$sourceSub) continue;

                    $targetSub = Subject::firstOrCreate(
                        [
                            'institute_class_id' => $targetClass->id,
                            'subject_name'       => $sourceSub->subject_name,
                        ],
                        [
                            'subject_code'     => $sourceSub->subject_code,
                            'credit_hours'     => $sourceSub->credit_hours,
                            'weekly_frequency' => $sourceSub->weekly_frequency ?? 4,
                        ]
                    );

                    TeacherSubjectSection::updateOrCreate(
                        [
                            'academic_term_id' => $targetSessionId,
                            'subject_id'       => $targetSub->id,
                            'class_section_id' => $targetSec->id,
                        ],
                        [
                            'teacher_id'       => $alloc->teacher_id,
                            'periods_per_week' => $alloc->periods_per_week,
                            'duration_minutes' => $alloc->duration_minutes,
                            'allowed_days'     => $alloc->allowed_days,
                        ]
                    );
                    $transferredFacultyCount++;
                }
            }
        }

        // 3. RETAIN & LINK CENTRALIZED TEXTBOOK PDF MATERIALS
        if ($copyMaterials) {
            foreach ($targetClass->subjects as $targetSub) {
                $matchingMaterials = SubjectMaterial::whereHas('subject.instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
                    ->where(function ($q) use ($targetSub) {
                        $q->where('title', 'LIKE', "%{$targetSub->subject_name}%")
                          ->orWhere('title', 'LIKE', "%" . ($targetSub->subject_code ?? 'XYZ') . "%");
                    })
                    ->get();

                foreach ($matchingMaterials as $mat) {
                    if ($mat->subject_id !== $targetSub->id) {
                        $newMat = SubjectMaterial::firstOrCreate(
                            [
                                'subject_id' => $targetSub->id,
                                'file_path'  => $mat->file_path,
                            ],
                            [
                                'uploaded_by'     => auth()->id(),
                                'title'           => $mat->title,
                                'document_type'   => $mat->document_type,
                                'mime_type'       => $mat->mime_type,
                                'file_size_bytes' => $mat->file_size_bytes,
                                'is_rag_indexed'  => true,
                            ]
                        );

                        if ($newMat->wasRecentlyCreated && $mat->ragChunks->count() > 0) {
                            foreach ($mat->ragChunks as $chunk) {
                                RagDocumentChunk::create([
                                    'subject_material_id' => $newMat->id,
                                    'chunk_index'         => $chunk->chunk_index,
                                    'chunk_content'       => $chunk->chunk_content,
                                    'embedding_vector'    => $chunk->embedding_vector,
                                    'token_count'         => $chunk->token_count,
                                ]);
                            }
                        }
                        $linkedMaterialsCount++;
                    }
                }
            }
        }

        $msg = "Successfully processed promotion for Class '{$sourceClass->custom_name}' across all sections!";
        if ($promotedStudentsCount > 0) {
            $msg .= " Promoted {$promotedStudentsCount} passed students to '{$targetClass->custom_name}'.";
        }
        if ($promotedWithFailedCount > 0) {
            $msg .= " Promoted {$promotedWithFailedCount} students to '{$targetClass->custom_name}' with failed subject records carried forward.";
        }
        if ($retainedStudentsCount > 0) {
            $msg .= " Retained {$retainedStudentsCount} failed students in '{$sourceClass->custom_name}' repeat section.";
        }
        if ($transferredFacultyCount > 0) {
            $msg .= " Preserved {$transferredFacultyCount} faculty member subject allocations.";
        }
        if ($linkedMaterialsCount > 0) {
            $msg .= " Retained {$linkedMaterialsCount} centralized textbook PDFs & RAG AI chunks.";
        }

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', $msg);
    }

    public function updateSectionIncharge(Request $request, ClassSection $section): RedirectResponse
    {
        if ($section->instituteClass->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $validated = $request->validate([
            'class_incharge_id' => 'nullable|exists:users,id',
        ]);

        $section->update([
            'class_incharge_id' => $validated['class_incharge_id'] ?? null,
        ]);

        $inchargeUser = $section->classIncharge;
        $inchargeName = $inchargeUser ? $inchargeUser->name : 'Unassigned';

        return redirect()
            ->back()
            ->with('success', "Class Incharge for Section '{$section->section_name}' updated to: {$inchargeName}.");
    }

    public function storeClass(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'system_class_id'        => 'nullable|exists:system_classes,id',
            'custom_name'            => 'required|string|max:255',
            'section_name'           => 'nullable|string|max:100',
            'capacity'               => 'required|integer|min:1|max:1000',
            'enrolled_students'      => 'nullable|integer|min:0',
            'enable_default_subjects'=> 'nullable|boolean',
            'link_central_materials' => 'nullable|boolean',
        ]);

        $instituteId = auth()->user()->institute_id;
        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        // Intelligent parsing: "9A" -> Class "9", Section "A"
        $parsed = $this->consolidationService->parseClassAndSection($validated['custom_name'], $validated['section_name'] ?? null);
        $targetClassName = $parsed['class_name'];
        $targetSectionName = $parsed['section_name'];
        $requiredCapacity = $validated['capacity'];

        // Automatic Intelligent Room Allocation
        $optimalRoom = $this->findOptimalRoom($instituteId, $requiredCapacity);
        $roomId      = $optimalRoom?->id;
        $roomNumber  = $optimalRoom?->room_number ?? 'Unassigned Room';
        $roomCapacity= $optimalRoom?->capacity ?? $requiredCapacity;

        $enrolled = $validated['enrolled_students'] ?? 0;
        if ($enrolled > $roomCapacity) {
            return back()->withInput()->with('error', "Enrolled students count ({$enrolled}) cannot exceed Allocated Room Capacity ({$roomCapacity}).");
        }

        $systemClassId = $validated['system_class_id'] ?? null;

        $existingClass = InstituteClass::where('institute_id', $instituteId)
            ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
            ->where(function ($q) use ($targetClassName, $validated) {
                $q->whereRaw('LOWER(custom_name) = ?', [strtolower($targetClassName)])
                    ->orWhereRaw('LOWER(custom_name) = ?', [strtolower($validated['custom_name'])]);
            })
            ->first();

        if ($existingClass) {
            $class = $existingClass;
            if ($systemClassId && !$class->system_class_id) {
                $class->update(['system_class_id' => $systemClassId]);
            }
        } else {
            $class = InstituteClass::create([
                'institute_id'     => $instituteId,
                'academic_term_id' => $activeTerm?->id,
                'system_class_id'  => $systemClassId,
                'custom_name'      => $targetClassName,
            ]);
        }

        $existingSection = ClassSection::where('institute_class_id', $class->id)
            ->whereRaw('LOWER(section_name) = ?', [strtolower($targetSectionName)])
            ->first();

        if (!$existingSection) {
            ClassSection::create([
                'institute_class_id' => $class->id,
                'section_name'       => $targetSectionName,
                'room_id'           => $roomId,
                'room_number'       => $roomNumber,
                'capacity'          => $roomCapacity,
                'enrolled_students' => $enrolled,
            ]);
        }

        // Auto-enable standard default subjects if pre-defined system class was chosen
        $createdSubjectsCount = 0;
        $linkedMaterialsCount = 0;

        if ($systemClassId) {
            $systemClass = SystemClass::find($systemClassId);
            $enableDefault = $request->has('enable_default_subjects') ? (bool) $request->input('enable_default_subjects') : true;

            if ($systemClass && $enableDefault && !empty($systemClass->default_subjects)) {
                foreach ($systemClass->default_subjects as $defSub) {
                    $subName = $defSub['name'] ?? null;
                    if (!$subName) continue;

                    $subject = Subject::firstOrCreate(
                        [
                            'institute_class_id' => $class->id,
                            'subject_name'       => $subName,
                        ],
                        [
                            'subject_code'      => $defSub['code'] ?? null,
                            'weekly_frequency' => $defSub['periods'] ?? 4,
                            'periods_per_week'  => $defSub['periods'] ?? 4,
                            'duration_minutes'  => 60,
                        ]
                    );

                    if ($subject->wasRecentlyCreated) {
                        $createdSubjectsCount++;
                    }

                    $linkMaterials = $request->has('link_central_materials') ? (bool) $request->input('link_central_materials') : true;
                    if ($linkMaterials && $subject->wasRecentlyCreated) {
                        $matchingMaterials = SubjectMaterial::whereHas('subject.instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
                            ->where(function ($q) use ($subName, $defSub) {
                                $q->where('title', 'LIKE', "%{$subName}%")
                                  ->orWhere('title', 'LIKE', "%" . ($defSub['code'] ?? 'XYZ') . "%");
                            })
                            ->get();

                        foreach ($matchingMaterials as $mat) {
                            if ($mat->subject_id !== $subject->id) {
                                $newMat = SubjectMaterial::firstOrCreate(
                                    [
                                        'subject_id' => $subject->id,
                                        'file_path'  => $mat->file_path,
                                    ],
                                    [
                                        'uploaded_by'     => auth()->id(),
                                        'title'           => $mat->title,
                                        'document_type'   => $mat->document_type,
                                        'mime_type'       => $mat->mime_type,
                                        'file_size_bytes' => $mat->file_size_bytes,
                                        'is_rag_indexed'  => true,
                                    ]
                                );

                                if ($newMat->wasRecentlyCreated && $mat->ragChunks->count() > 0) {
                                    foreach ($mat->ragChunks as $chunk) {
                                        RagDocumentChunk::create([
                                            'subject_material_id' => $newMat->id,
                                            'chunk_index'         => $chunk->chunk_index,
                                            'chunk_content'       => $chunk->chunk_content,
                                            'embedding_vector'    => $chunk->embedding_vector,
                                            'token_count'         => $chunk->token_count,
                                        ]);
                                    }
                                }
                                $linkedMaterialsCount++;
                            }
                        }
                    }
                }
            }
        }

        $msg = "Class '{$targetClassName}' provisioned with Section '{$targetSectionName}' and automatically assigned optimal room '{$roomNumber}'.";
        if ($createdSubjectsCount > 0) {
            $msg .= " Enabled {$createdSubjectsCount} standard default subjects.";
        }
        if ($linkedMaterialsCount > 0) {
            $msg .= " Auto-linked {$linkedMaterialsCount} centralized textbook PDFs & RAG AI chunks.";
        }

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', $msg);
    }

    public function destroyClass(InstituteClass $class): RedirectResponse
    {
        if ($class->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $class->delete();

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', "Class '{$class->custom_name}' deleted successfully.");
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institute_class_id' => 'required|exists:institute_classes,id',
            'section_name'       => 'required|string|max:100',
            'capacity'           => 'required|integer|min:1|max:1000',
            'enrolled_students'  => 'nullable|integer|min:0',
        ]);

        $class = InstituteClass::findOrFail($validated['institute_class_id']);
        if ($class->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $instituteId = auth()->user()->institute_id;
        $requiredCapacity = $validated['capacity'];

        // Automatic Intelligent Room Allocation
        $optimalRoom = $this->findOptimalRoom($instituteId, $requiredCapacity);
        $roomId      = $optimalRoom?->id;
        $roomNumber  = $optimalRoom?->room_number ?? 'Unassigned Room';
        $roomCapacity= $optimalRoom?->capacity ?? $requiredCapacity;

        $enrolled = $validated['enrolled_students'] ?? 0;
        if ($enrolled > $roomCapacity) {
            return back()->withInput()->with('error', "Enrolled students count ({$enrolled}) cannot exceed Allocated Room Capacity ({$roomCapacity}).");
        }

        ClassSection::create([
            'institute_class_id' => $class->id,
            'section_name'       => $validated['section_name'],
            'room_id'           => $roomId,
            'room_number'       => $roomNumber,
            'capacity'          => $roomCapacity,
            'enrolled_students' => $enrolled,
        ]);

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', "Section '{$validated['section_name']}' added to {$class->custom_name} with automatically assigned room '{$roomNumber}'.");
    }

    public function destroySection(ClassSection $section): RedirectResponse
    {
        if ($section->instituteClass->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $sectionName = $section->section_name;
        $section->delete();

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', "Section '{$sectionName}' deleted successfully.");
    }

    public function subjectsIndex(Request $request): View
    {
        $instituteId = auth()->user()->institute_id;
        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        $classes = InstituteClass::where('institute_id', $instituteId)
            ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
            ->with(['subjects.room', 'sections'])
            ->get();

        $subjects = Subject::whereHas('instituteClass', function ($q) use ($instituteId, $activeTerm) {
            $q->where('institute_id', $instituteId);
            if ($activeTerm) {
                $q->where('academic_term_id', $activeTerm->id);
            }
        })
            ->with(['instituteClass', 'room'])
            ->orderBy('subject_name', 'asc')
            ->get();

        $rooms = Room::where('institute_id', $instituteId)->orderBy('room_number')->get();

        return view('principal.subjects.index', compact('classes', 'subjects', 'rooms'));
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institute_class_id'        => 'required|exists:institute_classes,id',
            'subject_name'              => 'required|string|max:255',
            'subject_code'              => 'nullable|string|max:50',
            'credit_hours'              => 'required|integer|min:1|max:10',
            'lecture_duration_minutes'  => 'nullable|integer|min:15|max:480',
            'total_marks'               => 'nullable|integer|min:1|max:1000',
            'passing_marks'             => 'nullable|integer|min:1|max:1000',
            'mcq_weightage'             => 'nullable|numeric|min:0|max:100',
            'short_answer_weightage'    => 'nullable|numeric|min:0|max:100',
            'long_answer_weightage'     => 'nullable|numeric|min:0|max:100',
            'assignment_quiz_weightage' => 'nullable|numeric|min:0|max:100',
        ]);

        $class = InstituteClass::findOrFail($validated['institute_class_id']);
        if ($class->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        Subject::create([
            'institute_class_id'        => $class->id,
            'subject_name'              => $validated['subject_name'],
            'subject_code'              => $validated['subject_code'] ?? null,
            'credit_hours'              => $validated['credit_hours'],
            'lecture_duration_minutes'  => $validated['lecture_duration_minutes'] ?? 60,
            'total_marks'               => $validated['total_marks'] ?? 100,
            'passing_marks'             => $validated['passing_marks'] ?? 33,
            'mcq_weightage'             => $validated['mcq_weightage'] ?? 20.00,
            'short_answer_weightage'    => $validated['short_answer_weightage'] ?? 30.00,
            'long_answer_weightage'     => $validated['long_answer_weightage'] ?? 30.00,
            'assignment_quiz_weightage' => $validated['assignment_quiz_weightage'] ?? 20.00,
        ]);

        $total = $validated['total_marks'] ?? 100;
        $dur   = $validated['lecture_duration_minutes'] ?? 60;
        return redirect()
            ->back()
            ->with('success', "Subject '{$validated['subject_name']}' created with Lecture Duration: {$dur} mins, Total Marks: {$total}.");
    }

    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        if ($subject->instituteClass->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $validated = $request->validate([
            'subject_name'              => 'required|string|max:255',
            'subject_code'              => 'nullable|string|max:50',
            'credit_hours'              => 'required|integer|min:1|max:10',
            'lecture_duration_minutes'  => 'required|integer|min:15|max:480',
            'total_marks'               => 'required|integer|min:1|max:1000',
            'passing_marks'             => 'required|integer|min:1|max:1000',
            'mcq_weightage'             => 'required|numeric|min:0|max:100',
            'short_answer_weightage'    => 'required|numeric|min:0|max:100',
            'long_answer_weightage'     => 'required|numeric|min:0|max:100',
            'assignment_quiz_weightage' => 'required|numeric|min:0|max:100',
        ]);

        $subject->update($validated);

        // Sync lecture duration to all teacher subject allocations for this subject
        TeacherSubjectSection::where('subject_id', $subject->id)
            ->update(['duration_minutes' => $validated['lecture_duration_minutes']]);

        // Auto-regenerate timetable using updated subject lecture duration settings
        $this->consolidationService = app(\App\Services\ClassSectionConsolidationService::class);
        $generatorService = app(\App\Services\TimetableGeneratorService::class);
        $generatorService->regenerateForActiveTerm(auth()->user()->institute_id);

        return redirect()
            ->back()
            ->with('success', "Subject '{$subject->subject_name}' updated (Duration: {$validated['lecture_duration_minutes']} mins, Total Marks: {$validated['total_marks']}) and timetable auto-updated.");
    }

    public function destroySubject(Subject $subject): RedirectResponse
    {
        if ($subject->instituteClass->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $subjectName = $subject->subject_name;
        $subject->delete();

        return redirect()
            ->back()
            ->with('success', "Subject '{$subjectName}' deleted successfully.");
    }
}
