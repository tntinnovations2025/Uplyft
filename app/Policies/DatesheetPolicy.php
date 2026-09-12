<?php

namespace App\Policies;

use App\Models\Datesheet;
use App\Models\User;

class DatesheetPolicy
{
    /**
     * Determine whether the user can view datesheets.
     */
    public function viewAny(User $user): bool
    {
        return $user->isPrincipal() || $user->isTeacher() || $user->isStudent();
    }

    /**
     * Determine whether the user can create datesheet entries.
     */
    public function create(User $user): bool
    {
        return $user->isAdministration() || $user->isPrincipal();
    }

    /**
     * Determine whether the user can update datesheet entries.
     */
    public function update(User $user, $datesheet): bool
    {
        if (! $user->isAdministration() && ! $user->isPrincipal()) {
            return false;
        }

        $authorizedCampuses = method_exists($user, 'authorizedCampusIds')
            ? ($user->authorizedCampusIds() ?: [$user->institute_id])
            : [$user->institute_id];

        return $datesheet->institute_id === $user->institute_id
            || in_array($datesheet->institute_id, $authorizedCampuses, true);
    }

    /**
     * Determine whether the user can delete the datesheet entry.
     * Enforces strict multi-tenant boundary matching (BUG-LMS-002).
     */
    public function delete(User $user, $datesheet): bool
    {
        if (! $user->isAdministration() && ! $user->isPrincipal()) {
            return false;
        }

        $authorizedCampuses = method_exists($user, 'authorizedCampusIds')
            ? ($user->authorizedCampusIds() ?: [$user->institute_id])
            : [$user->institute_id];

        return $datesheet->institute_id === $user->institute_id
            || in_array($datesheet->institute_id, $authorizedCampuses, true);
    }
}
