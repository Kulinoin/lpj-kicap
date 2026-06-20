# Decision — Kicap Event sebagai Konsep Utama, LPJ sebagai Output

**Tanggal:** 20 Juni 2026
**Project:** Kicap Event
**Status:** Locked untuk pengembangan berikutnya

## Keputusan

Nama konsep produk berubah dari Kicap LPJ menjadi Kicap Event.

Event/Kegiatan adalah objek utama yang dikelola aplikasi. LPJ adalah output akhir berupa dokumen pertanggungjawaban yang digenerate setelah event/kegiatan direview dan difinalisasi.

## Alasan

LPJ bukan proses awal, melainkan dokumen hasil akhir dari event/kegiatan. Alur kerja mesin yang sudah ada tetap benar, hanya istilah produk yang perlu dirapikan agar tidak salah kaprah.

## Dampak

- UI dan dokumen memakai Event/Kegiatan untuk objek utama.
- LPJ hanya digunakan untuk output dokumen final, preview, export, dan template dokumen.
- Alur kerja Admin membuat kerangka dan User mengisi operasional tetap dipertahankan.
- Database/internal code legacy boleh tetap memakai nama `lpj` sementara jika rename berisiko.

## Vocabulary Lock

| Konsep | Istilah |
|---|---|
| Nama aplikasi | Kicap Event |
| Objek utama | Event / Kegiatan |
| Data yang dibuat Admin | Event / Kegiatan |
| Data yang dibuka User | Event Saya / Kegiatan Saya |
| Tipe | Tipe Event / Tipe Kegiatan |
| Status utama | Status Event / Status Kegiatan |
| Operasional | Operasional Event / Operasional Kegiatan |
| Keuangan | Dana Event / Dana Kegiatan |
| Saldo user | Saldo Pegangan User per Event/Kegiatan |
| Transaksi | Transaksi Event / Transaksi Kegiatan |
| Dokumentasi | Dokumentasi Event / Dokumentasi Kegiatan |
| Catatan | Catatan Event / Catatan Kegiatan |
| Output final | LPJ |
| Export final | Generate LPJ / Export LPJ |
| Preview final | Preview LPJ |
| Template output | Template Dokumen LPJ |

## Aturan Kata LPJ

Kata LPJ digunakan untuk dokumen final, preview dokumen, export PDF/print-ready, template dokumen LPJ, halaman pengesahan LPJ, rincian transaksi valid yang masuk LPJ, dan lampiran yang masuk LPJ final.

Kata LPJ tidak digunakan untuk objek kerja baru, daftar pekerjaan lapangan, dashboard aktif, status proses operasional, catat cepat, transfer saldo, dana talangan, atau dokumentasi lapangan mentah.
