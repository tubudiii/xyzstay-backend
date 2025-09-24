#!/bin/bash
set -e

echo "🏁 Laravel entrypoint start"

WORKDIR="/var/www"
cd "$WORKDIR"

########################################
# 1) Pastikan composer dependencies ada
########################################
if [ ! -f "$WORKDIR/vendor/autoload.php" ]; then
  echo "📦 vendor/ tidak ditemukan, menjalankan composer install..."
  composer install \
    --no-dev \
    --prefer-dist \
    --no-ansi \
    --no-interaction \
    --no-progress \
    --optimize-autoloader
else
  echo "✅ vendor/ ditemukan"
fi

########################################
# 2) Pastikan direktori penting ada
########################################
echo "📁 Memastikan direktori storage & cache ada..."
mkdir -p storage/framework/{cache,sessions,views,testing} || true
mkdir -p bootstrap/cache || true

########################################
# 3) Perbaiki permission untuk Laravel
########################################
echo "🔧 Fixing permissions for storage & bootstrap/cache..."
# Ownership (kalau container jalan sebagai root & web user www-data)
chown -R www-data:www-data storage bootstrap/cache || true
# Permission grup-user read/write/execute sesuai kebutuhan Laravel
find storage -type d -exec chmod 775 {} \; || true
find storage -type f -exec chmod 664 {} \; || true
chmod -R 775 bootstrap/cache || true

# ACL (opsional, kalau tersedia di image)
if command -v setfacl >/dev/null 2>&1; then
  echo "🛡️  Applying ACL for www-data (opsional)..."
  setfacl -R -m u:www-data:rwx storage bootstrap/cache || true
  setfacl -R -d -m u:www-data:rwx storage bootstrap/cache || true
else
  echo "ℹ️  setfacl tidak tersedia, melewati ACL step (ini aman)"
fi

########################################
# 4) Generate APP_KEY jika kosong
########################################
if [ -f ".env" ]; then
  # Ambil nilai APP_KEY (bisa kosong)
  CURRENT_APP_KEY="$(grep -E '^APP_KEY=' .env | cut -d '=' -f2- | tr -d '[:space:]')"
  if [ -z "$CURRENT_APP_KEY" ]; then
    echo "🔐 APP_KEY kosong, menjalankan php artisan key:generate..."
    php artisan key:generate --force || echo "⚠️ key:generate gagal (cek .env dan permission)"
  else
    echo "✅ APP_KEY sudah terisi"
  fi
else
  echo "⚠️ File .env tidak ditemukan, lewatkan key:generate. Pastikan .env tersedia!"
fi

########################################
# 5) Tunggu DB siap (kalau variabel DB di-set)
########################################
if [ -n "${DB_HOST:-}" ] && [ -n "${DB_PORT:-}" ] && [ -n "${DB_DATABASE:-}" ] && [ -n "${DB_USERNAME:-}" ]; then
  echo "⏳ Menunggu MySQL siap di ${DB_HOST}:${DB_PORT} (db=${DB_DATABASE})..."
  # beri jeda awal kecil untuk kontainer DB yang baru naik
  sleep 5
  until php -r "
  try {
      new PDO(
          'mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}',
          '${DB_USERNAME}',
          '${DB_PASSWORD}'
      );
      exit(0);
  } catch (Exception \$e) {
      exit(1);
  }
  "; do
      echo "⏳ MySQL belum siap, retry 5s..."
      sleep 5
  done
  echo "✅ MySQL ready!"
else
  echo "ℹ️ Variabel DB tidak lengkap, melewati cek koneksi DB."
fi

########################################
# 6) Symlink storage -> public/storage
########################################
echo "🔗 Membuat storage symlink (aman jika sudah ada)..."
php artisan storage:link || echo "⚠️ storage:link gagal (mungkin sudah ada)"

########################################
# 7) Bersihkan dan cache config/route/view/event
########################################
echo "🧹 Membersihkan cache lama..."
php artisan cache:clear || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan event:clear || true

echo "⚡ Membuat cache config/route/view/event..."
php artisan config:cache || echo "⚠️ config:cache failed"
php artisan route:cache || echo "⚠️ route:cache failed"
php artisan view:cache || echo "⚠️ view:cache failed"
php artisan event:cache || echo "⚠️ event:cache failed"

########################################
# 8) (Opsional) Migrasi DB di production
########################################
# echo "🚀 Menjalankan migrasi..."
# php artisan migrate --force || echo "⚠️ Migration failed (lewati)"

########################################
# 9) Start PHP-FPM (PID 1)
########################################
echo "🚀 Starting PHP-FPM..."
exec php-fpm
