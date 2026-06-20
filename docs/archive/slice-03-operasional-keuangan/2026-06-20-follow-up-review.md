# Archive Slice 03 Follow-up Review

Waktu: 2026-06-20

Status: Follow-up atas review manual user. Belum commit/push.

## Perubahan

- `Dana Masuk LPJ` memakai pilihan sumber dana:
  - `Lembaga`
  - `Sponsor`
  - `Dinas`
- Kolom `User Terkait Transfer` pada `Mutasi Saldo` menampilkan nama user lawan transfer untuk mutasi transfer, dan `-` untuk mutasi non-transfer.
- Kategori transaksi User di PWA memakai pilihan tetap:
  - `Konsumsi`
  - `Akomodasi`
  - `Operasional`
  - `Transportasi`
  - `Dokumentasi`
  - `Lainnya`
- Bottom navigation PWA dipisah menjadi:
  - `Operasional` untuk input catatan petugas.
  - `Keuangan` untuk saldo, pengeluaran, transfer, dan dana talangan.

## Guardrail

- Transfer saldo tetap tanpa approval Admin.
- Transfer saldo tetap tidak membuat pengeluaran LPJ.
- Dana talangan tetap membuat klaim internal.
- User tetap tidak membuat LPJ.
