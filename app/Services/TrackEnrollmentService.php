<?php

namespace App\Services;

use App\Models\AcademicTrack;
use App\Models\ClassSection;
use App\Models\ClassSubject;
use App\Models\InstituteClass;
use App\Models\Student;
use App\Models\StudentSubjectEnrollment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrackEnrollmentService
{
    /**
     * Retrieve the subject pool for a class, distinguishing compulsory vs. elective pools
     * and listing configured academic tracks (with backward-compatibility fallback for junior grades).
     */
    public function getAvailableSubjectsForClass(int $classId): array
    {
        $instituteClass = InstituteClass::with([
            'systemClass',
            'classSubjects.subject',
            'subjects',
            'academicTracks' => fn ($q) => $q->where('is_active', true)->with('subjects'),
        ])->findOrFail($classId);

        $configuredClassSubjects = $instituteClass->classSubjects;
        $hasConfiguredPool = $configuredClassSubjects->isNotEmpty();

        if ($hasConfiguredPool) {
            $compulsorySubjects = $configuredClassSubjects
                ->where('subject_type', 'compulsory')
                ->pluck('subject')
                ->filter()
                ->values();

            $electiveSubjects = $configuredClassSubjects
                ->where('subject_type', 'elective')
                ->pluck('subject')
                ->filter()
                ->values();
        } else {
            // Junior Classes / Legacy Fallback: All class subjects default to Compulsory
            $compulsorySubjects = $instituteClass->subjects->values();
            $electiveSubjects = collect();
        }

        $tracks = $instituteClass->academicTracks->map(function (AcademicTrack $track) {
            return [
                'id' => $track->id,
                'track_name' => $track->track_name,
                'track_code' => $track->track_code,
                'description' => $track->description,
                'allow_custom_electives' => (bool) $track->allow_custom_electives,
                'subject_ids' => $track->subjects->pluck('id')->values()->all(),
                'subjects' => $track->subjects->map(fn ($s) => [
                    'id' => $s->id,
                    'subject_name' => $s->subject_name,
                    'subject_code' => $s->subject_code,
                    'credit_hours' => $s->credit_hours,
                ])->values()->all(),
            ];
        })->values();

        return [
            'class_id' => $instituteClass->id,
            'class_name' => $instituteClass->name,
            'has_configured_pool' => $hasConfiguredPool,
            'compulsory_subjects' => $compulsorySubjects->map(fn ($s) => [
                'id' => $s->id,
                'subject_name' => $s->subject_name,
                'subject_code' => $s->subject_code,
                'credit_hours' => $s->credit_hours,
                'subject_type' => 'compulsory',
            ])->values()->all(),
            'elective_subjects' => $electiveSubjects->map(fn ($s) => [
                'id' => $s->id,
                'subject_name' => $s->subject_name,
                'subject_code' => $s->subject_code,
                'credit_hours' => $s->credit_hours,
                'subject_type' => 'elective',
            ])->values()->all(),
            'tracks' => $tracks->all(),
        ];
    }

    /**
     * Assign a student to their curriculum roster:
     * - Auto-enrolls all compulsory subjects for the class.
     * - Enrolls track-specific electives if a track is selected.
     * - Supports custom elective selections (if permitted by track or open electives).
     * - Seeds granular records into `student_subject_enrollments`.
     */
    public function assignTrackToStudent(
        int $studentUserId,
        int $classSectionId,
        ?int $trackId = null,
        array $customSubjectIds = []
    ): array {
        return DB::transaction(function () use ($studentUserId, $classSectionId, $trackId, $customSubjectIds) {
            $studentUser = User::findOrFail($studentUserId);
            $section = ClassSection::with('instituteClass')->findOrFail($classSectionId);
            $class = $section->instituteClass;

            if (! $class) {
                throw new InvalidArgumentException("Class section [{$classSectionId}] is not linked to a valid class.");
            }

            $instituteId = $section->instituteClass->institute_id
                ?? $studentUser->institute_id
                ?? auth()->user()?->institute_id;

            // 1. Fetch available subjects & pools for this class
            $pool = $this->getAvailableSubjectsForClass($class->id);
            $compulsorySubjectIds = collect($pool['compulsory_subjects'])->pluck('id')->map(fn ($id) => (int) $id)->all();
            $allowedElectiveIds = collect($pool['elective_subjects'])->pluck('id')->map(fn ($id) => (int) $id)->all();

            $selectedElectiveIds = [];
            $academicTrack = null;

            // 2. Validate Track (if specified)
            if ($trackId !== null) {
                $academicTrack = AcademicTrack::with('subjects')
                    ->where('class_id', $class->id)
                    ->where('is_active', true)
                    ->findOrFail($trackId);

                $trackSubjectIds = $academicTrack->subjects->pluck('id')->map(fn ($id) => (int) $id)->all();

                if (! empty($customSubjectIds)) {
                    // Check if track permits custom combinations
                    if (! $academicTrack->allow_custom_electives) {
                        // If custom modifications are not allowed, enforce the strict track bundle
                        $selectedElectiveIds = $trackSubjectIds;
                    } else {
                        // Allow customization but verify chosen subjects belong to class elective pool or track bundle
                        $validElectiveChoices = array_unique(array_merge($allowedElectiveIds, $trackSubjectIds));
                        $sanitizedCustom = array_map('intval', $customSubjectIds);

                        foreach ($sanitizedCustom as $chosenId) {
                            if (! in_array($chosenId, $validElectiveChoices, true)) {
                                throw new InvalidArgumentException("Subject ID [{$chosenId}] is not an available elective for class [{$class->name}].");
                            }
                        }
                        $selectedElectiveIds = array_values(array_unique($sanitizedCustom));
                    }
                } else {
                    $selectedElectiveIds = $trackSubjectIds;
                }
            } else {
                // No track selected: Handle open electives (if any electives chosen)
                if (! empty($customSubjectIds)) {
                    $sanitizedCustom = array_map('intval', $customSubjectIds);
                    foreach ($sanitizedCustom as $chosenId) {
                        if (! in_array($chosenId, $allowedElectiveIds, true)) {
                            throw new InvalidArgumentException("Subject ID [{$chosenId}] is not an available elective for class [{$class->name}].");
                        }
                    }
                    $selectedElectiveIds = array_values(array_unique($sanitizedCustom));
                }
            }

            // 3. Complete Roster: All Compulsory + Selected Electives
            $finalSubjectIds = array_values(array_unique(array_merge($compulsorySubjectIds, $selectedElectiveIds)));

            // 4. Update / Upsert granular student_subject_enrollments
            $activeEnrollmentIds = [];
            foreach ($finalSubjectIds as $subjectId) {
                $enrollment = StudentSubjectEnrollment::updateOrCreate(
                    [
                        'student_id' => $studentUser->id,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'institute_id' => $instituteId,
                        'class_section_id' => $classSectionId,
                        'academic_track_id' => $trackId,
                        'enrollment_status' => 'active',
                    ]
                );
                $activeEnrollmentIds[] = $enrollment->id;
            }

            // 5. Mark any previously active enrollments that are not in the new roster as dropped
            StudentSubjectEnrollment::where('student_id', $studentUser->id)
                ->where('enrollment_status', 'active')
                ->whereNotIn('id', $activeEnrollmentIds)
                ->update(['enrollment_status' => 'dropped']);

            // 6. Sync track & selected subject preferences on the Student profile if present
            $studentProfile = Student::where('user_id', $studentUser->id)->first();
            if ($studentProfile) {
                $studentProfile->update([
                    'academic_track_id' => $trackId,
                    'selected_subject_ids' => $finalSubjectIds,
                ]);
            }

            return [
                'student_user_id' => $studentUser->id,
                'class_section_id' => $classSectionId,
                'class_id' => $class->id,
                'academic_track_id' => $trackId,
                'compulsory_subjects_count' => count($compulsorySubjectIds),
                'elective_subjects_count' => count($selectedElectiveIds),
                'enrolled_subjects' => $finalSubjectIds,
                'enrolled_count' => count($finalSubjectIds),
            ];
        });
    }
}
