<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Backend-controlled membership/subscription plans. Every commercial attribute
 * (price, currency, billing interval, trial, Stripe price / payment link,
 * PayPal plan / subscribe link, feature list, badge and ordering) is editable
 * from the admin console and read by the public /membership page.
 */
final class MembershipPlanRepository
{
    public const INTERVALS = ['one_time', 'weekly', 'monthly', 'quarterly', 'yearly'];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(bool $includeInactive = true): array
    {
        try {
            if (!is_file(base_path('config/installed.php'))) {
                return [];
            }

            $sql = 'SELECT * FROM membership_plans';
            if (!$includeInactive) {
                $sql .= ' WHERE is_active = 1';
            }
            $sql .= ' ORDER BY sort_order ASC, price_cents ASC, id ASC';

            $rows = $this->pdo()->query($sql)->fetchAll() ?: [];

            return array_map([$this, 'normalize'], $rows);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function activePlans(): array
    {
        return $this->all(false);
    }

    public function find(int $id): ?array
    {
        try {
            $stmt = $this->pdo()->prepare('SELECT * FROM membership_plans WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();

            return $row ? $this->normalize($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function findBySlug(string $slug): ?array
    {
        try {
            $stmt = $this->pdo()->prepare('SELECT * FROM membership_plans WHERE slug = :slug LIMIT 1');
            $stmt->execute(['slug' => strtolower(trim($slug))]);
            $row = $stmt->fetch();

            return $row ? $this->normalize($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function save(array $input): int
    {
        $id = (int) ($input['id'] ?? 0);
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('A plan name is required.');
        }

        $slug = $this->slug((string) ($input['slug'] ?? $name));
        if ($slug === '') {
            throw new \RuntimeException('A plan slug is required.');
        }

        $currency = strtoupper(substr(trim((string) ($input['currency'] ?? 'USD')), 0, 3));
        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new \RuntimeException('Currency must be a 3-letter code such as USD, EUR or GBP.');
        }

        $interval = (string) ($input['billing_interval'] ?? 'monthly');
        if (!in_array($interval, self::INTERVALS, true)) {
            $interval = 'monthly';
        }

        foreach (['stripe_payment_link', 'paypal_subscribe_url'] as $urlKey) {
            $value = trim((string) ($input[$urlKey] ?? ''));
            if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                throw new \RuntimeException('Stripe payment links and PayPal subscribe links must be valid URLs.');
            }
        }

        $stripePriceId = trim((string) ($input['stripe_price_id'] ?? ''));
        if ($stripePriceId !== '' && !preg_match('/^price_[A-Za-z0-9_]+$/', $stripePriceId)) {
            throw new \RuntimeException('Stripe price id should look like price_123.');
        }

        $params = [
            'slug' => $slug,
            'name' => $name,
            'tagline' => $this->nullable($input['tagline'] ?? null, 255),
            'description' => trim((string) ($input['description'] ?? '')) ?: null,
            'price_cents' => max(0, (int) round(((float) ($input['price'] ?? 0)) * 100)),
            'currency' => $currency,
            'billing_interval' => $interval,
            'trial_days' => max(0, (int) ($input['trial_days'] ?? 0)),
            'stripe_price_id' => $stripePriceId !== '' ? $stripePriceId : null,
            'stripe_payment_link' => trim((string) ($input['stripe_payment_link'] ?? '')) ?: null,
            'paypal_plan_id' => $this->nullable($input['paypal_plan_id'] ?? null, 190),
            'paypal_subscribe_url' => trim((string) ($input['paypal_subscribe_url'] ?? '')) ?: null,
            'features' => json_encode($this->parseFeatures($input['features'] ?? ''), JSON_UNESCAPED_SLASHES),
            'badge' => $this->nullable($input['badge'] ?? null, 60),
            'cta_label' => trim((string) ($input['cta_label'] ?? 'Get started')) ?: 'Get started',
            'is_featured' => !empty($input['is_featured']) ? 1 : 0,
            'is_active' => !empty($input['is_active']) ? 1 : 0,
            'sort_order' => max(0, (int) ($input['sort_order'] ?? 100)),
        ];

        $pdo = $this->pdo();

        if ($id > 0) {
            $params['id'] = $id;
            $stmt = $pdo->prepare(
                'UPDATE membership_plans SET
                    slug = :slug, name = :name, tagline = :tagline, description = :description,
                    price_cents = :price_cents, currency = :currency, billing_interval = :billing_interval,
                    trial_days = :trial_days, stripe_price_id = :stripe_price_id, stripe_payment_link = :stripe_payment_link,
                    paypal_plan_id = :paypal_plan_id, paypal_subscribe_url = :paypal_subscribe_url,
                    features = :features, badge = :badge, cta_label = :cta_label,
                    is_featured = :is_featured, is_active = :is_active, sort_order = :sort_order
                 WHERE id = :id'
            );
            $stmt->execute($params);

            return $id;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO membership_plans
                (slug, name, tagline, description, price_cents, currency, billing_interval, trial_days,
                 stripe_price_id, stripe_payment_link, paypal_plan_id, paypal_subscribe_url,
                 features, badge, cta_label, is_featured, is_active, sort_order)
             VALUES
                (:slug, :name, :tagline, :description, :price_cents, :currency, :billing_interval, :trial_days,
                 :stripe_price_id, :stripe_payment_link, :paypal_plan_id, :paypal_subscribe_url,
                 :features, :badge, :cta_label, :is_featured, :is_active, :sort_order)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name), tagline = VALUES(tagline), description = VALUES(description),
                price_cents = VALUES(price_cents), currency = VALUES(currency),
                billing_interval = VALUES(billing_interval), trial_days = VALUES(trial_days),
                stripe_price_id = VALUES(stripe_price_id), stripe_payment_link = VALUES(stripe_payment_link),
                paypal_plan_id = VALUES(paypal_plan_id), paypal_subscribe_url = VALUES(paypal_subscribe_url),
                features = VALUES(features), badge = VALUES(badge), cta_label = VALUES(cta_label),
                is_featured = VALUES(is_featured), is_active = VALUES(is_active), sort_order = VALUES(sort_order)'
        );
        $stmt->execute($params);

        $newId = (int) $pdo->lastInsertId();
        if ($newId > 0) {
            return $newId;
        }

        $lookup = $pdo->prepare('SELECT id FROM membership_plans WHERE slug = :slug LIMIT 1');
        $lookup->execute(['slug' => $slug]);

        return (int) $lookup->fetchColumn();
    }

    public function setActive(int $id, bool $active): void
    {
        $stmt = $this->pdo()->prepare('UPDATE membership_plans SET is_active = :active WHERE id = :id');
        $stmt->execute(['active' => $active ? 1 : 0, 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo()->prepare('DELETE FROM membership_plans WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function recordSignup(int $planId, string $email, ?string $name = null, string $provider = 'pending'): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO membership_signups (plan_id, email, name, provider, status)
             VALUES (:plan_id, :email, :name, :provider, :status)'
        );
        $stmt->execute([
            'plan_id' => $planId,
            'email' => strtolower(trim($email)),
            'name' => $name !== null ? trim($name) : null,
            'provider' => in_array($provider, ['stripe', 'paypal', 'manual', 'pending'], true) ? $provider : 'pending',
            'status' => 'pending',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $plan
     */
    public function formatPrice(array $plan): string
    {
        $amount = ((int) ($plan['price_cents'] ?? 0)) / 100;
        $symbol = $this->currencySymbol((string) ($plan['currency'] ?? 'USD'));
        $formatted = $amount == (int) $amount
            ? number_format($amount, 0)
            : number_format($amount, 2);

        return $symbol . $formatted;
    }

    public function intervalLabel(string $interval): string
    {
        return match ($interval) {
            'one_time' => 'one-time',
            'weekly' => '/week',
            'monthly' => '/month',
            'quarterly' => '/quarter',
            'yearly' => '/year',
            default => '',
        };
    }

    private function currencySymbol(string $currency): string
    {
        return match (strtoupper($currency)) {
            'USD', 'CAD', 'AUD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'INR' => '₹',
            default => '',
        };
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalize(array $row): array
    {
        $row['id'] = (int) ($row['id'] ?? 0);
        $row['price_cents'] = (int) ($row['price_cents'] ?? 0);
        $row['trial_days'] = (int) ($row['trial_days'] ?? 0);
        $row['is_active'] = (int) ($row['is_active'] ?? 0);
        $row['is_featured'] = (int) ($row['is_featured'] ?? 0);
        $row['sort_order'] = (int) ($row['sort_order'] ?? 100);

        $features = $row['features'] ?? null;
        if (is_string($features)) {
            $decoded = json_decode($features, true);
            $features = is_array($decoded) ? $decoded : [];
        }
        $row['features'] = is_array($features)
            ? array_values(array_filter(array_map('strval', $features), static fn (string $f): bool => trim($f) !== ''))
            : [];

        return $row;
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private function parseFeatures(mixed $value): array
    {
        if (is_array($value)) {
            $lines = $value;
        } else {
            $lines = preg_split('/\r\n|\r|\n/', (string) $value) ?: [];
        }

        $features = array_map('trim', $lines);

        return array_values(array_filter($features, static fn (string $line): bool => $line !== ''));
    }

    private function nullable(mixed $value, int $max): ?string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $max);
    }

    private function slug(string $value): string
    {
        $value = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $value));

        return trim($value, '-');
    }

    private function pdo(): PDO
    {
        return Database::connection();
    }
}
