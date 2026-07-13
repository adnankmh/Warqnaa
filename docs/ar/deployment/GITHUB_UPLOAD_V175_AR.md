# رفع وبناء Warqna v175 عبر GitHub

1. ارفع الملفات مع الحفاظ على `.github/workflows`.
2. شغّل Production Release Check وBackend CI وFlutter Web/Android/iOS.
3. في الخادم: `composer install --no-dev --optimize-autoloader` ثم `php artisan migrate --force` ثم `php artisan optimize`.
4. اضبط أسرار API والإعلانات وFirebase، وحدد `WARQNA_API_URL` بعنوان HTTPS حقيقي.
5. GitHub Pages يبقى قابلاً للدخول محلياً عند غياب API، بينما المشتريات والتحديات والمنافسات تحتاج الخادم.
