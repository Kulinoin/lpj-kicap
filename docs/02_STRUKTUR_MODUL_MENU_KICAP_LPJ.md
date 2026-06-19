# Struktur Modul & Menu Kicap LPJ v1.0

**Status:** Draft MVP  
**Role:** Admin dan User  
**Catatan utama:** Transfer saldo antar user tidak perlu approval Admin; Admin tetap dapat melihat riwayat transfer.

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

- Ringkasan LPJ aktif.
- LPJ menunggu review.
- Transaksi perlu review.
- Klaim dana talangan menunggu verifikasi.
- Total dana kegiatan.
- Ringkasan saldo user.
- Transfer saldo terbaru.
- Checklist LPJ siap finalisasi.

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
- Izin user membuat LPJ.
- Izin user transfer saldo.
- Penugasan user ke LPJ.

Menu:

```text
User Management
```

---

### 2.4 Master LPJ

Fungsi:

- Tipe LPJ.
- Template narasi.
- Template dokumen.
- Kategori transaksi.
- Metode pembayaran.
- Status default.
- Checklist kelengkapan.

Menu group:

```text
Master LPJ
  - Tipe LPJ
  - Template Narasi
  - Template Dokumen
  - Kategori Transaksi
  - Metode Pembayaran
  - Checklist Kelengkapan
```

---

### 2.5 Data LPJ

Fungsi:

- Melihat semua LPJ.
- Membuat LPJ.
- Review data kegiatan.
- Review transaksi.
- Review dokumentasi.
- Finalisasi LPJ.
- Export dokumen.

Menu group:

```text
Data LPJ
  - Semua LPJ
  - LPJ Draft
  - LPJ Diajukan
  - LPJ Perlu Revisi
  - LPJ Disetujui
  - LPJ Final
  - Arsip LPJ
```

Catatan:

- Untuk implementasi UI, status boleh berupa filter di halaman Semua LPJ.
- Jika sidebar terlalu ramai, cukup gunakan `Semua LPJ` dan `Review LPJ`.

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
  - Checklist LPJ
  - Review LPJ
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

- LPJ aktif.
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

### 3.2 LPJ Saya

Fungsi:

- Melihat LPJ yang ditugaskan.
- Membuat LPJ jika diberi izin.
- Melengkapi data kegiatan.
- Mengajukan review.

Menu group:

```text
LPJ Saya
  - Semua LPJ Saya
  - Buat LPJ
  - Draft
  - Perlu Revisi
  - Diajukan
  - Final
```

Catatan:

- Status bisa dibuat sebagai tab/filter agar menu PWA tetap ringkas.

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
- Ajukan review ke Admin.

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
Master LPJ
  - Tipe LPJ
  - Template Narasi
  - Template Dokumen
  - Kategori Transaksi
  - Metode Pembayaran
  - Checklist Kelengkapan
Data LPJ
  - Semua LPJ
  - Review LPJ
  - Arsip LPJ
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
Dashboard Saya
LPJ Saya
Catat Cepat
Saldo Saya
Catat Pengeluaran
Transfer Saldo
Dana Talangan
Dokumentasi
Catatan Kegiatan
Preview LPJ
```

