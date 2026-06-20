# Next Task

## Status Terakhir

Slice 06 — Generate Dokumen LPJ selesai implementasi dan validasi otomatis fokus PASS pada 20 Juni 2026. Product concept rename ke Kicap Event tetap menjadi vocabulary lock: Event/Kegiatan adalah objek kerja, LPJ adalah output dokumen akhir.

## Baseline Terkunci dari Slice 06

- Dokumen LPJ final hanya dapat dibuat untuk event/kegiatan status `finish`.
- Admin dapat membuka print-ready preview LPJ final.
- Admin dapat download PDF LPJ final.
- Output LPJ final berisi cover formal, halaman pengesahan, footer/nomor halaman, isi global, keuangan global, rincian transaksi valid, dokumentasi, lampiran, dan penutup.
- Output LPJ final hanya menampilkan transaksi berstatus `valid`.
- Transfer saldo tidak tampil di LPJ akhir.
- Klaim/reimbursement dana talangan tidak tampil default dan tidak dihitung sebagai pengeluaran baru.
- Dana talangan yang transaksi operasionalnya sudah `valid` tampil sebagai biaya kegiatan.
- Dokumentasi, lampiran, dan catatan hanya tampil jika ditandai `Masuk LPJ`.
- User tetap tidak membuat/generate LPJ final.
- Total dana yang dialokasikan Admin ke user tidak boleh melebihi dana masuk event.
- Dana Masuk Event menampilkan alokasi dana dan sisa alokasi.
- User yang ditugaskan dapat membuka print preview LPJ untuk event/kegiatan `finish`.
- Admin memiliki menu `Dokumen LPJ` untuk melihat snapshot LPJ final yang sudah dihasilkan.
- Snapshot LPJ tersimpan setiap print preview/PDF final dibuat.
- Admin memiliki menu `Pengaturan Penyimpanan` untuk Local Storage atau Cloudflare R2.
- Dokumentasi/lampiran/proof/avatar menyimpan disk storage agar aman saat pindah provider.
- Foto yang diupload disiapkan kompres otomatis ke WebP jika server mengaktifkan GD/WebP.
- Admin dapat upload logo lembaga dari `Organization Profiles` untuk cover/kop LPJ.
- Panduan penempatan logo aplikasi, favicon, dan ikon PWA tersedia di `docs/active/APP_ASSET_GUIDE.md`.
- User menu `Catatan` berdiri sendiri untuk catatan petugas.
- User menu `Operasional` fokus pada peserta, tim/panitia/pendamping, dan rundown.
- User menu `Beranda` kembali menjadi layar awal ringkasan/daftar event.

## Next Slice

Slice 07 — MVP Polish & Siap Pakai.

## Scope Awal Slice 07

- Polish UI mobile dan admin setelah review manual Slice 06.
- PWA installable sanity check.
- Validasi end-to-end dari Admin setup event/kegiatan sampai PDF LPJ final.
- Perbaikan bug kecil yang ditemukan dari review manual.
- Dokumentasi penggunaan MVP.
- Checklist manual final MVP.
- Archive final MVP.

## Guardrail Slice 07

- Jangan rename schema/model/route internal `lpj` tanpa slice teknis khusus.
- Jangan mengubah aturan role Admin/User.
- Jangan membuat User bisa membuat event/kegiatan atau LPJ final.
- Jangan mengubah status event/kegiatan selain `draft`, `aktif`, `finish`, `arsipkan`.
- Jangan membuat transfer saldo butuh approval Admin.
- Jangan menampilkan transfer saldo atau klaim/reimbursement di LPJ final default.
- Jangan membiarkan total alokasi dana pegangan user melewati dana masuk event.

## Validasi Rencana Slice 07

- End-to-end Admin membuat event/kegiatan, assign User, input pelaksanaan/keuangan, review, finalisasi, preview, dan download PDF.
- User hanya melihat event/kegiatan assigned `aktif` dan `finish`.
- Event/kegiatan `finish` tetap read-only untuk User.
- PDF LPJ final hanya tersedia untuk event/kegiatan `finish`.
- Print preview LPJ tersedia untuk Admin dan User yang ditugaskan pada event/kegiatan `finish`.
- Menu `Dokumen LPJ` menampilkan snapshot yang sudah dihasilkan dan bisa dibuka ulang.
- Alokasi dana pegangan user tertolak jika melewati dana masuk event.
- Pengaturan penyimpanan Local/R2 dapat dibuka Admin.
- Upload logo lembaga tampil di output LPJ final.
- Beranda User kembali ke layar awal setelah berpindah dari menu lain.
- Menu Catatan, Operasional, Dokumentasi, dan Keuangan masing-masing berdiri sesuai fungsi.
- Build frontend PASS.
- Test fokus role, input, review/finalisasi, dokumentasi, dan report PASS.
