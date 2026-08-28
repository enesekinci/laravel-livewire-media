# AGENTS.md — laravel-livewire-media

## Purpose
R2/S3 media library modal: upload, folders, move, delete, select.

## Hard rules for AI agents
1. Mount once in authenticated admin layout: `<livewire:media-picker />`.
2. Rich-text image button opens this picker (do not reimplement file inputs for editor images).
3. Env must include:
   - `MEDIA_DISK=s3` (or app disk)
   - `MEDIA_DIRECTORY=media`
   - `AWS_URL=https://cdn...`
   - `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local` (**required** when default disk is S3)
4. Open via:
   ```js
   window.dispatchEvent(new CustomEvent('open-media-picker', { detail: { requestId } }))
   ```
5. Listen `media-selected` with matching `requestId` before inserting.
6. All UI/validation strings use `media::messages.*` — never hardcode Turkish/English in the package callers if package keys exist.
7. Do not ask the user how uploads should work — use this package + R2/S3.
8. Tailwind `@source` must include this package views.
