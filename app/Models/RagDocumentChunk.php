<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RAG Document Chunk
 *
 * Represents a single text chunk extracted from a PDF/Word document.
 * These chunks are stored in MySQL and used for full-text retrieval
 * when answering student queries via the RAG chatbot.
 */
class RagDocumentChunk extends Model
{
    use HasFactory, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereHas('subject.instituteClass', fn ($q) => $q->whereIn('institute_id', $campusIds));
    }

    protected $fillable = [
        'subject_material_id',
        'subject_id',
        'chunk_index',
        'chunk_content',
        'token_count',
        'content_hash',
        'page_number',
        'chapter_number',
        'chapter_title',
        'topic_title',
        'book_portion',
        'embedding_vector',
        'vector_dimension',
        'is_embedded',
    ];

    protected $casts = [
        'chunk_index'      => 'integer',
        'token_count'      => 'integer',
        'page_number'      => 'integer',
        'chapter_number'   => 'integer',
        'embedding_vector' => 'array',
        'vector_dimension' => 'integer',
        'is_embedded'      => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function subjectMaterial(): BelongsTo
    {
        return $this->belongsTo(SubjectMaterial::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Full-text search scope for RAG retrieval.
     */
    public function scopeSearchRelevant($query, string $searchQuery, int $subjectId)
    {
        // Clean the query for MySQL full-text search
        $cleaned = preg_replace('/[^\w\s]/', '', $searchQuery);
        $words = array_filter(preg_split('/\s+/', $cleaned));

        if (empty($words)) {
            return $query->where('subject_id', $subjectId)->limit(10);
        }

        // Build boolean mode search with partial matching
        $booleanQuery = implode(' ', array_map(fn ($w) => "+{$w}*", $words));

        return $query->where('subject_id', $subjectId)
            ->whereRaw(
                "MATCH(chunk_content) AGAINST(? IN BOOLEAN MODE)",
                [$booleanQuery]
            )
            ->selectRaw(
                "*, MATCH(chunk_content) AGAINST(? IN BOOLEAN MODE) AS relevance_score",
                [$booleanQuery]
            )
            ->orderByDesc('relevance_score')
            ->limit(35);
    }

    /**
     * Keyword fallback search when full-text returns nothing.
     */
    public function scopeKeywordFallback($query, string $searchQuery, int $subjectId)
    {
        $words = array_filter(preg_split('/\s+/', $searchQuery));

        return $query->where('subject_id', $subjectId)
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->orWhere('chunk_content', 'LIKE', "%{$word}%");
                }
            })
            ->orderBy('chunk_index')
            ->limit(35);
    }

    /**
     * Granular metadata scope filters (page range, chapter range, topic, book portion).
     */
    public function scopeApplyScopeFilters($query, array $filters)
    {
        // Page Range Filter (e.g. Page 1 to 30)
        if (!empty($filters['page_start'])) {
            $query->where('page_number', '>=', (int) $filters['page_start']);
        }
        if (!empty($filters['page_end'])) {
            $query->where('page_number', '<=', (int) $filters['page_end']);
        }

        // Chapter Range / Specific Chapter Filter (e.g. Chapter 1 to 2)
        if (!empty($filters['chapter_start'])) {
            $query->where('chapter_number', '>=', (int) $filters['chapter_start']);
        }
        if (!empty($filters['chapter_end'])) {
            $query->where('chapter_number', '<=', (int) $filters['chapter_end']);
        }
        if (!empty($filters['chapter_number'])) {
            $query->where('chapter_number', (int) $filters['chapter_number']);
        }

        // Topic Filter (matches topic_title, chapter_title, or content)
        if (!empty($filters['topic']) && $filters['topic'] !== 'All Topics') {
            $topic = $filters['topic'];
            $query->where(function ($q) use ($topic) {
                $q->where('topic_title', 'LIKE', "%{$topic}%")
                  ->orWhere('chapter_title', 'LIKE', "%{$topic}%")
                  ->orWhere('chunk_content', 'LIKE', "%{$topic}%");
            });
        }

        // Book Portion Filter (first_half, second_half, complete)
        if (!empty($filters['portion']) && $filters['portion'] !== 'complete') {
            $query->where(function ($q) use ($filters) {
                $q->where('book_portion', $filters['portion'])
                  ->orWhere('book_portion', 'complete');
            });
        }

        return $query;
    }

    // ── Vector Search Scopes ─────────────────────────────────────────────────

    /**
     * Vector similarity search scope using MySQL cosine_similarity() function.
     * Retrieves top-K chunks sorted by cosine similarity to the query vector.
     */
    public function scopeVectorSearch($query, array $queryVector, int $subjectId, int $topK = 20)
    {
        $vectorJson = json_encode($queryVector);

        return $query->where('subject_id', $subjectId)
            ->where('is_embedded', true)
            ->selectRaw(
                "*, cosine_similarity(embedding_vector, CAST(? AS JSON)) AS vector_score",
                [$vectorJson]
            )
            ->having('vector_score', '>', config('lms.vector_search.similarity_threshold', 0.15))
            ->orderByDesc('vector_score')
            ->limit($topK);
    }

    /**
     * Scope to get chunks that haven't been embedded yet.
     */
    public function scopeNotEmbedded($query)
    {
        return $query->where('is_embedded', false);
    }
}
