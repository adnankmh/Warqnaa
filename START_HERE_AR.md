# ابدأ من هنا — ورقنا V0.5

1. فك جميع أجزاء الإصدار داخل `C:\xampp\htdocs\Warqnaa` مباشرة، مع الاحتفاظ بالمجلد المخفي `.git`.
2. افتح GitHub Desktop ونفّذ `Commit to main` ثم `Push origin`.
3. راقب Actions: Production Release Gate، Backend CI، Flutter Web، وAndroid APK/AAB.
4. اضبط `WARQNA_API_URL` على خادم Laravel الحقيقي عند استخدام اللعب والاقتصاد المتصل.
5. على الخادم نفّذ `composer install` ثم `php artisan migrate --force` ثم `php artisan optimize`.
6. لا تفعّل حسابات الاختبار في الإنتاج، واترك `WARQNA_SEED_DEMO_USERS=false`.
7. الإصدار الظاهر: **V0.5**. الإصدار التقني: **0.5.0+500**.
