# Slice 04 — Pelaksanaan & Dokumentasi

Status: Implementasi + validasi otomatis PASS, menunggu review manual user sebelum commit/push.

## Vocabulary Note

Mulai decision 20 Juni 2026, Slice 04 dipahami sebagai pelaksanaan dan dokumentasi **Event/Kegiatan**. LPJ hanya menjadi output dokumen akhir yang menerima data terpilih dari catatan, dokumentasi, dan lampiran.

## Scope

- Catatan pelaksanaan kegiatan.
- Data peserta.
- Data panitia/pendamping.
- Rundown.
- Dokumentasi kegiatan.
- Lampiran pendukung.
- Evaluasi/kendala/saran yang dapat dipilih masuk LPJ.

## Out of Scope

- User membuat LPJ.
- User mengurus narasi formal Admin.
- PDF resmi/final.
- Perubahan aturan transfer saldo.
- Menampilkan transfer saldo di output final LPJ default.
- Review/finalisasi Admin.

## Implementasi

- Data model baru: `activity_participants`, `activity_committees`, `activity_schedules`, `activity_documentations`, dan `activity_attachments`.
- Service baru: `ActivityExecutionService`.
- Endpoint baru:
  - `POST /api/app/lpjs/{lpj}/execution-data`
  - `POST /api/app/lpjs/{lpj}/documentations`
  - `POST /api/app/lpjs/{lpj}/attachments`
- Detail LPJ User memuat payload `execution`.
- PWA tab `Operasional` memuat input peserta, panitia/pendamping, rundown, dokumentasi, lampiran, dan daftar file tersimpan.
- Catatan operasional menambah tipe `Evaluasi` dan flag `include_in_report`.

## Guardrail

- Endpoint memakai scope LPJ yang ditugaskan kepada User.
- Input hanya aktif untuk LPJ status `aktif`.
- LPJ `finish` read-only untuk input pelaksanaan dan upload dokumentasi/lampiran.
- Tidak ada endpoint PDF resmi/final pada Slice 04.
- Input pelaksanaan tidak membuat transaksi keuangan.

## Validasi PASS

- `php artisan migrate --force`
- `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php` — 5 tests, 29 assertions
- `php artisan route:list --path=api/app`
- `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php` — 5 tests, 28 assertions
- `php artisan test tests/Feature/Slice03OperationalFinanceTest.php` — 6 tests, 32 assertions
- `php artisan test` — 45 tests, 161 assertions
- `php artisan migrate:fresh --seed -n`
- `npm run build`
