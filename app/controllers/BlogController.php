<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ContentRepository;

final class BlogController extends Controller
{
    public function index(): void
    {
        $content = new ContentRepository();

        $this->render('blog/index', [
            'title' => 'Blog | Crest Web Media',
            'metaDescription' => 'Insights on PHP, Laravel, TailwindCSS, SEO, WordPress, Squarespace, Joomla, AI development, security and performance.',
            'posts' => $content->posts(),
        ]);
    }

    public function show(string $slug): void
    {
        $content = new ContentRepository();
        $post = $content->postBySlug($slug);

        if (!$post) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Post Not Found']);
            return;
        }

        $this->render('blog/show', [
            'title' => $post['title'] . ' | Crest Web Media',
            'metaDescription' => $post['meta_description'] ?? $post['excerpt'],
            'post' => $post,
            'posts' => $content->posts(),
            'schema' => json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $post['title'],
                'description' => $post['meta_description'] ?? $post['excerpt'],
                'datePublished' => $post['published_at'] ?? date('Y-m-d'),
                'dateModified' => $post['updated_at'] ?? date('Y-m-d'),
                'author' => [
                    '@type' => 'Organization',
                    'name' => 'Crest Web Media',
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'Crest Web Media',
                ],
                'mainEntityOfPage' => '/blog/' . $post['slug'],
                'keywords' => array_merge([$post['focus_keyword'] ?? 'web development'], $post['secondary_keywords'] ?? []),
                'wordCount' => $post['word_count'] ?? null,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    }
}
