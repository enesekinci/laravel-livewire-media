<?php

namespace EnesEkinci\Media\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class MediaPicker extends Component
{
    use WithFileUploads;

    public bool $open = false;

    public string $requestId = '';

    public string $search = '';

    /** @var mixed */
    public $uploads = [];

    public ?string $error = null;

    #[On('open-media-picker')]
    public function openPicker(?string $requestId = null): void
    {
        $this->requestId = $requestId ?: (string) Str::uuid();
        $this->error = null;
        $this->search = '';
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
        $this->uploads = [];
        $this->error = null;
    }

    public function updatedUploads(): void
    {
        $this->error = null;

        $files = is_array($this->uploads) ? $this->uploads : [$this->uploads];
        $maxKb = (int) config('media.max_kb', 5120);
        $mimes = implode(',', config('media.mimes', ['jpg', 'jpeg', 'png', 'gif', 'webp']));

        try {
            $this->validate([
                'uploads' => ['required'],
                'uploads.*' => ['file', "max:{$maxKb}", "mimes:{$mimes}"],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->error = $e->validator->errors()->first() ?: 'Geçersiz dosya.';
            $this->uploads = [];

            return;
        }

        $disk = (string) config('media.disk', 's3');
        $directory = trim((string) config('media.directory', 'media'), '/');

        foreach ($files as $file) {
            if (! $file) {
                continue;
            }
            $file->storePublicly($directory, $disk);
        }

        $this->uploads = [];
        $this->dispatch('media-uploaded');
    }

    public function select(string $path): void
    {
        $disk = (string) config('media.disk', 's3');

        if (! Storage::disk($disk)->exists($path)) {
            $this->error = 'Dosya bulunamadı.';

            return;
        }

        $url = Storage::disk($disk)->url($path);

        $this->dispatch('media-selected', requestId: $this->requestId, url: $url, path: $path);
        $this->js(
            'window.dispatchEvent(new CustomEvent("media-selected", { detail: '
            .json_encode(['requestId' => $this->requestId, 'url' => $url, 'path' => $path])
            .' }))'
        );

        $this->close();
    }

    public function delete(string $path): void
    {
        $disk = (string) config('media.disk', 's3');

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * @return list<array{path: string, url: string, name: string, lastModified: int}>
     */
    public function items(): array
    {
        $disk = (string) config('media.disk', 's3');
        $directory = trim((string) config('media.directory', 'media'), '/');
        $limit = max(1, (int) config('media.limit', 60));
        $search = mb_strtolower(trim($this->search));

        $storage = Storage::disk($disk);

        try {
            $paths = $storage->files($directory);
        } catch (\Throwable $e) {
            $this->error = 'Medya listelenemedi: '.$e->getMessage();

            return [];
        }

        $items = collect($paths)
            ->filter(function (string $path) use ($search) {
                if ($search === '') {
                    return true;
                }

                return str_contains(mb_strtolower(basename($path)), $search);
            })
            ->map(function (string $path) use ($storage) {
                try {
                    $lastModified = $storage->lastModified($path);
                } catch (\Throwable) {
                    $lastModified = 0;
                }

                return [
                    'path' => $path,
                    'url' => $storage->url($path),
                    'name' => basename($path),
                    'lastModified' => $lastModified,
                ];
            })
            ->sortByDesc('lastModified')
            ->take($limit)
            ->values()
            ->all();

        return $items;
    }

    public function render(): View
    {
        return view('media::livewire.picker', [
            'items' => $this->open ? $this->items() : [],
        ]);
    }
}
