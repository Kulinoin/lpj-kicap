# Worklog Kicap LPJ

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
- Menambahkan endpoint detail LPJ User dan simpan catatan operasional.
- Mengunci akses detail hanya untuk User yang ditugaskan.
- Mengunci input operasional hanya untuk LPJ `aktif`.
- Mengunci LPJ `finish` sebagai read-only di PWA User.
- Menghapus editor narasi dari PWA User karena narasi adalah urusan Admin.
- Menambahkan detail LPJ dan autosave catatan operasional di React PWA.
- Mengubah bottom navigation petugas menjadi 5 item dengan `Input Cepat` sebagai tombol tengah.
- Mengganti label UX yang terlihat user ke bahasa Indonesia, termasuk `Input Cepat` dan `Selesai`.
- Validasi PASS: `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php`.
- Validasi PASS: `php artisan test`.
- Validasi PASS: `php artisan migrate:fresh --seed`.
- Validasi PASS: `cmd.exe /c "cd /d D:\kulino\lpj-kicap && npm.cmd run build"`.

## 2026-06-20 18:53 +07 — Slice 03 Operasional Keuangan

- Menambahkan tabel dana masuk LPJ, saldo user per LPJ, mutasi saldo, transaksi operasional, dan klaim dana talangan.
- Menambahkan model `LpjFundReceipt`, `LpjUserBalance`, `LpjBalanceMutation`, `LpjFinancialTransaction`, dan `LpjAdvanceClaim`.
- Menambahkan `LpjFinanceService` untuk dana masuk, dana pegangan, pengeluaran, transfer saldo, dan dana talangan.
- Menambahkan endpoint PWA untuk pengeluaran, transfer saldo, dan dana talangan.
- Menambahkan payload finance pada detail LPJ User.
- Menambahkan panel Operasional Keuangan pada detail LPJ PWA.
- Menambahkan resource Filament untuk `Dana Masuk LPJ`, `Mutasi Saldo`, `Saldo User`, `Transaksi Operasional`, dan `Klaim Talangan`.
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

- Mengubah sumber dana pada `Dana Masuk LPJ` menjadi pilihan `Lembaga`, `Sponsor`, dan `Dinas`.
- Mengubah kategori transaksi User pada PWA menjadi pilihan tetap: `Konsumsi`, `Akomodasi`, `Operasional`, `Transportasi`, `Dokumentasi`, dan `Lainnya`.
- Memisahkan menu bawah PWA menjadi `Operasional` untuk catatan petugas dan `Keuangan` untuk transaksi/saldo.
- Mengganti label kolom mutasi menjadi `User Terkait Transfer` dan menampilkan `-` untuk mutasi non-transfer.
