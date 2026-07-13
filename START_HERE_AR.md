# ابدأ من هنا — ورقنا V176

1. ارفع المجلد كاملاً إلى المستودع مع `.github/workflows`.
2. شغّل: Production Release Check، ثم Backend CI، ثم Flutter Web/Android/iOS.
3. نفّذ في الخادم: `composer install` ثم `php artisan migrate --force` ثم `php artisan optimize:clear`.
4. اضبط `WARQNA_API_URL` بعنوان Laravel الحقيقي؛ فتح الحزمة والمقتنيات المؤقتة خادمية لمنع التلاعب.
5. جوائز الحزمة المؤقتة تظهر في تبويب **مقتنياتي** داخل المتجر مع عداد الصلاحية، وتختفي من الملكية النشطة عند الانتهاء.
6. الإصدار الحالي: **1.76.0+176**.
