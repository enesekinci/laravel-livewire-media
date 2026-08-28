<?php

return [

    'disk' => env('MEDIA_DISK', env('FILESYSTEM_DISK', 's3')),

    'directory' => env('MEDIA_DIRECTORY', 'media'),

    'max_kb' => (int) env('MEDIA_MAX_KB', 5120),

    'mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],

    /*
    |--------------------------------------------------------------------------
    | How many recent files to list in the picker grid
    |--------------------------------------------------------------------------
    */
    'limit' => (int) env('MEDIA_LIMIT', 60),

    /*
    |--------------------------------------------------------------------------
    | Grid thumbnails (picker preview — originals used on select)
    |--------------------------------------------------------------------------
    |
    | On upload, a .thumb.webp sibling is written next to raster images.
    | Existing files without a thumb are generated the first time their
    | folder is opened (lazy_on_list), up to lazy_max per request.
    |
    */
    'thumb' => [
        'width' => (int) env('MEDIA_THUMB_WIDTH', 320),
        'quality' => (int) env('MEDIA_THUMB_QUALITY', 80),
        'generate' => (bool) env('MEDIA_THUMB_GENERATE', true),
        'lazy_on_list' => (bool) env('MEDIA_THUMB_LAZY_ON_LIST', true),
        'lazy_max' => (int) env('MEDIA_THUMB_LAZY_MAX', 12),
    ],

];
