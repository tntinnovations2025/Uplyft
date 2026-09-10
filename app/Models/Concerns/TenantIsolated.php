<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Adds a global tenant-isolation scope to a model.
 *
 * Direct-column models are constrained via their own `institute_id` column
 * by default. Models that only reach an institute through a relation chain
 * (e.g. Subject -> InstituteClass -> institute_id) override
 * `applyTenantIsolation()` with the appropriate whereHas path.
 *
 * When no tenant context is resolvable (queued jobs, console, seeders), the
 * scope safely becomes a no-op so legitimate background flows keep working.
 */
trait TenantIsolated
{
    public static function bootTenantIsolated(): void
    {
        static::addGlobalScope('tenant_isolation', static function (Builder $builder) {
            $campusIds = static::authorizedCampusIdsForTenantScope();

            if ($campusIds === null || count($campusIds) === 0) {
                return;
            }

            static::applyTenantIsolation($builder, $campusIds);
        });
    }

    /**
     * Tenant context resolution:
     * - Authenticated users → their authorized campus set (org principals get
     *   every campus in their organization; everyone else strictly their own).
     * - Queue/console/no-auth flows → single `current_institute_id` binding
     *   when available; otherwise the scope becomes a no-op.
     */
    protected static function authorizedCampusIdsForTenantScope(): ?array
    {
        if (auth()->check()) {
            $user = auth()->user();

            if (method_exists($user, 'authorizedCampusIds')) {
                return $user->authorizedCampusIds();
            }

            $homeId = $user->institute_id ?? null;

            return $homeId !== null ? [(int) $homeId] : null;
        }

        if (app()->bound('current_institute_id') && app('current_institute_id')) {
            return [(int) app('current_institute_id')];
        }

        return null;
    }

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $table = $builder->getModel()->getTable();

        if (count($campusIds) === 1) {
            $builder->where($table.'.institute_id', $campusIds[0]);
        } else {
            $builder->whereIn($table.'.institute_id', $campusIds);
        }
    }
}