# Slice 02 — LPJ Detail & Input Mobile

## Status

Implementasi selesai dan validasi otomatis PASS pada 20 Juni 2026.

Slice ini melanjutkan baseline Slice 01 dan koreksi produk pada 20 Juni 2026:

- LPJ tetap hanya dibuat oleh Admin.
- User hanya membuka LPJ `aktif` dan `finish` yang ditugaskan.
- User hanya dapat mengisi data operasional untuk LPJ berstatus `aktif`.
- LPJ `finish` tampil read-only untuk User.
- Nilai status backend tetap `draft`, `aktif`, `finish`, `arsipkan`; label UX menggunakan bahasa Indonesia.
- Narasi LPJ adalah urusan Admin, bukan input User/Petugas.

## Scope

- Halaman detail LPJ di PWA User.
- Endpoint detail LPJ untuk User yang ditugaskan.
- Input catatan operasional petugas:
  - Hasil di lapangan.
  - Kendala.
  - Saran tindak lanjut.
- Autosave ringan untuk catatan operasional.
- Bottom navigation petugas menjadi 5 item: `Beranda`, `LPJ`, `Input Cepat`, `Selesai`, `Profil`.
- Tombol tengah `Input Cepat` menjadi fungsi utama yang sering dipakai petugas.
- Label UX PWA yang masih terlihat Inggris diganti ke bahasa Indonesia, misalnya `Finish` menjadi `Selesai`.

## Out of Scope

- Transaksi keuangan.
- Saldo pegangan.
- Transfer saldo.
- Dana talangan.
- Upload dokumentasi/lampiran.
- Peserta, panitia, rundown, dan dokumentasi lengkap.
- Edit narasi LPJ oleh User.
- Review/finalisasi Admin mendalam.
- Generate PDF.

## Implementasi

- Menambahkan tabel `narrative_templates`.
- Menambahkan tabel `lpj_narratives`.
- Menambahkan tabel `activity_notes`.
- Menambahkan model `NarrativeTemplate` dan `LpjNarrative`.
- Menambahkan model `ActivityNote`.
- Menambahkan `ActivityNoteService` untuk catatan operasional User.
- Menyiapkan `LpjNarrativeService` sebagai fondasi narasi Admin berikutnya.
- Menambahkan `NarrativeTemplateSeeder` untuk lima tipe LPJ awal.
- Menambahkan endpoint:

```text
GET  /api/app/lpjs/{lpj}
POST /api/app/lpjs/{lpj}/activity-notes
```

- Menambahkan layar detail LPJ di React PWA.
- Menambahkan autosave catatan operasional dari PWA.
- Menghapus akses edit narasi dari PWA User.
- Mengubah bottom navigation petugas menjadi 5 item dengan `Input Cepat` sebagai tombol tengah.
- Menambahkan test feature `Slice02LpjDetailMobileInputTest`.

## Validasi Otomatis PASS

- `php artisan migrate --force`
- `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php`
- `php artisan test`
- `php artisan migrate:fresh --seed`
- `php artisan route:list --path=api/app`
- `cmd.exe /c "cd /d D:\kulino\lpj-kicap && npm.cmd run build"`

## Validasi Manual Rencana

- Login sebagai User.
- Buka `/app` dari viewport HP.
- Pastikan bottom navigation memiliki 5 item.
- Pastikan tab tengah bertuliskan `Input Cepat`.
- Buka LPJ aktif yang ditugaskan.
- Pastikan detail LPJ tampil.
- Pastikan form `Input Operasional` tampil, bukan form narasi.
- Isi `Hasil di Lapangan`, `Kendala`, atau `Saran Tindak Lanjut`.
- Tunggu autosave dan refresh halaman.
- Pastikan catatan operasional tetap tersimpan.
- Buka LPJ `finish` yang ditugaskan.
- Pastikan LPJ `finish` tampil read-only dan label status terlihat `Selesai`.
