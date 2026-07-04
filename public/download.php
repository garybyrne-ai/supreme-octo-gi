<?php

declare(strict_types=1);

require dirname(__DIR__) . '/config/database.php';

$shopConfig = require dirname(__DIR__) . '/config/shop.php';
$token = (string) ($_GET['token'] ?? '');

if (!preg_match('/^[A-Za-z0-9_-]{40,140}$/', $token)) {
    failDownload(404);
}

try {
    $pdo = \Config\Database::connection();
    $hash = hash('sha256', $token);

    $enterpriseGrant = resolveEnterpriseGrant($pdo, $hash, $shopConfig);
    if ($enterpriseGrant !== null) {
        streamZip($enterpriseGrant['file'], (string) $enterpriseGrant['slug']);
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare(
        'SELECT secure_links.id AS link_id, secure_links.expires_at, products.title, products.slug, products.private_file_path
         FROM secure_links
         INNER JOIN products ON products.id = secure_links.product_id
         INNER JOIN purchases ON purchases.id = secure_links.purchase_id
         WHERE secure_links.token_hash = :token_hash
           AND secure_links.used_at IS NULL
           AND secure_links.expires_at > UTC_TIMESTAMP()
           AND purchases.status = "paid"
           AND products.product_type = "file"
           AND products.is_active = 1
         LIMIT 1
         FOR UPDATE'
    );
    $stmt->execute(['token_hash' => $hash]);
    $row = $stmt->fetch();

    if (!$row) {
        $pdo->rollBack();
        failDownload(404);
    }

    $file = resolvePrivateZip((string) $shopConfig['private_dir'], (string) $row['private_file_path']);
    if ($file === null) {
        $pdo->rollBack();
        failDownload(404);
    }

    $update = $pdo->prepare('UPDATE secure_links SET used_at = UTC_TIMESTAMP(), download_count = download_count + 1 WHERE id = :id AND used_at IS NULL');
    $update->execute(['id' => (int) $row['link_id']]);
    if ($update->rowCount() !== 1) {
        $pdo->rollBack();
        failDownload(409);
    }
    $pdo->commit();

    streamZip($file, (string) ($row['slug'] ?: 'crest-code-package'));
} catch (\Throwable) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    failDownload(500);
}

function resolvePrivateZip(string $basePath, string $relativePath): ?string
{
    $base = realpath($basePath);
    if ($base === false || $relativePath === '') {
        return null;
    }

    $relativePath = str_replace(["\0", '/', '\\'], ['', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $relativePath);
    $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);
    $target = realpath($base . DIRECTORY_SEPARATOR . $relativePath);

    if ($target === false || !is_file($target)) {
        return null;
    }

    $baseWithSlash = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (!str_starts_with($target, $baseWithSlash) || strtolower(pathinfo($target, PATHINFO_EXTENSION)) !== 'zip') {
        return null;
    }

    return $target;
}

function resolveEnterpriseGrant(PDO $pdo, string $tokenHash, array $shopConfig): ?array
{
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            'SELECT
                download_grants.id AS grant_id,
                download_grants.max_downloads,
                download_grants.download_count,
                product_assets.storage_driver,
                product_assets.storage_path,
                products.slug
             FROM download_grants
             INNER JOIN product_assets ON product_assets.id = download_grants.product_asset_id
             INNER JOIN commerce_order_items ON commerce_order_items.id = download_grants.order_item_id
             INNER JOIN commerce_orders ON commerce_orders.id = commerce_order_items.order_id
             INNER JOIN products ON products.id = commerce_order_items.product_id
             WHERE download_grants.token_hash = :token_hash
               AND download_grants.revoked_at IS NULL
               AND download_grants.expires_at > UTC_TIMESTAMP()
               AND download_grants.download_count < download_grants.max_downloads
               AND commerce_orders.status IN ("paid", "processing", "fulfilled")
               AND product_assets.is_active = 1
             LIMIT 1
             FOR UPDATE'
        );
        $stmt->execute(['token_hash' => $tokenHash]);
        $row = $stmt->fetch();

        if (!$row) {
            $pdo->rollBack();
            return null;
        }

        if ((string) $row['storage_driver'] !== 'private') {
            $pdo->rollBack();
            failDownload(501);
        }

        $file = resolvePrivateZip((string) $shopConfig['private_dir'], (string) $row['storage_path']);
        if ($file === null) {
            $pdo->rollBack();
            failDownload(404);
        }

        $update = $pdo->prepare(
            'UPDATE download_grants
             SET download_count = download_count + 1
             WHERE id = :id
               AND revoked_at IS NULL
               AND download_count < max_downloads'
        );
        $update->execute(['id' => (int) $row['grant_id']]);
        if ($update->rowCount() !== 1) {
            $pdo->rollBack();
            failDownload(409);
        }

        $event = $pdo->prepare(
            'INSERT INTO download_events (download_grant_id, ip_address, user_agent)
             VALUES (:download_grant_id, :ip_address, :user_agent)'
        );
        $event->execute([
            'download_grant_id' => (int) $row['grant_id'],
            'ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        ]);

        $pdo->commit();
        return ['file' => $file, 'slug' => (string) $row['slug']];
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if (str_contains($exception->getMessage(), 'download_grants')) {
            return null;
        }

        throw $exception;
    }
}

function streamZip(string $path, string $slug): never
{
    $filename = preg_replace('/[^a-z0-9_-]+/i', '-', $slug) ?: 'crest-code-package';
    $filename = strtolower(trim($filename, '-')) . '.zip';

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($path));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, private');
    header('Pragma: no-cache');

    $handle = fopen($path, 'rb');
    if ($handle === false) {
        failDownload(500);
    }

    while (!feof($handle)) {
        echo fread($handle, 1048576);
        flush();
    }

    fclose($handle);
    exit;
}

function failDownload(int $status): never
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo $status === 404 ? 'Download link not found or expired.' : 'Download could not be completed.';
    exit;
}
