@echo off
REM تشغيل عامل الطابور Laravel (مطلوب للرد التلقائي والإرسال الجماعي)
REM التشغيل: من جذر المشروع (مثلاً D:\111)
REM   scripts\queue-work.bat
REM أو انقر مزدوجاً بعد التأكد أن المسار الحالي هو جذر المشروع

cd /d "%~dp0\.."
php artisan queue:work
pause
