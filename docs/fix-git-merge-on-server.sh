#!/bin/bash
# حل تعارض Git عند الدمج: إزالة vendor.zip والتخلّي عن التغييرات في vendor/
# تحذير: تنفيذ git checkout -- vendor/ يستبدل مجلد vendor — الموقع سيتوقف حتى تعيد تثبيت الحزم (composer install أو رفع vendor من جهازك).
# الاستخدام: ارفع الملف إلى مجلد المشروع على السيرفر ثم نفّذه من مجلد المشروع:
#   cd /home/rootclaudsoftadi/public_html
#   bash docs/fix-git-merge-on-server.sh

set -e
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT"

echo "=== مجلد المشروع: $PROJECT_ROOT ==="
echo "تحذير: بعد هذا السكربت قد يتوقف الموقع حتى تعيد تثبيت الحزم (المسار 1 أو 2 في SERVER_SETUP_SANCTUM.md)"
echo ""

if [ -f "vendor.zip" ]; then
  echo "إزالة vendor.zip..."
  rm -f vendor.zip
  echo "تم."
else
  echo "vendor.zip غير موجود — تخطي."
fi

if [ -d "vendor" ]; then
  echo "التخلّي عن التغييرات المحلية في vendor/ (الموقع سيتوقف حتى تعيد تثبيت الحزم)..."
  git checkout -- vendor/ 2>/dev/null || true
  echo "تم."
else
  echo "مجلد vendor غير موجود — تخطي."
fi

echo ""
echo "الآن أعد تنفيذ السحب/الدمج من cPanel أو نفّذ: git pull"
echo "بعد نجاح الدمج يجب إعادة تثبيت الحزم فوراً:"
echo "  المسار 1: php ~/composer.phar install --no-dev --optimize-autoloader"
echo "  المسار 2: رفع مجلد vendor من جهازك (بعد composer install --no-dev و composer dump-autoload)"
echo "ثم: php artisan config:clear && php artisan cache:clear"
