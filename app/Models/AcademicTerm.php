<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AcademicTerm extends Model
{
    use HasFactory, SoftDeletes, TenantIsolated;

    protected static function booted(): void
    {
        static::saved(function ($term) {
            if ($term->institute_id) {
                static::forgetActiveTermCache($term->institute_id);
            }
        });

        static::deleted(function ($term) {
            if ($term->institute_id) {
                static::forgetActiveTermCache($term->institute_id);
            }
        });
    }

    protected static array $resolvedActiveTerms = [];

    /**
     * Get the active academic term for an institute, cached briefly to avoid
     * re-querying the same value on every page render and controller call.
     *
     * Only the term id is stored in the cache (a scalar) so cached values can
     * never unserialize into stale objects.
     */
    public static function getActiveTerm(int $instituteId): ?self
    {
        if (array_key_exists($instituteId, static::$resolvedActiveTerms)) {
            return static::$resolvedActiveTerms[$instituteId];
        }

        $termId = Cache::remember("academic_active_term_id_{$instituteId}", 60, function () use ($instituteId) {
            return static::where('institute_id', $instituteId)
                ->where('is_active', true)
                ->value('id');
        });

        return static::$resolvedActiveTerms[$instituteId] = $termId ? static::find($termId) : null;
    }

    public static function forgetActiveTermCache(int $instituteId): void
    {
        Cache::forget("academic_active_term_id_{$instituteId}");
        unset(static::$resolvedActiveTerms[$instituteId]);
    }

    protected $fillable = [
        'institute_id',
        'name',
        'start_date',
        'end_date',
        'promotion_deadline',
        'is_active',
    ];

    protected $casts = [
        'start_date'         => 'date',
        'end_date'           => 'date',
        'promotion_deadline' => 'datetime',
        'is_active'          => 'boolean',
    ];

    public function isPromotionWindowOpen(): bool
    {
        if (!$this->is_active || !$this->promotion_deadline) {
            return false;
        }
        return now()->lte($this->promotion_deadline);
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    public function teacherSubjectSections(): HasMany
    {
        return $this->hasMany(TeacherSubjectSection::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForInstitute($query, int $instituteId)
    {
        return $query->where('institute_id', $instituteId);
    }

    // ── Business Logic: Single Active Term Guarantee ──────────────────────
    /**
     * Mark this academic term as active, deactivating all other terms for this institute.
     */
    public function markAsActive(): bool
    {
        return DB::transaction(function () {
            // Deactivate all terms for this institute
            static::where('institute_id', $this->institute_id)
                ->where('id', '!=', $this->id)
                ->update(['is_active' => false]);

            // Activate current term
            return $this->update(['is_active' => true]);
        });
    }
}
