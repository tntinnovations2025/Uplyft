<?php

namespace App\Policies;

use App\Models\User;

/**
 * UserPolicy – Enforces hierarchical account creation and access control.
 *
 * Hierarchy:
 *  Global Admin → can create Principals
 *  Principal    → can create Teachers & Students within their institute
 *  Teacher (delegated) → can create Teachers & Students within their institute
 */
class UserPolicy
{
    /**
     * Global Admin can do everything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isGlobalAdmin()) {
            return true;
        }

        return null; // Fall through to specific checks
    }

    /**
     * View any list of users.
     * Principals and delegated teachers can view users in their institute.
     */
    public function viewAny(User $user): bool
    {
        return $user->isPrincipal() || $user->hasDelegatedAdminRights();
    }

    /**
     * View a specific user.
     * Must be in the same institute.
     */
    public function view(User $user, User $target): bool
    {
        // Users can always view their own profile
        if ($user->id === $target->id) {
            return true;
        }

        // Principal / delegated teacher can view users in their institute
        if ($user->canCreateAccounts() && $user->institute_id === $target->institute_id) {
            return true;
        }

        return false;
    }

    /**
     * Create a Principal account.
     * ONLY Global Admin can do this (handled by before()).
     */
    public function createPrincipal(User $user): bool
    {
        return false; // Only global_admin via before()
    }

    /**
     * Create a Teacher or Student account.
     * Allowed for Principals, or Teachers with is_delegated_admin == true,
     * but only within their own institute.
     */
    public function createStaffOrStudent(User $user): bool
    {
        return $user->canCreateAccounts();
    }

    /**
     * Update a user's profile.
     * - Users can update their own profile
     * - Principals can update users in their institute
     * - Delegated teachers can update users in their institute
     */
    public function update(User $user, User $target): bool
    {
        // Self-update
        if ($user->id === $target->id) {
            return true;
        }

        // Principal / delegated teacher can update institute members
        if ($user->canCreateAccounts() && $user->institute_id === $target->institute_id) {
            // But cannot update principals (only global admin can)
            return ! $target->isPrincipal();
        }

        return false;
    }

    /**
     * Delete / deactivate a user account.
     * Same rules as update, plus cannot delete self.
     */
    public function delete(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false; // Cannot delete yourself
        }

        if ($user->canCreateAccounts() && $user->institute_id === $target->institute_id) {
            return ! $target->isPrincipal();
        }

        return false;
    }

    /**
     * Toggle the delegation flag on a teacher.
     * Only Principals within the same institute can do this.
     */
    public function toggleDelegation(User $user, User $target): bool
    {
        return $user->isPrincipal()
            && $target->isTeacher()
            && $user->institute_id === $target->institute_id;
    }

    /**
     * Process a password reset request.
     * - Principals process requests for their institute's users
     * - Global Admin processes requests for principals
     */
    public function processPasswordReset(User $user, User $target): bool
    {
        // Principal can reset passwords for teachers/students in their institute
        if ($user->isPrincipal()
            && $user->institute_id === $target->institute_id
            && ! $target->isPrincipal()) {
            return true;
        }

        return false; // Global admin handled by before()
    }
}
