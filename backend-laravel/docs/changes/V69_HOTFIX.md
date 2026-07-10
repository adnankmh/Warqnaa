# Warqna v69 Hotfix

## إصلاح Composer

- إزالة الخيار غير المدعوم `--no-audit` من ملفات التشغيل.
- استخدام `composer install --no-security-blocking` المتوافق مع نسخة Composer التي ظهرت عند المستخدم.
- إضافة fallback إلى `--no-blocking` ثم install عادي بعد ضبط `audit.block-insecure=false`.
- تحديث ملف setup-windows.bat و setup-linux-mac.sh.

## طريقة التشغيل

1. فك الضغط.
2. افتح مجلد `warqna-laravel-platform-v69-hotfix`.
3. شغل `setup-windows.bat`.
4. بعد الانتهاء شغل `start-windows.bat`.
