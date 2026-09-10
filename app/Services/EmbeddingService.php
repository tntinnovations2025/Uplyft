<?php

namespace App\Services;

use App\Models\RagDocumentChunk;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    protected string $provider;
    protected string $apiKey;
    protected string $model;
    protected int $dimension;
    protected string $endpoint;

    public function __construct()
    {
        $this->provider  = (string) config('lms.embedding.provider', 'tfidf');
        $this->apiKey    = (string) config('lms.embedding.api_key', '');
        $this->model     = (string) config('lms.embedding.model', 'text-embedding-3-small');
        $this->dimension = (int) config('lms.embedding.dimension', 256);
        $this->endpoint  = (string) config('lms.embedding.endpoint', 'https://api.openai.com/v1/embeddings');
    }

    /**
     * Generate dense vector embedding for a single text string.
     *
     * @param string $text
     * @param int|null $subjectId Optional context for TF-IDF corpus tuning
     * @return array<float> Normalized float array
     */
    public function generateEmbedding(string $text, ?int $subjectId = null): array
    {
        $cleaned = trim($text);
        if (empty($cleaned)) {
            return array_fill(0, $this->dimension, 0.0);
        }

        return match ($this->provider) {
            'openai'      => $this->embedViaOpenAi($cleaned),
            'huggingface' => $this->embedViaHuggingFace($cleaned),
            'jina'        => $this->embedViaJina($cleaned),
            'ollama'      => $this->embedViaOllama($cleaned),
            default       => $this->embedViaTfIdf($cleaned, $subjectId),
        };
    }

    /**
     * Generate embeddings for a batch of strings.
     *
     * @param array<string> $texts
     * @param int|null $subjectId
     * @return array<array<float>>
     */
    public function generateBatchEmbeddings(array $texts, ?int $subjectId = null): array
    {
        if (empty($texts)) {
            return [];
        }

        if ($this->provider === 'openai' && !empty($this->apiKey)) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type'  => 'application/json',
                ])->timeout(60)->post($this->endpoint, [
                    'model'      => $this->model,
                    'input'      => array_values($texts),
                    'dimensions' => $this->dimension,
                ]);

                if ($response->successful()) {
                    $data = $response->json('data', []);
                    $vectors = [];
                    foreach ($data as $item) {
                        $vectors[] = $this->normalizeVector($item['embedding'] ?? []);
                    }
                    return $vectors;
                }
            } catch (\Throwable $e) {
                Log::warning("EmbeddingService: Batch OpenAI embedding failed ({$e->getMessage()}). Falling back to TF-IDF.");
            }
        }

        // Fallback or default to local TF-IDF
        $results = [];
        foreach ($texts as $text) {
            $results[] = $this->embedViaTfIdf($text, $subjectId);
        }

        return $results;
    }

    /**
     * High-speed, local TF-IDF semantic hash embedding with sub-word n-grams (zero API dependency, 256-dim).
     */
    protected function embedViaTfIdf(string $text, ?int $subjectId = null): array
    {
        $dim = $this->dimension;
        $vector = array_fill(0, $dim, 0.0);

        // Normalize text
        $cleaned = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text));
        $tokens = array_filter(preg_split('/\s+/u', $cleaned), fn ($t) => mb_strlen($t) > 1);

        if (empty($tokens)) {
            return $vector;
        }

        // Calculate term frequencies
        $termCounts = array_count_values($tokens);
        $totalTokens = count($tokens);

        // Stopwords to downweight
        $stopWords = [
            'the','and','is','in','at','of','on','for','to','a','an','this','that','with','by',
            'from','as','be','are','was','were','it','its','or','which','what','when','where',
            'who','how','all','any','both','each','few','more','most','other','some','such','than'
        ];

        foreach ($termCounts as $term => $count) {
            $tf = $count / $totalTokens;
            $idfWeight = in_array($term, $stopWords) ? 0.2 : (1.0 + log(1 + mb_strlen($term)));
            $weight = $tf * $idfWeight;

            // 1. Full word hashing
            $hash1 = crc32($term);
            $hash2 = crc32($term . '_salt2');
            $idx1 = abs($hash1) % $dim;
            $idx2 = abs($hash2) % $dim;

            $vector[$idx1] += $weight;
            $vector[$idx2] += ($weight * 0.5);

            // 2. Character n-gram hashing (3-5 grams) for stem/morphology matching
            $len = mb_strlen($term);
            if ($len >= 4) {
                for ($n = 3; $n <= min(5, $len); $n++) {
                    for ($start = 0; $start <= $len - $n; $start++) {
                        $ngram = mb_substr($term, $start, $n);
                        $nHash = abs(crc32('ng_' . $ngram)) % $dim;
                        $vector[$nHash] += ($weight * 0.35);
                    }
                }
            }
        }

        return $this->normalizeVector($vector);
    }

    /**
     * OpenAI Embeddings API call.
     */
    protected function embedViaOpenAi(string $text): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post($this->endpoint, [
                'model'      => $this->model,
                'input'      => $text,
                'dimensions' => $this->dimension,
            ]);

            if ($response->successful()) {
                $raw = $response->json('data.0.embedding', []);
                return $this->normalizeVector($raw);
            }
        } catch (\Throwable $e) {
            Log::warning("EmbeddingService: OpenAI embedding error ({$e->getMessage()}). Fallback to TF-IDF.");
        }

        return $this->embedViaTfIdf($text);
    }

    /**
     * HuggingFace Inference API call.
     */
    protected function embedViaHuggingFace(string $text): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->timeout(30)->post($this->endpoint, [
                'inputs' => $text,
            ]);

            if ($response->successful()) {
                $raw = $response->json();
                if (is_array($raw) && isset($raw[0]) && is_numeric($raw[0])) {
                    return $this->normalizeVector($raw);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("EmbeddingService: HuggingFace embedding error ({$e->getMessage()}). Fallback to TF-IDF.");
        }

        return $this->embedViaTfIdf($text);
    }

    /**
     * Jina Embeddings API call.
     */
    protected function embedViaJina(string $text): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post('https://api.jina.ai/v1/embeddings', [
                'model' => 'jina-embeddings-v2-base-en',
                'input' => [$text],
            ]);

            if ($response->successful()) {
                $raw = $response->json('data.0.embedding', []);
                return $this->normalizeVector($raw);
            }
        } catch (\Throwable $e) {
            Log::warning("EmbeddingService: Jina embedding error ({$e->getMessage()}). Fallback to TF-IDF.");
        }

        return $this->embedViaTfIdf($text);
    }

    /**
     * Ollama local embedding call.
     */
    protected function embedViaOllama(string $text): array
    {
        try {
            $endpoint = $this->endpoint ?: 'http://127.0.0.1:11434/api/embeddings';
            $response = Http::timeout(30)->post($endpoint, [
                'model'  => $this->model ?: 'nomic-embed-text',
                'prompt' => $text,
            ]);

            if ($response->successful()) {
                $raw = $response->json('embedding', []);
                return $this->normalizeVector($raw);
            }
        } catch (\Throwable $e) {
            Log::warning("EmbeddingService: Ollama embedding error ({$e->getMessage()}). Fallback to TF-IDF.");
        }

        return $this->embedViaTfIdf($text);
    }

    /**
     * Normalize vector to unit length (L2 norm) for cosine similarity.
     */
    public function normalizeVector(array $vector): array
    {
        $sumSquares = 0.0;
        foreach ($vector as $val) {
            $floatVal = (float) $val;
            $sumSquares += ($floatVal * $floatVal);
        }

        $magnitude = sqrt($sumSquares);
        if ($magnitude <= 0.00000001) {
            return $vector;
        }

        return array_map(fn ($v) => round(((float) $v) / $magnitude, 6), $vector);
    }

    /**
     * Calculate cosine similarity between two float vectors in PHP.
     */
    public function cosineSimilarity(array $vecA, array $vecB): float
    {
        $countA = count($vecA);
        $countB = count($vecB);

        if ($countA === 0 || $countA !== $countB) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $countA; $i++) {
            $a = (float) $vecA[$i];
            $b = (float) $vecB[$i];
            $dotProduct += ($a * $b);
            $normA += ($a * $a);
            $normB += ($b * $b);
        }

        $magnitude = sqrt($normA) * sqrt($normB);

        return $magnitude > 0.0000001 ? ($dotProduct / $magnitude) : 0.0;
    }
}
