# ملاحظات إصدار Warqna v165

إصدار إصلاحي مركز لحل انهيار APK على Android قبل ظهور واجهة التطبيق.

## السبب المثبت

Logcat أظهر أن AndroidX Startup يشغّل WorkManager عند بداية العملية، ثم يفشل في إنشاء WorkDatabase قبل MainActivity.

## الإصلاح

- إزالة WorkManagerInitializer من AndroidX Startup.
- إضافة WarqnaApplication لتوفير إعداد Lazy WorkManager.
- تعطيل minify وresource shrink في Release.
- إضافة قواعد ProGuard لـ WorkManager وRoom.
- الحفاظ على جميع ميزات v164.
