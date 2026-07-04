<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Reusable, SSRF-safe website scanners for background monitoring (and, going
 * forward, the ToolsController — which currently keeps its own copies; migrating
 * it onto this service is a tracked follow-up so the SSRF guard is single-sourced).
 *
 * Only headers, DNS/email and TLS are needed for the alert conditions
 * (score drop, cert expiry, SPF/DMARC break), so those live here.
 */
final class WebsiteScanService
{
    /**
     * @return array<string, mixed>|null Normalised report, or null on failure.
     */
    public function headersReport(string $input): ?array
    {
        $url = $this->normalizePublicUrl($input);
        if ($url === null) {
            return null;
        }

        $headers = $this->fetchHeaders($url);
        if ($headers === []) {
            return null;
        }

        $result = $this->scoreHeaders($headers);

        return [
            'tool' => 'Security Headers',
            'icon' => 'fa-lock',
            'target' => $url,
            'score' => $result['score'],
            'summary' => 'HTTP security-header posture.',
            'checks' => $result['checks'],
            'facts' => $result['facts'] ?? [],
            'signals' => $this->checkSignals($result['checks']),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function dnsEmailReport(string $input): ?array
    {
        $host = $this->normalizePublicHost($input);
        if ($host === null) {
            return null;
        }

        $result = $this->dnsReport($host);

        return [
            'tool' => 'DNS & Email Security',
            'icon' => 'fa-envelope-circle-check',
            'target' => $host,
            'score' => $result['score'],
            'summary' => 'DNS resilience and email anti-spoofing posture.',
            'checks' => $result['checks'],
            'signals' => $this->checkSignals($result['checks']),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function tlsReportNormalised(string $input): ?array
    {
        $host = $this->normalizePublicHost($input);
        if ($host === null) {
            return null;
        }

        $tls = $this->tlsReport($host);
        if ($tls === []) {
            return null;
        }

        $days = (int) $tls['days_remaining'];

        return [
            'tool' => 'TLS / SSL Certificate',
            'icon' => 'fa-certificate',
            'target' => $host,
            'score' => (int) $tls['score'],
            'summary' => 'HTTPS certificate validity and expiry health.',
            'days_remaining' => $days,
            'facts' => [
                ['label' => 'Issuer', 'value' => (string) $tls['issuer']],
                ['label' => 'Valid to', 'value' => (string) $tls['valid_to']],
                ['label' => 'Days left', 'value' => (string) $days],
            ],
            'checks' => [
                ['label' => 'Comfortable renewal window', 'present' => $days > 14, 'value' => $days . ' days remaining'],
            ],
            'signals' => ['days_remaining' => $days],
        ];
    }

    /**
     * Map every check to a pass/fail boolean keyed by its label — used to
     * detect regressions (e.g. SPF present -> missing) between runs.
     *
     * @param array<int, array<string, mixed>> $checks
     * @return array<string, bool>
     */
    private function checkSignals(array $checks): array
    {
        $signals = [];
        foreach ($checks as $check) {
            $signals[(string) ($check['label'] ?? '')] = !empty($check['present']);
        }

        return $signals;
    }

    // ---- SSRF-safe primitives (kept identical to ToolsController) -----------

    public function normalizePublicUrl(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $input)) {
            $input = 'https://' . $input;
        }

        $parts = parse_url($input);
        if (!is_array($parts) || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)) {
            return null;
        }

        if (!empty($parts['user']) || !empty($parts['pass'])) {
            return null;
        }

        $port = $parts['port'] ?? null;
        if ($port !== null && !in_array((int) $port, [80, 443], true)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '' || $host === 'localhost' || str_ends_with($host, '.local')) {
            return null;
        }

        $records = dns_get_record($host, DNS_A + DNS_AAAA);
        if ($records === false || $records === []) {
            return null;
        }

        foreach ($records as $record) {
            $ip = (string) ($record['ip'] ?? $record['ipv6'] ?? '');
            if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return null;
            }
        }

        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        return strtolower((string) $parts['scheme']) . '://' . $host . $path . $query;
    }

    public function normalizePublicHost(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        if (str_contains($input, '://')) {
            $parts = parse_url($input);
            $input = is_array($parts) ? (string) ($parts['host'] ?? '') : '';
        }

        $host = strtolower(trim($input, " \t\n\r\0\x0B/"));
        if ($host === '' || $host === 'localhost' || str_ends_with($host, '.local')) {
            return null;
        }

        if (!preg_match('/^(?=.{1,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $host)) {
            return null;
        }

        return $this->hostResolvesPublicly($host) ? $host : null;
    }

    private function hostResolvesPublicly(string $host): bool
    {
        $records = dns_get_record($host, DNS_A + DNS_AAAA);
        if ($records === false || $records === []) {
            return false;
        }

        foreach ($records as $record) {
            $ip = (string) ($record['ip'] ?? $record['ipv6'] ?? '');
            if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        return true;
    }

    private function fetchHeaders(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'timeout' => 4,
                'ignore_errors' => true,
                'follow_location' => 0,
                'header' => "User-Agent: CrestWebMediaMonitor/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $raw = @get_headers($url, true, $context);
        if (!is_array($raw)) {
            return [];
        }

        $headers = [];
        foreach ($raw as $name => $value) {
            if (is_int($name)) {
                $headers['status'][] = (string) $value;
                continue;
            }
            $headers[strtolower((string) $name)] = is_array($value) ? implode(', ', $value) : (string) $value;
        }

        return $headers;
    }

    private function scoreHeaders(array $headers): array
    {
        $checks = [
            'strict-transport-security' => ['label' => 'HTTP Strict Transport Security (HSTS)', 'weight' => 16],
            'content-security-policy' => ['label' => 'Content Security Policy (CSP)', 'weight' => 16],
            'x-frame-options' => ['label' => 'Clickjacking protection', 'weight' => 12],
            'x-content-type-options' => ['label' => 'MIME sniffing protection', 'weight' => 8],
            'referrer-policy' => ['label' => 'Referrer privacy policy', 'weight' => 8],
            'permissions-policy' => ['label' => 'Browser permissions policy', 'weight' => 8],
            'cross-origin-opener-policy' => ['label' => 'Cross-Origin-Opener-Policy (COOP)', 'weight' => 6],
            'cross-origin-resource-policy' => ['label' => 'Cross-Origin-Resource-Policy (CORP)', 'weight' => 5],
        ];

        $score = 6;
        $rows = [];
        foreach ($checks as $header => $check) {
            $present = !empty($headers[$header]);
            $score += $present ? $check['weight'] : 0;
            $rows[] = [
                'label' => $check['label'],
                'present' => $present,
                'value' => $present ? (string) $headers[$header] : 'Missing',
            ];
        }

        foreach (['server' => 'Server banner', 'x-powered-by' => 'X-Powered-By disclosure'] as $header => $label) {
            $leaks = !empty($headers[$header]);
            $score += $leaks ? 0 : 6;
            $rows[] = [
                'label' => $label . ' hidden',
                'present' => !$leaks,
                'value' => $leaks ? 'Exposed: ' . $headers[$header] : 'Not disclosed',
            ];
        }

        if (!empty($headers['set-cookie'])) {
            $cookie = strtolower((string) $headers['set-cookie']);
            $secure = str_contains($cookie, 'secure') && str_contains($cookie, 'httponly');
            $score += $secure ? 4 : 0;
            $rows[] = [
                'label' => 'Secure cookie flags',
                'present' => $secure,
                'value' => $secure ? 'HttpOnly + Secure present' : 'Missing HttpOnly and/or Secure',
            ];
        }

        return [
            'score' => min(100, $score),
            'status' => $headers['status'][0] ?? 'No status line',
            'checks' => $rows,
            'facts' => [
                ['label' => 'HTTP status', 'value' => (string) ($headers['status'][0] ?? 'Unknown')],
                ['label' => 'Server', 'value' => (string) ($headers['server'] ?? 'Not disclosed')],
            ],
        ];
    }

    private function dnsReport(string $host): array
    {
        $txt = @dns_get_record($host, DNS_TXT) ?: [];
        $dmarc = @dns_get_record('_dmarc.' . $host, DNS_TXT) ?: [];
        $records = [
            'A' => @dns_get_record($host, DNS_A) ?: [],
            'AAAA' => @dns_get_record($host, DNS_AAAA) ?: [],
            'MX' => @dns_get_record($host, DNS_MX) ?: [],
            'NS' => @dns_get_record($host, DNS_NS) ?: [],
            'CAA' => defined('DNS_CAA') ? (@dns_get_record($host, DNS_CAA) ?: []) : [],
            'TXT' => $txt,
            'DMARC' => $dmarc,
        ];

        $spf = array_values(array_filter($txt, static fn (array $row): bool => str_starts_with((string) ($row['txt'] ?? ''), 'v=spf1')));
        $dmarcPolicy = $dmarc[0]['txt'] ?? '';
        $score = 20;
        $score += $records['MX'] !== [] ? 15 : 0;
        $score += $spf !== [] ? 20 : 0;
        $score += $dmarc !== [] ? 25 : 0;
        $score += $records['CAA'] !== [] ? 10 : 0;
        $score += $records['NS'] !== [] ? 10 : 0;

        return [
            'score' => min(100, $score),
            'checks' => [
                ['label' => 'A/AAAA public records', 'present' => ($records['A'] !== [] || $records['AAAA'] !== []), 'value' => $this->recordSummary(array_merge($records['A'], $records['AAAA']), ['ip', 'ipv6'])],
                ['label' => 'Mail exchanger (MX) records', 'present' => $records['MX'] !== [], 'value' => $this->recordSummary($records['MX'], ['target'])],
                ['label' => 'SPF email protection', 'present' => $spf !== [], 'value' => $spf[0]['txt'] ?? 'Missing'],
                ['label' => 'DMARC email policy', 'present' => $dmarc !== [], 'value' => $dmarcPolicy ?: 'Missing'],
                ['label' => 'CAA certificate control', 'present' => $records['CAA'] !== [], 'value' => $this->recordSummary($records['CAA'], ['value', 'tag'])],
                ['label' => 'Nameservers', 'present' => $records['NS'] !== [], 'value' => $this->recordSummary($records['NS'], ['target'])],
            ],
        ];
    }

    private function tlsReport(string $host): array
    {
        if (!function_exists('stream_socket_client') || !function_exists('openssl_x509_parse')) {
            return [];
        }

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => true,
                'verify_peer_name' => true,
                'peer_name' => $host,
                'SNI_enabled' => true,
            ],
        ]);

        $socket = @stream_socket_client('ssl://' . $host . ':443', $errno, $error, 5, STREAM_CLIENT_CONNECT, $context);
        if (!is_resource($socket)) {
            return [];
        }

        $params = stream_context_get_params($socket);
        fclose($socket);
        $cert = $params['options']['ssl']['peer_certificate'] ?? null;
        if (!$cert) {
            return [];
        }

        $parsed = openssl_x509_parse($cert);
        if (!is_array($parsed)) {
            return [];
        }

        $validTo = (int) ($parsed['validTo_time_t'] ?? 0);
        $days = $validTo > 0 ? (int) floor(($validTo - time()) / 86400) : 0;

        return [
            'score' => $days > 60 ? 100 : ($days > 14 ? 75 : ($days > 0 ? 45 : 0)),
            'subject' => $parsed['subject']['CN'] ?? $host,
            'issuer' => $parsed['issuer']['O'] ?? ($parsed['issuer']['CN'] ?? 'Unknown issuer'),
            'valid_from' => isset($parsed['validFrom_time_t']) ? date('Y-m-d', (int) $parsed['validFrom_time_t']) : 'Unknown',
            'valid_to' => $validTo > 0 ? date('Y-m-d', $validTo) : 'Unknown',
            'days_remaining' => max(0, $days),
        ];
    }

    private function recordSummary(array $records, array $keys): string
    {
        if ($records === []) {
            return 'Missing';
        }

        $values = [];
        foreach ($records as $record) {
            foreach ($keys as $key) {
                if (!empty($record[$key])) {
                    $values[] = (string) $record[$key];
                }
            }
        }

        return $values === [] ? 'Present' : implode(', ', array_slice(array_unique($values), 0, 5));
    }
}
