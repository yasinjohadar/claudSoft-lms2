#!/bin/bash
# تثبيت Composer على السيرفر باستخدام PHP فقط (بدون صلاحيات root).
# الاستخدام: ارفع هذا الملف إلى السيرفر ثم نفّذه: bash install-composer-on-server.sh
# أو: chmod +x install-composer-on-server.sh && ./install-composer-on-server.sh

set -e
INSTALL_DIR="${COMPOSER_HOME:-$HOME}"
COMPOSER_PHAR="$INSTALL_DIR/composer.phar"

echo "=== تثبيت Composer في: $INSTALL_DIR ==="
mkdir -p "$INSTALL_DIR"
cd "$INSTALL_DIR"

if [ -f "$COMPOSER_PHAR" ]; then
  echo "Composer موجود مسبقاً في $COMPOSER_PHAR"
else
  echo "تحميل Composer installer..."
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php composer-setup.php --install-dir="$INSTALL_DIR" --filename=composer.phar
  php -r "unlink('composer-setup.php');"
  echo "تم تثبيت Composer في: $COMPOSER_PHAR"
fi

echo ""
echo "لتثبيت حزم المشروع من مجلد التطبيق (مثلاً public_html) نفّذ:"
echo "  cd /home/rootclaudsoftadi/public_html"
echo "  php $COMPOSER_PHAR install --no-dev --optimize-autoloader"
echo ""
echo "ثم مسح الكاش:"
echo "  php artisan config:clear"
echo "  php artisan cache:clear"
