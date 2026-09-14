<?php

namespace App\Services;

use App\Models\AssessmentQuestion;
use App\Models\PastPaperExemplar;
use Illuminate\Support\Facades\Log;

class MockDeduplicationValidator
{
    protected EmbeddingService $embeddingService;
    public const SIMILARITY_THRESHOLD = 0.88;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Normalize a question stem to a canonical form for exact collision hashing.
     */
    public function normalize(string $stem): string
    {
        $clean = mb_strtolower(trim($stem));
        // Remove question numbers (e.g., "1.", "Q1:", "Question 2 -")
        $clean = preg_replace('/^(?:question\s*\d+|\bq\d+|\b\d+)[\.\:\-\)]\s*/ui', '', $clean);
        // Normalize whitespace and punctuation variants
        $clean = preg_replace('/[\s\p{P}]+/u', ' ', $clean);
        return trim($clean);
    }

    /**
     * Compute SHA-256 hash of normalized stem.
     */
    public function computeStemHash(string $stem): string
    {
        return hash('sha256', $this->normalize($stem));
    }

    /**
     * Generate concept vector for semantic comparison.
     */
    public function computeConceptVector(string $stem, string $correctAnswer, ?int $subjectId = null): array
    {
        $text = trim($stem) . ' ' . trim($correctAnswer);
        return $this->embeddingService->generateEmbedding($text, $subjectId);
    }

    /**
     * Validate a question candidate against:
     * 1. In-batch seen hashes
     * 2. Database exact hash collisions in institute's assessment_questions
     * 3. Database semantic cosine similarity (> 0.88 threshold)
     *
     * @return array{is_valid: bool, reason: ?string, stem_hash: string, vector: array}
     */
    public function validateQuestion(
        string $stem,
        string $correctAnswer,
        int $instituteId,
        int $subjectId,
        array $seenHashes = [],
        array $seenVectors = []
    ): array {
        $stemHash = $this->computeStemHash($stem);

        // 1. In-batch exact hash collision
        if (isset($seenHashes[$stemHash])) {
            return [
                'is_valid' => false,
                'reason' => 'Duplicate question detected within current generation batch.',
                'stem_hash' => $stemHash,
                'vector' => [],
            ];
        }

        // 2. Database exact hash collision against institute's assessment questions
        $dbExactCollision = AssessmentQuestion::where('question_hash', $stemHash)
            ->whereHas('assessment.subject', function ($q) use ($instituteId, $subjectId) {
                $q->where('id', $subjectId)
                  ->whereHas('instituteClass', fn ($ic) => $ic->where('institute_id', $instituteId));
            })->exists();

        if ($dbExactCollision) {
            return [
                'is_valid' => false,
                'reason' => 'Exact question collision with previously created assessment in this institution.',
                'stem_hash' => $stemHash,
                'vector' => [],
            ];
        }

        // 3. Compute concept vector
        $vector = $this->computeConceptVector($stem, $correctAnswer, $subjectId);

        // 4. In-batch semantic cosine similarity check
        foreach ($seenVectors as $sVec) {
            $sim = $this->embeddingService->cosineSimilarity($vector, $sVec);
            if ($sim >= self::SIMILARITY_THRESHOLD) {
                return [
                    'is_valid' => false,
                    'reason' => "Semantic similarity collision ({$sim} >= " . self::SIMILARITY_THRESHOLD . ") within batch.",
                    'stem_hash' => $stemHash,
                    'vector' => $vector,
                ];
            }
        }

        // 5. Database semantic similarity check against stored assessment questions
        $existingQuestions = AssessmentQuestion::whereNotNull('embedding')
            ->whereHas('assessment.subject', function ($q) use ($instituteId, $subjectId) {
                $q->where('id', $subjectId)
                  ->whereHas('instituteClass', fn ($ic) => $ic->where('institute_id', $instituteId));
            })
            ->latest('id')
            ->limit(100)
            ->get(['id', 'embedding', 'statement']);

        foreach ($existingQuestions as $eq) {
            $eqVector = is_array($eq->embedding) ? $eq->embedding : json_decode($eq->embedding, true);
            if (is_array($eqVector) && !empty($eqVector)) {
                $sim = $this->embeddingService->cosineSimilarity($vector, $eqVector);
                if ($sim >= self::SIMILARITY_THRESHOLD) {
                    return [
                        'is_valid' => false,
                        'reason' => "Semantic similarity collision ({$sim} >= " . self::SIMILARITY_THRESHOLD . ") with existing question #{$eq->id}.",
                        'stem_hash' => $stemHash,
                        'vector' => $vector,
                    ];
                }
            }
        }

        return [
            'is_valid' => true,
            'reason' => null,
            'stem_hash' => $stemHash,
            'vector' => $vector,
        ];
    }
}
