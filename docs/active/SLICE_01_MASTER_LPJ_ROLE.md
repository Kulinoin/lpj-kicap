# Slice 01 — Master LPJ & Role

## Status

Implemented, validation script ready.

## Target

- Role Admin/User.
- Profil lembaga.
- Tipe LPJ.
- Status LPJ.
- Struktur awal data kegiatan.
- Data user seed.

## Implementasi

### Database

- Menambahkan kolom role dan izin dasar pada tabel users:
  - role
  - is_active
  - can_create_lpj
  - can_transfer_balance
- Menambahkan tabel:
  - organization_profiles
  - lpj_types
  - lpjs
  - lpj_assigned_users

### Model

- User
- OrganizationProfile
- LpjType
- Lpj
- LpjAssignedUser

### Seed

- Admin:
  - admin@kicap.id
  - password
- User:
  - user@kicap.id
  - password
- Pendamping:
  - pendamping@kicap.id
  - password
- Profil lembaga awal:
  - PT. Kazoku Indonesia Center
  - Lembaga Pelatihan Kerja
- Tipe LPJ awal:
  - Penyelenggaraan Event
  - Pendampingan Peserta Seleksi
  - Delegasi / Perwakilan
  - Bantuan Dana / Sponsorship
  - Kegiatan Internal
- Demo LPJ awal:
  - LPJ-DEMO-001

### Filament

Resource dibuat menggunakan generator Filament terpasang:

- User
- OrganizationProfile
- LpjType
- Lpj

Admin dapat mengelola master dasar dari backoffice.

### PWA/API sanity endpoint

- GET `/api/master/lpj-types`

Endpoint ini disiapkan sebagai sumber awal tipe LPJ untuk PWA.

## Validasi Otomatis

- `php artisan migrate --seed`
- `php artisan test --filter=Slice01MasterLpjRoleTest`
- `php artisan route:list`
- `php artisan optimize:clear`

## Validasi Manual

- Admin login ke panel.
- User seed tersedia.
- Admin melihat menu resource master.
- User tidak diberi akses ke panel Admin.
- Tipe LPJ tersedia.
- LPJ demo awal tersedia.
- Endpoint `/api/master/lpj-types` mengembalikan 5 tipe LPJ.

## Catatan

- Slice ini belum mengerjakan wizard narasi.
- Slice ini belum mengerjakan transaksi, saldo, dana talangan, dan dokumen PDF.
- Transfer saldo tetap mengikuti lock konsep: tanpa approval Admin, tetapi implementasi mutasi saldo baru masuk Slice 03.

## Tambahan Patch: Login Username/Email & Profil

Tambahan sebelum commit Slice 01:

- Login mendukung username atau email melalui custom Laravel Auth provider.
- Field user bertambah:
  - username
  - whatsapp
  - profile_photo_path
- Field `name` dipakai sebagai Nama Lengkap.
- Profile page ditambahkan:
  - Nama lengkap bisa diubah.
  - WhatsApp bisa diubah.
  - Foto profil bisa diubah.
  - Username dan email dikunci dari form profile.
- Seed username:
  - admin
  - user
  - pendamping

Catatan:
- User PWA nanti memakai pondasi field yang sama.
- UI profile untuk user lapangan akan disambungkan saat PWA auth/profile dibuat.

## Patch Login Form Username

- Halaman login Filament dioverride agar field login tidak lagi bertipe email-only.
- Label login menjadi `Username / Email`.
- Input login menerima username tanpa karakter `@`.
- Auth tetap memakai field request `email`, tetapi provider custom mencari ke kolom `email` atau `username`.

## Patch Profile Dropdown Modal

- `Profil Saya` tidak lagi dijadikan menu sidebar.
- Profile dipindahkan ke user dropdown di kanan atas.
- Item profile membuka modal/popup.
- Field yang bisa diedit:
  - Nama lengkap
  - WhatsApp
  - Foto profil
- Field yang dikunci:
  - Username
  - Email

Catatan:
- Halaman `MyProfile` tetap disimpan sebagai fallback teknis, tetapi tidak didaftarkan ke navigation/sidebar.

## Patch Avatar Sync & Password Profile

Penyesuaian setelah Slice 01 commit:

- Foto profil diletakkan di bagian paling atas modal profile.
- Avatar pojok kanan atas memakai `profile_photo_path` melalui kontrak Filament `HasAvatar`.
- Bagian ganti password ditambahkan di bawah sendiri.
- Password baru bersifat opsional.
- Username dan email tetap terkunci.

## Patch Center Profile Photo

- Area foto profil pada modal profile diposisikan di tengah/center.
- Fallback halaman profile juga disesuaikan agar foto profil tampil center.
