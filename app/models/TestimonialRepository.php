<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Admin-editable testimonials. Falls back to the hard-coded ContentRepository
 * defaults until an admin edits them.
 */
final class TestimonialRepository extends EditableContentRepository
{
    protected function filename(): string
    {
        return 'testimonials.json';
    }

    protected function normalize(array $row): ?array
    {
        $name = $this->text($row['name'] ?? '', 80);
        $quote = $this->text($row['quote'] ?? '', 700);
        if ($name === '' || $quote === '') {
            return null;
        }

        $flag = $this->text($row['flag'] ?? '', 8);
        if ($flag === '') {
            $flag = 'GL';
        }

        return [
            'name' => $name,
            'role' => $this->text($row['role'] ?? '', 90),
            'country' => $this->text($row['country'] ?? '', 40) ?: 'Global',
            'flag' => strtoupper($flag),
            'quote' => $quote,
        ];
    }
}
