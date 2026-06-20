# PRD Kicap LPJ v1.0

**Nama aplikasi:** Kicap LPJ  
**Domain rencana:** `lpj.kicap.id`  
**Lembaga awal:** PT. Kazoku Indonesia Center  
**Jenis lembaga:** Lembaga Pelatihan Kerja  
**Status:** PRD MVP v1.0  
**Basis:** Concept Lock & PRD Handoff Kicap LPJ, disesuaikan dengan keputusan terbaru bahwa transfer saldo antar user tidak memerlukan approval Admin.

---

## 0. Keputusan Revisi 20 Juni 2026

Bagian ini menjadi acuan terbaru jika ada konflik dengan teks PRD lama.

1. LPJ hanya dibuat oleh Admin.
2. User/petugas tidak membuat LPJ; User hanya mengisi data operasional pada LPJ yang ditugaskan.
3. Status LPJ MVP hanya:

```text
draft
aktif
finish
arsipkan
```

4. Di halaman user, LPJ yang tampil hanya status `aktif` dan `finish`.
5. Status `draft` dan `arsipkan` adalah area kerja Admin.
6. Status `finish` bersifat terkunci untuk input operasional User, kecuali nanti ada keputusan unlock khusus.
7. Login memakai satu halaman untuk Admin dan User.
8. Setelah login, Admin diarahkan ke panel Admin dan User diarahkan ke PWA.
9. Opsi `Ingat saya` wajib berjalan agar user HP tidak sering login ulang.
10. User PWA menggunakan bottom navigation mengambang dan tetap nyaman di HP/tablet.
11. Admin panel dibuat responsive full-width.
12. Tema warna dipilih agar cocok dengan logo Kicap, nyaman, eye-catching, dan memotivasi; tidak wajib mengikuti warna hijau contoh login.

---

## 1. Ringkasan Produk

Kicap LPJ adalah aplikasi PWA mobile-first untuk membantu lembaga, panitia, tim pendamping, atau organisasi membuat Laporan Pertanggungjawaban secara lebih cepat dan rapi.

Aplikasi membantu proses mulai dari pencatatan cepat di lapangan, pengelolaan saldo user, pencatatan transaksi, upload bukti, dokumentasi kegiatan, review Admin, finalisasi, sampai export dokumen LPJ.

Kicap LPJ menjaga prinsip:

```text
Operasional detail dicatat di sistem.
Dokumen LPJ final tetap global, sederhana, formal, dan siap dibaca.
```

---

## 2. Masalah yang Diselesaikan

Kondisi lapangan sering tidak ideal:

1. Dana kegiatan dibagi ke beberapa user.
2. Masing-masing user membawa saldo pegangan.
3. Transfer saldo antar user sering terjadi.
4. Ada pengeluaran mendadak.
5. Ada dana talangan pribadi.
6. Bukti transaksi bisa menyusul atau hilang.
7. Kegiatan berubah dari rencana awal.
8. Catatan kendala sering lupa ditulis.
9. Dokumen LPJ akhir tetap harus rapi dan formal.

Kicap LPJ dibuat untuk mengakomodir kondisi itu tanpa membuat output akhir menjadi terlalu rumit.

---

## 3. Tujuan Produk

1. Memudahkan pencatatan kegiatan dan transaksi dari HP.
2. Menyediakan saldo pegangan user per kegiatan/LPJ.
3. Mendukung transfer saldo antar user tanpa approval Admin, tetapi tetap tercatat.
4. Mencatat dana talangan pribadi dan klaim internal.
5. Membantu Admin melakukan review transaksi, kelengkapan, dan finalisasi LPJ.
6. Menghasilkan dokumen LPJ global yang formal dan siap export PDF.
7. Menyimpan detail operasional internal untuk audit Admin.

---

## 4. Target Pengguna

Kicap LPJ dapat digunakan oleh:

- Lembaga pelatihan.
- Sekolah.
- Kampus.
- Komunitas.
- Yayasan.
- Panitia kegiatan.
- Tim pendamping peserta.
- Organisasi pemuda.
- Instansi kecil/menengah.
- Event internal organisasi.

---

## 5. Role MVP

### 5.1 Admin

Admin adalah pengelola utama aplikasi/organisasi.

Tugas Admin:

- Kelola profil lembaga.
- Kelola user.
- Kelola tipe LPJ.
- Kelola template narasi.
- Kelola template dokumen.
- Membuat LPJ.
- Melihat semua LPJ.
- Mengatur user yang bertugas dalam LPJ.
- Memberikan dana pegangan ke user.
- Melihat saldo user.
- Melihat transaksi global.
- Melihat transfer saldo antar user.
- Review dan validasi transaksi.
- Review kelengkapan LPJ.
- Mengembalikan LPJ untuk revisi.
- Menyetujui LPJ.
- Finalisasi LPJ.
- Export PDF.
- Mengunci LPJ final.
- Verifikasi dan bayar klaim dana talangan.

### 5.2 User

User adalah petugas lapangan, panitia, bendahara kegiatan, pendamping, atau orang yang diberi akses input data.

Tugas User:

- Mengisi data operasional pada LPJ yang ditugaskan.
- Mengisi data kegiatan.
- Mencatat transaksi.
- Upload bukti/nota.
- Melihat saldo pegangan sendiri.
- Transfer saldo ke user lain jika diizinkan.
- Mencatat dana talangan pribadi.
- Upload dokumentasi kegiatan.
- Mencatat pelaksanaan kegiatan.
- Mencatat kendala, hasil, dan evaluasi.
- Mengajukan LPJ ke Admin untuk review.

---

## 6. Status LPJ

Status LPJ MVP:

```text
draft
aktif
finish
arsipkan
```

Alur:

```text
draft → aktif → finish → arsipkan
```

Aturan:

1. `draft` dibuat dan dikelola Admin sebelum LPJ dibuka untuk user.
2. `aktif` berarti LPJ berjalan dan dapat diisi User yang ditugaskan.
3. `finish` berarti LPJ selesai, dikunci dari input operasional User, dan siap menjadi dasar output resmi.
4. `arsipkan` berarti LPJ tidak aktif tetapi tetap tersimpan untuk Admin.
5. User hanya melihat LPJ status `aktif` dan `finish`.

---

## 7. Tipe LPJ / Tipe Kegiatan

MVP mendukung tipe LPJ berikut:

| Tipe | Fungsi |
|---|---|
| Penyelenggaraan Event | Untuk kegiatan yang diselenggarakan sendiri |
| Pendampingan Peserta Seleksi | Untuk mendampingi peserta mengikuti kegiatan pihak luar |
| Delegasi / Perwakilan | Untuk mengirim peserta/tim mewakili lembaga |
| Bantuan Dana / Sponsorship | Untuk pertanggungjawaban penggunaan dana bantuan |
| Kegiatan Internal | Untuk rapat, pelatihan, workshop, dan kegiatan internal |

Tipe LPJ memengaruhi:

1. Field data.
2. Template narasi.
3. Label dokumen.
4. Bagian penyelenggara/peran organisasi.
5. Struktur output LPJ.

---

## 8. Input Data LPJ

### 8.1 Data Dasar Kegiatan

- Nama kegiatan.
- Tipe LPJ.
- Tanggal mulai.
- Tanggal selesai.
- Lokasi/tempat kegiatan.
- Penanggung jawab.
- Nama organisasi/lembaga.
- Sumber dana.
- Nomor surat/tugas jika ada.
- Periode LPJ.

### 8.2 Narasi

- Latar belakang.
- Maksud.
- Tujuan.
- Pelaksanaan.
- Hasil.
- Evaluasi.
- Kendala.
- Saran.
- Penutup.

Narasi otomatis berdasarkan tipe LPJ dan dapat diedit manual.

### 8.3 Data Penyelenggara dan Peran Organisasi

Untuk kegiatan sendiri:

- Penyelenggara.
- Ketua panitia.
- Sekretaris.
- Bendahara.
- Seksi acara.
- Seksi konsumsi.
- Seksi dokumentasi.

Untuk pendampingan:

- Penyelenggara eksternal.
- Peran organisasi.
- Koordinator pendamping.
- Jumlah pendamping.
- Tugas pendamping.
- Catatan bahwa organisasi bukan penyelenggara utama.

### 8.4 Data Peserta

- Nama peserta.
- Asal/kelas/divisi.
- Nomor peserta jika ada.
- Status kehadiran.
- Status hasil seleksi jika ada.
- Keterangan.

### 8.5 Data Panitia/Pendamping

- Nama.
- Jabatan/peran.
- Tugas.
- Kontak opsional.

### 8.6 Rundown / Jadwal

- Waktu.
- Kegiatan.
- Penanggung jawab.
- Keterangan.

### 8.7 Dokumentasi

- Foto kegiatan.
- Foto peserta.
- Foto lokasi.
- Foto briefing.
- Foto keberangkatan.
- Foto registrasi.
- Foto pelaksanaan.
- Screenshot pengumuman jika ada.
- Caption setiap foto.

### 8.8 Lampiran Pendukung

- Surat undangan.
- Surat tugas.
- Daftar hadir.
- Sertifikat.
- Pengumuman hasil seleksi.
- Nota/kwitansi.
- Tiket/struk transport.
- Proposal/RAB jika ada.

---

## 9. Fitur Cepat Lapangan

Tombol cepat:

```text
+ Pengeluaran
+ Dana Masuk
+ Transfer Saldo
+ Dana Talangan
+ Catatan Kegiatan
+ Upload Foto/Nota
```

Form cepat minimal:

- Nominal.
- Kategori.
- Keterangan.
- Foto bukti.
- Metode pembayaran.
- Tanggal otomatis, bisa diedit.

Detail tambahan bisa dilengkapi setelah kegiatan selesai.

---

## 10. Sistem Keuangan Operasional

### 10.1 Dana Masuk Kegiatan

Dana masuk kegiatan adalah dana global untuk kegiatan/LPJ.

Masuk LPJ akhir sebagai total dana diterima.

### 10.2 Dana Pegangan User

Dana pegangan user adalah distribusi internal dari Admin ke user.

Tidak tampil default di LPJ akhir, tetapi dipakai sistem untuk:

- Kontrol saldo user.
- Catat pengeluaran per user.
- Rekonsiliasi saldo.
- Audit internal.

### 10.3 Pengeluaran Kegiatan

Pengeluaran kegiatan adalah transaksi valid yang masuk ke LPJ akhir.

Pengeluaran dapat berasal dari:

1. Saldo pegangan user.
2. Dana talangan pribadi user.

### 10.4 Transfer Saldo Antar User

Transfer saldo antar user tidak memerlukan approval Admin.

Transfer hanya:

- Mengurangi saldo pengirim.
- Menambah saldo penerima.
- Membuat mutasi internal.
- Terlihat oleh Admin di riwayat.
- Tidak masuk pengeluaran LPJ.

### 10.5 Dana Talangan / Klaim

Dana talangan terjadi ketika user membayar biaya kegiatan memakai uang pribadi.

Aturan:

- Pengeluarannya masuk LPJ jika valid.
- Klaim internal dibuat untuk user.
- Pembayaran klaim tidak dihitung ulang sebagai pengeluaran LPJ.
- Detail klaim tidak tampil default di LPJ akhir.

---

## 11. Status Transaksi

Status transaksi:

| Status | Fungsi |
|---|---|
| Draft | Baru dicatat cepat |
| Menunggu Bukti | Bukti belum diupload |
| Perlu Review | Perlu dicek Admin |
| Valid | Masuk laporan LPJ |
| Ditolak | Tidak masuk laporan |
| Direvisi | Sudah diperbaiki |

Output LPJ akhir hanya mengambil transaksi yang berstatus **Valid**.

---

## 12. Transaksi Tanpa Bukti

Sistem mendukung transaksi tanpa bukti.

User wajib mengisi alasan/catatan pertanggungjawaban jika bukti tidak ada.

Contoh alasan:

- Nota hilang.
- Penjual tidak menyediakan nota.
- Biaya parkir.
- Biaya mendadak.
- Bukti menyusul.

Admin menentukan apakah transaksi valid.

---

## 13. Split Transaksi

Satu bukti dapat dipecah menjadi beberapa item transaksi.

Contoh:

```text
Nota Rp100.000
- Snack peserta Rp50.000
- Air mineral Rp30.000
- ATK Rp20.000
```

Untuk MVP, split transaksi dibuat sederhana.

---

## 14. Koreksi dan Audit Trail

Transaksi yang sudah masuk review/final tidak boleh dihapus diam-diam.

Sistem harus mencatat:

- Koreksi nominal.
- Catatan revisi.
- Pembatalan transaksi.
- Riwayat perubahan.
- User yang mengubah.
- Waktu perubahan.

---

## 15. Rekonsiliasi / Closing Kegiatan

Setelah kegiatan selesai, Admin melakukan closing.

Data rekonsiliasi:

- User.
- Saldo sistem.
- Sisa fisik.
- Selisih.
- Catatan selisih.

Rekonsiliasi membantu memastikan dana pegangan user beres sebelum LPJ difinalisasi.

---

## 16. Checklist Kelengkapan LPJ

Checklist:

- Data kegiatan lengkap.
- Latar belakang sudah diisi.
- Maksud dan tujuan sudah diisi.
- Peserta sudah diinput.
- Panitia/pendamping sudah diinput.
- Rundown/pelaksanaan sudah diisi.
- Transaksi sudah divalidasi.
- Bukti transaksi lengkap atau diberi alasan.
- Dokumentasi kegiatan sudah diupload.
- Evaluasi sudah diisi.
- Pengesahan lengkap.
- Saldo user sudah direkonsiliasi.
- LPJ siap finalisasi.

Status kelengkapan:

```text
Belum Lengkap
Siap Review
Siap Finalisasi
```

---

## 17. Output Dokumen

Output MVP:

```text
PDF
Print-ready view
```

Word tidak wajib untuk MVP. Jika tidak terlalu berat, export Word boleh masuk fitur tambahan; kalau tidak, dipindah ke post-MVP.

---

## 18. Scope MVP

Fitur wajib MVP:

1. PWA mobile-first.
2. Login tunggal Admin/User berbasis role.
3. Profil lembaga.
4. Admin membuat LPJ/kegiatan baru.
5. Tipe LPJ.
6. Template latar belakang otomatis editable.
7. Template maksud dan tujuan otomatis editable.
8. Input data dasar kegiatan.
9. Catat transaksi per user.
10. Upload bukti/nota.
11. Saldo pegangan user.
12. Transfer saldo antar user tanpa approval Admin.
13. Dana talangan/klaim.
14. Catatan pelaksanaan kegiatan.
15. Upload dokumentasi kegiatan.
16. Checklist kelengkapan LPJ.
17. Review/validasi Admin.
18. Output LPJ global.
19. Layout dokumen formal.
20. Export PDF.
21. Autosave draft ringan.
22. Opsi `Ingat saya` pada login.
23. Bottom navigation mengambang untuk user PWA.
24. Admin panel responsive full-width.
25. Tema visual yang cocok dengan logo dan nyaman untuk penggunaan harian.

---

## 19. Post-MVP

Fitur post-MVP:

- OCR nota otomatis.
- Tanda tangan digital resmi.
- Approval bertingkat banyak jabatan.
- Multi organisasi/SaaS penuh.
- Template dokumen banyak gaya.
- Offline sync kompleks.
- Dashboard analitik.
- Scan QR transaksi.
- Export Excel lengkap.
- Notifikasi lanjutan.
- Import peserta dari Excel.
