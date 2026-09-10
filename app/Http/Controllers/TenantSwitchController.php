<?php

namespace App\Http\Controllers;

use App\Models\Institute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TenantSwitchController extends Controller
{
    /**
     * Switch active institute / campus profile context for multi-institute organization owners & admins.
     */
    public function switchInstitute(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institute_id' => 'required|exists:institutes,id',
        ]);

        $targetId = (int) $validated['institute_id'];
        $user = auth()->user();

        if (! $user) {
            abort(401);
        }

        // Authorization: user may switch only to a campus within their authorized
        // campus set (own campus for teachers/staff/students; every campus of their
        // organization for principals; any campus for Global Admins).
        $campusIds = $user->authorizedCampusIds();

        $hasAccess = $campusIds === null || in_array($targetId, $campusIds, true);

        if (! $hasAccess) {
            return back()->with('error', 'Unauthorized access: You do not have permission to switch to this campus.');
        }

        // Store active institute ID in session & user record
        session(['active_institute_id' => $targetId]);

        if (Schema::hasColumn('users', 'current_institute_id')) {
            $user->update(['current_institute_id' => $targetId]);
        }

        $targetInstitute = Institute::withoutGlobalScopes()->find($targetId);
        $campusName = $targetInstitute ? $targetInstitute->name : "Campus #{$targetId}";

        return back()->with('success', "✨ Switched active campus context to '{$campusName}'.");
    }
}
