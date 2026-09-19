<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Real-time operational notification stream for Institute Principals and Campus Admins.
 */
Broadcast::channel('institute.{instituteId}.principal', function ($user, $instituteId) {
    if ($user->isGlobalAdmin()) {
        return true;
    }

    $activeInstId = method_exists($user, 'getActiveInstituteId') ? $user->getActiveInstituteId() : $user->institute_id;

    // Check if user is Principal or has delegated admin rights and belongs to this institute
    $isAuthorizedRole = $user->isPrincipal() || $user->hasDelegatedAdminRights();
    $belongsToInstitute = (int) $activeInstId === (int) $instituteId 
        || (method_exists($user, 'canAccessInstitute') && $user->canAccessInstitute((int) $instituteId));

    return $isAuthorizedRole && $belongsToInstitute;
});
