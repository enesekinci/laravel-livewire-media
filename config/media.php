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

];
