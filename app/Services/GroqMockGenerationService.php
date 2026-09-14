<?php

namespace App\Services;

use App\Models\PastPaperExemplar;
use App\Models\Subject;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GroqMockGenerationService
{
    protected string $groqApiKey;
    protected string $groqModel;
    protected RagVectorRetrievalService $ragRetrievalService;
    protected PastPaperStyleRetrievalService $styleRetrievalService;
    protected MockDeduplicationValidator $dedupValidator;

    public function __construct(
        RagVectorRetrievalService $ragRetrievalService,
        PastPaperStyleRetrievalService $styleRetrievalService,
        MockDeduplicationValidator $dedupValidator
    ) {
        $this->groqApiKey = (string) config('services.groq.api_key', '');
        $this->groqModel = (string) config('services.groq.model', 'llama-3.3-70b-versatile');
        $this->ragRetrievalService = $ragRetrievalService;
        $this->styleRetrievalService = $styleRetrievalService;
        $this->dedupValidator = $dedupValidator;
    }

    /**
     * Generate a complete, Cambridge CAIE / Edexcel standard Mock Examination.
     *
     * @param int $subjectId
     * @param int $instituteId
     * @param array $topics
     * @param int $totalMcqs 30 or 40
     * @param string $examStandard 'caie_o_level'|'caie_a_level'|'edexcel_igcse'
     * @param int $optionsCount 3, 4 (default), or 5
     * @return array
     */
    public function generateMockExam(
        int $subjectId,
        int $instituteId,
        array $topics = [],
        int $totalMcqs = 30,
        string $examStandard = 'caie_o_level',
        int $optionsCount = 4
    ): array {
        $optionsCount = in_array($optionsCount, [3, 4, 5], true) ? $optionsCount : 4;
        $subject = Subject::find($subjectId);
        $subjectName = $subject ? $subject->name : 'Subject';
        $syllabusCode = $subject ? ($subject->code ?? 'O/A Level') : 'O/A Level';

        // 1. Retrieve Past Paper Style Exemplars (Few-Shot conditioning)
        $exemplars = $this->styleRetrievalService->getStyleExemplars($subjectId, $topics, 5);
        $fewShotContext = $this->styleRetrievalService->formatFewShotContext($exemplars);

        // 2. Retrieve Syllabus Textbook / RAG Context Chunks
        $topicQuery = !empty($topics) ? implode(', ', $topics) : $subjectName;
        $ragChunks = $this->ragRetrievalService->retrieve($subjectId, $topicQuery, ['top_k' => 20]);
        $ragContext = $this->ragRetrievalService->formatContextWithCitations($ragChunks);

        // Standard exam timing
        $timeLimitMinutes = ($totalMcqs >= 40) ? 60 : 45;

        // Batch generation settings (generate in chunks of 10 for maximum accuracy and JSON integrity)
        $batchSize = 10;
        $numBatches = (int) ceil($totalMcqs / $batchSize);

        $validatedQuestions = [];
        $seenHashes = [];
        $seenVectors = [];
        $dedupStats = [
            'total_candidates_evaluated' => 0,
            'exact_duplicates_rejected' => 0,
            'semantic_duplicates_rejected' => 0,
        ];

        $systemPrompt = $this->buildChiefExaminerSystemPrompt($examStandard, $subjectName, $syllabusCode, $optionsCount);

        for ($batch = 1; $batch <= $numBatches; $batch++) {
            $neededInBatch = min($batchSize, $totalMcqs - count($validatedQuestions));
            if ($neededInBatch <= 0) {
                break;
            }

            $currentBatchTopics = !empty($topics) ? $topics : [$subjectName];
            $batchTopicStr = implode(', ', array_slice($currentBatchTopics, ($batch - 1) % count($currentBatchTopics), 3));

            $candidates = $this->fetchCandidateBatch(
                $systemPrompt,
                $fewShotContext,
                $ragContext,
                $batchTopicStr,
                $neededInBatch,
                $batch,
                $subjectName,
                $optionsCount
            );

            foreach ($candidates as $cand) {
                if (count($validatedQuestions) >= $totalMcqs) {
                    break;
                }

                $dedupStats['total_candidates_evaluated']++;

                $stem = (string) ($cand['question_stem'] ?? $cand['question_text'] ?? '');
                $options = $cand['options'] ?? [];
                $correctAnswer = (string) ($cand['correct_answer'] ?? 'A');
                $explanation = (string) ($cand['explanation'] ?? $cand['evaluation_rubric'] ?? '');
                $topicTag = (string) ($cand['topic'] ?? $batchTopicStr);
                $cogLevel = (string) ($cand['cognitive_level'] ?? 'Application');

                if (empty($stem) || !is_array($options) || count($options) < 3) {
                    continue;
                }

                // Format options to key-value [A => '...', B => '...', C => '...', D => '...']
                $formattedOptions = $this->normalizeOptions($options, $optionsCount);

                // Run Deduplication Matrix Validation
                $valResult = $this->dedupValidator->validateQuestion(
                    $stem,
                    $correctAnswer,
                    $instituteId,
                    $subjectId,
                    $seenHashes,
                    $seenVectors
                );

                if (!$valResult['is_valid']) {
                    if (str_contains($valResult['reason'], 'cosine similarity')) {
                        $dedupStats['semantic_duplicates_rejected']++;
                    } else {
                        $dedupStats['exact_duplicates_rejected']++;
                    }
                    Log::info("Mock generation deduplication: candidate rejected ({$valResult['reason']})");
                    continue;
                }

                // Record hash and vector
                $seenHashes[$valResult['stem_hash']] = true;
                if (!empty($valResult['vector'])) {
                    $seenVectors[] = $valResult['vector'];
                }

                $validatedQuestions[] = [
                    'question_number' => count($validatedQuestions) + 1,
                    'topic' => $topicTag,
                    'question_stem' => $stem,
                    'options' => $formattedOptions,
                    'correct_answer' => $this->normalizeCorrectAnswerKey($correctAnswer, $formattedOptions, $optionsCount),
                    'explanation' => $explanation,
                    'cognitive_level' => $cogLevel,
                    'stem_hash' => $valResult['stem_hash'],
                    'similarity_score' => $valResult['max_similarity'] ?? 0.0,
                    'vector' => $valResult['vector'],
                ];
            }
        }

        // If after normal batches we are slightly below target due to deduplication, run a targeted top-up loop
        $retryAttempts = 0;
        while (count($validatedQuestions) < $totalMcqs && $retryAttempts < 3) {
            $retryAttempts++;
            $remainingCount = $totalMcqs - count($validatedQuestions);
            $randomTopic = !empty($topics) ? $topics[array_rand($topics)] : $subjectName;

            $topUpCandidates = $this->fetchCandidateBatch(
                $systemPrompt,
                $fewShotContext,
                $ragContext,
                $randomTopic . " (Advanced / Novel angle)",
                $remainingCount + 2,
                99 + $retryAttempts,
                $subjectName,
                $optionsCount
            );

            foreach ($topUpCandidates as $cand) {
                if (count($validatedQuestions) >= $totalMcqs) {
                    break;
                }

                $dedupStats['total_candidates_evaluated']++;
                $stem = (string) ($cand['question_stem'] ?? $cand['question_text'] ?? '');
                $options = $cand['options'] ?? [];
                $correctAnswer = (string) ($cand['correct_answer'] ?? 'A');
                $explanation = (string) ($cand['explanation'] ?? '');
                $topicTag = (string) ($cand['topic'] ?? $randomTopic);
                $cogLevel = (string) ($cand['cognitive_level'] ?? 'Application');

                if (empty($stem) || !is_array($options) || count($options) < 3) {
                    continue;
                }

                $formattedOptions = $this->normalizeOptions($options, $optionsCount);
                $valResult = $this->dedupValidator->validateQuestion(
                    $stem,
                    $correctAnswer,
                    $instituteId,
                    $subjectId,
                    $seenHashes,
                    $seenVectors
                );

                if ($valResult['is_valid']) {
                    $seenHashes[$valResult['stem_hash']] = true;
                    if (!empty($valResult['vector'])) {
                        $seenVectors[] = $valResult['vector'];
                    }

                    $validatedQuestions[] = [
                        'question_number' => count($validatedQuestions) + 1,
                        'topic' => $topicTag,
                        'question_stem' => $stem,
                        'options' => $formattedOptions,
                        'correct_answer' => $this->normalizeCorrectAnswerKey($correctAnswer, $formattedOptions, $optionsCount),
                        'explanation' => $explanation,
                        'cognitive_level' => $cogLevel,
                        'stem_hash' => $valResult['stem_hash'],
                        'similarity_score' => $valResult['max_similarity'] ?? 0.0,
                        'vector' => $valResult['vector'],
                    ];
                }
            }
        }

        // Re-number final questions
        foreach ($validatedQuestions as $idx => &$q) {
            $q['question_number'] = $idx + 1;
        }
        unset($q);

        $standardLabels = [
            'caie_o_level' => 'Cambridge CAIE O Level (Paper 1 MCQ)',
            'caie_a_level' => 'Cambridge CAIE A Level (Paper 1 MCQ)',
            'edexcel_igcse' => 'Pearson Edexcel IGCSE Standard MCQ',
        ];

        return [
            'success' => true,
            'title' => "{$subjectName} Mock Examination - " . ($standardLabels[$examStandard] ?? 'CAIE Standard'),
            'subject_name' => $subjectName,
            'exam_standard' => $examStandard,
            'exam_standard_label' => $standardLabels[$examStandard] ?? 'CAIE Standard',
            'total_mcqs' => count($validatedQuestions),
            'target_mcqs' => $totalMcqs,
            'options_per_mcq' => $optionsCount,
            'time_limit_minutes' => $timeLimitMinutes,
            'topics_covered' => $topics,
            'exemplars_used' => $exemplars->count(),
            'deduplication_stats' => $dedupStats,
            'questions' => $validatedQuestions,
            'created_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Build the Cambridge Chief Examiner system prompt.
     */
    protected function buildChiefExaminerSystemPrompt(string $examStandard, string $subjectName, string $code, int $optionsCount = 4): string
    {
        $letterChoices = implode(', ', array_slice(['A', 'B', 'C', 'D', 'E'], 0, $optionsCount));
        return <<<PROMPT
You are the Cambridge Assessment International Education (CAIE) Chief Examiner and Lead Question Setter for {$subjectName} ({$code}).
Your mission is to construct official-standard Multiple Choice Questions (MCQs) indistinguishable from live Cambridge O/A Level / Edexcel examinations.

INTERNATIONAL EXAMINATION BENCHMARK RULES:
1. PURE {$optionsCount}-OPTION MCQ FORMAT: Every single question must have exactly {$optionsCount} options labeled {$letterChoices}. No "All of the above" or "None of the above".
2. UNAMBIGUOUS SINGLE ANSWER KEY: Exactly ONE option is factually, mathematically, and scientifically correct. The other remaining options MUST be plausible distractors.
3. PLAUSIBLE DISTRACTORS: Distractors must reflect common student misconceptions, inverted ratios, arithmetic sign errors, or confusion between related laws/theories. Distribute plausibility across all letter choices ({$letterChoices}).
4. RIGOROUS COGNITIVE TAXONOMY: Distribute questions across:
   - Recall & Definition (20%)
   - Conceptual Understanding (30%)
   - Numerical Application & Problem Solving (35%)
   - Analysis, Experimental Data & Deduction (15%)
5. SCIENTIFIC & NOTATIONAL ACCURACY: Use standard SI units, Cambridge-approved scientific terminology, chemical nomenclature, and algebraic notations.
6. COMPREHENSIVE MARK SCHEME RATIONALE: Provide a clear, analytical explanation for each question explaining why the correct option is true and why each distractor is incorrect.
7. ABSOLUTE ZERO DUPLICATION: Generate completely original questions. Do not copy past paper questions verbatim; emulate their structure, rigor, style, and tone while testing fresh scenarios.

OUTPUT FORMAT:
Respond ONLY with a valid JSON object matching the requested schema. Do NOT include markdown code blocks, preamble, or conversational commentary.
PROMPT;
    }

    /**
     * Fetch a batch of candidate MCQs from Groq.
     */
    protected function fetchCandidateBatch(
        string $systemPrompt,
        string $fewShotContext,
        string $ragContext,
        string $topicString,
        int $count,
        int $batchNumber,
        string $subjectName,
        int $optionsCount = 4
    ): array {
        $keys = array_slice(['A', 'B', 'C', 'D', 'E'], 0, $optionsCount);
        $optionsSchema = [];
        foreach ($keys as $k) {
            $optionsSchema[$k] = "Plausible choice for {$k}";
        }
        $optionsJsonSample = json_encode($optionsSchema, JSON_PRETTY_PRINT);

        $userPrompt = <<<PROMPT
Generate {$count} high-rigor Cambridge examination standard MCQs on the topic(s): "{$topicString}" for {$subjectName}.
Each question MUST have exactly {$optionsCount} options: ({$optionsJsonSample}).

{$fewShotContext}

=== SYLLABUS TEXTBOOK CONTEXT (GROUND TRUTH) ===
{$ragContext}
=== END SYLLABUS CONTEXT ===

OUTPUT JSON SCHEMA:
{
    "batch": {$batchNumber},
    "questions": [
        {
            "topic": "Specific Topic / Subtopic",
            "cognitive_level": "Recall | Understanding | Application | Analysis",
            "question_stem": "Clear, precise Cambridge-style question stem...",
            "options": {$optionsJsonSample},
            "correct_answer": "A",
            "explanation": "Detailed mark scheme explanation showing why correct option is right and why others are misconceptions."
        }
    ]
}
PROMPT;

        $response = $this->callGroq($systemPrompt, $userPrompt, 4000);
        return $this->parseJsonQuestions($response);
    }

    /**
     * Query the Groq API.
     */
    protected function callGroq(string $systemPrompt, string $userPrompt, int $maxTokens = 4000): string
    {
        if (empty($this->groqApiKey)) {
            Log::warning("GroqMockGenerationService: Missing GROQ_API_KEY");
            return '';
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->groqApiKey}",
                'Content-Type'  => 'application/json',
            ])->timeout(90)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => $this->groqModel,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.25,
                'max_tokens'  => $maxTokens,
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->successful()) {
                return (string) $response->json('choices.0.message.content', '');
            }

            Log::warning("Groq API error in mock generation: " . $response->body());
            return '';
        } catch (\Throwable $e) {
            Log::error("Groq API call exception in mock generation: {$e->getMessage()}");
            return '';
        }
    }

    /**
     * Clean and parse JSON response.
     */
    protected function parseJsonQuestions(string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        $clean = preg_replace('/<think>[\s\S]*?<\/think>/i', '', $raw);
        $clean = preg_replace('/^```json\s*/i', '', trim($clean));
        $clean = preg_replace('/^```\s*/', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);

        if (preg_match('/\{[\s\S]*\}/', $clean, $match)) {
            $clean = $match[0];
        }

        $data = json_decode($clean, true);
        if ($data && isset($data['questions']) && is_array($data['questions'])) {
            return $data['questions'];
        }

        return [];
    }

    /**
     * Normalize options array into [A => '...', B => '...', C => '...', D => '...'].
     */
    protected function normalizeOptions($options, int $optionsCount = 4): array
    {
        $keys = array_slice(['A', 'B', 'C', 'D', 'E'], 0, $optionsCount);

        if (!is_array($options)) {
            $res = [];
            foreach ($keys as $k) {
                $res[$k] = "Option {$k}";
            }
            return $res;
        }

        // If sequential array [0 => '...', 1 => '...', 2 => '...']
        if (array_is_list($options)) {
            $res = [];
            foreach ($keys as $idx => $k) {
                $res[$k] = isset($options[$idx]) ? (string) $options[$idx] : "Option {$k}";
            }
            return $res;
        }

        // If associative array
        $res = [];
        foreach ($keys as $k) {
            $res[$k] = isset($options[$k]) ? (string) $options[$k] : (isset($options[strtolower($k)]) ? (string) $options[strtolower($k)] : "Option {$k}");
        }
        return $res;
    }

    /**
     * Normalize correct answer key to A, B, C, D, or E.
     */
    protected function normalizeCorrectAnswerKey(string $keyOrText, array $options, int $optionsCount = 4): string
    {
        $allowed = array_slice(['A', 'B', 'C', 'D', 'E'], 0, $optionsCount);
        $clean = strtoupper(trim($keyOrText));
        if (in_array($clean, $allowed, true)) {
            return $clean;
        }

        // Check if correct answer matches one of option values
        foreach ($options as $k => $val) {
            if (mb_strtolower(trim($val)) === mb_strtolower(trim($keyOrText))) {
                return $k;
            }
        }

        return 'A';
    }
}
