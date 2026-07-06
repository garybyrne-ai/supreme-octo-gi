<?php

declare(strict_types=1);

namespace App\Services;

/**
 * A lightweight, same-host site crawler. Starting from one URL it breadth-first
 * discovers internal pages, fetches each (via an injected SSRF-safe fetcher),
 * runs the injected per-page SEO audit, and rolls the per-page results up into
 * one site-wide score and an aggregated issue list.
 *
 * It intentionally stays small: a hard page cap and an overall wall-clock
 * deadline keep a crawl bounded and fast, so it never behaves like an
 * unthrottled bot. This audits the member's own site — not the whole web.
 */
final class SiteCrawler
{
    /** Never crawl more than this regardless of the requested cap. */
    public const HARD_CAP = 30;

    /** Overall wall-clock budget for a crawl, in seconds. */
    private const DEADLINE = 45;

    /** File extensions that are never HTML pages. */
    private const SKIP_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'css', 'js', 'pdf', 'zip', 'rar', 'gz', 'mp4', 'mp3', 'avi', 'mov', 'woff', 'woff2', 'ttf', 'eot', 'xml', 'json', 'csv', 'doc', 'docx', 'xls', 'xlsx'];

    /**
     * @param callable(string):string $fetch  Returns page HTML ('' on failure).
     * @param callable(string,string):array $audit  ($url,$html) => seoAudit result.
     * @param int $maxPages Requested page cap (clamped to HARD_CAP).
     * @return array<string, mixed>
     */
    public function crawl(string $startUrl, int $maxPages, callable $fetch, callable $audit): array
    {
        $maxPages = max(1, min(self::HARD_CAP, $maxPages));
        $startHost = strtolower((string) parse_url($startUrl, PHP_URL_HOST));
        $startTime = time();

        $queue = [$this->canonicalize($startUrl)];
        $seen = [$queue[0] => true];
        $pages = [];
        $reachedCap = false;

        while ($queue !== []) {
            if (count($pages) >= $maxPages) {
                $reachedCap = $queue !== [];
                break;
            }
            if (time() - $startTime > self::DEADLINE) {
                $reachedCap = true; // ran out of time budget with pages still queued
                break;
            }
            $url = array_shift($queue);

            $html = $fetch($url);
            if ($html === '') {
                continue;
            }

            $result = $audit($url, $html);
            $checks = is_array($result['checks'] ?? null) ? $result['checks'] : [];
            $failed = [];
            foreach ($checks as $c) {
                if (empty($c['present'])) {
                    $failed[] = ['label' => (string) ($c['label'] ?? ''), 'weight' => (int) ($c['weight'] ?? 1), 'advice' => (string) ($c['advice'] ?? ''), 'details' => $c['details'] ?? [], 'value' => (string) ($c['value'] ?? '')];
                }
            }
            $pages[] = [
                'url' => $url,
                'score' => (int) ($result['score'] ?? 0),
                'title' => (string) ($result['title'] ?? ''),
                'words' => (int) ($result['words'] ?? 0),
                'failed' => $failed,
                'fail_count' => count($failed),
            ];

            // Discover more internal links to crawl.
            foreach ($this->extractLinks($html, $url, $startHost) as $link) {
                if (!isset($seen[$link]) && count($seen) < self::HARD_CAP * 4) {
                    $seen[$link] = true;
                    $queue[] = $link;
                }
            }
        }

        return $this->rollup($startUrl, $pages, $reachedCap);
    }

    /**
     * Aggregate per-page audits into a site-wide score and issue list.
     *
     * @param array<int, array<string, mixed>> $pages
     * @return array<string, mixed>
     */
    private function rollup(string $startUrl, array $pages, bool $reachedCap): array
    {
        $crawled = count($pages);
        $siteScore = $crawled > 0
            ? (int) round(array_sum(array_map(static fn (array $p): int => (int) $p['score'], $pages)) / $crawled)
            : 0;

        // Aggregate every failing check across pages: how many pages hit it,
        // the shared advice, and a couple of concrete example locations.
        $issues = [];
        foreach ($pages as $page) {
            foreach ($page['failed'] as $fail) {
                $label = $fail['label'];
                if ($label === '') {
                    continue;
                }
                if (!isset($issues[$label])) {
                    $issues[$label] = ['label' => $label, 'weight' => $fail['weight'], 'advice' => $fail['advice'], 'pages' => 0, 'examples' => []];
                }
                $issues[$label]['pages']++;
                if (count($issues[$label]['examples']) < 4) {
                    $issues[$label]['examples'][] = ['url' => $page['url'], 'value' => $fail['value'], 'details' => array_slice($fail['details'], 0, 3)];
                }
            }
        }

        // Rank issues by impact: weight × pages affected.
        $issues = array_values($issues);
        usort($issues, static fn (array $a, array $b): int => ($b['weight'] * $b['pages']) <=> ($a['weight'] * $a['pages']));

        // Best and worst pages help the member prioritise.
        $ranked = $pages;
        usort($ranked, static fn (array $a, array $b): int => $a['score'] <=> $b['score']);

        return [
            'start' => $startUrl,
            'host' => (string) (parse_url($startUrl, PHP_URL_HOST) ?: $startUrl),
            'crawled' => $crawled,
            'site_score' => $siteScore,
            'reached_cap' => $reachedCap,
            'issues' => $issues,
            'issue_count' => count($issues),
            'pages' => $pages,
            'worst_pages' => array_slice($ranked, 0, 5),
        ];
    }

    /**
     * Extract same-host, crawlable page links from a page's HTML.
     *
     * @return array<int, string>
     */
    private function extractLinks(string $html, string $baseUrl, string $host): array
    {
        if (!preg_match_all('/<a\b[^>]*\bhref=["\']([^"\']+)["\']/i', $html, $m)) {
            return [];
        }

        $out = [];
        foreach ($m[1] as $href) {
            $href = trim($href);
            if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:') || str_starts_with($href, 'data:')) {
                continue;
            }
            $abs = $this->resolveUrl($href, $baseUrl);
            if ($abs === null) {
                continue;
            }
            if (strtolower((string) parse_url($abs, PHP_URL_HOST)) !== $host) {
                continue; // same-host only
            }
            $path = (string) parse_url($abs, PHP_URL_PATH);
            $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
            if ($ext !== '' && in_array($ext, self::SKIP_EXT, true)) {
                continue;
            }
            $out[] = $this->canonicalize($abs);
        }

        return array_values(array_unique($out));
    }

    /**
     * Resolve a possibly-relative href against the page URL. Returns an absolute
     * http(s) URL or null.
     */
    private function resolveUrl(string $href, string $baseUrl): ?string
    {
        if (preg_match('#^https?://#i', $href)) {
            return $href;
        }
        $scheme = (string) parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';
        $host = (string) parse_url($baseUrl, PHP_URL_HOST);
        if ($host === '') {
            return null;
        }
        if (str_starts_with($href, '//')) {
            return $scheme . ':' . $href;
        }
        if (str_starts_with($href, '/')) {
            return $scheme . '://' . $host . $href;
        }
        // Relative to the current directory.
        $basePath = (string) parse_url($baseUrl, PHP_URL_PATH);
        $dir = rtrim(str_contains(substr($basePath, 1), '/') ? dirname($basePath) : '/', '/');
        return $scheme . '://' . $host . $dir . '/' . $href;
    }

    /**
     * Normalise a URL for dedupe: drop the fragment and any trailing slash.
     */
    private function canonicalize(string $url): string
    {
        $url = preg_replace('/#.*$/', '', $url) ?? $url;
        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return rtrim($url, '/');
        }
        $scheme = strtolower($parts['scheme'] ?? 'https');
        $host = strtolower($parts['host']);
        $path = rtrim($parts['path'] ?? '/', '/');
        if ($path === '') {
            $path = '';
        }
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        return $scheme . '://' . $host . $path . $query;
    }
}
