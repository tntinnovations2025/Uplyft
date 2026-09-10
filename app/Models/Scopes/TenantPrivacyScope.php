<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * TenantPrivacyScope
 *
 * Applied globally to the Institute model.
 *
 * BEHAVIOUR BY DEFAULT:
 *   All queries on Institute will exclude sensitive operational columns
 *   (marks, attendance records, personal student data) — in practice this
 *   scope restricts the SELECT to the "governance" columns only unless
 *   withoutGlobalScope(TenantPrivacyScope::class) is explicitly called
 *   (= emergency override).
 *
 * EMERGENCY OVERRIDE:
 *   Set GLOBAL_ADMIN_EMERGENCY_OVERRIDE=true in .env
 *   OR call Institute::withoutGlobalScope(TenantPrivacyScope::class)
 *   in the specific query that legitimately needs unrestricted access.
 *
 * DESIGN NOTE:
 *   Tenant operational data (student marks, fee records, attendance logs)
 *   lives in separate tables that are never joined in Global-Admin queries.
 *   This scope acts as an architectural guardrail to enforce that contract.
 */
class TenantPrivacyScope implements Scope
{
    /**
     * Columns visible to the Global Admin by default.
     * Operational / PII columns are intentionally omitted.
     */
    private const ALLOWED_COLUMNS = [
        'institutes.id',
        'institutes.name',
        'institutes.slug',
        'institutes.logo_path',
        'institutes.subscription_tier',
        'institutes.subscription_starts_at',
        'institutes.subscription_expires_at',
        'institutes.is_active',
        'institutes.is_onboarded',
        'institutes.contact_email',
        'institutes.contact_phone',
        'institutes.city',
        'institutes.country',
        'institutes.education_systems',
        'institutes.tenant_db_name',
        'institutes.created_at',
        'institutes.updated_at',
        'institutes.deleted_at',
    ];

    public function apply(Builder $builder, Model $model): void
    {
        // If emergency override is active in .env, bypass the scope entirely
        if (config('uplyft.global_admin_emergency_override', false)) {
            return;
        }

        // Restrict SELECT to governance-safe columns only when the query
        // hasn't already specified a custom select (e.g. from a controller)
        if (empty($builder->getQuery()->columns)) {
            $builder->select(self::ALLOWED_COLUMNS);
        }
    }
}
