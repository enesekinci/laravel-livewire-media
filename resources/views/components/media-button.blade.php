@props([
    'label' => null,
    'accent' => '#0b5cab',
])

@php
    $requestId = (string) \Illuminate\Support\Str::uuid();
@endphp

<button
    type="button"
    {{ $attributes->class([
        'inline-flex cursor-pointer items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold text-white shadow-sm',
    ])->merge([
        'style' => 'background: '.$accent,
    ]) }}
    x-data
    @click="$dispatch('open-media-picker', { requestId: {{ \Illuminate\Support\Js::from($requestId) }} })"
>
    {{ $label ?? __('media::messages.pick_button') }}
</button>
