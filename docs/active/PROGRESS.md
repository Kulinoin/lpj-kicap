# Progress Kicap LPJ

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
- Master LPJ.
- Data model LPJ.

### Next Slice

Slice 01 — Master LPJ & Role.

## Slice 01 — Master LPJ & Role

- Menambahkan role MVP Admin/User.
- Menambahkan izin dasar user: aktif, boleh membuat LPJ, boleh transfer saldo.
- Menambahkan profil lembaga awal.
- Menambahkan master tipe LPJ awal.
- Menambahkan struktur awal LPJ/kegiatan.
- Menambahkan penugasan user ke LPJ.
- Menambahkan seed user, profil lembaga, tipe LPJ, dan LPJ demo.
- Menambahkan endpoint awal `/api/master/lpj-types`.
- Menambahkan test Slice 01.
- Resource Filament dibuat lewat generator berdasarkan versi package terpasang.

### Patch Slice 01 — Username/Email Login & Profile

- Menambahkan login menggunakan username/email lewat custom Laravel Auth provider.
- Menambahkan username pada user.
- Menambahkan WhatsApp user.
- Menambahkan foto profile user.
- Mengunci username dan email pada halaman profile.
- Nama lengkap memakai field `name`.
- Menambahkan halaman Profil Saya di Filament.
- Menambahkan test patch login/profile.

### Patch Login Form Username

- Mengganti form login Filament agar menerima username/email.
- Menghapus constraint email-only pada input login.
- Menambahkan test custom login form dan provider username/email.

### Patch Profile Dropdown Modal

- Memindahkan profile dari sidebar ke user dropdown kanan atas.
- Menambahkan modal profile dari user menu.
- Menyembunyikan halaman MyProfile dari navigation/sidebar.
- Username dan email tetap terkunci.
