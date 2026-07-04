<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\MemberRepository;
use App\Models\NewsletterOfferRepository;
use App\Models\ToolLeadRepository;
use App\Services\AuditLogger;
use App\Services\LeadMailer;

final class AccountController extends Controller
{
    public function forms(): void
    {
        Security::ensureSession();
        $this->json([
            'csrf' => Security::csrfToken(),
            'loginCaptcha' => Security::refreshCaptcha('account_login')['question'] ?? '',
            'registerCaptcha' => Security::refreshCaptcha('account_register')['question'] ?? '',
        ]);
    }

    public function login(): void
    {
        if (Security::hitRateLimit('account_login', 8, 900)) {
            $this->json(['ok' => false, 'message' => 'Too many login attempts. Please wait a few minutes.'], 429);
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null) || !Security::verifyCaptcha('account_login', $_POST['captcha'] ?? null)) {
            $this->json(['ok' => false, 'message' => 'Security check failed. Refresh the form and try again.'], 422);
        }

        $member = (new MemberRepository())->verify((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''));
        if ($member === null) {
            (new AuditLogger())->log('account.login.failed', ['email' => $_POST['email'] ?? '']);
            $this->json(['ok' => false, 'message' => 'Invalid email or password.'], 401);
        }

        $_SESSION['member'] = $member + ['logged_in_at' => time()];
        (new AuditLogger())->log('account.login.success', ['email' => $member['email']]);
        $this->json(['ok' => true, 'message' => 'You are signed in. Tools and client features are ready for this browser.']);
    }

    public function register(): void
    {
        if (Security::hitRateLimit('account_register', 5, 900)) {
            $this->json(['ok' => false, 'message' => 'Too many registration attempts. Please wait a few minutes.'], 429);
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null) || !Security::verifyCaptcha('account_register', $_POST['captcha'] ?? null)) {
            $this->json(['ok' => false, 'message' => 'Security check failed. Refresh the form and try again.'], 422);
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
            $this->json(['ok' => false, 'message' => 'Use a valid name, email and a password of at least 10 characters.'], 422);
        }

        try {
            $member = (new MemberRepository())->create($name, $email, $password);
        } catch (\Throwable $exception) {
            $this->json(['ok' => false, 'message' => $exception->getMessage()], 422);
        }

        $_SESSION['member'] = [
            'id' => $member['id'],
            'name' => $member['name'],
            'email' => $member['email'],
            'forum_verified' => (bool) ($member['forum_verified'] ?? false),
            'logged_in_at' => time(),
        ];
        $_SESSION['tool_lead'] = ['name' => $name, 'email' => $email, 'source' => 'account-register', 'verified_at' => time()];

        (new ToolLeadRepository())->store(['name' => $name, 'email' => $email, 'source' => 'account-register']);
        (new LeadMailer())->sendOffer($email, $name, (new NewsletterOfferRepository())->current());
        (new AuditLogger())->log('account.registered', ['email' => $email]);

        $this->json(['ok' => true, 'message' => 'Account created. Tool results are unlocked. Forum posting will be available after admin approval.']);
    }

    private function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
        exit;
    }
}
