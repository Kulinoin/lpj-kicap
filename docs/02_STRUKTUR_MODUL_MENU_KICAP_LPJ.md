# Struktur Modul & Menu Kicap Event v1.0

**Status:** Draft MVP
**Role:** Admin dan User
**Catatan utama:** Transfer saldo antar user tidak perlu approval Admin; Admin tetap dapat melihat riwayat transfer.

---

## 0.0 Decision — Kicap Event sebagai Konsep Utama

Keputusan 20 Juni 2026 mengunci vocabulary berikut:

```text
Event / Kegiatan = objek utama
LPJ = output dokumen akhir
```

Menu dan label UI memakai Event/Kegiatan untuk objek utama. LPJ hanya dipakai untuk preview/export/template/dokumen final. Nama file dan internal code legacy boleh tetap memakai `lpj` sampai ada slice teknis rename yang aman.

---

## 0. Keputusan Revisi 20 Juni 2026

Jika ada konflik dengan struktur lama, gunakan keputusan berikut:

1. Event/Kegiatan hanya dibuat oleh Admin.
2. User tidak memiliki menu `Buat Event` / `Buat Kegiatan`.
3. Status event/kegiatan MVP: `draft`, `aktif`, `finish`, `arsipkan`.
4. Halaman user hanya menampilkan event/kegiatan `aktif` dan `finish`.
5. Login hanya satu halaman untuk semua role.
6. User PWA memakai bottom navigation mengambang, responsive untuk HP dan tablet.
7. Admin panel dibuat responsive full-width agar tabel/resource tidak terasa sempit.
8. Tema visual boleh dipilih bebas selama cocok dengan logo Kicap, nyaman, dan memotivasi.

---

## 1. Prinsip Struktur Menu

1. Admin menggunakan Filament/backoffice.
2. User lapangan menggunakan React PWA mobile-first.
3. Menu dibuat sederhana dan tidak terlalu banyak.
4. Status, filter, dan action tidak dijadikan menu utama.
5. Detail internal tetap tersedia untuk Admin.
6. User hanya melihat data yang relevan dengan tugasnya.

---

## 2. Modul Admin

### 2.1 Dashboard

Fungsi:

- Ringkasan event aktif.
- Event menunggu review.
- Transaksi perlu review.
- Klaim dana talangan menunggu verifikasi.
- Total dana kegiatan.
- Ringkasan saldo user.
- Transfer saldo terbaru.
- Checklist event siap finalisasi.

Menu:

```text
Dashboard
```

---

### 2.2 Profil Lembaga

Fungsi:

- Kelola nama lembaga.
- Jenis lembaga.
- Alamat.
- Telepon/HP.
- Email.
- Website.
- Logo.
- Footer dokumen.
- Data pengesahan.
- Nama dan jabatan penandatangan.

Menu:

```text
Profil Lembaga
```

---

### 2.3 User Management

Fungsi:

- Kelola Admin.
- Kelola User.
- Status aktif/nonaktif.
- Izin user membuat event/kegiatan.
- Izin user transfer saldo.
- Penugasan user ke event/kegiatan.

Menu:

```text
User Management
```

---

### 2.4 Master Event

Fungsi:

- Tipe Event.
- Template narasi.
- Template dokumen.
- Kategori transaksi.
- Metode pembayaran.
- Status default.
- Checklist kelengkapan.

Menu group:

```text
Master Event
  - Tipe Event
  - Template Narasi
  - Template Dokumen
  - Kategori Transaksi
  - Metode Pembayaran
  - Checklist Kelengkapan
```

---

### 2.5 Data Event

Fungsi:

- Melihat semua event/kegiatan.
- Membuat event/kegiatan.
- Review data kegiatan.
- Review transaksi.
- Review dokumentasi.
- Finalisasi event/kegiatan.
- Export dokumen.

Menu group:

```text
Data Event
  - Semua Event
  - Event Draft
  - Event Aktif
  - Event Finish
  - Arsip Event
```

Catatan:

- Untuk implementasi UI, status boleh berupa filter di halaman Semua Event.
- Jika sidebar terlalu ramai, cukup gunakan `Semua Event` dan `Review Event`.

---

### 2.6 Dana Kegiatan

Fungsi:

- Catat dana masuk kegiatan.
- Distribusi dana pegangan ke user.
- Rekap dana global.
- Sisa dana kegiatan.

Menu group:

```text
Dana Kegiatan
  - Dana Masuk
  - Dana Pegangan User
  - Rekap Dana
```

---

### 2.7 Saldo Pegangan User

Fungsi:

- Lihat saldo semua user.
- Lihat mutasi saldo.
- Tambah dana pegangan user.
- Lihat transfer saldo.
- Koreksi/adjustment saldo jika diperlukan.

Menu group:

```text
Saldo Pegangan User
  - Ringkasan Saldo
  - Tambah Dana Pegangan
  - Mutasi Saldo
  - Riwayat Transfer Saldo
  - Koreksi Saldo
```

Catatan penting:

```text
Transfer saldo antar user tidak perlu approval Admin.
Admin hanya melihat riwayat dan dapat melakukan koreksi jika ada kesalahan.
```

---

### 2.8 Transaksi

Fungsi:

- Review transaksi global.
- Validasi transaksi.
- Menolak transaksi.
- Melihat transaksi tanpa bukti.
- Melihat split transaksi.
- Koreksi transaksi.
- Audit perubahan.

Menu group:

```text
Transaksi
  - Semua Transaksi
  - Perlu Review
  - Menunggu Bukti
  - Transaksi Valid
  - Transaksi Ditolak
  - Audit Transaksi
```

---

### 2.9 Klaim Dana Talangan

Fungsi:

- Melihat klaim dana talangan.
- Verifikasi klaim.
- Tolak klaim.
- Tandai klaim sebagai dibayar.
- Melihat riwayat pembayaran klaim.

Menu group:

```text
Klaim Dana Talangan
  - Semua Klaim
  - Perlu Verifikasi
  - Disetujui/Diverifikasi
  - Ditolak
  - Dibayar
```

---

### 2.10 Dokumentasi & Lampiran

Fungsi:

- Melihat dokumentasi kegiatan.
- Melihat lampiran resmi.
- Melihat lampiran internal.
- Mengatur apakah lampiran tampil di LPJ final.

Menu group:

```text
Dokumentasi & Lampiran
  - Dokumentasi Kegiatan
  - Lampiran LPJ
  - Lampiran Internal
```

---

### 2.11 Review & Finalisasi

Fungsi:

- Checklist kelengkapan.
- Review data LPJ.
- Review transaksi.
- Review dokumentasi.
- Rekonsiliasi saldo.
- Minta revisi.
- Setujui LPJ.
- Finalisasi.
- Lock final.

Menu group:

```text
Review & Finalisasi
  - Checklist Event
  - Review Event
  - Rekonsiliasi
  - Finalisasi
```

---

### 2.12 Export Dokumen

Fungsi:

- Preview print-ready.
- Export PDF.
- Export internal audit jika diperlukan.

Menu:

```text
Export Dokumen
```

---

## 3. Modul User / PWA

### 3.1 Dashboard Saya

Isi:

- Event aktif.
- Saldo pegangan saya.
- Klaim dana talangan saya.
- Transaksi perlu dilengkapi.
- Catatan kegiatan terakhir.
- Shortcut catat cepat.

Menu:

```text
Dashboard Saya
```

---

### 3.2 Event Saya

Fungsi:

- Melihat event/kegiatan yang ditugaskan.
- Melengkapi data kegiatan.
- Mengajukan review.
- Membuka event/kegiatan finish secara read-only sesuai izin.

Menu group:

```text
Event Saya
  - Aktif
  - Finish
```

Catatan:

- Status dibuat sebagai tab/filter agar menu PWA tetap ringkas.
- Event/kegiatan draft dan arsip tidak tampil di PWA user.

### 3.2.1 Bottom Navigation User

Menu utama user ditampilkan sebagai bottom navigation mengambang:

```text
Beranda
Event
Catat
Saldo
Akun
```

Catatan:

- `Catat` menjadi akses cepat untuk transaksi, bukti, dokumentasi, transfer saldo, dan dana talangan.
- Bottom navigation harus nyaman dipakai di HP dan tetap proporsional di tablet.
- Hindari sidebar untuk user PWA.
- Gunakan warna aktif yang jelas, touch target besar, dan kontras yang nyaman.

---

### 3.3 Catat Cepat

Fungsi:

- Catat pengeluaran.
- Dana masuk jika diberi izin.
- Transfer saldo.
- Dana talangan.
- Catatan kegiatan.
- Upload bukti/foto.

Menu:

```text
Catat Cepat
```

Tombol:

```text
+ Pengeluaran
+ Dana Masuk
+ Transfer Saldo
+ Dana Talangan
+ Catatan Kegiatan
+ Upload Foto/Nota
```

---

### 3.4 Saldo Saya

Fungsi:

- Lihat saldo awal.
- Lihat pengeluaran.
- Lihat transfer masuk/keluar.
- Lihat sisa saldo.
- Lihat dana talangan.
- Lihat status klaim.

Menu group:

```text
Saldo Saya
  - Ringkasan
  - Mutasi
  - Transfer Saldo
```

Transfer saldo:

```text
User memilih penerima
User mengisi nominal
Sistem validasi saldo cukup
Saldo langsung berpindah
Mutasi tercatat
```

---

### 3.5 Catat Pengeluaran

Fungsi:

- Input transaksi.
- Upload bukti.
- Tandai bukti menyusul.
- Isi alasan tanpa bukti.
- Pilih kategori.
- Pilih metode pembayaran.
- Pilih sumber dana: saldo pegangan atau dana talangan.

Menu:

```text
Catat Pengeluaran
```

---

### 3.6 Dana Talangan

Fungsi:

- Catat pengeluaran memakai uang pribadi.
- Upload bukti.
- Buat klaim internal.
- Lihat status klaim.

Menu group:

```text
Dana Talangan
  - Buat Catatan Talangan
  - Klaim Saya
```

---

### 3.7 Dokumentasi

Fungsi:

- Upload foto kegiatan.
- Upload foto peserta.
- Upload foto lokasi.
- Upload screenshot pengumuman.
- Isi caption.
- Pilih apakah masuk lampiran LPJ.

Menu:

```text
Dokumentasi
```

---

### 3.8 Catatan Kegiatan

Fungsi:

- Catat kejadian lapangan.
- Catat kendala.
- Catat perubahan jadwal.
- Catat hasil.
- Catat evaluasi.
- Pilih catatan yang masuk dokumen LPJ.

Menu:

```text
Catatan Kegiatan
```

---

### 3.9 Preview LPJ

Fungsi:

- Preview bagian LPJ.
- Cek kelengkapan.
- Melihat rangkuman event/kegiatan aktif/finish sesuai izin.

Menu:

```text
Preview LPJ
```

---

## 4. Ringkasan Menu Admin

```text
Dashboard
Profil Lembaga
User Management
Master Event
  - Tipe Event
  - Template Narasi
  - Template Dokumen
  - Kategori Transaksi
  - Metode Pembayaran
  - Checklist Kelengkapan
Data Event
  - Semua Event
  - Review Event
  - Arsip Event
Dana Kegiatan
  - Dana Masuk
  - Dana Pegangan User
  - Rekap Dana
Saldo Pegangan User
  - Ringkasan Saldo
  - Tambah Dana Pegangan
  - Mutasi Saldo
  - Riwayat Transfer Saldo
  - Koreksi Saldo
Transaksi
  - Semua Transaksi
  - Perlu Review
  - Menunggu Bukti
  - Transaksi Valid
  - Transaksi Ditolak
Klaim Dana Talangan
  - Semua Klaim
  - Perlu Verifikasi
  - Dibayar
Dokumentasi & Lampiran
Review & Finalisasi
Export Dokumen
```

---

## 5. Ringkasan Menu User

```text
Beranda
Event
Catat
Saldo
Akun
```

Subfitur user tetap tersedia dari halaman terkait:

```text
Event Aktif
Event Finish
Catat Pengeluaran
Transfer Saldo
Dana Talangan
Upload Bukti/Foto
Dokumentasi
Catatan Kegiatan
Preview LPJ
```
