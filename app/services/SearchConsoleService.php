<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SearchConsoleSettingsRepository;
use App\Models\SearchConsoleTokenRepository;

/**
 * Google Search Console OAuth 2.0 + Search Analytics API client.
 *
 * Scope: webmasters.readonly. Delivers what Google's API actually exposes —
 * search analytics (queries, pages, clicks, impressions, CTR, position) and the
 * verified site list. Backlinks are NOT available via this API (Google only
 * offers them as a manual CSV export), so those are handled separately.
 */
final class SearchConsoleService
{
    private const SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const API_BASE = 'https://www.googleapis.com/webmasters/v3';

    public function __construct(
        private readonly SearchConsoleSettingsRepository $settings = new SearchConsoleSettingsRepository(),
        private readonly SearchConsoleTokenRepository $tokens = new SearchConsoleTokenRepository(),
    ) {
    }

    public function redirectUri(): string
    {
        $https = ($_SERVER['HTTPS'] ?? '') === 'on'
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
        $scheme = $https ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');

        return $scheme . '://' . $host . '/account/search-console/callback';
    }

    /**
     * Build the Google consent URL. `state` should be a CSRF value we verify.
     */
    public function authUrl(string $state): string
    {
        $params = [
            'client_id' => $this->settings->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ];

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Exchange an authorization code for tokens and persist them for the member.
     */
    public function exchangeCode(string $email, string $code): bool
    {
        $resp = $this->postForm(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => $this->settings->clientId(),
            'client_secret' => $this->settings->clientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (!is_array($resp) || empty($resp['access_token'])) {
            return false;
        }

        $data = [
            'access_token' => (string) $resp['access_token'],
            'expires_at' => time() + (int) ($resp['expires_in'] ?? 3600) - 60,
            'connected_at' => gmdate('c'),
        ];
        // A refresh_token is only returned on first consent; keep any prior one.
        if (!empty($resp['refresh_token'])) {
            $data['refresh_token'] = (string) $resp['refresh_token'];
        }
        $this->tokens->save($email, $data);

        return $this->tokens->connected($email);
    }

    /**
     * Return a valid access token, refreshing via the refresh token if expired.
     */
    public function accessToken(string $email): ?string
    {
        $rec = $this->tokens->get($email);
        if ($rec === null) {
            return null;
        }

        if (!empty($rec['access_token']) && (int) ($rec['expires_at'] ?? 0) > time()) {
            return (string) $rec['access_token'];
        }

        $refresh = trim((string) ($rec['refresh_token'] ?? ''));
        if ($refresh === '') {
            return null;
        }

        $resp = $this->postForm(self::TOKEN_URL, [
            'client_id' => $this->settings->clientId(),
            'client_secret' => $this->settings->clientSecret(),
            'refresh_token' => $refresh,
            'grant_type' => 'refresh_token',
        ]);

        if (!is_array($resp) || empty($resp['access_token'])) {
            return null;
        }

        $this->tokens->save($email, [
            'access_token' => (string) $resp['access_token'],
            'expires_at' => time() + (int) ($resp['expires_in'] ?? 3600) - 60,
        ]);

        return (string) $resp['access_token'];
    }

    /**
     * List the member's verified Search Console properties.
     *
     * @return array<int, string>
     */
    public function listSites(string $email): array
    {
        $token = $this->accessToken($email);
        if ($token === null) {
            return [];
        }

        $resp = $this->getJson(self::API_BASE . '/sites', $token);
        $sites = [];
        foreach ((array) ($resp['siteEntry'] ?? []) as $entry) {
            $url = (string) ($entry['siteUrl'] ?? '');
            $level = (string) ($entry['permissionLevel'] ?? '');
            if ($url !== '' && $level !== 'siteUnverifiedUser') {
                $sites[] = $url;
            }
        }

        return $sites;
    }

    /**
     * Query search analytics for a dimension ('query' or 'page').
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchAnalytics(string $email, string $site, string $dimension, int $days = 28, int $rowLimit = 25): array
    {
        $token = $this->accessToken($email);
        if ($token === null || $site === '') {
            return [];
        }

        $body = [
            'startDate' => gmdate('Y-m-d', time() - ($days * 86400)),
            'endDate' => gmdate('Y-m-d', time() - 86400),
            'dimensions' => [$dimension === 'page' ? 'page' : 'query'],
            'rowLimit' => max(1, min(100, $rowLimit)),
        ];

        $resp = $this->postJson(
            self::API_BASE . '/sites/' . rawurlencode($site) . '/searchAnalytics/query',
            $token,
            $body
        );

        $rows = [];
        foreach ((array) ($resp['rows'] ?? []) as $row) {
            $rows[] = [
                'key' => (string) (($row['keys'][0] ?? '')),
                'clicks' => (int) round((float) ($row['clicks'] ?? 0)),
                'impressions' => (int) round((float) ($row['impressions'] ?? 0)),
                'ctr' => round(((float) ($row['ctr'] ?? 0)) * 100, 1),
                'position' => round((float) ($row['position'] ?? 0), 1),
            ];
        }

        return $rows;
    }

    /**
     * Aggregate totals (clicks + impressions) over the window.
     *
     * @return array{clicks: int, impressions: int, ctr: float, position: float}
     */
    public function totals(string $email, string $site, int $days = 28): array
    {
        $token = $this->accessToken($email);
        if ($token === null || $site === '') {
            return ['clicks' => 0, 'impressions' => 0, 'ctr' => 0.0, 'position' => 0.0];
        }

        $resp = $this->postJson(
            self::API_BASE . '/sites/' . rawurlencode($site) . '/searchAnalytics/query',
            $token,
            [
                'startDate' => gmdate('Y-m-d', time() - ($days * 86400)),
                'endDate' => gmdate('Y-m-d', time() - 86400),
                'dimensions' => [],
            ]
        );

        $row = ($resp['rows'][0] ?? []);
        return [
            'clicks' => (int) round((float) ($row['clicks'] ?? 0)),
            'impressions' => (int) round((float) ($row['impressions'] ?? 0)),
            'ctr' => round(((float) ($row['ctr'] ?? 0)) * 100, 1),
            'position' => round((float) ($row['position'] ?? 0), 1),
        ];
    }

    /* ------------------------------------------------------------- HTTP --- */

    private function postForm(string $url, array $fields): ?array
    {
        return $this->request('POST', $url, [
            'Content-Type: application/x-www-form-urlencoded',
        ], http_build_query($fields));
    }

    private function postJson(string $url, string $token, array $body): ?array
    {
        return $this->request('POST', $url, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ], (string) json_encode($body));
    }

    private function getJson(string $url, string $token): ?array
    {
        return $this->request('GET', $url, ['Authorization: Bearer ' . $token], null);
    }

    /**
     * @param array<int, string> $headers
     */
    private function request(string $method, string $url, array $headers, ?string $body): ?array
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array_merge($headers, ['Accept: application/json']),
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!is_string($raw) || $status < 200 || $status >= 300) {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Parse a Google Search Console "Top linking sites" CSV export.
     *
     * @return array<int, array{site: string, links: int}>
     */
    public function parseBacklinksCsv(string $csv): array
    {
        $rows = [];
        foreach (preg_split('/\r\n|\r|\n/', $csv) ?: [] as $i => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $cells = str_getcsv($line);
            $site = trim((string) ($cells[0] ?? ''));
            // Skip the header row and anything that isn't a domain-ish first cell.
            if ($site === '' || stripos($site, 'site') === 0 || stripos($site, 'linking') !== false) {
                continue;
            }
            $links = (int) preg_replace('/[^0-9]/', '', (string) ($cells[1] ?? '0'));
            $rows[] = ['site' => mb_substr($site, 0, 190), 'links' => $links];
        }

        usort($rows, static fn (array $a, array $b): int => $b['links'] <=> $a['links']);

        return $rows;
    }
}
