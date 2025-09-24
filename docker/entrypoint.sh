#!/bin/bash
set -e

# ⬇️ Pastikan vendor ada (karena kita pakai bind mount ./:/var/www)
if [ ! -f /var/www/vendor/autoload.php ]; then
  echo "📦 vendor/ tidak ditemukan, menjalankan composer install..."
  composer install --no-dev --prefer-dist --no-ansi --no-interaction --no-progress --optimize-autoloader
fi

echo "⏳ Waiting for MySQL to initialize..."
sleep 10

echo "⏳ Checking MySQL connection..."
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
    echo "⏳ Waiting for MySQL..."
    sleep 5
done

echo "✅ MySQL is ready!"

# Opsional: migrasi/seed kalau dibutuhkan
# php artisan migrate --force || echo "⚠️ Migration failed, skipping..."

echo "🔗 Creating storage symlink..."
php artisan storage:link || echo "⚠️ Storage link failed"

echo "⚡ Caching config, route, and view..."
php artisan config:cache || echo "⚠️ Config cache failed"
php artisan route:cache || echo "⚠️ Route cache failed"
php artisan view:cache || echo "⚠️ View cache failed"

echo "🚀 Starting PHP-FPM..."
exec php-fpm
