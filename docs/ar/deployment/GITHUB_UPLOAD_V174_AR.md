# رفع Warqna v174 إلى GitHub

## قبل الرفع

1. استبدل ملفات المشروع بمحتويات مجلد v174 الداخلي مع إبقاء مجلد `.git`.
2. شغّل `CHECK_WARQNA_WINDOWS.bat`.
3. تأكد أن الإصدار الظاهر هو `1.74.0+174`.
4. نفّذ Commit وPush إلى فرع `main`.

رسالة Commit مقترحة:

```text
Warqna v174 direct invite stable orientation and authoritative XP
```

## GitHub Actions المطلوبة

يجب نجاح:

- Production Release Gate
- Backend CI
- Flutter Web Pages
- Flutter Android
- Flutter iOS

Artifact Android يجب أن يحمل build **174**.

## بعد نجاح البناء

اختبر على جهاز حقيقي:

- إنشاء غرفة والانتقال الفوري إليها.
- إرسال دعوة للاعب آخر ثم الضغط على الإشعار والتأكد من فتح الغرفة مباشرة.
- الإشعار أثناء فتح التطبيق، وفي الخلفية، وبعد إغلاقه.
- ثبات الاتجاه الطولي وعدم التحول المفاجئ إلى الأفقي.
- اختيار الوضع الأفقي يدوياً ثم إعادة تشغيل التطبيق.
- انتهاء جولة وظهور XP فوراً لكل لاعب حقيقي وعدم منح البوت نقاطاً.
- المستويات 40 و51 و60 و70 و80 و90 للتأكد من نسب الانتقال الجديدة.

## نشر Laravel

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

اضبط مفاتيح Firebase وOAuth وAdMob وعنوان API الإنتاجي قبل الاختبار النهائي.
