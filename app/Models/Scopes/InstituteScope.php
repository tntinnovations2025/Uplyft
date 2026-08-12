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
        // 1. If a user is authenticated, isolate queries to their institute
        if (Auth::check() && isset(Auth::user()->institute_id)) {
            $builder->where($model->getTable() . '.institute_id', Auth::user()->institute_id);
            return;
        }

        // 2. Fallback check for public registration endpoints or queue workers:
        // Check if a global tenant ID has been bound to the application container.
        if (app()->bound('current_institute_id')) {
            $builder->where($model->getTable() . '.institute_id', app('current_institute_id'));
        }
    }
}
