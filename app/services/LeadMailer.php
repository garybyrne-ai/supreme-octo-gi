<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MailSettingsRepository;

final class LeadMailer
{
    private ?string $lastError = null;

    public function sendCode(string $email, string $name, string $code): bool
    {
        $subject = 'Your Crest Web Media tool access code';
        $html = '<p>Hi ' . e($name) . ',</p><p>Your tool access code is <strong style="font-size:22px">' . e($code) . '</strong>.</p><p>This code expires in 15 minutes.</p>';
        return $this->send($email, $subject, $html);
    }

    public function sendOffer(string $email, string $name, array $offer): void
    {
        $html = $this->offerHtml($name, $offer);
        $this->send($email, (string) $offer['subject'], $html);
    }

    public function sendTest(string $to): array
    {
        $settings = (new MailSettingsRepository())->current();
        $subject = 'Crest Web Media mail test';
        $html = '<p>This is a test email from Crest Web Media.</p><p>Driver: <strong>' . e((string) ($settings['driver'] ?? 'php_mail')) . '</strong></p><p>If you received this, your website mail settings are working.</p>';

        $sent = $this->send($to, $subject, $html);
        return ['ok' => $sent, 'error' => $this->lastError];
    }

    public function sendDigitalProductDelivery(string $to, string $name, array $product, ?string $downloadUrl): bool
    {
        $title = (string) ($product['title'] ?? 'Crest Web Media digital product');
        $subject = 'Your Crest Web Media code shop order is ready';
        $html = '<!doctype html><html><body style="margin:0;background:#02040c;color:#f3f8ff;font-family:Arial,sans-serif">'
            . '<div style="max-width:680px;margin:0 auto;padding:30px;background:linear-gradient(145deg,#071020,#030712);border:1px solid #00e5ff">'
            . '<p style="color:#23ff9a;text-transform:uppercase;font-size:12px;letter-spacing:.08em">Digital Code Shop</p>'
            . '<h1 style="font-size:30px;line-height:1.12;color:#fff">Your purchase is ready.</h1>'
            . '<p>Hi ' . e($name !== '' ? $name : 'there') . ',</p>'
            . '<p style="line-height:1.7;color:#c8d8ef">Thanks for buying <strong style="color:#fff">' . e($title) . '</strong>. Keep this email safe for your records.</p>';

        if ($downloadUrl !== null) {
            $html .= '<p style="line-height:1.7;color:#c8d8ef">Your secure download link expires in 24 hours and can be used once.</p>'
                . '<p><a href="' . e($downloadUrl) . '" style="display:inline-block;padding:15px 22px;background:linear-gradient(135deg,#00b7ff,#7b3eff);color:#fff;text-decoration:none;font-weight:bold;border-radius:999px">Download Package</a></p>';
        } else {
            $html .= '<p style="line-height:1.7;color:#c8d8ef">Your snippet product can be viewed safely from the Code Shop screen. Scripts are displayed as code and rendered previews are sandboxed.</p>';
        }

        $html .= '<p style="line-height:1.7;color:#9db4d0">If you need installation help, reply to this email or open a support ticket from the website.</p>'
            . '</div></body></html>';

        return $this->send($to, $subject, $html);
    }

    /**
     * @param array<string, mixed> $monitor
     * @param array<string, mixed> $report
     * @param array<int, string> $changes
     */
    public function sendMonitorAlert(string $email, string $name, array $monitor, array $report, array $changes): bool
    {
        $tool = (string) ($report['tool'] ?? 'Monitor');
        $target = (string) ($monitor['target'] ?? '');
        $score = (int) ($report['score'] ?? 0);
        $subject = 'Alert: ' . $tool . ' changed for ' . $target;

        $items = '';
        foreach ($changes as $change) {
            $items .= '<li style="margin-bottom:6px;line-height:1.6;color:#ffd7e2">' . e($change) . '</li>';
        }

        $html = '<!doctype html><html><body style="margin:0;background:#02040c;color:#f3f8ff;font-family:Arial,sans-serif">'
            . '<div style="max-width:680px;margin:0 auto;padding:30px;background:linear-gradient(145deg,#071020,#030712);border:1px solid #ff4d81">'
            . '<p style="color:#ff4d81;text-transform:uppercase;font-size:12px;letter-spacing:.08em">Growth Lab Monitoring Alert</p>'
            . '<h1 style="font-size:26px;line-height:1.15;color:#fff">' . e($tool) . ' changed for ' . e($target) . '</h1>'
            . '<p>Hi ' . e($name !== '' ? $name : 'there') . ',</p>'
            . '<p style="line-height:1.7;color:#c8d8ef">Your scheduled monitor found the following on its latest run (current score <strong style="color:#fff">' . e((string) $score) . '/100</strong>):</p>'
            . '<ul style="padding-left:18px">' . $items . '</ul>'
            . '<p><a href="https://www.crestwebmedia.com/account/dashboard" style="display:inline-block;padding:14px 22px;background:linear-gradient(135deg,#00b7ff,#7b3eff);color:#fff;text-decoration:none;font-weight:bold;border-radius:999px">Open your dashboard</a></p>'
            . '<p style="line-height:1.7;color:#9db4d0;font-size:13px">You are receiving this because you set up monitoring on Crest Web Media Growth Lab. Manage or remove monitors from your dashboard.</p>'
            . '</div></body></html>';

        return $this->send($email, $subject, $html);
    }

    /**
     * Recovery nudge for a checkout that was started but never paid.
     *
     * @param array<string, mixed> $order
     */
    public function sendAbandonedOrderRecovery(string $email, array $order, string $productUrl, string $coupon = ''): bool
    {
        $name = trim((string) ($order['name'] ?? ''));
        $title = (string) ($order['title'] ?? 'your order');
        $price = (string) ($order['currency'] ?? 'EUR') . ' ' . (string) ($order['price'] ?? '');
        $subject = 'Still thinking it over? ' . $title . ' is waiting for you';

        $couponBlock = '';
        if ($coupon !== '') {
            $couponBlock = '<div style="padding:16px 18px;border:1px dashed #23ff9a;background:rgba(35,255,154,.08);margin:20px 0;border-radius:12px">'
                . '<p style="margin:0;color:#c8d8ef">Use code <strong style="color:#23ff9a;font-size:18px;letter-spacing:.05em">' . e($coupon) . '</strong> at checkout to save on this order.</p>'
                . '</div>';
        }

        $html = '<!doctype html><html><body style="margin:0;background:#02040c;color:#f3f8ff;font-family:Arial,sans-serif">'
            . '<div style="max-width:640px;margin:0 auto;padding:30px;background:linear-gradient(145deg,#071020,#030712);border:1px solid #00b7ff">'
            . '<p style="color:#00e5ff;text-transform:uppercase;font-size:12px;letter-spacing:.08em">Crest Web Media Code Shop</p>'
            . '<h1 style="font-size:28px;line-height:1.15;color:#fff">You left ' . e($title) . ' behind.</h1>'
            . '<p>Hi ' . e($name !== '' ? $name : 'there') . ',</p>'
            . '<p style="line-height:1.7;color:#c8d8ef">It looks like your checkout for <strong style="color:#fff">' . e($title) . '</strong> (' . e($price) . ') didn\'t finish. No problem — your order is still saved and you can pick up right where you left off.</p>'
            . $couponBlock
            . '<p><a href="' . e($productUrl) . '" style="display:inline-block;padding:15px 24px;background:linear-gradient(135deg,#00b7ff,#7b3eff);color:#fff;text-decoration:none;font-weight:bold;border-radius:999px">Complete My Order</a></p>'
            . '<p style="line-height:1.7;color:#9db4d0;font-size:13px">Questions before you buy? Just reply to this email or open a support ticket on the site and we\'ll help. If you\'ve already completed your purchase, please ignore this message.</p>'
            . '</div></body></html>';

        return $this->send($email, $subject, $html);
    }

    /**
     * Emails a copy of an on-page SEO / security audit result to the person who
     * ran it, so they keep the report and can act on the fixes later.
     *
     * @param array<string, mixed> $report
     */
    public function sendAuditReport(string $email, string $name, array $report, string $reportUrl = ''): bool
    {
        $tool = (string) ($report['tool'] ?? 'Website');
        $target = (string) ($report['target'] ?? '');
        $score = (int) ($report['score'] ?? 0);
        $subject = 'Your ' . $tool . ' audit report' . ($target !== '' ? ' for ' . $target : '');

        $facts = '';
        foreach (($report['facts'] ?? []) as $fact) {
            if (!is_array($fact)) {
                continue;
            }
            $facts .= '<tr>'
                . '<td style="padding:8px 12px;border-bottom:1px solid rgba(255,255,255,.08);color:#9db4d0">' . e((string) ($fact['label'] ?? '')) . '</td>'
                . '<td style="padding:8px 12px;border-bottom:1px solid rgba(255,255,255,.08);color:#fff;text-align:right">' . e((string) ($fact['value'] ?? '')) . '</td>'
                . '</tr>';
        }

        // Show the highest-priority fixes first (failing checks).
        $fixes = '';
        $shown = 0;
        foreach (($report['checks'] ?? []) as $check) {
            if (!is_array($check) || !empty($check['present'])) {
                continue; // present === true means the check passed
            }
            $label = (string) ($check['label'] ?? ($check['title'] ?? ''));
            $detail = (string) ($check['advice'] ?? ($check['value'] ?? ''));
            if ($label === '') {
                continue;
            }
            $fixes .= '<li style="margin-bottom:8px;line-height:1.55;color:#ffd7e2"><strong style="color:#fff">' . e($label) . '</strong>'
                . ($detail !== '' ? ' — ' . e($detail) : '') . '</li>';
            if (++$shown >= 6) {
                break;
            }
        }
        if ($fixes === '') {
            $fixes = '<li style="line-height:1.55;color:#23ff9a">Nice — no critical issues flagged on this scan.</li>';
        }

        $scoreColor = $score >= 80 ? '#23ff9a' : ($score >= 55 ? '#ffc66f' : '#ff6b8b');

        $html = '<!doctype html><html><body style="margin:0;background:#02040c;color:#f3f8ff;font-family:Arial,sans-serif">'
            . '<div style="max-width:680px;margin:0 auto;padding:30px;background:linear-gradient(145deg,#071020,#030712);border:1px solid #00b7ff">'
            . '<p style="color:#00e5ff;text-transform:uppercase;font-size:12px;letter-spacing:.08em">Crest Web Media Growth Lab</p>'
            . '<h1 style="font-size:26px;line-height:1.15;color:#fff">' . e($tool) . ' audit' . ($target !== '' ? ' for ' . e($target) : '') . '</h1>'
            . '<p>Hi ' . e($name !== '' ? $name : 'there') . ',</p>'
            . '<p style="line-height:1.7;color:#c8d8ef">Here\'s the report you just ran. Your overall score is '
            . '<strong style="color:' . $scoreColor . ';font-size:20px">' . e((string) $score) . '/100</strong>.</p>'
            . ($facts !== '' ? '<table style="width:100%;border-collapse:collapse;margin:18px 0;font-size:14px">' . $facts . '</table>' : '')
            . '<h2 style="font-size:17px;color:#fff;margin:22px 0 8px">Top fixes to prioritise</h2>'
            . '<ul style="padding-left:18px;margin:0">' . $fixes . '</ul>';

        if ($reportUrl !== '') {
            $html .= '<p style="margin-top:24px"><a href="' . e($reportUrl) . '" style="display:inline-block;padding:14px 22px;background:linear-gradient(135deg,#00b7ff,#7b3eff);color:#fff;text-decoration:none;font-weight:bold;border-radius:999px">Open the full report</a></p>';
        }

        $html .= '<p style="line-height:1.7;color:#9db4d0;font-size:13px;margin-top:24px">Want these audits to run automatically and keep a white-label PDF history? Growth Lab Pro adds unlimited scans, saved reports and continuous monitoring. Reply to this email if you\'d like a hand implementing the fixes.</p>'
            . '</div></body></html>';

        return $this->send($email, $subject, $html);
    }

    private function send(string $to, string $subject, string $html): bool
    {
        $settings = (new MailSettingsRepository())->current();
        $this->lastError = null;
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->mailboxHeader((string) $settings['from_name'], (string) $settings['from_email']),
        ];

        $sent = false;
        $error = null;
        try {
            if (($settings['driver'] ?? 'php_mail') === 'gmail_smtp') {
                $sent = $this->sendSmtp($settings, $to, $subject, $html);
            } elseif (function_exists('mail')) {
                $sent = @mail($to, $subject, $html, implode("\r\n", $headers));
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
            $this->lastError = $error;
        }

        $dir = base_path('storage/logs');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $dir . '/mail-' . gmdate('Y-m-d') . '.jsonl',
            json_encode(['sent' => $sent, 'driver' => $settings['driver'] ?? 'php_mail', 'error' => $error, 'to' => $to, 'subject' => $subject, 'html' => $html, 'timestamp' => gmdate('c')], JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if (!$sent && $this->lastError === null) {
            $this->lastError = 'The mail driver returned false. Check hosting mail limits, SPF/DMARC, or Gmail SMTP credentials.';
        }

        return $sent;
    }

    private function sendSmtp(array $settings, string $to, string $subject, string $html): bool
    {
        $host = (string) $settings['smtp_host'];
        $port = (int) $settings['smtp_port'];
        $username = (string) $settings['smtp_username'];
        $password = (string) $settings['smtp_password'];
        $fromEmail = (string) $settings['from_email'];
        $fromName = (string) $settings['from_name'];
        $encryption = (string) ($settings['smtp_encryption'] ?? 'tls');
        $isGmail = str_contains(strtolower($host), 'gmail.com');
        $envelopeFrom = $isGmail && $username !== '' ? $username : $fromEmail;
        $headerFrom = $isGmail && $username !== '' ? $username : $fromEmail;

        $transport = $port === 465 ? 'ssl://' : 'tcp://';
        $socket = @stream_socket_client($transport . $host . ':' . $port, $errno, $errstr, 20, STREAM_CLIENT_CONNECT);
        if (!is_resource($socket)) {
            throw new \RuntimeException('SMTP connection failed: ' . ($errstr ?: 'connection refused or timed out'));
        }

        stream_set_timeout($socket, 20);
        $this->expect($socket, [220]);
        $this->command($socket, 'EHLO crestwebmedia.local', [250]);

        if ($encryption === 'tls' && $port !== 465) {
            $this->command($socket, 'STARTTLS', [220]);
            $cryptoMethod = defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')
                ? STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                : STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (!stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
                fclose($socket);
                throw new \RuntimeException('Could not start TLS for SMTP.');
            }
            $this->command($socket, 'EHLO crestwebmedia.local', [250]);
        }

        $this->command($socket, 'AUTH LOGIN', [334]);
        $this->command($socket, base64_encode($username), [334]);
        $this->command($socket, base64_encode($password), [235]);
        $this->command($socket, 'MAIL FROM:<' . $envelopeFrom . '>', [250]);
        $this->command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
        $this->command($socket, 'DATA', [354]);

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->mailboxHeader($fromName, $headerFrom),
            'Reply-To: ' . $this->mailboxHeader($fromName, $fromEmail),
            'To: <' . $to . '>',
            'Subject: ' . $this->encodeHeader($subject),
            'Date: ' . date(DATE_RFC2822),
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@crestwebmedia.com>',
        ];
        $message = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n.", "\n..", $html) . "\r\n.";
        $this->command($socket, $message, [250]);
        $this->command($socket, 'QUIT', [221]);
        fclose($socket);

        return true;
    }

    private function command($socket, string $command, array $expected): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->expect($socket, $expected);
    }

    private function expect($socket, array $expected): string
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (preg_match('/^\d{3}\s/', $line)) {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expected, true)) {
            throw new \RuntimeException('SMTP error: ' . trim($response));
        }

        return $response;
    }

    private function mailboxHeader(string $name, string $email): string
    {
        return $this->encodeHeader($name) . ' <' . $email . '>';
    }

    private function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function offerHtml(string $name, array $offer): string
    {
        return '<!doctype html><html><body style="margin:0;background:#060504;color:#f3f8ff;font-family:Arial,sans-serif">'
            . '<div style="max-width:680px;margin:0 auto;padding:28px;background:linear-gradient(145deg,#16120d,#060504);border:1px solid #e2873c">'
            . '<p style="color:#0fffc1;text-transform:uppercase;font-size:12px">' . e($offer['preheader']) . '</p>'
            . '<h1 style="font-size:34px;line-height:1.05;color:#fff">' . e($offer['headline']) . '</h1>'
            . '<p>Hi ' . e($name) . ',</p>'
            . '<p style="line-height:1.7;color:#d8e6f7">' . nl2br(e($offer['intro'])) . '</p>'
            . '<div style="padding:18px;border:1px solid #00cfff;background:rgba(0,207,255,.08);margin:22px 0"><strong style="color:#ffc66f">Offer:</strong><p style="line-height:1.7">' . nl2br(e($offer['offer'])) . '</p></div>'
            . '<p style="line-height:1.7">Services: SEO, SERP strategy, PPC campaigns, website development, app development, AI integrations and workflow automation.</p>'
            . '<p><a href="' . e($offer['cta_url']) . '" style="display:inline-block;padding:14px 20px;background:#e2873c;color:#060504;text-decoration:none;font-weight:bold">' . e($offer['cta_label']) . '</a></p>'
            . '</div></body></html>';
    }
}
