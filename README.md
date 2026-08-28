# Laravel Livewire Media

R2 / S3 medya yöneticisi — Livewire modal.

## Özellikler

- Dosya yükleme (Livewire temporary upload → R2/S3)
- Klasör oluştur / gezin / sil (boş klasör)
- Dosya taşı / sil / seç
- Arama

## Kurulum

```bash
composer require enesekinci/laravel-livewire-media:^1.1
```

Layout:

```blade
@auth
    <livewire:media-picker />
@endauth
```

`.env`:

```env
MEDIA_DISK=s3
MEDIA_DIRECTORY=media
AWS_URL=https://cdn.example.com
LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local
```

> `FILESYSTEM_DISK=s3` iken Livewire geçici upload’ları **local** diskte olmalı.

## Kullanım

```js
window.dispatchEvent(new CustomEvent('open-media-picker', {
  detail: { requestId: 'editor-1' }
}))

window.addEventListener('media-selected', (e) => {
  console.log(e.detail.url)
})
```

## Lisans

MIT
