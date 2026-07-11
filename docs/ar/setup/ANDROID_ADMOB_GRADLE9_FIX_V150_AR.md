# إصلاح بناء Android — Warqna v150

## سبب الخطأ

كان المشروع يعتمد على:

```yaml
google_mobile_ads: ^6.0.0
```

هذا الإصدار يستخدم إعداد Gradle قديمًا لا يعمل مع Gradle 9، لذلك كان البناء يتوقف عند:

```text
Could not get unknown property 'all' for configuration container
```

## الإصلاح المطبق

- ترقية Google Mobile Ads إلى `7.0.0`، وهو أول إصدار يتضمن إصلاح Gradle 9.2.1.
- تثبيت Flutter على `3.44.0` بدل استخدام نسخة `stable` متغيرة مع الوقت.
- استخدام Java 17.
- إعادة إنشاء مجلد Android نظيفًا في كل Build.
- تثبيت Android SDK 36 وBuild Tools 36.0.0.
- ضبط `compileSdk = 36` و`minSdk = 24`.
- التحقق تلقائيًا أن `pubspec.lock` حلّ `google_mobile_ads` إلى `7.0.0` قبل البناء.
- إبقاء معرفات Google التجريبية عند عدم إدخال معرفات AdMob الحقيقية.

## البناء

من GitHub:

```text
Actions → Build Android APK and AAB → Run workflow
```

بعد النجاح حمّل Artifact باسم:

```text
warqna-v150-android
```

ويحتوي على:

```text
app-release.apk
app-release.aab
```
