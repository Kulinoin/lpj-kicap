# Template Narasi Otomatis Kicap Event v1.0

**Status:** Draft template awal
**Prinsip:** Template otomatis, editable, bisa di-reset ke template awal.

---

## 0. Decision — Kicap Event sebagai Konsep Utama

Keputusan 20 Juni 2026:

```text
Event / Kegiatan = objek utama
LPJ = output dokumen akhir
```

Template narasi mengambil data dari event/kegiatan dan menghasilkan narasi untuk dokumen LPJ. Istilah LPJ tetap dipakai ketika membahas output dokumen, bukan objek kerja awal.

---

## 1. Prinsip Template

Template narasi harus:

1. Menggunakan placeholder dari data kegiatan.
2. Dibuat berdasarkan tipe event/kegiatan.
3. Bisa diedit manual oleh user.
4. Bisa di-reset ke template awal.
5. Tidak mengubah template master ketika diedit di event/kegiatan.
6. Aman secara konteks, terutama untuk pendampingan kegiatan pihak luar.

---

## 2. Placeholder Template

Placeholder awal:

```text
{nama_kegiatan}
{nama_organisasi}
{jenis_lembaga}
{penyelenggara_eksternal}
{tanggal_kegiatan}
{tanggal_mulai}
{tanggal_selesai}
{lokasi_kegiatan}
{jumlah_peserta}
{jumlah_pendamping}
{nama_penanggung_jawab}
{sumber_dana}
{periode_lpj}
{hasil_kegiatan}
```

---

## 3. Penyelenggaraan Event

### 3.1 Latar Belakang

```text
Kegiatan {nama_kegiatan} diselenggarakan sebagai bentuk pelaksanaan program kerja {nama_organisasi}. Kegiatan ini bertujuan untuk memberikan manfaat kepada peserta melalui rangkaian acara yang telah direncanakan, dilaksanakan, dan dievaluasi oleh panitia pelaksana.

Melalui kegiatan ini, diharapkan tujuan yang telah ditetapkan dapat tercapai dengan baik serta seluruh penggunaan dana dan sumber daya dapat dipertanggungjawabkan secara transparan.
```

### 3.2 Maksud

```text
Maksud dari kegiatan ini adalah untuk melaksanakan program kegiatan {nama_organisasi} sesuai dengan rencana yang telah disusun serta memberikan laporan pertanggungjawaban atas pelaksanaan kegiatan tersebut.
```

### 3.3 Tujuan

```text
Tujuan kegiatan ini adalah:
1. Melaksanakan kegiatan {nama_kegiatan} secara tertib dan terarah.
2. Mewujudkan program kerja organisasi/lembaga.
3. Memberikan manfaat kepada peserta kegiatan.
4. Menyampaikan pertanggungjawaban pelaksanaan kegiatan dan penggunaan anggaran.
```

### 3.4 Penutup

```text
Demikian laporan pertanggungjawaban kegiatan {nama_kegiatan} ini disusun sebagai bentuk pertanggungjawaban atas pelaksanaan kegiatan dan penggunaan dana. Semoga laporan ini dapat menjadi bahan evaluasi dan dokumentasi bagi pelaksanaan kegiatan berikutnya.
```

---

## 4. Pendampingan Peserta Seleksi

### 4.1 Latar Belakang

```text
Kegiatan pendampingan peserta seleksi {nama_kegiatan} dilaksanakan sebagai bentuk dukungan kepada peserta yang mengikuti kegiatan seleksi yang diselenggarakan oleh {penyelenggara_eksternal}. Dalam kegiatan ini, {nama_organisasi} berperan sebagai pihak pendamping yang membantu peserta selama proses keberangkatan, registrasi, pelaksanaan seleksi, hingga kegiatan selesai.

Pendampingan ini dilakukan agar peserta dapat mengikuti seluruh rangkaian seleksi dengan tertib, tepat waktu, dan sesuai ketentuan yang berlaku dari pihak penyelenggara.
```

### 4.2 Maksud

```text
Maksud dari kegiatan ini adalah untuk memberikan pendampingan kepada peserta dalam mengikuti seleksi {nama_kegiatan} serta memastikan proses pendampingan dapat dipertanggungjawabkan secara administratif dan keuangan.
```

### 4.3 Tujuan

```text
Tujuan kegiatan pendampingan ini adalah:
1. Mendampingi peserta selama mengikuti proses seleksi.
2. Membantu koordinasi teknis keberangkatan, kehadiran, dan administrasi peserta.
3. Mendokumentasikan proses pendampingan peserta.
4. Melaporkan hasil kegiatan pendampingan kepada pihak terkait.
5. Mempertanggungjawabkan penggunaan dana selama kegiatan berlangsung.
```

### 4.4 Peran Organisasi

```text
Dalam kegiatan ini, {nama_organisasi} tidak bertindak sebagai penyelenggara utama, melainkan sebagai pihak pendamping peserta. Penyelenggara utama kegiatan adalah {penyelenggara_eksternal}. Peran {nama_organisasi} meliputi pendampingan teknis, koordinasi keberangkatan, dokumentasi, serta pendampingan peserta selama kegiatan berlangsung.
```

### 4.5 Penutup

```text
Demikian laporan pertanggungjawaban pendampingan peserta seleksi {nama_kegiatan} ini disusun sebagai dokumentasi dan pertanggungjawaban atas kegiatan pendampingan serta penggunaan dana yang telah dilakukan.
```

---

## 5. Delegasi / Perwakilan

### 5.1 Latar Belakang

```text
Kegiatan {nama_kegiatan} diikuti oleh peserta/tim sebagai bentuk delegasi atau perwakilan dari {nama_organisasi}. Keikutsertaan ini merupakan bagian dari upaya lembaga untuk mendukung pengembangan kompetensi, partisipasi, dan representasi dalam kegiatan yang relevan.
```

### 5.2 Maksud

```text
Maksud kegiatan ini adalah untuk mengirimkan peserta/tim sebagai delegasi atau perwakilan {nama_organisasi} dalam kegiatan {nama_kegiatan} serta menyusun pertanggungjawaban atas pelaksanaan dan penggunaan dana selama kegiatan berlangsung.
```

### 5.3 Tujuan

```text
Tujuan kegiatan ini adalah:
1. Mengikuti kegiatan {nama_kegiatan} sebagai perwakilan lembaga.
2. Mendukung peserta/tim dalam mengikuti seluruh rangkaian kegiatan.
3. Mendokumentasikan proses dan hasil kegiatan.
4. Menyampaikan laporan pertanggungjawaban pelaksanaan dan penggunaan dana.
```

---

## 6. Bantuan Dana / Sponsorship

### 6.1 Latar Belakang

```text
Kegiatan {nama_kegiatan} mendapatkan dukungan dana dari {sumber_dana}. Dukungan tersebut digunakan untuk menunjang pelaksanaan kegiatan sesuai kebutuhan yang telah direncanakan dan disesuaikan dengan kondisi lapangan.
```

### 6.2 Maksud

```text
Maksud laporan ini adalah untuk menyampaikan pertanggungjawaban penggunaan dana bantuan/sponsorship dalam kegiatan {nama_kegiatan}.
```

### 6.3 Tujuan

```text
Tujuan laporan ini adalah:
1. Menyampaikan penggunaan dana secara transparan.
2. Melaporkan pelaksanaan kegiatan yang didukung dana tersebut.
3. Menyediakan dokumentasi dan bukti pertanggungjawaban.
4. Menjadi bahan evaluasi bagi pihak terkait.
```

---

## 7. Kegiatan Internal

### 7.1 Latar Belakang

```text
Kegiatan internal {nama_kegiatan} dilaksanakan sebagai bagian dari kebutuhan organisasi/lembaga dalam mendukung operasional, koordinasi, peningkatan kapasitas, atau pelaksanaan program internal.
```

### 7.2 Maksud

```text
Maksud kegiatan ini adalah untuk menjalankan kebutuhan internal {nama_organisasi} serta menyusun laporan pertanggungjawaban atas kegiatan dan penggunaan dana yang dilakukan.
```

### 7.3 Tujuan

```text
Tujuan kegiatan ini adalah:
1. Melaksanakan kegiatan internal secara tertib.
2. Mendukung kebutuhan koordinasi dan operasional lembaga.
3. Mendokumentasikan pelaksanaan kegiatan.
4. Mempertanggungjawabkan penggunaan dana.
```

---

## 8. Template Evaluasi dan Kendala

```text
Selama pelaksanaan kegiatan, terdapat beberapa catatan yang menjadi bahan evaluasi. Catatan tersebut meliputi kendala teknis, perubahan kondisi lapangan, serta kebutuhan penyesuaian selama kegiatan berlangsung.

Adapun kendala yang terjadi telah ditangani sesuai kemampuan tim di lapangan. Evaluasi ini diharapkan dapat menjadi bahan perbaikan untuk pelaksanaan kegiatan berikutnya.
```

---

## 9. Template Keuangan Global

```text
Dana yang diterima untuk kegiatan ini digunakan untuk mendukung kebutuhan pelaksanaan kegiatan. Setiap pengeluaran dicatat berdasarkan transaksi yang terjadi di lapangan dan telah melalui proses validasi.

Rincian penggunaan dana disajikan pada bagian laporan keuangan, sedangkan bukti transaksi valid dilampirkan sebagai bagian dari dokumen pendukung LPJ.
```
