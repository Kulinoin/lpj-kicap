# Panduan Deploy VPS — lpj.kicap.id

Panduan ini untuk memasang Kicap Event dari repo GitHub SSH ke VPS sampai domain `lpj.kicap.id` siap diakses.

## Target

- Domain: `lpj.kicap.id`
- Repo: `git@github.com:Kulinoin/lpj-kicap.git`
- Web root: `/var/www/lpj-kicap/current/public`
- Stack: Nginx, PHP 8.3+, MySQL, Composer, Node.js LTS, Laravel queue, Laravel scheduler.

## 1. DNS

Di DNS provider, arahkan:

```text
A     lpj     <IP_VPS>
AAAA  lpj     <IPv6_VPS>   # opsional
```

Tunggu propagasi, lalu cek:

```bash
dig +short lpj.kicap.id
```

## 2. Paket Server

Login ke VPS sebagai user sudo, lalu pasang paket dasar:

```bash
sudo apt update
sudo apt install -y nginx mysql-server git unzip curl supervisor cron
sudo apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl
```

Pasang Composer:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
rm composer-setup.php
```

Pasang Node.js LTS sesuai standar server Anda. Contoh dengan NodeSource:

```bash
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs
```

## 3. Akses GitHub SSH

Buat deploy key di VPS:

```bash
ssh-keygen -t ed25519 -C "lpj-kicap-vps" -f ~/.ssh/lpj_kicap_deploy
cat ~/.ssh/lpj_kicap_deploy.pub
```

Tambahkan public key tersebut ke GitHub repository sebagai Deploy Key dengan akses read.

Buat SSH config:

```bash
cat <<'EOF' >> ~/.ssh/config
Host github.com-lpj-kicap
    HostName github.com
    User git
    IdentityFile ~/.ssh/lpj_kicap_deploy
    IdentitiesOnly yes
EOF
chmod 600 ~/.ssh/config
```

Tes koneksi:

```bash
ssh -T github.com-lpj-kicap
```

## 4. Clone Repo

```bash
sudo mkdir -p /var/www/lpj-kicap
sudo chown -R $USER:www-data /var/www/lpj-kicap
git clone git@github.com-lpj-kicap:Kulinoin/lpj-kicap.git /var/www/lpj-kicap/current
cd /var/www/lpj-kicap/current
```

## 5. Database

Buat database dan user MySQL:

```bash
sudo mysql
```

```sql
CREATE DATABASE lpj_kicap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'lpj_kicap'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON lpj_kicap.* TO 'lpj_kicap'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 6. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```dotenv
APP_NAME="Kicap Event"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lpj.kicap.id
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lpj_kicap
DB_USERNAME=lpj_kicap
DB_PASSWORD=GANTI_PASSWORD_KUAT

FILESYSTEM_DISK=public
QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_DOMAIN=.kicap.id
```

Jika memakai R2/S3 untuk storage lampiran, isi `R2_*` sesuai credential produksi dan sesuaikan `FILESYSTEM_DISK`.

## 7. Install Aplikasi

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Seeder hanya dijalankan jika memang ingin membuat data awal:

```bash
php artisan db:seed --force
```

Set permission:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache public/build public/icons public/favicon.ico
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

## 8. Nginx

Buat file `/etc/nginx/sites-available/lpj.kicap.id`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name lpj.kicap.id;
    root /var/www/lpj-kicap/current/public;

    index index.php index.html;
    charset utf-8;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }

    location = /robots.txt {
        access_log off;
        log_not_found off;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan site:

```bash
sudo ln -s /etc/nginx/sites-available/lpj.kicap.id /etc/nginx/sites-enabled/lpj.kicap.id
sudo nginx -t
sudo systemctl reload nginx
```

## 9. SSL

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d lpj.kicap.id
```

Pilih redirect HTTP ke HTTPS saat diminta.

## 10. Queue dan Scheduler

Jika `QUEUE_CONNECTION=database`, buat tabel queue jika migration belum tersedia:

```bash
php artisan queue:table
php artisan migrate --force
```

Buat Supervisor config `/etc/supervisor/conf.d/lpj-kicap-worker.conf`:

```ini
[program:lpj-kicap-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lpj-kicap/current/artisan queue:work --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/lpj-kicap/current/storage/logs/worker.log
stopwaitsecs=3600
```

Aktifkan:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

Tambahkan scheduler:

```bash
sudo crontab -u www-data -e
```

Isi:

```cron
* * * * * cd /var/www/lpj-kicap/current && php artisan schedule:run >> /dev/null 2>&1
```

## 11. Smoke Check

```bash
curl -I https://lpj.kicap.id
curl -I https://lpj.kicap.id/favicon.ico
curl -I https://lpj.kicap.id/icons/kicap-event-logo.svg
curl -I https://lpj.kicap.id/build/manifest.webmanifest
php artisan about
php artisan route:list --path=admin
```

Cek dari browser:

- `https://lpj.kicap.id/login`
- `https://lpj.kicap.id/admin`
- `https://lpj.kicap.id/app`

Pastikan:

- HTTPS aktif.
- Favicon tampil.
- Login memakai logo Kicap.
- Admin diarahkan ke `/admin`.
- User diarahkan ke `/app`.
- PWA bisa di-install di perangkat mobile.

## 12. Update Rilis Berikutnya

Untuk update dari GitHub:

```bash
cd /var/www/lpj-kicap/current
git pull --ff-only
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart lpj-kicap-worker:*
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
```

Lakukan smoke check ulang setelah update.
