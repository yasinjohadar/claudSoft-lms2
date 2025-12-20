#!/bin/bash

# حل مشكلة Laravel Breeze بعد الرفع على السيرفر
# قم بتشغيل هذا السكريبت على السيرفر بعد الرفع

echo "=========================================="
echo "حل مشكلة Laravel Breeze بعد الرفع"
echo "=========================================="
echo ""

# الانتقال إلى مجلد المشروع
cd /home/rootclaudsoftadi/public_html || exit 1

echo "1. مسح جميع ملفات الـ cache..."
php artisan optimize:clear

echo ""
echo "2. مسح cache الـ config..."
php artisan config:clear

echo ""
echo "3. مسح cache الـ routes..."
php artisan route:clear

echo ""
echo "4. مسح cache الـ views..."
php artisan view:clear

echo ""
echo "5. حذف ملفات الـ cache يدوياً..."
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/config.php

echo ""
echo "6. إعادة بناء الـ cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "=========================================="
echo "تم إصلاح المشكلة بنجاح!"
echo "=========================================="
echo ""
echo "الآن قم بزيارة الموقع للتحقق من الحل."

