# AGENTS.md — Kicap Event

Dokumen ini berisi aturan kerja untuk AI assistant/agent yang membantu pengembangan Kicap Event.

**Project:** Kicap Event
**Lembaga awal:** PT. Kazoku Indonesia Center
**Jenis lembaga:** Lembaga Pelatihan Kerja
**Repo lokal:** `D:\kulino\lpj-kicap`
**WSL path:** `/mnt/d/kulino/lpj-kicap`
**GitHub SSH:** `git@github.com:Kulinoin/lpj-kicap.git`

---

## 1. Status Agent

Codex boleh digunakan setelah user memberikan instruksi eksplisit pada chat.

Tetap ikuti workflow slice:

- Baca dokumen aktif terlebih dahulu.
- Jangan implementasi di luar scope.
- Jangan commit sebelum validasi pass.
- Jangan push sebelum user setuju.

---

## 1.1 Vocabulary Lock Terbaru

Keputusan 20 Juni 2026:

```text
Event / Kegiatan = objek utama yang dikelola aplikasi
LPJ = output akhir/dokumen hasil dari event/kegiatan
```

Gunakan **Kicap Event** sebagai nama aplikasi dan **Event/Kegiatan** untuk objek kerja yang dibuat Admin, dibuka User, diisi operasionalnya, direview, dan difinalisasi.

Gunakan **LPJ** hanya untuk konteks dokumen akhir, preview dokumen, export/generate PDF, template dokumen, halaman pengesahan, rincian transaksi valid yang masuk dokumen, dan lampiran yang masuk dokumen final.

Jangan rename database/table/model/service secara agresif dalam slice biasa. Internal legacy seperti `lpj`, `lpjs`, `lpj_id`, `LpjResource`, dan route yang sudah berjalan boleh tetap dipertahankan sampai ada slice teknis khusus.

---

## 2. Stack

Stack yang digunakan:

```text
Laravel
React PWA
Filament
Vite
Tailwind CSS
MySQL
Laravel Storage
Queue/Job
Server-side document generator
```

---

## 3. Aturan Utama

1. Jangan menggunakan asumsi yang tidak dikonfirmasi.
2. Ikuti PRD dan dokumen aktif.
3. Gunakan vertical slice.
4. Setiap slice harus kecil, jelas, dan bisa divalidasi.
5. Jangan commit sebelum validasi pass.
6. Jangan push sebelum user setuju.
7. Buat archive di `docs/archive/` setelah validasi dan sebelum commit.
8. Jangan gunakan `git add .`.
9. Stage file secara eksplisit.
10. Update dokumentasi setiap ada perubahan penting.
11. Jika dokumen lama konflik dengan keputusan revisi terbaru, ikuti keputusan revisi terbaru.

---

## 4. Keputusan Produk yang Dikunci

### 4.1 Nama dan Domain

```text
Nama aplikasi: Kicap Event
Domain: lpj.kicap.id
```

### 4.2 Role

MVP hanya menggunakan:

```text
Admin
User
```

### 4.3 Pembuat Event/Kegiatan

```text
Event/Kegiatan hanya dibuat oleh Admin.
User/petugas hanya input kebutuhan operasional pada event/kegiatan yang ditugaskan.
LPJ adalah output akhir dari event/kegiatan.
```

### 4.4 Tipe Event/Kegiatan

Tipe awal:

```text
Penyelenggaraan Event
Pendampingan Peserta Seleksi
Delegasi / Perwakilan
Bantuan Dana / Sponsorship
Kegiatan Internal
```

### 4.5 Status Event/Kegiatan

Status MVP:

```text
draft
aktif
finish
arsipkan
```

Aturan:

```text
User hanya melihat event/kegiatan aktif dan finish yang ditugaskan.
Draft dan arsipkan adalah area kerja Admin.
Finish terkunci dari input operasional User.
```

### 4.6 Login

Login memakai satu halaman untuk semua role.

Aturan:

```text
Login menerima username/email dan password.
Checkbox Ingat saya wajib berfungsi.
Admin diarahkan ke /admin.
User diarahkan ke /app.
User yang mencoba akses /admin harus ditolak atau diarahkan.
```

### 4.7 Tema dan UI

User PWA:

```text
Responsive HP/tablet.
Bottom navigation mengambang.
Tampilan terasa seperti aplikasi mobile.
```

Admin:

```text
Responsive full-width.
Tidak menyisakan ruang kosong kiri-kanan yang terlalu lebar.
```

Tema visual:

```text
Cocok dengan logo Kicap.
Eye-catching, nyaman, dan memotivasi.
Tidak wajib hijau.
Arah awal: merah/coral, teal, amber, putih/netral terang, slate.
```

### 4.8 Transfer Saldo

Transfer saldo antar user **tidak memerlukan approval Admin**.

Aturan:

```text
User pengirim memilih penerima dan nominal.
Sistem validasi saldo cukup.
Saldo pengirim langsung berkurang.
Saldo penerima langsung bertambah.
Mutasi transfer_out dan transfer_in tercatat.
Admin dapat melihat riwayat transfer.
Transfer tidak masuk LPJ akhir.
```

Jika terjadi kesalahan, gunakan koreksi/reversal oleh Admin.

### 4.9 Dana Talangan

Dana talangan:

```text
Pengeluarannya masuk LPJ jika transaksi valid.
Klaimnya internal.
Pembayaran klaim tidak dihitung ulang sebagai pengeluaran LPJ.
Detail klaim tidak tampil default di LPJ akhir.
```

### 4.10 Output LPJ

Output LPJ akhir:

```text
Global
Formal
Mudah dibaca
PDF/print-ready
```

Tidak tampil default:

```text
Saldo per user
Transfer saldo
Status klaim dana talangan
Reimbursement klaim
Transaksi ditolak
Audit perubahan
Detail operasional internal
```

---

## 5. Workflow Slice

Urutan kerja:

```text
1. Bahas tujuan slice.
2. Kunci scope.
3. Implementasi sesuai slice.
4. Jalankan validasi.
5. Perbaiki error jika ada.
6. Buat archive di docs/archive/.
7. Tampilkan status akhir.
8. Tunggu persetujuan user.
9. Commit.
10. Push.
```

Commit/push harus dipisah dari implementasi.

---

## 6. Dokumentasi Wajib

Dokumen awal:

```text
00_CONCEPT_LOCK_KICAP_LPJ_v1_0.md
01_PRD_KICAP_LPJ_v1_0.md
02_STRUKTUR_MODUL_MENU_KICAP_LPJ.md
03_WORKFLOW_PENGGUNAAN_KICAP_LPJ.md
04_ROLE_PERMISSION_KICAP_LPJ.md
05_STRUKTUR_OUTPUT_DOKUMEN_LPJ_KICAP.md
06_TEMPLATE_NARASI_OTOMATIS_KICAP_LPJ.md
07_DATA_MODEL_AWAL_KICAP_LPJ.md
08_TECH_STACK_ARSITEKTUR_KICAP_LPJ.md
09_ROADMAP_VERTICAL_SLICE_KICAP_LPJ.md
10_AGENTS_KICAP_LPJ.md
README.md
```

Saat project berjalan, folder aktif harus tersedia:

```text
docs/active/PROGRESS.md
docs/active/CHECKLIST.md
docs/active/WORKLOG.md
docs/active/NEXT_TASK.md
```

Archive slice:

```text
docs/archive/
```

---

## 7. Aturan Repo

Gunakan path:

```bash
cd /mnt/d/kulino/lpj-kicap
```

Remote SSH:

```bash
git@github.com:Kulinoin/lpj-kicap.git
```

Jangan memakai remote HTTPS kecuali user meminta.

---

## 8. Larangan

Jangan:

1. Menggunakan asumsi yang tidak dikonfirmasi.
2. Menggunakan `git add .`.
3. Commit sebelum validasi.
4. Push sebelum user setuju.
5. Mengubah PRD tanpa update dokumen.
6. Membuat role tambahan tanpa persetujuan.
7. Membuat User bisa membuat LPJ tanpa keputusan baru.
8. Membuat status LPJ selain `draft`, `aktif`, `finish`, `arsipkan` tanpa keputusan baru.
9. Membuat transfer saldo butuh approval Admin.
10. Menampilkan transfer saldo di LPJ final default.
11. Menghitung reimbursement klaim sebagai pengeluaran LPJ kedua kali.
12. Membuat PDF resmi/final untuk LPJ yang belum `finish`.
