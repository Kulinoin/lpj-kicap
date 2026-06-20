# Checklist Manual Aktif

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
