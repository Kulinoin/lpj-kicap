# Worklog Kicap Event

## 2026-06-21 — Slice 07 MVP Polish User PWA

- Memoles User PWA mengikuti brief UI Kicap Event dan screenshot referensi dashboard, daftar event, dan profil.
- Mengubah Beranda User menjadi ringkasan mobile-first: sapaan, avatar menuju Profil, nama lembaga, indikator online/offline, statistik Aktif/Selesai/Tugas, maksimal dua Event Aktif, dan transaksi terakhir.
- Menambahkan layar Daftar Event di dalam PWA dengan search client-side, tab filter `Semua/Aktif/Selesai/Tugas`, kartu event ber-accent bar, badge status, progress, dan chevron.
- Merapikan Profil User menjadi layar profil dengan avatar besar, statistik, menu Personal Information, Activity History, Settings, dan Logout.
- Avatar profil langsung upload saat dipilih; Personal Information autosave ringan saat blur/back; Settings password memakai password lama sebelum update.
- Menata ulang bottom navigation User menjadi `Beranda`, `Operasional`, `Keuangan`, `Dokumentasi`, `Catatan` dengan tombol tengah Keuangan sebagai aksi visual utama.
- Memperluas payload `/api/app/lpjs` dengan role penugasan, jumlah peserta, progress ringkas, dan transaksi terakhir dari data nyata.
- Memperluas payload `/api/app/profile` dengan nama lembaga, role label, dan tahun member.
- Menjaga guardrail: tidak mengubah login, role Admin/User, status event, transfer saldo, PDF LPJ final, atau schema/model internal `lpj`.
- Validasi PASS: `npm run build`.
- Validasi PASS: `php -l routes/web.php`.
- Validasi PASS: `php artisan test tests/Feature/Slice01UsernameLoginFormTest.php tests/Feature/Slice02LpjDetailMobileInputTest.php tests/Feature/Slice03OperationalFinanceTest.php tests/Feature/Slice04ExecutionDocumentationTest.php` — 28 tests, 126 assertions.
- Validasi PASS: `php artisan test tests/Feature/Slice05ReviewFinalizationTest.php tests/Feature/Slice06ReportGenerationTest.php` — 7 tests, 53 assertions.
- Validasi PASS: `php artisan migrate:fresh --seed -n`.
- Validasi PASS: `php artisan route:list --path=api/app` dan `php artisan route:list --path=app`.
- Validasi PASS: `php artisan test tests/Feature/HealthCheckTest.php` — 3 tests, 4 assertions.
- Validasi PASS: `php artisan test` — 53 tests, 222 assertions.
- Validasi PASS: `git diff --check`.
- Visual smoke: Playwright memakai Chrome lokal berhasil membuka login dan masuk sebagai `user@kicap.id`, lalu merender Beranda/Daftar Event/Profil. Catatan runtime: PHP local server merespons lambat sekitar 25 detik untuk request awal, sehingga validasi utama tetap memakai artisan/test/build.

### Follow-up Review User 2026-06-21

- Mengunci sapaan atas Beranda agar menampilkan nama depan user satu kata, bukan role.
- Menghapus tombol teks `Kembali` di detail event karena header sudah memiliki ikon kembali dengan fungsi yang sama.
- Memoles tampilan login Filament mengikuti screenshot referensi: background mint/ice, card putih centered, ikon aplikasi, field rounded, checkbox `Ingat saya`, tombol teal gradient, dan footer versi/copyright.
- Menambahkan placeholder login `Masukkan username/email` dan `Masukkan password`.
- Mengubah label tombol submit login menjadi `Login`.
- Menghilangkan frame/kotak hitam pada login dan memakai mark login transparan tanpa pembungkus kotak.
- Mengecilkan tinggi field input login agar lebih proporsional dengan placeholder.
- Memperbaiki rasio card/form login agar tidak terlihat gepeng: card responsif 27rem, input full-width, dan logo 80x80 proporsional.
- Mengubah footer login menjadi `V2.4.1 © 2026 Kulino` dengan font tipis.
- Menambahkan kolom Avatar pada tabel Admin `Users`.
- Mengganti dashboard default Filament dengan dashboard Kicap Event berisi statistik operasional dan tabel Event Terbaru.
- Menambahkan daftar direktori aset favicon/logo/PWA pada `docs/active/APP_ASSET_GUIDE.md`.
- Mengganti favicon dan logo aplikasi dengan aset resmi Kicap dari folder `D:/kulino/Logo`.
- Menyesuaikan nama file aset aplikasi menjadi `kicap-event-logo.svg`, `kicap-event-192.png`, `kicap-event-512.png`, dan `kicap-event-apple-touch.png`.
- Menambahkan panduan deploy VPS domain `lpj.kicap.id` via repo GitHub di `docs/deploy/VPS_LPJ_KICAP_ID_DEPLOY.md`.
- Validasi PASS: syntax `Login.php` dan `AdminPanelProvider.php`.
- Validasi PASS: `php artisan test tests/Feature/Slice01UsernameLoginFormTest.php tests/Feature/Slice01ProfileLoginPatchTest.php` — 14 tests, 38 assertions.
- Validasi PASS: `npm run build`.
- Validasi PASS: `php artisan route:list --path=admin/login`.
- Validasi PASS: syntax file dashboard/widget/user table.
- Validasi PASS: `php artisan route:list --path=admin`.
- Validasi PASS: `php artisan test tests/Feature/Slice01UsernameLoginFormTest.php tests/Feature/Slice01ProfileLoginPatchTest.php tests/Feature/Slice01MasterLpjRoleTest.php` — 21 tests, 62 assertions.
- Validasi PASS: Livewire smoke `AdminDashboard` dan `ListUsers`.
- Validasi PASS: visual smoke login via Chrome lokal, card 432px, input 379px x 44px, logo 80px x 80px.
- Validasi PASS: `git diff --check`.

## 2026-06-21 — Slice 06 Follow-up R2, Logo LPJ, dan Catatan User

- Melanjutkan implementasi Cloudflare R2 dengan resource Admin `Pengaturan Penyimpanan`.
- Menambahkan `AppFileStorageService` untuk memilih disk aktif, membuat URL file, menghapus file lama, dan menyiapkan konversi gambar ke WebP.
- Menambahkan metadata disk pada dokumentasi kegiatan, lampiran, bukti transaksi, dan avatar profil.
- Menambahkan fallback aman jika runtime belum memiliki GD/WebP.
- Mengubah `Organization Profiles` agar `Logo Lembaga untuk LPJ` berupa upload gambar.
- Menambahkan fallback favicon dan panduan aset aplikasi di `docs/active/APP_ASSET_GUIDE.md`.
- Mengganti nav User `Profil` menjadi `Catatan`.
- Memindahkan input catatan petugas ke menu `Catatan`.
- Memastikan menu `Operasional` hanya berisi peserta, tim/panitia/pendamping, dan rundown.
- Memastikan klik `Beranda` membersihkan detail dan kembali ke layar awal.
- Validasi PASS: `php artisan migrate:fresh --seed -n`.
- Validasi PASS: `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php tests/Feature/Slice06ReportGenerationTest.php` — 8 tests, 64 assertions.
- Validasi PASS: `php artisan test tests/Feature/Slice01UsernameLoginFormTest.php tests/Feature/Slice01ProfileLoginPatchTest.php tests/Feature/Slice01ProfileAvatarPasswordPatchTest.php tests/Feature/Slice02LpjDetailMobileInputTest.php tests/Feature/Slice03OperationalFinanceTest.php` — 29 tests, 107 assertions.
- Validasi PASS: `php artisan route:list --path=admin`, `php artisan route:list --path=api/app`, dan `php artisan route:list --path=app/lpjs`.
- Validasi PASS: `npm run build`.
- Validasi PASS: `git diff --check`.
- Catatan runtime: `composer dump-autoload --no-scripts --no-interaction` timeout, tetapi autoload package S3 dan service aplikasi terverifikasi bisa dibaca.
- Catatan runtime: GD/WebP lokal belum aktif, sehingga WebP auto-compress akan aktif setelah server menyediakan `imagewebp`.

## 2026-06-20 22:40 WIB — Slice 06 Generate Dokumen LPJ

- Menambahkan generator dokumen LPJ final berbasis `LpjReportService`.
- Menambahkan print-ready Blade view untuk cover formal, halaman pengesahan dengan kop lengkap, isi LPJ global, keuangan global, rincian transaksi valid, dokumentasi, lampiran, dan penutup.
- Menambahkan export PDF server-side memakai Dompdf.
- Menambahkan route Admin untuk preview print-ready dan download PDF LPJ.
- Menambahkan action `Preview LPJ` dan `PDF LPJ` pada tabel Event, hanya untuk event/kegiatan `finish`.
- Menjaga guardrail: output final hanya untuk `finish`, hanya transaksi valid yang tampil, transfer saldo tidak tampil, klaim/reimbursement tidak tampil sebagai pengeluaran baru, dan dana talangan valid tampil sebagai biaya kegiatan.
- Menambahkan coverage test `Slice06ReportGenerationTest`.
- Validasi PASS: syntax PHP file baru/route/test.
- Validasi PASS: `php artisan test tests/Feature/Slice06ReportGenerationTest.php`.
- Validasi PASS: `php artisan test tests/Feature/Slice05ReviewFinalizationTest.php`.
- Validasi PASS: `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php`.
- Validasi PASS: `php artisan route:list --path=admin`.
- Validasi PASS: `php artisan route:list --path=api/app`.
- Validasi PASS: `npm run build`.
- Validasi PASS: `php artisan migrate:fresh --seed -n`.
- Validasi PASS: `git diff --check`.

## 2026-06-20 — Slice 06 Follow-up Dana & Print User

- Menambahkan batas alokasi dana pegangan user agar total alokasi `fund_in` tidak melewati dana masuk event.
- Menambahkan informasi `Alokasi Dana` dan `Sisa Alokasi` pada tabel `Dana Masuk Event`.
- Menambahkan informasi alokasi pada payload keuangan User.
- Menambahkan route print preview LPJ untuk User yang ditugaskan pada event/kegiatan `finish`.
- Menambahkan tombol `Cetak LPJ` pada detail event selesai di PWA User.
- Menjaga PDF export Admin tetap ada, tetapi jalur ringan User memakai print preview HTML.
- Validasi PASS: `php artisan test tests/Feature/Slice03OperationalFinanceTest.php`.
- Validasi PASS: `php artisan test tests/Feature/Slice06ReportGenerationTest.php`.
- Validasi PASS: test login/profile/detail operasional User.
- Validasi PASS: test pelaksanaan/dokumentasi dan review/finalisasi.
- Validasi PASS: route app/API, build frontend, migrate fresh seed, dan diff check.

## 2026-06-20 — Slice 06 Follow-up Menu Dokumen LPJ

- Menambahkan model dan migration `LpjReportSnapshot`.
- Menambahkan menu Admin `Dokumen LPJ` untuk daftar snapshot LPJ yang sudah dihasilkan.
- Menyimpan snapshot HTML print-ready saat Admin membuka preview, Admin export PDF, atau User membuka cetak LPJ.
- Snapshot menyimpan versi, nomor snapshot, event, pembuat, sumber generate, total dana masuk, pengeluaran valid, sisa dana, dan waktu generate.
- Menambahkan route Admin untuk membuka ulang snapshot tersimpan.
- Validasi PASS: syntax, migrate fresh seed, route admin, build frontend, test Slice 06, test Slice 03, dan diff check.

## 2026-06-20 — Slice 06 Follow-up PWA User Dokumentasi

- Memisahkan tampilan menu `Operasional` dan `Dokumentasi` di PWA User.
- Menu `Operasional` sekarang fokus pada catatan petugas, data peserta, panitia/pendamping, dan rundown.
- Menu `Dokumentasi` sekarang menjadi halaman khusus upload dokumentasi, upload lampiran, dan daftar file tersimpan.
- Klik event dari `Beranda` sekarang membuka detail event read-only, bukan berpindah ke menu `Operasional`.
- Detail event dari `Beranda` hanya menampilkan data event dan tombol `Cetak LPJ` jika LPJ sudah tersedia.
- Validasi PASS: build frontend, test Slice 04 + Slice 02, route API, dan diff check.

## 2026-06-20 21:26 WIB — Slice 05 Review & Finalisasi Event

- Menambahkan `LpjReviewService` untuk submit review, checklist, review transaksi, revisi transaksi, rekonsiliasi sederhana, dan finalisasi event/kegiatan.
- Menambahkan action Admin pada tabel Event untuk melihat checklist finalisasi.
- Menambahkan action Admin pada tabel Transaksi Event untuk validasi, tolak, minta revisi, dan minta bukti.
- Menambahkan endpoint User `submit-review` untuk mengajukan event/kegiatan aktif ke review Admin.
- Menambahkan endpoint User revisi transaksi untuk transaksi miliknya yang berstatus `perlu_revisi` atau `menunggu_bukti`.
- Menambahkan payload PWA untuk kelengkapan, catatan Admin, alasan tanpa bukti, dan status revisi transaksi.
- Menambahkan tombol `Ajukan Review` pada detail event/kegiatan User.
- Menambahkan form revisi transaksi inline pada riwayat keuangan User.
- Menjaga `finish` sebagai lock final untuk input operasional, keuangan, pelaksanaan, dokumentasi, dan revisi transaksi.
- Tidak membuat PDF resmi/final pada Slice 05.
- Validasi PASS: `php artisan test tests/Feature/Slice05ReviewFinalizationTest.php`.
- Validasi PASS: `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php`.
- Validasi PASS: `php artisan test tests/Feature/Slice03OperationalFinanceTest.php`.
- Validasi PASS: `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php`.
- Validasi PASS: `php artisan route:list --path=api/app`.
- Validasi PASS: `php artisan route:list --path=admin`.
- Validasi PASS: `npm run build`.
- Validasi PASS: `php artisan test`.
- Validasi PASS: `php artisan migrate:fresh --seed -n`.

## 2026-06-20 21:55 WIB — Slice 05 Follow-up UX/Admin

- Mengganti menu bawah User PWA dari `Selesai` menjadi `Dokumentasi`.
- Mengubah dashboard User agar event aktif diprioritaskan dan tetap menampilkan event selesai dari Beranda.
- Menghaluskan font weight dan kartu event agar tidak terlalu penuh/kaku.
- Merapikan form Admin Event: select prompt jelas, sumber dana select `Lembaga/Sponsor/Dinas`, field audit/finalisasi tidak lagi tampil sebagai input manual.
- Membuat create/edit Event kembali ke list utama setelah simpan.
- Memadatkan list Event ke kolom penting.
- Menambahkan action `Aktif` untuk event draft dan `Selesai` untuk event aktif.
- Merapikan select Dana Masuk, Mutasi Saldo, dan Role User agar tidak menampilkan key placeholder system.
- Validasi fokus PASS: syntax file Filament yang dipatch.
- Validasi fokus PASS: `npm run build`.
- Validasi fokus PASS: `php artisan route:list --path=admin`.
- Validasi fokus PASS: `php artisan route:list --path=api/app`.
- Validasi fokus PASS: `php artisan test tests/Feature/Slice01MasterLpjRoleTest.php`.
- Validasi fokus PASS: `php artisan test tests/Feature/Slice05ReviewFinalizationTest.php`.
- Validasi fokus PASS: smoke form Filament via `php artisan tinker`.

## 2026-06-20 — Product Concept Rename

- Mengunci pemahaman baru: `Event/Kegiatan` adalah objek utama aplikasi, sedangkan `LPJ` adalah output akhir/dokumen hasil event/kegiatan.
- Menambahkan decision doc `docs/decisions/DECISION_2026-06-20_KICAP_EVENT_RENAME.md`.
- Memperbarui panduan aktif agar istilah lama yang menyebut LPJ sebagai objek kerja dibaca sebagai legacy wording.
- Menjaga alur kerja mesin tetap sama: Admin membuat event/kegiatan, User mengisi operasional, Admin review/finalisasi, sistem generate LPJ.
- Tidak melakukan rename schema/model/route/service internal.
- Mengubah label brand dan label UI aman menjadi `Kicap Event`, `Event Saya`, `Detail Event`, `Dana Kegiatan`, `Dana Masuk Event`, dan `Transaksi Event`.
- Validasi PASS: `php artisan route:list > /tmp/kicap_event_routes.txt`.
- Validasi PASS: `php artisan config:clear`.
- Validasi PASS: `php artisan view:clear`.
- Validasi PASS: `npm run build`.
- Validasi PASS: `php artisan test tests/Feature/HealthCheckTest.php`.
- Archive dibuat: `archives/2026-06-20_210040_kicap-event-concept-rename.zip`.

## 2026-06-19 20:23:44 +07 — Slice 00

- Membuat pondasi Laravel.
- Menambahkan Filament.
- Menambahkan React PWA baseline.
- Menambahkan MySQL via Docker dengan port unik.
- Menambahkan health check.
- Menambahkan seed auth dasar.
- Menambahkan README dan docs active.
- Memperbaiki error cache database sebelum migration.
- Memperbaiki timezone dari UTC menjadi Asia/Jakarta.
- Memperbaiki validasi timezone agar membaca JSON secara benar.
- Memperbarui phpunit.xml untuk testing environment Slice 00.
- Mengubah test dari Pest-style ke PHPUnit class-style.
- Membuat archive final setelah validasi otomatis.

## 2026-06-20 — Revisi Dokumen Pra-Implementasi

- Mengunci keputusan bahwa LPJ hanya dibuat oleh Admin.
- Mengunci User hanya input operasional pada LPJ yang ditugaskan.
- Menyederhanakan status LPJ menjadi `draft`, `aktif`, `finish`, `arsipkan`.
- Mengunci halaman user hanya menampilkan LPJ `aktif` dan `finish`.
- Mengunci login tunggal berbasis role dengan `Ingat saya`.
- Mengunci arah UI login berdasarkan referensi screenshot user.
- Mengunci user PWA memakai bottom navigation mengambang.
- Mengunci Admin panel responsive full-width.
- Mengunci tema visual cocok dengan logo Kicap, eye-catching, nyaman, dan memotivasi.
- Mengunci archive slice berada di `docs/archive/`.
- Mereset Slice 01 untuk dikerjakan ulang dari awal.

## 2026-06-20 09:59:52 +07 — Slice 01 Revisi Implementasi

- Mengimplementasikan login tunggal Admin/User dengan field username/email dan password.
- Menambahkan response login berbasis role untuk redirect Admin ke `/admin` dan User ke `/app`.
- Mengunci akses `/app` untuk user terautentikasi dan mengarahkan Admin kembali ke panel Admin.
- Menambahkan endpoint `/api/app/lpjs` untuk LPJ assigned berstatus `aktif` dan `finish`.
- Mengganti kontrak status LPJ menjadi `draft`, `aktif`, `finish`, `arsipkan`.
- Mengatur seed User/Pendamping agar tidak dapat membuat LPJ.
- Memperbaiki form/tabel Filament untuk LPJ, assignment user, role Admin/User, dan admin full-width.
- Mengganti baseline React PWA menjadi tampilan mobile/tablet dengan bottom navigation mengambang.
- Menambahkan coverage test Slice 01 untuk role, remember-me, redirect, status, dan filter LPJ user.
- Validasi otomatis PASS: migrate fresh seed, test suite, route admin, dan build frontend.

## 2026-06-20 — Slice 01 Revisi Follow-up Logout User

- Menambahkan tombol `Keluar` di header PWA User.
- Menambahkan route POST `/app/logout` yang logout, invalidate session, regenerate CSRF token, lalu redirect ke `/login`.
- Menambahkan meta CSRF token untuk request logout dari React.
- Validasi PASS: `php artisan test tests/Feature/Slice01UsernameLoginFormTest.php`.
- Validasi PASS: `npm.cmd run build`.

## 2026-06-20 — Slice 01 Revisi Follow-up Full App Looks + Profil User

- Mengganti bottom navigation PWA dari label huruf menjadi ikon aplikasi via `lucide-react`.
- Merapikan ukuran font, density, card LPJ, summary tile, dan tab bar agar terasa seperti mobile app.
- Menambahkan layar Profil pada tab PWA User.
- Profil User dapat melihat nama, email, username, avatar, dan WhatsApp.
- Profil User dapat update nama, WhatsApp, foto/avatar, dan password.
- Menambahkan endpoint `GET /api/app/profile` dan `POST /api/app/profile`.
- Validasi PASS: `php artisan test tests/Feature/Slice01UsernameLoginFormTest.php`.
- Validasi PASS: `php artisan test`.
- Validasi PASS: `npm.cmd run build`.

## 2026-06-20 15:02 +07 — Slice 02 LPJ Detail & Input Mobile

- Menambahkan tabel `narrative_templates` dan `lpj_narratives`.
- Menambahkan tabel `activity_notes`.
- Menambahkan model `NarrativeTemplate` dan `LpjNarrative`.
- Menambahkan model `ActivityNote`.
- Menambahkan `ActivityNoteService` untuk catatan operasional User.
- Menyiapkan `LpjNarrativeService` untuk fondasi narasi Admin berikutnya.
- Menambahkan seed template narasi untuk lima tipe LPJ awal.
- Menambahkan endpoint detail event User dan simpan catatan operasional.
- Mengunci akses detail hanya untuk User yang ditugaskan.
- Mengunci input operasional hanya untuk LPJ `aktif`.
- Mengunci LPJ `finish` sebagai read-only di PWA User.
- Menghapus editor narasi dari PWA User karena narasi adalah urusan Admin.
- Menambahkan detail event dan autosave catatan operasional di React PWA.
- Mengubah bottom navigation petugas menjadi 5 item dengan `Input Cepat` sebagai tombol tengah.
- Mengganti label UX yang terlihat user ke bahasa Indonesia, termasuk `Input Cepat` dan `Selesai`.
- Validasi PASS: `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php`.
- Validasi PASS: `php artisan test`.
- Validasi PASS: `php artisan migrate:fresh --seed`.
- Validasi PASS: `cmd.exe /c "cd /d D:\kulino\lpj-kicap && npm.cmd run build"`.

## 2026-06-20 18:53 +07 — Slice 03 Dana Kegiatan

- Menambahkan tabel dana masuk LPJ, saldo user per LPJ, mutasi saldo, transaksi operasional, dan klaim dana talangan.
- Menambahkan model `LpjFundReceipt`, `LpjUserBalance`, `LpjBalanceMutation`, `LpjFinancialTransaction`, dan `LpjAdvanceClaim`.
- Menambahkan `LpjFinanceService` untuk dana masuk, dana pegangan, pengeluaran, transfer saldo, dan dana talangan.
- Menambahkan endpoint PWA untuk pengeluaran, transfer saldo, dan dana talangan.
- Menambahkan payload finance pada detail event User.
- Menambahkan panel Dana Kegiatan pada detail event PWA.
- Menambahkan resource Filament untuk `Dana Masuk Event`, `Mutasi Saldo`, `Saldo User`, `Transaksi Operasional`, dan `Klaim Talangan`.
- Menambahkan seed saldo demo dan penugasan user kedua untuk simulasi transfer.
- Menambahkan coverage test `Slice03OperationalFinanceTest`.
- Validasi PASS: `php artisan test tests/Feature/Slice03OperationalFinanceTest.php`.
- Validasi PASS: `php artisan route:list --path=api/app`.
- Validasi PASS: `php artisan route:list --path=admin`.
- Validasi PASS: `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php`.
- Validasi PASS: `php artisan test`.
- Validasi PASS: `php artisan migrate:fresh --seed -n`.
- Validasi PASS: `cmd.exe /c "cd /d D:\kulino\lpj-kicap && npm.cmd run build"`.

## 2026-06-20 — Slice 03 Follow-up Review Manual

- Mengubah sumber dana pada `Dana Masuk Event` menjadi pilihan `Lembaga`, `Sponsor`, dan `Dinas`.
- Mengubah kategori transaksi User pada PWA menjadi pilihan tetap: `Konsumsi`, `Akomodasi`, `Operasional`, `Transportasi`, `Dokumentasi`, dan `Lainnya`.
- Memisahkan menu bawah PWA menjadi `Operasional` untuk catatan petugas dan `Keuangan` untuk transaksi/saldo.
- Mengganti label kolom mutasi menjadi `User Terkait Transfer` dan menampilkan `-` untuk mutasi non-transfer.

## 2026-06-20 20:42 +07 — Slice 04 Pelaksanaan & Dokumentasi

- Menambahkan tabel peserta kegiatan, panitia/pendamping, rundown, dokumentasi kegiatan, dan lampiran pendukung.
- Menambahkan model `ActivityParticipant`, `ActivityCommittee`, `ActivitySchedule`, `ActivityDocumentation`, dan `ActivityAttachment`.
- Menambahkan `ActivityExecutionService` untuk menyimpan data pelaksanaan serta upload dokumentasi/lampiran.
- Menambahkan endpoint PWA `execution-data`, `documentations`, dan `attachments`.
- Menambahkan payload pelaksanaan/dokumentasi pada detail event User.
- Menambahkan input peserta, panitia/pendamping, rundown, upload dokumentasi, upload lampiran, dan daftar file tersimpan di tab `Operasional`.
- Menambahkan tipe catatan `Evaluasi` dan opsi `include_in_report` untuk memilih catatan masuk LPJ.
- Mengunci LPJ `finish` tetap read-only untuk input pelaksanaan dan upload.
- Menambahkan coverage test `Slice04ExecutionDocumentationTest`.
- Validasi PASS: `php artisan migrate --force`.
- Validasi PASS: `php artisan test tests/Feature/Slice04ExecutionDocumentationTest.php`.
- Validasi PASS: `php artisan route:list --path=api/app`.
- Validasi PASS: `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php`.
- Validasi PASS: `php artisan test tests/Feature/Slice03OperationalFinanceTest.php`.
- Validasi PASS: `php artisan test`.
- Validasi PASS: `php artisan migrate:fresh --seed -n`.
- Validasi PASS: `npm run build`.
