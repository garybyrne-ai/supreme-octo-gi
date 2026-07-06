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
            'schema' => $this->articleSchema($post),
        ]);
    }

    /**
     * Rich BlogPosting + FAQPage schema for stronger search + AI visibility.
     *
     * @param array<string, mixed> $post
     */
    private function articleSchema(array $post): string
    {
        $base = rtrim((string) ($this->config['url'] ?? ''), '/');
        $url = $base . '/blog/' . $post['slug'];

        $graph = [
            [
                '@type' => 'BlogPosting',
                '@id' => $url . '#article',
                'headline' => $post['title'],
                'description' => $post['meta_description'] ?? $post['excerpt'],
                'datePublished' => $post['published_at'] ?? date('Y-m-d'),
                'dateModified' => $post['updated_at'] ?? ($post['published_at'] ?? date('Y-m-d')),
                'inLanguage' => 'en',
                'articleSection' => $post['category'] ?? 'Guides',
                'wordCount' => $post['word_count'] ?? null,
                'author' => ['@type' => 'Organization', 'name' => 'Crest Web Media', 'url' => $base],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'Crest Web Media',
                    'logo' => ['@type' => 'ImageObject', 'url' => $base . '/assets/images/crest-web-media-logo.webp'],
                ],
                'image' => $base . '/assets/images/crest-web-media-logo.webp',
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
                'keywords' => implode(', ', array_merge([$post['focus_keyword'] ?? 'web development'], $post['secondary_keywords'] ?? [])),
            ],
        ];

        if (!empty($post['faq']) && is_array($post['faq'])) {
            $questions = [];
            foreach ($post['faq'] as $faq) {
                $questions[] = [
                    '@type' => 'Question',
                    'name' => $faq['question'] ?? '',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer'] ?? ''],
                ];
            }
            $graph[] = ['@type' => 'FAQPage', '@id' => $url . '#faq', 'mainEntity' => $questions];
        }

        return (string) json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
