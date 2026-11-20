# Kelautan.ID X-Media Extractor & Archiver

Aplikasi MPA (PHP 8.1+ native, MySQL/MariaDB, Apache2) untuk mengekstrak video/GIF/gambar kualitas tertinggi dari unggahan X/Twitter dan mengarsipkannya ke server lokal. Spesifikasi lengkap: `prd_kelautan_id_x_media_extractor.md`.

## Instalasi (Ubuntu + Apache2 + PHP-FPM)

```bash
sudo apt install apache2 php-fpm php-curl php-mysql php-mbstring mysql-server
sudo a2enmod rewrite headers proxy_fcgi
sudo mkdir -p /var/www/xmedia.kelautan.id && sudo cp -r . /var/www/xmedia.kelautan.id
cd /var/www/xmedia.kelautan.id

mysql -u root -p < database/schema.sql
# buat user DB (ganti password), lalu:
cp .env.example .env && nano .env

sudo chown -R www-data:www-data storage && sudo chmod -R 750 storage
sudo -u www-data php bin/create-admin.php admin admin
```

VirtualHost: `DocumentRoot /var/www/xmedia.kelautan.id/public` dengan `AllowOverride All`.
Folder `config/`, `src/`, `views/`, `storage/` berada di luar DocumentRoot.

## Pengembangan lokal

```bash
cp .env.example .env     # sesuaikan DB_*
mysql -u root < database/schema.sql
php bin/create-admin.php
php -S localhost:8000 -t public public/index.php
```

## Catatan
- Ekstraksi: FxTwitter → VxTwitter → yt-dlp (opsional, `YTDLP_ENABLED=true`).
- Foto selalu `?format={ext asli}&name=orig` (fallback `4096x4096` / `large` bila HEAD gagal).
- Download proxy & penyimpanan dibatasi whitelist host `*.twimg.com`, HTTPS, IP publik (anti-SSRF).
- `/m/{uuid}` mendukung HTTP Range (seek video).
- Login dibatasi 5 percobaan gagal per sesi (kunci 5 menit); tambahkan `fail2ban`/rate-limit Apache untuk perlindungan tingkat IP.
