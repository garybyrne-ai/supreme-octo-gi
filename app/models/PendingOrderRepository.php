<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Records code-shop checkouts the moment they start, so a buyer who bounces off
 * the Stripe/PayPal page (an "abandoned order") can be followed up with a
 * recovery email. A matching webhook success marks the order completed; a cron
 * sweep emails anyone still pending after a grace period. JSON-backed — works
 * with no database and survives file replacement.
 */
final class PendingOrderRepository
{
    /**
     * Record a started checkout. Returns the stored order (with its id).
     *
     * @param array<string, mixed> $product
     * @return array<string, mixed>
     */
    public function start(string $email, string $name, array $product, string $gateway): array
    {
        $email = strtolower(trim($email));
        $data = $this->load();

        $order = [
            'id' => bin2hex(random_bytes(8)),
            'email' => $email,
            'name' => trim($name),
            'slug' => (string) ($product['slug'] ?? ''),
            'title' => (string) ($product['title'] ?? 'Code shop order'),
            'price' => number_format(((int) ($product['price_cents'] ?? 0)) / 100, 2),
            'currency' => (string) ($product['currency'] ?? 'EUR'),
            'gateway' => $gateway === 'paypal' ? 'paypal' : 'stripe',
            'status' => 'started',
            'created_at' => gmdate('c'),
            'recovered_at' => null,
            'completed_at' => null,
        ];

        // Collapse repeat clicks: if the same email has an open order for the
        // same product, refresh it instead of stacking duplicates.
        if ($email !== '') {
            foreach ($data['orders'] as $i => $existing) {
                if (
                    ($existing['status'] ?? '') === 'started'
                    && strtolower((string) ($existing['email'] ?? '')) === $email
                    && (string) ($existing['slug'] ?? '') === $order['slug']
                ) {
                    $order['id'] = (string) $existing['id'];
                    $order['recovered_at'] = $existing['recovered_at'] ?? null;
                    $data['orders'][$i] = $order;
                    $this->persist($data);
                    return $order;
                }
            }
        }

        $data['orders'][] = $order;
        // Keep the file bounded.
        if (count($data['orders']) > 500) {
            $data['orders'] = array_slice($data['orders'], -500);
        }
        $this->persist($data);

        return $order;
    }

    /**
     * Mark the most recent open order for this email + product as completed.
     * If no email is known, match on product slug alone (best effort).
     */
    public function markCompleted(string $email, string $slug): bool
    {
        $email = strtolower(trim($email));
        $data = $this->load();
        $matchedIndex = null;

        foreach ($data['orders'] as $i => $order) {
            if (($order['status'] ?? '') === 'completed') {
                continue;
            }
            if ((string) ($order['slug'] ?? '') !== $slug) {
                continue;
            }
            if ($email !== '' && strtolower((string) ($order['email'] ?? '')) !== $email) {
                continue;
            }
            $matchedIndex = $i; // keep the last (most recent) match
        }

        if ($matchedIndex === null) {
            return false;
        }

        $data['orders'][$matchedIndex]['status'] = 'completed';
        $data['orders'][$matchedIndex]['completed_at'] = gmdate('c');
        $this->persist($data);

        return true;
    }

    /**
     * Orders that started more than $graceMinutes ago, never completed, never
     * yet recovered, and that carry an email we can write to.
     *
     * @return array<int, array<string, mixed>>
     */
    public function dueForRecovery(int $graceMinutes = 45, int $expireHours = 72): array
    {
        $now = time();
        $due = [];
        foreach ($this->load()['orders'] as $order) {
            if (($order['status'] ?? '') !== 'started' || !empty($order['recovered_at'])) {
                continue;
            }
            $email = strtolower(trim((string) ($order['email'] ?? '')));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $started = strtotime((string) ($order['created_at'] ?? '')) ?: 0;
            if ($started === 0) {
                continue;
            }
            $ageMinutes = ($now - $started) / 60;
            if ($ageMinutes < $graceMinutes || $ageMinutes > $expireHours * 60) {
                continue;
            }
            $due[] = $order;
        }

        return $due;
    }

    public function markRecovered(string $id): void
    {
        $data = $this->load();
        foreach ($data['orders'] as $i => $order) {
            if ((string) ($order['id'] ?? '') === $id) {
                $data['orders'][$i]['recovered_at'] = gmdate('c');
                $data['orders'][$i]['status'] = 'recovered';
                $this->persist($data);
                return;
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 50): array
    {
        $orders = $this->load()['orders'];
        usort($orders, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return array_slice($orders, 0, $limit);
    }

    /**
     * @return array{started:int, recovered:int, completed:int}
     */
    public function stats(): array
    {
        $started = $recovered = $completed = 0;
        foreach ($this->load()['orders'] as $order) {
            $status = (string) ($order['status'] ?? '');
            if ($status === 'completed') {
                $completed++;
            } elseif ($status === 'recovered' || !empty($order['recovered_at'])) {
                $recovered++;
            } else {
                $started++;
            }
        }

        return ['started' => $started, 'recovered' => $recovered, 'completed' => $completed];
    }

    private function load(): array
    {
        $path = $this->path();
        if (!is_file($path)) {
            return ['orders' => []];
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded) || !is_array($decoded['orders'] ?? null)) {
            return ['orders' => []];
        }

        return ['orders' => array_values(array_filter($decoded['orders'], 'is_array'))];
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
        return base_path('storage/data/pending-orders.json');
    }
}
