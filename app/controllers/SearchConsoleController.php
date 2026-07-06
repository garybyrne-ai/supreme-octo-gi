<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\MemberRepository;
use App\Models\SearchConsoleSettingsRepository;
use App\Models\SearchConsoleTokenRepository;
use App\Services\AuditLogger;
use App\Services\SearchConsoleService;

/**
 * Google Search Console integration for Pro members: connect via OAuth, then
 * view top queries, top pages, clicks/impressions and (via manual CSV import)
 * backlinks. Everything is gated to logged-in Growth Lab Pro members.
 */
final class SearchConsoleController extends Controller
{
    public function dashboard(): void
    {
        $member = $this->requireProMember();
        $email = (string) $member['email'];

        $settings = new SearchConsoleSettingsRepository();
        $tokens = new SearchConsoleTokenRepository();
        $service = new SearchConsoleService();

        $connected = $tokens->connected($email);
        $sites = $connected ? $service->listSites($email) : [];
        $record = $tokens->get($email) ?? [];
        $site = (string) ($record['site'] ?? ($sites[0] ?? ''));

        $data = [
            'title' => 'Search Console Insights | Growth Lab Pro',
            'metaDescription' => 'Your Google Search Console data — top queries, pages, clicks and backlinks — inside your Crest Web Media dashboard.',
            'member' => $member,
            'configured' => $settings->configured(),
            'connected' => $connected,
            'sites' => $sites,
            'site' => $site,
            'record' => $record,
            'csrf' => Security::csrfToken(),
            'notice' => $_SESSION['account_notice'] ?? null,
            'error' => $_SESSION['account_error'] ?? null,
            'totals' => ['clicks' => 0, 'impressions' => 0, 'ctr' => 0.0, 'position' => 0.0],
            'topQueries' => [],
            'topPages' => [],
            'backlinks' => is_array($record['backlinks'] ?? null) ? $record['backlinks'] : [],
        ];

        if ($connected && $site !== '') {
            $data['totals'] = $service->totals($email, $site, 28);
            $data['topQueries'] = $service->searchAnalytics($email, $site, 'query', 28, 25);
            $data['topPages'] = $service->searchAnalytics($email, $site, 'page', 28, 15);
        }

        unset($_SESSION['account_notice'], $_SESSION['account_error']);
        $this->render('pages/search-console', $data);
    }

    public function connect(): void
    {
        $member = $this->requireProMember();

        $settings = new SearchConsoleSettingsRepository();
        if (!$settings->configured()) {
            $_SESSION['account_error'] = 'Search Console is not configured yet. Please contact support.';
            $this->redirect('/account/search-console');
        }

        // CSRF state — verified in the callback.
        $state = bin2hex(random_bytes(16));
        $_SESSION['gsc_oauth_state'] = $state;

        $this->redirect((new SearchConsoleService())->authUrl($state));
    }

    public function callback(): void
    {
        $member = $this->requireProMember();
        $email = (string) $member['email'];

        $state = (string) ($_GET['state'] ?? '');
        $expected = (string) ($_SESSION['gsc_oauth_state'] ?? '');
        unset($_SESSION['gsc_oauth_state']);

        if ($state === '' || !hash_equals($expected, $state)) {
            $_SESSION['account_error'] = 'Security check failed during Google sign-in. Please try again.';
            $this->redirect('/account/search-console');
        }

        if (!empty($_GET['error']) || empty($_GET['code'])) {
            $_SESSION['account_error'] = 'Google connection was cancelled.';
            $this->redirect('/account/search-console');
        }

        $ok = (new SearchConsoleService())->exchangeCode($email, (string) $_GET['code']);
        if ($ok) {
            $_SESSION['account_notice'] = 'Google Search Console connected. Your data is loading below.';
            (new AuditLogger())->log('gsc.connected', ['email' => $email]);
        } else {
            $_SESSION['account_error'] = 'Could not complete the Google connection. Please try again.';
        }

        $this->redirect('/account/search-console');
    }

    public function selectSite(): void
    {
        $member = $this->requireProMember();
        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['account_error'] = 'Session token expired. Please try again.';
            $this->redirect('/account/search-console');
        }

        $site = trim((string) ($_POST['site'] ?? ''));
        if ($site !== '') {
            (new SearchConsoleTokenRepository())->setSite((string) $member['email'], $site);
            $_SESSION['account_notice'] = 'Property selected.';
        }
        $this->redirect('/account/search-console');
    }

    public function importBacklinks(): void
    {
        $member = $this->requireProMember();
        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['account_error'] = 'Session token expired. Please try again.';
            $this->redirect('/account/search-console');
        }

        $file = $_FILES['backlinks_csv'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 2_000_000) {
            $_SESSION['account_error'] = 'Upload a valid CSV file (max 2MB) exported from Search Console.';
            $this->redirect('/account/search-console');
        }

        $csv = (string) file_get_contents((string) $file['tmp_name']);
        $rows = (new SearchConsoleService())->parseBacklinksCsv($csv);
        if ($rows === []) {
            $_SESSION['account_error'] = 'No rows found. Export "Top linking sites" from Search Console → Links and upload that CSV.';
            $this->redirect('/account/search-console');
        }

        (new SearchConsoleTokenRepository())->saveBacklinks((string) $member['email'], $rows, (string) ($file['name'] ?? 'backlinks.csv'));
        $_SESSION['account_notice'] = count($rows) . ' linking sites imported.';
        (new AuditLogger())->log('gsc.backlinks_imported', ['email' => $member['email'], 'count' => count($rows)]);
        $this->redirect('/account/search-console');
    }

    public function disconnect(): void
    {
        $member = $this->requireProMember();
        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['account_error'] = 'Session token expired. Please try again.';
            $this->redirect('/account/search-console');
        }

        (new SearchConsoleTokenRepository())->disconnect((string) $member['email']);
        $_SESSION['account_notice'] = 'Google Search Console disconnected.';
        (new AuditLogger())->log('gsc.disconnected', ['email' => $member['email']]);
        $this->redirect('/account/search-console');
    }

    /**
     * @return array<string, mixed>
     */
    private function requireProMember(): array
    {
        Security::ensureSession();
        $member = $_SESSION['member'] ?? null;
        if (!is_array($member) || empty($member['email'])) {
            $this->redirect('/');
        }

        // Refresh membership from storage in case a webhook upgraded them.
        $fresh = (new MemberRepository())->findByEmail((string) $member['email']);
        if (is_array($fresh)) {
            $member['membership'] = $fresh['membership'] ?? ($member['membership'] ?? null);
            $_SESSION['member'] = $member;
        }

        if (!MemberRepository::isPro($member)) {
            $_SESSION['account_error'] = 'Search Console insights are a Growth Lab Pro feature.';
            $this->redirect('/tools-pricing');
        }

        return $member;
    }
}
