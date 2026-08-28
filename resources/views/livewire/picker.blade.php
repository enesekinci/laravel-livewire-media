<div
    x-data
    x-on:open-media-picker.window="$wire.openPicker(($event.detail && $event.detail.requestId) || null)"
>
    @if ($open)
        <div
            wire:key="media-overlay"
            wire:click.self="close"
            role="dialog"
            aria-modal="true"
            aria-label="Medya yöneticisi"
            style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;background:rgba(15,23,42,.55);backdrop-filter:blur(2px);"
        >
            <div
                wire:click.stop
                style="display:flex;flex-direction:column;width:min(960px,100%);height:min(780px,92vh);overflow:hidden;border-radius:1rem;background:#fff;box-shadow:0 25px 50px -12px rgba(0,0,0,.35);"
            >
                <div class="flex shrink-0 items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">Medya yöneticisi</h2>
                        <p class="text-xs text-slate-500">Yükle · klasörle · taşı · sil · seç</p>
                    </div>
                    <button
                        type="button"
                        wire:click="close"
                        class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm text-slate-600 hover:bg-slate-50"
                    >
                        Kapat
                    </button>
                </div>

                {{-- Breadcrumb --}}
                <div class="flex shrink-0 flex-wrap items-center gap-1 border-b border-slate-100 px-4 py-2 text-sm">
                    <button type="button" wire:click="openFolder('')" class="rounded px-1.5 py-0.5 font-medium text-[#0b5cab] hover:bg-slate-100">
                        {{ $rootLabel }}
                    </button>
                    @foreach ($crumbs as $i => $crumb)
                        <span class="text-slate-300">/</span>
                        <button type="button" wire:click="goToCrumb({{ $i }})" class="rounded px-1.5 py-0.5 text-slate-700 hover:bg-slate-100">
                            {{ $crumb }}
                        </button>
                    @endforeach
                    @if ($folder !== '')
                        <button type="button" wire:click="goUp" class="ml-2 rounded border border-slate-200 px-2 py-0.5 text-xs text-slate-600 hover:bg-slate-50">
                            Üst klasör
                        </button>
                    @endif
                </div>

                {{-- Toolbar --}}
                <div class="flex shrink-0 flex-wrap items-center gap-2 border-b border-slate-100 px-4 py-3">
                    <label class="relative inline-flex cursor-pointer items-center gap-2 overflow-hidden rounded-lg bg-[#0b5cab] px-3 py-2 text-sm font-semibold text-white hover:bg-[#094a8f]">
                        <span wire:loading.remove wire:target="upload">Dosya yükle</span>
                        <span wire:loading wire:target="upload">Yükleniyor…</span>
                        <input
                            type="file"
                            style="position:absolute;inset:0;opacity:0;cursor:pointer;"
                            wire:model="upload"
                            accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                        >
                    </label>

                    <div class="flex min-w-[14rem] flex-1 items-center gap-1">
                        <input
                            type="text"
                            wire:model="newFolder"
                            wire:keydown.enter.prevent="createFolder"
                            placeholder="Yeni klasör adı"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#0b5cab]"
                        >
                        <button
                            type="button"
                            wire:click="createFolder"
                            class="shrink-0 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                        >
                            Klasör
                        </button>
                    </div>

                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Ara…"
                        class="w-36 rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#0b5cab]"
                    >

                    <button
                        type="button"
                        wire:click="$refresh"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50"
                    >
                        Yenile
                    </button>
                </div>

                @if ($movingPath)
                    <div class="flex shrink-0 items-center justify-between gap-2 border-b border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-900">
                        <span>Taşınıyor: <strong>{{ basename($movingPath) }}</strong></span>
                        <span class="flex gap-2">
                            <button type="button" wire:click="moveHere" class="rounded-md bg-amber-600 px-2.5 py-1 text-xs font-semibold text-white">Buraya taşı</button>
                            <button type="button" wire:click="cancelMove" class="rounded-md border border-amber-300 px-2.5 py-1 text-xs">İptal</button>
                        </span>
                    </div>
                @endif

                @if ($error)
                    <div class="mx-4 mt-3 shrink-0 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $error }}</div>
                @endif
                @if ($status)
                    <div class="mx-4 mt-3 shrink-0 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ $status }}</div>
                @endif

                <div
                    class="min-h-0 flex-1 overflow-y-auto p-4"
                    wire:loading.class="opacity-60"
                    wire:target="upload,deleteFile,deleteFolder,createFolder,moveHere,search,openFolder"
                >
                    @if (count($entries) === 0)
                        <div class="flex h-full min-h-[16rem] flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 px-4 text-center text-sm text-slate-500">
                            <p class="font-medium text-slate-600">Bu klasör boş</p>
                            <p class="mt-1">Dosya yükleyin veya yeni klasör oluşturun.</p>
                        </div>
                    @else
                        @php
                            $folders = collect($entries)->where('type', 'folder')->values();
                            $files = collect($entries)->where('type', 'file')->values();
                        @endphp

                        @if ($folders->isNotEmpty())
                            <div class="mb-4 flex flex-col gap-1.5">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Klasörler</div>
                                <div class="flex flex-col gap-1">
                                    @foreach ($folders as $item)
                                        <div class="group flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 hover:border-[#0b5cab]/40 hover:bg-white">
                                            <button
                                                type="button"
                                                wire:click="openFolder({{ \Illuminate\Support\Js::from($item['relative']) }})"
                                                class="flex min-w-0 flex-1 items-center gap-2 text-left"
                                            >
                                                <svg class="h-5 w-5 shrink-0 text-[#0b5cab]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M10 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V8a2 2 0 00-2-2h-8l-2-2z"/>
                                                </svg>
                                                <span class="truncate text-sm font-medium text-slate-700">{{ $item['name'] }}</span>
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="deleteFolder({{ \Illuminate\Support\Js::from($item['path']) }})"
                                                wire:confirm="Boş klasör silinsin mi?"
                                                class="shrink-0 rounded-md px-2 py-1 text-[11px] font-semibold text-red-600 opacity-0 hover:bg-red-50 group-hover:opacity-100"
                                            >
                                                Sil
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($files->isNotEmpty())
                            @if ($folders->isNotEmpty())
                                <div class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Dosyalar</div>
                            @endif
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:0.75rem;align-items:start;">
                                @foreach ($files as $item)
                                    <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50 {{ $movingPath === $item['path'] ? 'ring-2 ring-amber-400' : '' }}">
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
                                            <div class="truncate px-2 py-1.5 text-[11px] text-slate-600">{{ $item['name'] }}</div>
                                        </button>

                                        <div class="absolute right-1.5 top-1.5 hidden gap-1 group-hover:flex">
                                            <button
                                                type="button"
                                                wire:click="startMove({{ \Illuminate\Support\Js::from($item['path']) }})"
                                                class="rounded-md bg-white/95 px-1.5 py-0.5 text-[11px] font-semibold text-slate-700 shadow"
                                            >
                                                Taşı
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="deleteFile({{ \Illuminate\Support\Js::from($item['path']) }})"
                                                wire:confirm="Bu dosya silinsin mi?"
                                                class="rounded-md bg-white/95 px-1.5 py-0.5 text-[11px] font-semibold text-red-600 shadow"
                                            >
                                                Sil
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
