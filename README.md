# Kicap Event

Kicap Event adalah aplikasi PWA mobile-first untuk mengelola event/kegiatan, transaksi, saldo user, dokumentasi, review Admin, finalisasi, dan export dokumen LPJ.

## Konsep Produk Terkini

Mulai 20 Juni 2026, konsep produk dikunci sebagai **Kicap Event**:

- **Event/Kegiatan** adalah objek utama yang dibuat Admin dan diisi oleh User/petugas.
- **LPJ** adalah output akhir berupa dokumen pertanggungjawaban yang digenerate setelah event/kegiatan direview dan difinalisasi.
- Perubahan ini meluruskan istilah produk, bukan mengubah alur kerja mesin.
- Internal code legacy seperti `lpj`, `lpjs`, `lpj_id`, route, model, atau service boleh tetap dipertahankan sementara jika rename berisiko.

## Slice 00 — Project Foundation

Status: pondasi awal.

### Stack aktual

- Laravel: v13.16.1
- Filament: v4.11.7
- React PWA: React + Vite
- Database: MySQL Docker
- PHP: 8.5.0
- Node: v24.16.0
- NPM: 11.13.0

### Port lokal

- App / PWA: http://127.0.0.1:8730/app
- Admin Panel: http://127.0.0.1:8730/admin
- Health Check: http://127.0.0.1:8730/health
- Vite dev server: http://127.0.0.1:5230
- MySQL host port: 127.0.0.1:3460

### Login

Kicap Event memakai satu halaman login berbasis role.

- Admin diarahkan ke `/admin`.
- User diarahkan ke `/app`.
- Login menerima username/email dan password.
- Checkbox `Ingat saya` wajib berfungsi, terutama untuk user HP.

Seed awal:

- Admin: admin@kicap.id / password
- User: user@kicap.id / password

Catatan: Slice 01 direvisi untuk mengunci login tunggal, role, status event/kegiatan sederhana, dan UI baseline.

### Menjalankan aplikasi

Terminal 1:

```bash
cd /mnt/d/kulino/lpj-kicap
docker compose up -d mysql
php artisan serve --host=127.0.0.1 --port=8730
```

Terminal 2 untuk frontend dev:

```bash
cd /mnt/d/kulino/lpj-kicap
VITE_PORT=5230 npm run dev
```

Build frontend:

```bash
cd /mnt/d/kulino/lpj-kicap
npm run build
```

### Validasi cepat

```bash
curl http://127.0.0.1:8730/health
php artisan migrate:status
npm run build
git remote -v
```

### Catatan UI

Frontend Kicap Event diarahkan menjadi mobile-first seperti aplikasi HP. User PWA memakai bottom navigation mengambang, responsive untuk HP/tablet, dan tema visual yang cocok dengan logo Kicap.

Admin panel dibuat responsive full-width agar halaman table/resource tidak terasa sempit atau menyisakan ruang kosong berlebihan.

Archive validasi slice disimpan di:

```text
docs/archive/
```

## phpMyAdmin lokal

phpMyAdmin hanya digunakan sebagai tools lokal development dan tidak jalan otomatis.

Buka phpMyAdmin:

    cd /mnt/d/kulino/lpj-kicap
    bash scripts/phpmyadmin-open.sh

Akses:

    http://127.0.0.1:8190

Login:

    Server   : mysql
    Username : lpj_kicap
    Password : lpj_kicap_secret
    Database : lpj_kicap

Tutup phpMyAdmin setelah selesai:

    cd /mnt/d/kulino/lpj-kicap
    bash scripts/phpmyadmin-close.sh

Catatan keamanan:

- phpMyAdmin dibatasi ke 127.0.0.1, jadi hanya bisa diakses dari PC lokal.
- phpMyAdmin tidak dijalankan default bersama aplikasi.
- Untuk production, phpMyAdmin tidak dipasang sebagai bagian aplikasi.
