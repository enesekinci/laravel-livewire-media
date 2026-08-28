<?php

namespace EnesEkinci\Media;

use EnesEkinci\Media\Livewire\MediaPicker;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class MediaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/media.php', 'media');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'media');

        Livewire::component('media-picker', MediaPicker::class);
        Livewire::component('media.picker', MediaPicker::class);

        Blade::component('media::components.media-button', 'media-button');
        Blade::component('media::components.media-button', 'admin.media-button');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/media.php' => config_path('media.php'),
            ], 'media-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/media'),
            ], 'media-views');
        }
    }
}
