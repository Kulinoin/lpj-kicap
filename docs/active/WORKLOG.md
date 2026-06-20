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
