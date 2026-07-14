# إصلاح تجميع Flutter — Warqna V0.2.5

## سبب الخطأ

- `firebase_messaging 16.4.2` إصدار منشور مع خطأ تجميع (`FirebasePlugin not found`).
- تشغيل `flutter pub upgrade --major-versions` غيّر `google_mobile_ads` و`flutter_webrtc` و`flutter_lints` إلى إصدارات رئيسية لم تُختبر مع هذا الإصدار من المشروع.
- كانت هناك مراجع غير صحيحة داخل صفحتي اللعب، ودالة XP مستخدمة من ملفات `part` خارج كائن `AppController`.

## الإصدارات المثبتة لهذا الإصدار

```yaml
google_mobile_ads: 7.0.0
flutter_webrtc: 1.4.0
flutter_lints: 4.0.0
firebase_core: 4.11.0
firebase_messaging: 16.4.1
```

لا تشغّل `flutter pub upgrade --major-versions` على V0.2.5. تحديث الإصدارات الرئيسية يحتاج فرع تطوير واختبارات منفصلة.

## الإصلاح على Windows

من جذر المشروع شغّل:

```text
FIX_FLUTTER_COMPILE_WINDOWS.bat
```

السكربت يحذف `pubspec.lock` و`.dart_tool` و`build`، ثم ينفذ `flutter clean` و`flutter pub get` و`flutter analyze`.

بعد نجاحه شغّل:

```text
RUN_FLUTTER_WEB_V025.bat
```
