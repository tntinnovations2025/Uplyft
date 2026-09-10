<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectMaterial;
use App\Models\User;

class SubjectMaterialPolicy
{
    /**
     * Determine if the user can view materials for a given subject.
     */
    public function view(User $user, Subject $subject): bool
    {
        // 1. Principal & Administration have full visibility
        if ($user->isAdministration()) {
            return true;
        }

        // 2. Students can only view materials for subjects in their class section
        if ($user->isStudent()) {
            $student = $user->studentProfile ?? Student::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();

            if (!$student || !$student->class_section_id) {
                return false;
            }

            $section = $student->classSection;
            $classId = $section ? $section->institute_class_id : null;

            // Direct class match
            if ($classId && $subject->institute_class_id === $classId) {
                return true;
            }

            // Or teacher assignment match for the student's section
            return $subject->teacherAssignments()
                ->where('class_section_id', $student->class_section_id)
                ->exists();
        }

        // 3. Teachers can only view materials for subjects they teach
        if ($user->isTeacher()) {
            $teacherId = $user->id;
            $profileId = $user->teacherProfile?->id;

            return $subject->teacherAssignments()
                ->where(function ($q) use ($teacherId, $profileId) {
                    $q->where('teacher_id', $teacherId);
                    if ($profileId) {
                        $q->orWhere('teacher_id', $profileId);
                    }
                })->exists()
                || $subject->timetables()->where('teacher_id', $teacherId)->exists();
        }

        return false;
    }

    /**
     * Determine if the user can upload materials for a given subject.
     */
    public function upload(User $user, Subject $subject): bool
    {
        if ($user->isAdministration()) {
            return true;
        }

        if ($user->isTeacher()) {
            return $this->view($user, $subject);
        }

        return false;
    }

    /**
     * Determine if the user can delete a subject material.
     */
    public function delete(User $user, SubjectMaterial $material): bool
    {
        if ($user->isAdministration()) {
            return true;
        }

        return $material->uploaded_by === $user->id;
    }
}
