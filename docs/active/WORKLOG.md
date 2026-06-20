# Worklog Kicap Event

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
