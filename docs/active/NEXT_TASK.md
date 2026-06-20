# Next Task

## Slice 03 — Operasional Keuangan

Slice 02 selesai divalidasi dan disetujui untuk commit/push pada 20 Juni 2026.

## Baseline Terkunci dari Slice 02

- User/Petugas hanya input data operasional lapangan.
- Narasi LPJ adalah urusan Admin, bukan User/Petugas.
- PWA User memakai bottom navigation 5 item: `Beranda`, `LPJ`, `Input Cepat`, `Selesai`, `Profil`.
- Tombol tengah `Input Cepat` dipakai untuk fungsi yang paling sering digunakan petugas.
- LPJ `finish` tampil sebagai `Selesai` dan read-only.
- Bahasa UX interface User memakai bahasa Indonesia.

## Scope Awal Slice 03

- Dana masuk kegiatan.
- Dana pegangan user.
- Saldo user.
- Catat pengeluaran per user.
- Upload bukti transaksi.
- Transfer saldo antar user tanpa approval Admin.
- Dana talangan/klaim.
- Status transaksi dasar.

## Guardrail Slice 03

- Transfer saldo antar user tidak memerlukan approval Admin.
- Transfer saldo tidak masuk LPJ akhir.
- Pembayaran klaim dana talangan tidak dihitung ulang sebagai pengeluaran LPJ.
- User tetap tidak membuat LPJ.
- User tetap tidak mengurus narasi LPJ.
- Jangan membuat PDF resmi/final untuk LPJ yang belum `finish`.

## Validasi Rencana Slice 03

- Admin memberi dana pegangan.
- User melihat saldo.
- User catat pengeluaran.
- User upload bukti.
- User transfer saldo ke user lain dan saldo langsung berpindah.
- User catat dana talangan dan klaim internal terbentuk.
- Transfer tidak masuk pengeluaran LPJ.
