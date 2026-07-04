<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CmsRepository;

final class CmsThemeAssetManager
{
    private const MAX_BYTES = 4194304;
    private const ALLOWED_MIMES = [
        'image/svg+xml' => 'svg',
        'image/webp' => 'webp',
        'image/png' => 'png',
        'image/x-icon' => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
    ];

    public function __construct(private readonly CmsRepository $repository = new CmsRepository())
    {
    }

    public function uploadBrandAsset(array $file, string $assetKey, string $variant, ?int $uploadedBy = null): array
    {
        $assetKey = $this->safeKey($assetKey);
        $variant = $this->safeVariant($variant);

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Brand asset upload failed.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > self::MAX_BYTES) {
            throw new \RuntimeException('Brand assets must be smaller than 4MB.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if (!is_uploaded_file($tmp)) {
            throw new \RuntimeException('Uploaded asset could not be verified.');
        }

        $mime = $this->detectMime($tmp);
        if (!isset(self::ALLOWED_MIMES[$mime])) {
            throw new \RuntimeException('Allowed theme assets are SVG, WebP, PNG and ICO only.');
        }

        if ($mime === 'image/svg+xml') {
            $this->validateSvg($tmp);
        }

        $extension = self::ALLOWED_MIMES[$mime];
        $dir = base_path('public/uploads/theme');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $assetKey . '-' . $variant . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $target = $dir . '/' . $filename;

        if (!move_uploaded_file($tmp, $target)) {
            throw new \RuntimeException('Could not store the uploaded brand asset.');
        }
        chmod($target, 0644);

        [$width, $height] = $this->imageSize($target, $mime);
        $record = [
            'asset_key' => $assetKey,
            'variant' => $variant,
            'disk' => 'public',
            'path' => '/uploads/theme/' . $filename,
            'original_name' => (string) ($file['name'] ?? $filename),
            'mime_type' => $mime,
            'size_bytes' => filesize($target) ?: $size,
            'checksum_sha256' => hash_file('sha256', $target),
            'width' => $width,
            'height' => $height,
            'uploaded_by' => $uploadedBy,
        ];
        $record['id'] = $this->repository->saveThemeAsset($record);

        return $record;
    }

    private function validateSvg(string $path): void
    {
        $svg = strtolower((string) file_get_contents($path));
        foreach (['<script', 'onload=', 'onerror=', 'javascript:', '<foreignobject', '<?php'] as $needle) {
            if (str_contains($svg, $needle)) {
                throw new \RuntimeException('Unsafe SVG content was blocked.');
            }
        }
    }

    private function imageSize(string $path, string $mime): array
    {
        if ($mime === 'image/svg+xml') {
            return [null, null];
        }

        $info = @getimagesize($path);
        return is_array($info) ? [(int) $info[0], (int) $info[1]] : [null, null];
    }

    private function detectMime(string $path): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = (string) finfo_file($finfo, $path);
                finfo_close($finfo);
                return $mime;
            }
        }

        return (string) (mime_content_type($path) ?: '');
    }

    private function safeKey(string $value): string
    {
        $value = strtolower(preg_replace('/[^a-z0-9_-]+/i', '-', $value) ?? '');
        $value = trim($value, '-');
        return $value !== '' ? substr($value, 0, 80) : 'brand';
    }

    private function safeVariant(string $variant): string
    {
        return in_array($variant, ['primary', 'dark', 'light', 'icon', 'favicon', 'social'], true) ? $variant : 'primary';
    }
}
