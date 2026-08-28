# Laravel Livewire Media

R2 / S3 medya yöneticisi — Livewire modal: yükle, ara, seç, sil.

## Kurulum

```bash
composer config repositories.media vcs https://github.com/enesekinci/laravel-livewire-media
composer require enesekinci/laravel-livewire-media:^1.0
```

Layout’a bir kez ekle:

```blade
<livewire:media-picker />
```

`.env`:

```env
MEDIA_DISK=s3
MEDIA_DIRECTORY=media
AWS_URL=https://cdn.example.com
```

## Kullanım

Aç:

```js
window.dispatchEvent(new CustomEvent('open-media-picker', {
  detail: { requestId: 'editor-1' }
}))
```

Seçim:

```js
window.addEventListener('media-selected', (e) => {
  console.log(e.detail.url, e.detail.path, e.detail.requestId)
})
```

Buton:

```blade
<x-media-button label="Görsel seç" />
```

## Lisans

MIT
