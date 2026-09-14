<?php

namespace App\Services;

use App\Models\PastPaperExemplar;
use App\Models\PastPaperUpload;
use App\Models\Subject;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as SmalotParser;

class PastPaperParserService
{
    protected DocumentParserService $documentParser;
    protected EmbeddingService $embeddingService;
    protected string $groqApiKey;
    protected string $groqModel;

    public function __construct(
        DocumentParserService $documentParser,
        EmbeddingService $embeddingService
    ) {
        $this->documentParser = $documentParser;
        $this->embeddingService = $embeddingService;
        $this->groqApiKey = (string) config('services.groq.api_key', '');
        $this->groqModel = (string) config('services.groq.model', 'llama-3.3-70b-versatile');
    }

    /**
     * Store and ingest an uploaded Past Paper PDF.
     */
    public function ingestUploadedPdf(
        UploadedFile $file,
        int $instituteId,
        int $subjectId,
        string $examSeries
    ): PastPaperUpload {
        $storedPath = $file->store("past-papers/{$instituteId}/{$subjectId}", 'local');

        $upload = PastPaperUpload::create([
            'institute_id' => $instituteId,
            'subject_id' => $subjectId,
            'exam_series' => $examSeries,
            'file_path' => $storedPath,
            'parsed_status' => 'processing',
            'total_questions_extracted' => 0,
        ]);

        try {
            $extractedCount = $this->parseAndIndexUpload($upload);
            $upload->update([
                'parsed_status' => 'indexed',
                'total_questions_extracted' => $extractedCount,
            ]);
        } catch (\Throwable $e) {
            Log::error("PastPaperParserService error for Upload ID {$upload->id}: " . $e->getMessage());
            $upload->update(['parsed_status' => 'failed']);
        }

        return $upload;
    }

    /**
     * Parse text from past paper upload and extract MCQ exemplars.
     */
    public function parseAndIndexUpload(PastPaperUpload $upload): int
    {
        $fullPath = Storage::disk('local')->path($upload->file_path);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/' . $upload->file_path);
        }

        $rawText = '';
        if (file_exists($fullPath)) {
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            if ($extension === 'pdf' && class_exists(SmalotParser::class)) {
                try {
                    $parser = new SmalotParser();
                    $pdf = $parser->parseFile($fullPath);
                    $rawText = $pdf->getText();
                } catch (\Throwable $e) {
                    Log::warning("Smalot PDF parse failed: {$e->getMessage()}. Using basic stream extraction.");
                }
            }

            if (empty(trim($rawText))) {
                $rawText = @file_get_contents($fullPath) ?: '';
            }
        }

        if (empty(trim($rawText))) {
            Log::warning("PastPaperParserService: No text could be extracted from {$upload->file_path}");
            return 0;
        }

        // Extract MCQs from raw text
        $questions = $this->extractMcqsFromText($rawText, $upload->subject);
        $savedCount = 0;

        foreach ($questions as $q) {
            $stem = trim($q['question_stem'] ?? '');
            if (empty($stem)) {
                continue;
            }

            $options = $q['options'] ?? [];
            if (!is_array($options) || count($options) < 2) {
                continue;
            }

            // Standardize options into A, B, C, D
            $stdOptions = [];
            $labels = ['A', 'B', 'C', 'D'];
            $optIdx = 0;
            foreach ($options as $k => $val) {
                if ($optIdx >= 4) break;
                $key = is_string($k) && in_array(strtoupper($k), $labels) ? strtoupper($k) : $labels[$optIdx];
                $stdOptions[$key] = is_string($val) ? trim($val) : (string)$val;
                $optIdx++;
            }

            $correctAnswer = strtoupper(trim((string)($q['correct_answer'] ?? 'A')));
            if (!in_array($correctAnswer, ['A', 'B', 'C', 'D'])) {
                $correctAnswer = 'A';
            }

            $normalized = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $stem)));
            $stemHash = hash('sha256', $normalized);

            // Compute embedding vector
            $conceptText = $stem . ' ' . ($stdOptions[$correctAnswer] ?? '');
            $embedding = $this->embeddingService->generateEmbedding($conceptText, $upload->subject_id);

            PastPaperExemplar::updateOrCreate(
                [
                    'past_paper_upload_id' => $upload->id,
                    'stem_hash' => $stemHash,
                ],
                [
                    'subject_id' => $upload->subject_id,
                    'topic_tag' => $q['topic_tag'] ?? null,
                    'question_stem' => $stem,
                    'options' => $stdOptions,
                    'correct_answer' => $correctAnswer,
                    'explanation' => $q['explanation'] ?? null,
                    'embedding' => $embedding,
                ]
            );

            $savedCount++;
        }

        return $savedCount;
    }

    /**
     * Extract structured MCQs from raw text using AI/regex parsing.
     */
    protected function extractMcqsFromText(string $rawText, ?Subject $subject = null): array
    {
        $subjectName = $subject?->subject_name ?? 'O/A Level Subject';

        // Split text into manageable chunks if very large
        $textExcerpt = mb_substr($rawText, 0, 14000);

        if (!empty($this->groqApiKey)) {
            try {
                $systemPrompt = <<<PROMPT
You are a Cambridge CAIE / Edexcel Past Paper Extraction Engine.
Extract multiple-choice questions (MCQs) from the provided past examination paper text.
Output ONLY raw valid JSON array of objects.

JSON Format:
[
  {
    "question_stem": "Which statement describes...",
    "options": {
      "A": "Option text 1",
      "B": "Option text 2",
      "C": "Option text 3",
      "D": "Option text 4"
    },
    "correct_answer": "A",
    "topic_tag": "Syllabus topic name or concept",
    "explanation": "Brief rationale for correct option"
  }
]
PROMPT;

                $userPrompt = "Extract all MCQs from this {$subjectName} past paper:\n\n" . $textExcerpt;

                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->groqApiKey}",
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $this->groqModel,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 4096,
                ]);

                if ($response->successful()) {
                    $content = (string) $response->json('choices.0.message.content', '');
                    $cleanJson = preg_replace('/<think>[\s\S]*?<\/think>/i', '', $content);
                    $cleanJson = preg_replace('/^```json\s*/i', '', trim($cleanJson));
                    $cleanJson = preg_replace('/^```\s*/', '', $cleanJson);
                    $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);

                    if (preg_match('/\[[\s\S]*\]/', $cleanJson, $match)) {
                        $parsed = json_decode($match[0], true);
                        if (is_array($parsed) && !empty($parsed)) {
                            return $parsed;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Groq past paper parser extraction failed: " . $e->getMessage());
            }
        }

        // Fallback regex parser for standard CAIE format (1. Question stem... A... B... C... D...)
        return $this->heuristicRegexParser($rawText);
    }

    /**
     * Heuristic regex parser fallback for standard past paper formats.
     */
    protected function heuristicRegexParser(string $text): array
    {
        $questions = [];
        $pattern = '/(?:^|\n)(?:Q(?:uestion)?\s*)?(\d{1,2})[\.\:\)]\s*(.+?)(?=(?:\n(?:Q(?:uestion)?\s*)?\d{1,2}[\.\:\)]|\Z))/s';

        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $block = trim($m[2]);

                // Look for options A, B, C, D
                if (preg_match('/A[\.\:\)]\s*(.+?)\s*B[\.\:\)]\s*(.+?)\s*C[\.\:\)]\s*(.+?)\s*D[\.\:\)]\s*(.+?)$/s', $block, $optMatch)) {
                    $stem = trim(preg_replace('/A[\.\:\)]\s*.+$/s', '', $block));
                    if (!empty($stem)) {
                        $questions[] = [
                            'question_stem' => $stem,
                            'options' => [
                                'A' => trim($optMatch[1]),
                                'B' => trim($optMatch[2]),
                                'C' => trim($optMatch[3]),
                                'D' => trim($optMatch[4]),
                            ],
                            'correct_answer' => 'A',
                            'topic_tag' => 'General',
                            'explanation' => null,
                        ];
                    }
                }
            }
        }

        return $questions;
    }
}
