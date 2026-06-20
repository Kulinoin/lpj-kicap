# Workflow Penggunaan Kicap LPJ v1.0

**Status:** Draft MVP  
**Catatan utama:** Transfer saldo antar user langsung tercatat tanpa approval Admin.

---

## 0. Keputusan Revisi 20 Juni 2026

Keputusan ini menggantikan workflow lama yang bertentangan:

1. LPJ hanya dibuat oleh Admin.
2. User hanya input kebutuhan operasional pada LPJ yang ditugaskan.
3. Status LPJ MVP: `draft`, `aktif`, `finish`, `arsipkan`.
4. User hanya melihat LPJ `aktif` dan `finish`.
5. Login satu halaman, lalu redirect berdasarkan role.
6. `Ingat saya` harus membuat sesi login lebih tahan lama terutama di HP.

---

## 1. Workflow Besar

```text
Admin membuat LPJ draft
→ Admin mengatur user bertugas
→ Admin mengaktifkan LPJ
→ Admin memberi dana pegangan
→ User mencatat transaksi/dokumentasi/catatan lapangan
→ User bisa transfer saldo antar user jika perlu
→ User bisa mencatat dana talangan pribadi
→ Admin review transaksi dan kelengkapan
→ Admin melakukan rekonsiliasi/closing
→ Admin menyelesaikan LPJ
→ Sistem generate dokumen LPJ global
```

---

## 2. Workflow Admin

1. Login.
2. Mengatur profil lembaga.
3. Membuat LPJ/kegiatan baru sebagai draft.
4. Memilih tipe LPJ.
5. Mengatur user yang bertugas.
6. Mengubah status LPJ menjadi aktif saat siap diisi User.
7. Memberikan dana pegangan ke user.
8. Memantau transaksi dan saldo.
9. Melihat riwayat transfer saldo antar user.
10. Review transaksi.
11. Review kelengkapan LPJ.
12. Melakukan rekonsiliasi/closing.
13. Mengubah status LPJ menjadi finish.
14. Export PDF.

---

## 3. Workflow User

1. Login dari HP.
2. Membuka LPJ/kegiatan.
3. Melihat saldo pegangan.
4. Catat pengeluaran.
5. Upload bukti/nota.
6. Transfer saldo ke user lain jika perlu.
7. Catat dana talangan jika memakai uang pribadi.
8. Upload dokumentasi.
9. Catat kejadian/kendala.
10. Lengkapi bagian kegiatan.
11. Melihat LPJ finish secara read-only sesuai izin.

---

## 4. Workflow Pembuatan LPJ

### 4.1 Buat LPJ

```text
Admin memilih Buat LPJ
Pilih tipe LPJ
Isi data dasar kegiatan
Sistem membuat LPJ Draft
Admin menugaskan User
Admin mengubah status ke Aktif saat LPJ siap diisi User
```

Data dasar:

- Nama kegiatan.
- Tipe LPJ.
- Tanggal mulai.
- Tanggal selesai.
- Lokasi.
- Penanggung jawab.
- Sumber dana.
- Periode LPJ.
- Nomor surat/tugas jika ada.

### 4.2 Generate Narasi Awal

```text
Sistem membaca tipe LPJ
Sistem mengambil template narasi
Sistem mengganti placeholder
Narasi disimpan ke LPJ
User dapat mengedit
```

### 4.3 Lengkapi Data Non-Keuangan

User melengkapi:

- Data peserta.
- Data panitia/pendamping.
- Rundown.
- Pelaksanaan.
- Hasil.
- Evaluasi.
- Kendala.
- Saran.
- Dokumentasi.
- Lampiran pendukung.

---

## 5. Workflow Dana Masuk dan Dana Pegangan

### 5.1 Dana Masuk Kegiatan

```text
Admin mencatat dana masuk kegiatan
Sistem menambah total dana kegiatan
Dana masuk tampil di laporan LPJ global
```

### 5.2 Dana Pegangan User

```text
Admin memilih LPJ/kegiatan
Admin memilih user
Admin mengisi nominal dana pegangan
Sistem menambah saldo pegangan user
Sistem mencatat mutasi internal
```

Catatan:

- Dana pegangan user tidak tampil default di LPJ akhir.
- Dana pegangan dipakai untuk kontrol operasional.

---

## 6. Workflow Catat Pengeluaran

```text
User klik + Pengeluaran
Pilih LPJ/kegiatan
Isi nominal, kategori, keterangan
Pilih sumber dana
Upload bukti atau isi alasan tanpa bukti
Simpan
Status transaksi awal = Draft / Perlu Review / Menunggu Bukti
```

Sumber dana:

1. **Saldo Pegangan**  
   Saldo user berkurang.

2. **Dana Talangan Pribadi**  
   Saldo user tidak berkurang, sistem membuat klaim internal.

---

## 7. Workflow Transfer Saldo Antar User

Transfer saldo **tidak perlu approval Admin**.

```text
User A klik Transfer Saldo
Pilih LPJ/kegiatan
Pilih User B sebagai penerima
Isi nominal
Isi catatan
Sistem cek saldo User A cukup
Jika cukup:
  Saldo User A langsung berkurang
  Saldo User B langsung bertambah
  Mutasi transfer_out dan transfer_in tercatat
  Riwayat transfer bisa dilihat Admin
Jika tidak cukup:
  Sistem menolak transfer
```

Aturan:

1. Transfer tidak masuk pengeluaran LPJ.
2. Transfer bukan dana masuk baru.
3. Transfer tidak mengubah total dana kegiatan global.
4. Transfer hanya memindahkan saldo pegangan antar user.
5. Transfer tidak boleh membuat saldo pengirim minus.
6. Admin dapat melakukan koreksi jika terjadi kesalahan input.

---

## 8. Workflow Dana Talangan / Klaim

```text
User klik + Dana Talangan
Pilih LPJ/kegiatan
Isi nominal, kategori, keterangan
Upload bukti atau alasan tanpa bukti
Simpan
Sistem membuat transaksi pengeluaran dengan sumber dana talangan
Sistem membuat klaim internal atas nama user
```

Efek:

- Pengeluaran kegiatan masuk LPJ jika valid.
- Saldo pegangan user tidak berkurang.
- Klaim internal dapat diverifikasi dan dibayar Admin.
- Pembayaran klaim tidak dihitung ulang di LPJ.

Status klaim:

```text
Diajukan
Diverifikasi
Ditolak
Dibayar
```

---

## 9. Workflow Review Transaksi

```text
Admin membuka transaksi Perlu Review
Admin memeriksa kategori, nominal, bukti, dan catatan
Admin memilih Valid / Ditolak / Perlu Revisi
```

Jika valid:

- Transaksi masuk laporan LPJ.
- Jika sumber dana talangan, klaim tetap tercatat internal.

Jika ditolak:

- Transaksi tidak masuk LPJ akhir.
- Catatan penolakan wajib diisi.

Jika perlu revisi:

- User memperbaiki data.
- Riwayat perubahan tersimpan.

---

## 10. Workflow Transaksi Tanpa Bukti

```text
User mencatat transaksi
User memilih Bukti Tidak Ada / Bukti Menyusul
User mengisi alasan
Admin review alasan
Admin menentukan Valid atau Ditolak
```

Transaksi tanpa bukti tetap bisa valid jika Admin menyetujui alasan.

---

## 11. Workflow Split Transaksi

```text
User/Admin membuka transaksi
Upload satu bukti
Menambahkan beberapa item transaksi
Setiap item punya kategori dan nominal
Total item harus sama dengan total bukti
```

Untuk MVP, split transaksi dibuat sederhana.

---

## 12. Workflow Dokumentasi

```text
User upload foto kegiatan
Isi caption
Pilih kategori dokumentasi
Tandai tampil di LPJ atau internal saja
Simpan
```

Kategori dokumentasi:

- Foto lokasi.
- Foto peserta.
- Foto briefing.
- Foto registrasi.
- Foto pelaksanaan.
- Foto hasil.
- Screenshot pengumuman.
- Lain-lain.

---

## 13. Workflow Catatan Kegiatan

```text
User klik + Catatan Kegiatan
Pilih jenis catatan
Isi keterangan
Tandai apakah masuk Evaluasi/Kendala/Saran
Simpan
```

Contoh:

- Peserta terlambat.
- Lokasi berubah.
- Rundown mundur.
- Biaya tambahan muncul.
- Konsumsi kurang.
- Hasil seleksi belum diumumkan.

---

## 14. Workflow Checklist Kelengkapan

```text
Sistem membaca data LPJ
Sistem menampilkan checklist
User/Admin melengkapi item yang kurang
Jika semua wajib terpenuhi:
  Status kelengkapan = Siap Review
```

Checklist:

- Data kegiatan.
- Narasi.
- Peserta/panitia/pendamping.
- Rundown.
- Transaksi valid.
- Bukti atau alasan tanpa bukti.
- Dokumentasi.
- Evaluasi.
- Pengesahan.
- Rekonsiliasi saldo.

---

## 15. Workflow Rekonsiliasi / Closing

```text
Admin membuka Rekonsiliasi
Sistem menampilkan saldo sistem per user
Admin input sisa fisik
Sistem menghitung selisih
Jika ada selisih:
  Admin wajib mengisi catatan
Admin menyimpan closing
```

Rekonsiliasi membantu memastikan saldo user beres sebelum finalisasi LPJ.

---

## 16. Workflow Finalisasi LPJ

```text
LPJ Aktif
Admin review semua bagian
Admin melakukan rekonsiliasi/closing
Admin menyelesaikan LPJ
Status = finish
Dokumen dikunci
PDF dapat dibuat
```

---

## 17. Workflow Export Dokumen

```text
LPJ finish
Admin/User membuka preview
Sistem menampilkan print-ready view
Sistem generate PDF
PDF disimpan/diunduh
```

PDF hanya mengambil:

- Data global LPJ.
- Transaksi valid.
- Bukti transaksi valid.
- Dokumentasi yang dipilih masuk LPJ.
- Evaluasi/kendala/saran final.
- Pengesahan.

PDF tidak menampilkan default:

- Saldo per user.
- Transfer saldo.
- Klaim internal.
- Reimbursement.
- Audit trail.
- Transaksi ditolak.
