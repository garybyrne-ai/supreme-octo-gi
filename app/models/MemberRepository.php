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
