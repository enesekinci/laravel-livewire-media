@teleport('body')
<div
    x-data
    x-on:open-media-picker.window="$wire.openPicker(($event.detail && $event.detail.requestId) || null)"
>
    @if ($open)
        <div
            wire:click.self="close"
            role="dialog"
            aria-modal="true"
            aria-label="Medya yöneticisi"
            style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;background:rgba(15,23,42,.55);backdrop-filter:blur(2px);"
        >
            <div
                style="display:flex;flex-direction:column;width:min(920px,100%);height:min(720px,92vh);overflow:hidden;border-radius:1rem;background:#fff;box-shadow:0 25px 50px -12px rgba(0,0,0,.35);"
            >
                <div class="flex shrink-0 items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">Medya yöneticisi</h2>
                        <p class="text-xs text-slate-500">Yükle, seç veya sil</p>
                    </div>
                    <button
                        type="button"
                        wire:click="close"
                        class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm text-slate-600 hover:bg-slate-50"
                    >
                        Kapat
                    </button>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2 border-b border-slate-100 px-4 py-3">
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
                        class="min-w-[10rem] flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#0b5cab]"
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
                    <div class="mx-4 mt-3 shrink-0 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                        {{ $error }}
                    </div>
                @endif

                <div
                    class="min-h-0 flex-1 overflow-y-auto p-4"
                    wire:loading.class="opacity-60"
                    wire:target="uploads,delete,search"
                >
                    @if (count($items) === 0)
                        <div class="flex h-full min-h-[16rem] flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 px-4 text-center text-sm text-slate-500">
                            <p class="font-medium text-slate-600">Henüz medya yok</p>
                            <p class="mt-1">Yukarıdan dosya yükleyin veya sürükleyip bırakın.</p>
                        </div>
                    @else
                        <div
                            style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:0.75rem;"
                        >
                            @foreach ($items as $item)
                                <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                    <button
                                        type="button"
                                        wire:click="select({{ \Illuminate\Support\Js::from($item['path']) }})"
                                        class="block w-full text-left"
                                        title="Seç: {{ $item['name'] }}"
                                    >
                                        <div style="aspect-ratio:1/1;overflow:hidden;background:#fff;">
                                            <img
                                                src="{{ $item['url'] }}"
                                                alt="{{ $item['name'] }}"
                                                style="width:100%;height:100%;object-fit:cover;"
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
@endteleport
