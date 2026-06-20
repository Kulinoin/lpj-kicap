# App Asset Guide

Panduan penempatan logo aplikasi Kicap Event.

## Logo Lembaga untuk LPJ

- Diatur dari Admin melalui menu `Organization Profiles`.
- Field `Logo Lembaga untuk LPJ` menerima upload gambar.
- Logo ini dipakai pada cover dan kop dokumen LPJ print/PDF.

## Logo Aplikasi

Logo aplikasi masih hardcode agar sederhana untuk MVP.

File yang bisa diganti manual:

- `public/icons/kicap-event-logo.svg` — logo aplikasi utama. Dipakai untuk login, favicon SVG, favicon Filament, dan manifest PWA.
- `public/icons/kicap-event-192.png` — ikon PWA Android 192x192.
- `public/icons/kicap-event-512.png` — ikon PWA Android 512x512.
- `public/icons/kicap-event-apple-touch.png` — ikon Apple touch 180x180.
- `public/icons/user-avatar-placeholder.svg` — fallback avatar pada tabel User Admin jika user belum punya foto profil.
- `public/favicon.ico` — fallback favicon browser.

Jika ingin memakai ikon PWA PNG maskable:

1. Ganti file `public/icons/kicap-event-192.png` dan `public/icons/kicap-event-512.png`.
2. Update daftar `manifest.icons` di `vite.config.js`.
3. Jalankan `npm run build`.

Catatan: nama file aplikasi sudah disesuaikan ke nama `Kicap Event`.

## Daftar Direktori Aset Aplikasi

Gunakan daftar ini saat mengganti favicon/logo aplikasi/PWA:

```text
public/favicon.ico
public/icons/kicap-event-logo.svg
public/icons/kicap-event-192.png
public/icons/kicap-event-512.png
public/icons/kicap-event-apple-touch.png
public/icons/user-avatar-placeholder.svg
public/build/manifest.webmanifest
public/build/sw.js
vite.config.js
resources/views/filament/auth/login-polish.blade.php
```

Keterangan:

- `public/favicon.ico` untuk favicon browser fallback.
- `public/icons/kicap-event-logo.svg` untuk logo aplikasi utama, login, favicon SVG, favicon Filament, dan manifest PWA.
- `public/icons/kicap-event-192.png` dan `public/icons/kicap-event-512.png` untuk ikon install PWA.
- `public/icons/kicap-event-apple-touch.png` untuk ikon homescreen iOS.
- `public/build/manifest.webmanifest` dan `public/build/sw.js` adalah hasil build, jangan diedit manual; update sumbernya di `vite.config.js`, lalu jalankan `npm run build`.
- `resources/views/filament/auth/login-polish.blade.php` berisi styling login dan referensi logo login.
