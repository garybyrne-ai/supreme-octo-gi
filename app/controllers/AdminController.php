<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Security;
use App\Models\AdminModuleDraftRepository;
use App\Models\BacklinkPlanRepository;
use App\Models\CommerceRepository;
use App\Models\ContentRepository;
use App\Models\SiteContentRepository;
use App\Models\MailSettingsRepository;
use App\Models\MemberRepository;
use App\Models\MembershipPlanRepository;
use App\Models\NewsletterOfferRepository;
use App\Models\PayPalSettingsRepository;
use App\Models\SupportTicketRepository;
use App\Models\ToolLeadRepository;
use App\Services\AuditLogger;
use App\Services\LeadMailer;
use App\Services\MediaLibrary;

final class AdminController extends Controller
{
    public function login(): void
    {
        $this->render('admin/login', [
            'title' => 'Admin Login | Crest Web Media',
            'metaDescription' => 'Secure admin login.',
            'csrf' => Security::csrfToken(),
            'captcha' => Security::captchaChallenge('admin_login'),
        ]);
    }

    public function authenticate(): void
    {
        $logger = new AuditLogger();
        $email = trim((string) ($_POST['email'] ?? ''));

        if (Security::hitRateLimit('admin_login', 5, 900)) {
            $logger->log('admin.login.rate_limited', ['email' => $email]);
            http_response_code(429);
            $this->render('admin/login', [
                'title' => 'Admin Login | Crest Web Media',
                'metaDescription' => 'Secure admin login.',
                'csrf' => Security::csrfToken(),
                'captcha' => Security::refreshCaptcha('admin_login'),
                'error' => 'Too many login attempts. Please wait a few minutes and try again.',
            ]);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $logger->log('admin.login.csrf_failed', ['email' => $email]);
            http_response_code(419);
            $this->render('admin/login', [
                'title' => 'Admin Login | Crest Web Media',
                'metaDescription' => 'Secure admin login.',
                'csrf' => Security::csrfToken(),
                'captcha' => Security::refreshCaptcha('admin_login'),
                'error' => 'Session token expired. Please try again.',
            ]);
            return;
        }

        if (!Security::verifyCaptcha('admin_login', $_POST['captcha'] ?? null)) {
            $logger->log('admin.login.captcha_failed', ['email' => $email]);
            http_response_code(422);
            $this->render('admin/login', [
                'title' => 'Admin Login | Crest Web Media',
                'metaDescription' => 'Secure admin login.',
                'csrf' => Security::csrfToken(),
                'captcha' => Security::refreshCaptcha('admin_login'),
                'error' => 'Security check failed. Please solve the new captcha.',
            ]);
            return;
        }

        $password = (string) ($_POST['password'] ?? '');

        if ($this->validCredentials($email, $password)) {
            session_regenerate_id(true);
            $_SESSION['admin'] = ['email' => $email, 'logged_in_at' => time()];
            $logger->log('admin.login.success', ['email' => $email]);
            $this->redirect('/admin/dashboard');
        }

        $logger->log('admin.login.failed', ['email' => $email]);
        http_response_code(401);
        $this->render('admin/login', [
            'title' => 'Admin Login | Crest Web Media',
            'metaDescription' => 'Secure admin login.',
            'csrf' => Security::csrfToken(),
            'captcha' => Security::refreshCaptcha('admin_login'),
            'error' => 'Invalid admin credentials.',
        ]);
    }

    private function validCredentials(string $email, string $password): bool
    {
        if (is_file(base_path('config/installed.php'))) {
            $pdo = Database::connection();
            $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, (string) $user['password_hash'])) {
                $this->recordLoginAttempt($email, false);
                return false;
            }

            $update = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
            $update->execute(['id' => $user['id']]);
            $this->recordLoginAttempt($email, true);
            return true;
        }

        $expectedHash = password_hash('ChangeThisBeforeProduction!', PASSWORD_DEFAULT);
        return $email === 'admin@crestwebmedia.com' && password_verify($password, $expectedHash);
    }

    private function recordLoginAttempt(string $email, bool $success): void
    {
        try {
            $fingerprint = Security::deviceFingerprint();
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'INSERT INTO login_attempts (email, success, user_agent, device_hash, metadata)
                 VALUES (:email, :success, :user_agent, :device_hash, :metadata)'
            );
            $stmt->execute([
                'email' => $email,
                'success' => $success ? 1 : 0,
                'user_agent' => $fingerprint['user_agent'],
                'device_hash' => $fingerprint['device_hash'],
                'metadata' => json_encode($fingerprint),
            ]);
        } catch (\Throwable) {
            (new AuditLogger())->log('admin.login_attempt.database_log_failed', ['email' => $email, 'success' => $success]);
        }
    }

    public function dashboard(): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        $content = new ContentRepository();
        $modules = [
            ['slug' => 'blog', 'title' => 'Blog Manager', 'icon' => 'fa-newspaper', 'summary' => 'Draft, optimize and publish insight articles with SEO metadata.'],
            ['slug' => 'portfolio', 'title' => 'Portfolio Manager', 'icon' => 'fa-layer-group', 'summary' => 'Manage case studies, project cards, categories and visual proof.'],
            ['slug' => 'services', 'title' => 'Services Manager', 'icon' => 'fa-screwdriver-wrench', 'summary' => 'Tune service pages, FAQs, benefits, tags and conversion copy.'],
            ['slug' => 'testimonials', 'title' => 'Testimonials', 'icon' => 'fa-comment-dots', 'summary' => 'Curate client quotes, proof points and trust-building snippets.'],
            ['slug' => 'tickets', 'title' => 'Support Tickets', 'icon' => 'fa-ticket', 'summary' => 'Review customer support tickets, priorities and request references.'],
            ['slug' => 'content', 'title' => 'Site Content', 'icon' => 'fa-pen-ruler', 'summary' => 'Edit the home hero, contact / DIRECT SIGNAL block, backlink plans and page intro headings — no code needed.'],
            ['slug' => 'newsletter-offer', 'title' => 'Newsletter Offer', 'icon' => 'fa-envelope-open-text', 'summary' => 'Edit the automated SEO, PPC, website and app development offer sent to tool leads.'],
            ['slug' => 'mail-settings', 'title' => 'Mail Settings', 'icon' => 'fa-paper-plane', 'summary' => 'Choose PHP mail or Gmail SMTP for tool sign-in codes and automated offer emails.'],
            ['slug' => 'paypal-settings', 'title' => 'Payment Settings', 'icon' => 'fa-credit-card', 'summary' => 'Configure PayPal links, Stripe checkout keys and Growth Lab Pass subscriptions.'],
            ['slug' => 'commerce', 'title' => 'Commerce Engine', 'icon' => 'fa-cart-shopping', 'summary' => 'Sell themes, plugins, templates and services marketplace-style with private ZIP packages, licenses, orders and download grants.'],
            ['slug' => 'membership', 'title' => 'Membership Plans', 'icon' => 'fa-id-card', 'summary' => 'Control every membership tier: price, billing interval, trial, Stripe and PayPal wiring, features and availability.'],
            ['slug' => 'faq', 'title' => 'FAQ Manager', 'icon' => 'fa-circle-question', 'summary' => 'Edit answers for common sales, delivery and support questions.'],
            ['slug' => 'seo', 'title' => 'SEO Center', 'icon' => 'fa-chart-line', 'summary' => 'Review titles, descriptions, schema signals and crawl priorities.'],
            ['slug' => 'redirects', 'title' => 'Redirect Manager', 'icon' => 'fa-route', 'summary' => 'Plan redirects, campaign URLs and migration-safe route changes.'],
            ['slug' => 'media', 'title' => 'Media Library', 'icon' => 'fa-images', 'summary' => 'Organize images, icons, downloads and optimized web assets.'],
            ['slug' => 'forum-members', 'title' => 'Forum Members', 'icon' => 'fa-comments', 'summary' => 'Approve registered members before they can post in the tech forum.'],
            ['slug' => 'analytics', 'title' => 'Analytics', 'icon' => 'fa-wave-square', 'summary' => 'Track traffic, conversion events and technical performance signals.'],
            ['slug' => 'activity', 'title' => 'Activity Logs', 'icon' => 'fa-clock-rotate-left', 'summary' => 'Audit logins, content changes, contact events and system actions.'],
            ['slug' => 'backups', 'title' => 'Backups', 'icon' => 'fa-database', 'summary' => 'Monitor backup status, export data and protect recovery points.'],
            ['slug' => 'theme', 'title' => 'Theme Settings', 'icon' => 'fa-palette', 'summary' => 'Control brand colors, hero text, footer signals and interface polish.'],
        ];

        $this->render('admin/dashboard', [
            'title' => 'CMS Dashboard | Crest Web Media',
            'metaDescription' => 'Crest Web Media CMS dashboard.',
            'stats' => [
                'Services' => count($content->services()),
                'Blog Posts' => count($content->posts()),
                'Portfolio Items' => count($content->portfolio()),
                'Testimonials' => count($content->testimonials()),
                'Open Tickets' => count((new SupportTicketRepository())->recent(50)),
            ],
            'modules' => $modules,
            'tickets' => (new SupportTicketRepository())->recent(5),
            'notice' => $_SESSION['admin_notice'] ?? null,
            'error' => $_SESSION['admin_error'] ?? null,
            'csrf' => Security::csrfToken(),
        ]);
        unset($_SESSION['admin_notice'], $_SESSION['admin_error']);
    }

    public function module(string $slug): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        $modules = [
            'blog' => ['title' => 'Blog Manager', 'icon' => 'fa-newspaper', 'actions' => ['Create SEO article', 'Edit post metadata', 'Review categories']],
            'portfolio' => ['title' => 'Portfolio Manager', 'icon' => 'fa-layer-group', 'actions' => ['Add project', 'Update case study', 'Tune category filters']],
            'services' => ['title' => 'Services Manager', 'icon' => 'fa-screwdriver-wrench', 'actions' => ['Edit service copy', 'Manage FAQs', 'Update deliverables']],
            'testimonials' => ['title' => 'Testimonials', 'icon' => 'fa-comment-dots', 'actions' => ['Add quote', 'Update client role', 'Feature proof card']],
            'tickets' => ['title' => 'Support Tickets', 'icon' => 'fa-ticket', 'actions' => ['Review new tickets', 'Assign priority', 'Reply to customer']],
            'content' => ['title' => 'Site Content', 'icon' => 'fa-pen-ruler', 'actions' => ['Edit home hero', 'Edit contact block', 'Manage backlink plans']],
            'newsletter-offer' => ['title' => 'Newsletter Offer', 'icon' => 'fa-envelope-open-text', 'actions' => ['Edit offer copy', 'Review tool leads', 'Update CTA']],
            'mail-settings' => ['title' => 'Mail Settings', 'icon' => 'fa-paper-plane', 'actions' => ['Choose mail driver', 'Configure Gmail SMTP', 'Review delivery logs']],
            'paypal-settings' => ['title' => 'Payment Settings', 'icon' => 'fa-credit-card', 'actions' => ['Set gateway keys', 'Add checkout links', 'Configure Growth Lab Pass']],
            'commerce' => ['title' => 'Commerce Engine', 'icon' => 'fa-cart-shopping', 'actions' => ['Create marketplace item', 'Attach private ZIP package', 'Review order lifecycle']],
            'membership' => ['title' => 'Membership Plans', 'icon' => 'fa-id-card', 'actions' => ['Create plan', 'Edit pricing and gateways', 'Pause or feature a plan']],
            'faq' => ['title' => 'FAQ Manager', 'icon' => 'fa-circle-question', 'actions' => ['Add answer', 'Update schema FAQ', 'Review sales objections']],
            'seo' => ['title' => 'SEO Center', 'icon' => 'fa-chart-line', 'actions' => ['Audit metadata', 'Preview schema', 'Map internal links']],
            'redirects' => ['title' => 'Redirect Manager', 'icon' => 'fa-route', 'actions' => ['Add redirect', 'Import route map', 'Test status codes']],
            'media' => ['title' => 'Media Library', 'icon' => 'fa-images', 'actions' => ['Upload asset', 'Optimize image', 'Attach alt text']],
            'forum-members' => ['title' => 'Forum Members', 'icon' => 'fa-comments', 'actions' => ['Review members', 'Approve posting', 'Revoke posting']],
            'analytics' => ['title' => 'Analytics', 'icon' => 'fa-wave-square', 'actions' => ['Review visits', 'Check conversions', 'Inspect speed metrics']],
            'activity' => ['title' => 'Activity Logs', 'icon' => 'fa-clock-rotate-left', 'actions' => ['View login events', 'Filter system logs', 'Export audit trail']],
            'backups' => ['title' => 'Backups', 'icon' => 'fa-database', 'actions' => ['Run backup', 'Download export', 'Verify restore point']],
            'theme' => ['title' => 'Theme Settings', 'icon' => 'fa-palette', 'actions' => ['Upload brand asset', 'Compile typography map', 'Approve script injection']],
        ];

        if (!isset($modules[$slug])) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Module Not Found']);
            return;
        }

        $this->render('admin/module', [
            'title' => $modules[$slug]['title'] . ' | CMS Dashboard',
            'metaDescription' => 'Crest Web Media CMS module.',
            'module' => $modules[$slug],
            'moduleSlug' => $slug,
            'modules' => $modules,
            'tickets' => $slug === 'tickets' ? (new SupportTicketRepository())->recent(20) : [],
            'toolLeads' => $slug === 'newsletter-offer' ? (new ToolLeadRepository())->recent(30) : [],
            'mediaItems' => $slug === 'media' ? (new MediaLibrary())->items(120) : [],
            'forumMembers' => $slug === 'forum-members' ? (new MemberRepository())->recent(120) : [],
            'membershipPlans' => $slug === 'membership' ? (new MembershipPlanRepository())->all() : [],
            'siteContent' => $slug === 'content' ? (new SiteContentRepository())->all() : [],
            'backlinkPlans' => $slug === 'content' ? (new BacklinkPlanRepository())->all() : [],
            'pageIntroDefs' => $slug === 'content' ? (new SiteContentRepository())->pageIntroDefinitions() : [],
            'catalogProducts' => $slug === 'commerce' ? (new CommerceRepository())->allProducts() : [],
            'intervals' => MembershipPlanRepository::INTERVALS,
            'catalogCategories' => CommerceRepository::CATALOG_CATEGORIES,
            'serviceDeliveries' => CommerceRepository::SERVICE_DELIVERIES,
            'newsletterOffer' => (new NewsletterOfferRepository())->current(),
            'mailSettings' => (new MailSettingsRepository())->current(),
            'paypalSettings' => (new PayPalSettingsRepository())->current(),
            'moduleDrafts' => (new AdminModuleDraftRepository())->byModule($slug),
            'notice' => $_SESSION['admin_notice'] ?? null,
            'error' => $_SESSION['admin_error'] ?? null,
            'csrf' => Security::csrfToken(),
        ]);
        unset($_SESSION['admin_notice'], $_SESSION['admin_error']);
    }

    public function saveModuleDraft(string $slug): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['admin_error'] = 'Module action token expired. Please try again.';
            $this->redirect('/admin/modules/' . $slug);
        }

        try {
            $_POST['module'] = $slug;
            (new AdminModuleDraftRepository())->save($_POST);
            $_SESSION['admin_notice'] = 'Module action saved. This draft is now stored in the backend action log.';
            (new AuditLogger())->log('admin.module_action.saved', ['module' => $slug, 'action' => $_POST['action'] ?? '']);
        } catch (\Throwable $exception) {
            $_SESSION['admin_error'] = $exception->getMessage();
        }

        $this->redirect('/admin/modules/' . $slug);
    }

    public function updateSiteContent(string $section): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['admin_error'] = 'Site content token expired. Please try again.';
            $this->redirect('/admin/modules/content');
        }

        try {
            $repo = new SiteContentRepository();
            match ($section) {
                'hero' => $repo->saveHero($_POST),
                'contact' => $repo->saveContact($_POST),
                'page-intros' => $repo->savePageIntros($_POST),
                default => throw new \RuntimeException('Unknown content section.'),
            };
            $_SESSION['admin_notice'] = 'Site content updated.';
            (new AuditLogger())->log('admin.site_content.updated', ['section' => $section]);
        } catch (\Throwable $exception) {
            $_SESSION['admin_error'] = $exception->getMessage();
        }

        $this->redirect('/admin/modules/content');
    }

    public function saveBacklinkPlan(): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['admin_error'] = 'Backlink plan token expired. Please try again.';
            $this->redirect('/admin/modules/content');
        }

        try {
            $slug = (new BacklinkPlanRepository())->save($_POST);
            $_SESSION['admin_notice'] = 'Backlink plan saved.';
            (new AuditLogger())->log('admin.backlink_plan.saved', ['slug' => $slug]);
        } catch (\Throwable $exception) {
            $_SESSION['admin_error'] = $exception->getMessage();
        }

        $this->redirect('/admin/modules/content');
    }

    public function deleteBacklinkPlan(): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['admin_error'] = 'Backlink plan token expired. Please try again.';
            $this->redirect('/admin/modules/content');
        }

        try {
            $slug = (string) ($_POST['slug'] ?? '');
            if (($_POST['state'] ?? '') === 'delete') {
                (new BacklinkPlanRepository())->delete($slug);
                $_SESSION['admin_notice'] = 'Backlink plan deleted.';
                (new AuditLogger())->log('admin.backlink_plan.deleted', ['slug' => $slug]);
            } else {
                $active = ($_POST['state'] ?? '') === 'activate';
                (new BacklinkPlanRepository())->setActive($slug, $active);
                $_SESSION['admin_notice'] = $active ? 'Backlink plan activated.' : 'Backlink plan paused.';
                (new AuditLogger())->log('admin.backlink_plan.toggled', ['slug' => $slug, 'active' => $active]);
            }
        } catch (\Throwable $exception) {
            $_SESSION['admin_error'] = $exception->getMessage();
        }

        $this->redirect('/admin/modules/content');
    }

    public function createTestMember(): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['admin_error'] = 'Test account token expired. Please try again.';
            $this->redirect('/admin/modules/membership');
        }

        try {
            $member = (new MemberRepository())->upsertProMember(
                (string) ($_POST['name'] ?? 'Pro Tester'),
                (string) ($_POST['email'] ?? ''),
                (string) ($_POST['password'] ?? '')
            );
            $_SESSION['admin_notice'] = 'Pro test account ready: ' . ($member['email'] ?? '') . '. Sign in at /account with the password you just set to test every tool as a paid member. Delete this account before launch.';
            (new AuditLogger())->log('admin.test_member.created', ['email' => $member['email'] ?? '']);
        } catch (\Throwable $exception) {
            $_SESSION['admin_error'] = $exception->getMessage();
        }

        $this->redirect('/admin/modules/membership');
    }

    public function updateNewsletterOffer(): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['admin_error'] = 'Newsletter offer token expired. Please try again.';
            $this->redirect('/admin/modules/newsletter-offer');
        }

        try {
            (new NewsletterOfferRepository())->save($_POST);
            $_SESSION['admin_notice'] = 'Newsletter offer updated.';
            (new AuditLogger())->log('admin.newsletter_offer.updated');
        } catch (\Throwable $exception) {
            $_SESSION['admin_error'] = $exception->getMessage();
        }

        $this->redirect('/admin/modules/newsletter-offer');
    }

    public function updateMailSettings(): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['admin_error'] = 'Mail settings token expired. Please try again.';
            $this->redirect('/admin/modules/mail-settings');
        }

        try {
            (new MailSettingsRepository())->save($_POST);
            $_SESSION['admin_notice'] = 'Mail settings updated.';
            (new AuditLogger())->log('admin.mail_settings.updated', ['driver' => $_POST['driver'] ?? '']);
        } catch (\Throwable $exception) {
            $_SESSION['admin_error'] = $exception->getMessage();
        }

        $this->redirect('/admin/modules/mail-settings');
    }

    public function updatePayPalSettings(): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['admin_error'] = 'PayPal settings token expired. Please try again.';
            $this->redirect('/admin/modules/paypal-settings');
        }

        try {
            (new PayPalSettingsRepository())->save($_POST);
            $_SESSION['admin_notice'] = 'PayPal Growth Lab settings updated.';
            (new AuditLogger())->log('admin.paypal_settings.updated', ['mode' => $_POST['mode'] ?? 'sandbox']);
        } catch (\Throwable $exception) {
            $_SESSION['admin_error'] = $exception->getMessage();
        }

        $this->redirect('/admin/modules/paypal-settings');
    }

    public function testMailSettings(): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['admin_error'] = 'Mail test token expired. Please try again.';
            $this->redirect('/admin/modules/mail-settings');
        }

        $to = strtolower(trim((string) ($_POST['test_email'] ?? '')));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['admin_error'] = 'Enter a valid test email address.';
            $this->redirect('/admin/modules/mail-settings');
        }

        $result = (new LeadMailer())->sendTest($to);
        if ($result['ok'] ?? false) {
            $_SESSION['admin_notice'] = 'Test email sent to ' . $to . '. Check inbox and spam/promotions folders.';
        } else {
            $_SESSION['admin_error'] = 'Mail test failed: ' . ($result['error'] ?? 'Unknown delivery error.');
        }

        (new AuditLogger())->log('admin.mail_settings.tested', ['to' => $to, 'ok' => (bool) ($result['ok'] ?? false)]);
        $this->redirect('/admin/modules/mail-settings');
    }

    public function uploadMedia(): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['admin_error'] = 'Media upload token expired. Please try again.';
            $this->redirect('/admin/modules/media');
        }

        try {
            $item = (new MediaLibrary())->upload($_FILES['asset'] ?? [], (string) ($_POST['alt'] ?? ''));
            $_SESSION['admin_notice'] = 'Image uploaded and converted to WebP: ' . $item['filename'];
            (new AuditLogger())->log('admin.media.uploaded', ['path' => $item['path']]);
        } catch (\Throwable $exception) {
            $_SESSION['admin_error'] = $exception->getMessage();
        }

        $this->redirect('/admin/modules/media');
    }

    public function verifyForumMember(): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['admin_error'] = 'Forum approval token expired. Please try again.';
            $this->redirect('/admin/modules/forum-members');
        }

        try {
            $verified = ($_POST['status'] ?? '') === 'approve';
            (new MemberRepository())->setForumVerified((string) ($_POST['member_id'] ?? ''), $verified);
            $_SESSION['admin_notice'] = $verified ? 'Forum member approved for posting.' : 'Forum member posting access revoked.';
            (new AuditLogger())->log('admin.forum_member.updated', ['member_id' => $_POST['member_id'] ?? '', 'verified' => $verified]);
        } catch (\Throwable $exception) {
            $_SESSION['admin_error'] = $exception->getMessage();
        }

        $this->redirect('/admin/modules/forum-members');
    }

    public function logout(): void
    {
        Security::ensureSession();

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->render('admin/login', [
                'title' => 'Admin Login | Crest Web Media',
                'metaDescription' => 'Secure admin login.',
                'csrf' => Security::csrfToken(),
                'captcha' => Security::refreshCaptcha('admin_login'),
                'error' => 'Logout token expired. Please log in again.',
            ]);
            return;
        }

        (new AuditLogger())->log('admin.logout');
        $_SESSION = [];
        session_destroy();
        $this->redirect('/admin');
    }
}
