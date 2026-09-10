<?php

namespace App\Services;

use App\Models\RagDocumentChunk;
use App\Models\SubjectMaterial;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RagVectorRetrievalService
{
    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Retrieve relevant chunks using hybrid vector + full-text search with multi-faceted filtering.
     *
     * @param int $subjectId
     * @param string $query
     * @param array{
     *     chapter_number?: int,
     *     chapter_title?: string,
     *     topic?: string,
     *     page_start?: int,
     *     page_end?: int,
     *     portion?: string,
     *     material_id?: int,
     *     top_k?: int
     * } $filters
     * @return Collection<RagDocumentChunk>
     */
    public function retrieve(int $subjectId, string $query, array $filters = []): Collection
    {
        $topK = (int) ($filters['top_k'] ?? config('lms.vector_search.top_k', 15));

        // 1. Generate query embedding vector
        $queryVector = $this->embeddingService->generateEmbedding($query, $subjectId);

        // 2. Perform vector search in MySQL
        $vectorResults = $this->searchByVector($subjectId, $queryVector, $filters, $topK);

        // 3. Perform FULLTEXT / Keyword search
        $fulltextResults = $this->searchByFulltext($subjectId, $query, $filters, $topK);

        // 4. Merge & re-rank using Reciprocal Rank Fusion / Weighted Scoring
        return $this->fuseAndRerankResults($vectorResults, $fulltextResults, $queryVector, $topK);
    }

    /**
     * Vector Cosine Similarity Search with Scoped Filtering.
     */
    public function searchByVector(int $subjectId, array $queryVector, array $filters = [], int $topK = 20): Collection
    {
        $baseQuery = RagDocumentChunk::query()
            ->where('subject_id', $subjectId)
            ->where('is_embedded', true);

        // Apply scoped filters
        $this->applyFiltersToQuery($baseQuery, $filters);

        try {
            $vectorJson = json_encode($queryVector);
            $threshold = config('lms.vector_search.similarity_threshold', 0.10);

            $results = (clone $baseQuery)
                ->selectRaw("*, cosine_similarity(embedding_vector, CAST(? AS JSON)) AS vector_score", [$vectorJson])
                ->having('vector_score', '>=', $threshold)
                ->orderByDesc('vector_score')
                ->limit($topK)
                ->get();

            if ($results->isNotEmpty()) {
                return $results;
            }
        } catch (\Throwable $e) {
            Log::warning("RagVectorRetrievalService: MySQL cosine_similarity execution failed: {$e->getMessage()}. Using PHP in-memory vector ranking.");
        }

        // In-memory fallback if MySQL stored function is unavailable
        $candidateChunks = $baseQuery->limit(100)->get();
        if ($candidateChunks->isEmpty()) {
            return new Collection();
        }

        $ranked = [];
        foreach ($candidateChunks as $chunk) {
            $chunkVec = $chunk->embedding_vector;
            if (is_array($chunkVec) && !empty($chunkVec)) {
                $sim = $this->embeddingService->cosineSimilarity($queryVector, $chunkVec);
                $chunk->vector_score = $sim;
                $ranked[] = $chunk;
            }
        }

        usort($ranked, fn ($a, $b) => ($b->vector_score ?? 0) <=> ($a->vector_score ?? 0));

        return new Collection(array_slice($ranked, 0, $topK));
    }

    /**
     * FULLTEXT / Keyword Search with Scoped Filtering.
     */
    public function searchByFulltext(int $subjectId, string $query, array $filters = [], int $topK = 20): Collection
    {
        $baseQuery = RagDocumentChunk::query()->where('subject_id', $subjectId);
        $this->applyFiltersToQuery($baseQuery, $filters);

        // Clean words for boolean mode
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', $query);
        $words = array_filter(preg_split('/\s+/u', (string) $cleaned), fn ($w) => mb_strlen($w) > 1);

        if (!empty($words)) {
            $booleanQuery = implode(' ', array_map(fn ($w) => "+{$w}*", $words));
            try {
                $results = (clone $baseQuery)
                    ->whereRaw("MATCH(chunk_content) AGAINST(? IN BOOLEAN MODE)", [$booleanQuery])
                    ->selectRaw("*, MATCH(chunk_content) AGAINST(? IN BOOLEAN MODE) AS ft_score", [$booleanQuery])
                    ->orderByDesc('ft_score')
                    ->limit($topK)
                    ->get();

                if ($results->isNotEmpty()) {
                    return $results;
                }
            } catch (\Throwable $e) {
                // Fallback to LIKE query
            }
        }

        // Keyword fallback
        return (clone $baseQuery)
            ->where(function ($q) use ($words) {
                foreach ($words as $w) {
                    $q->orWhere('chunk_content', 'LIKE', "%{$w}%");
                }
            })
            ->orderBy('chunk_index')
            ->limit($topK)
            ->get();
    }

    /**
     * Chapter-Wise Search specifically.
     */
    public function searchChapter(int $subjectId, int|string $chapter, string $query, int $topK = 15): Collection
    {
        $filters = is_numeric($chapter)
            ? ['chapter_number' => (int) $chapter]
            : ['chapter_title' => (string) $chapter];

        return $this->retrieve($subjectId, $query, array_merge($filters, ['top_k' => $topK]));
    }

    /**
     * Topic-Wise Search specifically.
     */
    public function searchTopic(int $subjectId, string $topic, string $query, int $topK = 15): Collection
    {
        return $this->retrieve($subjectId, $query, [
            'topic' => $topic,
            'top_k' => $topK,
        ]);
    }

    /**
     * Page-Wise Search specifically.
     */
    public function searchPageRange(int $subjectId, int $startPage, int $endPage, string $query = '', int $topK = 15): Collection
    {
        return $this->retrieve($subjectId, $query, [
            'page_start' => $startPage,
            'page_end'   => $endPage,
            'top_k'      => $topK,
        ]);
    }

    /**
     * Format retrieved chunks into an enriched prompt context block with citations.
     */
    public function formatContextWithCitations(Collection $chunks): string
    {
        if ($chunks->isEmpty()) {
            return '';
        }

        $materialIds = $chunks->pluck('subject_material_id')->unique();
        $materials = SubjectMaterial::whereIn('id', $materialIds)->pluck('title', 'id');

        $context = "";
        $currentMaterialId = null;

        foreach ($chunks as $chunk) {
            $materialTitle = $materials[$chunk->subject_material_id] ?? 'Subject Material';
            
            $headerParts = [];
            $headerParts[] = "Book: \"{$materialTitle}\"";

            if ($chunk->chapter_number || $chunk->chapter_title) {
                $chap = $chunk->chapter_title ?: "Chapter {$chunk->chapter_number}";
                $headerParts[] = "Chapter: {$chap}";
            }

            if ($chunk->topic_title) {
                $headerParts[] = "Topic: {$chunk->topic_title}";
            }

            if ($chunk->page_number) {
                $headerParts[] = "Page: {$chunk->page_number}";
            }

            $citation = "[Source: " . implode(' | ', $headerParts) . "]";
            $context .= "\n{$citation}\n" . trim($chunk->chunk_content) . "\n";
        }

        return trim($context);
    }

    /**
     * Apply filter options (chapter, topic, page range, portion) to Eloquent query.
     */
    protected function applyFiltersToQuery($query, array $filters): void
    {
        if (!empty($filters['material_id'])) {
            $query->where('subject_material_id', (int) $filters['material_id']);
        }

        if (!empty($filters['page_start'])) {
            $query->where('page_number', '>=', (int) $filters['page_start']);
        }

        if (!empty($filters['page_end'])) {
            $query->where('page_number', '<=', (int) $filters['page_end']);
        }

        if (!empty($filters['chapter_number'])) {
            $query->where('chapter_number', (int) $filters['chapter_number']);
        }

        if (!empty($filters['chapter_title'])) {
            $title = $filters['chapter_title'];
            $query->where(function ($q) use ($title) {
                $q->where('chapter_title', 'LIKE', "%{$title}%")
                  ->orWhere('chunk_content', 'LIKE', "%{$title}%");
            });
        }

        if (!empty($filters['topic']) && $filters['topic'] !== 'All Topics') {
            $topic = $filters['topic'];
            $query->where(function ($q) use ($topic) {
                $q->where('topic_title', 'LIKE', "%{$topic}%")
                  ->orWhere('chapter_title', 'LIKE', "%{$topic}%")
                  ->orWhere('chunk_content', 'LIKE', "%{$topic}%");
            });
        }

        if (!empty($filters['portion']) && $filters['portion'] !== 'complete') {
            $portion = $filters['portion'];
            $query->where(function ($q) use ($portion) {
                $q->where('book_portion', $portion)
                  ->orWhere('book_portion', 'complete');
            });
        }
    }

    /**
     * Fuse Vector & Fulltext results, deduplicate by ID, and re-rank with weighted score.
     */
    protected function fuseAndRerankResults(
        Collection $vectorResults,
        Collection $fulltextResults,
        array $queryVector,
        int $topK
    ): Collection {
        $merged = [];
        $weightVector = (float) config('lms.vector_search.hybrid_vector_weight', 0.6);
        $weightFt = (float) config('lms.vector_search.hybrid_fulltext_weight', 0.4);

        // Process vector items
        foreach ($vectorResults as $chunk) {
            $id = $chunk->id;
            $vScore = (float) ($chunk->vector_score ?? 0.5);
            $merged[$id] = [
                'chunk' => $chunk,
                'score' => $vScore * $weightVector,
            ];
        }

        // Process fulltext items
        foreach ($fulltextResults as $chunk) {
            $id = $chunk->id;
            $ftScore = (float) ($chunk->ft_score ?? 1.0);
            $normalizedFt = min(1.0, $ftScore / 10.0);

            if (isset($merged[$id])) {
                $merged[$id]['score'] += ($normalizedFt * $weightFt);
            } else {
                // If not in vector results, compute cosine similarity on demand if vector exists
                $vScore = 0.3;
                if (!empty($chunk->embedding_vector) && is_array($chunk->embedding_vector)) {
                    $vScore = $this->embeddingService->cosineSimilarity($queryVector, $chunk->embedding_vector);
                }
                $merged[$id] = [
                    'chunk' => $chunk,
                    'score' => ($vScore * $weightVector) + ($normalizedFt * $weightFt),
                ];
            }
        }

        // Sort descending by merged score
        uasort($merged, fn ($a, $b) => $b['score'] <=> $a['score']);

        $finalChunks = [];
        foreach (array_slice($merged, 0, $topK) as $item) {
            $chunk = $item['chunk'];
            $chunk->relevance_score = round($item['score'], 4);
            $finalChunks[] = $chunk;
        }

        return new Collection($finalChunks);
    }
}
