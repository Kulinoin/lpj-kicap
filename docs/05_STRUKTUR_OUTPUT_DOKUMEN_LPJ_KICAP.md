# Struktur Output Dokumen LPJ Kicap v1.0

**Status:** Draft layout dokumen  
**Prinsip:** Output LPJ global, formal, dan mudah dibaca. Detail internal tetap tersimpan di sistem.

---

## 0. Keputusan Revisi 20 Juni 2026

1. Dokumen resmi/PDF hanya dibuat dari LPJ status `finish`.
2. Istilah "LPJ final" pada dokumen ini berarti output resmi dari LPJ yang sudah `finish`.
3. LPJ `draft` dan `aktif` hanya boleh memiliki preview kerja, bukan PDF resmi.
4. LPJ `arsipkan` dapat diexport ulang oleh Admin jika diperlukan.

---

## 1. Output yang Didukung MVP

Output utama:

```text
PDF
Print-ready view
```

Word:

```text
Tidak wajib untuk MVP.
Boleh masuk fitur tambahan jika tidak terlalu berat.
```

---

## 2. Prinsip Layout

1. Cover formal bersih.
2. Halaman pengesahan memakai kop lengkap.
3. Halaman isi berikutnya cukup footer dan nomor halaman.
4. Tidak menampilkan detail internal default.
5. Dokumen harus tetap nyaman dibaca.
6. Dokumen harus cocok untuk kegiatan sendiri maupun pendampingan pihak luar.
7. Untuk pendampingan, gunakan kalimat aman agar tidak mengklaim sebagai penyelenggara utama.

---

## 3. Data Identitas Dokumen

Data identitas:

- Logo lembaga.
- Jenis lembaga.
- Nama lembaga.
- Nama unit/divisi opsional.
- Alamat lengkap.
- Telepon.
- HP.
- Email.
- Website.
- Footer dokumen.
- Nomor halaman.

Default awal:

```text
Nama lembaga: PT. Kazoku Indonesia Center
Jenis lembaga: Lembaga Pelatihan Kerja
Alamat: [Diisi kemudian]
Telepon: [Diisi kemudian]
HP: [Diisi kemudian]
Email: [Diisi kemudian]
Website: [Diisi kemudian]
Logo: [Placeholder]
```

---

## 4. Struktur Output LPJ

Struktur dokumen final:

1. Cover.
2. Halaman Pengesahan.
3. Kata Pengantar opsional.
4. Daftar Isi opsional.
5. Identitas Kegiatan.
6. Latar Belakang.
7. Maksud dan Tujuan.
8. Penyelenggara/Peran Organisasi.
9. Data Peserta.
10. Data Panitia/Pendamping.
11. Rundown/Pelaksanaan Kegiatan.
12. Hasil Kegiatan.
13. Evaluasi, Kendala, dan Saran.
14. Laporan Keuangan Global.
15. Rincian Transaksi Valid.
16. Dokumentasi Kegiatan.
17. Lampiran.
18. Penutup.

---

## 5. Cover

Cover tidak memakai kop surat penuh.

Susunan cover:

```text
LAPORAN PERTANGGUNGJAWABAN

[NAMA / JUDUL KEGIATAN]

[Logo lembaga / placeholder]

Lokasi Kegiatan:
[Lokasi]

Tanggal:
[Tanggal / Periode]

[Nama lembaga]
[Alamat]
[Kontak / Email / Website opsional]
[Tahun]
```

Catatan untuk tipe pendampingan:

- Gunakan `Lokasi Kegiatan`, bukan `Diselenggarakan di`.
- Gunakan `Pendampingan Peserta`, bukan klaim sebagai penyelenggara.
- Cantumkan penyelenggara eksternal pada bagian isi dokumen.

---

## 6. Halaman Pengesahan

Halaman pengesahan memakai kop lengkap.

Elemen:

- Logo.
- Jenis lembaga.
- Nama lembaga.
- Alamat.
- Kontak.
- Email.
- Garis kop surat.
- Judul halaman.
- Identitas LPJ.
- Dibuat oleh.
- Diperiksa oleh.
- Disetujui oleh.
- Kota dan tanggal pengesahan.
- Area tanda tangan.
- Stempel opsional.

Contoh:

```text
HALAMAN PENGESAHAN

Nama Kegiatan : [Nama kegiatan]
Tipe LPJ      : [Tipe LPJ]
Periode       : [Periode]
Lokasi        : [Lokasi]
Dibuat oleh   : [Nama]
Diperiksa oleh: [Nama]
Disetujui oleh: [Nama]
```

---

## 7. Halaman Isi

Halaman isi tidak memakai kop penuh.

Elemen halaman isi:

- Judul bagian.
- Isi laporan.
- Footer ringkas.
- Nomor halaman.

Footer contoh:

```text
Kicap LPJ - PT. Kazoku Indonesia Center | Halaman X
```

---

## 8. Identitas Kegiatan

Field yang ditampilkan:

| Field | Keterangan |
|---|---|
| Nama kegiatan | Judul kegiatan |
| Tipe LPJ | Tipe kegiatan |
| Tanggal | Tanggal mulai/selesai |
| Lokasi | Lokasi kegiatan |
| Penanggung jawab | Nama PJ |
| Sumber dana | Sumber dana global |
| Nomor surat/tugas | Jika ada |
| Periode LPJ | Periode laporan |

---

## 9. Latar Belakang, Maksud, dan Tujuan

Bagian ini dibuat dari template otomatis dan dapat diedit.

Untuk pendampingan, narasi harus menyebut organisasi sebagai pendamping, bukan penyelenggara utama jika kegiatan milik pihak luar.

---

## 10. Penyelenggara / Peran Organisasi

Untuk event sendiri:

- Penyelenggara.
- Ketua panitia.
- Sekretaris.
- Bendahara.
- Seksi.

Untuk pendampingan:

- Penyelenggara eksternal.
- Peran organisasi.
- Koordinator pendamping.
- Jumlah pendamping.
- Tugas pendamping.
- Catatan organisasi bukan penyelenggara utama.

---

## 11. Data Peserta

Tabel peserta:

| No | Nama Peserta | Asal/Kelas/Divisi | Nomor Peserta | Kehadiran | Hasil | Keterangan |
|---|---|---|---|---|---|---|

---

## 12. Data Panitia / Pendamping

Tabel:

| No | Nama | Jabatan/Peran | Tugas | Kontak Opsional |
|---|---|---|---|---|

---

## 13. Rundown / Pelaksanaan

Tabel rundown:

| No | Waktu | Kegiatan | Penanggung Jawab | Keterangan |
|---|---|---|---|---|

Narasi pelaksanaan:

- Kegiatan berjalan bagaimana.
- Apakah sesuai rencana.
- Kendala selama kegiatan.
- Jumlah peserta hadir.
- Aktivitas utama.
- Hasil sementara/akhir.

---

## 14. Hasil Kegiatan

Untuk event:

- Jumlah peserta hadir.
- Target tercapai/tidak.
- Output kegiatan.
- Catatan hasil.

Untuk pendampingan:

- Jumlah peserta didampingi.
- Peserta hadir.
- Hasil seleksi.
- Status hasil final/sementara/menunggu.
- Catatan hasil.

---

## 15. Evaluasi, Kendala, dan Saran

Bagian ini dapat diambil dari catatan kegiatan yang dipilih user/Admin.

Isi:

- Kendala.
- Solusi.
- Evaluasi.
- Saran.

---

## 16. Laporan Keuangan Global

Ditampilkan:

- Total dana diterima.
- Total pengeluaran.
- Total sisa dana.
- Rincian per kategori.

Tidak ditampilkan default:

- Saldo per user.
- Transfer saldo antar user.
- Klaim dana talangan.
- Reimbursement klaim.
- Audit perubahan.

---

## 17. Rincian Transaksi Valid

Output LPJ hanya mengambil transaksi berstatus Valid.

Kolom:

| No | Tanggal | Kategori | Uraian | Metode | Nominal | Keterangan |
|---|---|---|---|---|---:|---|

Catatan:

- Dana talangan yang valid tampil sebagai pengeluaran.
- Klaim/reimbursement tidak tampil sebagai pengeluaran baru.
- Transfer saldo tidak tampil.

---

## 18. Dokumentasi Kegiatan

Layout dokumentasi:

- Foto.
- Caption.
- Tanggal opsional.
- Kategori dokumentasi.

Dokumentasi bisa disusun 2 foto per halaman atau sesuai proporsi.

---

## 19. Lampiran LPJ

Lampiran resmi:

- Bukti transaksi valid.
- Dokumentasi kegiatan.
- Surat undangan.
- Surat tugas.
- Daftar hadir.
- Sertifikat.
- Pengumuman hasil.
- Dokumen pendukung resmi.

Lampiran internal tidak tampil default:

- Rekap saldo user.
- Riwayat transfer saldo.
- Klaim dana talangan.
- Audit perubahan.
- Transaksi ditolak.
- Riwayat revisi.

---

## 20. Penutup

Penutup berisi ringkasan bahwa LPJ disusun sebagai bentuk pertanggungjawaban kegiatan dan penggunaan dana.
