<?php

namespace EnesEkinci\Media\Console;

use EnesEkinci\Media\Support\Thumbnail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateThumbnailsCommand extends Command
{
    protected $signature = 'media:thumbnails
                            {--path= : Only under this directory prefix (e.g. media/products)}
                            {--force : Regenerate even when thumb exists}';

    protected $description = 'Generate .thumb.webp previews for existing media files on the configured disk';

    public function handle(): int
    {
        $disk = (string) config('media.disk', 's3');
        $storage = Storage::disk($disk);
        $prefix = trim((string) $this->option('path') ?: (string) config('media.directory', 'media'), '/');
        $force = (bool) $this->option('force');

        $files = $storage->allFiles($prefix);
        $created = 0;
        $skipped = 0;

        foreach ($files as $path) {
            if (str_ends_with(strtolower($path), '.thumb.webp') || basename($path) === '.keep') {
                continue;
            }

            if (! Thumbnail::isRasterImage($path)) {
                $skipped++;

                continue;
            }

            if (! $force && $storage->exists(Thumbnail::path($path))) {
                $skipped++;

                continue;
            }

            if (Thumbnail::generate($storage, $path)) {
                $created++;
                $this->line("  ✓ {$path}");
            } else {
                $skipped++;
            }
        }

        $this->info("Done. Created: {$created}, skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
