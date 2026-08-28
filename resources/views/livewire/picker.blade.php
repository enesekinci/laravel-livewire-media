<div
    x-data
    x-on:open-media-picker.window="$wire.openPicker(($event.detail && $event.detail.requestId) || null)"
>
    @if ($open)
        <div
            class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-900/50 p-4"
            wire:click.self="close"
            role="dialog"
            aria-modal="true"
            aria-label="Medya yöneticisi"
        >
            <div class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">Medya yöneticisi</h2>
                        <p class="text-xs text-slate-500">Yükle, seç veya sil — R2 / S3</p>
                    </div>
                    <button
                        type="button"
                        wire:click="close"
                        class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm text-slate-600 hover:bg-slate-50"
                    >
                        Kapat
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 px-4 py-3">
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-[#0b5cab] px-3 py-2 text-sm font-semibold text-white hover:bg-[#094a8f]">
                        <span wire:loading.remove wire:target="uploads">Dosya yükle</span>
                        <span wire:loading wire:target="uploads">Yükleniyor…</span>
                        <input
                            type="file"
                            class="hidden"
                            wire:model="uploads"
                            multiple
                            accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                        >
                    </label>

                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Dosya ara…"
                        class="min-w-[12rem] flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#0b5cab] focus:ring-2 focus:ring-[#0b5cab]/20"
                    >

                    <button
                        type="button"
                        wire:click="$refresh"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50"
                    >
                        Yenile
                    </button>
                </div>

                @if ($error)
                    <div class="mx-4 mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                        {{ $error }}
                    </div>
                @endif

                <div class="min-h-0 flex-1 overflow-y-auto p-4" wire:loading.class="opacity-60" wire:target="uploads,delete,search">
                    @if (count($items) === 0)
                        <div class="rounded-xl border border-dashed border-slate-200 px-4 py-16 text-center text-sm text-slate-500">
                            Henüz medya yok. Yukarıdan dosya yükleyin.
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                            @foreach ($items as $item)
                                <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                    <button
                                        type="button"
                                        wire:click="select({{ \Illuminate\Support\Js::from($item['path']) }})"
                                        class="block w-full text-left"
                                        title="Seç: {{ $item['name'] }}"
                                    >
                                        <div class="aspect-square overflow-hidden bg-white">
                                            <img
                                                src="{{ $item['url'] }}"
                                                alt="{{ $item['name'] }}"
                                                class="h-full w-full object-cover transition group-hover:scale-[1.03]"
                                                loading="lazy"
                                            >
                                        </div>
                                        <div class="truncate px-2 py-1.5 text-[11px] text-slate-600">
                                            {{ $item['name'] }}
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="delete({{ \Illuminate\Support\Js::from($item['path']) }})"
                                        wire:confirm="Bu dosya silinsin mi?"
                                        class="absolute right-1.5 top-1.5 hidden rounded-md bg-white/95 px-1.5 py-0.5 text-[11px] font-semibold text-red-600 shadow group-hover:inline-flex"
                                    >
                                        Sil
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
