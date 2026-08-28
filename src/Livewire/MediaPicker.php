<?php

namespace EnesEkinci\Media\Livewire;

use EnesEkinci\Media\Support\Thumbnail;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaPicker extends Component
{
    use WithFileUploads;

    public bool $open = false;

    public string $requestId = '';

    public string $folder = '';

    public string $search = '';

    public string $newFolder = '';

    public ?string $movingPath = null;

    /** @var mixed */
    public $upload;

    public ?string $error = null;

    public ?string $status = null;

    #[On('open-media-picker')]
    public function openPicker(?string $requestId = null): void
    {
        $this->requestId = $requestId ?: (string) Str::uuid();
        $this->error = null;
        $this->status = null;
        $this->search = '';
        $this->newFolder = '';
        $this->movingPath = null;
        $this->folder = '';
        $this->upload = null;
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
        $this->upload = null;
        $this->error = null;
        $this->status = null;
        $this->movingPath = null;
    }

    public function openFolder(string $path): void
    {
        $this->folder = trim($path, '/');
        $this->search = '';
        $this->error = null;
    }

    public function goUp(): void
    {
        if ($this->folder === '') {
            return;
        }

        $parent = Str::of($this->folder)->beforeLast('/')->toString();
        $this->folder = $parent === $this->folder ? '' : $parent;
    }

    public function goToCrumb(int $index): void
    {
        $parts = $this->folder === '' ? [] : explode('/', $this->folder);
        $this->folder = implode('/', array_slice($parts, 0, max(0, $index + 1)));
    }

    public function createFolder(): void
    {
        $this->error = null;
        $name = Str::slug(trim($this->newFolder));

        if ($name === '') {
            $this->error = $this->t('folder_name_required');

            return;
        }

        $path = $this->join($this->currentDirectory(), $name);
        $disk = $this->disk();

        if (Storage::disk($disk)->exists($path)) {
            $this->error = $this->t('folder_exists');

            return;
        }

        try {
            if (method_exists(Storage::disk($disk), 'directoryExists') && Storage::disk($disk)->directoryExists($path)) {
                $this->error = $this->t('folder_exists');

                return;
            }
        } catch (\Throwable) {
            // ignore — some drivers throw on missing dirs
        }

        Storage::disk($disk)->makeDirectory($path);
        // Keep folder visible on S3-style disks
        Storage::disk($disk)->put($path.'/.keep', '');

        $this->newFolder = '';
        $this->status = $this->t('folder_created');
    }

    public function updatedUpload(): void
    {
        $this->error = null;
        $this->status = null;

        if (! $this->upload instanceof TemporaryUploadedFile) {
            return;
        }

        $maxKb = (int) config('media.max_kb', 5120);
        $mimes = implode(',', config('media.mimes', ['jpg', 'jpeg', 'png', 'gif', 'webp']));

        try {
            $this->validate([
                'upload' => ['required', 'file', "max:{$maxKb}", "mimes:{$mimes}"],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->error = $e->validator->errors()->first('upload') ?: $this->t('invalid_file');
            $this->upload = null;

            return;
        }

        try {
            $stored = $this->upload->storePublicly($this->currentDirectory(), $this->disk());
            if ($stored && config('media.thumb.generate', true)) {
                Thumbnail::generate(Storage::disk($this->disk()), $stored);
            }
            $this->status = $this->t('file_uploaded');
        } catch (\Throwable $e) {
            $this->error = $this->t('upload_failed', ['error' => $e->getMessage()]);
        }

        $this->upload = null;
        $this->dispatch('media-uploaded');
    }

    public function select(string $path): void
    {
        if ($this->movingPath) {
            return;
        }

        $disk = $this->disk();

        if (! Storage::disk($disk)->exists($path)) {
            $this->error = $this->t('file_not_found');

            return;
        }

        $url = Storage::disk($disk)->url($path);

        // Single browser event only — Livewire also mirrors dispatch() to window.
        $this->dispatch('media-selected', requestId: $this->requestId, url: $url, path: $path);

        $this->close();
    }

    public function startMove(string $path): void
    {
        $this->movingPath = $path;
        $this->status = $this->t('move_hint');
        $this->error = null;
    }

    public function cancelMove(): void
    {
        $this->movingPath = null;
        $this->status = null;
    }

    public function moveHere(): void
    {
        if (! $this->movingPath) {
            return;
        }

        $disk = $this->disk();
        $from = $this->movingPath;
        $name = basename($from);
        $to = $this->join($this->currentDirectory(), $name);

        if ($from === $to) {
            $this->error = $this->t('file_already_here');
            $this->movingPath = null;

            return;
        }

        if (Storage::disk($disk)->exists($to)) {
            $this->error = $this->t('file_exists_target');

            return;
        }

        try {
            Storage::disk($disk)->move($from, $to);
            Thumbnail::move(Storage::disk($disk), $from, $to);
            $this->status = $this->t('file_moved');
            $this->movingPath = null;
        } catch (\Throwable $e) {
            $this->error = $this->t('move_failed', ['error' => $e->getMessage()]);
        }
    }

    public function deleteFile(string $path): void
    {
        $disk = $this->disk();

        if (Storage::disk($disk)->exists($path)) {
            Thumbnail::delete(Storage::disk($disk), $path);
            Storage::disk($disk)->delete($path);
            $this->status = $this->t('file_deleted');
        }

        if ($this->movingPath === $path) {
            $this->movingPath = null;
        }
    }

    public function deleteFolder(string $path): void
    {
        $disk = $this->disk();
        $storage = Storage::disk($disk);

        $files = $storage->allFiles($path);
        $keepOnly = collect($files)->every(fn (string $f) => str_ends_with($f, '/.keep') || basename($f) === '.keep');

        if (count($files) > 0 && ! $keepOnly) {
            $this->error = $this->t('folder_not_empty');

            return;
        }

        $storage->deleteDirectory($path);
        $this->status = $this->t('folder_deleted');
    }



    public function rules(): array
    {
        $maxKb = (int) config('media.max_kb', 5120);
        $mimes = implode(',', config('media.mimes', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']));

        return [
            'upload' => ['nullable', 'file', "max:{$maxKb}", "mimes:{$mimes}"],
        ];
    }

    public function messages(): array
    {
        $max = (int) config('media.max_kb', 5120);

        return [
            'upload.max' => __('media::messages.validation.upload_max', ['max' => $max]),
            'upload.mimes' => __('media::messages.validation.upload_mimes'),
            'upload.required' => __('media::messages.validation.upload_required'),
            'upload.file' => __('media::messages.validation.upload_file'),
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'upload' => __('media::messages.attributes.upload'),
        ];
    }

    protected function t(string $key, array $replace = []): string
    {
        return __('media::messages.'.$key, $replace);
    }

    protected function disk(): string
    {
        return (string) config('media.disk', 's3');
    }

    protected function root(): string
    {
        return trim((string) config('media.directory', 'media'), '/');
    }

    protected function currentDirectory(): string
    {
        return $this->folder === ''
            ? $this->root()
            : $this->join($this->root(), $this->folder);
    }

    protected function join(string ...$parts): string
    {
        return collect($parts)
            ->map(fn (string $p) => trim($p, '/'))
            ->filter()
            ->implode('/');
    }

    /**
     * @return list<array{type: string, path: string, name: string, url?: string, thumb_url?: string}>
     */
    public function entries(): array
    {
        $disk = $this->disk();
        $storage = Storage::disk($disk);
        $dir = $this->currentDirectory();
        $limit = max(1, (int) config('media.limit', 60));
        $search = mb_strtolower(trim($this->search));

        try {
            $directories = $storage->directories($dir);
            $files = $storage->files($dir);
        } catch (\Throwable $e) {
            $this->error = $this->t('list_failed', ['error' => $e->getMessage()]);

            return [];
        }

        $folderItems = collect($directories)
            ->map(function (string $path) {
                return [
                    'type' => 'folder',
                    'path' => $path,
                    'name' => basename($path),
                    'relative' => trim(Str::after($path, $this->root().'/'), '/'),
                ];
            })
            ->filter(function (array $item) use ($search) {
                if ($search === '') {
                    return true;
                }

                return str_contains(mb_strtolower($item['name']), $search);
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $listedPaths = collect($files)->flip();
        $lazy = (bool) config('media.thumb.generate', true)
            && (bool) config('media.thumb.lazy_on_list', true);
        $lazyMax = max(0, (int) config('media.thumb.lazy_max', 12));
        $generated = 0;

        $fileItems = collect($files)
            ->reject(fn (string $path) => basename($path) === '.keep' || str_ends_with(strtolower($path), '.thumb.webp'))
            ->filter(function (string $path) use ($search) {
                if ($search === '') {
                    return true;
                }

                return str_contains(mb_strtolower(basename($path)), $search);
            })
            ->sortBy(fn (string $path) => mb_strtolower(basename($path)), SORT_NATURAL | SORT_FLAG_CASE)
            ->reverse()
            ->take($limit)
            ->map(function (string $path) use ($storage, $listedPaths, $lazy, $lazyMax, &$generated) {
                $url = $storage->url($path);
                $thumbPath = Thumbnail::path($path);
                $hasThumb = $listedPaths->has($thumbPath);

                if (! $hasThumb && $lazy && $generated < $lazyMax && Thumbnail::isRasterImage($path)) {
                    if (Thumbnail::ensure($storage, $path, knownMissing: true)) {
                        $hasThumb = true;
                        $generated++;
                    }
                }

                return [
                    'type' => 'file',
                    'path' => $path,
                    'name' => basename($path),
                    'url' => $url,
                    'thumb_url' => $hasThumb ? $storage->url($thumbPath) : $url,
                ];
            })
            ->values();

        return $folderItems
            ->concat($fileItems)
            ->values()
            ->all();
    }

    public function render(): View
    {
        $crumbs = $this->folder === '' ? [] : explode('/', $this->folder);

        return view('media::livewire.picker', [
            'entries' => $this->open ? $this->entries() : [],
            'crumbs' => $crumbs,
            'rootLabel' => $this->root(),
        ]);
    }
}
