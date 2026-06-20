# Next Task

## Status Terakhir

Slice 04 — Pelaksanaan & Dokumentasi selesai implementasi dan validasi otomatis PASS pada 20 Juni 2026. Product concept rename ke Kicap Event sedang diterapkan sebagai pelurusan istilah, bukan perubahan alur kerja mesin.

## Vocabulary Lock Terbaru

```text
Event / Kegiatan = objek utama yang dikelola aplikasi
LPJ = output akhir/dokumen hasil dari event/kegiatan
```

Gunakan Event/Kegiatan untuk objek yang dibuat Admin, dibuka User, diisi operasionalnya, direview, dan difinalisasi. Gunakan LPJ hanya untuk dokumen final, preview, export/generate, template dokumen, halaman pengesahan, rincian transaksi valid yang masuk dokumen, dan lampiran final.

## Baseline Terkunci dari Slice 03-04

- Admin mencatat dana masuk LPJ dan dana pegangan user.
- User melihat saldo pegangan per LPJ.
- User mencatat pengeluaran operasional pada event/kegiatan aktif yang ditugaskan.
- User upload bukti transaksi atau mengisi alasan tanpa bukti.
- Transfer saldo antar user tidak membutuhkan approval Admin.
- Transfer saldo hanya memindahkan saldo pegangan dan tidak masuk pengeluaran LPJ.
- Dana talangan membuat transaksi operasional dan klaim internal.
- Pembayaran klaim talangan tidak boleh dihitung ulang sebagai pengeluaran LPJ.
- LPJ `finish` tetap read-only untuk input operasional dan input keuangan User.
- User dapat mengisi data peserta, panitia/pendamping, rundown, dokumentasi, lampiran, dan catatan evaluasi/kendala/saran pada event/kegiatan aktif yang ditugaskan.
- Catatan operasional dan file dokumentasi/lampiran dapat ditandai `Masuk LPJ`.
- LPJ `finish` tetap read-only untuk input pelaksanaan dan upload dokumentasi.

## Next Slice

Slice 05 — Review & Finalisasi Event.

## Scope Awal Slice 05

- Checklist kelengkapan event/kegiatan.
- Review Admin.
- Validasi transaksi.
- Transaksi tanpa bukti dengan alasan.
- Revisi transaksi.
- Status event/kegiatan.
- Rekonsiliasi saldo sederhana.
- Lock final event/kegiatan sebagai dasar generate LPJ.

## Guardrail Slice 05

- User tetap tidak membuat LPJ.
- User tetap tidak mengurus narasi formal Admin.
- Jangan membuat PDF resmi/final untuk LPJ yang belum `finish`.
- Jangan mengubah aturan transfer saldo Slice 03.
- Jangan menampilkan transfer saldo di output final LPJ default.
- Jangan menghitung reimbursement klaim sebagai pengeluaran LPJ kedua kali.

## Validasi Rencana Slice 05

- Admin melihat checklist kelengkapan LPJ.
- Admin review transaksi User.
- Admin dapat menandai transaksi valid/ditolak/perlu revisi.
- LPJ dapat dikunci `finish`.
- User tidak dapat input operasional/keuangan/pelaksanaan setelah `finish`.
