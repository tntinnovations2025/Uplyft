<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class InstituteScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // 1. Authenticated tenant: isolate to the user's authorized campus set
        //    (org principals → whereIn over their organization's campuses;
        //    everyone else → their own campus; global admin → no constraint).
        if (Auth::check()) {
            $user = Auth::user();

            if (method_exists($user, 'authorizedCampusIds')) {
                $campusIds = $user->authorizedCampusIds();

                if ($campusIds === null) {
                    // Global admin / user without campus: fall through to fallback.
                } elseif (count($campusIds) === 1) {
                    $builder->where($model->getTable() . '.institute_id', $campusIds[0]);
                    return;
                } else {
                    $builder->whereIn($model->getTable() . '.institute_id', $campusIds);
                    return;
                }
            } else {
                // Legacy fallback: prior single-institute behaviour
                $activeInstId = $user->institute_id ?? null;
                if ($activeInstId) {
                    $builder->where($model->getTable() . '.institute_id', $activeInstId);
                    return;
                }
            }
        }

        // 2. Fallback check for public registration endpoints, queue workers or background requests:
        if (app()->bound('current_institute_id')) {
            $builder->where($model->getTable() . '.institute_id', app('current_institute_id'));
        }
    }
}

