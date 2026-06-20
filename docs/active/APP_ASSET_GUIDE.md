# App Asset Guide

Panduan penempatan logo aplikasi Kicap Event.

## Logo Lembaga untuk LPJ

- Diatur dari Admin melalui menu `Organization Profiles`.
- Field `Logo Lembaga untuk LPJ` menerima upload gambar.
- Logo ini dipakai pada cover dan kop dokumen LPJ print/PDF.

## Logo Aplikasi

Logo aplikasi masih hardcode agar sederhana untuk MVP.

File yang bisa diganti manual:

- `public/icons/kicap-lpj.svg` — dipakai di header PWA User, favicon Filament, dan manifest PWA saat ini.
- `public/favicon.ico` — fallback favicon browser.

Jika ingin memakai ikon PWA PNG maskable:

1. Tambahkan file misalnya `public/icons/pwa-192.png` dan `public/icons/pwa-512.png`.
2. Update daftar `manifest.icons` di `vite.config.js`.
3. Jalankan `npm run build`.

Catatan: pertahankan nama file lama jika ingin mengganti cepat tanpa mengubah kode.
