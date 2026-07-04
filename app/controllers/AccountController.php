<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\MembershipPlanRepository;
use App\Models\MemberRepository;
use App\Models\MonitorRepository;
use App\Models\NewsletterOfferRepository;
use App\Models\SavedReportRepository;
use App\Models\ToolLeadRepository;
use App\Models\ToolUsageRepository;
use App\Services\AuditLogger;
use App\Services\LeadMailer;

final class AccountController extends Controller
{
    public function dashboard(): void
    {
        Security::ensureSession();
        $member = $_SESSION['member'] ?? null;
        if (!is_array($member) || empty($member['email'])) {
            $this->redirect('/');
        }

        // Refresh membership from storage — payment webhooks update the member file.
        $fresh = (new MemberRepository())->findByEmail((string) $member['email']);
        if (is_array($fresh)) {
            $member['membership'] = $fresh['membership'] ?? ($member['membership'] ?? null);
            $member['forum_verified'] = (bool) ($fresh['forum_verified'] ?? false);
            $_SESSION['member'] = $member;
        }

        $isPro = MemberRepository::isPro($member);
        $email = (string) $member['email'];

        $this->render('pages/account-dashboard', [
            'title' => 'Your Dashboard | Crest Web Media Growth Lab',
            'metaDescription' => 'Your Growth Lab dashboard: tools, membership status, free scans and saved reports.',
            'member' => $member,
            'isPro' => $isPro,
            'scanUsage' => (new ToolUsageRepository())->status($email),
            'savedReports' => $isPro ? (new SavedReportRepository())->forEmail($email) : [],
            'monitors' => $isPro ? (new MonitorRepository())->forEmail($email) : [],
            'monitorTypes' => MonitorRepository::TYPES,
            'plans' => (new MembershipPlanRepository())->activePlans(),
            'planRepo' => new MembershipPlanRepository(),
            'csrf' => Security::csrfToken(),
            'notice' => $_SESSION['account_notice'] ?? null,
            'error' => $_SESSION['account_error'] ?? null,
        ]);
        unset($_SESSION['account_notice'], $_SESSION['account_error']);
    }

    public function addMonitor(): void
    {
        Security::ensureSession();
        $member = $_SESSION['member'] ?? null;
        if (!is_array($member) || empty($member['email'])) {
            $this->redirect('/');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['account_error'] = 'Session token expired. Please try again.';
            $this->redirect('/account/dashboard');
        }

        if (!MemberRepository::isPro($member)) {
            $_SESSION['account_error'] = 'Scheduled monitoring is a Growth Lab Pro feature.';
            $this->redirect('/tools-pricing');
        }

        try {
            (new MonitorRepository())->add(
                (string) $member['email'],
                (string) ($_POST['type'] ?? ''),
                (string) ($_POST['target'] ?? '')
            );
            $_SESSION['account_notice'] = 'Monitor added. We will re-check it weekly and email you if anything regresses.';
            (new AuditLogger())->log('account.monitor.added', ['email' => $member['email'], 'type' => $_POST['type'] ?? '']);
        } catch (\Throwable $exception) {
            $_SESSION['account_error'] = $exception->getMessage();
        }

        $this->redirect('/account/dashboard');
    }

    public function deleteMonitor(): void
    {
        Security::ensureSession();
        $member = $_SESSION['member'] ?? null;
        if (!is_array($member) || empty($member['email'])) {
            $this->redirect('/');
        }

        if (Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            (new MonitorRepository())->delete((string) $member['email'], (string) ($_POST['id'] ?? ''));
            $_SESSION['account_notice'] = 'Monitor removed.';
        }

        $this->redirect('/account/dashboard');
    }

    public function saveReport(): void
    {
        Security::ensureSession();
        $member = $_SESSION['member'] ?? null;
        if (!is_array($member) || empty($member['email'])) {
            $this->redirect('/');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['account_error'] = 'Session token expired. Please try again.';
            $this->redirect('/account/dashboard');
        }

        if (!MemberRepository::isPro($member)) {
            $_SESSION['account_error'] = 'Saving reports is a Growth Lab Pro feature. Free accounts can view and download reports.';
            $this->redirect('/tools-pricing');
        }

        try {
            (new SavedReportRepository())->save((string) $member['email'], [
                'tool' => $_POST['tool'] ?? 'Report',
                'target' => $_POST['target'] ?? '',
                'score' => $_POST['score'] ?? 0,
                'grade' => $_POST['grade'] ?? '',
                'summary' => $_POST['summary'] ?? '',
            ]);
            $_SESSION['account_notice'] = 'Report saved. Your last three reports are available on your dashboard.';
            (new AuditLogger())->log('account.report.saved', ['email' => $member['email'], 'tool' => $_POST['tool'] ?? '']);
        } catch (\Throwable $exception) {
            $_SESSION['account_error'] = $exception->getMessage();
        }

        $this->redirect('/account/dashboard');
    }

    public function deleteReport(): void
    {
        Security::ensureSession();
        $member = $_SESSION['member'] ?? null;
        if (!is_array($member) || empty($member['email'])) {
            $this->redirect('/');
        }

        if (Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            (new SavedReportRepository())->delete((string) $member['email'], (string) ($_POST['id'] ?? ''));
            $_SESSION['account_notice'] = 'Report removed.';
        }

        $this->redirect('/account/dashboard');
    }

    public function logout(): void
    {
        Security::ensureSession();
        unset($_SESSION['member']);
        $this->redirect('/');
    }

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
        $_SESSION['tool_lead'] = ['name' => $member['name'] ?? '', 'email' => $member['email'], 'source' => 'account-login', 'verified_at' => time()];
        (new AuditLogger())->log('account.login.success', ['email' => $member['email']]);
        $this->json(['ok' => true, 'message' => 'You are signed in. Redirecting to your dashboard…', 'redirect' => '/account/dashboard']);
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

        $this->json(['ok' => true, 'message' => 'Account created. Redirecting to your dashboard…', 'redirect' => '/account/dashboard']);
    }

    private function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
        exit;
    }
}
