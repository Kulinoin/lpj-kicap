# Slice 06 — Generate Dokumen LPJ

Tanggal: 2026-06-20 22:40 WIB

## Tujuan

Membuat output LPJ final untuk event/kegiatan yang sudah `finish`, mencakup print-ready view dan export PDF formal.

## Scope

- Cover formal.
- Halaman pengesahan dengan kop lengkap.
- Footer dan nomor halaman.
- Isi LPJ global.
- Keuangan global.
- Rincian transaksi valid.
- Dokumentasi kegiatan.
- Lampiran.
- Export PDF.
- Print-ready view.

## Implementasi

- Menambahkan `LpjReportService` sebagai penyusun data final LPJ.
- Menambahkan template `resources/views/reports/lpj-final.blade.php`.
- Menambahkan route Admin:
  - `admin/reports/lpjs/{lpj}/print`
  - `admin/reports/lpjs/{lpj}/pdf`
- Menambahkan action tabel Event:
  - `Preview LPJ`
  - `PDF LPJ`
- Menambahkan dependency `barryvdh/laravel-dompdf`.
- Menambahkan test `tests/Feature/Slice06ReportGenerationTest.php`.
- Menambahkan aturan plafon alokasi dana pegangan user.
- Menambahkan kolom `Alokasi Dana` dan `Sisa Alokasi` pada `Dana Masuk Event`.
- Menambahkan print preview LPJ untuk User yang ditugaskan pada event/kegiatan `finish`.

## Guardrail

- Output final hanya untuk event/kegiatan `finish`.
- User tidak dapat membuka route generate/preview LPJ final.
- Transaksi yang tampil hanya status `valid`.
- Transfer saldo tidak tampil.
- Klaim/reimbursement dana talangan tidak tampil default.
- Dana talangan yang valid tampil sebagai biaya kegiatan.
- Dokumentasi/lampiran/catatan internal tidak tampil jika tidak ditandai `Masuk LPJ`.
- Total alokasi dana pegangan user tidak boleh melewati total dana masuk event.
- User hanya dapat membuka print preview LPJ untuk event/kegiatan assigned berstatus `finish`.

## Validasi PASS

- `php -l app/Services/LpjReportService.php`
- `php -l routes/web.php`
- `php -l tests/Feature/Slice06ReportGenerationTest.php`
- `php artisan test tests/Feature/Slice06ReportGenerationTest.php`
- `php artisan test tests/Feature/Slice03OperationalFinanceTest.php`
- Test login/profile/detail operasional User
- Test pelaksanaan/dokumentasi dan review/finalisasi
- `php artisan test tests/Feature/Slice05ReviewFinalizationTest.php`
- `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php`
- `php artisan route:list --path=admin`
- `php artisan route:list --path=api/app`
- `npm run build`
- `php artisan migrate:fresh --seed -n`
- `git diff --check`

## Handoff

Review manual user fokus pada event/kegiatan `finish`: buka preview LPJ, cek konten formal, cek filter transaksi valid, cek transfer/klaim tidak tampil, lalu download PDF.
