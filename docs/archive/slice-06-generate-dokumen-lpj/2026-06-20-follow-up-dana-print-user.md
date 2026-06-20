# Archive Slice 06 Follow-up — Dana & Print User

Tanggal: 2026-06-20

## Ringkasan

Follow-up ini menambahkan kontrol plafon dana pegangan user dan membuka print preview LPJ untuk User pada event/kegiatan yang sudah selesai.

## Perubahan

- Total alokasi dana pegangan user (`fund_in`) tidak boleh melebihi dana masuk event.
- `Dana Masuk Event` menampilkan:
  - `Alokasi Dana`
  - `Sisa Alokasi`
- Payload keuangan User menampilkan alokasi dana dan sisa alokasi.
- User assigned dapat membuka route print preview LPJ untuk event/kegiatan `finish`.
- PWA User menampilkan tombol `Cetak LPJ` pada detail event selesai.
- PDF export Admin tetap tersedia; jalur ringan User menggunakan print preview HTML.

## Validasi PASS

- `php -l app/Models/Lpj.php`
- `php -l app/Services/LpjFinanceService.php`
- `php -l routes/web.php`
- `php -l tests/Feature/Slice03OperationalFinanceTest.php`
- `php -l tests/Feature/Slice06ReportGenerationTest.php`
- `npm run build`
- `php artisan route:list --path=app/lpjs`
- `php artisan route:list --path=api/app`
- `php artisan test tests/Feature/Slice03OperationalFinanceTest.php` — 7 tests, 38 assertions
- `php artisan test tests/Feature/Slice06ReportGenerationTest.php` — 2 tests, 26 assertions
- `php artisan test tests/Feature/Slice01MasterLpjRoleTest.php tests/Feature/Slice01UsernameLoginFormTest.php tests/Feature/Slice01ProfileLoginPatchTest.php tests/Feature/Slice01ProfileAvatarPasswordPatchTest.php tests/Feature/Slice02LpjDetailMobileInputTest.php` — 29 tests, 93 assertions
- `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php tests/Feature/Slice05ReviewFinalizationTest.php` — 9 tests, 49 assertions
- `php artisan migrate:fresh --seed -n`
- `git diff --check`

## Catatan Produk

Definisi alokasi yang dipakai adalah total mutasi Admin `fund_in`, bukan saldo user saat ini. Dengan begitu sisa alokasi tidak berubah hanya karena User mencatat pengeluaran.
