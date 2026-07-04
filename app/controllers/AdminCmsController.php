<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Services\AuditLogger;
use App\Services\CmsThemeAssetManager;
use App\Services\CustomCodeCompiler;
use App\Services\DigitalCommerceEngine;
use App\Services\MigrationService;
use App\Services\ScriptInjectionGuard;
use App\Services\TypographyEngine;

final class AdminCmsController extends Controller
{
    public function fontOptions(): void
    {
        $this->requireAdmin();
        $engine = new TypographyEngine();

        $this->json([
            'fonts' => $engine->verifiedGoogleFonts(),
            'selectors' => $engine->selectorMap(),
        ]);
    }

    public function addMissingTables(): void
    {
        $this->requireAdmin();
        $this->verifyToken('/admin/dashboard');

        try {
            $result = (new MigrationService())->addMissingTables();
            (new AuditLogger())->log('cms.migrations.add_missing_tables', $result);

            $tableCount = count($result['tables']);
            $message = $tableCount > 0
                ? 'Missing CMS tables checked. ' . $tableCount . ' enterprise tables are now available.'
                : 'Missing CMS tables checked. No new tables were required.';

            if (!empty($result['warnings'])) {
                $message .= ' Legacy database constraints were detected, so compatible table creation was used where needed.';
            }

            $this->respondSuccess($message, $result, '/admin/dashboard');
        } catch (\Throwable $exception) {
            (new AuditLogger())->log('cms.migrations.failed', ['error' => $exception->getMessage()]);
            $this->respondFailure('Could not add missing tables: ' . $exception->getMessage(), '/admin/dashboard', 500);
        }
    }

    public function uploadThemeAsset(): void
    {
        $this->requireAdmin();
        $this->verifyToken('/admin/modules/theme');

        try {
            $asset = (new CmsThemeAssetManager())->uploadBrandAsset(
                $_FILES['asset'] ?? [],
                (string) ($_POST['asset_key'] ?? 'brand'),
                (string) ($_POST['variant'] ?? 'primary'),
                $this->adminUserId()
            );

            (new AuditLogger())->log('cms.theme_asset.uploaded', [
                'asset_key' => $asset['asset_key'],
                'variant' => $asset['variant'],
                'path' => $asset['path'],
            ]);
            $this->respondSuccess('Theme asset uploaded and validated.', $asset, '/admin/modules/theme');
        } catch (\Throwable $exception) {
            $this->respondFailure($exception->getMessage(), '/admin/modules/theme');
        }
    }

    public function saveTypography(): void
    {
        $this->requireAdmin();
        $this->verifyToken('/admin/modules/theme');

        try {
            $rules = $this->inputRules();
            $css = (new TypographyEngine())->saveRules($rules, $this->adminUserId());
            (new AuditLogger())->log('cms.typography.rebuilt', ['rules' => array_keys($rules)]);
            $this->respondSuccess('Typography rules saved and cache rebuilt.', [
                'css_bytes' => strlen($css),
            ], '/admin/modules/theme');
        } catch (\Throwable $exception) {
            $this->respondFailure($exception->getMessage(), '/admin/modules/theme');
        }
    }

    public function saveScriptInjection(): void
    {
        $this->requireAdmin();
        $this->verifyToken('/admin/modules/theme');

        try {
            $id = (new ScriptInjectionGuard())->validateAndSave($_POST, $this->adminUserId());
            (new AuditLogger())->log('cms.script_injection.saved', [
                'id' => $id,
                'location' => $_POST['location'] ?? '',
            ]);
            $this->respondSuccess('Script injection saved after validation.', ['id' => $id], '/admin/modules/theme');
        } catch (\Throwable $exception) {
            $this->respondFailure($exception->getMessage(), '/admin/modules/theme');
        }
    }

    public function saveCustomCodeAsset(): void
    {
        $this->requireAdmin();
        $this->verifyToken('/admin/modules/theme');

        try {
            $compiler = new CustomCodeCompiler();
            $type = (string) ($_POST['asset_type'] ?? 'css');
            $name = trim((string) ($_POST['name'] ?? 'Custom Code'));
            $enabled = !empty($_POST['is_enabled']);

            $asset = $type === 'js'
                ? $compiler->saveJs($name, (string) ($_POST['code'] ?? ''), (string) ($_POST['load_strategy'] ?? 'defer'), $enabled, $this->adminUserId())
                : $compiler->saveCss($name, (string) ($_POST['code'] ?? ''), $enabled, $this->adminUserId());

            (new AuditLogger())->log('cms.custom_code.saved', ['type' => $type, 'path' => $asset['path']]);
            $this->respondSuccess('Custom code compiled and cached.', $asset, '/admin/modules/theme');
        } catch (\Throwable $exception) {
            $this->respondFailure($exception->getMessage(), '/admin/modules/theme');
        }
    }

    public function saveProduct(): void
    {
        $this->requireAdmin();
        $this->verifyToken('/admin/modules/paypal-settings');

        try {
            $id = (new DigitalCommerceEngine())->saveProduct($_POST);
            (new AuditLogger())->log('cms.commerce.product.saved', [
                'id' => $id,
                'slug' => $_POST['slug'] ?? '',
            ]);
            $this->respondSuccess('Digital product saved.', ['id' => $id], '/admin/modules/paypal-settings');
        } catch (\Throwable $exception) {
            $this->respondFailure($exception->getMessage(), '/admin/modules/paypal-settings');
        }
    }

    public function uploadProductAsset(): void
    {
        $this->requireAdmin();
        $this->verifyToken('/admin/modules/paypal-settings');

        try {
            $productId = (int) ($_POST['product_id'] ?? 0);
            if ($productId < 1) {
                throw new \RuntimeException('A valid product id is required.');
            }

            $asset = (new DigitalCommerceEngine())->uploadPackage(
                $_FILES['package'] ?? [],
                $productId,
                trim((string) ($_POST['version_label'] ?? '1.0.0'))
            );

            (new AuditLogger())->log('cms.commerce.package.uploaded', [
                'product_id' => $productId,
                'asset_id' => $asset['id'],
            ]);
            $this->respondSuccess('Private ZIP package uploaded above the public root.', $asset, '/admin/modules/paypal-settings');
        } catch (\Throwable $exception) {
            $this->respondFailure($exception->getMessage(), '/admin/modules/paypal-settings');
        }
    }

    private function requireAdmin(): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }
    }

    private function verifyToken(string $redirect): void
    {
        if (!Security::verifyCsrf($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            $this->respondFailure('CMS session token expired. Please try again.', $redirect, 419);
        }
    }

    private function inputRules(): array
    {
        if (isset($_POST['rules']) && is_array($_POST['rules'])) {
            return $_POST['rules'];
        }

        if (isset($_POST['rules']) && is_string($_POST['rules'])) {
            $decoded = json_decode($_POST['rules'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $raw = (string) file_get_contents('php://input');
        $decoded = json_decode($raw, true);

        if (is_array($decoded['rules'] ?? null)) {
            return $decoded['rules'];
        }

        throw new \RuntimeException('Typography rules payload is missing.');
    }

    private function adminUserId(): ?int
    {
        return isset($_SESSION['admin']['id']) ? (int) $_SESSION['admin']['id'] : null;
    }

    private function respondSuccess(string $message, array $data = [], string $redirect = '/admin/dashboard'): void
    {
        if ($this->wantsJson()) {
            $this->json(['ok' => true, 'message' => $message, 'data' => $data]);
            exit;
            return;
        }

        $_SESSION['admin_notice'] = $message;
        $this->redirect($redirect);
    }

    private function respondFailure(string $message, string $redirect, int $status = 422): void
    {
        if ($this->wantsJson()) {
            $this->json(['ok' => false, 'error' => $message], $status);
            exit;
            return;
        }

        $_SESSION['admin_error'] = $message;
        $this->redirect($redirect);
    }

    private function wantsJson(): bool
    {
        return str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
            || strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    }
}
