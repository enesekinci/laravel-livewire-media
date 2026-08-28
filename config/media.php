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
    | Existing files get a thumb the first time their folder is opened.
    |
    */
    'thumb' => [
        'width' => (int) env('MEDIA_THUMB_WIDTH', 320),
        'quality' => (int) env('MEDIA_THUMB_QUALITY', 80),
        'generate' => (bool) env('MEDIA_THUMB_GENERATE', true),
    ],

];
