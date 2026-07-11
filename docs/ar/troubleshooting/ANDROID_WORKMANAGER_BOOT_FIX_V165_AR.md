# Warqna v165 — إصلاح انهيار APK قبل فتح الواجهة

السجل الحقيقي من الهاتف أظهر أن التطبيق لا يصل إلى Dart ولا إلى واجهة Flutter. الانهيار يحدث في Android قبل فتح MainActivity بسبب AndroidX Startup أثناء محاولة تهيئة WorkManager وقاعدة WorkDatabase.

التوقيع الظاهر في Logcat:

```text
FATAL EXCEPTION: main
Process: com.warqna.warqna_mobile
Unable to get provider androidx.startup.InitializationProvider
Failed to create an instance of androidx.work.impl.WorkDatabase
androidx.work.WorkManagerInitializer
```

## ما تم تغييره

- إزالة `androidx.work.WorkManagerInitializer` من AndroidX Startup باستخدام `tools:node=remove`.
- إضافة `WarqnaApplication` كـ `Configuration.Provider` حتى يتم تهيئة WorkManager لاحقًا بشكل آمن إذا احتاجته أي إضافة.
- إضافة اعتماد مباشر على `androidx.work:work-runtime` في مشروع Android المولّد.
- تعطيل `isMinifyEnabled` و `isShrinkResources` في Release لتجنب كسر كود Room/WorkManager المولّد.
- إضافة قواعد ProGuard احتياطية لـ WorkManager وRoom وSQLite.
- فحص CI يمنع بناء APK إذا لم تكن الحماية موجودة في Manifest وGradle.

هذا الإصلاح يستهدف سبب الانهيار المثبت من Logcat وليس تعديلًا افتراضيًا جديدًا.
