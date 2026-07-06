<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Admin-editable portfolio / work items. Falls back to the hard-coded
 * ContentRepository defaults until an admin edits them.
 */
final class PortfolioRepository extends EditableContentRepository
{
    private const ACCENTS = ['blue', 'cyan', 'green', 'orange', 'purple'];

    protected function filename(): string
    {
        return 'portfolio.json';
    }

    protected function normalize(array $row): ?array
    {
        $title = $this->text($row['title'] ?? '', 120);
        if ($title === '') {
            return null;
        }

        $accent = strtolower(trim((string) ($row['accent'] ?? 'blue')));
        if (!in_array($accent, self::ACCENTS, true)) {
            $accent = 'blue';
        }

        $url = trim((string) ($row['url'] ?? ''));
        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            $url = '';
        }

        $item = [
            'title' => $title,
            'category' => $this->text($row['category'] ?? '', 90),
            'summary' => $this->text($row['summary'] ?? '', 320),
            'accent' => $accent,
        ];
        // url and image are optional — only store when present.
        if ($url !== '') {
            $item['url'] = $url;
        }
        $image = $this->text($row['image'] ?? '', 160);
        if ($image !== '') {
            $item['image'] = $image;
        }

        return $item;
    }
}
