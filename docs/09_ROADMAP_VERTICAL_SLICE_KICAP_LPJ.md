# Roadmap Vertical Slice Kicap Event v1.0

**Prinsip:** Jangan terlalu banyak slice. Slice harus praktis, padat, bisa divalidasi, dan menghasilkan increment yang bisa dicoba.

---

## 0.0 Decision — Kicap Event sebagai Konsep Utama

Keputusan 20 Juni 2026:

```text
Event / Kegiatan = objek utama
LPJ = output dokumen akhir
```

Roadmap berikut tetap berlaku secara alur. Nama slice lama tidak perlu diganti, tetapi pengembangan berikutnya harus memakai istilah Event/Kegiatan untuk objek utama dan LPJ hanya untuk output dokumen final/preview/export/template.

---

## 0. Keputusan Revisi 20 Juni 2026

Roadmap ini mengikuti revisi:

1. Slice 01 di-reset dan dikerjakan ulang dari awal.
2. Event/Kegiatan hanya dibuat oleh Admin.
3. User hanya input operasional.
4. Status event/kegiatan MVP: `draft`, `aktif`, `finish`, `arsipkan`.
5. User hanya melihat event/kegiatan `aktif` dan `finish`.
6. Login tunggal berbasis role dengan remember-me aktif.
7. User PWA memakai bottom navigation mengambang.
8. Admin panel dibuat full-width.
9. Archive disimpan di `docs/archive/`.

---

## 1. Aturan Pengerjaan

1. Gunakan vertical slice.
2. Tiap slice harus punya target jelas.
3. Tiap slice harus bisa divalidasi.
4. Update dokumen setiap slice.
5. Buat archive di `docs/archive/` setelah validasi pass.
6. Commit dan push setelah user menyetujui.
7. Jangan `git add .`; stage file sesuai scope.
8. Jangan lanjut jika ada error.
9. Patch error dalam slice yang sama.
10. Hindari asumsi.
11. Cek versi dan signature package yang digunakan.
12. Gunakan data real/seed, bukan hardcoded palsu untuk flow utama.
13. Codex boleh digunakan setelah user memberi instruksi eksplisit pada chat.

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

## 3. Slice 01 — Master Event & Role

Target:

- Role Admin/User.
- Login tunggal berbasis role.
- Remember-me aktif.
- Profil lembaga.
- Tipe Event.
- Status event/kegiatan sederhana: draft/aktif/finish/arsipkan.
- Struktur awal data kegiatan.
- Data user seed.
- Admin membuat LPJ.
- User hanya melihat event/kegiatan aktif/finish yang ditugaskan.
- Admin panel full-width.

Hasil:

- Admin bisa mengelola master dasar.
- Admin bisa membuat event/kegiatan secara awal.
- Role MVP aktif.
- User tidak bisa membuat event/kegiatan.
- User PWA punya pondasi menu bawah mengambang.

Validasi:

- Admin login.
- User login.
- Login remember-me tersedia.
- Admin melihat menu master.
- User tidak melihat menu Admin.
- Tipe Event tersedia.
- User hanya melihat event/kegiatan aktif/finish yang ditugaskan.

---

## 4. Slice 02 — LPJ Detail & Input Mobile

Target:

- Halaman detail event/kegiatan aktif untuk User.
- Input/lengkapi data kegiatan sesuai penugasan.
- Template narasi otomatis.
- Narasi editable.
- Draft/autosave ringan.
- Tipe Event memengaruhi template.

Hasil:

- User bisa melengkapi event/kegiatan aktif dari HP.
- Narasi awal otomatis terbentuk.

Validasi:

- Admin membuat LPJ tipe Event dan mengaktifkannya.
- Admin membuat LPJ tipe Pendampingan dan mengaktifkannya.
- User melihat event/kegiatan aktif yang ditugaskan.
- Narasi berbeda sesuai tipe.
- Narasi bisa diedit.
- Draft tersimpan.

---

## 5. Slice 03 — Dana Kegiatan

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
- Status event/kegiatan.
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
