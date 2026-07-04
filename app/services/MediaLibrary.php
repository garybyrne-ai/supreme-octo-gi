<?php

declare(strict_types=1);

namespace App\Services;

final class MediaLibrary
{
    private const MAX_BYTES = 8388608;
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public function upload(array $file, string $alt = ''): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed. Choose a valid image and try again.');
        }

        if ((int) ($file['size'] ?? 0) < 1 || (int) ($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new \RuntimeException('Images must be smaller than 8MB.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if (!is_uploaded_file($tmp)) {
            throw new \RuntimeException('Upload source could not be verified.');
        }

        $mime = $this->detectMime($tmp);
        if (!isset(self::ALLOWED_MIMES[$mime])) {
            throw new \RuntimeException('Only JPG, PNG, GIF and WebP images are allowed.');
        }

        $info = @getimagesize($tmp);
        if (!is_array($info) || empty($info[0]) || empty($info[1])) {
            throw new \RuntimeException('The uploaded file is not a readable image.');
        }

        $datePath = gmdate('Y/m');
        $targetDir = base_path('public/uploads/media/' . $datePath);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $safeBase = $this->slug(pathinfo((string) ($file['name'] ?? 'image'), PATHINFO_FILENAME));
        $filename = $safeBase . '-' . bin2hex(random_bytes(4)) . '.webp';
        $target = $targetDir . '/' . $filename;

        $this->convertToWebp($tmp, $mime, $target);

        $item = [
            'id' => bin2hex(random_bytes(8)),
            'filename' => $filename,
            'original_name' => (string) ($file['name'] ?? $filename),
            'path' => '/uploads/media/' . $datePath . '/' . $filename,
            'mime_type' => 'image/webp',
            'size_bytes' => filesize($target) ?: 0,
            'width' => (int) $info[0],
            'height' => (int) $info[1],
            'alt' => trim($alt),
            'created_at' => gmdate('c'),
        ];

        $items = $this->items(500);
        array_unshift($items, $item);
        $this->save($items);

        return $item;
    }

    public function items(int $limit = 80): array
    {
        $items = $this->storedItems();
        $known = array_fill_keys(array_column($items, 'path'), true);

        foreach ($this->scanUploads() as $item) {
            if (!isset($known[$item['path']])) {
                $items[] = $item;
            }
        }

        usort($items, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));
        return array_slice($items, 0, max(1, min(500, $limit)));
    }

    private function convertToWebp(string $source, string $mime, string $target): void
    {
        if (!function_exists('imagewebp')) {
            throw new \RuntimeException('PHP GD WebP support is required for automatic image conversion.');
        }

        $image = match ($mime) {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($source) : false,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($source) : false,
            'image/gif' => function_exists('imagecreatefromgif') ? @imagecreatefromgif($source) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
            default => false,
        };

        if (!$image instanceof \GdImage) {
            throw new \RuntimeException('The server could not decode this image. Enable GD support for JPG, PNG, GIF and WebP.');
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        if (!imagewebp($image, $target, 84)) {
            imagedestroy($image);
            throw new \RuntimeException('The server could not write the WebP image.');
        }

        imagedestroy($image);
        chmod($target, 0644);
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

    private function scanUploads(): array
    {
        $root = base_path('public/uploads/media');
        if (!is_dir($root)) {
            return [];
        }

        $items = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                continue;
            }

            $extension = strtolower($file->getExtension());
            if (!in_array($extension, ['webp', 'jpg', 'jpeg', 'png', 'gif'], true)) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            $path = '/uploads/media/' . $relative;
            $items[] = [
                'id' => hash('sha256', $path),
                'filename' => $file->getFilename(),
                'original_name' => $file->getFilename(),
                'path' => $path,
                'mime_type' => $extension === 'webp' ? 'image/webp' : 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension),
                'size_bytes' => $file->getSize(),
                'width' => 0,
                'height' => 0,
                'alt' => '',
                'created_at' => gmdate('c', $file->getMTime()),
            ];
        }

        return $items;
    }

    private function storedItems(): array
    {
        $path = $this->dataPath();
        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
    }

    private function save(array $items): void
    {
        $dir = dirname($this->dataPath());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->dataPath(), json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function dataPath(): string
    {
        return base_path('storage/data/media-library.json');
    }

    private function slug(string $value): string
    {
        $value = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $value) ?? 'image');
        $value = trim($value, '-');
        return $value !== '' ? substr($value, 0, 70) : 'image';
    }
}
