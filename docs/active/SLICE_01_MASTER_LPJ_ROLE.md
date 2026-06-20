# Slice 01 — Master LPJ, Role, Login, dan UI Baseline

## Status

Reset dan akan dikerjakan ulang dari awal mengikuti keputusan revisi 20 Juni 2026.

Implementasi Slice 01 lama dianggap tidak menjadi acuan final karena ada perubahan produk:

- LPJ hanya dibuat oleh Admin.
- User tidak membuat LPJ.
- Status LPJ disederhanakan menjadi `draft`, `aktif`, `finish`, `arsipkan`.
- User hanya melihat LPJ `aktif` dan `finish` yang ditugaskan.
- Login menjadi satu halaman untuk Admin dan User.
- `Ingat saya` wajib berfungsi.
- User PWA memakai bottom navigation mengambang.
- Admin panel dibuat responsive full-width.

## Target Baru

- Role MVP Admin/User.
- Login tunggal branded Kicap LPJ.
- Login menerima username/email dan password.
- Checkbox `Ingat saya` berjalan secara fungsional.
- Redirect setelah login:

```text
Admin -> /admin
User  -> /app
```

- Profil lembaga awal.
- Tipe LPJ awal.
- Struktur LPJ awal dengan status:

```text
draft
aktif
finish
arsipkan
```

- Admin dapat membuat dan mengelola LPJ.
- Admin dapat menugaskan User ke LPJ.
- User tidak dapat membuat LPJ.
- User hanya melihat LPJ `aktif` dan `finish` yang ditugaskan.
- Seed Admin/User/Pendamping.
- Endpoint awal untuk kebutuhan PWA.
- Admin layout full-width.
- User PWA baseline responsive HP/tablet dengan bottom navigation mengambang.

## Arah UI Login

Struktur mengikuti referensi screenshot user:

- Background lembut.
- Kartu login putih di tengah.
- Logo Kicap di atas.
- Judul `Kicap LPJ`.
- Field `Username / Email`.
- Field `Password`.
- Checkbox `Ingat saya`.
- Tombol login utama.
- Footer versi dan copyright.

Warna tidak wajib mengikuti hijau pada screenshot. Tema dipilih agar cocok dengan logo Kicap, eye-catching, nyaman, dan memotivasi.

Arah palet awal:

```text
Brand accent: merah/coral selaras logo
Action/positive: teal segar
Highlight: amber hangat
Surface: putih dan netral terang
Text: slate/abu gelap
```

## Validasi Otomatis Rencana

- `php artisan migrate:fresh --seed`
- Test login tunggal username/email.
- Test remember-me/remember token tersedia.
- Test Admin redirect ke `/admin`.
- Test User redirect ke `/app`.
- Test User ditolak dari `/admin`.
- Test status LPJ hanya `draft`, `aktif`, `finish`, `arsipkan`.
- Test User tidak bisa membuat LPJ.
- Test User hanya menerima LPJ aktif/finish yang ditugaskan.
- `npm run build`

## Validasi Manual Rencana

- Buka halaman login tunggal.
- Cek tampilan login sesuai arah visual.
- Login Admin.
- Login User dengan `Ingat saya`.
- Cek User masuk ke PWA.
- Cek bottom navigation user di HP/tablet.
- Cek Admin panel tidak menyisakan ruang kosong lebar kiri-kanan.
- Cek LPJ draft tidak tampil di user.
- Cek LPJ aktif dan finish tampil di user jika ditugaskan.

## Di Luar Scope Slice 01

- Wizard narasi LPJ lengkap.
- Transaksi, saldo, transfer saldo, dan dana talangan.
- Review transaksi.
- Rekonsiliasi.
- Generate PDF.
- Offline sync kompleks.
