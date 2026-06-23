# Progress Kicap Event

## Decision 2026-06-20 — Kicap Event

Konsep terbaru:

```text
Event / Kegiatan = objek utama yang dikelola aplikasi
LPJ = output akhir/dokumen hasil dari event/kegiatan
```

Catatan progres lama yang menyebut LPJ sebagai objek kerja dibaca sebagai legacy wording untuk Event/Kegiatan, kecuali konteksnya dokumen final/export/template LPJ.

## Slice 07 — MVP Polish & Siap Pakai

Status: Implementasi User PWA polish, admin dashboard polish, aset resmi aplikasi, dan panduan deploy VPS selesai; validasi otomatis PASS, siap commit/push.

Selesai:

- User PWA Beranda dipoles mengikuti brief/screenshot: sapaan, avatar menuju Profil, lembaga, online/offline indicator, statistik Aktif/Selesai/Tugas, Event Aktif, dan transaksi terakhir.
- User PWA memiliki layar Daftar Event dengan search, filter `Semua/Aktif/Selesai/Tugas`, kartu event mobile, progress, badge status, dan chevron.
- User Profil dipoles menjadi layar profil mobile dengan avatar besar, statistik, menu Personal Information, Activity History, Settings, dan Logout.
- Avatar profil langsung upload saat dipilih.
- Personal Information autosave ringan saat field blur/back.
- Settings password memakai password lama sebelum update.
- Bottom navigation User diurutkan sesuai brief: Beranda, Operasional, Keuangan, Dokumentasi, Catatan.
- Payload PWA menambahkan data ringkasan berbasis data nyata: role penugasan, jumlah peserta, progress, transaksi terakhir, lembaga, role label, dan tahun member.
- Login Filament dipoles dengan logo resmi Kicap, field proporsional, footer `V2.4.1 © 2026 Kulino`, dan tanpa frame hitam.
- Admin dashboard default Filament diganti dengan dashboard operasional Kicap Event.
- Tabel Admin `Users` menampilkan kolom avatar/foto profil.
- Favicon dan logo aplikasi diganti memakai aset resmi Kicap dengan nama file `kicap-event-*`.
- Panduan deploy VPS untuk domain `lpj.kicap.id` tersedia di `docs/deploy/VPS_LPJ_KICAP_ID_DEPLOY.md`.
- Tidak mengubah guardrail role, status event, transfer saldo, LPJ final, atau schema/model/route internal `lpj`.

Validasi otomatis PASS:

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

Catatan runtime:

- Playwright dengan Chrome lokal berhasil login sebagai User dan merender screen Beranda, Daftar Event, dan Profil.
- PHP local server merespons lambat pada request awal, sehingga bukti final tetap mengandalkan test/build/artisan dan review manual user.

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
- Master Event.
- Data model LPJ.

### Next Slice Revisi

Slice 01 — Master Event, Role, Login, dan UI Baseline.

## Slice 01 — Master Event & Role

Status: Reset dan akan dikerjakan ulang dari awal.

Alasan reset:

- LPJ hanya dibuat oleh Admin.
- User hanya input operasional.
- Status event/kegiatan disederhanakan menjadi `draft`, `aktif`, `finish`, `arsipkan`.
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
- Status event/kegiatan dikunci ke `draft`, `aktif`, `finish`, `arsipkan`.
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

## Slice 03 — Operasional Keuangan

Status: Implementasi + validasi otomatis PASS, menunggu review manual user sebelum commit/push.

Selesai:

- Menambahkan data model dana masuk LPJ, saldo pegangan user, mutasi saldo, transaksi operasional, dan klaim dana talangan.
- Menambahkan service transaksi keuangan berbasis DB transaction.
- Admin dapat mencatat dana masuk LPJ dan dana pegangan user melalui Filament.
- Admin dapat melihat saldo user, mutasi saldo, transaksi operasional, dan klaim talangan.
- User PWA dapat melihat saldo pada detail LPJ.
- User PWA dapat mencatat pengeluaran dari saldo pegangan dengan bukti atau alasan tanpa bukti.
- User PWA dapat transfer saldo antar user yang ditugaskan tanpa approval Admin.
- User PWA dapat mencatat dana talangan dan sistem membuat klaim internal.
- Transfer saldo tidak membuat transaksi pengeluaran LPJ.
- LPJ `finish` tetap read-only untuk input keuangan.
- Follow-up review: sumber dana masuk dijadikan pilihan `Lembaga/Sponsor/Dinas`.
- Follow-up review: kategori transaksi User dijadikan pilihan tetap.
- Follow-up review: `Input Operasional` dan `Operasional Keuangan` dipisah menjadi menu bawah `Operasional` dan `Keuangan`.
- Follow-up review: kolom `User Terkait Transfer` diberi placeholder `-` untuk mutasi non-transfer.

Validasi otomatis PASS:

- `php artisan test tests/Feature/Slice03OperationalFinanceTest.php` — 6 tests, 31 assertions
- `php artisan route:list --path=api/app`
- `php artisan route:list --path=admin`
- `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php` — 5 tests, 27 assertions
- `php artisan test` — 40 tests, 130 assertions
- `php artisan migrate:fresh --seed -n`
- `cmd.exe /c "cd /d D:\kulino\lpj-kicap && npm.cmd run build"`

## Slice 04 — Pelaksanaan & Dokumentasi

Status: Implementasi + validasi otomatis PASS, menunggu review manual user sebelum commit/push.

Selesai:

- Menambahkan data model peserta kegiatan, panitia/pendamping, rundown, dokumentasi kegiatan, dan lampiran pendukung.
- Menambahkan `ActivityExecutionService` untuk menyimpan data pelaksanaan dan upload dokumentasi/lampiran.
- Endpoint detail LPJ User menampilkan payload pelaksanaan/dokumentasi untuk LPJ yang ditugaskan.
- User PWA dapat input peserta, panitia/pendamping, dan rundown pada tab `Operasional`.
- User PWA dapat upload dokumentasi kegiatan dan lampiran pendukung.
- Catatan operasional menambahkan `Evaluasi` dan bisa dipilih `Masuk LPJ`.
- LPJ `finish` tetap read-only untuk input pelaksanaan dan upload dokumentasi.
- Guardrail Slice 03 tetap: transfer saldo tidak diubah dan tidak menjadi pengeluaran LPJ.
- Tidak membuat endpoint PDF resmi/final pada Slice 04.

Validasi otomatis PASS:

- `php artisan migrate --force`
- `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php` — 5 tests, 29 assertions
- `php artisan route:list --path=api/app`
- `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php` — 5 tests, 28 assertions
- `php artisan test tests/Feature/Slice03OperationalFinanceTest.php` — 6 tests, 32 assertions
- `php artisan test` — 45 tests, 161 assertions
- `php artisan migrate:fresh --seed -n`
- `npm run build`

## Product Concept Rename — Kicap Event

Status: Dokumentasi konsep + label UI aman diperbarui + validasi otomatis PASS, menunggu review manual user sebelum commit/push.

Selesai:

- Membuat decision doc `docs/decisions/DECISION_2026-06-20_KICAP_EVENT_RENAME.md`.
- Mengunci vocabulary: Event/Kegiatan sebagai objek utama, LPJ sebagai output dokumen akhir.
- Memperbarui README, AGENTS, PRD, workflow, role-permission, data model, roadmap, dan dokumen aktif agar keputusan terbaru menjadi panduan berikutnya.
- Mengubah label user-facing yang aman dari LPJ-as-object menjadi Event/Kegiatan.
- Tidak melakukan rename schema/model/route/service internal.

Validasi PASS:

- `rg` sisa istilah LPJ untuk review konteks.
- `php artisan route:list`
- `php artisan config:clear`
- `php artisan view:clear`
- `npm run build`
- `php artisan test tests/Feature/HealthCheckTest.php`

Archive:

- `archives/2026-06-20_210040_kicap-event-concept-rename.zip`

## Slice 05 — Review & Finalisasi Event

Status: Implementasi + validasi otomatis berjalan, menunggu review manual user sebelum commit/push.

Selesai:

- Menambahkan `LpjReviewService` untuk submit review, checklist kelengkapan, review transaksi, revisi transaksi, rekonsiliasi sederhana, dan finalisasi.
- User PWA dapat mengajukan event/kegiatan aktif untuk review Admin.
- User PWA dapat mengirim revisi transaksi miliknya saat Admin menandai transaksi `Perlu Revisi` atau `Menunggu Bukti`.
- Admin dapat melihat checklist finalisasi melalui action pada tabel Event.
- Admin dapat menandai transaksi sebagai `Valid`, `Ditolak`, `Perlu Revisi`, atau `Menunggu Bukti` melalui tabel Transaksi Event.
- Transaksi tanpa bukti tidak dapat divalidasi jika tidak memiliki alasan.
- Total pengeluaran valid dan sisa dana dihitung ulang dari transaksi berstatus `valid`.
- Admin dapat mengunci event/kegiatan menjadi `finish` hanya setelah checklist finalisasi PASS.
- Event/kegiatan `finish` tetap read-only untuk input User.
- Tidak membuat endpoint PDF resmi/final pada Slice 05.

Validasi otomatis PASS:

- `php artisan test tests/Feature/Slice05ReviewFinalizationTest.php` — 4 tests, 20 assertions
- `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php` — 5 tests, 28 assertions
- `php artisan test tests/Feature/Slice03OperationalFinanceTest.php` — 6 tests, 32 assertions
- `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php` — 5 tests, 29 assertions
- `php artisan route:list --path=api/app`
- `php artisan route:list --path=admin`
- `npm run build`
- `php artisan test` — 49 tests, 181 assertions
- `php artisan migrate:fresh --seed -n`

Follow-up UX/Admin 2026-06-20:

- User PWA: menu bawah `Selesai` diganti menjadi `Dokumentasi`.
- User PWA: dashboard menampilkan event terbaru dengan prioritas event aktif.
- User PWA: kartu event dibuat lebih modern dan font weight diturunkan agar tidak terasa penuh.
- Admin Event: form Event dirapikan dari placeholder system Filament, sumber dana menjadi select `Lembaga/Sponsor/Dinas`, dan field audit/finalisasi tidak tampil sebagai input manual.
- Admin Event: create/edit kembali ke list utama setelah simpan.
- Admin Event: list Event dipadatkan ke kolom penting.
- Admin Event: action cepat status ditambahkan, `Aktif` untuk draft dan `Selesai` untuk aktif dengan guardrail checklist finalisasi.
- Admin Dana: select Event/User pada Dana Masuk dan Mutasi Saldo diberi prompt yang jelas.

Validasi fokus PASS:

- `php -l` file Filament yang dipatch.
- `npm run build`.
- `php artisan route:list --path=admin`.
- `php artisan route:list --path=api/app`.
- `php artisan test tests/Feature/Slice01MasterLpjRoleTest.php` — 7 tests, 24 assertions.
- `php artisan test tests/Feature/Slice05ReviewFinalizationTest.php` — 4 tests, 20 assertions.
- `php artisan tinker --execute='...'` form smoke — `forms ok`.

## Slice 06 — Generate Dokumen LPJ

Status: Implementasi + validasi otomatis fokus PASS, menunggu review manual user sebelum commit/push.

Selesai:

- Menambahkan dependency `barryvdh/laravel-dompdf` untuk export PDF server-side.
- Menambahkan `LpjReportService` untuk menyusun payload LPJ final dari event/kegiatan `finish`.
- Menambahkan print-ready view formal LPJ dengan cover, halaman pengesahan, footer/nomor halaman, identitas kegiatan, pelaksanaan, keuangan global, transaksi valid, dokumentasi, lampiran, dan penutup.
- Menambahkan route Admin `admin/reports/lpjs/{lpj}/print` dan `admin/reports/lpjs/{lpj}/pdf`.
- Menambahkan action Admin `Preview LPJ` dan `PDF LPJ` pada tabel Event, hanya tampil untuk event/kegiatan `finish`.
- Output final hanya mengambil transaksi berstatus `valid`.
- Transfer saldo, saldo per user, klaim/reimbursement, transaksi ditolak, catatan internal, serta dokumentasi/lampiran yang tidak ditandai `Masuk LPJ` tidak tampil default di LPJ final.
- Dana talangan yang sudah valid tetap tampil sebagai biaya kegiatan.
- User tetap tidak dapat membuat/generate LPJ final.

Validasi otomatis PASS:

- `php -l app/Services/LpjReportService.php`
- `php -l routes/web.php`
- `php -l tests/Feature/Slice06ReportGenerationTest.php`
- `php artisan test tests/Feature/Slice06ReportGenerationTest.php` — 2 tests, 19 assertions
- `php artisan test tests/Feature/Slice05ReviewFinalizationTest.php` — 4 tests, 20 assertions
- `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php` — 5 tests, 29 assertions
- `php artisan route:list --path=admin`
- `php artisan route:list --path=api/app`
- `npm run build`
- `php artisan migrate:fresh --seed -n`
- `git diff --check`

Catatan validasi:

- Kombinasi test Slice 04 + Slice 05 dalam satu command melewati timeout runtime lokal, lalu dijalankan terpisah dan keduanya PASS.

Follow-up dana/print 2026-06-20:

- Menambahkan aturan bahwa total dana yang dialokasikan Admin ke user untuk satu event tidak boleh lebih besar dari dana masuk event.
- `Dana Masuk Event` menampilkan `Alokasi Dana` dan `Sisa Alokasi` untuk membantu Admin memantau plafon dana.
- Payload keuangan User menampilkan `allocated_fund` dan `remaining_allocation`.
- User yang ditugaskan dapat membuka print preview LPJ untuk event/kegiatan `finish`.
- Tombol `Cetak LPJ` tampil pada detail event selesai di PWA User.
- PDF export Admin tetap tersedia, tetapi jalur ringan utama untuk User adalah print preview HTML.

Validasi follow-up PASS:

- `php artisan test tests/Feature/Slice03OperationalFinanceTest.php` — 7 tests, 38 assertions
- `php artisan test tests/Feature/Slice06ReportGenerationTest.php` — 2 tests, 26 assertions
- `php artisan test tests/Feature/Slice01MasterLpjRoleTest.php tests/Feature/Slice01UsernameLoginFormTest.php tests/Feature/Slice01ProfileLoginPatchTest.php tests/Feature/Slice01ProfileAvatarPasswordPatchTest.php tests/Feature/Slice02LpjDetailMobileInputTest.php` — 29 tests, 93 assertions
- `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php tests/Feature/Slice05ReviewFinalizationTest.php` — 9 tests, 49 assertions
- `php artisan route:list --path=app/lpjs`
- `php artisan route:list --path=api/app`
- `npm run build`
- `php artisan migrate:fresh --seed -n`
- `git diff --check`

Follow-up Dokumen LPJ 2026-06-20:

- Menambahkan tabel snapshot `lpj_report_snapshots`.
- Menambahkan menu Admin `Dokumen LPJ`.
- Setiap Admin/User membuka print preview LPJ final atau Admin export PDF, sistem menyimpan snapshot HTML print-ready.
- Snapshot menyimpan nomor versi, event, user pembuat, sumber generate, total dana masuk, total pengeluaran valid, total sisa dana, waktu generate, dan HTML dokumen.
- Admin dapat membuka ulang snapshot dari menu `Dokumen LPJ`.

Validasi follow-up PASS:

- `php -l` model/service/route/resource/test terkait snapshot
- `php artisan migrate:fresh --seed -n`
- `php artisan test tests/Feature/Slice06ReportGenerationTest.php` — 3 tests, 32 assertions
- `php artisan test tests/Feature/Slice03OperationalFinanceTest.php` — 7 tests, 38 assertions
- `php artisan route:list --path=admin`
- `npm run build`
- `git diff --check`

Follow-up PWA User 2026-06-20:

- Memisahkan tampilan role User: menu `Operasional` hanya berisi catatan petugas, peserta, panitia/pendamping, dan rundown.
- Upload dokumentasi, upload lampiran, dan daftar file tersimpan dipindahkan penuh ke menu `Dokumentasi`.
- Klik event dari `Beranda` sekarang membuka detail event read-only, bukan masuk ke menu `Operasional`.
- Detail event dari `Beranda` hanya menampilkan informasi event dan akses `Cetak LPJ` jika LPJ sudah tersedia.
- API/data model tidak berubah; perubahan hanya pada pemisahan halaman PWA.

Validasi follow-up PASS:

- `npm run build`
- `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php tests/Feature/Slice02LpjDetailMobileInputTest.php` — 10 tests, 57 assertions
- `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php tests/Feature/Slice06ReportGenerationTest.php` — 8 tests, 60 assertions
- `php artisan route:list --path=api/app`
- `git diff --check`

Follow-up Storage, Logo, dan Catatan 2026-06-21:

- Menambahkan pengaturan Admin `Pengaturan Penyimpanan` untuk memilih Local Storage atau Cloudflare R2.
- Menambahkan dependency S3 adapter agar Cloudflare R2 dapat dipakai lewat driver storage S3-compatible.
- Dokumentasi kegiatan, lampiran, bukti transaksi, dan avatar profil sekarang menyimpan metadata disk agar URL tetap benar saat pindah provider.
- Upload gambar dokumentasi/lampiran/proof/avatar disiapkan untuk kompres otomatis ke WebP saat runtime server memiliki GD/WebP.
- Runtime lokal saat validasi belum memiliki GD/WebP, sehingga sistem fallback aman ke file asli.
- Menu `Organization Profiles` memakai upload gambar `Logo Lembaga untuk LPJ`, bukan input teks path.
- Menambahkan panduan aset aplikasi di `docs/active/APP_ASSET_GUIDE.md`.
- Favicon browser fallback `public/favicon.ico` didaftarkan di halaman PWA dan asset PWA build.
- Bottom navigation User mengganti `Profil` menjadi `Catatan`.
- Menu `Catatan` menjadi tempat input catatan petugas.
- Menu `Operasional` hanya berisi data pelaksanaan: peserta, tim/panitia/pendamping, dan rundown.
- Tombol `Beranda` selalu kembali ke layar awal ringkasan dan daftar event, bukan mempertahankan detail event terakhir.

Validasi follow-up PASS:

- `php -l` file PHP yang dipatch.
- `php artisan migrate:fresh --seed -n`
- `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php tests/Feature/Slice06ReportGenerationTest.php` — 8 tests, 64 assertions.
- `php artisan test tests/Feature/Slice01UsernameLoginFormTest.php tests/Feature/Slice01ProfileLoginPatchTest.php tests/Feature/Slice01ProfileAvatarPasswordPatchTest.php tests/Feature/Slice02LpjDetailMobileInputTest.php tests/Feature/Slice03OperationalFinanceTest.php` — 29 tests, 107 assertions.
- `php artisan route:list --path=admin`
- `php artisan route:list --path=api/app`
- `php artisan route:list --path=app/lpjs`
- `npm run build`

Catatan validasi:

- `composer dump-autoload --no-scripts --no-interaction` timeout pada fase `Generating optimized autoload files`, tetapi autoload sudah dapat membaca S3 adapter dan service aplikasi.



## Patch 2026-06-22 18:58:49 — Stabilisasi Upload Kamera dan Kompresi Gambar

- PWA sudah dipulihkan ke kondisi normal setelah percobaan patch detail keuangan dibatalkan.
- Fitur upload dari kamera/galeri tetap aman dan aktif.
- File gambar besar dikompresi di frontend sebelum upload menggunakan canvas dan output JPEG agar upload dari kamera HP lebih ringan.
- Batas upload bukti transaksi/revisi dinaikkan menjadi 10MB untuk keamanan upload setelah kompresi.
- Patch detail riwayat transaksi keuangan ditunda dan tidak masuk commit ini.
- Tidak ada perubahan database dan tidak menjalankan migrasi.


## Patch 2026-06-23 10:01:02 — Patch 1A — API Detail Transaksi Keuangan

- Menambahkan endpoint read-only detail transaksi keuangan untuk PWA.
- Endpoint hanya dapat diakses User pada event/LPJ yang terlihat/ditugaskan kepadanya.
- Payload detail mencakup nominal, status, sumber dana, kategori, keterangan, alasan tanpa bukti, catatan Admin, reviewer, klaim dana talangan, dan bukti transaksi jika tersedia.
- Belum ada perubahan UI PWA pada patch ini.
- Tidak mengubah struktur database dan tidak menjalankan migrasi.


## Patch 2026-06-23 10:03:10 — Patch 1B — Tombol Lihat Detail Riwayat Keuangan

- Menambahkan tombol sederhana `Lihat detail` pada riwayat transaksi keuangan PWA.
- Tombol mengambil data dari endpoint detail transaksi Patch 1A.
- Detail sementara ditampilkan menggunakan alert agar risiko UI/modal tetap rendah.
- Belum menambahkan bottom sheet/modal dan belum menambahkan viewer lampiran fullscreen.
- Tidak mengubah struktur database dan tidak menjalankan migrasi.


## Patch 2026-06-23 10:49:54 — Patch 1C — Dialog Minimal Detail Keuangan

- Mengganti tombol `Lihat detail` menjadi item riwayat yang dapat ditekan langsung.
- Riwayat keuangan kini menampilkan tanggal, bukan tombol detail.
- Detail keuangan ditampilkan dalam custom dialog minimal berisi kategori, nominal, tanggal, keterangan, dan bukti.
- Bukti gambar ditampilkan sebagai preview kecil responsif; PDF/lampiran dibuka melalui tautan.
- Pola ini menjadi acuan untuk detail operasional dan dokumentasi: tampilkan hanya data inti sesuai form.
- Tidak mengubah struktur database dan tidak menjalankan migrasi.


## Patch 2026-06-23 10:57:47 — Patch 1C FIX — Popup Center dan Bukti Sederhana

- Popup detail keuangan dipusatkan di tengah layar, bukan menempel di bawah.
- Riwayat terbaru menampilkan format ringkas `Kategori - tanggal`.
- Preview/buka bukti foto di PWA dihilangkan sementara; popup hanya menampilkan status bukti tersimpan atau belum ada bukti.
- Detail tetap minimal sesuai data inti form: kategori, nominal, tanggal, keterangan, dan bukti.
- Tidak mengubah struktur database dan tidak menjalankan migrasi.


## Patch 2026-06-23 11:05:21 — Patch 1C FIX v2 — Slash Tanggal dan Preview Bukti Foto

- Format riwayat keuangan diubah menjadi `Kategori / tanggal` agar tidak rancu dengan tanda hubung pada tanggal.
- Popup detail keuangan menampilkan preview bukti foto langsung di dalam popup.
- Preview foto tidak dijadikan tombol buka foto; hanya ditampilkan sebagai bukti visual di popup.
- Lampiran non-gambar tetap ditampilkan sebagai status lampiran tersimpan.
- Tidak mengubah struktur database dan tidak menjalankan migrasi.


## Patch 2026-06-23 11:08:38 — Patch 1C FIX — Preview Bukti via Auth Route

- Menambahkan route khusus untuk menampilkan bukti transaksi melalui Laravel/API yang sudah terautentikasi.
- Payload detail transaksi kini memakai URL preview API, bukan URL storage publik langsung.
- Tujuannya agar preview WebP/JPG/PNG tetap tampil di popup PWA meskipun file storage tidak terbuka langsung dari public path.
- Tidak mengubah struktur database dan tidak menjalankan migrasi.


## Patch 2026-06-23 11:38:52 — Patch 1C FIX — Tampilkan Alasan Jika Tidak Ada Bukti

- Popup detail keuangan kini menampilkan `Ada lampiran.` jika bukti transaksi tersedia.
- Jika bukti tidak tersedia, popup menampilkan isi field `Alasan jika tidak ada bukti` dari transaksi.
- Preview/buka foto tidak ditampilkan di PWA untuk menjaga tampilan tetap stabil dan sederhana.
- Tidak mengubah struktur database dan tidak menjalankan migrasi.
