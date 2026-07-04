<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class CmsRepository
{
    public function saveThemeAsset(array $asset): int
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $deactivate = $pdo->prepare(
                'UPDATE theme_assets SET is_active = 0 WHERE asset_key = :asset_key AND variant = :variant'
            );
            $deactivate->execute([
                'asset_key' => $asset['asset_key'],
                'variant' => $asset['variant'],
            ]);

            $stmt = $pdo->prepare(
                'INSERT INTO theme_assets
                    (asset_key, variant, disk, path, original_name, mime_type, size_bytes, checksum_sha256, width, height, uploaded_by)
                 VALUES
                    (:asset_key, :variant, :disk, :path, :original_name, :mime_type, :size_bytes, :checksum_sha256, :width, :height, :uploaded_by)'
            );
            $stmt->execute([
                'asset_key' => $asset['asset_key'],
                'variant' => $asset['variant'],
                'disk' => $asset['disk'] ?? 'public',
                'path' => $asset['path'],
                'original_name' => $asset['original_name'],
                'mime_type' => $asset['mime_type'],
                'size_bytes' => (int) $asset['size_bytes'],
                'checksum_sha256' => $asset['checksum_sha256'],
                'width' => $asset['width'] ?? null,
                'height' => $asset['height'] ?? null,
                'uploaded_by' => $asset['uploaded_by'] ?? null,
            ]);

            $id = (int) $pdo->lastInsertId();
            $pdo->commit();
            return $id;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function activeThemeAssets(): array
    {
        $stmt = $this->pdo()->query(
            'SELECT * FROM theme_assets WHERE is_active = 1 ORDER BY asset_key ASC, variant ASC'
        );

        return $stmt->fetchAll() ?: [];
    }

    public function upsertTypographyRule(array $rule): void
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO typography_rules
                (selector_key, css_selector, font_family, font_source, font_weights, fallback_stack, font_display, is_enabled, sort_order, updated_by)
             VALUES
                (:selector_key, :css_selector, :font_family, :font_source, :font_weights, :fallback_stack, :font_display, :is_enabled, :sort_order, :updated_by)
             ON DUPLICATE KEY UPDATE
                css_selector = VALUES(css_selector),
                font_family = VALUES(font_family),
                font_source = VALUES(font_source),
                font_weights = VALUES(font_weights),
                fallback_stack = VALUES(fallback_stack),
                font_display = VALUES(font_display),
                is_enabled = VALUES(is_enabled),
                sort_order = VALUES(sort_order),
                updated_by = VALUES(updated_by)'
        );

        $stmt->execute([
            'selector_key' => $rule['selector_key'],
            'css_selector' => $rule['css_selector'],
            'font_family' => $rule['font_family'],
            'font_source' => $rule['font_source'] ?? 'google',
            'font_weights' => json_encode(array_values($rule['font_weights'] ?? [400]), JSON_THROW_ON_ERROR),
            'fallback_stack' => $rule['fallback_stack'],
            'font_display' => $rule['font_display'] ?? 'swap',
            'is_enabled' => !empty($rule['is_enabled']) ? 1 : 0,
            'sort_order' => (int) ($rule['sort_order'] ?? 100),
            'updated_by' => $rule['updated_by'] ?? null,
        ]);
    }

    public function typographyRules(): array
    {
        $stmt = $this->pdo()->query(
            'SELECT * FROM typography_rules WHERE is_enabled = 1 ORDER BY sort_order ASC, selector_key ASC'
        );

        $rules = $stmt->fetchAll() ?: [];
        foreach ($rules as &$rule) {
            $decoded = json_decode((string) ($rule['font_weights'] ?? '[]'), true);
            $rule['font_weights'] = is_array($decoded) ? $decoded : [];
        }

        return $rules;
    }

    public function saveStyleCache(string $cacheKey, string $css): void
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO theme_style_cache (cache_key, css, checksum_sha256, built_at)
             VALUES (:cache_key, :css, :checksum_sha256, UTC_TIMESTAMP())
             ON DUPLICATE KEY UPDATE
                css = VALUES(css),
                checksum_sha256 = VALUES(checksum_sha256),
                built_at = VALUES(built_at)'
        );
        $stmt->execute([
            'cache_key' => $cacheKey,
            'css' => $css,
            'checksum_sha256' => hash('sha256', $css),
        ]);
    }

    public function cachedStyle(string $cacheKey = 'global-font-map'): ?string
    {
        $stmt = $this->pdo()->prepare('SELECT css FROM theme_style_cache WHERE cache_key = :cache_key LIMIT 1');
        $stmt->execute(['cache_key' => $cacheKey]);
        $css = $stmt->fetchColumn();

        return is_string($css) && $css !== '' ? $css : null;
    }

    public function saveScriptInjection(array $payload): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO script_injections
                (name, location, code, code_hash, load_strategy, is_enabled, approved_by)
             VALUES
                (:name, :location, :code, :code_hash, :load_strategy, :is_enabled, :approved_by)'
        );
        $stmt->execute([
            'name' => $payload['name'],
            'location' => $payload['location'],
            'code' => $payload['code'],
            'code_hash' => hash('sha256', (string) $payload['code']),
            'load_strategy' => $payload['load_strategy'] ?? 'inline',
            'is_enabled' => !empty($payload['is_enabled']) ? 1 : 0,
            'approved_by' => $payload['approved_by'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function scriptInjections(string $location): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM script_injections WHERE location = :location AND is_enabled = 1 ORDER BY id ASC'
        );
        $stmt->execute(['location' => $location]);

        return $stmt->fetchAll() ?: [];
    }

    public function saveCustomCodeAsset(array $payload): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO custom_code_assets
                (asset_type, name, code, compiled_path, load_strategy, checksum_sha256, is_enabled, updated_by)
             VALUES
                (:asset_type, :name, :code, :compiled_path, :load_strategy, :checksum_sha256, :is_enabled, :updated_by)'
        );
        $stmt->execute([
            'asset_type' => $payload['asset_type'],
            'name' => $payload['name'],
            'code' => $payload['code'],
            'compiled_path' => $payload['compiled_path'] ?? null,
            'load_strategy' => $payload['load_strategy'] ?? 'inline',
            'checksum_sha256' => hash('sha256', (string) $payload['code']),
            'is_enabled' => !empty($payload['is_enabled']) ? 1 : 0,
            'updated_by' => $payload['updated_by'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function enabledCustomCodeAssets(string $assetType): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM custom_code_assets WHERE asset_type = :asset_type AND is_enabled = 1 ORDER BY id ASC'
        );
        $stmt->execute(['asset_type' => $assetType]);

        return $stmt->fetchAll() ?: [];
    }

    private function pdo(): PDO
    {
        return Database::connection();
    }
}
