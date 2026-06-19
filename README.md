# Kicap LPJ

Kicap LPJ adalah aplikasi PWA mobile-first untuk pencatatan kegiatan, transaksi, saldo user, dokumentasi, review Admin, finalisasi, dan export dokumen LPJ.

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

### Login seed awal

- Admin: admin@kicap.id
- User: user@kicap.id
- Password: password

Catatan: role detail Admin/User akan dirapikan di Slice 01.

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

Frontend Kicap LPJ diarahkan menjadi mobile-first seperti aplikasi HP. Menu dibuat ringkas, fungsional, dan mewakili workflow utama, bukan sidebar/menu yang terlalu banyak.
