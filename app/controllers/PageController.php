<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\ContentRepository;
use App\Models\ToolLeadRepository;
use App\Services\AuditLogger;
use App\Services\SeoService;

final class PageController extends Controller
{
    private ContentRepository $content;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->content = new ContentRepository();
    }

    public function about(): void
    {
        $this->simplePage('About Crest Web Media', 'A remote web and app studio building cool, fast, secure digital systems for ambitious clients worldwide.', 'about');
    }

    public function services(): void
    {
        $this->render('pages/services', [
            'title' => 'Services | Crest Web Media',
            'metaDescription' => 'Web development, app development, SEO, penetration testing, performance optimization and CMS services.',
            'services' => $this->content->services(),
        ]);
    }

    public function service(string $slug): void
    {
        $service = $this->content->serviceBySlug($slug);
        if (!$service) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Service Not Found']);
            return;
        }

        $this->render('services/show', [
            'title' => $service['title'] . ' | Crest Web Media',
            'metaDescription' => $service['seo_description'] ?? $service['summary'],
            'service' => $service,
            'services' => $this->content->services(),
            'schema' => SeoService::faqSchema($service['faq']),
        ]);
    }

    public function portfolio(): void
    {
        $this->render('pages/portfolio', [
            'title' => 'Portfolio | Crest Web Media',
            'metaDescription' => 'Featured websites, web applications, e-commerce stores and digital products.',
            'portfolio' => $this->content->portfolio(),
        ]);
    }

    public function caseStudies(): void
    {
        $this->portfolio();
    }

    public function process(): void
    {
        $this->render('pages/process', [
            'title' => 'Process | Crest Web Media',
            'metaDescription' => 'A structured digital process from discovery to deployment and support.',
            'process' => $this->content->process(),
        ]);
    }

    public function technologies(): void
    {
        $this->render('pages/technologies', [
            'title' => 'Technologies | Crest Web Media',
            'metaDescription' => 'PHP, Laravel, MySQL, TailwindCSS, WordPress, Joomla, Squarespace, Kali Linux, FreeBSD and cloud tooling.',
            'technologies' => $this->content->technologies(),
        ]);
    }

    public function testimonials(): void
    {
        $this->render('pages/testimonials', [
            'title' => 'Testimonials | Crest Web Media',
            'metaDescription' => 'Client feedback for Crest Web Media websites, applications, SEO and security services.',
            'testimonials' => $this->content->testimonials(),
        ]);
    }

    public function pricing(): void
    {
        $this->render('pages/pricing', [
            'title' => 'Pricing | Crest Web Media',
            'metaDescription' => 'Project pricing for digital agency websites, CMS, web apps and growth platforms.',
            'pricing' => $this->content->pricing(),
        ]);
    }

    public function faq(): void
    {
        $faqs = $this->content->faqs();
        $this->render('pages/faq', [
            'title' => 'FAQ | Crest Web Media',
            'metaDescription' => 'Frequently asked questions about Crest Web Media projects, security, performance and support.',
            'faqs' => $faqs,
            'schema' => SeoService::faqSchema($faqs),
        ]);
    }

    public function contact(): void
    {
        $this->render('pages/contact', [
            'title' => 'Contact | Crest Web Media',
            'metaDescription' => 'Start a project with Crest Web Media.',
            'csrf' => Security::csrfToken(),
            'captcha' => Security::captchaChallenge('contact'),
            'contact' => $this->content->contact(),
        ]);
    }

    public function sendContact(): void
    {
        if (Security::hitRateLimit('contact_form', 6, 900)) {
            http_response_code(429);
            (new AuditLogger())->log('contact.rate_limited');
            $this->render('pages/contact', [
                'title' => 'Contact | Crest Web Media',
                'metaDescription' => 'Start a project with Crest Web Media.',
                'csrf' => Security::csrfToken(),
                'captcha' => Security::refreshCaptcha('contact'),
                'contact' => $this->content->contact(),
                'error' => 'Too many form attempts. Please wait a few minutes and try again.',
            ]);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            (new AuditLogger())->log('contact.csrf_failed');
            $this->render('pages/contact', [
                'title' => 'Contact | Crest Web Media',
                'metaDescription' => 'Start a project with Crest Web Media.',
                'csrf' => Security::csrfToken(),
                'captcha' => Security::refreshCaptcha('contact'),
                'contact' => $this->content->contact(),
                'error' => 'Your secure form token expired. Please try again.',
            ]);
            return;
        }

        if (!Security::verifyCaptcha('contact', $_POST['captcha'] ?? null)) {
            http_response_code(422);
            (new AuditLogger())->log('contact.captcha_failed');
            $this->render('pages/contact', [
                'title' => 'Contact | Crest Web Media',
                'metaDescription' => 'Start a project with Crest Web Media.',
                'csrf' => Security::csrfToken(),
                'captcha' => Security::refreshCaptcha('contact'),
                'contact' => $this->content->contact(),
                'error' => 'Security check failed. Please solve the new captcha.',
            ]);
            return;
        }

        $payload = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'service' => trim((string) ($_POST['service'] ?? '')),
            'message' => trim((string) ($_POST['message'] ?? '')),
        ];

        if (strlen($payload['name']) < 2 || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL) || strlen($payload['message']) < 20) {
            http_response_code(422);
            $this->render('pages/contact', [
                'title' => 'Contact | Crest Web Media',
                'metaDescription' => 'Start a project with Crest Web Media.',
                'csrf' => Security::csrfToken(),
                'captcha' => Security::refreshCaptcha('contact'),
                'contact' => $this->content->contact(),
                'error' => 'Please enter a valid name, email and a message of at least 20 characters.',
            ]);
            return;
        }

        (new AuditLogger())->log('contact.submitted', $payload);

        $this->render('pages/contact', [
            'title' => 'Contact | Crest Web Media',
            'metaDescription' => 'Start a project with Crest Web Media.',
            'csrf' => Security::csrfToken(),
            'captcha' => Security::refreshCaptcha('contact'),
            'contact' => $this->content->contact(),
            'success' => 'Message captured locally. Wire PHPMailer credentials in production to send it.',
        ]);
    }

    public function chatLead(): void
    {
        header('Content-Type: application/json');

        if (Security::hitRateLimit('chatbot_lead', 10, 900)) {
            http_response_code(429);
            echo json_encode(['ok' => false, 'message' => 'Too many chatbot requests. Please try again shortly.']);
            return;
        }

        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $name = trim((string) ($payload['name'] ?? 'Website visitor'));
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $interest = trim((string) ($payload['interest'] ?? 'Chatbot service enquiry'));
        $message = trim((string) ($payload['message'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Please enter a valid email so we can follow up.']);
            return;
        }

        (new ToolLeadRepository())->store([
            'name' => strlen($name) >= 2 ? $name : 'Website visitor',
            'email' => $email,
            'source' => 'chatbot: ' . substr($interest, 0, 70),
        ]);
        (new AuditLogger())->log('chatbot.lead_captured', [
            'email' => $email,
            'interest' => $interest,
            'message' => substr($message, 0, 500),
        ]);

        echo json_encode(['ok' => true, 'message' => 'Got it. Your details are saved and Crest Web Media can follow up with the right service plan.']);
    }

    public function techNews(): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: public, max-age=300');

        echo json_encode([
            'items' => (new \App\Services\TechNewsService())->items(8, true),
        ]);
    }

    public function legal(): void
    {
        $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        $titles = [
            'privacy-policy' => 'Privacy Policy',
            'cookie-policy' => 'Cookie Policy',
            'terms-and-conditions' => 'Terms and Conditions',
        ];

        $this->simplePage($titles[$path] ?? 'Legal', 'Legal information for Crest Web Media.', 'legal');
    }

    public function search(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $pool = array_merge($this->content->services(), $this->content->posts());
        $results = array_values(array_filter($pool, static function (array $item) use ($query): bool {
            return $query !== '' && str_contains(strtolower(json_encode($item)), strtolower($query));
        }));

        $this->render('pages/search', [
            'title' => 'Search Results | Crest Web Media',
            'metaDescription' => 'Search Crest Web Media services, blog posts and resources.',
            'query' => $query,
            'results' => $results,
        ]);
    }

    private function simplePage(string $title, string $meta, string $variant): void
    {
        $this->render('pages/simple', [
            'title' => $title . ' | Crest Web Media',
            'metaDescription' => $meta,
            'pageTitle' => $title,
            'variant' => $variant,
            'services' => $this->content->services(),
            'technologies' => $this->content->technologies(),
            'contact' => $this->content->contact(),
        ]);
    }
}
