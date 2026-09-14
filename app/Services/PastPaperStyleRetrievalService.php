<?php

namespace App\Services;

use App\Models\PastPaperExemplar;
use Illuminate\Database\Eloquent\Collection;

class PastPaperStyleRetrievalService
{
    /**
     * Retrieve 3–5 representative past paper exemplars for few-shot style conditioning.
     *
     * @param int $subjectId
     * @param array $topics
     * @param int $limit
     * @return Collection<PastPaperExemplar>
     */
    public function getStyleExemplars(int $subjectId, array $topics = [], int $limit = 5): Collection
    {
        $query = PastPaperExemplar::where('subject_id', $subjectId);

        if (!empty($topics)) {
            $query->where(function ($q) use ($topics) {
                foreach ($topics as $t) {
                    $q->orWhere('topic_tag', 'like', "%{$t}%")
                      ->orWhere('question_stem', 'like', "%{$t}%");
                }
            });
        }

        $exemplars = $query->inRandomOrder()->limit($limit)->get();

        // Fallback to any available exemplars for this subject if topic-specific count is low
        if ($exemplars->count() < 3) {
            $fallback = PastPaperExemplar::where('subject_id', $subjectId)
                ->whereNotIn('id', $exemplars->pluck('id'))
                ->inRandomOrder()
                ->limit($limit - $exemplars->count())
                ->get();
            $exemplars = $exemplars->merge($fallback);
        }

        return $exemplars;
    }

    /**
     * Format retrieved exemplars into a Few-Shot string for LLM system/user prompts.
     */
    public function formatFewShotContext(Collection $exemplars): string
    {
        if ($exemplars->isEmpty()) {
            return "No previous past paper exemplars indexed yet. Follow standard Cambridge CAIE O/A Level style.";
        }

        $lines = ["### OFFICIAL CAMBRIDGE / EDEXCEL PAST PAPER STYLE EXEMPLARS (FEW-SHOT GUIDES):"];
        $i = 1;
        foreach ($exemplars as $ex) {
            $opts = $ex->options ?? [];
            $lines[] = "--- Example {$i} ---";
            $lines[] = "Topic: " . ($ex->topic_tag ?: 'General Core');
            $lines[] = "Question Stem: " . $ex->question_stem;
            $lines[] = "Options:";
            $lines[] = "  A: " . ($opts['A'] ?? '');
            $lines[] = "  B: " . ($opts['B'] ?? '');
            $lines[] = "  C: " . ($opts['C'] ?? '');
            $lines[] = "  D: " . ($opts['D'] ?? '');
            $lines[] = "Key Answer: " . $ex->correct_answer;
            if ($ex->explanation) {
                $lines[] = "Rationale: " . $ex->explanation;
            }
            $i++;
        }

        return implode("\n", $lines);
    }
}
