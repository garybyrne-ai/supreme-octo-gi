<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ContentRepository;
use App\Models\SiteContentRepository;
use App\Services\SeoService;

final class HomeController extends Controller
{
    public function index(): void
    {
        $content = new ContentRepository();
        $homepageServices = array_values(array_filter(
            $content->services(),
            static fn (array $service): bool => !preg_match('/-(ireland|dublin|uk|usa|europe)$/', (string) $service['slug'])
        ));

        $this->render('home', [
            'title' => 'Crest Web Media | Websites, Apps, SEO And AI Workflow Systems',
            'metaDescription' => 'Crest Web Media builds enterprise-style websites, PHP platforms, apps, SEO systems, API integrations and AI workflows for clients in Ireland, the UK, the USA and Europe.',
            'hero' => (new SiteContentRepository())->hero(),
            'contact' => $content->contact(),
            'stats' => $content->stats(),
            'services' => $homepageServices,
            'why' => $content->why(),
            'technologies' => $content->technologies(),
            'process' => $content->process(),
            'portfolio' => $content->portfolio(),
            'testimonials' => $content->testimonials(),
            'faqs' => $content->faqs(),
            'posts' => array_slice($content->posts(), 0, 3),
            'schema' => SeoService::organizationSchema($this->config) . "\n" . SeoService::faqSchema($content->faqs()),
        ]);
    }
}
