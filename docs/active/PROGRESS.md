# Progress Kicap Event

## Decision 2026-06-20 — Kicap Event

Konsep terbaru:

```text
Event / Kegiatan = objek utama yang dikelola aplikasi
LPJ = output akhir/dokumen hasil dari event/kegiatan
```

Catatan progres lama yang menyebut LPJ sebagai objek kerja dibaca sebagai legacy wording untuk Event/Kegiatan, kecuali konteksnya dokumen final/export/template LPJ.

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
