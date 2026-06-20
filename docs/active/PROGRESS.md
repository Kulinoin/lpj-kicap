# Progress Kicap LPJ

## Slice 00 — Project Foundation

Status: Implementasi + patch cache + patch timezone + patch PHPUnit tests + validasi otomatis + archive final.

### Selesai

- Laravel foundation.
- Filament admin baseline.
- React PWA baseline.
- MySQL Docker isolated.
- Port unik agar tidak bentrok.
- Health check.
- README awal.
- Docs active.
- Patch cache Laravel agar tidak memakai database sebelum migration.
- Patch timezone aplikasi ke Asia/Jakarta.
- Patch phpunit.xml agar cocok dengan environment Slice 00.
- Patch test bawaan ke PHPUnit class-style.
- Archive final dibuat setelah validasi pass.

### Validasi PASS

- php artisan migrate --force
- php artisan test
- npm run build
- /health HTTP 200 dan database ok
- /app HTTP 200
- /admin HTTP 302
- Git remote SSH benar

### Belum

- Commit.
- Push.
- Role Admin/User detail.
- Master LPJ.
- Data model LPJ.

### Next Slice Revisi

Slice 01 — Master LPJ, Role, Login, dan UI Baseline.

## Slice 01 — Master LPJ & Role

Status: Reset dan akan dikerjakan ulang dari awal.

Alasan reset:

- LPJ hanya dibuat oleh Admin.
- User hanya input operasional.
- Status LPJ disederhanakan menjadi `draft`, `aktif`, `finish`, `arsipkan`.
- User hanya melihat LPJ `aktif` dan `finish`.
- Login menjadi satu halaman berbasis role.
- `Ingat saya` wajib berjalan.
- User PWA memakai bottom navigation mengambang.
- Admin panel dibuat full-width.

Target baru tercatat di `docs/active/SLICE_01_MASTER_LPJ_ROLE.md`.

## Slice 01 Revisi — Implementasi 2026-06-20

Status: Implementasi + validasi otomatis PASS, disetujui untuk commit/push pada 20 Juni 2026.

Selesai:

- Login tunggal Admin/User memakai field username/email dan password.
- Redirect role setelah login: Admin ke `/admin`, User ke `/app`.
- Checkbox `Ingat saya` dipertahankan dan teruji menghasilkan remember token.
- User role ditolak dari panel Admin.
- Status LPJ dikunci ke `draft`, `aktif`, `finish`, `arsipkan`.
- Seed user MVP: Admin dapat membuat LPJ, User/Pendamping tidak dapat membuat LPJ.
- Endpoint PWA `/api/app/lpjs` hanya menampilkan LPJ assigned berstatus `aktif` dan `finish`.
- Admin resource LPJ memakai pilihan status terkunci dan assignment user.
- Admin panel memakai layout full-width.
- User PWA baseline responsive dengan bottom navigation mengambang.

Validasi otomatis PASS:

- `php artisan migrate:fresh --seed`
- `php artisan test` — 26 tests, 59 assertions
- `php artisan route:list --path=admin`
- `cmd.exe /c "cd /d D:\kulino\lpj-kicap && npm.cmd run build"`

Catatan runtime:

- `npm run build` dari bash gagal karena `npm` Windows tidak bisa membaca shim Vite Linux.
- Build PASS setelah `npm install --ignore-scripts` lewat Windows npm melengkapi optional native dependency Rolldown, lalu build dijalankan via `cmd.exe`.

## Slice 02 — LPJ Detail & Input Mobile

Status: Implementasi + validasi otomatis PASS, menunggu review manual user sebelum commit/push.

Selesai:

- Halaman detail LPJ untuk User di PWA mobile.
- Endpoint detail LPJ hanya untuk User yang ditugaskan.
- LPJ `aktif` dapat diisi catatan operasionalnya oleh User yang ditugaskan.
- LPJ `finish` tampil read-only untuk User yang ditugaskan.
- Narasi LPJ tidak tampil dan tidak dapat diedit oleh User/Petugas.
- Autosave ringan untuk catatan operasional mobile.
- Bottom navigation petugas menjadi 5 item dengan `Input Cepat` sebagai tombol tengah.
- Label UX PWA yang terlihat user memakai bahasa Indonesia, termasuk `Input Cepat` dan `Selesai`.

Validasi otomatis PASS:

- `php artisan migrate --force`
- `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php` — 5 tests, 27 assertions
- `php artisan test` — 34 tests, 99 assertions
- `php artisan migrate:fresh --seed`
- `php artisan route:list --path=api/app`
- `cmd.exe /c "cd /d D:\kulino\lpj-kicap && npm.cmd run build"`
