<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CmsRepository;

final class CustomCodeCompiler
{
    public function __construct(private readonly CmsRepository $repository = new CmsRepository())
    {
    }

    public function saveCss(string $name, string $css, bool $enabled = true, ?int $updatedBy = null): array
    {
        $css = $this->sanitizeCss($css);
        $compiled = $this->minifyCss($css);
        $path = $this->writeCompiled('css', $compiled);

        $id = $this->repository->saveCustomCodeAsset([
            'asset_type' => 'css',
            'name' => $name !== '' ? $name : 'Custom CSS',
            'code' => $compiled,
            'compiled_path' => $path,
            'load_strategy' => 'inline',
            'is_enabled' => $enabled,
            'updated_by' => $updatedBy,
        ]);

        return ['id' => $id, 'path' => $path, 'bytes' => strlen($compiled)];
    }

    public function saveJs(string $name, string $js, string $strategy = 'defer', bool $enabled = true, ?int $updatedBy = null): array
    {
        $strategy = in_array($strategy, ['defer', 'async', 'module'], true) ? $strategy : 'defer';
        $js = $this->sanitizeJs($js);
        $compiled = $this->minifyJs($js);
        $path = $this->writeCompiled('js', $compiled);

        $id = $this->repository->saveCustomCodeAsset([
            'asset_type' => 'js',
            'name' => $name !== '' ? $name : 'Custom JS',
            'code' => $compiled,
            'compiled_path' => $path,
            'load_strategy' => $strategy,
            'is_enabled' => $enabled,
            'updated_by' => $updatedBy,
        ]);

        return ['id' => $id, 'path' => $path, 'bytes' => strlen($compiled)];
    }

    public function renderCssLinks(): string
    {
        return $this->renderAssets('css');
    }

    public function renderJsScripts(): string
    {
        return $this->renderAssets('js');
    }

    private function renderAssets(string $type): string
    {
        $html = '';
        foreach ($this->repository->enabledCustomCodeAssets($type) as $asset) {
            $path = (string) ($asset['compiled_path'] ?? '');
            if ($path === '') {
                continue;
            }

            if ($type === 'css') {
                $html .= "\n" . '<link rel="stylesheet" href="' . e($path) . '">';
                continue;
            }

            $strategy = (string) ($asset['load_strategy'] ?? 'defer');
            $attribute = $strategy === 'module' ? ' type="module"' : ' ' . e($strategy);
            $html .= "\n" . '<script src="' . e($path) . '"' . $attribute . '></script>';
        }

        return $html;
    }

    private function writeCompiled(string $type, string $code): string
    {
        $hash = substr(hash('sha256', $code), 0, 16);
        $relativeDir = 'assets/' . $type . '/cms';
        $absoluteDir = base_path('public/' . $relativeDir);
        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $filename = 'custom-' . $hash . '.' . $type;
        file_put_contents($absoluteDir . '/' . $filename, $code, LOCK_EX);

        return '/' . $relativeDir . '/' . $filename;
    }

    private function sanitizeCss(string $css): string
    {
        foreach (['@import', 'expression(', 'javascript:', 'vbscript:', '</style'] as $needle) {
            if (str_contains(strtolower($css), $needle)) {
                throw new \RuntimeException('Unsafe CSS was blocked.');
            }
        }

        return $css;
    }

    private function sanitizeJs(string $js): string
    {
        $lower = strtolower($js);
        foreach (['<?php', '<script', '</script', 'eval(', 'new function', 'document.write', 'innerhtml='] as $needle) {
            if (str_contains($lower, $needle)) {
                throw new \RuntimeException('Unsafe JavaScript was blocked.');
            }
        }

        return $js;
    }

    private function minifyCss(string $css): string
    {
        $css = preg_replace('/\/\*[\s\S]*?\*\//', '', $css) ?? '';
        $css = preg_replace('/\s+/', ' ', $css) ?? '';
        $css = preg_replace('/\s*([{}:;,>])\s*/', '$1', $css) ?? '';
        return trim(str_replace(';}', '}', $css));
    }

    private function minifyJs(string $js): string
    {
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js) ?? '';
        $js = preg_replace('/^\s*\/\/.*$/m', '', $js) ?? '';
        $js = preg_replace('/\s+/', ' ', $js) ?? '';
        return trim($js);
    }
}
