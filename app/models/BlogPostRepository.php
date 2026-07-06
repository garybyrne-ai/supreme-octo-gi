<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Admin-editable blog posts. Falls back to the hard-coded ContentRepository
 * posts until an admin edits them.
 *
 * normalize() is dual-mode: it accepts either the full post shape (when seeding
 * from the defaults) or the simpler admin-form shape (a "content" textarea using
 * "## Heading" section markers, plus one-per-line checklist and "Q || A" FAQ),
 * and always emits the full post shape the blog views expect.
 */
final class BlogPostRepository extends EditableContentRepository
{
    protected function filename(): string
    {
        return 'blog-posts.json';
    }

    protected function normalize(array $row): ?array
    {
        $title = $this->text($row['title'] ?? '', 200);
        if ($title === '') {
            return null;
        }

        $slug = $this->slugify((string) ($row['slug'] ?? '')) ?: $this->slugify($title);

        // Body sections: reuse existing array (seed) or parse the content textarea.
        if (is_array($row['body_sections'] ?? null) && $row['body_sections'] !== []) {
            $sections = [];
            foreach ($row['body_sections'] as $s) {
                if (!is_array($s)) {
                    continue;
                }
                $paras = [];
                foreach ((array) ($s['paragraphs'] ?? []) as $p) {
                    $p = trim((string) $p);
                    if ($p !== '') {
                        $paras[] = mb_substr($p, 0, 4000);
                    }
                }
                $heading = $this->text($s['heading'] ?? '', 160);
                if ($heading !== '' || $paras !== []) {
                    $sections[] = ['heading' => $heading, 'paragraphs' => $paras];
                }
            }
        } else {
            $sections = $this->parseContent((string) ($row['content'] ?? ''));
        }
        if ($sections === []) {
            $sections = [['heading' => '', 'paragraphs' => [$this->text($row['excerpt'] ?? $title, 4000)]]];
        }

        $checklist = $this->parseLines($row['checklist'] ?? [], 300);
        $faq = $this->parseFaq($row['faq'] ?? []);
        $secondary = array_slice($this->parseLines($row['secondary_keywords'] ?? ($row['secondary'] ?? []), 60, true), 0, 10);

        $plain = $title . ' ' . implode(' ', array_map(
            static fn (array $s): string => $s['heading'] . ' ' . implode(' ', $s['paragraphs']),
            $sections
        ));
        $wordCount = str_word_count(strip_tags($plain));
        $body = $sections === [] ? [] : array_merge(...array_map(static fn (array $s): array => $s['paragraphs'], $sections));

        $date = $this->normalizeDate((string) ($row['published_at'] ?? ($row['date'] ?? '')));
        $meta = $this->text($row['meta_description'] ?? ($row['meta'] ?? ''), 320);
        $excerpt = $this->text($row['excerpt'] ?? '', 320);
        if ($excerpt === '') {
            $excerpt = mb_substr(strip_tags((string) ($body[0] ?? $title)), 0, 200);
        }

        return [
            'slug' => $slug,
            'title' => $title,
            'seo_title' => $this->text($row['seo_title'] ?? $title, 200),
            'meta_description' => $meta !== '' ? $meta : $excerpt,
            'category' => $this->text($row['category'] ?? 'Insights', 60) ?: 'Insights',
            'focus_keyword' => $this->text($row['focus_keyword'] ?? ($row['focus'] ?? ''), 90),
            'secondary_keywords' => $secondary,
            'excerpt' => $excerpt,
            'reading_time' => max(1, (int) ceil($wordCount / 210)) . ' min read',
            'word_count' => $wordCount,
            'views' => max(0, (int) ($row['views'] ?? 0)),
            'published_at' => $date,
            'updated_at' => $this->normalizeDate((string) ($row['updated_at'] ?? $date)) ?: $date,
            'body_sections' => $sections,
            'body' => $body,
            'checklist' => $checklist,
            'faq' => $faq,
        ];
    }

    // ---- admin form helpers: turn a stored post back into editable text ----

    /** @param array<int, array<string, mixed>> $sections */
    public static function sectionsToText(array $sections): string
    {
        $parts = [];
        foreach ($sections as $s) {
            if (!is_array($s)) {
                continue;
            }
            $block = '';
            $heading = trim((string) ($s['heading'] ?? ''));
            if ($heading !== '') {
                $block .= '## ' . $heading . "\n\n";
            }
            $block .= implode("\n\n", array_map('strval', (array) ($s['paragraphs'] ?? [])));
            $parts[] = trim($block);
        }

        return implode("\n\n", $parts);
    }

    /** @param array<int, array<string, mixed>> $faq */
    public static function faqToText(array $faq): string
    {
        $lines = [];
        foreach ($faq as $f) {
            if (is_array($f) && ($f['question'] ?? '') !== '') {
                $lines[] = trim((string) $f['question']) . ' || ' . trim((string) ($f['answer'] ?? ''));
            }
        }

        return implode("\n", $lines);
    }

    // ---- parsing ----

    /**
     * @return array<int, array{heading: string, paragraphs: array<int, string>}>
     */
    private function parseContent(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $blocks = preg_split('/\n{2,}/', trim($text)) ?: [];
        $sections = [];
        $current = null;

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }

            if (preg_match('/^#{1,3}\s+/', $block)) {
                $lines = explode("\n", $block, 2);
                $heading = (string) preg_replace('/^#{1,3}\s+/', '', trim($lines[0]));
                if ($current !== null) {
                    $sections[] = $current;
                }
                $current = ['heading' => $this->text($heading, 160), 'paragraphs' => []];
                if (isset($lines[1]) && trim($lines[1]) !== '') {
                    $current['paragraphs'][] = $this->collapse($lines[1]);
                }
                continue;
            }

            if ($current === null) {
                $current = ['heading' => '', 'paragraphs' => []];
            }
            $current['paragraphs'][] = $this->collapse($block);
        }
        if ($current !== null) {
            $sections[] = $current;
        }

        return $sections;
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private function parseLines(mixed $value, int $max, bool $commas = false): array
    {
        $out = [];
        if (is_array($value)) {
            foreach ($value as $item) {
                $item = $this->text($item, $max);
                if ($item !== '') {
                    $out[] = $item;
                }
            }
            return $out;
        }
        $pattern = $commas ? '/[\r\n,]+/' : '/\r\n|\r|\n/';
        foreach (preg_split($pattern, (string) $value) ?: [] as $line) {
            $line = $this->text($line, $max);
            if ($line !== '') {
                $out[] = $line;
            }
        }

        return $out;
    }

    /**
     * @param mixed $value
     * @return array<int, array{question: string, answer: string}>
     */
    private function parseFaq(mixed $value): array
    {
        $out = [];
        if (is_array($value)) {
            foreach ($value as $f) {
                if (!is_array($f)) {
                    continue;
                }
                $q = $this->text($f['question'] ?? '', 200);
                $a = $this->text($f['answer'] ?? '', 1200);
                if ($q !== '' && $a !== '') {
                    $out[] = ['question' => $q, 'answer' => $a];
                }
            }
            return $out;
        }
        foreach (preg_split('/\r\n|\r|\n/', (string) $value) ?: [] as $line) {
            if (trim($line) === '') {
                continue;
            }
            $parts = preg_split('/\s*\|\|\s*|\s+\|\s+/', $line, 2) ?: [];
            $q = $this->text($parts[0] ?? '', 200);
            $a = $this->text($parts[1] ?? '', 1200);
            if ($q !== '' && $a !== '') {
                $out[] = ['question' => $q, 'answer' => $a];
            }
        }

        return $out;
    }

    private function collapse(string $text): string
    {
        return mb_substr(trim((string) preg_replace('/\s+/', ' ', $text)), 0, 4000);
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        return trim((string) preg_replace('/[^a-z0-9]+/', '-', $value), '-');
    }

    private function normalizeDate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return date('Y-m-d');
        }
        $ts = strtotime($value);
        return $ts !== false ? date('Y-m-d', $ts) : date('Y-m-d');
    }
}
