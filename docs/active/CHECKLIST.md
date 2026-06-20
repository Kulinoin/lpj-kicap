# Checklist Manual Aktif

## Slice 05 — Review & Finalisasi Event

- [ ] Login sebagai User.
- [ ] Buka event/kegiatan aktif yang ditugaskan.
- [ ] Pastikan detail menampilkan status kelengkapan.
- [ ] Klik `Ajukan Review`.
- [ ] Pastikan status kelengkapan menjadi `Siap Review`.
- [ ] Login sebagai Admin.
- [ ] Buka menu `Semua Event`.
- [ ] Klik action `Checklist` pada event/kegiatan yang sama.
- [ ] Pastikan checklist menampilkan data dasar, pelaksanaan, catatan masuk LPJ, dokumentasi/lampiran, review transaksi, alasan tanpa bukti, dan rekonsiliasi saldo.
- [ ] Buka menu `Transaksi Event`.
- [ ] Tandai transaksi dengan bukti/alasan sebagai `Valid`.
- [ ] Tandai transaksi bermasalah sebagai `Revisi` atau `Minta Bukti`.
- [ ] Login lagi sebagai User.
- [ ] Buka tab `Keuangan`.
- [ ] Pastikan transaksi yang diminta revisi menampilkan catatan Admin.
- [ ] Kirim revisi transaksi dengan bukti atau alasan tanpa bukti.
- [ ] Login sebagai Admin.
- [ ] Validasi transaksi revisi.
- [ ] Buka `Checklist` sampai semua item PASS.
- [ ] Klik `Finalisasi`.
- [ ] Pastikan status event/kegiatan menjadi `Selesai`.
- [ ] Login sebagai User.
- [ ] Pastikan event/kegiatan `finish` tampil read-only.
- [ ] Pastikan User tidak bisa input operasional, keuangan, pelaksanaan, dokumentasi, atau revisi transaksi setelah `finish`.
- [ ] Pastikan tidak ada tombol/endpoint PDF resmi untuk event/kegiatan yang belum `finish`.

## Product Concept Rename — Kicap Event

- [ ] Pastikan brand aplikasi tampil sebagai `Kicap Event`.
- [ ] Pastikan daftar pekerjaan User memakai istilah `Event Saya` / `Kegiatan Saya`, bukan `LPJ Saya`.
- [ ] Pastikan detail kerja memakai istilah `Detail Event` atau `Detail Kegiatan`.
- [ ] Pastikan dana/transaksi operasional memakai istilah event/kegiatan.
- [ ] Pastikan kata `LPJ` tetap ada hanya untuk konteks `Masuk LPJ`, preview/export/generate dokumen, template dokumen, dan output final.
- [ ] Pastikan tidak ada perubahan alur login, role, transfer saldo, dana talangan, atau finalisasi.
- [ ] Pastikan schema/model/route internal `lpj` tidak direname agresif pada task ini.

## Slice 04 — Pelaksanaan & Dokumentasi

- [ ] Login sebagai User.
- [ ] Buka `/app` dari viewport HP.
- [ ] Pastikan bottom navigation tetap 5 item: `Beranda`, `Operasional`, `Keuangan`, `Selesai`, `Profil`.
- [ ] Buka tab `Operasional`.
- [ ] Buka event/kegiatan aktif yang ditugaskan.
- [ ] Pastikan catatan petugas berisi `Hasil di Lapangan`, `Evaluasi`, `Kendala`, dan `Saran Tindak Lanjut`.
- [ ] Isi catatan evaluasi/kendala/saran.
- [ ] Tandai catatan yang perlu masuk LPJ.
- [ ] Isi data peserta: nama, asal, nomor peserta, kehadiran, hasil, dan keterangan.
- [ ] Isi data panitia/pendamping: nama, jabatan/peran, tugas, dan kontak.
- [ ] Isi rundown: waktu mulai, waktu selesai, nama aktivitas, penanggung jawab, dan catatan.
- [ ] Simpan data kegiatan.
- [ ] Upload dokumentasi kegiatan berupa gambar/PDF.
- [ ] Tandai dokumentasi yang perlu masuk LPJ.
- [ ] Upload lampiran pendukung.
- [ ] Tandai lampiran yang perlu masuk LPJ.
- [ ] Pastikan file tersimpan tampil pada daftar `File tersimpan`.
- [ ] Kembali ke tab `Keuangan`.
- [ ] Pastikan aturan transfer saldo Slice 03 tetap langsung tanpa approval.
- [ ] Pastikan input pelaksanaan tidak membuat transaksi pengeluaran LPJ.
- [ ] Buka event/kegiatan `finish` sebagai User.
- [ ] Pastikan data pelaksanaan dan upload dokumentasi/lampiran read-only.
- [ ] Pastikan tidak ada tombol/endpoint PDF resmi untuk LPJ yang belum `finish`.
