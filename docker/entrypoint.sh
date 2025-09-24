#!/bin/bash
set -e

echo "🏁  Laravel entrypoint start"

WORKDIR="/var/www"
cd "$WORKDIR"

########################################
# 1) Pastikan composer dependencies ada
########################################
if [ ! -f "$WORKDIR/vendor/autoload.php" ]; then
  echo "📦  vendor/ tidak ditemukan, menjalankan composer install..."
  composer install \
    --no-dev \
    --prefer-dist \
    --no-ansi \
    --no-interaction \
    --no-progress \
    --optimize-autoloader
else
  echo "✅  vendor/ ditemukan"
fi

########################################
# 2) Pastikan direktori penting ada
########################################
echo "📁  Memastikan direktori storage & cache ada..."
mkdir -p storage/framework/{cache,sessions,views,testing} || true
mkdir -p bootstrap/cache || true

########################################
# 3) Perbaiki permission untuk Laravel
########################################
echo "🔧  Fixing permissions for storage & bootstrap/cache..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

########################################
# 4) Tunggu MySQL siap (kalau dipakai)
########################################
if [ -n "$DB_HOST" ]; then
  echo "⏳ Waiting for MySQL to initialize..."
  until php -r "
    try {
      new PDO('mysql:host=${DB_HOST};port=${DB_PORT:-3306};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');
      exit(0);
    } catch (Exception \$e) {
      exit(1);
    }" >/dev/null 2>&1; do
    echo "⏳ Checking MySQL connection..."
    sleep 3
  done
  echo "✅ MySQL is ready!"
fi

########################################
# 5) Laravel specific tasks
########################################
echo "🔗 Creating storage symlink..."
php artisan storage:link || true

echo "⚡ Caching config, route, and view..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

########################################
# 6) Start PHP-FPM
########################################
echo "🚀 Starting PHP-FPM..."
exec php-fpm
