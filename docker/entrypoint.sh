#!/bin/sh
set -e

echo "🚀 Starting Dental Clinic Application Bootstrapping..."

# 1. مسح كاش البيانات القديم في Redis لضمان تحديث التخصصات والأقسام عند تشغيل الـ Seeders
echo "🧹 Clearing outdated application data cache..."
php artisan cache:clear

# 2. تحسين الأداء عبر بناء ملفات الكاش للإعدادات والمسارات والواجهات في البيئة الإنتاجية
echo "📦 Optimizing application performance caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. ربط مجلد التخزين العام بالـ Public للتمكن من قراءة ملفات وصور الأشعة المرفوعة
echo "🔗 Ensuring storage symbolic link exists..."
php artisan storage:link --force

# 4. تشغيل الـ Migrations لتحديث جداول قاعدة البيانات بشكل آمن وإنتاجي
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 5. تشغيل الـ Seeders لحقن البيانات الأولية الأساسية (التخصصات والأقسام)
echo "🌱 Seeding default clinic settings and metadata..."
php artisan db:seed --force

echo "✅ Bootstrapping completed successfully! Launching process manager..."

# 6. نقل عملية التنفيذ إلى Supervisor ليبقى السيرفر والـ Queue Workers قيد التشغيل الدائم
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
