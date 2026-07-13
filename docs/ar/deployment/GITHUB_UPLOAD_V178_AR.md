# رفع وبناء Warqna v178 عبر GitHub

1. ارفع المشروع كاملاً مع مجلد `.github/workflows`.
2. شغّل Production Release Check وBackend CI وFlutter Web/Android/iOS.
3. على الخادم: `composer install --no-dev --optimize-autoloader` ثم `php artisan migrate --force` ثم `php artisan optimize`.
4. اضبط `WARQNA_API_URL` وبيانات الإعلانات وFirebase في Secrets/Variables.
5. لا تعتمد الحزم اليومية الإنتاجية دون Laravel؛ الخادم هو المرجع لوقت الفتح والمكافأة وتاريخ الانتهاء والمخزون.
