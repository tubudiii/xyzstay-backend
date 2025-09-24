#!/bin/bash
set -e

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

# echo "🚀 Running migrations and seeders..."
# php artisan migrate:fresh --seed --force || echo "⚠️ Migration failed, skipping..."


# echo "🔐 Generating Shield permissions..."
# php artisan shield:generate --all || echo "⚠️ Shield generation failed"




echo "⚡ Caching config, route, and view..."
echo "🔗 Creating storage symlink..."
php artisan storage:link || echo "⚠️ Storage link failed"
php artisan config:cache || echo "⚠️ Config cache failed"
php artisan route:cache || echo "⚠️ Route cache failed"
php artisan view:cache || echo "⚠️ View cache failed"

echo "🚀 Starting PHP-FPM..."
exec php-fpm
