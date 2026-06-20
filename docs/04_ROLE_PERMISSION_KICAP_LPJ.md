# Role & Permission Kicap LPJ v1.0

**Role MVP:** Admin dan User  
**Catatan:** Super Admin tidak dimunculkan untuk MVP umum.

---

## 0. Keputusan Revisi 20 Juni 2026

Jika ada konflik dengan bagian lama, gunakan aturan berikut:

1. LPJ hanya dibuat oleh Admin.
2. User tidak memiliki permission membuat LPJ.
3. User hanya input operasional pada LPJ `aktif` yang ditugaskan.
4. User dapat melihat LPJ `finish` yang ditugaskan secara read-only sesuai izin.
5. Status LPJ MVP: `draft`, `aktif`, `finish`, `arsipkan`.
6. Login satu halaman untuk Admin dan User, redirect berdasarkan role.

---

## 1. Prinsip Permission

1. Permission dibuat sederhana berbasis role.
2. Status LPJ lebih penting daripada menambah role.
3. Admin mengelola dan mereview semua data.
4. User fokus input data lapangan.
5. User hanya melihat data yang menjadi tugasnya.
6. Transfer saldo antar user tidak butuh approval Admin, tetapi tercatat untuk audit.

---

## 2. Admin

Admin memiliki akses penuh terhadap organisasi.

Hak Admin:

| Fitur | Hak Admin |
|---|---|
| Profil lembaga | Kelola |
| User | Kelola |
| Tipe LPJ | Kelola |
| Template narasi | Kelola |
| Template dokumen | Kelola |
| LPJ | Buat, lihat semua, edit sesuai status, review, finalisasi |
| Dana kegiatan | Kelola |
| Dana pegangan user | Tambah dan koreksi |
| Saldo user | Lihat semua |
| Transfer saldo | Lihat riwayat dan audit |
| Transaksi | Review, validasi, tolak, revisi |
| Klaim dana talangan | Verifikasi, tolak, bayar |
| Dokumentasi | Review |
| Lampiran | Review |
| Checklist | Review |
| Rekonsiliasi | Kelola |
| Export dokumen | Export PDF/print-ready |
| Audit trail | Lihat |

---

## 3. User

User adalah petugas lapangan.

Hak User:

| Fitur | Hak User |
|---|---|
| Dashboard saya | Lihat |
| LPJ saya | Lihat |
| Buat LPJ | Tidak |
| Data kegiatan | Isi/edit sesuai status |
| Narasi | Isi/edit sebelum final |
| Transaksi | Buat/edit sesuai status |
| Upload bukti | Ya |
| Transaksi tanpa bukti | Ya, wajib isi alasan |
| Split transaksi | Ya, jika fitur aktif |
| Saldo saya | Lihat |
| Transfer saldo | Bisa jika diberi izin |
| Dana talangan | Catat |
| Klaim saya | Lihat status |
| Dokumentasi | Upload/edit sesuai status |
| Catatan kegiatan | Buat/edit |
| Ajukan review | Ya |
| Export final | Lihat/download jika diizinkan |

---

## 4. Matrix Permission

| Modul/Fitur | Admin | User |
|---|---|---|
| Login | Ya | Ya |
| Kelola profil lembaga | Ya | Tidak |
| Kelola user | Ya | Tidak |
| Kelola tipe LPJ | Ya | Tidak |
| Kelola template narasi | Ya | Tidak |
| Buat LPJ | Ya | Tidak |
| Lihat semua LPJ | Ya | Tidak |
| Lihat LPJ sendiri/ditugaskan | Ya | Ya |
| Edit LPJ draft | Ya | Ya, miliknya/ditugaskan |
| Ajukan LPJ | Ya | Ya |
| Review LPJ | Ya | Tidak |
| Minta revisi | Ya | Tidak |
| Setujui LPJ | Ya | Tidak |
| Finalisasi LPJ | Ya | Tidak |
| Export PDF | Ya | Ya, jika LPJ final dan diizinkan |
| Tambah dana kegiatan | Ya | Tidak |
| Tambah dana pegangan user | Ya | Tidak |
| Lihat saldo semua user | Ya | Tidak |
| Lihat saldo sendiri | Ya | Ya |
| Transfer saldo antar user | Ya | Ya, jika diizinkan |
| Approval transfer saldo | Tidak diperlukan | Tidak diperlukan |
| Lihat riwayat transfer | Ya semua | Ya miliknya |
| Koreksi saldo | Ya | Tidak |
| Catat pengeluaran | Ya | Ya |
| Validasi transaksi | Ya | Tidak |
| Upload bukti | Ya | Ya |
| Catat dana talangan | Ya | Ya |
| Verifikasi klaim | Ya | Tidak |
| Tandai klaim dibayar | Ya | Tidak |
| Upload dokumentasi | Ya | Ya |
| Rekonsiliasi | Ya | Tidak |
| Audit trail | Ya | Tidak |

---

## 5. Permission Berdasarkan Status LPJ

### draft

| Aksi | Admin | User |
|---|---|---|
| Membuat LPJ | Ya | Tidak |
| Edit data dasar | Ya | Tidak |
| Edit narasi | Ya | Tidak |
| Menugaskan user | Ya | Tidak |
| Memberi dana pegangan | Ya | Tidak |
| Tampil di PWA user | Tidak | Tidak |
| Export resmi | Tidak | Tidak |

### aktif

| Aksi | Admin | User |
|---|---|---|
| Edit data | Ya | Ya, jika ditugaskan |
| Tambah transaksi | Ya | Ya, jika ditugaskan |
| Upload bukti | Ya | Ya, jika ditugaskan |
| Upload dokumentasi | Ya | Ya, jika ditugaskan |
| Transfer saldo | Ya | Ya, jika diizinkan |
| Catat talangan | Ya | Ya, jika ditugaskan |
| Review transaksi | Ya | Tidak |
| Rekonsiliasi | Ya | Tidak |
| Export resmi | Tidak | Tidak |

### finish

| Aksi | Admin | User |
|---|---|---|
| Edit operasional | Tidak, kecuali unlock khusus | Tidak |
| Preview | Ya | Ya, jika ditugaskan/diizinkan |
| Export PDF | Ya | Ya, jika diizinkan |
| Tampil di PWA user | Ya | Ya, jika ditugaskan |
| Arsipkan | Ya | Tidak |

### arsipkan

| Aksi | Admin | User |
|---|---|---|
| Lihat | Ya | Tidak default |
| Edit | Tidak | Tidak |
| Export ulang | Ya | Tidak default |

---

## 6. Aturan Khusus Transfer Saldo

Transfer saldo antar user tidak memerlukan approval Admin.

Aturan permission:

1. User hanya dapat transfer dari saldo miliknya.
2. User hanya dapat melihat transfer miliknya.
3. Admin dapat melihat semua transfer.
4. Admin dapat membuat koreksi/reversal jika terjadi kesalahan.
5. Transfer tidak masuk LPJ final.
6. Transfer dicatat sebagai mutasi internal.

---

## 7. Aturan Khusus Dana Talangan

1. User dapat mencatat dana talangan.
2. Dana talangan membuat transaksi pengeluaran dan klaim internal.
3. Admin memvalidasi transaksi pengeluarannya.
4. Admin memverifikasi klaim internalnya.
5. Pembayaran klaim tidak menambah pengeluaran LPJ.
6. Klaim tidak tampil default di LPJ akhir.
