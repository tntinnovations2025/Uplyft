<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;

/**
 * Assessment Policy
 *
 * Governs authorization for assessment CRUD operations.
 * - Teachers can create/update their own assessments (quizzes, assignments, projects).
 * - Principals can edit strict dates/times for midterms and finals.
 * - Only the creator or a principal can delete an assessment.
 */
class AssessmentPolicy
{
    /**
     * Any authenticated teacher/principal can view assessments in their institute.
     */
    public function viewAny(User $user): bool
    {
        return $user->isTeacher() || $user->isPrincipal();
    }

    /**
     * View a specific assessment (same institute check done via controller).
     */
    public function view(User $user, Assessment $assessment): bool
    {
        return $user->isTeacher() || $user->isPrincipal();
    }

    /**
     * Teachers and principals can create assessments.
     */
    public function create(User $user): bool
    {
        return $user->isTeacher() || $user->isPrincipal();
    }

    /**
     * Teachers can update their own assessments if still editable.
     * Principals can update any assessment (to set dates/deadlines).
     */
    public function update(User $user, Assessment $assessment): bool
    {
        if ($user->isPrincipal()) {
            return true;
        }

        if ($user->isTeacher()) {
            return $assessment->creator_id === $user->id && $assessment->isEditable();
        }

        return false;
    }

    /**
     * Only the creator or a principal can delete a draft assessment.
     */
    public function delete(User $user, Assessment $assessment): bool
    {
        if ($assessment->status !== Assessment::STATUS_DRAFT) {
            return false;
        }

        return $user->isPrincipal() || $assessment->creator_id === $user->id;
    }

    /**
     * Only Principal or Administration (delegated admin) can set strict start/end times for midterms and finals.
     */
    public function setExamSchedule(User $user, Assessment $assessment): bool
    {
        if (! in_array($assessment->type, Assessment::MANDATORY_TYPES)) {
            return false;
        }

        return $user->isAdministration();
    }

    /**
     * Only Principal or Administration (delegated admin) can set the result deadline.
     */
    public function setResultDeadline(User $user, Assessment $assessment): bool
    {
        return $user->isAdministration();
    }

    /**
     * Teachers or principals can grade assessments.
     */
    public function grade(User $user, Assessment $assessment): bool
    {
        if ($user->isPrincipal()) {
            return true;
        }

        return $user->isTeacher() && $assessment->creator_id === $user->id;
    }
}
