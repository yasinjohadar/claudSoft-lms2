#!/bin/bash

# Laravel Deployment Script
# استخدم هذا السكريبت بعد رفع الملفات على السيرفر

echo "🚀 بدء عملية النشر..."

# 1. تثبيت Dependencies
echo "📦 تثبيت Dependencies..."
composer install --no-dev --optimize-autoloader

# 2. مسح جميع الـ Cache
echo "🧹 مسح الـ Cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear

# 3. إنشاء Application Key (إذا لم يكن موجوداً)
if [ ! -f .env ]; then
    echo "⚠️  ملف .env غير موجود. يرجى إنشاؤه أولاً."
    exit 1
fi

# 4. ربط Storage
echo "🔗 ربط Storage..."
php artisan storage:link

# 5. إنشاء الـ Cache للإنتاج
echo "💾 إنشاء الـ Cache للإنتاج..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. مسح Permission Cache
echo "🔐 مسح Permission Cache..."
php artisan permission:cache-reset

# 7. تعيين Permissions
echo "📝 تعيين Permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# تحقق من وجود مجلدات logs
if [ ! -d "storage/logs" ]; then
    mkdir -p storage/logs
    echo "✅ تم إنشاء مجلد storage/logs"
fi

# تحقق من permissions للمجلدات
echo "🔍 فحص Permissions..."
ls -la storage/ | head -5
ls -la bootstrap/cache/ | head -5

# 8. تشغيل Migrations (اختياري - قم بإلغاء التعليق إذا لزم الأمر)
# echo "🗄️  تشغيل Migrations..."
# php artisan migrate --force

# 9. فحص Routes (اختياري - لإزالة التعليق إذا لزم الأمر)
# echo "🔍 فحص Routes..."
# php artisan route:list --name=student.question-module.start

# 10. فحص Logs Permissions
echo "📋 فحص Logs Permissions..."
if [ -f "storage/logs/laravel.log" ]; then
    echo "✅ ملف laravel.log موجود"
    ls -lh storage/logs/laravel.log
else
    echo "⚠️  ملف laravel.log غير موجود - سيتم إنشاؤه تلقائياً عند أول خطأ"
fi

echo "✅ تم النشر بنجاح!"
echo ""
echo "📋 Checklist:"
echo "  - تحقق من ملف .env (APP_DEBUG=false, APP_ENV=production)"
echo "  - تحقق من Permissions (storage و bootstrap/cache)"
echo "  - تحقق من الـ Logs: tail -f storage/logs/laravel.log"
echo "  - تحقق من Routes: php artisan route:list --name=student.question-module"
echo "  - اختبر الموقع"
echo ""
echo "🔧 في حالة وجود مشاكل:"
echo "  - افحص الـ Logs: tail -50 storage/logs/laravel.log"
echo "  - امسح Route Cache: php artisan route:clear && php artisan route:cache"
echo "  - افحص Permissions: ls -la storage/ bootstrap/cache/"

