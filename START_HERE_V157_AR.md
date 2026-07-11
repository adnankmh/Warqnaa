# ابدأ من هنا — Warqna v157

هذه حزمة كاملة تضم Flutter وLaravel وGitHub Actions، وليست ملفات ترقيع منفصلة.

## الإصلاحات الجديدة

- إضافة مجموعة اختبارات Laravel Unit فعلية في `backend-laravel/tests/Unit`.
- منع خطأ PHPUnit 11: `Test directory tests/Unit not found`.
- إضافة فحص CI صريح لوجود مجموعتي `Feature` و`Unit` قبل تشغيل الاختبارات.
- تحديث `actions/checkout` و`actions/setup-java` في مسارات CI المعدلة إلى إصدارات تعمل على Node 24.
- إزالة `cache: gradle` من خطوة Java التي كانت تُنفذ قبل إنشاء ملفات Android، وهو سبب خطأ عدم العثور على ملفات Gradle.
- تحديث رفع ملفات Android إلى `actions/upload-artifact@v6`.
- توحيد الإصدار إلى `1.57.0+157`.

## الفحص المحلي

على Windows:

```bat
CHECK_V157_WINDOWS.bat
```

على Linux/macOS:

```bash
./check-v157.sh
```

بعد الرفع، شغّل Workflow: `Build Android APK and AAB` يدويًا، وراقب Backend CI.
