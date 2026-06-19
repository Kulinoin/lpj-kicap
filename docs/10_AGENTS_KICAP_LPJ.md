# AGENTS.md — Kicap LPJ

Dokumen ini berisi aturan kerja untuk AI assistant/agent yang membantu pengembangan Kicap LPJ.

**Project:** Kicap LPJ  
**Lembaga awal:** PT. Kazoku Indonesia Center  
**Jenis lembaga:** Lembaga Pelatihan Kerja  
**Repo lokal:** `D:\kulino\lpj-kicap`  
**WSL path:** `/mnt/d/kulino/lpj-kicap`  
**GitHub SSH:** `git@github.com:Kulinoin/lpj-kicap.git`

---

## 1. Status Agent

Saat dokumen ini dibuat, pengembangan dilakukan manual melalui chat.

Codex belum digunakan.

Jangan menjalankan workflow Codex sampai user memberikan konfirmasi eksplisit.

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
7. Buat archive setelah validasi dan sebelum commit.
8. Jangan gunakan `git add .`.
9. Stage file secara eksplisit.
10. Update dokumentasi setiap ada perubahan penting.
11. Jangan menggunakan Codex kecuali user sudah mengonfirmasi.

---

## 4. Keputusan Produk yang Dikunci

### 4.1 Nama dan Domain

```text
Nama aplikasi: Kicap LPJ
Domain: lpj.kicap.id
```

### 4.2 Role

MVP hanya menggunakan:

```text
Admin
User
```

### 4.3 Tipe LPJ

Tipe awal:

```text
Penyelenggaraan Event
Pendampingan Peserta Seleksi
Delegasi / Perwakilan
Bantuan Dana / Sponsorship
Kegiatan Internal
```

### 4.4 Status LPJ

```text
Draft
Dalam Pengisian
Diajukan
Perlu Revisi
Disetujui
Final
Diarsipkan
```

### 4.5 Transfer Saldo

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

### 4.6 Dana Talangan

Dana talangan:

```text
Pengeluarannya masuk LPJ jika transaksi valid.
Klaimnya internal.
Pembayaran klaim tidak dihitung ulang sebagai pengeluaran LPJ.
Detail klaim tidak tampil default di LPJ akhir.
```

### 4.7 Output LPJ

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
3. Buat script implementasi.
4. Jalankan validasi.
5. Perbaiki error jika ada.
6. Buat archive.
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

1. Menggunakan Codex tanpa konfirmasi.
2. Menggunakan `git add .`.
3. Commit sebelum validasi.
4. Push sebelum user setuju.
5. Mengubah PRD tanpa update dokumen.
6. Membuat role tambahan tanpa persetujuan.
7. Membuat transfer saldo butuh approval Admin.
8. Menampilkan transfer saldo di LPJ final default.
9. Menghitung reimbursement klaim sebagai pengeluaran LPJ kedua kali.
10. Membuat PDF resmi/final untuk LPJ yang belum final.

