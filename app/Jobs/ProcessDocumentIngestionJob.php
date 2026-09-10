<?php

namespace App\Jobs;

use App\Models\RagDocumentChunk;
use App\Models\SubjectMaterial;
use App\Services\DocumentParserService;
use App\Services\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDocumentIngestionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries = 2;
    public int $backoff = 30;

    protected int $materialId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $materialId)
    {
        $this->materialId = $materialId;
    }

    /**
     * Execute the job.
     */
    public function handle(DocumentParserService $parserService, EmbeddingService $embeddingService): void
    {
        @set_time_limit(900);
        @ini_set('memory_limit', '1024M');

        $material = SubjectMaterial::find($this->materialId);
        if (!$material) {
            Log::warning("ProcessDocumentIngestionJob: Material {$this->materialId} not found.");
            return;
        }

        Log::info("ProcessDocumentIngestionJob: Starting ingestion for Material ID: {$material->id} ('{$material->title}')");

        // 1. Parse and chunk document
        $maxTokens = (int) config('lms.chunking.max_tokens', 600);
        $overlap = (int) config('lms.chunking.overlap', 100);

        $chunks = $parserService->parseAndChunk($material, $maxTokens, $overlap);

        if (empty($chunks)) {
            Log::warning("ProcessDocumentIngestionJob: No chunks generated for Material ID: {$material->id}");
            return;
        }

        $totalChunks = count($chunks);
        Log::info("ProcessDocumentIngestionJob: Generated {$totalChunks} chunks for Material ID: {$material->id}. Generating embeddings...");

        // 2. Remove previously indexed chunks (support re-ingestion)
        RagDocumentChunk::where('subject_material_id', $material->id)->delete();

        // 3. Batch generate embeddings and persist chunks
        $now = now();
        $batchInserts = [];
        $batchSize = 25;

        for ($i = 0; $i < $totalChunks; $i += $batchSize) {
            $slice = array_slice($chunks, $i, $batchSize);
            $texts = array_column($slice, 'content');

            // Generate dense embeddings for slice
            $embeddings = $embeddingService->generateBatchEmbeddings($texts, $material->subject_id);

            foreach ($slice as $k => $item) {
                $chunkIndex = $i + $k;
                $portionRatio = ($chunkIndex + 1) / max($totalChunks, 1);
                $bookPortion = $portionRatio <= 0.5 ? 'first_half' : 'second_half';

                $embVector = $embeddings[$k] ?? $embeddingService->generateEmbedding($item['content'], $material->subject_id);
                $vectorDim = count($embVector);

                $batchInserts[] = [
                    'subject_material_id' => $material->id,
                    'subject_id'          => $material->subject_id,
                    'chunk_index'         => $chunkIndex,
                    'chunk_content'       => $item['content'],
                    'token_count'         => $item['token_count'],
                    'content_hash'        => $item['content_hash'],
                    'page_number'         => $item['page_number'],
                    'chapter_number'      => $item['chapter_number'],
                    'chapter_title'       => $item['chapter_title'],
                    'topic_title'         => $item['topic_title'],
                    'book_portion'        => $bookPortion,
                    'embedding_vector'    => json_encode($embVector),
                    'vector_dimension'    => $vectorDim,
                    'is_embedded'         => true,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }

            if (!empty($batchInserts)) {
                DB::table('rag_document_chunks')->insert($batchInserts);
                $batchInserts = [];
            }
        }

        $material->update(['is_rag_indexed' => true]);
        Log::info("ProcessDocumentIngestionJob: Successfully indexed and embedded Material ID {$material->id} with {$totalChunks} chunks in MySQL.");
    }
}
