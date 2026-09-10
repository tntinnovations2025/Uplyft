<?php

namespace App\Services;

use App\Models\SubjectMaterial;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentParserService
{
    /**
     * Parse document and return structured chunks with hierarchical metadata.
     *
     * @param SubjectMaterial $material
     * @param int $maxTokens
     * @param int $overlapTokens
     * @return array<int, array{
     *     content: string,
     *     page_number: ?int,
     *     chapter_number: ?int,
     *     chapter_title: ?string,
     *     topic_title: ?string,
     *     token_count: int,
     *     content_hash: string
     * }>
     */
    public function parseAndChunk(SubjectMaterial $material, int $maxTokens = 600, int $overlapTokens = 100): array
    {
        $rawText = $this->extractRawText($material);

        if (empty(trim($rawText))) {
            Log::warning("DocumentParserService: Empty text extracted from material ID: {$material->id}");
            return [];
        }

        return $this->chunkTextHierarchically($rawText, $maxTokens, $overlapTokens);
    }

    /**
     * Extract raw text from file based on extension/mime type.
     */
    public function extractRawText(SubjectMaterial $material): string
    {
        $fullPath = Storage::disk('local')->path($material->file_path);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/' . $material->file_path);
        }

        if (!file_exists($fullPath)) {
            Log::error("DocumentParserService: File not found at path: {$material->file_path}");
            return '';
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf'  => $this->extractFromPdf($fullPath),
            'docx' => $this->extractFromDocx($fullPath),
            'doc'  => $this->extractFromDoc($fullPath),
            'txt'  => $this->extractFromTxt($fullPath),
            default => $this->extractFromGeneric($fullPath),
        };
    }

    /**
     * Extract text with explicit page markers from PDF.
     */
    protected function extractFromPdf(string $filePath): string
    {
        // 1. Primary engine: Smalot PDF Parser
        try {
            if (class_exists(\Smalot\PdfParser\Parser::class)) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($filePath);
                $pages = $pdf->getPages();
                $textParts = [];

                foreach ($pages as $pageNum => $page) {
                    $pageText = trim($page->getText());
                    if (!empty($pageText)) {
                        $textParts[] = "--- Page " . ($pageNum + 1) . " ---\n" . $pageText;
                    }
                }

                $fullText = implode("\n\n", $textParts);
                if (strlen(trim($fullText)) > 20) {
                    return $fullText;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("DocumentParserService: Smalot PDF Parser failed: {$e->getMessage()}");
        }

        // 2. pdftotext CLI tool if available
        try {
            $escaped = escapeshellarg($filePath);
            $output = @shell_exec("pdftotext {$escaped} -");
            if ($output && strlen(trim($output)) > 20) {
                return trim($output);
            }
        } catch (\Throwable $e) {
            // Ignore and proceed to fallback
        }

        // 3. Fallback: stream uncompress
        return $this->basicPdfStreamExtraction($filePath);
    }

    /**
     * Extract text from DOCX (Office Open XML).
     *
     * @note LIBXML_NOENT/LIBXML_XINCLUDE are intentionally NOT used — they
     *       enable XXE (external entity expansion). We parse with NONET so
     *       no external resources resolve during extraction.
     */
    protected function extractFromDocx(string $filePath): string
    {
        $text = '';
        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $xmlData = $zip->getFromIndex($index);
                $xml = new \DOMDocument();
                // Prevent XXE: no entity substitution, no network access.
                @$xml->loadXML($xmlData, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
                $text = strip_tags($xml->saveXML());
            }
            $zip->close();
        }

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /**
     * Extract text from DOC (binary), streaming reads so large legacy
     * documents do not double memory usage in the web/worker process.
     */
    protected function extractFromDoc(string $filePath): string
    {
        $clean = '';
        foreach ($this->streamRead($filePath) as $chunk) {
            $clean .= preg_replace('/[^\x20-\x7E\r\n\t]/', ' ', $chunk);
        }

        return trim(preg_replace('/\s+/', ' ', $clean));
    }

    /**
     * Extract text from TXT via chunked streaming.
     */
    protected function extractFromTxt(string $filePath): string
    {
        return trim($this->streamReadAll($filePath));
    }

    /**
     * Generic text fallback via chunked streaming.
     */
    protected function extractFromGeneric(string $filePath): string
    {
        return trim($this->streamReadAll($filePath));
    }

    /**
     * Fallback stream extraction for PDFs.
     */
    protected function basicPdfStreamExtraction(string $filePath): string
    {
        $content = $this->streamReadAll($filePath);
        if ($content === '') {
            return '';
        }

        $textParts = [];
        if (preg_match_all('/stream\s*\n(.*?)\nendstream/s', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                $decoded = @gzuncompress($stream);
                if ($decoded) {
                    if (preg_match_all('/\(([\x20-\x7E\s]{3,})\)/', $decoded, $textMatches)) {
                        foreach ($textMatches[1] as $t) {
                            $t = trim($t);
                            if (strlen($t) > 3 && !preg_match('/^(URW|Nimbus|Font|Encoding|CID|Identity|Widths|Helvetica|Times|Arial|BaseFont)/i', $t)) {
                                $textParts[] = $t;
                            }
                        }
                    }
                }
            }
        }

        return implode(' ', $textParts);
    }

    /**
     * Semantic Hierarchical Chunking with overlap and metadata tracking.
     */
    public function chunkTextHierarchically(string $text, int $maxTokens = 600, int $overlapTokens = 100): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $structured = [];

        $currentPage = 1;
        $currentChapterNum = null;
        $currentChapterTitle = null;
        $currentTopicTitle = null;

        $wordBuffer = [];
        $approxWordsPerPage = 350;
        $totalWordCounter = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // 1. Detect Explicit Page Markers or Form Feeds
            if (str_contains($line, "\f") || preg_match('/^(?:Page|--- Page|\[Page)\s*(\d+)/i', $trimmed, $m)) {
                if (!empty($m[1])) {
                    $currentPage = (int) $m[1];
                } else {
                    $currentPage++;
                }
                continue;
            }

            // 2. Detect Chapter Headings (e.g. Chapter 3: Photosynthesis, Unit 2 - Optics)
            if (preg_match('/^(?:Chapter|Unit|Module|Lesson|Section)\s+(\d+)\s*[:.\-–—]\s*(.*)/i', $trimmed, $m)) {
                $currentChapterNum = (int) $m[1];
                $currentChapterTitle = trim($m[2]) ?: "Chapter {$currentChapterNum}";
                $currentTopicTitle = $currentChapterTitle;
            } elseif (preg_match('/^(?:Chapter|Unit|Module|Lesson)\s+(\d+)\b/i', $trimmed, $m)) {
                $currentChapterNum = (int) $m[1];
                $currentChapterTitle = "Chapter {$currentChapterNum}";
            }

            // 3. Detect Topic / Section Subheadings (e.g. 3.2 Light Dependent Reactions, # Topic Name)
            if (preg_match('/^(?:\d+\.\d+|\b[A-Z0-9.\-]{1,5}\b)\s+([A-Z][a-zA-Z0-9\s,\'-]{3,60})$/', $trimmed, $m)) {
                $currentTopicTitle = trim($m[1]);
            } elseif (preg_match('/^#{1,3}\s+(.*)/', $trimmed, $m)) {
                $currentTopicTitle = trim($m[1]);
            }

            if (empty($trimmed)) {
                continue;
            }

            // Split line into words
            $lineWords = preg_split('/\s+/', $trimmed);
            foreach ($lineWords as $word) {
                if ($word === '') {
                    continue;
                }
                $wordBuffer[] = $word;
                $totalWordCounter++;

                if (empty($m[1]) && $totalWordCounter % $approxWordsPerPage === 0) {
                    $currentPage++;
                }

                // When word buffer reaches max tokens threshold
                if (count($wordBuffer) >= $maxTokens) {
                    $chunkContent = implode(' ', $wordBuffer);
                    $sanitized = $this->sanitizeUtf8($chunkContent);

                    $structured[] = [
                        'content'        => $sanitized,
                        'page_number'    => $currentPage,
                        'chapter_number' => $currentChapterNum,
                        'chapter_title'  => $currentChapterTitle,
                        'topic_title'    => $currentTopicTitle,
                        'token_count'    => count($wordBuffer),
                        'content_hash'   => hash('sha256', $sanitized),
                    ];

                    // Retain overlapping tokens
                    $wordBuffer = array_slice($wordBuffer, -$overlapTokens);
                }
            }
        }

        // Flush remaining buffer
        if (count($wordBuffer) >= config('lms.chunking.min_chunk_length', 20)) {
            $chunkContent = implode(' ', $wordBuffer);
            $sanitized = $this->sanitizeUtf8($chunkContent);

            $structured[] = [
                'content'        => $sanitized,
                'page_number'    => $currentPage,
                'chapter_number' => $currentChapterNum,
                'chapter_title'  => $currentChapterTitle,
                'topic_title'    => $currentTopicTitle,
                'token_count'    => count($wordBuffer),
                'content_hash'   => hash('sha256', $sanitized),
            ];
        }

        return $structured;
    }

    /**
     * Yield file content in bounded chunks so a single file never needs to
     * be inflated twice in memory (fread window + accumulated output).
     *
     * @return \Generator<int, string>
     */
    protected function streamRead(string $filePath, int $chunkSize = 1048576): \Generator
    {
        $handle = @fopen($filePath, 'rb');
        if (! $handle) {
            return;
        }

        try {
            while (! feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                if ($chunk === false || $chunk === '') {
                    break;
                }
                yield $chunk;
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * Concatenate a streamed file into a single string.
     */
    protected function streamReadAll(string $filePath): string
    {
        $contents = '';
        foreach ($this->streamRead($filePath) as $chunk) {
            $contents .= $chunk;
        }

        return $contents;
    }

    /**
     * Sanitize UTF-8 strings.
     */
    protected function sanitizeUtf8(string $text): string
    {
        $sanitized = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        if ($sanitized === false) {
            $sanitized = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        return trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string) $sanitized));
    }
}
