<?php

declare(strict_types=1);

namespace App\Services;

final class TechNewsService
{
    private const CACHE_TTL = 86400;

    /** @return array<int, array{title: string, url: string, source: string, date: string}> */
    public function items(int $limit = 8, bool $allowRefresh = false): array
    {
        $cacheFile = base_path('storage/cache/tech-news.json');
        $cached = $this->readCache($cacheFile);

        if ($cached && (($cached['updated_at'] ?? 0) > time() - self::CACHE_TTL)) {
            return array_slice($cached['items'] ?? [], 0, $limit);
        }

        if (!$allowRefresh) {
            return array_slice($cached['items'] ?? $this->fallbackItems(), 0, $limit);
        }

        $items = $this->fetchItems();
        if ($items === [] && $cached) {
            return array_slice($cached['items'] ?? [], 0, $limit);
        }

        if ($items === []) {
            $items = $this->fallbackItems();
        }

        $items = array_slice($items, 0, $limit);
        $this->writeCache($cacheFile, $items);

        return $items;
    }

    /** @return array<string, mixed>|null */
    private function readCache(string $cacheFile): ?array
    {
        if (!is_file($cacheFile)) {
            return null;
        }

        $payload = json_decode((string) @file_get_contents($cacheFile), true);
        return is_array($payload) ? $payload : null;
    }

    /** @param array<int, array{title: string, url: string, source: string, date: string}> $items */
    private function writeCache(string $cacheFile, array $items): void
    {
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        @file_put_contents($cacheFile, json_encode([
            'updated_at' => time(),
            'items' => $items,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /** @return array<int, array{title: string, url: string, source: string, date: string}> */
    private function fetchItems(): array
    {
        if (!function_exists('simplexml_load_string')) {
            return [];
        }

        $feeds = [
            'The Verge' => 'https://www.theverge.com/rss/index.xml',
            'Ars Technica' => 'https://feeds.arstechnica.com/arstechnica/index',
            'TechCrunch' => 'https://techcrunch.com/feed/',
        ];

        $items = [];
        foreach ($feeds as $source => $url) {
            $xml = $this->fetchUrl($url);
            if ($xml === '') {
                continue;
            }

            $feed = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            if (!$feed) {
                continue;
            }

            foreach (($feed->channel->item ?? []) as $item) {
                $title = trim((string) $item->title);
                $link = trim((string) $item->link);
                if ($title === '' || $link === '') {
                    continue;
                }

                $timestamp = strtotime((string) ($item->pubDate ?? '')) ?: time();
                $items[] = [
                    'title' => $this->cleanTitle($title),
                    'url' => $link,
                    'source' => $source,
                    'date' => date('M j', $timestamp),
                    '_sort' => $timestamp,
                ];
            }
        }

        usort($items, static fn (array $a, array $b): int => ($b['_sort'] ?? 0) <=> ($a['_sort'] ?? 0));

        return array_map(static function (array $item): array {
            unset($item['_sort']);
            return $item;
        }, $items);
    }

    private function fetchUrl(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 4,
                'user_agent' => 'CrestWebMediaTechTicker/1.0',
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $result = @file_get_contents($url, false, $context);
        return is_string($result) ? $result : '';
    }

    private function cleanTitle(string $title): string
    {
        return trim(html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    /** @return array<int, array{title: string, url: string, source: string, date: string}> */
    private function fallbackItems(): array
    {
        return [
            ['title' => 'AI search, Core Web Vitals and secure apps are reshaping digital growth', 'url' => '/blog', 'source' => 'Crest Web Media', 'date' => date('M j')],
            ['title' => 'Modern websites need SEO, speed, security and conversion tracking working together', 'url' => '/seo-tools', 'source' => 'Crest Web Media', 'date' => date('M j')],
            ['title' => 'AI automation is becoming a practical advantage for support, sales and reporting workflows', 'url' => '/ai-automation-finder', 'source' => 'Crest Web Media', 'date' => date('M j')],
        ];
    }
}
