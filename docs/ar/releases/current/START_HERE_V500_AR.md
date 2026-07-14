# ابدأ من هنا — Warqna V0.5

الإصدار الكامل: **0.5.0+500**.

## طريقة الرفع السريعة

1. احتفظ بالمجلد المخفي `.git` داخل `C:\xampp\htdocs\Warqnaa`.
2. فك جميع أجزاء V0.5 داخل المجلد نفسه ووافق على الاستبدال.
3. افتح GitHub Desktop، ثم نفّذ `Commit to main` و`Push origin`.
4. راقب إجراءات GitHub التالية حتى تنجح:
   - Production Release Gate
   - Backend CI and Security Foundation
   - Build and deploy Flutter Web
   - Build Android APK and AAB
5. نزّل APK وAAB من قسم Artifacts داخل إجراء Android الناجح.

## قبل النشر الإنتاجي

- شغّل migrations على خادم Laravel.
- اضبط `WARQNA_API_URL` على عنوان HTTPS حقيقي.
- استخدم معرفات AdMob الإنتاجية عند الجاهزية، واترك معرفات الاختبار خلال التجربة فقط.
- لا تفعل `WARQNA_SEED_DEMO_USERS` على قاعدة بيانات عامة.
