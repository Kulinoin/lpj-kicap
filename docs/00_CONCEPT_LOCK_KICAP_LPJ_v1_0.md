# Concept Lock Kicap LPJ v1.0

**Status:** Locked concept / basis PRD dan implementasi  
**Tanggal lock:** 19 Juni 2026  
**Nama aplikasi:** Kicap LPJ  
**Domain rencana:** `lpj.kicap.id`  
**Lembaga awal:** PT. Kazoku Indonesia Center  
**Jenis lembaga:** Lembaga Pelatihan Kerja  
**Repo lokal:** `D:\kulino\lpj-kicap`  
**WSL path:** `/mnt/d/kulino/lpj-kicap`  
**GitHub SSH:** `git@github.com:Kulinoin/lpj-kicap.git`  

---

## 0. Revisi Terkunci 20 Juni 2026

Keputusan berikut menggantikan bagian lama yang bertentangan, tanpa mengubah konsep utama aplikasi:

1. LPJ hanya dibuat oleh Admin.
2. User/petugas hanya mengisi kebutuhan operasional pada LPJ yang ditugaskan.
3. Status LPJ MVP disederhanakan menjadi:

```text
draft
aktif
finish
arsipkan
```

4. Halaman user hanya menampilkan LPJ berstatus `aktif` dan `finish`.
5. Login menggunakan satu halaman untuk semua role.
6. Setelah login, sistem mengarahkan Admin ke panel Admin dan User ke PWA.
7. Fitur `Ingat saya` wajib tersedia dan berjalan, terutama untuk user HP agar tidak sering login ulang.
8. User PWA menggunakan layout responsive mobile/tablet dengan bottom navigation mengambang.
9. Admin panel dibuat responsive dan full-width, tidak terlalu banyak ruang kosong kiri-kanan.
10. Tema visual dipilih agar cocok dengan logo Kicap, lebih eye-catching, nyaman, dan memotivasi user; tidak wajib mengikuti warna hijau pada contoh login.
11. Archive project disimpan di dalam `docs/archive/`.
12. Slice 01 di-reset dan dikerjakan ulang mengikuti keputusan revisi ini.

---

## 1. Ringkasan Konsep

Kicap LPJ adalah aplikasi **PWA mobile-first** untuk membantu user lapangan mencatat kegiatan, transaksi operasional, dokumentasi, bukti kegiatan, saldo pegangan user, transfer saldo antar user, dana talangan, dan klaim/reimbursement secara cepat.

Aplikasi ini bukan hanya aplikasi pembukuan. Fokus utamanya adalah:

```text
Catat cepat di lapangan
→ kelola saldo operasional user
→ lengkapi data kegiatan
→ review Admin
→ finalisasi
→ generate dokumen LPJ global
```

Output akhir berupa dokumen LPJ resmi yang rapi, formal, dan mudah dibaca. Detail internal seperti saldo per user, transfer saldo, dana talangan, audit perubahan, dan status klaim tetap tersimpan di sistem untuk kebutuhan kontrol internal Admin.

---

## 2. Prinsip Utama

1. **Mobile-first**  
   User lapangan harus nyaman input data dari HP.

2. **PWA**  
   Aplikasi bisa dibuka seperti aplikasi, installable, dan mendukung autosave/draft ringan.

3. **Detail di sistem, LPJ final sederhana**  
   Sistem mencatat detail operasional, tapi dokumen akhir tetap global dan formal.

4. **Template otomatis tapi editable**  
   Narasi dibuat otomatis berdasarkan tipe LPJ, tetapi user tetap bisa mengedit.

5. **Aman secara konteks kegiatan**  
   Untuk tipe pendampingan peserta, dokumen tidak boleh seolah-olah menyatakan organisasi sebagai penyelenggara utama jika kegiatan diselenggarakan pihak luar.

6. **Realistis untuk kondisi lapangan**  
   Mendukung saldo pindah tangan, dana pribadi/talangan, bukti menyusul, transaksi tanpa bukti dengan alasan, dan revisi transaksi.

---

## 3. Keputusan Produk yang Dikunci

| Bagian | Keputusan |
|---|---|
| Nama aplikasi | Kicap LPJ |
| Domain | `lpj.kicap.id` |
| Bentuk aplikasi | PWA mobile-first |
| Stack | Laravel + React PWA + Filament |
| Database | MySQL untuk implementasi awal |
| Role MVP | Admin + User |
| Pembuat LPJ | Admin saja |
| Tugas User | Input operasional pada LPJ aktif/finish yang ditugaskan |
| Super Admin | Tidak dimunculkan di MVP umum |
| Tipe LPJ | Berbasis tipe kegiatan |
| Status LPJ MVP | draft, aktif, finish, arsipkan |
| Login | Satu halaman login berbasis role |
| Ingat saya | Aktif dan wajib berjalan untuk sesi tahan lama |
| Template narasi | Otomatis berdasarkan tipe LPJ, editable |
| Saldo user | Ada saldo pegangan/operasional per user |
| Transfer saldo | Antar user, langsung tercatat tanpa approval Admin |
| Dana talangan | Dicatat sebagai biaya kegiatan jika valid, klaimnya internal |
| Reimbursement klaim | Tidak dihitung ulang sebagai pengeluaran LPJ |
| Output LPJ | Global, formal, sederhana, mudah dibaca |
| Detail internal | Tidak tampil default di LPJ final |
| Cover | Formal bersih |
| Kop surat | Kop lengkap hanya di halaman pengesahan/awal sesuai template |
| Halaman isi | Footer + nomor halaman |
| UI User | Mobile/tablet responsive dengan bottom navigation mengambang |
| UI Admin | Responsive full-width |
| Tema visual | Brand accent merah/coral, aksen pendukung teal/amber, dasar netral terang |
| Archive | `docs/archive/` |
| Workflow kerja | Manual chat, vertical slice, archive sebelum commit/push |
| Codex | Tidak digunakan sampai user konfirmasi eksplisit |

---

## 4. Tipe LPJ Awal

1. **Penyelenggaraan Event**  
   Untuk kegiatan yang diselenggarakan sendiri.

2. **Pendampingan Peserta Seleksi**  
   Untuk mendampingi peserta mengikuti seleksi/lomba/audisi/kegiatan pihak luar. Organisasi bukan penyelenggara utama.

3. **Delegasi / Perwakilan**  
   Untuk mengirim peserta/tim mewakili lembaga/organisasi.

4. **Bantuan Dana / Sponsorship**  
   Untuk mempertanggungjawabkan penggunaan dana bantuan.

5. **Kegiatan Internal**  
   Untuk rapat, pelatihan, workshop, program internal, atau operasional lembaga.

---

## 5. Sistem Keuangan Operasional

Kicap LPJ membedakan:

```text
Data operasional internal
Output LPJ resmi
```

Aturan utama:

| Jenis | Fungsi | Masuk LPJ Akhir |
|---|---|---|
| Dana Masuk Kegiatan | Dana diterima untuk kegiatan | Ya |
| Dana Pegangan User | Admin memberi dana ke user | Tidak, internal |
| Pengeluaran Kegiatan | Belanja/biaya kegiatan | Ya |
| Transfer Saldo Antar User | User A memindahkan saldo ke User B | Tidak, internal |
| Dana Talangan Pribadi | User bayar pakai uang pribadi | Pengeluarannya ya, klaimnya tidak |
| Pengembalian Sisa Dana | User setor sisa saldo | Tidak sebagai pemasukan baru, internal |
| Reimbursement Klaim | Admin mengganti dana talangan | Tidak, settlement internal |

---

## 6. Transfer Saldo Antar User

Transfer saldo antar user **tidak memerlukan approval Admin**.

Alur:

```text
User A memilih Transfer Saldo
User A memilih User B
User A mengisi nominal dan catatan
Sistem validasi saldo cukup
Saldo User A langsung berkurang
Saldo User B langsung bertambah
Mutasi internal tercatat
Admin dapat melihat riwayat transfer
```

Aturan:

1. Transfer tidak masuk pengeluaran LPJ.
2. Transfer hanya mutasi internal antar saldo pegangan user.
3. Saldo pengirim tidak boleh minus.
4. Admin tidak perlu menyetujui, tetapi dapat melihat riwayat dan audit.
5. Transfer dapat dikoreksi melalui mekanisme adjustment/reversal oleh Admin jika ada kesalahan.

---

## 7. Dana Talangan / Klaim

Dana talangan terjadi ketika user membayar biaya kegiatan memakai uang pribadi.

Efek sistem:

1. Transaksi biaya kegiatan tetap masuk sebagai pengeluaran kegiatan jika valid.
2. Saldo pegangan user tidak berkurang.
3. Sistem membuat klaim internal atas nama user.
4. Klaim bisa diverifikasi, ditolak, atau dibayar Admin.
5. Pembayaran klaim tidak dihitung ulang sebagai pengeluaran LPJ.

Status klaim:

```text
Diajukan
Diverifikasi
Ditolak
Dibayar
```

Detail klaim tidak perlu tampil default di dokumen LPJ akhir.

---

## 8. Output LPJ Final

Dokumen LPJ akhir menampilkan:

- Total dana diterima.
- Total pengeluaran.
- Rincian pengeluaran valid.
- Rincian pengeluaran per kategori.
- Total sisa dana.
- Bukti transaksi valid.
- Dokumentasi kegiatan.
- Evaluasi.
- Pengesahan.

Tidak tampil default:

- Saldo per user.
- Transfer saldo antar user.
- Status klaim dana talangan.
- Reimbursement klaim.
- Transaksi ditolak.
- Audit perubahan.
- Detail operasional internal.

---

## 9. Catatan Implementasi

1. Pengerjaan menggunakan vertical slice.
2. Setiap slice harus punya validasi.
3. Setiap slice harus update dokumentasi.
4. Archive dibuat setelah validasi pass dan sebelum commit.
5. Commit/push hanya setelah user menyetujui.
6. Jangan gunakan `git add .`.
7. Hindari asumsi.
8. Cek versi package dan signature sebelum implementasi.
