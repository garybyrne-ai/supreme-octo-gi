<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Admin-editable FAQ entries. Falls back to the hard-coded ContentRepository
 * defaults until an admin edits them. Powers the FAQ page and FAQPage schema.
 */
final class FaqContentRepository extends EditableContentRepository
{
    protected function filename(): string
    {
        return 'faqs.json';
    }

    protected function normalize(array $row): ?array
    {
        $question = $this->text($row['question'] ?? '', 200);
        $answer = $this->text($row['answer'] ?? '', 1200);
        if ($question === '' || $answer === '') {
            return null;
        }

        return [
            'question' => $question,
            'answer' => $answer,
        ];
    }
}
