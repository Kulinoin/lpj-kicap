# Roadmap Vertical Slice Kicap LPJ v1.0

**Prinsip:** Jangan terlalu banyak slice. Slice harus praktis, padat, bisa divalidasi, dan menghasilkan increment yang bisa dicoba.

---

## 1. Aturan Pengerjaan

1. Gunakan vertical slice.
2. Tiap slice harus punya target jelas.
3. Tiap slice harus bisa divalidasi.
4. Update dokumen setiap slice.
5. Buat archive setelah validasi pass.
6. Commit dan push setelah user menyetujui.
7. Jangan `git add .`; stage file sesuai scope.
8. Jangan lanjut jika ada error.
9. Patch error dalam slice yang sama.
10. Hindari asumsi.
11. Cek versi dan signature package yang digunakan.
12. Gunakan data real/seed, bukan hardcoded palsu untuk flow utama.
13. Belum menggunakan Codex sampai user konfirmasi eksplisit.

---

## 2. Slice 00 — Project Foundation

Target:

- Setup Laravel.
- Setup React PWA.
- Setup Filament.
- Setup database MySQL.
- Auth dasar.
- Layout awal.
- Health check.
- README awal.
- Connect repo lokal ke GitHub SSH.

Hasil:

- Aplikasi bisa jalan.
- Admin panel bisa diakses.
- User PWA bisa dibuka.
- Struktur proyek siap lanjut.

Validasi:

- App bisa jalan.
- Database connect.
- Filament login muncul.
- React PWA baseline muncul.
- Git remote SSH benar.
- README tersedia.

---

## 3. Slice 01 — Master LPJ & Role

Target:

- Role Admin/User.
- Profil lembaga.
- Tipe LPJ.
- Status LPJ.
- Struktur awal data kegiatan.
- Data user seed.

Hasil:

- Admin bisa mengelola master dasar.
- LPJ/kegiatan bisa dibuat secara awal.
- Role MVP aktif.

Validasi:

- Admin login.
- User login.
- Admin melihat menu master.
- User tidak melihat menu Admin.
- Tipe LPJ tersedia.

---

## 4. Slice 02 — LPJ Wizard Mobile

Target:

- Wizard buat LPJ.
- Input data dasar kegiatan.
- Template narasi otomatis.
- Narasi editable.
- Draft/autosave ringan.
- Tipe LPJ memengaruhi template.

Hasil:

- User bisa membuat LPJ dari HP.
- Narasi awal otomatis terbentuk.

Validasi:

- Buat LPJ tipe Event.
- Buat LPJ tipe Pendampingan.
- Narasi berbeda sesuai tipe.
- Narasi bisa diedit.
- Draft tersimpan.

---

## 5. Slice 03 — Operasional Keuangan

Target:

- Dana masuk.
- Dana pegangan user.
- Saldo user.
- Catat pengeluaran per user.
- Upload bukti transaksi.
- Transfer saldo antar user tanpa approval Admin.
- Dana talangan/klaim.
- Status transaksi dasar.

Hasil:

- Operasional keuangan lapangan bisa berjalan.

Validasi:

- Admin memberi dana pegangan.
- User melihat saldo.
- User catat pengeluaran.
- User upload bukti.
- User transfer saldo ke user lain, saldo langsung berpindah.
- User catat dana talangan, klaim internal terbentuk.
- Transfer tidak masuk pengeluaran LPJ.

---

## 6. Slice 04 — Pelaksanaan & Dokumentasi

Target:

- Catatan pelaksanaan kegiatan.
- Data peserta.
- Data panitia/pendamping.
- Rundown.
- Dokumentasi kegiatan.
- Lampiran pendukung.
- Evaluasi/kendala/saran.

Hasil:

- Data non-keuangan untuk LPJ bisa dilengkapi.

Validasi:

- Input peserta.
- Input pendamping/panitia.
- Input rundown.
- Upload dokumentasi.
- Catatan kendala bisa dipilih masuk LPJ.

---

## 7. Slice 05 — Review & Finalisasi

Target:

- Checklist kelengkapan.
- Review Admin.
- Validasi transaksi.
- Transaksi tanpa bukti dengan alasan.
- Revisi transaksi.
- Status LPJ.
- Rekonsiliasi saldo sederhana.
- Lock final.

Hasil:

- LPJ bisa diajukan, direview, disetujui, dan difinalisasi.

Validasi:

- User ajukan LPJ.
- Admin review transaksi.
- Admin validasi/menolak transaksi.
- Admin minta revisi.
- User revisi.
- Admin rekonsiliasi saldo.
- LPJ final terkunci.

---

## 8. Slice 06 — Generate Dokumen LPJ

Target:

- Cover formal.
- Halaman pengesahan dengan kop lengkap.
- Footer dan nomor halaman.
- Isi LPJ.
- Keuangan global.
- Rincian transaksi valid.
- Dokumentasi.
- Lampiran.
- Export PDF.
- Print-ready view.

Hasil:

- Dokumen LPJ resmi bisa dihasilkan.

Validasi:

- PDF menampilkan data global.
- PDF hanya menampilkan transaksi valid.
- Transfer saldo tidak tampil di LPJ akhir.
- Klaim/reimbursement tidak tampil sebagai pengeluaran baru.
- Dana talangan valid tampil sebagai biaya kegiatan.
- Lampiran bukti valid tersusun rapi.

---

## 9. Slice 07 — MVP Polish & Siap Pakai

Target:

- Polish UI mobile.
- PWA installable.
- Validasi end-to-end.
- Perbaikan bug.
- Dokumentasi penggunaan.
- Checklist manual.
- Archive final MVP.
- Commit/push final.

Hasil:

- MVP siap digunakan untuk kegiatan nyata.

Validasi end-to-end:

```text
Admin setup lembaga
Admin buat LPJ
Admin beri dana user
User input transaksi
User transfer saldo
User input dana talangan
User upload bukti/dokumentasi
Admin review transaksi
Admin rekonsiliasi
Admin finalisasi
Sistem generate PDF
```

---

## 10. Post-MVP

Fitur lanjutan:

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

