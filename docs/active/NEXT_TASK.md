# Next Task

## Slice 01 Revisi — Master LPJ, Role, Login, dan UI Baseline

Implementasi ulang Slice 01 berdasarkan dokumen revisi 20 Juni 2026 sudah selesai secara otomatis dan menunggu review manual user sebelum commit/push.

## Scope

- Login tunggal Kicap LPJ untuk Admin dan User.
- Login dengan username/email dan password.
- Checkbox `Ingat saya` berfungsi.
- Redirect role:

```text
Admin -> /admin
User  -> /app
```

- Role MVP Admin/User.
- Profil lembaga.
- Tipe LPJ awal.
- Status LPJ: `draft`, `aktif`, `finish`, `arsipkan`.
- Admin membuat LPJ.
- User tidak membuat LPJ.
- User hanya melihat LPJ `aktif` dan `finish` yang ditugaskan.
- Admin panel responsive full-width.
- User PWA responsive HP/tablet dengan bottom navigation mengambang.
- Tema visual cocok dengan logo Kicap, nyaman, eye-catching, dan memotivasi.

## Out of Scope

- Transaksi.
- Saldo pegangan.
- Transfer saldo.
- Dana talangan.
- Review/finalisasi mendalam.
- Generate PDF.
- Offline sync kompleks.

## Archive

Archive hasil validasi slice dibuat di:

```text
docs/archive/slice-01-revisi-master-lpj-role-login-ui-baseline/2026-06-20-0959/
```

## Next

- Review manual halaman login tunggal.
- Review manual `/admin` sebagai Admin.
- Review manual `/app` sebagai User di HP/tablet.
- Setelah user setuju, stage file eksplisit, commit, lalu tunggu persetujuan push.
