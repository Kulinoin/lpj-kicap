# Archive Slice 06 Follow-up — Menu Dokumen LPJ

Tanggal: 2026-06-20

## Ringkasan

Follow-up ini menambahkan menu Admin `Dokumen LPJ` sebagai daftar snapshot LPJ final yang sudah dihasilkan.

## Perubahan

- Menambahkan migration `lpj_report_snapshots`.
- Menambahkan model `LpjReportSnapshot`.
- Menambahkan resource Filament `Dokumen LPJ`.
- Menambahkan route Admin untuk membuka ulang snapshot.
- Menyimpan snapshot saat:
  - Admin membuka print preview LPJ.
  - Admin export PDF LPJ.
  - User membuka print preview LPJ untuk event selesai.

## Data Snapshot

- Event/kegiatan.
- Nomor snapshot dan versi.
- User pembuat snapshot.
- Sumber generate.
- Total dana masuk.
- Total pengeluaran valid.
- Total sisa dana.
- HTML print-ready.
- Waktu generate.

## Validasi PASS

- `php -l` model/service/route/resource/test snapshot.
- `php artisan migrate:fresh --seed -n`.
- `php artisan test tests/Feature/Slice06ReportGenerationTest.php` — 3 tests, 32 assertions.
- `php artisan test tests/Feature/Slice03OperationalFinanceTest.php` — 7 tests, 38 assertions.
- `php artisan route:list --path=admin`.
- `npm run build`.
- `git diff --check`.
