# Slice 03 — Operasional Keuangan

Status: Implementasi + validasi otomatis PASS, menunggu review manual user sebelum commit/push.

## Scope

- Dana masuk kegiatan dicatat oleh Admin.
- Dana pegangan user dicatat oleh Admin dan menambah saldo user per LPJ.
- User melihat saldo pada detail LPJ yang ditugaskan.
- User mencatat pengeluaran dari saldo pegangan.
- User upload bukti transaksi atau mengisi alasan tanpa bukti.
- User transfer saldo antar user yang ditugaskan pada LPJ yang sama tanpa approval Admin.
- User mencatat dana talangan dan sistem membuat klaim internal.
- Input operasional dan operasional keuangan dipisah menjadi menu bawah `Operasional` dan `Keuangan`.
- Sumber dana masuk dikunci ke pilihan `Lembaga`, `Sponsor`, dan `Dinas`.
- Kategori transaksi User dikunci ke pilihan `Konsumsi`, `Akomodasi`, `Operasional`, `Transportasi`, `Dokumentasi`, dan `Lainnya`.
- Admin dapat melihat dana masuk, mutasi saldo, saldo user, transaksi operasional, dan klaim talangan.

## Out of Scope

- Review/validasi/tolak/revisi transaksi oleh Admin.
- Rekonsiliasi saldo dan closing LPJ.
- PDF resmi/final LPJ.
- Transfer saldo tampil di LPJ akhir.
- Pembayaran klaim talangan.
- Role baru selain Admin/User.
- Status LPJ baru selain `draft`, `aktif`, `finish`, `arsipkan`.

## Guardrail

- Transfer saldo langsung memindahkan saldo dan tidak membuat transaksi pengeluaran LPJ.
- Pengeluaran dana talangan membuat transaksi operasional dan klaim internal, tetapi saldo pegangan user tidak berkurang.
- LPJ `finish` tampil read-only untuk input keuangan User.
- User tetap tidak dapat membuat LPJ atau mengurus narasi LPJ.

## Validasi Otomatis PASS

- `php artisan test tests/Feature/Slice03OperationalFinanceTest.php` — 6 tests, 31 assertions.
- `php artisan route:list --path=api/app` — endpoint Slice 03 tersedia.
- `php artisan route:list --path=admin` — resource Admin keuangan tersedia.
- `php artisan test tests/Feature/Slice02LpjDetailMobileInputTest.php` — 5 tests, 27 assertions.
- `php artisan test` — 40 tests, 130 assertions.
- `php artisan migrate:fresh --seed -n`
- `cmd.exe /c "cd /d D:\kulino\lpj-kicap && npm.cmd run build"`

## Review Manual Browser

- Login Admin, buka `/admin`, pastikan menu `Operasional Keuangan` berisi `Dana Masuk LPJ`, `Mutasi Saldo`, `Saldo User`, `Transaksi Operasional`, dan `Klaim Talangan`.
- Admin catat `Dana Masuk LPJ`, pastikan `Sumber Dana` berupa pilihan `Lembaga`, `Sponsor`, dan `Dinas`, lalu pastikan total dana LPJ bertambah di data LPJ.
- Admin buka `Mutasi Saldo`, klik `Beri Dana Pegangan`, pilih LPJ dan User, lalu pastikan `Saldo User` bertambah.
- Admin cek kolom `User Terkait Transfer` pada `Mutasi Saldo`: berisi nama user lawan transfer untuk mutasi transfer, dan `-` untuk mutasi yang tidak punya lawan user.
- Login User dari viewport HP, buka `/app`, pastikan bottom navigation berisi `Beranda`, `Operasional`, `Keuangan`, `Selesai`, dan `Profil`.
- Masuk `Operasional`, buka LPJ aktif demo, dan pastikan yang tampil hanya input operasional/catatan petugas.
- Masuk `Keuangan`, buka LPJ aktif demo, dan pastikan detail LPJ menampilkan saldo pegangan, form pengeluaran, form transfer, form dana talangan, dan riwayat terbaru.
- Pastikan kategori pengeluaran dan kategori dana talangan berupa pilihan tetap.
- Catat pengeluaran dengan upload bukti PDF/gambar, pastikan saldo berkurang dan transaksi muncul dengan status `Perlu Review`.
- Catat pengeluaran tanpa bukti dengan mengisi alasan, pastikan transaksi tetap tersimpan.
- Transfer saldo ke user lain yang ditugaskan, pastikan saldo pengirim langsung berkurang dan transfer tidak muncul sebagai pengeluaran.
- Catat dana talangan, pastikan saldo pegangan tidak berkurang dan Admin melihat klaim di `Klaim Talangan`.
- Buka LPJ `finish` yang ditugaskan, pastikan form keuangan tidak bisa dipakai.
