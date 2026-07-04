<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class CommerceRepository
{
    public function upsertProduct(array $product): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO products
                (slug, title, summary, description, product_type, platform, platform_tags, price_cents, currency, private_file_path, snippet_html, snippet_css, snippet_js, checkout_url, paypal_checkout_url, stripe_price_id, is_active, sort_order)
             VALUES
                (:slug, :title, :summary, :description, :product_type, :platform, :platform_tags, :price_cents, :currency, :private_file_path, :snippet_html, :snippet_css, :snippet_js, :checkout_url, :paypal_checkout_url, :stripe_price_id, :is_active, :sort_order)
             ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                summary = VALUES(summary),
                description = VALUES(description),
                product_type = VALUES(product_type),
                platform = VALUES(platform),
                platform_tags = VALUES(platform_tags),
                price_cents = VALUES(price_cents),
                currency = VALUES(currency),
                private_file_path = VALUES(private_file_path),
                snippet_html = VALUES(snippet_html),
                snippet_css = VALUES(snippet_css),
                snippet_js = VALUES(snippet_js),
                checkout_url = VALUES(checkout_url),
                paypal_checkout_url = VALUES(paypal_checkout_url),
                stripe_price_id = VALUES(stripe_price_id),
                is_active = VALUES(is_active),
                sort_order = VALUES(sort_order)'
        );

        $stmt->execute([
            'slug' => $product['slug'],
            'title' => $product['title'],
            'summary' => $product['summary'],
            'description' => $product['description'] ?? null,
            'product_type' => $product['product_type'] ?? 'file',
            'platform' => $product['platform'] ?? 'Core PHP',
            'platform_tags' => json_encode(array_values($product['platform_tags'] ?? []), JSON_THROW_ON_ERROR),
            'price_cents' => (int) ($product['price_cents'] ?? 0),
            'currency' => strtoupper((string) ($product['currency'] ?? 'USD')),
            'private_file_path' => $product['private_file_path'] ?? null,
            'snippet_html' => $product['snippet_html'] ?? null,
            'snippet_css' => $product['snippet_css'] ?? null,
            'snippet_js' => $product['snippet_js'] ?? null,
            'checkout_url' => $product['checkout_url'] ?? null,
            'paypal_checkout_url' => $product['paypal_checkout_url'] ?? null,
            'stripe_price_id' => $product['stripe_price_id'] ?? null,
            'is_active' => !empty($product['is_active']) ? 1 : 0,
            'sort_order' => (int) ($product['sort_order'] ?? 100),
        ]);

        $id = (int) $this->pdo()->lastInsertId();
        if ($id > 0) {
            return $id;
        }

        $lookup = $this->pdo()->prepare('SELECT id FROM products WHERE slug = :slug LIMIT 1');
        $lookup->execute(['slug' => $product['slug']]);

        return (int) $lookup->fetchColumn();
    }

    public function saveProductAsset(array $asset): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO product_assets
                (product_id, asset_type, storage_driver, storage_path, original_name, mime_type, size_bytes, checksum_sha256, version_label, is_active)
             VALUES
                (:product_id, :asset_type, :storage_driver, :storage_path, :original_name, :mime_type, :size_bytes, :checksum_sha256, :version_label, :is_active)'
        );
        $stmt->execute([
            'product_id' => (int) $asset['product_id'],
            'asset_type' => $asset['asset_type'] ?? 'download',
            'storage_driver' => $asset['storage_driver'] ?? 'private',
            'storage_path' => $asset['storage_path'],
            'original_name' => $asset['original_name'],
            'mime_type' => $asset['mime_type'] ?? 'application/zip',
            'size_bytes' => (int) ($asset['size_bytes'] ?? 0),
            'checksum_sha256' => $asset['checksum_sha256'],
            'version_label' => $asset['version_label'] ?? '1.0.0',
            'is_active' => !empty($asset['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function activeProductAsset(int $productId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM product_assets WHERE product_id = :product_id AND is_active = 1 ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['product_id' => $productId]);
        $asset = $stmt->fetch();

        return $asset ?: null;
    }

    public function upsertCustomer(array $customer): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO commerce_customers (email, name, company, vat_number, billing_address, shipping_address, user_id)
             VALUES (:email, :name, :company, :vat_number, :billing_address, :shipping_address, :user_id)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                company = VALUES(company),
                vat_number = VALUES(vat_number),
                billing_address = VALUES(billing_address),
                shipping_address = VALUES(shipping_address),
                user_id = COALESCE(VALUES(user_id), user_id)'
        );
        $stmt->execute([
            'email' => strtolower((string) $customer['email']),
            'name' => $customer['name'] ?? null,
            'company' => $customer['company'] ?? null,
            'vat_number' => $customer['vat_number'] ?? null,
            'billing_address' => json_encode($customer['billing_address'] ?? null),
            'shipping_address' => json_encode($customer['shipping_address'] ?? null),
            'user_id' => $customer['user_id'] ?? null,
        ]);

        $id = (int) $this->pdo()->lastInsertId();
        if ($id > 0) {
            return $id;
        }

        $lookup = $this->pdo()->prepare('SELECT id FROM commerce_customers WHERE email = :email LIMIT 1');
        $lookup->execute(['email' => strtolower((string) $customer['email'])]);

        return (int) $lookup->fetchColumn();
    }

    public function createOrder(array $order, array $items): array
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $customerId = $this->upsertCustomer($order['customer']);
            $reference = $order['order_reference'] ?? $this->orderReference();
            $subtotal = array_sum(array_map(static fn (array $item): int => (int) $item['total_cents'], $items));
            $tax = (int) ($order['tax_cents'] ?? 0);
            $total = $subtotal + $tax;

            $stmt = $pdo->prepare(
                'INSERT INTO commerce_orders
                    (order_reference, customer_id, status, subtotal_cents, tax_cents, total_cents, currency, gateway_provider, gateway_session_id, ip_address, user_agent, metadata)
                 VALUES
                    (:order_reference, :customer_id, :status, :subtotal_cents, :tax_cents, :total_cents, :currency, :gateway_provider, :gateway_session_id, :ip_address, :user_agent, :metadata)'
            );
            $stmt->execute([
                'order_reference' => $reference,
                'customer_id' => $customerId,
                'status' => $order['status'] ?? 'pending_payment',
                'subtotal_cents' => $subtotal,
                'tax_cents' => $tax,
                'total_cents' => $total,
                'currency' => strtoupper((string) ($order['currency'] ?? 'USD')),
                'gateway_provider' => $order['gateway_provider'] ?? 'stripe',
                'gateway_session_id' => $order['gateway_session_id'] ?? null,
                'ip_address' => $order['ip_address'] ?? null,
                'user_agent' => $order['user_agent'] ?? null,
                'metadata' => json_encode($order['metadata'] ?? null),
            ]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO commerce_order_items
                    (order_id, product_id, product_asset_id, quantity, unit_price_cents, tax_cents, total_cents, metadata)
                 VALUES
                    (:order_id, :product_id, :product_asset_id, :quantity, :unit_price_cents, :tax_cents, :total_cents, :metadata)'
            );

            foreach ($items as $item) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => (int) $item['product_id'],
                    'product_asset_id' => $item['product_asset_id'] ?? null,
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'unit_price_cents' => (int) $item['unit_price_cents'],
                    'tax_cents' => (int) ($item['tax_cents'] ?? 0),
                    'total_cents' => (int) $item['total_cents'],
                    'metadata' => json_encode($item['metadata'] ?? null),
                ]);
            }

            $pdo->commit();
            return ['id' => $orderId, 'order_reference' => $reference, 'total_cents' => $total];
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function recordTransaction(array $transaction): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO commerce_transactions
                (order_id, provider, provider_event_id, provider_payment_id, status, amount_cents, currency, raw_payload)
             VALUES
                (:order_id, :provider, :provider_event_id, :provider_payment_id, :status, :amount_cents, :currency, :raw_payload)
             ON DUPLICATE KEY UPDATE status = VALUES(status), raw_payload = VALUES(raw_payload)'
        );
        $stmt->execute([
            'order_id' => (int) $transaction['order_id'],
            'provider' => $transaction['provider'],
            'provider_event_id' => $transaction['provider_event_id'] ?? null,
            'provider_payment_id' => $transaction['provider_payment_id'] ?? null,
            'status' => $transaction['status'],
            'amount_cents' => (int) $transaction['amount_cents'],
            'currency' => strtoupper((string) ($transaction['currency'] ?? 'USD')),
            'raw_payload' => json_encode($transaction['raw_payload'] ?? null),
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function createDownloadGrant(int $orderItemId, int $productAssetId, int $ttlHours, int $maxDownloads): array
    {
        $token = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
        $expiresAt = gmdate('Y-m-d H:i:s', time() + max(1, $ttlHours) * 3600);

        $stmt = $this->pdo()->prepare(
            'INSERT INTO download_grants (order_item_id, product_asset_id, token_hash, expires_at, max_downloads)
             VALUES (:order_item_id, :product_asset_id, :token_hash, :expires_at, :max_downloads)'
        );
        $stmt->execute([
            'order_item_id' => $orderItemId,
            'product_asset_id' => $productAssetId,
            'token_hash' => hash('sha256', $token),
            'expires_at' => $expiresAt,
            'max_downloads' => max(1, $maxDownloads),
        ]);

        return [
            'id' => (int) $this->pdo()->lastInsertId(),
            'token' => $token,
            'expires_at' => $expiresAt,
        ];
    }

    private function orderReference(): string
    {
        return 'CWM-' . strtoupper(bin2hex(random_bytes(4))) . '-' . gmdate('ymd');
    }

    private function pdo(): PDO
    {
        return Database::connection();
    }
}
