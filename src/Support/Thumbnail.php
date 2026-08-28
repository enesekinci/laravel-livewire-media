<?php

namespace EnesEkinci\Media\Support;

use Illuminate\Contracts\Filesystem\Filesystem;

class Thumbnail
{
    public static function path(string $path): string
    {
        $info = pathinfo($path);
        $dir = ($info['dirname'] ?? '') !== '.' ? $info['dirname'].'/' : '';

        return $dir.($info['filename'] ?? basename($path)).'.thumb.webp';
    }

    public static function isRasterImage(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    public static function url(Filesystem $storage, string $path): ?string
    {
        $thumbPath = self::path($path);

        if (! $storage->exists($thumbPath)) {
            return null;
        }

        return $storage->url($thumbPath);
    }

    /**
     * Return thumb path if present, otherwise generate when allowed.
     */
    public static function ensure(Filesystem $storage, string $path, bool $knownMissing = false): ?string
    {
        $thumbPath = self::path($path);

        if (! $knownMissing && $storage->exists($thumbPath)) {
            return $thumbPath;
        }

        if (! config('media.thumb.generate', true)) {
            return null;
        }

        return self::generate($storage, $path);
    }

    public static function generate(Filesystem $storage, string $path): ?string
    {
        if (! self::isRasterImage($path)) {
            return null;
        }

        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return null;
        }

        try {
            $contents = $storage->get($path);
        } catch (\Throwable) {
            return null;
        }

        if ($contents === '' || $contents === false) {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            return null;
        }

        $width = max(64, (int) config('media.thumb.width', 320));
        $quality = max(40, min(95, (int) config('media.thumb.quality', 80)));

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);

        if ($sourceWidth < 1 || $sourceHeight < 1) {
            imagedestroy($image);

            return null;
        }

        $scale = min($width / $sourceWidth, $width / $sourceHeight, 1);
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));

        $thumb = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($thumb === false) {
            imagedestroy($image);

            return null;
        }

        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);

        imagecopyresampled(
            $thumb,
            $image,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );

        ob_start();
        imagewebp($thumb, null, $quality);
        $webp = ob_get_clean();

        imagedestroy($image);
        imagedestroy($thumb);

        if ($webp === false || $webp === '') {
            return null;
        }

        $thumbPath = self::path($path);

        try {
            $storage->put($thumbPath, $webp, ['visibility' => 'public']);
        } catch (\Throwable) {
            return null;
        }

        return $thumbPath;
    }

    public static function delete(Filesystem $storage, string $path): void
    {
        $thumbPath = self::path($path);

        if ($storage->exists($thumbPath)) {
            $storage->delete($thumbPath);
        }
    }

    public static function move(Filesystem $storage, string $from, string $to): void
    {
        $fromThumb = self::path($from);
        $toThumb = self::path($to);

        if (! $storage->exists($fromThumb)) {
            return;
        }

        if ($storage->exists($toThumb)) {
            $storage->delete($toThumb);
        }

        try {
            $storage->move($fromThumb, $toThumb);
        } catch (\Throwable) {
            // If move fails (some drivers), regenerate on next upload instead.
        }
    }
}
