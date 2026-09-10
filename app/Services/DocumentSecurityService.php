<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Header/MIME sniffing + content sanitization for document uploads.
 *
 * Validation NEVER trusts the browser-declared MIME type or the client
 * filename. Instead the actual file bytes are inspected:
 *   - PDFs:  -%PDF- magic within the first 2048 bytes
 *   - DOCX:  ZIP container (PK\x03\x04) containing [Content_Types].xml + word/document.xml
 *   - DOC:   OLE2 Compound File magic (D0CF11E0A1B11AE1)
 *   - TXT:   plain text with script/executable markers rejected
 *
 * Files that cannot be positively classified (executables, HTML, renamed
 * PHP shells, macros-bearing DOCX, oversized payloads) are rejected.
 */
class DocumentSecurityService
{
    public const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'txt'];

    private const HEAD_SCAN_BYTES = 65536;

    private const TEXT_SCRIPT_SCAN_BYTES = 524288;

    /**
     * Inspect the uploaded file and return a normalized descriptor.
     *
     * @throws ValidationException when the file is rejected.
     */
    public function inspect(UploadedFile $file): array
    {
        $result = $this->sniff($file);

        if (! $result['valid']) {
            throw ValidationException::withMessages(['document' => $result['reason']]);
        }

        return $result;
    }

    /**
     * Validate the upload; returns a human-readable reason or null if OK.
     */
    public function validateUpload(UploadedFile $file): ?string
    {
        $result = $this->sniff($file);

        return $result['valid'] ? null : $result['reason'];
    }

    /**
     * Generate a server-side trustless storage name for an upload.
     */
    public function trustedName(string $extension): string
    {
        return Str::lower(Str::random(40)) . '.' . strtolower($extension);
    }

    /**
     * Classify a file by inspecting its actual bytes.
     *
     * @return array{valid: bool, extension?: string, mime?: string, reason?: string}
     */
    public function sniff(UploadedFile $file): array
    {
        $failed = fn (string $reason): array => ['valid' => false, 'reason' => $reason];

        if ($file->getError() === UPLOAD_ERR_INI_SIZE) {
            return $failed('Upload was rejected by the server before processing (file exceeds the PHP upload size limit). Raise upload_max_filesize / post_max_size in php.ini.');
        }

        if (! $file->isValid()) {
            return $failed('The uploaded document could not be read. Please try again.');
        }

        $maxKb = (int) config('lms.max_upload_size_kb', 524288);
        if ($file->getSize() > $maxKb * 1024) {
            return $failed('Document exceeds the maximum allowed upload size of ' . number_format($maxKb / 1024, 1) . ' MB.');
        }

        $path = $file->getRealPath();
        if (! $path || ! is_readable($path)) {
            return $failed('The uploaded document is not readable on the server.');
        }

        $head = $this->readBytes($path, self::HEAD_SCAN_BYTES);
        $tail = $this->readBytes($path, 256, fromEnd: true);

        if ($head === '' && $file->getSize() > 0) {
            return $failed('Unable to inspect the uploaded document content.');
        }

        // Executable containers disguised as documents.
        $signature = substr($head, 0, 4);
        if ($this->startsWith($signature, ["MZ", "\x7FELF"]) || $this->signatureInFirstBytes($head, ["MZ", "\x7FELF", "\xCA\xFE\xBA\xBE"])) {
            return $failed('Executable or unknown binary content is not allowed as a document upload.');
        }

        // PDF
        if (preg_match('/%PDF-(\d\.\d)/', substr($head, 0, 2048))) {
            return $this->ok('pdf', 'application/pdf');
        }

        // OLE2 Compound File (legacy .doc). Optionally validate the ".doc" claim
        // by checking the docHeader trie after the 512-byte header later; the
        // magic alone is a strong positive signal.
        if (str_starts_with($head, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1")) {
            return $this->ok('doc', 'application/msword');
        }

        // ZIP container: must be a real DOCX (and macro-free).
        if (str_starts_with($head, "PK\x03\x04")) {
            return $this->validateDocx($path);
        }

        // Plain-text path: reject binary payloads and script/executable markers.
        if ($this->looksBinary($head)) {
            return $failed('Binary content was detected inside a text document. Only plain text files are accepted.');
        }

        if ($this->containsForbiddenScriptMarkers($head, $tail, $file->getSize())) {
            return $failed('The document contains embedded script or executable markers (PHP, HTML, or similar). Uploading web shells or active content is not allowed.');
        }

        return $this->ok('txt', 'text/plain');
    }

    /**
     * Return a positive classification descriptor.
     */
    protected function ok(string $extension, string $mime): array
    {
        return [
            'valid'     => true,
            'extension' => $extension,
            'mime'      => $mime,
        ];
    }

    /**
     * Stream-read the first/last N bytes of a file without loading it fully.
     */
    protected function readBytes(string $path, int $bytes, bool $fromEnd = false): string
    {
        $handle = @fopen($path, 'rb');
        if (! $handle) {
            return '';
        }

        try {
            if ($fromEnd) {
                $size = (int) fstat($handle)['size'];
                $offset = max(0, $size - $bytes);
                fseek($handle, $offset);
            }

            $data = (string) fread($handle, $bytes);
        } finally {
            fclose($handle);
        }

        return $data;
    }

    /**
     * Validate a ZIP container as a macro-free DOCX.
     */
    protected function validateDocx(string $path): array
    {
        $failed = fn (string $reason): array => ['valid' => false, 'reason' => $reason];

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return $failed('The ZIP-based document could not be parsed. Only valid .docx files are accepted.');
        }

        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = (string) $zip->getNameIndex($i);
        }
        $zip->close();

        $hasContentTypes = in_array('[Content_Types].xml', $names, true);
        $hasWordDocument = in_array('word/document.xml', $names, true);

        if (! $hasContentTypes || ! $hasWordDocument) {
            return $failed('The ZIP container is not a valid .docx document (missing content type entries).');
        }

        foreach ($names as $name) {
            $lower = strtolower($name);
            $extension = pathinfo($lower, PATHINFO_EXTENSION);

            if (str_contains($lower, 'vbagproject') || str_contains($lower, 'vba') || $extension === 'bin') {
                return $failed('Macro-enabled Word documents are not accepted.');
            }

            if (in_array($extension, ['php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'pl', 'py', 'sh', 'js', 'jsp', 'jspx', 'cgi', 'exe', 'bat', 'cmd', 'ps1'], true)) {
                return $failed('The document contains an active script or executable entry (' . $name . ') and is not accepted.');
            }

            if (str_contains($lower, '../') || str_contains($lower, "\0")) {
                return $failed('The document contains unsafe paths and is not accepted.');
            }
        }

        return $this->ok('docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    /**
     * Decide whether the leading bytes look like binary rather than text.
     */
    protected function looksBinary(string $head): bool
    {
        if ($head === '') {
            return true;
        }

        if (str_contains($head, "\x00")) {
            return true;
        }

        $nonPrintable = preg_match_all('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $head);
        $length = max(1, strlen($head));

        return ($nonPrintable / $length) > 0.05;
    }

    /**
     * Scan leading bytes (+ tail) for web-shell / script markers.
     */
    protected function containsForbiddenScriptMarkers(string $head, string $tail, int $size): bool
    {
        $scan = substr($head, 0, self::TEXT_SCRIPT_SCAN_BYTES) . '|' . $tail;
        $scan = strtolower($scan);

        $markers = [
            '<?php',
            '<?=',
            '<? ',
            '<?xml',
            '<script',
            '<html',
            '<!doctype html',
            '<form',
            '<iframe',
            '<object',
            '<embed',
            '<%',
            'javascript:',
            'vbscript:',
            'data:text/html',
            'base64,',
            'eval(',
            'system(',
            'shell_exec',
        ];

        foreach ($markers as $marker) {
            if (str_contains($scan, $marker)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether the buffer begins with any known byte string.
     */
    protected function startsWith(string $value, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if ($value === '' && $prefix === '') {
                continue;
            }
            if ($prefix !== '' && str_starts_with($value, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Search for a known signature within a short window of the buffer.
     */
    protected function signatureInFirstBytes(string $head, array $signatures): bool
    {
        $window = substr($head, 0, 16);

        foreach ($signatures as $signature) {
            if ($signature === '') {
                continue;
            }
            if (str_contains($window, $signature)) {
                return true;
            }
        }

        return false;
    }
}