# رفع Patch Warqna V0.2.2 — البناء 178

1. طبّق ملف Patch فوق النسخة الأساسية ولا تحذف الملفات القديمة غير الموجودة داخل Patch.
2. ارفع الملفات المعدلة فقط إلى المستودع.
3. شغّل مسارات: Production Release Check وBackend CI وFlutter Web وAndroid وiOS.
4. على خادم Laravel نفّذ `php artisan migrate --force` ثم `php artisan optimize:clear`.
5. تأكد أن `WARQNA_API_URL` في بناء Flutter يشير إلى خادم Laravel الفعلي؛ GitHub Pages وحده لا يشغّل Laravel.
6. راجع أسرار الإعلانات وFirebase وقاعدة البيانات قبل الإنتاج.
