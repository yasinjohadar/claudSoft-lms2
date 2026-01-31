#!/usr/bin/env bash
# تشغيل عامل الطابور Laravel (مطلوب للرد التلقائي والإرسال الجماعي)
# التشغيل: من جذر المشروع (D:\111 أو /path/to/project)
#   ./scripts/queue-work.sh
# أو: bash scripts/queue-work.sh

cd "$(dirname "$0")/.." || exit 1
php artisan queue:work
