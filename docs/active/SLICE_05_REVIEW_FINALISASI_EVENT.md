# Slice 05 — Review & Finalisasi Event

## Status

Implementasi berjalan pada 20 Juni 2026.

## Scope

- Checklist kelengkapan event/kegiatan untuk Admin.
- User dapat mengajukan event/kegiatan aktif untuk review Admin.
- Admin dapat review transaksi operasional.
- Admin dapat menandai transaksi `valid`, `ditolak`, `perlu_revisi`, atau `menunggu_bukti`.
- User dapat mengirim revisi transaksi miliknya setelah diminta Admin.
- Transaksi tanpa bukti wajib memiliki alasan sebelum dapat divalidasi.
- Rekonsiliasi saldo sederhana berdasarkan dana diterima, pengeluaran valid, sisa dana, dan saldo user.
- Admin dapat mengunci event/kegiatan sebagai `finish` jika checklist finalisasi PASS.

## Out of Scope

- Generate PDF resmi/final.
- Preview/export dokumen LPJ.
- Perubahan aturan transfer saldo.
- Perubahan aturan dana talangan/reimbursement.
- Role tambahan.
- Rename agresif schema/model/route internal `lpj`.

## Guardrail

- User tetap tidak membuat event/kegiatan atau LPJ.
- User tetap hanya input operasional pada event/kegiatan yang ditugaskan.
- Event/kegiatan `finish` tetap read-only untuk input operasional, keuangan, pelaksanaan, dokumentasi, dan revisi transaksi.
- Transfer saldo tetap langsung tanpa approval Admin dan tidak masuk pengeluaran LPJ final.
- Reimbursement klaim dana talangan tidak dihitung ulang sebagai pengeluaran LPJ.
- Tidak ada endpoint PDF resmi/final pada Slice 05.

## Validasi

- PASS: `php artisan test tests/Feature/Slice05ReviewFinalizationTest.php` — 4 tests, 20 assertions.
- PASS: `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php` — 5 tests, 28 assertions.
- PASS: `php artisan test tests/Feature/Slice03OperationalFinanceTest.php` — 6 tests, 32 assertions.
- PASS: `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php` — 5 tests, 29 assertions.
- PASS: `php artisan route:list --path=api/app`.
- PASS: `php artisan route:list --path=admin`.
- PASS: `npm run build`.
- PASS: `php artisan test` — 49 tests, 181 assertions.
- PASS: `php artisan migrate:fresh --seed -n`.

## Catatan Runtime

- Artisan boot di WSL path `/mnt/d/kulino/lpj-kicap` lambat pada run ini; `php artisan list --raw` selesai sekitar 95 detik.
- Test Slice 05 PASS, tetapi wall time lebih panjang dari durasi PHPUnit karena boot awal lambat.
