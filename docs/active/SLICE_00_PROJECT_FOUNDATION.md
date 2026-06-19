# Slice 00 — Project Foundation

Tanggal: 2026-06-19 20:12:06 +07

## Target

- Setup Laravel.
- Setup Filament Admin.
- Setup React PWA baseline.
- Setup MySQL.
- Auth dasar dengan seed user.
- Health check.
- README awal.
- Git remote SSH.
- Archive setelah validasi.

## Hasil Implementasi

- Laravel project tersedia di `/mnt/d/kulino/lpj-kicap`.
- Filament panel tersedia di `/admin`.
- React PWA baseline tersedia di `/app`.
- Health check tersedia di `/health`.
- MySQL berjalan via Docker pada port host `3460`.
- Port app: `8730`.
- Port Vite: `5230`.

## Stack Aktual

- Laravel: v13.16.1
- Filament: v4.11.7
- PHP: 8.5.0
- Node: v24.16.0
- NPM: 11.13.0

## Seed Auth Dasar

- `admin@kicap.id` / `password`
- `user@kicap.id` / `password`

Role dan permission detail masuk Slice 01.

## Fix Error

Error awal:

```text
SQLSTATE[42S02]: Table 'lpj_kicap.cache' doesn't exist
```

Penyebab:

- Laravel memakai database cache.
- `php artisan cache:clear` dipanggil sebelum tabel `cache` dibuat oleh migration.

Perbaikan:

- Set `CACHE_STORE=file`.
- Set `CACHE_DRIVER=file`.
- Hindari `php artisan cache:clear` sebelum migration.
- Bersihkan file cache config secara langsung.
- Jalankan migration.

## Catatan UI

Frontend harus mobile-first dan terasa seperti aplikasi HP. Menu jangan terlalu banyak; cukup ringkas, fungsional, dan mewakili alur kerja utama.

## Status

Belum commit dan belum push. Commit/push dilakukan setelah user validasi manual.
