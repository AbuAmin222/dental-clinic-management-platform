#!/bin/sh
set -e

echo "🚀 Starting Dental Clinic Application Bootstrapping..."

echo "🧹 Clearing outdated application data cache..."
php artisan cache:clear

echo "📦 Optimizing application performance caches and views clear..."
php artisan optimize:clear
php artisan view:clear


echo "📦 Optimizing application performance caches..."
# php artisan optimize:cache
# php artisan config:cache
# php artisan route:cache
php artisan view:cache

echo "🔗 Ensuring storage symbolic link exists..."
php artisan storage:link --force

echo "🗄️ Running database migrations..."
php artisan migrate --force

echo "🌱 Seeding default clinic settings and metadata..."
if [ "${CLINIC_RUN_SEED_ON_BOOT:-false}" = "true" ]; then
    echo "🌱 CLINIC_RUN_SEED_ON_BOOT=true — seeding default clinic settings and metadata..."
    php artisan db:seed --force
else
    echo "🌱 Skipping db:seed (CLINIC_RUN_SEED_ON_BOOT is not 'true') — this is expected on every restart after the first deploy."
fi

echo "✅ Bootstrapping completed successfully! Launching process manager..."

exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
