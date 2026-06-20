# Archive Slice 06 Follow-up — PWA User Dokumentasi

Tanggal: 2026-06-20

## Ringkasan

Follow-up ini memisahkan menu `Operasional` dan `Dokumentasi` pada PWA User.

## Perubahan

- Menu `Operasional` hanya menampilkan:
  - Catatan petugas.
  - Data peserta.
  - Panitia/pendamping.
  - Rundown.
- Menu `Dokumentasi` menampilkan:
  - Upload dokumentasi kegiatan.
  - Upload lampiran pendukung.
  - Daftar file tersimpan.
- Klik event dari `Beranda` membuka detail event read-only.
- Detail event dari `Beranda` tidak menampilkan form operasional, keuangan, atau dokumentasi.
- Jika LPJ sudah tersedia, detail event dari `Beranda` menampilkan akses `Cetak LPJ`.

## Validasi PASS

- `npm run build`.
- `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php tests/Feature/Slice02LpjDetailMobileInputTest.php` — 10 tests, 57 assertions.
- `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php tests/Feature/Slice06ReportGenerationTest.php` — 8 tests, 60 assertions.
- `php artisan route:list --path=api/app`.
- `git diff --check`.
