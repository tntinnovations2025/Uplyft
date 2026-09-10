<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\ClassSection;
use App\Models\InstituteClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherSubjectSection;
use App\Models\Timetable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AcademicTermController extends Controller
{
    public function index(): View
    {
        $instituteId = auth()->user()->institute_id;

        $terms = AcademicTerm::where('institute_id', $instituteId)
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        $activeTerm = $terms->firstWhere('is_active', true);

        return view('principal.academic-terms.index', compact('terms', 'activeTerm'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after:start_date',
            'promotion_deadline' => 'nullable|date',
            'is_active'          => 'nullable|boolean',
        ]);

        $instituteId = auth()->user()->institute_id;
        $deadline = !empty($validated['promotion_deadline'])
            ? \Carbon\Carbon::parse($validated['promotion_deadline'])->format('Y-m-d H:i:s')
            : null;

        $term = AcademicTerm::create([
            'institute_id'       => $instituteId,
            'name'               => $validated['name'],
            'start_date'         => $validated['start_date'],
            'end_date'           => $validated['end_date'],
            'promotion_deadline' => $deadline,
            'is_active'          => false,
        ]);

        // If marked active or first term for institute, activate it
        if (!empty($validated['is_active']) || AcademicTerm::where('institute_id', $instituteId)->count() === 1) {
            $term->markAsActive();
        }

        return redirect()
            ->route('principal.academic-terms.index')
            ->with('success', "Academic Term '{$term->name}' created successfully.");
    }

    public function updateDeadline(Request $request, AcademicTerm $term): RedirectResponse
    {
        if ($term->institute_id !== auth()->user()->institute_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'promotion_deadline' => 'nullable|date',
        ]);

        $deadline = !empty($validated['promotion_deadline'])
            ? \Carbon\Carbon::parse($validated['promotion_deadline'])->format('Y-m-d H:i:s')
            : null;

        $term->update([
            'promotion_deadline' => $deadline,
        ]);

        return redirect()
            ->back()
            ->with('success', "Class Promotion deadline for '{$term->name}' has been updated.");
    }

    public function setActive(AcademicTerm $term): RedirectResponse
    {
        // Security check
        if ($term->institute_id !== auth()->user()->institute_id) {
            abort(403, 'Unauthorized action.');
        }

        $term->markAsActive();

        return redirect()
            ->back()
            ->with('success', "Active Academic Session switched to '{$term->name}'.");
    }

    /**
     * Import / Clone classes, subjects, allocations, and students from a source term to a target term.
     */
    public function cloneData(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_term_id'     => 'required|exists:academic_terms,id',
            'target_term_id'     => 'required|exists:academic_terms,id|different:source_term_id',
            'import_classes'     => 'nullable|boolean',
            'import_students'    => 'nullable|boolean',
            'import_allocations' => 'nullable|boolean',
        ]);

        $instituteId = auth()->user()->institute_id;
        $sourceTerm  = AcademicTerm::where('institute_id', $instituteId)->findOrFail($validated['source_term_id']);
        $targetTerm  = AcademicTerm::where('institute_id', $instituteId)->findOrFail($validated['target_term_id']);

        $importedClassesCount     = 0;
        $importedSectionsCount    = 0;
        $importedSubjectsCount    = 0;
        $importedAllocationsCount = 0;
        $importedStudentsCount    = 0;

        DB::transaction(function () use (
            $instituteId,
            $sourceTerm,
            $targetTerm,
            $validated,
            &$importedClassesCount,
            &$importedSectionsCount,
            &$importedSubjectsCount,
            &$importedAllocationsCount,
            &$importedStudentsCount
        ) {
            $sectionMap = []; // [source_section_id => target_section_id]
            $subjectMap = []; // [source_subject_id => target_subject_id]

            $sourceClasses = InstituteClass::where('institute_id', $instituteId)
                ->where('academic_term_id', $sourceTerm->id)
                ->with(['sections', 'subjects'])
                ->get();

            foreach ($sourceClasses as $sc) {
                // Find or create target class
                $targetClass = InstituteClass::firstOrCreate(
                    [
                        'institute_id'     => $instituteId,
                        'academic_term_id' => $targetTerm->id,
                        'system_class_id'  => $sc->system_class_id,
                        'custom_name'      => $sc->custom_name,
                    ]
                );
                $importedClassesCount++;

                // Clone Sections
                foreach ($sc->sections as $sec) {
                    $targetSec = ClassSection::firstOrCreate(
                        [
                            'institute_class_id' => $targetClass->id,
                            'section_name'       => $sec->section_name,
                        ],
                        [
                            'room_id'  => $sec->room_id,
                            'capacity' => $sec->capacity,
                        ]
                    );
                    $sectionMap[$sec->id] = $targetSec->id;
                    $importedSectionsCount++;
                }

                // Clone Subjects & Link Textbook Materials
                foreach ($sc->subjects as $sub) {
                    $targetSub = Subject::firstOrCreate(
                        [
                            'institute_class_id' => $targetClass->id,
                            'subject_name'       => $sub->subject_name,
                        ],
                        [
                            'subject_code'      => $sub->subject_code,
                            'weekly_frequency' => $sub->weekly_frequency,
                            'periods_per_week'  => $sub->periods_per_week,
                            'duration_minutes'  => $sub->duration_minutes,
                        ]
                    );
                    $subjectMap[$sub->id] = $targetSub->id;
                    $importedSubjectsCount++;

                    // Automatically retain and link centralized textbook PDFs & RAG chunks for new session
                    $matchingMaterials = \App\Models\SubjectMaterial::where('subject_id', $sub->id)
                        ->whereHas('subject.instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
                        ->get();

                    foreach ($matchingMaterials as $mat) {
                        if ($mat->subject_id !== $targetSub->id) {
                            $newMat = \App\Models\SubjectMaterial::firstOrCreate(
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
                                    \App\Models\RagDocumentChunk::create([
                                        'subject_material_id' => $newMat->id,
                                        'chunk_index'         => $chunk->chunk_index,
                                        'chunk_content'       => $chunk->chunk_content,
                                        'embedding_vector'    => $chunk->embedding_vector,
                                        'token_count'         => $chunk->token_count,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Clone Faculty Allocations if requested
            if (!empty($validated['import_allocations'])) {
                $sourceAllocations = TeacherSubjectSection::where('academic_term_id', $sourceTerm->id)->get();
                foreach ($sourceAllocations as $alloc) {
                    $targetSecId = $sectionMap[$alloc->class_section_id] ?? null;
                    $targetSubId = $subjectMap[$alloc->subject_id] ?? null;

                    if ($targetSecId && $targetSubId) {
                        TeacherSubjectSection::firstOrCreate(
                            [
                                'academic_term_id' => $targetTerm->id,
                                'teacher_id'       => $alloc->teacher_id,
                                'subject_id'       => $targetSubId,
                                'class_section_id' => $targetSecId,
                            ],
                            [
                                'periods_per_week' => $alloc->periods_per_week,
                                'duration_minutes' => $alloc->duration_minutes,
                                'allowed_days'     => $alloc->allowed_days,
                            ]
                        );
                        $importedAllocationsCount++;
                    }
                }
            }

            // Import / Enroll Students into Target Term Classes if requested (Historical Preservation)
            if (!empty($validated['import_students'])) {
                $sourceStudents = Student::where('institute_id', $instituteId)
                    ->where(function ($q) use ($sourceTerm, $sectionMap) {
                        $q->where('academic_term_id', $sourceTerm->id)
                          ->orWhereIn('class_section_id', array_keys($sectionMap));
                    })->get();

                foreach ($sourceStudents as $student) {
                    $targetSecId = $sectionMap[$student->class_section_id] ?? null;
                    if ($targetSecId) {
                        $existingTargetStudent = Student::where('institute_id', $instituteId)
                            ->where('academic_term_id', $targetTerm->id)
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
                            $newStudentData['academic_term_id'] = $targetTerm->id;
                            $newStudentData['class_section_id'] = $targetSecId;

                            Student::create($newStudentData);
                            $importedStudentsCount++;

                            $newSec = ClassSection::find($targetSecId);
                            if ($newSec) {
                                $newSec->increment('enrolled_students');
                            }
                        }
                    }
                }
            }
        });

        return redirect()
            ->route('principal.academic-terms.index')
            ->with('success', "Import complete from '{$sourceTerm->name}' to '{$targetTerm->name}': {$importedClassesCount} Classes, {$importedSectionsCount} Sections, {$importedSubjectsCount} Subjects, {$importedAllocationsCount} Faculty Allocations, and {$importedStudentsCount} Students enrolled.");
    }

    public function destroy(AcademicTerm $term): RedirectResponse
    {
        if ($term->institute_id !== auth()->user()->institute_id) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($term) {
            // 1. Delete all timetable matrix entries for this term
            Timetable::where('academic_term_id', $term->id)->delete();

            // 2. Delete teacher allocations for this term
            TeacherSubjectSection::where('academic_term_id', $term->id)->delete();

            // 3. Delete attendance logs & settings for this term
            Attendance::where('academic_term_id', $term->id)->delete();
            AttendanceSetting::where('academic_term_id', $term->id)->delete();

            // 4. Detach students from this term
            Student::where('academic_term_id', $term->id)->update([
                'academic_term_id' => null,
                'class_section_id' => null,
            ]);

            // 5. Delete institute classes (and cascading sections & subjects) for this term
            $classes = InstituteClass::where('academic_term_id', $term->id)->get();
            foreach ($classes as $class) {
                foreach ($class->sections as $section) {
                    $section->delete();
                }
                foreach ($class->subjects as $subject) {
                    $subject->delete();
                }
                $class->delete();
            }

            // 6. Record active state and delete term
            $wasActive = $term->is_active;
            $term->delete();

            // 7. If deleted term was active, promote another term to active if available
            if ($wasActive) {
                $nextTerm = AcademicTerm::where('institute_id', $term->institute_id)->latest('start_date')->first();
                if ($nextTerm) {
                    $nextTerm->markAsActive();
                }
            }
        });

        return redirect()
            ->route('principal.academic-terms.index')
            ->with('success', "Academic Term '{$term->name}' and its entire cascading data have been permanently removed.");
    }
}
