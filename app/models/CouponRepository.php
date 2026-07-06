<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Backend-managed discount coupons. JSON-backed (no database required) and
 * applied on the order forms for backlinks, care plans, audits and speed
 * rescue: the code is validated server-side, the discounted price is recorded
 * on the order ticket, and usage is counted. For hosted Stripe payment links,
 * create the matching promotion code inside Stripe as well.
 */
final class CouponRepository
{
    public const CONTEXTS = ['all', 'membership', 'backlinks', 'care-plans', 'audit', 'speed-rescue'];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $path = $this->path();
        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(array_map([$this, 'normalize'], array_filter($decoded, 'is_array'))));
    }

    public function find(string $code): ?array
    {
        $code = $this->code($code);
        foreach ($this->all() as $coupon) {
            if ($coupon['code'] === $code) {
                return $coupon;
            }
        }

        return null;
    }

    /**
     * Validate a coupon for a given context ('backlinks', 'care-plans', ...).
     *
     * @return array{ok: bool, coupon: array<string, mixed>|null, message: string}
     */
    public function validate(string $code, string $context): array
    {
        $code = $this->code($code);
        if ($code === '') {
            return ['ok' => false, 'coupon' => null, 'message' => ''];
        }

        $coupon = $this->find($code);
        if ($coupon === null || empty($coupon['active'])) {
            return ['ok' => false, 'coupon' => null, 'message' => 'That coupon code is not valid.'];
        }

        if (($coupon['applies_to'] ?? 'all') !== 'all' && $coupon['applies_to'] !== $context) {
            return ['ok' => false, 'coupon' => null, 'message' => 'That coupon does not apply to this product.'];
        }

        $expires = (string) ($coupon['expires_at'] ?? '');
        if ($expires !== '') {
            $ts = strtotime($expires . ' 23:59:59');
            if ($ts !== false && $ts < time()) {
                return ['ok' => false, 'coupon' => null, 'message' => 'That coupon has expired.'];
            }
        }

        $maxUses = (int) ($coupon['max_uses'] ?? 0);
        if ($maxUses > 0 && (int) ($coupon['used_count'] ?? 0) >= $maxUses) {
            return ['ok' => false, 'coupon' => null, 'message' => 'That coupon has reached its usage limit.'];
        }

        return ['ok' => true, 'coupon' => $coupon, 'message' => ''];
    }

    /**
     * Apply a coupon to a "€59"-style price string.
     *
     * @param array<string, mixed> $coupon
     * @return array{original: string, discounted: string, label: string}
     */
    public function applyToPrice(array $coupon, string $price): array
    {
        $amount = (float) preg_replace('/[^0-9.]/', '', $price);
        $value = (float) ($coupon['value'] ?? 0);

        $discounted = ($coupon['type'] ?? 'percent') === 'percent'
            ? $amount * (1 - min(100, $value) / 100)
            : max(0, $amount - $value);

        $label = ($coupon['type'] ?? 'percent') === 'percent'
            ? number_format($value, $value == (int) $value ? 0 : 2) . '% off'
            : '€' . number_format($value, $value == (int) $value ? 0 : 2) . ' off';

        return [
            'original' => $price,
            'discounted' => '€' . number_format($discounted, $discounted == (int) $discounted ? 0 : 2),
            'label' => $label,
        ];
    }

    public function redeem(string $code): void
    {
        $code = $this->code($code);
        $coupons = $this->all();
        foreach ($coupons as $index => $coupon) {
            if ($coupon['code'] === $code) {
                $coupons[$index]['used_count'] = (int) ($coupon['used_count'] ?? 0) + 1;
            }
        }
        $this->persist($coupons);
    }

    public function save(array $input): string
    {
        $code = $this->code((string) ($input['code'] ?? ''));
        if ($code === '' || strlen($code) < 3) {
            throw new \RuntimeException('Coupon codes need at least 3 letters/numbers.');
        }

        $type = ($input['type'] ?? 'percent') === 'fixed' ? 'fixed' : 'percent';
        $value = (float) ($input['value'] ?? 0);
        if ($value <= 0 || ($type === 'percent' && $value > 100)) {
            throw new \RuntimeException($type === 'percent' ? 'Percent discounts must be between 1 and 100.' : 'Fixed discounts must be above zero.');
        }

        $appliesTo = (string) ($input['applies_to'] ?? 'all');
        if (!in_array($appliesTo, self::CONTEXTS, true)) {
            $appliesTo = 'all';
        }

        $expires = trim((string) ($input['expires_at'] ?? ''));
        if ($expires !== '' && strtotime($expires) === false) {
            throw new \RuntimeException('Enter a valid expiry date.');
        }

        $existing = $this->find($code);
        $coupon = [
            'code' => $code,
            'type' => $type,
            'value' => $value,
            'applies_to' => $appliesTo,
            'max_uses' => max(0, (int) ($input['max_uses'] ?? 0)),
            'used_count' => (int) ($existing['used_count'] ?? 0),
            'expires_at' => $expires !== '' ? date('Y-m-d', (int) strtotime($expires)) : '',
            'active' => !empty($input['active']),
            'created_at' => $existing['created_at'] ?? gmdate('c'),
        ];

        $coupons = $this->all();
        $replaced = false;
        foreach ($coupons as $index => $row) {
            if ($row['code'] === $code) {
                $coupons[$index] = $coupon;
                $replaced = true;
                break;
            }
        }
        if (!$replaced) {
            array_unshift($coupons, $coupon);
        }

        $this->persist($coupons);

        return $code;
    }

    public function delete(string $code): void
    {
        $code = $this->code($code);
        $this->persist(array_values(array_filter($this->all(), static fn (array $c): bool => $c['code'] !== $code)));
    }

    public function setActive(string $code, bool $active): void
    {
        $code = $this->code($code);
        $coupons = $this->all();
        foreach ($coupons as $index => $coupon) {
            if ($coupon['code'] === $code) {
                $coupons[$index]['active'] = $active;
            }
        }
        $this->persist($coupons);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    private function normalize(array $row): ?array
    {
        $code = $this->code((string) ($row['code'] ?? ''));
        if ($code === '') {
            return null;
        }

        return [
            'code' => $code,
            'type' => ($row['type'] ?? 'percent') === 'fixed' ? 'fixed' : 'percent',
            'value' => (float) ($row['value'] ?? 0),
            'applies_to' => in_array($row['applies_to'] ?? 'all', self::CONTEXTS, true) ? $row['applies_to'] : 'all',
            'max_uses' => max(0, (int) ($row['max_uses'] ?? 0)),
            'used_count' => max(0, (int) ($row['used_count'] ?? 0)),
            'expires_at' => (string) ($row['expires_at'] ?? ''),
            'active' => array_key_exists('active', $row) ? !empty($row['active']) : true,
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }

    private function code(string $value): string
    {
        return strtoupper((string) preg_replace('/[^A-Z0-9-]/i', '', $value));
    }

    private function persist(array $coupons): void
    {
        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $this->path(),
            json_encode(array_values($coupons), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    private function path(): string
    {
        return base_path('storage/data/coupons.json');
    }
}
