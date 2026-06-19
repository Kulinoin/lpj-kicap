# Tech Stack & Arsitektur Kicap LPJ v1.0

**Status:** Draft teknis awal  
**Repo lokal:** `D:\kulino\lpj-kicap`  
**WSL path:** `/mnt/d/kulino/lpj-kicap`  
**GitHub SSH:** `git@github.com:Kulinoin/lpj-kicap.git`

---

## 1. Stack yang Dikunci

| Bagian | Teknologi |
|---|---|
| Backend/API | Laravel |
| Admin/backoffice | Filament |
| User lapangan | React PWA |
| Tooling frontend | Vite |
| Styling | Tailwind CSS |
| Database awal | MySQL |
| File storage | Laravel Storage |
| Job berat | Queue/Job |
| Dokumen LPJ | Server-side document generator |
| Output | PDF dan print-ready view |

Catatan:

- File handoff menyebut MySQL/PostgreSQL sebagai opsi database.
- Untuk implementasi awal project ini, gunakan MySQL agar konsisten dengan workflow lokal sebelumnya.

---

## 2. Pembagian Aplikasi

### 2.1 Laravel

Laravel menjadi backend utama untuk:

- Auth.
- API.
- Model dan database.
- Business logic.
- File storage.
- Generate dokumen.
- Queue/job.
- Audit trail.

### 2.2 Filament

Filament digunakan untuk Admin/backoffice:

- Dashboard Admin.
- Profil lembaga.
- User management.
- Master data.
- Semua LPJ.
- Dana kegiatan.
- Saldo user.
- Transaksi global.
- Klaim dana talangan.
- Review/finalisasi.
- Export dokumen.

### 2.3 React PWA

React PWA digunakan untuk user lapangan:

- Dashboard saya.
- LPJ saya.
- Catat cepat.
- Upload bukti dari HP.
- Upload dokumentasi.
- Transfer saldo.
- Dana talangan.
- Preview LPJ.
- Autosave draft ringan.

---

## 3. Arsitektur High-Level

```text
[React PWA User]
      ↓ API
[Laravel API + Business Logic]
      ↓
[MySQL Database]
      ↓
[Laravel Storage]
      ↓
[PDF Generator / Queue]

[Filament Admin]
      ↓
[Laravel Models + Services]
```

---

## 4. Modul Backend

Direkomendasikan service/action per domain:

```text
app/Services/Lpj
app/Services/Finance
app/Services/Documents
app/Services/Reimbursement
app/Services/Audit
```

Contoh service:

- `CreateLpjService`
- `GenerateNarrativeService`
- `AllocateUserBalanceService`
- `TransferUserBalanceService`
- `RecordExpenseService`
- `RecordTalanganService`
- `ValidateTransactionService`
- `FinalizeLpjService`
- `GenerateLpjPdfService`
- `CreateAuditLogService`

---

## 5. Prinsip Keuangan

Semua perubahan saldo harus melalui service agar konsisten.

### 5.1 Top-up Dana Pegangan

```text
Admin memberi dana pegangan
→ saldo user bertambah
→ mutasi fund_allocation tercatat
```

### 5.2 Pengeluaran dari Saldo Pegangan

```text
User catat pengeluaran
→ transaksi dibuat
→ jika valid/posting
→ saldo user berkurang
→ mutasi expense tercatat
```

### 5.3 Transfer Saldo Antar User

```text
User A transfer ke User B
→ validasi saldo cukup
→ saldo A berkurang
→ saldo B bertambah
→ mutasi transfer_out dan transfer_in tercatat
→ tidak butuh approval Admin
```

### 5.4 Dana Talangan

```text
User catat pengeluaran dana pribadi
→ transaksi dibuat dengan funding_mode=dana_talangan
→ saldo user tidak berubah
→ klaim internal dibuat
→ pengeluaran masuk LPJ jika transaksi valid
→ reimbursement tidak dihitung ulang
```

---

## 6. PWA MVP

Fitur PWA MVP:

1. Installable.
2. Manifest.
3. Icon aplikasi.
4. Mobile-first layout.
5. Autosave draft ringan.
6. Indikator perubahan belum tersimpan.
7. Upload foto dari kamera HP.
8. Preview dokumen.
9. Offline sync kompleks belum masuk MVP.

---

## 7. File Storage

Jenis file:

- Bukti transaksi.
- Dokumentasi kegiatan.
- Lampiran resmi.
- Lampiran internal.
- Logo lembaga.
- PDF hasil export.

Storage awal:

```text
storage/app/public
```

Setiap file harus punya metadata:

- pemilik.
- LPJ terkait.
- jenis file.
- apakah masuk LPJ final.
- caption.
- urutan.

---

## 8. Generate Dokumen

Generate dokumen sebaiknya dilakukan server-side.

Output:

- PDF.
- Print-ready HTML view.

PDF final hanya dapat dibuat untuk LPJ status Final.

---

## 9. Audit Trail

Audit minimal untuk:

- Transaksi.
- Koreksi transaksi.
- Transfer saldo.
- Koreksi saldo.
- Klaim dana talangan.
- Review LPJ.
- Finalisasi LPJ.
- Export dokumen.

Audit menyimpan:

- user pelaku.
- aksi.
- data sebelum.
- data sesudah.
- waktu.
- catatan.

---

## 10. Repo dan SSH

Local folder:

```text
D:\kulino\lpj-kicap
```

WSL path:

```bash
cd /mnt/d/kulino/lpj-kicap
```

GitHub SSH remote:

```bash
git@github.com:Kulinoin/lpj-kicap.git
```

Cek SSH:

```bash
ssh -T git@github.com
```

---

## 11. Catatan Implementasi Slice 00

Slice 00 harus:

1. Setup Laravel.
2. Setup React PWA.
3. Setup Filament.
4. Setup database MySQL.
5. Setup auth dasar.
6. Setup folder docs.
7. Setup README.
8. Connect repo via SSH.
9. Health check.
10. Archive.
11. Commit/push setelah user validasi.

