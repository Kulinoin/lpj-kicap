# Slice 06 Follow-up — R2, Logo LPJ, dan Catatan User

Tanggal: 2026-06-21

## Scope

- Menyelesaikan konfigurasi Cloudflare R2 untuk media upload.
- Menyiapkan kompres otomatis foto ke WebP.
- Mengubah logo lembaga di `Organization Profiles` dari teks path menjadi upload gambar.
- Menambahkan panduan lokasi file logo aplikasi, favicon, dan ikon PWA.
- Memastikan `Beranda` User kembali menjadi halaman awal.
- Mengganti nav `Profil` menjadi `Catatan`.
- Memisahkan catatan petugas dari menu `Operasional`.

## Implementasi

- Menambahkan `StorageSetting`, migration, seeder, resource Admin `Pengaturan Penyimpanan`, dan `AppFileStorageService`.
- Dokumentasi kegiatan, lampiran, bukti transaksi, dan avatar profil menyimpan disk storage.
- Upload gambar masuk jalur WebP otomatis jika runtime mendukung `imagewebp`.
- Runtime tanpa GD/WebP fallback ke upload asli agar fitur tetap berjalan.
- `Organization Profiles` memakai `FileUpload` untuk `Logo Lembaga untuk LPJ`.
- PWA User memakai menu `Catatan` untuk activity notes.
- PWA User memakai menu `Operasional` untuk peserta, tim/panitia/pendamping, dan rundown.
- Tombol nav bawah selalu membersihkan detail event saat pindah menu, sehingga `Beranda` kembali ke layar awal.
- Menambahkan `docs/active/APP_ASSET_GUIDE.md`.

## Validasi PASS

- `php -l` file PHP yang dipatch.
- `php artisan migrate:fresh --seed -n`
- `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php tests/Feature/Slice06ReportGenerationTest.php` — 8 tests, 64 assertions.
- `php artisan test tests/Feature/Slice01UsernameLoginFormTest.php tests/Feature/Slice01ProfileLoginPatchTest.php tests/Feature/Slice01ProfileAvatarPasswordPatchTest.php tests/Feature/Slice02LpjDetailMobileInputTest.php tests/Feature/Slice03OperationalFinanceTest.php` — 29 tests, 107 assertions.
- `php artisan route:list --path=admin`
- `php artisan route:list --path=api/app`
- `php artisan route:list --path=app/lpjs`
- `npm run build`

## Catatan

- `composer dump-autoload --no-scripts --no-interaction` timeout pada fase `Generating optimized autoload files`.
- Autoload runtime tetap terverifikasi dapat membaca `League\Flysystem\AwsS3V3\AwsS3V3Adapter` dan `App\Services\AppFileStorageService`.
- Runtime lokal belum memiliki GD/WebP (`gd_loaded=no`, `imagewebp=no`), sehingga konversi WebP belum aktif di mesin lokal ini.
