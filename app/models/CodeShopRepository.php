<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class CodeShopRepository
{
    public function products(): array
    {
        try {
            if (!is_file(base_path('config/installed.php'))) {
                return $this->fallbackProducts();
            }

            $stmt = $this->pdo()->query(
                'SELECT * FROM products WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC'
            );

            $products = $stmt->fetchAll() ?: [];
            return $products === [] ? $this->fallbackProducts() : array_map([$this, 'normalizeProduct'], $products);
        } catch (\Throwable) {
            return $this->fallbackProducts();
        }
    }

    public function findBySlug(string $slug): ?array
    {
        $slug = strtolower(trim($slug));

        try {
            if (is_file(base_path('config/installed.php'))) {
                $stmt = $this->pdo()->prepare('SELECT * FROM products WHERE slug = :slug AND is_active = 1 LIMIT 1');
                $stmt->execute(['slug' => $slug]);
                $product = $stmt->fetch();
                if ($product) {
                    return $this->normalizeProduct($product);
                }
            }
        } catch (\Throwable) {
        }

        foreach ($this->fallbackProducts() as $product) {
            if ($product['slug'] === $slug) {
                return $product;
            }
        }

        return null;
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->pdo()->prepare('SELECT * FROM products WHERE id = :id AND is_active = 1 LIMIT 1');
            $stmt->execute(['id' => $id]);
            $product = $stmt->fetch();
            return $product ? $this->normalizeProduct($product) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function recordStripePurchase(array $session, array $event, array $product): array
    {
        $pdo = $this->pdo();
        $sessionId = (string) ($session['id'] ?? '');
        if ($sessionId === '') {
            throw new \RuntimeException('Stripe session id is missing.');
        }

        $existing = $this->purchaseBySession($sessionId);
        if ($existing !== null) {
            return $existing;
        }

        $email = strtolower(trim((string) ($session['customer_details']['email'] ?? $session['customer_email'] ?? '')));
        $name = trim((string) ($session['customer_details']['name'] ?? 'Code Shop Customer'));
        $currency = strtoupper((string) ($session['currency'] ?? $product['currency'] ?? 'USD'));
        $amount = (int) ($session['amount_total'] ?? $product['price_cents'] ?? 0);

        $stmt = $pdo->prepare(
            'INSERT INTO purchases
                (product_id, customer_email, customer_name, payment_provider, provider_session_id, provider_payment_id, amount_cents, currency, status, raw_payload)
             VALUES
                (:product_id, :customer_email, :customer_name, :payment_provider, :provider_session_id, :provider_payment_id, :amount_cents, :currency, :status, :raw_payload)'
        );
        $stmt->execute([
            'product_id' => (int) $product['id'],
            'customer_email' => $email,
            'customer_name' => $name,
            'payment_provider' => 'stripe',
            'provider_session_id' => $sessionId,
            'provider_payment_id' => (string) ($session['payment_intent'] ?? ''),
            'amount_cents' => $amount,
            'currency' => $currency,
            'status' => 'paid',
            'raw_payload' => json_encode($event, JSON_UNESCAPED_SLASHES),
        ]);

        return [
            'id' => (int) $pdo->lastInsertId(),
            'product_id' => (int) $product['id'],
            'customer_email' => $email,
            'customer_name' => $name,
            'status' => 'paid',
        ];
    }

    public function createSecureLink(int $purchaseId, int $productId, int $ttlHours = 24): array
    {
        $existing = $this->activeLinkForPurchase($purchaseId);
        if ($existing !== null) {
            return $existing + ['token' => null];
        }

        $token = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
        $hash = hash('sha256', $token);
        $expiresAt = gmdate('Y-m-d H:i:s', time() + ($ttlHours * 3600));

        $stmt = $this->pdo()->prepare(
            'INSERT INTO secure_links (purchase_id, product_id, token_hash, expires_at)
             VALUES (:purchase_id, :product_id, :token_hash, :expires_at)'
        );
        $stmt->execute([
            'purchase_id' => $purchaseId,
            'product_id' => $productId,
            'token_hash' => $hash,
            'expires_at' => $expiresAt,
        ]);

        return [
            'id' => (int) $this->pdo()->lastInsertId(),
            'token' => $token,
            'expires_at' => $expiresAt,
        ];
    }

    public function safeSnippetSrcdoc(array $product): string
    {
        $html = $this->sanitizeSnippet((string) ($product['snippet_html'] ?? ''));
        $css = $this->sanitizeCss((string) ($product['snippet_css'] ?? ''));

        return '<!doctype html><html><head><meta charset="utf-8"><style>body{margin:0;background:#030712;color:#f3f8ff;font-family:Arial,sans-serif;display:grid;place-items:center;min-height:100vh;padding:24px}' . $css . '</style></head><body>' . $html . '</body></html>';
    }

    private function purchaseBySession(string $sessionId): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM purchases WHERE provider_session_id = :session_id LIMIT 1');
        $stmt->execute(['session_id' => $sessionId]);
        $purchase = $stmt->fetch();
        return $purchase ?: null;
    }

    private function activeLinkForPurchase(int $purchaseId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id, expires_at FROM secure_links
             WHERE purchase_id = :purchase_id AND used_at IS NULL AND expires_at > UTC_TIMESTAMP()
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['purchase_id' => $purchaseId]);
        $link = $stmt->fetch();
        return $link ?: null;
    }

    private function normalizeProduct(array $product): array
    {
        $product['price_cents'] = (int) ($product['price_cents'] ?? 0);
        $product['platform_tags'] = $this->decodeTags($product['platform_tags'] ?? []);
        return $product;
    }

    private function decodeTags(mixed $tags): array
    {
        if (is_array($tags)) {
            return $tags;
        }

        $decoded = json_decode((string) $tags, true);
        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }

    private function sanitizeSnippet(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? '';
        $html = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? '';
        return $html;
    }

    private function sanitizeCss(string $css): string
    {
        $css = preg_replace('/@import[^;]+;/i', '', $css) ?? '';
        $css = preg_replace('/expression\s*\(|javascript\s*:/i', '', $css) ?? '';
        return $css;
    }

    private function pdo(): PDO
    {
        return Database::connection();
    }

    private function fallbackProducts(): array
    {
        return [
            [
                'id' => 1,
                'slug' => 'photo-to-key-php-website-backend',
                'title' => 'Photo To Key PHP Ordering Website With Backend',
                'summary' => 'A camera-first key ordering product with PHP backend, browser upload flow, checkout hooks, shipping, VAT fields and Core Web Vitals-ready UI.',
                'description' => 'Sell a modern Photo To Key webapp for locksmiths and key-duplication services. Customers can open the camera from the browser, take or upload a key photo, submit delivery details and move through secure payment flows. The package includes a PHP/MySQL backend, order workflow, admin-ready structure, PayPal and Stripe integration points, VAT and shipping fields, trust sections, tracking language and a futuristic conversion-focused interface.',
                'product_type' => 'file',
                'platform' => 'Core PHP',
                'platform_tags' => ['PHP 8', 'MySQL', 'Browser Camera', 'PayPal', 'Stripe', 'VAT Logic', 'Shipping Flow', 'Core Web Vitals'],
                'price_cents' => 4900,
                'currency' => 'USD',
                'private_file_path' => 'photo-to-key-php-website-backend.zip',
                'snippet_html' => null,
                'snippet_css' => null,
                'snippet_js' => null,
                'checkout_url' => '/contact?product=photo-to-key',
                'paypal_checkout_url' => '',
                'stripe_price_id' => '',
                'is_active' => 1,
                'sort_order' => 1,
            ],
        ];
    }
}
