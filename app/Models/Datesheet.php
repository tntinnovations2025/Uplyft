<?php

namespace App\Models;

/**
 * Datesheet Model
 *
 * Proxy model extending Assessment to represent scheduled examinations,
 * midterms, and finals on the official institutional datesheet.
 */
class Datesheet extends Assessment
{
    protected $table = 'assessments';

    /**
     * Get the institute ID that owns this datesheet entry.
     */
    public function getInstituteIdAttribute(): ?int
    {
        return $this->subject?->instituteClass?->institute_id
            ?? $this->classSection?->instituteClass?->institute_id
            ?? $this->academicTerm?->institute_id
            ?? (int) ($this->attributes['institute_id'] ?? 0) ?: null;
    }
}
