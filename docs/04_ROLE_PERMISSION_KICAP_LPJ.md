# Role & Permission Kicap LPJ v1.0

**Role MVP:** Admin dan User  
**Catatan:** Super Admin tidak dimunculkan untuk MVP umum.

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
| Buat LPJ | Bisa jika diberi izin |
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
| Buat LPJ | Ya | Ya, jika diberi izin |
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

### Draft

| Aksi | Admin | User |
|---|---|---|
| Edit data dasar | Ya | Ya |
| Edit narasi | Ya | Ya |
| Tambah transaksi | Ya | Ya |
| Upload bukti | Ya | Ya |
| Upload dokumentasi | Ya | Ya |
| Ajukan review | Ya | Ya |
| Export resmi | Tidak | Tidak |

### Dalam Pengisian

| Aksi | Admin | User |
|---|---|---|
| Edit data | Ya | Ya |
| Tambah transaksi | Ya | Ya |
| Transfer saldo | Ya | Ya |
| Catat talangan | Ya | Ya |
| Ajukan review | Ya | Ya |

### Diajukan

| Aksi | Admin | User |
|---|---|---|
| Review | Ya | Tidak |
| Edit besar | Ya | Tidak, kecuali dikembalikan |
| Tambah catatan revisi | Ya | Tidak |
| Export resmi | Tidak | Tidak |

### Perlu Revisi

| Aksi | Admin | User |
|---|---|---|
| Melihat alasan revisi | Ya | Ya |
| Edit data yang direvisi | Ya | Ya |
| Ajukan ulang | Ya | Ya |

### Disetujui

| Aksi | Admin | User |
|---|---|---|
| Finalisasi | Ya | Tidak |
| Edit | Terbatas | Tidak |
| Preview | Ya | Ya |
| Export resmi | Opsional | Opsional |

### Final

| Aksi | Admin | User |
|---|---|---|
| Edit | Tidak, kecuali buka lock khusus | Tidak |
| Export PDF | Ya | Ya, jika diizinkan |
| Arsipkan | Ya | Tidak |

### Diarsipkan

| Aksi | Admin | User |
|---|---|---|
| Lihat | Ya | Ya, jika terkait |
| Edit | Tidak | Tidak |
| Export ulang | Ya | Ya, jika diizinkan |

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

