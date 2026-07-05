<?php

declare(strict_types=1);

namespace App\Models;

final class MemberRepository
{
    public function create(string $name, string $email, string $password): array
    {
        $email = strtolower(trim($email));
        $members = $this->all();

        foreach ($members as $member) {
            if (($member['email'] ?? '') === $email) {
                throw new \RuntimeException('An account already exists for this email.');
            }
        }

        $member = [
            'id' => bin2hex(random_bytes(8)),
            'name' => trim($name),
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'forum_verified' => false,
            'created_at' => gmdate('c'),
        ];

        array_unshift($members, $member);
        $this->save($members);

        return $member;
    }

    /**
     * Create or refresh a member and grant them active Growth Lab Pro access.
     * Used by the admin "Pro Test Account" tool so the owner can log in and
     * exercise every gated tool. Merges by email — existing members and their
     * data are never clobbered.
     *
     * @return array<string, mixed> the member (without password hash)
     */
    public function upsertProMember(string $name, string $email, string $password): array
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Enter a valid email address for the test account.');
        }
        if (strlen($password) < 10) {
            throw new \RuntimeException('Use a password of at least 10 characters.');
        }

        $membership = [
            'plan' => 'growth-lab-pro-test',
            'plan_name' => 'Growth Lab Pro (test)',
            'status' => 'active',
            'provider' => 'admin-test',
            'activated_at' => gmdate('c'),
            'current_period_ends_at' => gmdate('c', time() + (3650 * 86400)),
        ];

        $members = $this->all();
        $found = false;
        foreach ($members as &$member) {
            if (($member['email'] ?? '') === $email) {
                $member['name'] = trim($name) !== '' ? trim($name) : ($member['name'] ?? 'Pro Tester');
                $member['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                $member['membership'] = $membership;
                $member['forum_verified'] = true;
                $found = true;
                break;
            }
        }
        unset($member);

        if (!$found) {
            array_unshift($members, [
                'id' => bin2hex(random_bytes(8)),
                'name' => trim($name) !== '' ? trim($name) : 'Pro Tester',
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'forum_verified' => true,
                'membership' => $membership,
                'created_at' => gmdate('c'),
            ]);
        }

        $this->save($members);

        return $this->findByEmail($email) ?? [];
    }

    public function verify(string $email, string $password): ?array
    {
        $email = strtolower(trim($email));

        foreach ($this->all() as $member) {
            if (($member['email'] ?? '') === $email && password_verify($password, (string) ($member['password_hash'] ?? ''))) {
                unset($member['password_hash']);
                return $member;
            }
        }

        return null;
    }

    public function findByEmail(string $email): ?array
    {
        $email = strtolower(trim($email));

        foreach ($this->all() as $member) {
            if (($member['email'] ?? '') === $email) {
                unset($member['password_hash']);
                return $member;
            }
        }

        return null;
    }

    /**
     * Attach or update a member's membership record (used by payment webhooks).
     * Matches by email so hosted Stripe/PayPal checkouts can activate accounts.
     *
     * @param array<string, mixed> $membership
     */
    public function setMembershipByEmail(string $email, array $membership): bool
    {
        $email = strtolower(trim($email));
        $members = $this->all();
        $found = false;

        foreach ($members as &$member) {
            if (($member['email'] ?? '') === $email) {
                $member['membership'] = $membership;
                $found = true;
                break;
            }
        }
        unset($member);

        if ($found) {
            $this->save($members);
        }

        return $found;
    }

    /**
     * A member is "Pro" when their membership is active and not past its period.
     *
     * @param array<string, mixed>|null $member
     */
    public static function isPro(?array $member): bool
    {
        $membership = $member['membership'] ?? null;
        if (!is_array($membership) || ($membership['status'] ?? '') !== 'active') {
            return false;
        }

        $endsAt = (string) ($membership['current_period_ends_at'] ?? '');
        if ($endsAt === '') {
            return true;
        }

        $ts = strtotime($endsAt);
        return $ts === false || $ts > time();
    }

    /**
     * Fetch a single member by id (without the password hash).
     *
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        foreach ($this->all() as $member) {
            if (($member['id'] ?? '') === $id) {
                unset($member['password_hash']);
                return $member;
            }
        }

        return null;
    }

    /**
     * Admin edit of a member: name, email, forum posting access and membership
     * tier with an explicit expiry date/time. Selecting "free" downgrades the
     * member; "pro" upgrades them and (optionally) sets when access ends.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed> the updated member (without password hash)
     */
    public function adminUpdate(string $id, array $data): array
    {
        $members = $this->all();

        $newEmail = strtolower(trim((string) ($data['email'] ?? '')));
        if ($newEmail !== '' && !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Enter a valid email address.');
        }
        foreach ($members as $existing) {
            if (($existing['id'] ?? '') !== $id && $newEmail !== '' && ($existing['email'] ?? '') === $newEmail) {
                throw new \RuntimeException('Another member already uses that email address.');
            }
        }

        $plan = strtolower(trim((string) ($data['plan'] ?? 'free')));
        $expiresIso = null;
        if ($plan === 'pro') {
            $expiresRaw = trim((string) ($data['expires_at'] ?? ''));
            if ($expiresRaw !== '') {
                $ts = strtotime($expiresRaw);
                if ($ts === false) {
                    throw new \RuntimeException('Enter a valid expiry date and time.');
                }
                $expiresIso = date('c', $ts);
            }
        }

        $found = false;
        foreach ($members as &$member) {
            if (($member['id'] ?? '') !== $id) {
                continue;
            }

            if (trim((string) ($data['name'] ?? '')) !== '') {
                $member['name'] = trim((string) $data['name']);
            }
            if ($newEmail !== '') {
                $member['email'] = $newEmail;
            }
            // Optional password reset — only when a new password is supplied.
            $newPassword = (string) ($data['password'] ?? '');
            if ($newPassword !== '') {
                if (strlen($newPassword) < 10) {
                    throw new \RuntimeException('New password must be at least 10 characters.');
                }
                $member['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $member['password_reset_at'] = gmdate('c');
            }
            $member['forum_verified'] = !empty($data['forum_verified']);

            if ($plan === 'pro') {
                $current = is_array($member['membership'] ?? null) ? $member['membership'] : [];
                $member['membership'] = array_replace($current, [
                    'plan' => $current['plan'] ?? 'growth-lab-pro',
                    'plan_name' => $current['plan_name'] ?? 'Growth Lab Pro',
                    'status' => 'active',
                    'provider' => $current['provider'] ?? 'admin',
                    'activated_at' => $current['activated_at'] ?? gmdate('c'),
                    'current_period_ends_at' => $expiresIso,
                    'updated_at' => gmdate('c'),
                ]);
            } else {
                // Downgrade to Free — drop the active membership entirely.
                $member['membership'] = null;
            }

            $found = true;
            break;
        }
        unset($member);

        if (!$found) {
            throw new \RuntimeException('Member not found.');
        }

        $this->save($members);

        return $this->find($id) ?? [];
    }

    public function deleteMember(string $id): void
    {
        $members = array_values(array_filter(
            $this->all(),
            static fn (array $member): bool => ($member['id'] ?? '') !== $id
        ));

        $this->save($members);
    }

    public function recent(int $limit = 80): array
    {
        $members = $this->all();
        usort($members, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return array_slice(array_map(static function (array $member): array {
            unset($member['password_hash']);
            $member['forum_verified'] = (bool) ($member['forum_verified'] ?? false);
            return $member;
        }, $members), 0, max(1, min(200, $limit)));
    }

    public function setForumVerified(string $id, bool $verified): void
    {
        $members = $this->all();
        $found = false;

        foreach ($members as &$member) {
            if (($member['id'] ?? '') === $id) {
                $member['forum_verified'] = $verified;
                $member['forum_verified_at'] = $verified ? gmdate('c') : null;
                $found = true;
                break;
            }
        }
        unset($member);

        if (!$found) {
            throw new \RuntimeException('Forum member not found.');
        }

        $this->save($members);
    }

    public function all(): array
    {
        $path = $this->path();
        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
    }

    private function save(array $members): void
    {
        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->path(), json_encode($members, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function path(): string
    {
        return base_path('storage/data/site-members.json');
    }
}
