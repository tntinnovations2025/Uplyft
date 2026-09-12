<?php

namespace App\Models;

use App\Models\Concerns\TenantIsolated;
use App\Models\Scopes\TenantPrivacyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Institute extends Model
{
    use HasFactory, SoftDeletes, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $table = $builder->getModel()->getTable();

        if (count($campusIds) === 1) {
            $builder->where($table.'.id', $campusIds[0]);
        } else {
            $builder->whereIn($table.'.id', $campusIds);
        }
    }

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'icon_path',
        'subscription_tier',
        'subscription_starts_at',
        'subscription_expires_at',
        'is_active',
        'is_onboarded',
        'contact_email',
        'contact_phone',
        'city',
        'country',
        'education_systems',
        'tenant_db_name',
        'organization_id',
        'campus_bg_path',
    ];

    // ── Education system options (used in registration & display) ────────
    public static array $educationSystemLabels = [
        'matric' => '🎓 Matric  (Play Group – Grade 10)',
        'higher_sec' => '📘 Higher Secondary  (1st & 2nd Year / FSc / FA)',
        'o_a_level' => '🌍 O / A Level',
        'acca' => '💼 ACCA / Professional',
        'other' => '📋 Other',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_onboarded' => 'boolean',
        'subscription_starts_at' => 'date',
        'subscription_expires_at' => 'date',
        'education_systems' => 'array',
    ];

    // ── Boot: auto-generate slug & tenant_db_name ────────────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Institute $institute) {
            if (empty($institute->slug)) {
                $institute->slug = Str::slug($institute->name);
            }
            if (empty($institute->tenant_db_name)) {
                $prefix = config('database.institute_prefix', 'uplifyt_inst_');
                $institute->tenant_db_name = $prefix.Str::snake(Str::slug($institute->name));
            }
        });

        // ── DATA PRIVACY: Apply global scope to ALL queries by default ───────
        // This prevents Global Admin queries from accidentally pulling
        // tenant-specific operational data (marks, attendance, etc.)
        // unless emergency override is explicitly activated.
        static::addGlobalScope(new TenantPrivacyScope);

        // ── CASCADE DELETION: Cleanly purge dependent data on institute deletion ──
        static::deleting(function (Institute $institute) {
            $instId = $institute->id;
            // 1. Dependent users
            \App\Models\User::withoutGlobalScopes()->where('institute_id', $instId)->forceDelete();

            // 2. Classes, sections, subjects
            $classIds = \App\Models\InstituteClass::withoutGlobalScopes()->where('institute_id', $instId)->pluck('id')->toArray();
            $subjectIds = !empty($classIds) ? \App\Models\Subject::withoutGlobalScopes()->whereIn('institute_class_id', $classIds)->pluck('id')->toArray() : [];

            // 3. Materials and vectors
            if (!empty($subjectIds)) {
                $materialIds = \App\Models\SubjectMaterial::whereIn('subject_id', $subjectIds)->pluck('id')->toArray();
                if (!empty($materialIds)) {
                    \App\Models\RagDocumentChunk::whereIn('subject_material_id', $materialIds)->delete();
                    \App\Models\SubjectMaterial::whereIn('id', $materialIds)->delete();
                }
                \App\Models\RagDocumentChunk::whereIn('subject_id', $subjectIds)->delete();
                \App\Models\Subject::withoutGlobalScopes()->whereIn('id', $subjectIds)->delete();
            }

            // 4. Daily Diaries
            \App\Models\DailyDiary::withoutGlobalScopes()->where('institute_id', $instId)->delete();

            // 5. Classes
            \App\Models\InstituteClass::withoutGlobalScopes()->where('institute_id', $instId)->get()->each->delete();
        });
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function featureToggles(): HasOne
    {
        return $this->hasOne(InstituteFeatureToggle::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(InstituteSetting::class);
    }

    public function getSettingsAttribute(): InstituteSetting
    {
        return InstituteSetting::getForInstitute($this->id);
    }

    public function classAssignments(): HasMany
    {
        return $this->hasMany(InstituteClassAssignment::class);
    }

    /**
     * All users belonging to this institute.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * The principal(s) assigned to this institute.
     */
    public function principals()
    {
        return $this->hasMany(User::class)->where('role', 'principal');
    }

    public function assignedClasses()
    {
        return $this->belongsToMany(
            SystemClass::class,
            'institute_class_assignments',
            'institute_id',
            'system_class_id'
        )->withPivot('is_active', 'assigned_at');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isSubscriptionActive(): bool
    {
        if (! $this->subscription_expires_at) {
            return true; // Never set = perpetual (demo / trial)
        }

        return now()->lessThanOrEqualTo($this->subscription_expires_at);
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) optional($this->featureToggles)->$feature;
    }

    public function hasCustomLogo(): bool
    {
        return ! empty($this->logo_path);
    }

    public function hasCustomIcon(): bool
    {
        return ! empty($this->icon_path);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? asset('storage/' . ltrim($this->logo_path, '/'))
            : null;
    }

    public function getIconUrlAttribute(): ?string
    {
        if ($this->icon_path) {
            return asset('storage/' . ltrim($this->icon_path, '/'));
        }

        return $this->logo_url;
    }

    public function getDisplayInitialAttribute(): string
    {
        return strtoupper(substr(trim($this->name ?: 'UPLYFT'), 0, 1));
    }

    public function getBgUrlAttribute(): string
    {
        return $this->campus_bg_path
            ? asset('storage/' . ltrim($this->campus_bg_path, '/'))
            : asset('images/default_campus_bg.jpg');
    }
}
