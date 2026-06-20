# Slice 05 Follow-up — UX User dan Admin Event

Tanggal: 2026-06-20 21:55 WIB

## Status

Patch follow-up dan validasi fokus PASS.

## Perubahan User PWA

- Menu bawah `Selesai` diganti menjadi `Dokumentasi`.
- Event selesai tetap dapat dibuka dari Beranda.
- Dashboard mengutamakan event aktif dan mengurutkan event terbaru.
- Kartu event dibuat lebih modern dengan aksen visual, shadow lebih halus, dan hierarchy teks lebih ringan.
- Font weight PWA diturunkan agar layar tidak terasa terlalu penuh.

## Perubahan Admin

- Form Event tidak lagi menampilkan placeholder system Filament pada select.
- Repeater `User Ditugaskan` memakai label `Tambah User Ditugaskan`.
- Field audit/finalisasi internal tidak lagi tampil sebagai input manual di form Event.
- Sumber dana Event menjadi select: `Lembaga`, `Sponsor`, `Dinas`.
- Create/edit Event kembali ke list utama setelah simpan.
- List Event dipadatkan ke kolom penting.
- Action status cepat:
  - Draft menampilkan tombol `Aktif`.
  - Aktif menampilkan tombol `Selesai`.
  - Tombol `Selesai` tetap memakai service finalisasi dan checklist Slice 05.
- Select pada Dana Masuk, Mutasi Saldo, dan Role User diberi prompt jelas agar tidak menampilkan key placeholder system.
- Create Dana Masuk dan Dana Pegangan User kembali ke list utama setelah simpan.

## Validasi Fokus PASS

- `php -l` untuk file Filament yang dipatch.
- `npm run build`.
- `php artisan route:list --path=admin`.
- `php artisan route:list --path=api/app`.
- `php artisan test tests/Feature/Slice01MasterLpjRoleTest.php` — 7 tests, 24 assertions.
- `php artisan test tests/Feature/Slice05ReviewFinalizationTest.php` — 4 tests, 20 assertions.
- `php artisan tinker --execute='...'` form smoke — `forms ok`.

## Catatan

- Validasi full suite tidak dijalankan sesuai instruksi user agar patch lebih cepat dan tetap fokus.
