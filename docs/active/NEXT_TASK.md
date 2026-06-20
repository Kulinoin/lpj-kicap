# Next Task

## Slice 04 — Pelaksanaan & Dokumentasi

Slice 03 selesai implementasi dan validasi otomatis PASS pada 20 Juni 2026. Menunggu review manual user sebelum commit/push.

## Baseline Terkunci dari Slice 03

- Admin mencatat dana masuk LPJ dan dana pegangan user.
- User melihat saldo pegangan per LPJ.
- User mencatat pengeluaran operasional pada LPJ aktif yang ditugaskan.
- User upload bukti transaksi atau mengisi alasan tanpa bukti.
- Transfer saldo antar user tidak membutuhkan approval Admin.
- Transfer saldo hanya memindahkan saldo pegangan dan tidak masuk pengeluaran LPJ.
- Dana talangan membuat transaksi operasional dan klaim internal.
- Pembayaran klaim talangan tidak boleh dihitung ulang sebagai pengeluaran LPJ.
- LPJ `finish` tetap read-only untuk input operasional dan input keuangan User.

## Scope Awal Slice 04

- Catatan pelaksanaan kegiatan.
- Data peserta.
- Data panitia/pendamping.
- Rundown.
- Dokumentasi kegiatan.
- Lampiran pendukung.
- Evaluasi/kendala/saran yang dapat dipilih masuk LPJ.

## Guardrail Slice 04

- User tetap tidak membuat LPJ.
- User tetap tidak mengurus narasi formal Admin.
- Jangan membuat PDF resmi/final untuk LPJ yang belum `finish`.
- Jangan mengubah aturan transfer saldo Slice 03.
- Jangan menampilkan transfer saldo di output final LPJ default.

## Validasi Rencana Slice 04

- User input peserta.
- User input pendamping/panitia.
- User input rundown.
- User upload dokumentasi kegiatan.
- User memilih catatan tertentu untuk masuk LPJ.
- LPJ `finish` tetap read-only.
