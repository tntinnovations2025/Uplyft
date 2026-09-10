<?php

namespace App\Policies;

use App\Models\User;

/**
 * UserPolicy â€“ Enforces hierarchical account creation and access control.
 *
 * Hierarchy:
 *  Global Admin â†’ can create Principals
 *  Principal    â†’ can create Teachers & Students within their institute
 *  Teacher (delegated) â†’ can create Teachers & Students within their institute
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

        // Principal / delegated teacher can view users in any campus of their organization
        if ($user->canCreateAccounts() && $user->canAccessInstitute($target->getHomeInstituteId())) {
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
        // Primary Principal protection: Only Global Admin can modify Primary Principal accounts
        if ($target->is_primary_principal && ! $user->isGlobalAdmin()) {
            return false;
        }

        // Self-update
        if ($user->id === $target->id) {
            return true;
        }

        // Principal / delegated teacher can update institute members across their organization's campuses
        if ($user->canCreateAccounts() && $user->canAccessInstitute($target->getHomeInstituteId())) {
            // Cannot update principals unless global admin
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
        if ($target->is_primary_principal && ! $user->isGlobalAdmin()) {
            return false;
        }

        if ($user->id === $target->id) {
            return false; // Cannot delete yourself
        }

        if ($user->canCreateAccounts() && $user->canAccessInstitute($target->getHomeInstituteId())) {
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
        if ($target->is_primary_principal && ! $user->isGlobalAdmin()) {
            return false;
        }

        return $user->isPrincipal()
            && $target->isTeacher()
            && $user->canAccessInstitute($target->getHomeInstituteId());
    }

    /**
     * Process a password reset request.
     * - Principals process requests for their institute's users
     * - Global Admin processes requests for principals
     */
    public function processPasswordReset(User $user, User $target): bool
    {
        if ($target->is_primary_principal && ! $user->isGlobalAdmin()) {
            return false;
        }

        // Principal can reset passwords for teachers/students in their organization's campuses
        if ($user->isPrincipal()
            && $user->canAccessInstitute($target->getHomeInstituteId())
            && ! $target->isPrincipal()) {
            return true;
        }

        return false; // Global admin handled by before()
    }
}
