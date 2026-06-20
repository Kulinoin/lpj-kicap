# Next Task

## Status Terakhir

Slice 05 — Review & Finalisasi Event selesai implementasi dan validasi otomatis awal PASS pada 20 Juni 2026. Product concept rename ke Kicap Event tetap menjadi vocabulary lock: Event/Kegiatan adalah objek kerja, LPJ adalah output dokumen akhir.

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

## Baseline Terkunci dari Slice 05

- User dapat mengajukan event/kegiatan aktif untuk review Admin.
- Admin melihat checklist kelengkapan sebelum finalisasi.
- Admin dapat review transaksi User sebagai `valid`, `ditolak`, `perlu_revisi`, atau `menunggu_bukti`.
- Transaksi tanpa bukti wajib punya alasan sebelum divalidasi.
- User dapat merevisi transaksi miliknya jika diminta Admin.
- Pengeluaran valid dan sisa dana dihitung dari transaksi berstatus `valid`.
- Event/kegiatan hanya dapat dikunci `finish` jika checklist finalisasi PASS.
- Event/kegiatan `finish` mengunci input User untuk operasional, keuangan, pelaksanaan, dokumentasi, dan revisi transaksi.

## Next Slice

Slice 06 — Generate Dokumen LPJ.

## Scope Awal Slice 06

- Cover formal.
- Halaman pengesahan dengan kop lengkap.
- Footer dan nomor halaman.
- Isi LPJ global.
- Keuangan global.
- Rincian transaksi valid.
- Dokumentasi.
- Lampiran.
- Export PDF.
- Print-ready view.

## Guardrail Slice 06

- User tetap tidak membuat LPJ.
- User tetap tidak mengurus narasi formal Admin.
- Jangan membuat PDF resmi/final untuk LPJ yang belum `finish`.
- Jangan mengubah aturan transfer saldo Slice 03.
- Jangan menampilkan transfer saldo di output final LPJ default.
- Jangan menghitung reimbursement klaim sebagai pengeluaran LPJ kedua kali.

## Validasi Rencana Slice 06

- PDF/print-ready hanya dapat dibuat untuk event/kegiatan `finish`.
- PDF menampilkan data global event/kegiatan.
- PDF hanya menampilkan transaksi valid.
- Transfer saldo tidak tampil di LPJ akhir.
- Klaim/reimbursement tidak tampil sebagai pengeluaran baru.
- Dana talangan valid tampil sebagai biaya kegiatan.
- Dokumentasi dan lampiran `Masuk LPJ` tersusun rapi.
