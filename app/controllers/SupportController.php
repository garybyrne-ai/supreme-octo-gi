<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\ContentRepository;
use App\Models\SupportTicketRepository;
use App\Services\AuditLogger;

final class SupportController extends Controller
{
    public function index(array $data = []): void
    {
        $this->render('pages/support', array_replace([
            'title' => 'Support Tickets | Crest Web Media',
            'metaDescription' => 'Create a secure support ticket for Crest Web Media website, app, SEO or security help.',
            'csrf' => Security::csrfToken(),
            'captcha' => Security::captchaChallenge('support_ticket'),
            'contact' => (new ContentRepository())->contact(),
        ], $data));
    }

    public function store(): void
    {
        $logger = new AuditLogger();

        if (Security::hitRateLimit('support_ticket', 6, 900)) {
            http_response_code(429);
            $logger->log('support.rate_limited');
            $this->index(['error' => 'Too many ticket attempts. Please wait a few minutes and try again.', 'captcha' => Security::refreshCaptcha('support_ticket')]);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $logger->log('support.csrf_failed');
            $this->index(['error' => 'Your secure form token expired. Please try again.', 'captcha' => Security::refreshCaptcha('support_ticket')]);
            return;
        }

        if (!Security::verifyCaptcha('support_ticket', $_POST['captcha'] ?? null)) {
            http_response_code(422);
            $logger->log('support.captcha_failed');
            $this->index(['error' => 'Security check failed. Please solve the new captcha.', 'captcha' => Security::refreshCaptcha('support_ticket')]);
            return;
        }

        $payload = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'subject' => trim((string) ($_POST['subject'] ?? '')),
            'priority' => trim((string) ($_POST['priority'] ?? 'normal')),
            'message' => trim((string) ($_POST['message'] ?? '')),
            'ip_address' => Security::clientIp(),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        ];

        $errors = $this->validate($payload);
        if ($errors !== []) {
            http_response_code(422);
            $this->index(['error' => implode(' ', $errors), 'captcha' => Security::refreshCaptcha('support_ticket'), 'old' => $payload]);
            return;
        }

        $reference = (new SupportTicketRepository())->create($payload);
        $logger->log('support.ticket_created', ['reference' => $reference, 'email' => $payload['email'], 'priority' => $payload['priority']]);

        $this->index([
            'success' => 'Ticket created. Your reference is ' . $reference . '.',
            'reference' => $reference,
            'captcha' => Security::refreshCaptcha('support_ticket'),
        ]);
    }

    private function validate(array $payload): array
    {
        $errors = [];

        if (strlen($payload['name']) < 2 || strlen($payload['name']) > 160) {
            $errors[] = 'Enter a valid name.';
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }

        if (strlen($payload['subject']) < 4 || strlen($payload['subject']) > 180) {
            $errors[] = 'Add a clear subject.';
        }

        if (!in_array($payload['priority'], ['normal', 'high', 'urgent'], true)) {
            $errors[] = 'Choose a valid priority.';
        }

        if (strlen($payload['message']) < 20 || strlen($payload['message']) > 3000) {
            $errors[] = 'Describe the issue in 20 to 3000 characters.';
        }

        return $errors;
    }
}
