<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'owner_user_id',
        'max_campuses',
        'is_active',
    ];

    protected $casts = [
        'max_campuses' => 'integer',
        'is_active'    => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Organization $org) {
            if (empty($org->slug)) {
                $org->slug = Str::slug($org->name);
            }
            if (empty($org->code)) {
                $org->code = 'ORG-' . date('Y') . '-' . Str::upper(Str::random(4));
            }
        });
    }

    /**
     * Owner (Primary Principal) of the organization.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * All institutes/campuses registered under this organization.
     */
    public function institutes(): HasMany
    {
        return $this->hasMany(Institute::class);
    }

    /**
     * Alias for institutes() — campuses of the organization network.
     */
    public function campuses(): HasMany
    {
        return $this->institutes();
    }

    /**
     * All users linked to this organization.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if the organization has remaining quota to add more campuses.
     */
    public function canAddMoreCampuses(): bool
    {
        return $this->campus_count < ($this->max_campuses ?? 3);
    }

    /**
     * Get actual count of registered campuses without global tenant privacy scope.
     */
    public function getCampusCountAttribute(): int
    {
        return $this->institutes()->withoutGlobalScopes()->count();
    }

    /**
     * Format campus quota string (e.g. "2 / 3 Campuses").
     */
    public function getCampusUsageTextAttribute(): string
    {
        $limit = $this->max_campuses ?? 3;
        return "{$this->campus_count} / {$limit} Campuses";
    }
}
