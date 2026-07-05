<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Referral program: each member gets a stable referral code/link. When a new
 * member registers with a ref code stored in their cookie, the referral is
 * recorded so the owner can pay a recurring commission. JSON-backed.
 */
final class ReferralRepository
{
    /**
     * Get (or lazily create) a member's referral code.
     */
    public function codeFor(string $email, string $name = ''): string
    {
        $email = strtolower(trim($email));
        $data = $this->load();
        if (!empty($data['codes'][$email])) {
            return (string) $data['codes'][$email];
        }

        $base = strtoupper((string) preg_replace('/[^a-z0-9]/i', '', $name !== '' ? $name : explode('@', $email)[0]));
        $base = substr($base, 0, 4) ?: 'REF';
        do {
            $code = $base . strtoupper(bin2hex(random_bytes(2)));
        } while ($this->emailForCode($code) !== null);

        $data['codes'][$email] = $code;
        $this->persist($data);

        return $code;
    }

    public function emailForCode(string $code): ?string
    {
        $code = strtoupper(trim($code));
        foreach ($this->load()['codes'] as $email => $c) {
            if (strtoupper((string) $c) === $code) {
                return (string) $email;
            }
        }

        return null;
    }

    /**
     * Record a referral: the referred email signed up under a ref code.
     * Ignored if the code is unknown, self-referral, or already recorded.
     */
    public function record(string $code, string $referredEmail): bool
    {
        $referredEmail = strtolower(trim($referredEmail));
        $referrer = $this->emailForCode($code);
        if ($referrer === null || $referrer === $referredEmail) {
            return false;
        }

        $data = $this->load();
        foreach ($data['events'] as $event) {
            if (strtolower((string) ($event['referred_email'] ?? '')) === $referredEmail) {
                return false; // already attributed
            }
        }

        $data['events'][] = [
            'code' => strtoupper(trim($code)),
            'referrer_email' => $referrer,
            'referred_email' => $referredEmail,
            'status' => 'signed_up',
            'created_at' => gmdate('c'),
        ];
        $this->persist($data);

        return true;
    }

    /**
     * @return array{code: string, count: int, events: array<int, array<string,mixed>>}
     */
    public function statsFor(string $email, string $name = ''): array
    {
        $email = strtolower(trim($email));
        $code = $this->codeFor($email, $name);
        $events = array_values(array_filter(
            $this->load()['events'],
            static fn (array $e): bool => strtolower((string) ($e['referrer_email'] ?? '')) === $email
        ));

        return ['code' => $code, 'count' => count($events), 'events' => $events];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allEvents(): array
    {
        $events = $this->load()['events'];
        usort($events, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return $events;
    }

    private function load(): array
    {
        $path = $this->path();
        $default = ['codes' => [], 'events' => []];
        if (!is_file($path)) {
            return $default;
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            return $default;
        }

        return [
            'codes' => is_array($decoded['codes'] ?? null) ? $decoded['codes'] : [],
            'events' => is_array($decoded['events'] ?? null) ? array_values(array_filter($decoded['events'], 'is_array')) : [],
        ];
    }

    private function persist(array $data): void
    {
        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->path(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function path(): string
    {
        return base_path('storage/data/referrals.json');
    }
}
