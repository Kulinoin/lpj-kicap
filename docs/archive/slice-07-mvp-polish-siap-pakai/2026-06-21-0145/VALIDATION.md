# Slice 07 — MVP Polish & Siap Pakai

Tanggal: 21 Juni 2026

## Scope

- Polish User PWA mengikuti brief UI Kicap Event dan screenshot referensi.
- Beranda User mobile-first dengan avatar, sapaan, lembaga, online/offline indicator, statistik, Event Aktif, dan transaksi terakhir.
- Daftar Event dengan search, filter `Semua/Aktif/Selesai/Tugas`, kartu event, progress, status, dan chevron.
- Profil User dengan avatar besar, statistik, Personal Information, Activity History, Settings, dan Logout.
- Bottom navigation User berurutan `Beranda`, `Operasional`, `Keuangan`, `Dokumentasi`, `Catatan`.
- Payload PWA diperluas memakai data nyata untuk progress, jumlah peserta, transaksi terakhir, role penugasan, dan profil lembaga.

## Guardrail

- Tidak mengubah flow login yang sudah dikunci.
- Tidak mengubah role Admin/User.
- Tidak membuat User bisa membuat event/kegiatan atau generate PDF LPJ final.
- Tidak mengubah status event/kegiatan.
- Tidak mengubah aturan transfer saldo, dana talangan, atau output LPJ final.
- Tidak rename schema/model/route internal legacy `lpj`.

## Validasi PASS

- `npm run build`
- `php -l routes/web.php`
- `php artisan test tests/Feature/Slice01UsernameLoginFormTest.php tests/Feature/Slice02LpjDetailMobileInputTest.php tests/Feature/Slice03OperationalFinanceTest.php tests/Feature/Slice04ExecutionDocumentationTest.php` — 28 tests, 126 assertions
- `php artisan test tests/Feature/Slice05ReviewFinalizationTest.php tests/Feature/Slice06ReportGenerationTest.php` — 7 tests, 53 assertions
- `php artisan migrate:fresh --seed -n`
- `php artisan route:list --path=api/app`
- `php artisan route:list --path=app`
- `php artisan test tests/Feature/HealthCheckTest.php` — 3 tests, 4 assertions
- `php artisan test` — 53 tests, 222 assertions
- `git diff --check`

## Visual Smoke

- Playwright memakai Chrome lokal berhasil membuka login, login sebagai `user@kicap.id`, dan merender Beranda, Daftar Event, serta Profil.
- Catatan runtime: PHP local server merespons lambat pada request awal sekitar 25 detik, sehingga bukti utama tetap test/build/artisan dan review manual user.

## Status

Siap review manual user sebelum commit.

## Follow-up Review User 2026-06-21

- Sapaan atas Beranda menampilkan nama depan user satu kata, bukan role.
- Tombol teks `Kembali` pada detail event dihapus karena sudah ada ikon kembali di header.
- Tampilan login Filament dipoles mengikuti screenshot referensi: background mint/ice, card putih centered, ikon aplikasi, field rounded, checkbox `Ingat saya`, tombol teal gradient, dan footer versi/copyright.
- Placeholder login diperjelas menjadi `Masukkan username/email` dan `Masukkan password`.
- Tombol submit login dilabeli `Login`.
- Frame/kotak hitam pada login dihilangkan.
- Logo login memakai mark transparan tanpa pembungkus kotak.
- Tinggi field input login diperkecil agar proporsional dengan placeholder.
- Rasio card/form login diperbaiki agar tidak terlihat gepeng: card responsif 27rem, input full-width, dan logo 80px proporsional.
- Footer login menjadi `V2.4.1 © 2026 Kulino`.
- Tabel Admin `Users` mendapat kolom Avatar/foto profil.
- Dashboard default Filament diganti dengan Dashboard Kicap Event berisi statistik operasional dan tabel Event Terbaru.
- Panduan direktori favicon/logo/PWA diperbarui di `docs/active/APP_ASSET_GUIDE.md`.
- Favicon dan logo aplikasi diganti memakai aset resmi Kicap dengan nama file `kicap-event-*`.
- Panduan deploy VPS `lpj.kicap.id` dari repo GitHub ditambahkan di `docs/deploy/VPS_LPJ_KICAP_ID_DEPLOY.md`.

Validasi tambahan PASS:

- `php -l app/Filament/Pages/Auth/Login.php`
- `php -l app/Providers/Filament/AdminPanelProvider.php`
- `php -l app/Filament/Resources/Users/Tables/UsersTable.php`
- `php -l app/Filament/Pages/AdminDashboard.php`
- `php -l app/Filament/Widgets/AdminOverviewStats.php`
- `php -l app/Filament/Widgets/LatestEventsTable.php`
- `php artisan test tests/Feature/Slice01UsernameLoginFormTest.php tests/Feature/Slice01ProfileLoginPatchTest.php` — 14 tests, 38 assertions
- `php artisan test tests/Feature/Slice01UsernameLoginFormTest.php tests/Feature/Slice01ProfileLoginPatchTest.php tests/Feature/Slice01MasterLpjRoleTest.php` — 21 tests, 62 assertions
- `npm run build`
- `php artisan route:list --path=admin/login`
- `php artisan route:list --path=admin`
- Livewire smoke `AdminDashboard` dan `ListUsers`
- Visual smoke login via Chrome lokal: card 432px, input 379px x 44px, logo 80px x 80px
- `git diff --check`

Validasi final sebelum commit/push:

- `npm run build` — manifest PWA memakai `kicap-event-logo.svg`, `kicap-event-192.png`, dan `kicap-event-512.png`
- `php -l app/Providers/Filament/AdminPanelProvider.php`
- `php -l app/Filament/Resources/OrganizationProfiles/Schemas/OrganizationProfileForm.php`
- `php -l resources/views/filament/auth/login-polish.blade.php`
- `php artisan route:list --path=admin`
- `php artisan route:list --path=admin/login`
- `php artisan route:list --path=app`
- `php artisan test tests/Feature/Slice01UsernameLoginFormTest.php tests/Feature/Slice01ProfileLoginPatchTest.php tests/Feature/Slice01MasterLpjRoleTest.php` — 21 tests, 62 assertions
- `php artisan test` — 53 tests, 222 assertions
- `git diff --check`
