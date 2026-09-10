<?php

namespace App\Support;

/**
 * Conservative allowlist HTML sanitizer for server-rendered AI content.
 *
 * Only a fixed set of formatting tags survive; every other tag is removed
 * (its inner text is preserved). Attributes are restricted to a small
 * safe set and URL-bearing attributes are scheme-validated so that
 * javascript:/data:/vbscript: payloads and event handlers cannot pass.
 *
 * This is used as a final defensive layer where `{!! !!}` output of
 * third-party (AI) content is required by the UI design.
 */
class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'div', 'span', 'br', 'hr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'mark', 'small', 'sub', 'sup',
        'ul', 'ol', 'li', 'blockquote', 'code', 'pre',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption',
        'a', 'img',
    ];

    private const SAFE_ATTRIBUTES = ['class', 'id', 'title', 'style', 'alt', 'rowspan', 'colspan'];

    private const SAFE_URL_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    private const FORBIDDEN_CSS_TOKENS = [
        'url(',
        'expression',
        'javascript:',
        'vbscript:',
        'behavior',
        '-moz-binding',
        '@import',
    ];

    /**
     * Sanitize a blob of generated HTML down to the allowlist.
     */
    public function clean(?string $html): string
    {
        $html = (string) ($html ?? '');

        if ($html === '') {
            return '';
        }

        // Drop processing instructions and comments.
        $html = preg_replace('/<\?[\s\S]*?\?>/', '', $html) ?? $html;
        $html = preg_replace('/<!-{2}[\s\S]*?-{2}>/', '', $html) ?? $html;

        // Remove <style> / <script> blocks entirely.
        $html = preg_replace('/<\s*(?:style|script)\b[^>]*>[\s\S]*?<\s*\/\s*(?:style|script)\s*>/i', '', $html) ?? $html;

        // Remove dangerous or non-formatting tags (keeping inner content).
        $blocked = implode('|', [
            'title', 'base', 'link', 'meta', 'object', 'embed', 'iframe',
            'form', 'input', 'button', 'select', 'textarea', 'option',
            'source', 'video', 'audio', 'canvas', 'svg', 'math', 'template',
            'noscript', 'applet', 'param', 'frame', 'frameset',
        ]);
        $html = preg_replace(sprintf('~</?(?:%s)\b[^>]*>~i', $blocked), '', $html) ?? $html;

        // Rebuild remaining tags with allowlisted tags + sanitized attributes.
        $html = preg_replace_callback('/<([a-zA-Z][a-zA-Z0-9]*)([^>]*)>/', function (array $match): string {
            $tag = strtolower($match[1]);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                return '';
            }

            return '<' . $tag . $this->sanitizeAttributes($tag, $match[2]) . '>';
        }, $html) ?? $html;

        // Neutralize any scheme-based payloads that survived in raw text.
        $html = preg_replace('/\bjavascript\s*:/i', 'javascript:', $html) ?? $html;

        return $html;
    }

    /**
     * Parse and re-emit a tag's attributes using the per-tag allowlist rules.
     */
    protected function sanitizeAttributes(string $tag, string $raw): string
    {
        if (! preg_match_all('/([a-zA-Z_:][a-zA-Z0-9_.:-]*)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+)))?/', $raw, $matches, PREG_SET_ORDER)) {
            return '';
        }

        $allowed = [];

        foreach ($matches as $match) {
            $name = strtolower($match[1]);

            if (str_starts_with($name, 'on')) {
                continue;
            }

            if (! in_array($name, self::SAFE_ATTRIBUTES, true) && ! in_array($name, ['href', 'src', 'target', 'rel'], true)) {
                continue;
            }

            $value = html_entity_decode($match[2] !== '' ? $match[2] : (($match[3] ?? '') !== '' ? $match[3] : ($match[4] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if (! $this->attributeAllowed($tag, $name, $value)) {
                continue;
            }

            $allowed[$name] = $value;
        }

        // Per-tag additions.
        if ($tag === 'a' && ! isset($allowed['rel'])) {
            $allowed['rel'] = 'noopener noreferrer';
        }

        $out = '';
        foreach ($allowed as $name => $value) {
            $out .= ' ' . $name . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }

        return $out;
    }

    /**
     * Decide whether a single attribute name/value survives for a tag.
     */
    protected function attributeAllowed(string $tag, string $name, string $value): bool
    {
        if ($name === 'href') {
            return $this->safeUrl($value, allowRelative: true, allowFragment: true);
        }

        if ($name === 'src') {
            if (! in_array($tag, ['img'], true)) {
                return false;
            }

            return $this->safeUrl($value) || str_starts_with(strtolower($value), 'data:image/png;base64,')
                || str_starts_with(strtolower($value), 'data:image/jpeg;base64,')
                || str_starts_with(strtolower($value), 'data:image/gif;base64,')
                || str_starts_with(strtolower($value), 'data:image/webp;base64,');
        }

        if ($name === 'target') {
            return in_array($value, ['_blank', '_self', '_top', '_parent'], true);
        }

        if ($name === 'rel') {
            return preg_match('/^[a-z\s-]+$/', $value) === 1;
        }

        if ($name === 'style') {
            $value = strtolower($value);

            foreach (self::FORBIDDEN_CSS_TOKENS as $token) {
                if (str_contains($value, $token)) {
                    return false;
                }
            }

            return true;
        }

        return true;
    }

    /**
     * URL scheme validation.
     */
    protected function safeUrl(string $url, bool $allowRelative = false, bool $allowFragment = false): bool
    {
        $trimmed = trim($url);

        if ($trimmed === '') {
            return false;
        }

        if ($allowFragment && str_starts_with($trimmed, '#')) {
            return true;
        }

        if ($allowRelative && in_array(substr($trimmed, 0, 1), ['/', '.'], true)) {
            return true;
        }

        $lower = strtolower($trimmed);
        $parsed = parse_url($lower);

        if (! is_array($parsed) || ! isset($parsed['scheme'])) {
            return false;
        }

        return in_array($parsed['scheme'], self::SAFE_URL_SCHEMES, true);
    }
}