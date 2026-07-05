<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Admin-editable services. Falls back to the hard-coded ContentRepository
 * defaults until an admin edits them. Detail-page extras (process, panels …)
 * still layer on by slug via ContentRepository::serviceBySlug().
 */
final class ServiceContentRepository extends EditableContentRepository
{
    protected function filename(): string
    {
        return 'services.json';
    }

    protected function normalize(array $row): ?array
    {
        $title = $this->text($row['title'] ?? '', 90);
        if ($title === '') {
            return null;
        }

        $slug = $this->slugify((string) ($row['slug'] ?? ''));
        if ($slug === '') {
            $slug = $this->slugify($title);
        }

        // Tags may arrive as an array (defaults) or a comma/newline list (form).
        $tags = [];
        $rawTags = $row['tags'] ?? [];
        if (is_string($rawTags)) {
            $rawTags = preg_split('/[\r\n,]+/', $rawTags) ?: [];
        }
        foreach ((array) $rawTags as $tag) {
            $tag = $this->text($tag, 40);
            if ($tag !== '') {
                $tags[] = $tag;
            }
        }

        $icon = trim((string) ($row['icon'] ?? ''));
        if ($icon === '') {
            $icon = 'fa-solid fa-screwdriver-wrench';
        }

        return [
            'slug' => $slug,
            'title' => $title,
            'summary' => $this->text($row['summary'] ?? '', 320),
            'icon' => mb_substr($icon, 0, 60),
            'tags' => array_slice($tags, 0, 8),
        ];
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }
}
